<?php

namespace Bigbuda\BbWooIntcomex\Woo;

use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;
use Bigbuda\BbWooIntcomex\Services\IntcomexAPI;

/**
 * If this file is called directly, abort.
 *
 */
if ( !defined( 'WPINC' ) ) {
    die;
}


class ProductDownloads
{
    public function __construct()
    {
        add_filter('woocommerce_customer_get_downloadable_products', [$this,'add_dynamic_link'], 10, 1);
        add_filter('woocommerce_account_downloads_columns', [$this,'add_columns']);
        add_filter('woocommerce_account_downloads_column_download-file', [$this,'add_columns_data'], 10, 1);
    }


    function add_columns($columns) {
        unset($columns['download-remaining']);
        unset($columns['download-expires']);
        //$columns['product_key'] = __('Product Key', 'bwi');
        return $columns;
    }
    public function add_dynamic_link($downloads)
    {
        $customer_orders = wc_get_orders(array(
            'customer_id' => get_current_user_id(),
            'status' => 'processing'
        ));

        foreach ($customer_orders as $order) {
            $tokens = $order->get_meta('bwi_intcomex_tokens');

            if(!is_array($tokens)) continue;

            foreach($tokens as $token) {
                $_product = SyncHelper::getProductBySKU($token->Sku);
                $product = new \WC_Product($_product->ID);
                foreach($token->Tokens as $_token) {
                    $downloads[] = array(
                        'product_key' => $_token->ProductKey,
                        'product_url' => $product->get_permalink(),
                        'product_name' => $product->get_name(),
                        'downloads_remaining' => '∞',
                        'download_url' => $_token->LinkUrl,
                        'download_name' => __("Descargar", "bwi"),
                        'order_id' => $order->get_id()
                    );
                }
            }

        }

        return $downloads;
    }

    public function add_columns_data($download) {
        ?>
        <a href="<?= $download['download_url']; ?>" target="_blank">
            <?= $download['download_name']; ?>
        </a>
        <?php if(isset($download['product_key'])): ?>
            <div style="margin-top: 1rem; min-width: 250px">
                <small><b><?= __("Product key","bwi"); ?>:</b></small><br>
                <div style="font-size: 11px"><?= $download['product_key']; ?></div>
            </div>
        <?php endif ?>
        <?php
    }




}

