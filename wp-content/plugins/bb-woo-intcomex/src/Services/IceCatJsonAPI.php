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
            urlencode($this->user),
            urlencode($brand),
            urlencode($code)
        ));
    }

    public function getDataArray($rs): array
    {
        $data = [];
        if (!empty($rs->data->GeneralInfo) && $info = $rs->data->GeneralInfo) {
            $data['name'] = $info->TitleInfo->GeneratedLocalTitle ?? "";
            $data['description'] = $info->Description->LongDesc ?? "";
            $data['summary_short'] = $info->SummaryDescription->ShortSummaryDescription ?? "";
            $data['summary_long'] = $info->SummaryDescription->LongSummaryDescription ?? "";
            $data['bullet_points'] = $info->BulletPoints->Values ?? "";
            $data['image'] = $rs->data->Image->HighPic ?? "";
            $data['gallery'] = $rs->data->Gallery ?? [];
            $data['multimedia'] = $rs->data->Multimedia ?? [];
            $data['features'] = $rs->data->FeaturesGroups ?? [];
        }
        return $data;
    }


    public function request($uri) {
        $response = $this->client->get($uri);
        $content = $response->getBody()->getContents();
        $content = json_decode($content);
        return $content;
    }
}