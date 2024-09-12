<?php

namespace Bigbuda\BbWooIntcomex\Services;

use Automattic\WooCommerce\Blocks\Options;
use GuzzleHttp\Client;

class IceCatJsonAPI {


    private static $instance = null;
    private Client $client;

    private $host = 'https://live.icecat.biz/api';
    private $user;
    public static function getInstance(): ?IceCatJsonAPI
    {
        if(self::$instance === null) {
            self::$instance = new IceCatJsonAPI();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $options = get_option('bwi_options');
        $this->user = $options['field_icecat_username'];

        $this->client = new Client([
            'base_uri'=> $this->host,
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
        //die($token);
    }

    public function getProductByMpn($brand, $code) {
        return $this->request(sprintf(
            '?UserName=%s&Brand=%s&ProductCode=%s&Language=es',
            $this->user,
            $brand,
            $code
        ));
    }



    public function request($uri) {
        $response = $this->client->get($uri);
        $content = $response->getBody()->getContents();
        $content = json_decode($content);
        return $content;
    }
}