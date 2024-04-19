<?php

namespace BWI\Services;

class IntcomexAPI {

    private string $apiKey;
    private string $apiSecret;
    private string $host;
    private string $utcDate;

    public function __construct()
    {
        $options = get_option('bwi_options');
        $this->apiKey = $options['field_api_key'];
        $this->apiSecret = $options['field_api_secret'];
        $this->host = $options['field_api_host'];
        $this->utcDate = gmdate('Y-m-d\TH:i:s\Z');
        $this->signature = $this->getSignature();
    }

    private function getSignature() {
        return hash('sha-256',$this->apiKey.",".$this->apiSecret.",".$this->utcDate);
    }

    public function getProducts() {

    }

}