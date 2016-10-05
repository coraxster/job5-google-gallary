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

    /**
     * imageStoreManager constructor.
     * @param string $storeFolder
     * @throws Exception
     */
    public function __construct($storeFolder, $timeout){
        if (!is_dir($storeFolder)) {
            mkdir($storeFolder, 0777, true);
        }
        if ( (!is_dir($storeFolder) or !is_writable($storeFolder)) ){
            throw new Exception('Error with access to public folder.');
        }
        $this->storeFolder = $storeFolder;
        $this->timeout = $timeout;
    }


    /**
     * static method. url-safe sanitizing string
     * @param $str
     * @return string
     */
    public static function sanitizeString($str){
        return trim(preg_replace('/[^a-z0-9-]+/', '-', strtolower($str)), '-');
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
        if (file_exists($this->storeFolder . $fileName)) {
            $fileName = rand(5, 15) . $fileName;
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
                throw new Exception('File "' . $link . '" not saved.');
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
            if ($image == false) throw new Exception('File "' . $link . '" not saved.');
            file_put_contents($this->storeFolder . $fileName, $image);
        }
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