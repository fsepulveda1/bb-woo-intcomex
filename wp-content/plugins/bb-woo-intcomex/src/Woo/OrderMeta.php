<?php

namespace Bigbuda\BbWooIntcomex\Woo;

/**
 * If this file is called directly, abort.
 *
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

/**
 * Applies the min and max restrictions for add-to-cart field based on manager values
 */
class OrderMeta
{

    public function __construct() {
        add_action( 'woocommerce_admin_order_data_after_billing_address', [$this,'adding_order_meta'], 10, 1 );
    }

    function adding_order_meta(\WC_Order $order){

            $orderNumber = $order->get_meta('bwi_intcomex_order_number');

            echo "<div><h3>Intcomex:</h3><p>";
            echo "<strong>Número de pedido: </strong> $orderNumber  <br>";
            echo "</p></div>";

    }

}
