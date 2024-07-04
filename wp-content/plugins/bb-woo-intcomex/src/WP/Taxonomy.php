<?php

namespace Bigbuda\BbWooIntcomex\WP;
/**
 * If this file is called directly, abort.
 *
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

class Taxonomy {

    public $options;

    public function __construct() {
        add_filter( 'term_updated_messages', [$this,'messages'] );
        add_action( 'init', [$this,'init'] );
    }

    /**
     * Registers the `marcas` taxonomy,
     * for use with 'post'.
     */
    function init() {

        register_taxonomy( 'marcas', [ 'product' ], [
            'hierarchical'          => false,
            'public'                => true,
            'show_in_nav_menus'     => true,
            'show_ui'               => true,
            'show_admin_column'     => false,
            'query_var'             => true,
            'rewrite'               => true,
            'capabilities'          => [
                'manage_terms' => 'edit_posts',
                'edit_terms'   => 'edit_posts',
                'delete_terms' => 'edit_posts',
                'assign_terms' => 'edit_posts',
            ],
            'labels'                => [
                'name'                       => __( 'Marca', 'lb' ),
                'singular_name'              => _x( 'Marca', 'taxonomy general name', 'lb' ),
                'search_items'               => __( 'Buscar Marca', 'lb' ),
                'popular_items'              => __( 'Marcas Populares', 'lb' ),
                'all_items'                  => __( 'Todas las Marcas', 'lb' ),
                'parent_item'                => __( 'Parent Marca', 'lb' ),
                'parent_item_colon'          => __( 'Parent Marca:', 'lb' ),
                'edit_item'                  => __( 'Editar Marca', 'lb' ),
                'update_item'                => __( 'Actualizar Marca', 'lb' ),
                'view_item'                  => __( 'Ver Marca', 'lb' ),
                'add_new_item'               => __( 'Añadir nueva Marca', 'lb' ),
                'new_item_name'              => __( 'Nueva Marca', 'lb' ),
                'separate_items_with_commas' => __( 'Separe las marcas con comas', 'lb' ),
                'add_or_remove_items'        => __( 'Añadir o eliminar marca', 'lb' ),
                'not_found'                  => __( 'No se encontraron marcas.', 'lb' ),
                'no_terms'                   => __( 'No hay marcas', 'lb' ),
                'menu_name'                  => __( 'Marcas', 'lb' ),
                'items_list_navigation'      => __( 'Marcas list navigation', 'lb' ),
                'items_list'                 => __( 'Marcas list', 'lb' ),
                'most_used'                  => _x( 'Mas Usadas', 'transportes', 'lb' ),
                'back_to_items'              => __( '&larr; Volver a Transportes', 'lb' ),
            ],
            'show_in_rest'          => false,
        ] );

    }

    /**
     * Sets the post updated messages for the `marcas` taxonomy.
     *
     * @param  array $messages Post updated messages.
     * @return array Messages for the `marcas` taxonomy.
     */
    function messages( $messages ) {

        $messages['marcas'] = [
            0 => '', // Unused. Messages start at index 1.
            1 => __( 'Marca añadida.', 'lb' ),
            2 => __( 'Marca eliminada.', 'lb' ),
            3 => __( 'Marca actualizada.', 'lb' ),
            4 => __( 'Marca no añadida.', 'lb' ),
            5 => __( 'Marca no actualizada.', 'lb' ),
            6 => __( 'Marca no eliminada.', 'lb' ),
        ];

        return $messages;
    }
}
