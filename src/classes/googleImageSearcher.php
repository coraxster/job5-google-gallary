<?php

/**
 * Created by PhpStorm.
 * User: dkuzmin
 * Date: 05.10.16
 */
class googleImageSearcher
{
    /**
     * @var string
     * Google dev api key
     */
    protected $apiKey;

    /**
     * @var string
     * Google custom search key
     */
    protected $customSearchKey;

    /**
     * @var string
     * request string
     */
    protected $query;

    /**
     * @var int
     * number of offset items to save object state
     */
    protected $chunkOffset = 0;

    /**
     * googleImageSearcher constructor.
     * @param $apiKey
     * @param $customSearchKey
     * @throws Exception
     */
    public function __construct($apiKey, $customSearchKey) {
        if ( (!is_string($apiKey)) or (strlen($apiKey) < 5) ){
            throw new Exception('Error with access to GoogleAPI key');
        }

        if ( (!is_string($customSearchKey)) or (strlen($customSearchKey) < 5) ){
            throw new Exception('Error with access to GoogleCustomSearchKey');
        }

        $this->apiKey = $apiKey;
        $this->customSearchKey = $customSearchKey;
    }


    /**
     * general static method
     * @param string $apiKey
     * @param string $customSearchKey
     * @param string $query
     * @param int $limit
     * @param int $start
     * @return array
     * @throws Exception
     */
    protected static function _doSearch($apiKey, $customSearchKey, $query, $limit = 10, $start = 1){
        $query = urlencode($query);
        $url = "https://www.googleapis.com/customsearch/v1?key={$apiKey}&cx={$customSearchKey}&q={$query}&searchType=image&fileType=jpg&imgSize=xlarge&num={$limit}&start={$start}&alt=json";

        if ( function_exists('curl_version') ){
            $ch =  curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $jsonResult = curl_exec($ch);
        }elseif( ini_get('allow_url_fopen') ){
            $jsonResult = file_get_contents($url);
        }else{
            throw new Exception('Not available: curl or php.ini => allow_url_fopen');
        }

        if (!$jsonResult){
            throw new Exception('Google API error 1. Request: ' . $url);
        }
        $obj = json_decode($jsonResult,true);

        if (!$obj){
            throw new Exception('Google API error 2. Responce:' . $jsonResult);
        }

        if (isset($obj['error'])){
            throw new Exception('Google API error 3. ' . $obj['error']['message']);
        }

        if (!isset($obj['items'])){
            throw new Exception('Google API error 4. No items object');
        }

        return $obj['items'];
    }


    /**
     * @param string $query
     */
    public function setQuery($query){
        $this->query = $query;
    }

    /**
     * @param string $query
     * @param int $num
     * @return array
     */
    public function getChunk($num = 10){
        if ($num > 10) $num = 10;
        $imagesDataChunk = self::_doSearch($this->apiKey, $this->customSearchKey, $this->query, $num, 1+$this->chunkOffset);
        $this->chunkOffset = $this->chunkOffset + $num;
        return $imagesDataChunk;
    }


    /**
     * @param $num
     * @return array
     * @throws Exception
     */
    public function getAll($num){
        if ($num > 100){
            throw new Exception('Google API error 5. Google limits number of total results for query. Its 100.');
        }
        $imagesDataArray = array();
        do{
            $imagesDataChunk = $this->getChunk( $num - count($imagesDataArray) );
            $imagesDataArray = array_merge($imagesDataArray, $imagesDataChunk);
        }while ( (count($imagesDataArray) <> $num) and (count($imagesDataChunk)) );

        return $imagesDataArray;
    }





}