<?php

namespace Bigbuda\BbWooIntcomex\Woo;

/**
 * If this file is called directly, abort.
 *
 */
if ( !defined( 'WPINC' ) ) {
    die;
}


class ProductTab
{
    private string $tab_intcomex = 'bwi_tab_intcomex';

    /**
     * Construct.
     *
     * @since 1.1.0
     */
    public function __construct()
    {
        add_filter('woocommerce_product_data_tabs', array($this, 'create_custom_tab_wc_product'), 10, 1); // Since WC 3.0.2
        add_action('woocommerce_product_data_panels', array($this, 'custom_product_tab_content'), 10, 1); // Since WC 3.0.2
        add_action('save_post', array($this, 'tab_product_save'), 10, 3);
    }

    /**
     * Creates the Stock Locations tab in WC Product.
     *
     * @since 1.0.0
     * @return array
     */
    public function create_custom_tab_wc_product($original_tabs) // Create the custom tabs for this plugin
    {
        // Define custom tabs
        $new_tab[$this->tab_intcomex] = array(
            'label' 	=> __( 'Intcomex', 'bwi' ),
            'target'    => $this->tab_intcomex,
            'class'     => array( 'show_if_simple', 'show_if_variable' ),
        );

        // Define tab positions
        $insert_at_position = 2;
        $tabs = array_slice( $original_tabs, 0, $insert_at_position, true );
        $tabs = array_merge( $tabs, $new_tab );
        $tabs = array_merge( $tabs, array_slice( $original_tabs, $insert_at_position, null, true ) );

        return $tabs;
    }

    /**
     * Add data to the Quantity rules tab in WC Product.
     *
     * @since 1.0.0
     * @return void
     */
    public function custom_product_tab_content($array)
    {
        // Get the product ID
        $product_id = get_the_ID();

        // Get the product object
        $product = wc_get_product( $product_id );

        // Populate the tab content
        echo '<div id="' . $this->tab_intcomex . '" class="panel woocommerce_options_panel">';

        woocommerce_wp_text_input( array(
            'id'            => '_mpn',
            'label'         => 'MPN',
            'description'   => __( 'MPN del producto desde intcomex.', 'bwi' ),
            'desc_tip'      => true,
            'class'         => 'woocommerce',
            'type'          => 'text',
            'value'         => get_post_meta($product_id, '_mpn', true),
            'custom_attributes' => [
                'readonly' => 'readonly'
            ]
        ) );

        woocommerce_wp_text_input( array(
            'id'            => '_intcomex_price_origin',
            'label'         => 'Precio en Intcomex',
            'description'   => __( 'Precio del producto desde intcomex.', 'bwi' ),
            'desc_tip'      => true,
            'class'         => 'woocommerce',
            'type'          => 'number',
            'value'         => get_post_meta($product_id, '_intcomex_price_origin', true),
            'custom_attributes' => [
                'readonly' => 'readonly'
            ]
        ) );

        woocommerce_wp_text_input( array(
            'id'            => '_intcomex_price_clp',
            'label'         => 'Precio en Intcomex en pesos',
            'description'   => __( 'Precio del producto desde intcomex.', 'bwi' ),
            'desc_tip'      => true,
            'class'         => 'woocommerce',
            'type'          => 'number',
            'value'         => get_post_meta($product_id, '_intcomex_price_clp', true),
            'custom_attributes' => [
                'readonly' => 'readonly'
            ]
        ) );

        woocommerce_wp_text_input( array(
            'id'            => '_intcomex_fee_clp',
            'label'         => 'Margen de ganancia en pesos',
            'description'   => __( 'Margen de ganancia aplicado al producto.', 'bwi' ),
            'desc_tip'      => true,
            'class'         => 'woocommerce',
            'type'          => 'number',
            'value'         => get_post_meta($product_id, '_intcomex_fee_clp', true),
            'custom_attributes' => [
                'readonly' => 'readonly'
            ]
        ) );


        woocommerce_wp_textarea_input( array(
            'id'            => '_custom_attributes',
            'label'         => __('Atributos personalizados','bmi'),
            'desc_tip'      => true,
            'class'         => 'woocommerce',
            'value'         => get_post_meta($product_id, '_custom_attributes', true),
        ) );

        echo '</div>';
    }

    /**
     * Saves data from custom Stock Locations tab upon WC Product save.
     *
     * @since 1.0.0
     * @return int|void
     */
    public function tab_product_save($post_id, $post, $update)
    {

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
            return $post_id;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        if ( ! current_user_can( 'edit_product', $post_id ) )
            return $post_id;


        if(isset($_POST['bmi_min_quantity'])) {
            update_post_meta($post_id, 'bmi_min_quantity', $_POST['bmi_min_quantity']);
        }
        if(isset($_POST['bmi_max_quantity'])) {
            update_post_meta($post_id, 'bmi_max_quantity', $_POST['bmi_max_quantity']);
        }
        if(isset($_POST['bmi_quantity_format'])) {
            update_post_meta($post_id, 'bmi_quantity_format', $_POST['bmi_quantity_format']);
        }

        // Check if product has variations
        if( isset($product_variations) && ( !empty($product_variations) || ($product_variations !== 0) ) ) {

            // Interate over variations
            foreach( $product_variations as $variation ) {
                update_post_meta($variation['variation_id'],'bmi_min_quantity', $_POST['bmi_min_quantity']);
                update_post_meta($variation['variation_id'],'bmi_max_quantity', $_POST['bmi_max_quantity']);
                update_post_meta($variation['variation_id'],'bmi_quantity_format', $_POST['bmi_quantity_format']);
            }
        }
    }
}
