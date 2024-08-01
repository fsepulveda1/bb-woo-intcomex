<?php

namespace Bigbuda\BbWooIntcomex\Woo;
/**
 * If this file is called directly, abort.
 *
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

class Attributes {

    public $options;

    public function __construct() {
        add_action( 'init', [$this,'init'] );
    }

    function init() {

        $attribute = array(
            'name'         => 'Marca',
            'slug'         => 'pa_marca',
            'type'         => 'select',
            'order_by'     => 'menu_order',
            'has_archives' => false,
        );

        if (!taxonomy_exists('pa_marca')) {
            wc_create_attribute($attribute);
        }
    }

}
