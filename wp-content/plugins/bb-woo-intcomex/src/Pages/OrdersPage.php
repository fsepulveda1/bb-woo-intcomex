<?php

namespace Bigbuda\BbWooIntcomex\Pages;

use Bigbuda\BbWooIntcomex\Services\IntcomexAPI;
use GuzzleHttp\Exception\RequestException;

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

        $tabs = [
            'get_order' => [
                'title' => 'Consultar pedidos',
                'description' => 'Ingresa el número de pedido y presiona el botón \'Consultar\' para obtener un detalle del pedido en Intcomex.'
            ],
            'get_order_status' => [
                'title' => 'Consultar estado de un pedido',
                'description' => 'Ingresa el número de pedido y presiona el botón \'Consultar\' para obtener el estado del pedido en Intcomex.'
            ],
            'generate_tokens' => [
                'title' => 'Generar token',
                'description' => 'Ingresa el número de pedido y presiona el botón \'Generar token\' para obtener generar el(los) token(s) de un pedido.'
            ],
            'get_token_status' => [
                'title' => 'Consultar estado de token',
                'description' => 'Ingresa el número de pedido y presiona el botón \'Consultar token\' para obtener el estado de un token.'
            ],
            'get_invoice' => [
                'title' => 'Consultar recibo',
                'description' => 'Ingresa el número de pedido y presiona el botón \'Consultar\' para obtener el recibo.'
            ]
        ];

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
                $type = $_POST['type'] ?? 'get_order';

                $data = match($type) {
                    'get_order' => (array)$intcomexAPI->getOrder($orderNumber),
                    'get_order_status' => (array)$intcomexAPI->getOrderStatus($orderNumber),
                    'generate_tokens' => (function () use($orderNumber, $intcomexAPI){

                        $tokens = $intcomexAPI->generateTokens($orderNumber);

                        if($tokens) {
                            $order = wc_get_order($orderNumber);
                            if(! $order->meta_exists('bwi_intcomex_tokens')) {
                                $order->add_meta_data('bwi_intcomex_tokens', $tokens, true);
                                $order->save();
                            }
                        }

                        return (array)$tokens;
                    })(),
                    'get_token_status' => (array)$intcomexAPI->getTokenStatus($orderNumber),
                    'get_invoice' => (array)$intcomexAPI->getInvoice($orderNumber),
                };

                if(!empty($data)) {
                    wp_send_json([
                        'status' => 'success',
                        'data' => $data
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
        catch (RequestException $e) {
            wp_send_json_error([
                'status' => 'request_error',
                'message' => $e->getResponse()->getBody()->getContents()
            ]);
        }
        catch (\Exception $e) {
            wp_send_json_error([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

}




