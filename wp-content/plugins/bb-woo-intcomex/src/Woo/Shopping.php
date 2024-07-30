<?php

namespace Bigbuda\BbWooIntcomex\Woo;

use Bigbuda\BbWooIntcomex\Services\IntcomexAPI;

class Shopping
{
    public IntcomexAPI $intComexAPI;
    public function __construct()
    {
        $this->intComexAPI = IntcomexAPI::getInstance();



        add_action('woocommerce_new_order', [$this,'orderCreated'] );
        add_action('woocommerce_order_status_changed', [$this,'orderChangeStatus'] );
        add_action('woocommerce_payment_complete', [$this,'completePayment'] );
    }


    public function orderCreated($orderID) {
        $orderData = [];
        $order = wc_get_order($orderID);

        foreach($order->get_items() as $item) {

        }

    }

    public function orderChangeStatus() {

    }

    public function paymentCompleted() {
        //TODO generate confirm order in intcomex
    }
}