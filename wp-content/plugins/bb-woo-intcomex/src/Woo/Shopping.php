<?php

namespace Bigbuda\BbWooIntcomex\Woo;

use Bigbuda\BbWooIntcomex\Services\IntcomexAPI;
use JetBrains\PhpStorm\NoReturn;

class Shopping
{
    public IntcomexAPI $intcomexAPI;
    public function __construct()
    {
        $this->intcomexAPI = IntcomexAPI::getInstance();
        add_action('woocommerce_checkout_order_created', [$this,'orderCreated'] );
        add_action('woocommerce_store_api_checkout_order_processed', [$this,'orderCreated'] );
        add_action('woocommerce_order_status_cancelled', [$this,'orderCancelled'] );
        add_action('woocommerce_order_status_processing', [$this,'orderProcessing'] );
    }


    public function orderCreated(\WC_Order $order): void
    {

        try {
            $orderItems = $this->getOrderItemsArray($order);
            $intcomexOrder = $this->intcomexAPI->getOrder($order->get_id());

            if(!isset($intcomexOrder->OrderNumber)) {
                $response = $this->intcomexAPI->placeOrder($orderItems, $order->get_id());
                $order->update_meta_data('bwi_intcomex_order_number', $response->OrderNumber);
            }
            else {
                $response = $this->intcomexAPI->updateOrder([
                    'Items' => $orderItems,
                    'OrderNumber' => $intcomexOrder->OrderNumber
                ]);
                $order->update_meta_data('bwi_intcomex_order_number', $intcomexOrder->OrderNumber);
            }
        }
        catch (\Exception $exception) {
            var_dump($exception);
        }

    }

    public function orderCancelled($order_id): void
    {
        $order = wc_get_order($order_id);
        $response = $this->intcomexAPI->cancelOrder(['OrderNumber' => $order->get_meta('bwi_intcomex_order_number')]);
        var_dump($response);
        die;
    }

    public function orderProcessing($order_id): void
    {
        $order = wc_get_order($order_id);
        $response = $this->intcomexAPI->releaseOrder(['OrderNumber' => $order->get_meta('bwi_intcomex_order_number')]);
        var_dump($response);
        die;
    }

    private function getOrderItemsArray(\WC_Order $order): array
    {
        $itemsArray = [];
        foreach($order->get_items() as $item) {
            $data = $item->get_data();
            $product = new \WC_Product($data['product_id']);
            $itemsArray[] = [
                'Sku' => $product->get_sku(),
                'Quantity' => $item->get_quantity(),
                'StoreItemId' => $product->get_id()
            ];
        }
        return $itemsArray;
    }
}