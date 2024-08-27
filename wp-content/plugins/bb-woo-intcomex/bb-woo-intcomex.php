<?php
/**
 * Plugin Name:     Bigbuda Woo Intcomex
 * Plugin URI:      https://www.bigbuda.cl
 * Description:     Integración de woocommerce con Intcomex
 * Author:          Bigbuda
 * Author URI:      https://www.bigbuda.cl
 * Text Domain:     bwi
 * Domain Path:     /bwi
 * Version:         0.1.0
 *
 * @package         BB_Woo_Intcomex
 */

use Bigbuda\BbWooIntcomex\CronJobs\CronJobs;
use Bigbuda\BbWooIntcomex\Pages\CronSettingsPage;
use Bigbuda\BbWooIntcomex\Pages\ImportersPage;
use Bigbuda\BbWooIntcomex\Pages\OrdersPage;
use Bigbuda\BbWooIntcomex\Pages\SettingsPage;
use Bigbuda\BbWooIntcomex\Shortcodes\ProductDescription;
use Bigbuda\BbWooIntcomex\Woo\Attributes;
use Bigbuda\BbWooIntcomex\Woo\OrderMeta;
use Bigbuda\BbWooIntcomex\Woo\ProductTab;
use Bigbuda\BbWooIntcomex\Woo\Shopping;
use Bigbuda\BbWooIntcomex\WP\Taxonomy;
use GuzzleHttp\Client;

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
    require __DIR__ . '/vendor/autoload.php';
}

define( 'BWI_DIR', dirname( __FILE__ ));
define( 'BWI_URL', plugin_dir_url( __FILE__ ));

global $bwi_db_version;
$bwi_db_version = '1.0';

add_action( 'plugins_loaded', 'bwi_initiate_plugin' );
function bwi_initiate_plugin()
{
    //Plugin pages
    new SettingsPage();
    new CronSettingsPage();
    new ImportersPage();
    new OrdersPage();

    //WordPress hooks
    new CronJobs();

    //Woocommerce hooks
    new Shopping();
    new ProductTab();
    new Attributes();
    new OrderMeta();

    //Shortcodes
    new ProductDescription();

    add_action('admin_enqueue_scripts', 'bwi_admin_scripts');
}

function bwi_admin_scripts() {
    if(isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'],'page=bwi-')) {
        wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css');
        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js');

        wp_enqueue_style('bwi-main', BWI_URL . '/assets/css/main.css');
        wp_enqueue_script('bwi-ajax', BWI_URL . '/assets/js/main.js', array(), "1.0", true);
        wp_localize_script('bwi-ajax', 'bwi_ajax_values',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('_ajax_nonce'),
            )
        );
    }
}

add_action( 'woocommerce_product_query', 'prioritize_products_with_images' );

function prioritize_products_with_images( $query ) {
    if ( ! is_admin() && $query->is_main_query() ) {
        $query->set( 'meta_query', array(
            array(
                'key'     => '_thumbnail_id',
                'compare' => 'EXISTS',
            ),
        ) );
        $query->set( 'orderby', array(
            'meta_value' => 'DESC',
            'date'       => 'DESC'
        ) );
    }
}

function getUsdValue() {

    $date = get_option('USD2CLP_date', date('Y-m-d'));
    if($date !== date('Y-m-d')) {
        $client = new Client([
            'timeout' => 1,
            'connect_timeout' => 1
        ]);
        try {
            $response = $client->get('https://api.sbif.cl/api-sbifv3/recursos_api/dolar?apikey=55f9b3ad028facf7dce500d439c00cbbcf1234a7&formato=json');
            $json = json_decode($response->getBody()->getContents());
            if ($json) {
                if(!empty($json->Dolares[0]->Valor)) {
                    update_option('USD2CLP', $json->Dolares[0]->Valor);
                }
                update_option('USD2CLP_date', $json->Dolares[0]->Fecha ?? date('Y-m-d'));
            }
        }
        catch (Exception $exception){
            //TODO send error to log
        }
    }

    return (float) str_replace(['.',','],['','.'],get_option('USD2CLP', 0));
}
