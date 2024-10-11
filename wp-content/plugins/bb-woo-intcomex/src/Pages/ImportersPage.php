<?php

namespace Bigbuda\BbWooIntcomex\Pages;

use Bigbuda\BbWooIntcomex\Importer\ImporterFactory;
use GuzzleHttp\Exception\RequestException;

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

        $options = get_option('bwi_options');
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
                'description' => __('Sincronización de datos e imágenes de productos desde Intcomex a Woocommerce.','bwi'),
                'type' => 'extended_catalog',
                'process_type' => 'batch',
                'log_file' => 'product_images_sync.log',
                'fields' => [
                    [
                        'type' => 'checkbox',
                        'name' => 'force_update',
                        'label' => 'Forzar actualización',
                        'description' => 'Si se marca esta opción, se eliminarán imágenes y atributos de los productos existentes para reemplazarlos por nuevos valores.'
                    ]
                ]
            ],
            [
                'title' => __('Sincronizar lista de precios', 'bwi'),
                'description' => __('Sincronización de datos de precios de USD a CLP desde intcomex a Woocommerce.','bwi'),
                'type' => 'product_prices',
                'process_type' => 'batch',
                'log_file' => 'product_images_sync.log',
                'fields' => [],
                'info' => [
                    'Dólar observado' => '$'.getUsdValue(),
                    'Fecha de actualización dolar' => get_option('USD2CLP_date') ? \DateTime::createFromFormat('Y-m-d',get_option('USD2CLP_date'))->format('d-m-Y') : "",
                    'Margen de ganancia' => ($options['field_profit_margin'] ?? 0).'%'
                ]
            ],
            [
                'title' => __('Sincronizar inventario', 'bwi'),
                'description' => __('Sincronización del inventario de productos desde Intcomex a Woocommerce.','bwi'),
                'type' => 'product_inventory',
                'process_type' => 'batch',
                'log_file' => 'product_images_sync.log',
                'fields' => []
            ],
            [
                'title' => __('Sincronizar información de icecat', 'bwi'),
                'description' => __('Sincronización de imagenes y título de productos desde Icecat a Woocommerce.','bwi'),
                'type' => 'icecat_images',
                'process_type' => 'batch',
                'log_file' => 'icecat_images_sync.log',
                'fields' => [
                    [
                        'type' => 'checkbox',
                        'name' => 'force_update',
                        'label' => 'Forzar actualización',
                        'description' => 'Si se marca esta opción, se eliminarán imágenes y atributos de los productos existentes para reemplazarlos por nuevos valores.'
                    ]
                ]
            ],
            [
                'title' => __('Regenerar miniaturas de imágenes', 'bwi'),
                'description' => __('Vuelve a generar las miniaturas de los productos en woocommerce.','bwi'),
                'type' => 'regenerate_thumbnails',
                'process_type' => 'batch',
                'log_file' => 'regenerate_thumbnails.log',
                'fields' => []
            ],
        ];

        include_once BWI_DIR."/templates/page-sync.php";
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
        catch (\Throwable $e) {
            $response['result'] = 'FAIL';
            $response['message'] = method_exists($e,'getResponse') ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
        }
        wp_send_json($response);
    }

}




