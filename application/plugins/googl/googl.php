<?php

class googl {

    protected $apiKey = 'YOUR-API-KEY';
    protected $baseURL = 'https://www.googleapis.com/urlshortener/v1/url';

    public function shortenURL($longUrl) {
        $postData = array('longUrl' => $longUrl);
        $info = $this->httpsPost($postData);
        return $info;
    }

    public function analyticsClick($shortUrl) {
        $params = array('shortUrl' => $shortUrl, 'key' => $this->apiKey,'projection' => "ANALYTICS_CLICKS");
        $info = $this->httpGet($params);
        return $info;
    }

    public function analyticsFull($shortUrl) {
        $params = array('shortUrl' => $shortUrl, 'key' => $this->apiKey,'projection' => "FULL");
        $info = $this->httpGet($params);
        return $info;
    }

    public function httpsPost($postData) {
        $curlObj = curl_init();

        $jsonData = json_encode($postData);

        curl_setopt($curlObj, CURLOPT_URL, $this->baseURL.'?key='.$this->apiKey);
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
        curl_setopt($curlObj, CURLOPT_POST, 1);
        curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

        $response = curl_exec($curlObj);

        //change the response json string to object
        $json = json_decode($response);
        curl_close($curlObj);

        return $json;
    }

    function httpGet($params) {
        $final_url = $this->baseURL.'?'.http_build_query($params);

        $curlObj = curl_init($final_url);

        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));

        $response = curl_exec($curlObj);

        //change the response json string to object
        $json = json_decode($response);
        curl_close($curlObj);

        return $json;
    }
}
?>