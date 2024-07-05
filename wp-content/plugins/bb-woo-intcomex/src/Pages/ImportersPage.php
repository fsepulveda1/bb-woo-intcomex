<?php

namespace Bigbuda\BbWooIntcomex\Pages;

use Bigbuda\BbWooIntcomex\Importer\ImporterFactory;

/**
 * If this file is called directly, abort.
 *
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

class ImportersPage {

    const BATCH_PROCESS_TYPE = "batch";
    const SINGLE_PROCESS_TYPE = "single";

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        add_action('admin_menu', [$this, 'settings_menu']);
        add_action('wp_ajax_process_script', [$this, 'process_script']);
    }

    public function settings_menu() {
        add_submenu_page(
            'bwi-settings',
            'Importadores',
            'Importadores',
            'manage_options',
            'bwi-importers',
            [$this,'settings_page']
        );
    }

    public function settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        /** TODO get importers from importer factory */
        $importers = [
            [
                'title' => __('Sincronizar catalogo de productos', 'bwi'),
                'type' => 'product',
                'description' => __('Sincronización de productos desde Intcomex a Woocommerce.','bwi'),
                'process_type' => 'batch',
                'log_file' => 'product_sync.log',
                'fields' =>   []
            ],
            [
                'title' => __('Sincronizar catalogo extendido de productos', 'bwi'),
                'description' => __('Sincronización de datos de productos desde Intcomex a Woocommerce.','bwi'),
                'type' => 'extended_catalog',
                'process_type' => 'batch',
                'log_file' => 'product_images_sync.log',
                'fields' => []
            ],
            [
                'title' => __('Sincronizar lista de precios', 'bwi'),
                'description' => __('Sincronización de datos de productos desde Icecat a Woocommerce.','bwi'),
                'type' => 'product_prices',
                'process_type' => 'batch',
                'log_file' => 'product_images_sync.log',
                'fields' => []
            ],
            [
                'title' => __('Sincronizar inventario', 'bwi'),
                'description' => __('Sincronización de datos de productos desde Icecat a Woocommerce.','bwi'),
                'type' => 'product_inventory',
                'process_type' => 'batch',
                'log_file' => 'product_images_sync.log',
                'fields' => []
            ],
            [
                'title' => __('Sincronizar información de icecat', 'bwi'),
                'description' => __('Sincronización de datos de productos desde Icecat a Woocommerce.','bwi'),
                'type' => 'icecat_images',
                'process_type' => 'batch',
                'log_file' => 'icecat_images_sync.log',
                'fields' => []
            ],
        ];

        include_once BWI_DIR."/templates/page-sync.php";
    }

    public function admin_scripts() {
        wp_enqueue_style('bootstrap','https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
        wp_enqueue_script('bootstrap','https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js');

        wp_enqueue_style('bwi-main', BWI_URL . '/assets/css/main.css');
        wp_enqueue_script( 'bwi-ajax', BWI_URL . '/assets/js/main.js', array(), "1.0", true );
        wp_localize_script( 'bwi-ajax', 'bwi_ajax_values',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'ajax_nonce' => wp_create_nonce( '_ajax_nonce' ),
            )
        );
    }

    /**
     * Procesa la importación de productos mediante lotes gestionados por ajax.
     * @return void
     */
    public function process_script() {

        try {
            $response = array();
            $form_data = $_POST;
            $importerType = $form_data['type'];
            $importProcessType =  $form_data['process_type'];

            if(!check_ajax_referer( '_ajax_nonce', 'ajax_nonce' )) {
                wp_send_json_error(['message' => 'Hay problemas con el token']);
            }

            if (current_user_can('administrator')) {
                $importerFactory = new ImporterFactory($importerType);

                if($importProcessType == self::BATCH_PROCESS_TYPE) {
                    $response = $importerFactory->importBatch($form_data);
                }
                if($importProcessType == self::SINGLE_PROCESS_TYPE) {
                    $response = $importerFactory->importSingle($form_data);
                }

            }
        }
        catch (\Exception $e) {
            $response['result'] = 'FAIL';
            $response['message'] = $e->getMessage();
        }
        wp_send_json($response);
    }

}




