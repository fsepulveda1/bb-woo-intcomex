<?php

namespace Bigbuda\BbWooIntcomex\Services;

use GuzzleHttp\Client;

class IntcomexAPI {


    private static $instance = null;
    private string $apiKey;
    private string $apiSecret;
    private string $host;
    private string $utcDate;

    private Client $client;

    public static function getInstance(): ?IntcomexAPI
    {
        if(self::$instance === null) {
            self::$instance = new IntcomexAPI();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $options = get_option('bwi_options');
        $this->apiKey = $options['field_api_key'];
        $this->apiSecret = $options['field_api_secret'];
        $this->host = $options['field_api_host'];
        $this->utcDate = gmdate('Y-m-d\TH:i:s\Z');

        $token = sprintf('Bearer apiKey=%s&utcTimeStamp=%s&signature=%s',
            $this->apiKey,
            $this->utcDate,
            $this->getSignature()
        );
        $this->client = new Client([
            'base_uri'=> $this->host,
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json',
            ]
        ]);
        //die($token);
    }

    private function getSignature() {
        return hash('sha256',$this->apiKey.",".$this->apiSecret.",".$this->utcDate);
    }

    public function getProducts() {
        return $this->request('/v1/getproducts');
    }

    public function getCatalog() {
        return $this->request('/v1/getcatalog');
    }

    public function getExtendedCatalog() {
        return $this->request('/v1/downloadextendedcatalog?locale=es&format=json');
    }

    public function request($uri) {
        try {
            $response = $this->client->get($uri);
            $content = $response->getBody()->getContents();
            $content = json_decode($content);
        }
        catch (\Exception $e) {
            $content = (array) $e;
        }
        return $content;
    }
}