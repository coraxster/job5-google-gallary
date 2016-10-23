<?php

/**
 * Created by PhpStorm.
 * User: dkuzmin
 * Date: 05.10.16
 */
class imageStoreManager
{
    /**
     * @var string
     * folder to store set of images
     */
    protected $storeFolder;

    /**
     * @var int
     * download timeout
     */
    protected $timeout;

    /**
     * @var array
     * already saved images
     */
    protected $savedImages = array();

    protected $thSize;

    /**
     * imageStoreManager constructor.
     * @param string $storeFolder
     * @throws Exception
     */
    public function __construct($storeFolder, $timeout, $thSize){
        if (!is_dir($storeFolder)) {
            mkdir($storeFolder, 0777, true);
        }
        if ( (!is_dir($storeFolder) or !is_writable($storeFolder)) ){
            throw new Exception('Error with access to public folder.');
        }
        $this->storeFolder = $storeFolder;
        $this->timeout = $timeout;
        $this->thSize = $thSize;
    }


    /**
     * static method. url-safe sanitizing string
     * @param $str
     * @return string
     */
    public static function sanitizeString($str){
        return trim(preg_replace('/[^a-z0-9-]+/', '-', strtolower($str)), '-');
    }


    private static function compressJpg($fileLocationFrom, $fileLocationTo, $width, $height){
        list($width_orig, $height_orig, $image_type) = getimagesize($fileLocationFrom);
        $ratio_orig = $width_orig / $height_orig;
        if ($width / $height > $ratio_orig) {
            $width = $height * $ratio_orig;
        } else {
            $height = $width / $ratio_orig;
        }
        $image_p = imagecreatetruecolor($width, $height);
        switch ($image_type) {
            case 1: $image = imagecreatefromgif($fileLocationFrom); break;
            case 2: $image = imagecreatefromjpeg($fileLocationFrom);  break;
            case 3: $image = imagecreatefrompng($fileLocationFrom); break;
            default: throw new \Exception('Unable to determine the type of image ' . $fileLocationFrom);
        }
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
        switch ($image_type) {
            case 1: $result = imagegif($image_p, $fileLocationTo); break;
            case 2: $result = imagejpeg($image_p, $fileLocationTo, 100);  break; // best quality
            case 3: $result = imagepng($image_p, $fileLocationTo, 0); break; // no compression
            default: throw new \Exception('Unable to save image ' . $fileLocationTo);
        }
        chmod($fileLocationTo, 0644);
        return $result;
    }


    /**
     * @return int
     */
    public function getSavedCount(){
        return count($this->savedImages);
    }

    /**
     * @return array
     */
    public function getSavedImages(){
        return $this->savedImages;
    }


    /**
     * @param string $link
     * @param string $fileName
     * @return bool
     * @throws Exception
     */
    public function storeImage($link, $fileName){
        while(file_exists($this->storeFolder . $fileName)){
                $fileName = rand(0, 10) . $fileName;
            }

        if ( function_exists('curl_version') ){
            $ch = curl_init($link);
            $fp = fopen($this->storeFolder . $fileName, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            if(curl_errno($ch)){
                fclose($fp);
                throw new \Exception('File "' . $link . '" not saved.');
            }
            fclose($fp);

        }else{
            $opts = array('http' =>
                array(
                    'method'  => 'GET',
                    'timeout' => $this->timeout
                )
            );
            $context  = stream_context_create($opts);
            $image = @file_get_contents($link, false, $context);
            if ($image == false) throw new \Exception('File "' . $link . '" not saved.');
            file_put_contents($this->storeFolder . $fileName, $image);
        }
        $thDir = $this->storeFolder . 'th/';
        if (!is_dir($thDir)){
            mkdir($thDir);
        }
        self::compressJpg($this->storeFolder . $fileName, $thDir . $fileName, $this->thSize, $this->thSize);
        $this->savedImages[] = $fileName;
        return true;
    }

    public function emptyStorage(){
        $files = glob($this->storeFolder); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file))
                unlink($file); // delete file
        }
    }







}