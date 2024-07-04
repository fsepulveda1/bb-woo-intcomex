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

use Bigbuda\BbWooIntcomex\Pages\ImportersPage;
use Bigbuda\BbWooIntcomex\Pages\SettingsPage;
use Bigbuda\BbWooIntcomex\WP\Taxonomy;

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
    new SettingsPage();
    new ImportersPage();
    new Taxonomy();
}

function priorizar_productos_con_imagenes($query) {
    if (!is_admin() && $query->is_main_query() && (is_shop() || is_product_category() || is_product_tag())) {
        $meta_query = $query->get('meta_query');

        if (!is_array($meta_query)) {
            $meta_query = array();
        }

        $meta_query['meta_thumbnail_id'] = array(
            'key'     => '_thumbnail_id',
            'compare' => 'EXISTS',
            'type' => 'NUMERIC'
        );

        $query->set('meta_query', $meta_query);
        $query->set('orderby', array(
            'meta_thumbnail_id' => 'DESC',
        ));

        return $query;
    }
}
add_action('pre_get_posts', 'priorizar_productos_con_imagenes');