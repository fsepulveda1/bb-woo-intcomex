<?php

namespace Bigbuda\BbWooIntcomex\Services;

use Automattic\WooCommerce\Blocks\Options;
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
        $this->apiKey = $options['field_api_key'] ?? "";
        $this->apiSecret = $options['field_api_secret'] ?? "";
        $this->host = $options['field_api_host'] ?? "";
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

    public function renewToken() {
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
    }

    private function getSignature() {
        return hash('sha256',$this->apiKey.",".$this->apiSecret.",".$this->utcDate);
    }

    public function getProducts() {
        return $this->request('/v1/getproducts');
    }

    public function getProduct($sku) {
        return $this->request('/v1/getproduct?mpn='.$sku);
    }

    public function getCatalog() {
        return $this->request('/v1/getcatalog');
    }

    public function getInventory() {
        return $this->request('/v1/getinventory');
    }

    public function getPriceList() {
        return $this->request('/v1/getpricelist');
    }

    public function getExtendedCatalog() {
        return $this->request('/v1/downloadextendedcatalog?locale=es&format=json');
    }

    /**
     * @param array{
     *      OrderNumber: string,
     *      LocationId: string,
     *      Tag: string,
     *      CustomerOrderNumber: string,
     *      Total: numeric,
     *      Discounts: numeric,
     *      DiscountType: string,
     *      CouponCodes : string[],
     *      TaxRegistrationNumber: string,
     *      InvoiceRequested: bool,
     *      ReceiveInvoiceByMail: bool,
     *      StoreOrder: object,
     *      Payments: array,
     *      Shipments: array,
     *      Items: array,
     *      TaxesIncludedInPrice: bool,
     *      Attachments: array,
     *      Options: array,
     *      AddressId: array
     *  } $params
     * @return array|mixed
     */
    public function placeOrder(array $params,$customerOrderNumber, $carrierID = 'CLA7') {
        return $this->post('/v1/placeorder?customerOrderNumber='.$customerOrderNumber.'&carrierId='.$carrierID, $params);
    }

    public function processOrder(array $params) {
        return $this->post('/v1/processorder', $params);
    }

    public function getOrder($orderNumber) {
        return $this->request('/v1/getorder?orderNumber='.$orderNumber);
    }

    public function getOrderStatus($orderNumber) {
        return $this->request('/v1/getorderstatus?orderNumber='.$orderNumber);
    }

    public function getOrderList($orderNumber) {
        return $this->request('/v1/getorders');
    }

    /**
     * @param array{OrderNumber: string, Payments: array} $params
     * @return array|mixed
     */
    public function registerOrderPayments(array $params) {
        return $this->post('/v1/registerpayments', $params);
    }

    /**
     * @param array{OrderNumber: string} $params
     * @return array|mixed
     */
    public function releaseOrder(array $params) {
        return $this->post('/v1/releaseOrder', $params);
    }

    /**
     * @param array{OrderNumber: string} $params
     * @return array|mixed
     */
    public function cancelOrder(array $params) {
        return $this->post('/v1/cancelorder', $params);
    }

    /**
     * @param array{Items: array,OrderNumber: string} $params
     * @return array|mixed
     */
    public function updateOrder(array $params) {
        return $this->post('/v1/updateorder', $params);
    }

    public function getInvoice($orderNumber,$invoiceNumber=null) {
        return $this->request(
            sprintf(
                '/v1/getinvoice?invoiceNumber=%s&orderNumber=%s',
                $invoiceNumber,
                $orderNumber
            )
        );
    }

    public function downloadAttachment($orderNumber,$attachmentId) {
        return $this->request(
            sprintf(
                '/v1/downloadattachment?orderNumber=%s&attachmentId=%s&downloadFile=1',
                $orderNumber,
                $attachmentId
            )
        );
    }

    /**
     * @param array{
     *     LocationId: string,
     *     Items: array,
     *     AddressId: array
     * } $params
     * @return array|mixed
     */
    public function calculateShippingRates(array $params) {
        return $this->post('/v1/calculateshippingrates', $params);
    }

    public function generateTokens($orderNumber) {
        return $this->post('/v1/generatetokens',['OrderNumber' => $orderNumber]);
    }

    public function getTokenStatus($productKey) {
        return $this->request('/v1/gettokenstatus?productKey='.$productKey);
    }


    public function request($uri) {
        $response = $this->client->get($uri);
        $content = $response->getBody()->getContents();
        return json_decode($content);
    }

    public function post($uri, $params) {

        $response = $this->client->post($uri,[
            'body' => json_encode($params)
        ]);
        $content = $response->getBody()->getContents();
        return json_decode($content);
    }
}