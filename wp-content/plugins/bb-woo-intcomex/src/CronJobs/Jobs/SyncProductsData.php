<?php

namespace Bigbuda\BbWooIntcomex\CronJobs\Jobs;

use Bigbuda\BbWooIntcomex\CronJobs\CronJobInterface;
use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;
use Bigbuda\BbWooIntcomex\Services\IceCatAPI;
use Bigbuda\BbWooIntcomex\Services\IceCatJsonAPI;
use Bigbuda\BbWooIntcomex\Services\IntcomexAPI;
use GuzzleHttp\Exception\ClientException;
use WP_Query;

class SyncProductsData implements CronJobInterface {

    public static function getNiceName(): string {
        return "Sincronización de catálogo de productos";
    }

    public static function getCronActionName(): string
    {
        return "bwi_sync_products_data";
    }

    public function run()
    {
        @ini_set('max_execution_time',-1);
        @ini_set('memory_limit',-1);

        $logfile = self::getCronActionName();
        $sync_base = true;
        $sync_extend = true;
        $sync_icecat = true;
        $removeNotIntcomexProducts = true;

        try {
            if (!function_exists('media_handle_upload')) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
            }

            if (!function_exists('wp_create_term')) {
                require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
            }

            plugin_log('Iniciando sincronización de productos', $logfile, 'w');

            $intcomexAPI = IntcomexAPI::getInstance();
            if ($sync_base) {
                plugin_log('Iniciando sincronización del catalogo', $logfile);
                $allProducts = $intcomexAPI->getCatalog();
                foreach ($allProducts as $intcomexProduct) {
                    $importerResponse = SyncHelper::addProductBase($intcomexProduct);
                    if ($importerResponse->isError()) {
                        plugin_log([
                            'error' => $importerResponse->getErrors(),
                            'intcomexProduct' => $intcomexProduct->Sku
                        ], $logfile);
                    }
                }

                unset($allProducts);
            }

            if ($sync_extend) {
                plugin_log('Iniciando sincronización del catalogo extendido', $logfile);
                $intcomexAPI->renewToken();
                $allProductsData = $intcomexAPI->getExtendedCatalog();
                if (is_array($allProductsData)) {
                    foreach ($allProductsData as $intcomexProduct) {
                        $importerResponse = SyncHelper::addExtendedProductInfo($intcomexProduct);
                        if ($importerResponse->isError()) {
                            plugin_log([
                                'error' => $importerResponse->getErrors(),
                                'intcomexProduct' => $intcomexProduct->localSku
                            ], $logfile);
                        }
                    }

                    unset($allProductsData);
                    unset($intcomexAPI);
                } else {
                    if ($allProductsData instanceof ClientException) {
                        plugin_log([
                            'error' => $allProductsData->getResponse()->getBody(),
                        ], $logfile);
                    }
                    plugin_log([
                        'error' => json_encode($allProductsData),
                    ], $logfile);
                }
            }

            if($removeNotIntcomexProducts) {
                $intcomexAPI = IntcomexAPI::getInstance();
                $query = new WP_Query([
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                ]);

                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $product_id = get_the_ID();
                        $product = wc_get_product($product_id);
                        if(!$intcomexAPI->getProduct($product->get_sku()) && $product->get_meta('bwi_intcomex_sku')) {
                            wp_trash_post($product_id);
                        }
                    }
                }
            }

            if ($sync_icecat) {
                plugin_log('Iniciando sincronización de datos desde icecat', $logfile);

                $iceCatAPI = new IceCatJsonAPI();
                $query = new WP_Query([
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                ]);

                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $product_id = get_the_ID();
                        $product = wc_get_product($product_id);

                        $brand = $product->get_attribute('pa_marca');
                        $mpn = $product->get_meta('_mpn');
                        if ($brand && $mpn) {
                            $rs = $iceCatAPI->getProductByMpn($brand, $mpn);
                            if($data = $iceCatAPI->getDataArray($rs)) {
                                SyncHelper::syncProductIceCat($product, $data);
                            }
                        } else {
                            plugin_log([
                                'error' => 'El producto no tiene marca o mpn asociado (mpn: ' . $mpn . ', marca: ' . $brand . ')',
                                'product' => $product->get_id()
                            ], $logfile);
                        }

                    }
                }
            }
        }
        catch (\Exception $exception) {
            plugin_log(['error' => $exception->getMessage()],$logfile);
        }

        plugin_log('Sincronización de productos finalizada',$logfile);
    }

}
