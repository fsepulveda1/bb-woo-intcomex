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

use Bigbuda\BbWooIntcomex\Pages\SettingsPage;

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
}