<?php

namespace Bigbuda\BbWooIntcomex\Woo;

use Bigbuda\BbWooIntcomex\Services\IntcomexAPI;
use BMI\Classes\ManagerDBHandler;
use JetBrains\PhpStorm\NoReturn;

class Shopping
{
    public IntcomexAPI $intcomexAPI;
    public function __construct()
    {
        $this->intcomexAPI = IntcomexAPI::getInstance();
        add_action('woocommerce_order_status_processing', [$this,'orderProcessing'] );
        add_action('woocommerce_order_status_completed', [$this,'orderProcessing'] );
        add_filter( 'woocommerce_add_to_cart_validation', [$this,'add_stock_validation'], 10, 3 );
    }

    // Validating stock when item cart is added
    public function add_stock_validation( $passed, $product_id, $quantity ) {
        $options = get_option('bwi_options');

        if(!$options['field_activate_cart_validation']) {
            return $passed;
        }

        if($passed) {
            $product = wc_get_product($product_id);

            if($product->is_virtual()) {
                return $passed;
            }

            $intcomexAPI = IntcomexAPI::getInstance();
            $intcomexProduct = $intcomexAPI->getProduct($product->get_sku());
            $passed = $quantity <= $intcomexProduct->InStock;

            if(!$passed) {
                $product->set_stock_quantity($intcomexProduct->InStock);
                $product->set_stock_status('outofstock');
                $product->save();
                wc_add_notice('No hay stock suficiente.', 'error');
            }

        }
        return $passed;
    }

    public function orderProcessing($order_id): void
    {
        $order = wc_get_order($order_id);
        try {
            $orderItems = $this->getOrderItemsArray($order);
            $intcomexOrder = $this->intcomexAPI->getOrder($order->get_id());

            if(isset($intcomexOrder->OrderNumber)) {
                $orderNumber = $intcomexOrder->OrderNumber;
            }
            else {
                $response = $this->intcomexAPI->placeOrder($orderItems, $order->get_id());
                $orderNumber = $response->OrderNumber;
            }

            if(!$order->get_meta('bwi_intcomex_order_number')) {
                $order->add_meta_data('bwi_intcomex_order_number', $orderNumber);
            }

            if($this->order_has_virtual_items($order)) {
                $intcomexAPI = IntcomexAPI::getInstance();
                $tokens = $intcomexAPI->generateTokens($orderNumber);
                $order->add_meta_data('bwi_intcomex_tokens', $tokens, true);
            }

        }
        catch (\Exception $exception) {
            $order->add_order_note('Intocomex error: '.$exception->getMessage());
            $order->update_meta_data('bwi_intcomex_order_error', $exception->getMessage());
        }

        $order->save();

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



    private function order_has_virtual_items(\WC_Order $order): bool
    {
        foreach ($order->get_items() as $order_item){
            $item = new \WC_Product($order_item->get_product_id());
            if ($item->is_virtual()) {
                return true;
            }
        }
        return false;
    }
}