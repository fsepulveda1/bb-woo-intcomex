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
use Bigbuda\BbWooIntcomex\Woo\ProductDownloads;
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
    new ProductDownloads();

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

add_filter( 'posts_clauses', 'prioritize_products_with_images', 10, 2 );
function prioritize_products_with_images( $clauses, $query ) {
    // Asegurarse de que estamos en la consulta principal y en una consulta de productos WooCommerce
    if ( ! is_admin() && $query->is_main_query() && ( is_shop() || is_product_category() || is_product_tag() ) ) {

        global $wpdb;

        // Unir la tabla de postmeta para obtener los productos con y sin imágenes
        $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS pm ON {$wpdb->posts}.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'";

        // Ordenar primero por los que tienen imágenes (no nulos), luego por fecha
        $clauses['orderby'] = " pm.meta_value DESC, {$wpdb->posts}.post_date DESC";
    }

    return $clauses;
}

function getUsdValue() {

    $date = get_option('USD2CLP_date');
    if($date !== date('Y-m-d')) {
        $client = new Client([
            'timeout' => 1,
            'connect_timeout' => 3
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
            plugin_log('Error USD2CLP: '.$exception->getMessage());
        }
    }

    return (float) str_replace(['.',','],['','.'],get_option('USD2CLP', 0));
}

/**
 * Write an entry to a log file in the uploads directory.
 *
 * @since x.x.x
 *
 * @param mixed $entry String or array of the information to write to the log.
 * @param string $file Optional. The file basename for the .log file.
 * @param string $mode Optional. The type of write. See 'mode' at https://www.php.net/manual/en/function.fopen.php.
 * @return boolean|int Number of bytes written to the lof file, false otherwise.
 */
if ( ! function_exists( 'plugin_log' ) ) {
    function plugin_log( $entry, $file = 'plugin',$mode = 'a' ): false|int
    {
        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'];
        $log_dir    = $upload_dir . '/bwi-logs/';
        if ( is_array( $entry ) ) {
            $entry = json_encode( $entry );
        }
        if(!file_exists($log_dir)) {
            mkdir($log_dir);
        }
        $file  = $log_dir . $file . '.log';
        $file  = fopen( $file, $mode );
        $bytes = fwrite( $file, current_time( 'mysql' ) . "::" . $entry . "\n" );
        fclose( $file );
        return $bytes;
    }
}