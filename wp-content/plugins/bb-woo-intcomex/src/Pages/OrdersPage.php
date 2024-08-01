<?php

namespace Bigbuda\BbWooIntcomex\Pages;

use Bigbuda\BbWooIntcomex\Services\IntcomexAPI;

/**
 * If this file is called directly, abort.
 *
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

class OrdersPage {

    const BATCH_PROCESS_TYPE = "batch";
    const SINGLE_PROCESS_TYPE = "single";

    public function __construct()
    {
        add_action('admin_menu', [$this, 'settings_menu']);
        add_action('wp_ajax_bwi_get_intcomex_order', [$this, 'get_intcomex_order']);
    }

    public function settings_menu() {
        add_submenu_page(
            'bwi-settings',
            'Pedidos',
            'Pedidos',
            'manage_options',
            'bwi-orders',
            [$this,'settings_page']
        );
    }

    public function settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        include_once BWI_DIR."/templates/page-orders.php";
    }

    /**
     * Procesa la importación de productos mediante lotes gestionados por ajax.
     * @return void
     */
    public function get_intcomex_order() {

        try {
            if(!check_ajax_referer( '_ajax_nonce', 'ajax_nonce' )) {
                wp_send_json_error(['message' => 'Hay problemas con el token']);
            }

            if (current_user_can('administrator')) {
                $orderNumber = isset($_POST['order_number']) && is_numeric($_POST['order_number']) ? $_POST['order_number'] : null;
                $intcomexAPI = IntcomexAPI::getInstance();
                $intcomexOrder = $intcomexAPI->getOrder($orderNumber);

                if(isset($intcomexOrder->OrderNumber)) {
                    wp_send_json([
                        'status' => 'success',
                        'data' => (array)$intcomexOrder
                    ]);
                }
                else {
                    wp_send_json_error([
                        'status' => 'not-found',
                        'message' => 'Pedido no encontrado'
                    ]);
                }
            }
        }
        catch (\Exception $e) {
            wp_send_json_error([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

}




