<?php
/**
 * Enqueue script and styles for child theme
 */
function woodmart_child_enqueue_styles() {
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), woodmart_get_theme_info( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010 );

// Añadir carga custom.js
add_action('wp_footer', 'custom_register_styles',1);
function custom_register_styles() {
   wp_enqueue_script( 'custom-js', get_stylesheet_directory_uri().'/js/custom.js', 'jquery' , '1.0', true);
}

// Armar post relacionados en base a categorías
function woodmart_get_related_posts_args( $post_id ) {
   $cats = get_the_category();
   $args = array();
   if ( $cats ) {
      $args = array(
         'cat'               => $cats[0]->term_id,
         'post__not_in'          => array( $post_id ),
         'showposts'             => 12,
         'ignore_sticky_posts'   => 1
      );
   }
   return $args;
}

// Ajustar opciones para ordenamiento de productos
add_filter( 'woocommerce_catalog_orderby', 'bb_remove_sorting_option_woocommerce_shop' );
function bb_remove_sorting_option_woocommerce_shop( $options ) {
   $options['rating'] = 'Ordenar por lo mejor valorado';  
   $options['menu_order'] = 'Ordenar alfabéticamente'; 
   $options['date'] = 'Ordenar por lo más reciente';   
   $options['popularity'] = 'Ordenar por lo más comprado';
   $options['price'] = 'Ordenar por los precios más bajos';
   $options['price-desc'] = 'Ordenar por los precios más altos';
   return $options;
}

// Página de opciones acf
add_action('acf/init', 'my_acf_op_init');
function my_acf_op_init() {

   // Check function exists.
   if( function_exists('acf_add_options_page') ) {

      // Register options page.
      $option_page = acf_add_options_page(array(
         'page_title'    => __('Opciones Extras'),
         'menu_title'    => __('Opciones Extras'),
         'menu_slug'     => 'theme-general-settings',
         'capability'    => 'edit_posts',
         'redirect'      => false
      ));
   }
}

// Botón de WhatsApp
add_action('wp_footer', 'bb_whatsapp_btn');
function bb_whatsapp_btn() {
   $btnWsp = get_field('acf_btn_whatsapp', 'option');
   $txtWsp = get_field('acf_number_whatsapp', 'option');
   $svgWsp = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="77" height="77" viewBox="0 0 77 77"> <defs> <clipPath id="clip-path"> <path id="Trazado_33" data-name="Trazado 33" d="M6.419,26.812l-.387-.637a14.981,14.981,0,1,1,5.129,5.084l-.631-.377-5.6,1.5ZM18.79,0A18.4,18.4,0,0,0,2.675,27.33L0,37.309l10.031-2.688A18.421,18.421,0,1,0,18.79,0Z" fill="#fafafa"/> </clipPath> <clipPath id="clip-path-2"> <path id="Trazado_34" data-name="Trazado 34" d="M12.2,11.278a4.647,4.647,0,0,0-1.61,3.679,5.225,5.225,0,0,0,.145,1.168,9.681,9.681,0,0,0,1.119,2.581,20.274,20.274,0,0,0,1.169,1.778,18.049,18.049,0,0,0,5.071,4.71,15.406,15.406,0,0,0,3.162,1.5,7.023,7.023,0,0,0,3.681.581,4.456,4.456,0,0,0,3.339-2.483,2.153,2.153,0,0,0,.155-1.267c-.19-.873-1.372-1.391-2.078-1.814a3.1,3.1,0,0,0-2.639-.594c-.685.281-1.123,1.353-1.567,1.9a.66.66,0,0,1-.851.185,11.956,11.956,0,0,1-5.964-5.111.729.729,0,0,1,.092-1,4.053,4.053,0,0,0,1.084-1.759,3.849,3.849,0,0,0-.485-2.082,4.939,4.939,0,0,0-1.538-2.317,1.649,1.649,0,0,0-.819-.206,2.394,2.394,0,0,0-1.469.547" transform="translate(-10.595 -10.731)" fill="#fafafa"/> </clipPath> </defs> <g id="Grupo_1540" data-name="Grupo 1540" transform="translate(-1351.139 -4610.585)"> <g id="Grupo_98" data-name="Grupo 98" transform="translate(1350.736 4610.182)"> <circle id="Elipse_6" data-name="Elipse 6" cx="38.5" cy="38.5" r="38.5" transform="translate(0.403 0.403)" fill="#33c61e"/> <g id="Grupo_97" data-name="Grupo 97" transform="translate(20.349 20.349)"> <g id="Grupo_94" data-name="Grupo 94"> <g id="Grupo_93" data-name="Grupo 93" clip-path="url(#clip-path)"> <rect id="Rectángulo_59" data-name="Rectángulo 59" width="52.699" height="52.699" transform="translate(-18.655 18.654) rotate(-45)" fill="#fafafa"/> </g> </g> <g id="Grupo_96" data-name="Grupo 96" transform="translate(9.88 10.008)"> <g id="Grupo_95" data-name="Grupo 95" clip-path="url(#clip-path-2)"> <rect id="Rectángulo_60" data-name="Rectángulo 60" width="24.573" height="24.573" transform="translate(-8.416 8.4) rotate(-45)" fill="#fafafa"/> </g> </g> </g> </g> </g> </svg> ';
   if ($btnWsp) {
      echo '<div class="bb-btn_wsp"><a href="https://wa.me/' . $txtWsp . '" target="_blank">' . $svgWsp . '</a></div>';
   }
}

// Ajustar campos de dirección en checkout y mis direcciones
add_filter( 'woocommerce_default_address_fields' , 'bb_modify_address_fields', 9999 );
function bb_modify_address_fields( $fields ) {
    unset( $fields['city'] );
    return $fields;
}

// Quitar calculadora en carrito
add_action('template_redirect', function() {
    if ( is_cart() ) {
        add_filter('woocommerce_product_needs_shipping', function() {
            return false;
        });
    }
});

//Añadir campos al formulario de registro
// 1. Añadir campos
add_action( 'woocommerce_register_form_start', 'bb_add_name_woo_account_registration' );
function bb_add_name_woo_account_registration() {
    ?>
    <p class="form-row form-row-first">
    <label for="reg_billing_first_name"><?php _e( 'First name', 'woocommerce' ); ?> <span class="required">*</span></label>
    <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
    </p>
    <p class="form-row form-row-last">
    <label for="reg_billing_last_name"><?php _e( 'Last name', 'woocommerce' ); ?> <span class="required">*</span></label>
    <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" />
    </p>
    <div class="clear"></div>
    <?php
}
  
// 2. Validar campos
add_filter( 'woocommerce_registration_errors', 'bb_validate_name_fields', 10, 3 );
function bb_validate_name_fields( $errors, $username, $email ) {
    if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
        $errors->add( 'billing_first_name_error', __( '<strong>Error</strong>: First name is required!', 'woocommerce' ) );
    }
    if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
        $errors->add( 'billing_last_name_error', __( '<strong>Error</strong>: Last name is required!.', 'woocommerce' ) );
    }
    return $errors;
}
  
// 3. Guardar campos
add_action( 'woocommerce_created_customer', 'bb_save_name_fields' );
function bb_save_name_fields( $customer_id ) {
    if ( isset( $_POST['billing_first_name'] ) ) {
        update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
        update_user_meta( $customer_id, 'first_name', sanitize_text_field($_POST['billing_first_name']) );
    }
    if ( isset( $_POST['billing_last_name'] ) ) {
        update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
        update_user_meta( $customer_id, 'last_name', sanitize_text_field($_POST['billing_last_name']) );
    }
}

// Mostrar solo categorías principales en archive de productos
function woodmart_product_categories() {
    global $product;

    if ( ! woodmart_get_opt( 'categories_under_title' ) ) {
        return;
    }

    $terms = get_the_terms( $product->get_id(), 'product_cat' );

    if ( ! $terms ) {
        return;
    }

    $terms_array = array();
    $parent      = array();
    $child       = array();
    $links       = array();

    foreach ( $terms as $term ) {
        $terms_array[ $term->term_id ] = $term;

        if ( ! $term->parent ) {
            $parent[ $term->term_id ] = $term->name;
        }
    }

    $terms = $parent;

    foreach ( $terms as $key => $value ) {
        $links[] = '<a href="' . esc_url( get_term_link( $key ) ) . '" rel="tag">' . esc_html( $value ) . '</a>';
    }

    ?>
    <div class="wd-product-cats<?php echo woodmart_get_old_classes( ' woodmart-product-cats' ); ?>">
        <?php echo implode( ', ', $links ); // phpcs:ignore ?>
    </div>
    <?php
}

// Manejar texto de stock en plural o singular dependiendo de la cantidad
function modificar_texto_stock( $availability_text, $product ) {
    // Verificamos si el producto tiene stock y modificamos el mensaje
    if ( $product->is_in_stock() ) {
        $stock_quantity = $product->get_stock_quantity();

        // Si solo hay una unidad, mostramos un mensaje en singular
        if ( $stock_quantity == 1 ) {
            $availability_text = '¡Solo queda 1 unidad disponible!';
        } else {
            // Si hay más de una unidad, mostramos un mensaje en plural
            $availability_text = 'Quedan ' . $stock_quantity . ' unidades disponibles. ¡Apresúrate!';
        }
    }

    // Puedes agregar otros casos si el producto no tiene stock o está agotado
    return $availability_text;
}
add_filter( 'woocommerce_get_availability_text', 'modificar_texto_stock', 10, 2 );

// Añadir un recargo por pagar con Webpay Plus
/*function custom_payment_gateway_surcharge( $cart ) {
    // Asegurarse de que estamos en el checkout
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    // Obtener el método de pago seleccionado
    $chosen_payment_method = WC()->session->get( 'chosen_payment_method' );

    // Comprobar si el método de pago es "transbank_webpay_plus_rest"
    if ( 'transbank_webpay_plus_rest' === $chosen_payment_method ) {
        // Obtener el porcentaje de recargo desde el campo ACF
        $surcharge_percentage = get_field( 'acf_webpay_fee', 'option' ); // Asegúrate de que el campo esté definido

        // Validar que el porcentaje sea numérico y no vacío
        if ( is_numeric( $surcharge_percentage ) && $surcharge_percentage > 0 ) {
            // Calcular el recargo
            $surcharge = $cart->cart_contents_total * ($surcharge_percentage / 100);

            // Añadir el recargo al total del carrito
            $cart->add_fee( __( 'Recargo por pago con Webpay', 'woocommerce' ), $surcharge );
        }
    }
}
add_action( 'woocommerce_cart_calculate_fees', 'custom_payment_gateway_surcharge' );
*/

add_action('woocommerce_cart_calculate_fees', 'apply_discount_for_bank_transfer');
function apply_discount_for_bank_transfer($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    // Verifica si el método de pago seleccionado es transferencia bancaria
    if (isset(WC()->session->chosen_payment_method) && WC()->session->chosen_payment_method === 'bacs') {
        $options = get_option( 'bwi_options' );
        $discount = ceil($cart->get_subtotal() * ($options['field_payment_method_margin'] / 100));
        $cart->add_fee(__('Descuento por Transferencia Bancaria ('.$options['field_payment_method_margin'].'%)', 'text-domain'), -$discount);
    }
}


function enqueue_custom_checkout_script() {
    if ( is_checkout() ) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Cuando se cambie el método de pago
                $('body').on('change', 'input[name="payment_method"]', function() {
                    // Forzar la actualización del carrito
                    $('body').trigger('update_checkout');
                });
            });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'enqueue_custom_checkout_script' );

// Quitar símbolo de resta en etiqueta de descuento
function woodmart_product_label() {
   global $product;

   $output = array();

   $product_attributes = woodmart_get_product_attributes_label();
   $percentage_label   = woodmart_get_opt( 'percentage_label' );

   if ( 'small' === woodmart_loop_prop( 'product_hover' ) ) {
      return;
   }

   if ( $product->is_on_sale() ) {

      $percentage = '';

      if ( $product->get_type() == 'variable' && $percentage_label ) {

         $available_variations = $product->get_variation_prices();
         $max_percentage       = 0;

         foreach ( $available_variations['regular_price'] as $key => $regular_price ) {
            $sale_price = $available_variations['sale_price'][ $key ];

            if ( $sale_price < $regular_price ) {
               $percentage = round( ( ( (float) $regular_price - (float) $sale_price ) / (float) $regular_price ) * 100 );

               if ( $percentage > $max_percentage ) {
                  $max_percentage = $percentage;
               }
            }
         }

         $percentage = $max_percentage;
      } elseif ( ( $product->get_type() == 'simple' || $product->get_type() == 'external' || $product->get_type() == 'variation' ) && $percentage_label ) {
         $percentage = round( ( ( (float) $product->get_regular_price() - (float) $product->get_sale_price() ) / (float) $product->get_regular_price() ) * 100 );
      }

      if ( $percentage ) {
         $output[] = '<span class="onsale product-label">' . sprintf( _x( '%d%%', 'sale percentage', 'woodmart' ), $percentage ) . '</span>';
      } else {
         $output[] = '<span class="onsale product-label">' . esc_html__( 'Sale', 'woodmart' ) . '</span>';
      }
   }

   if ( ! $product->is_in_stock() && 'thumbnail' === woodmart_get_opt( 'stock_status_position', 'thumbnail' ) ) {
      $output[] = '<span class="out-of-stock product-label">' . esc_html__( 'Sold out', 'woodmart' ) . '</span>';
   }

   if ( $product->is_featured() && woodmart_get_opt( 'hot_label' ) ) {
      $output[] = '<span class="featured product-label">' . esc_html__( 'Hot', 'woodmart' ) . '</span>';
   }

   if ( woodmart_get_opt( 'new_label' ) && woodmart_is_new_label_needed( get_the_ID() ) ) {
      $output[] = '<span class="new product-label">' . esc_html__( 'New', 'woodmart' ) . '</span>';
   }

   if ( $product_attributes ) {
      foreach ( $product_attributes as $attribute ) {
         $output[] = $attribute;
      }
   }

   $output = apply_filters( 'woodmart_product_label_output', $output );

   if ( $output ) {
      woodmart_enqueue_inline_style( 'woo-mod-product-labels' );
      $shape = woodmart_get_opt( 'label_shape' );

      if ( 'rectangular' === $shape ) {
         woodmart_enqueue_inline_style( 'woo-mod-product-labels-rect' );
      }

      if ( 'rounded' === $shape ) {
         woodmart_enqueue_inline_style( 'woo-mod-product-labels-round' );
      }

      echo '<div class="product-labels labels-' . $shape . '">' . implode( '', $output ) . '</div>';
   }
}
add_filter( 'woocommerce_sale_flash', 'woodmart_product_label', 10 );

// Traducir sin contexto 
function bb_translate_strings( $translated_text, $text, $domain ) {
   switch ( $translated_text ) {
      case 'Load more posts' :
         $translated_text = __( 'Cargar más', 'woodmart' );
         break;
      case 'Back to list' :
         $translated_text = __( 'Volver al blog', 'woodmart' );
         break;
      case 'Find a %s' :
         $translated_text = __( 'Buscar %s', 'woodmart' );
         break;
      case 'Loading...' :
         $translated_text = __( 'Cargando...', 'woodmart' );
         break;
      case 'Hello, %s' :
         $translated_text = __( 'Hola, %s', 'woodmart' );
         break;   
      case 'Back to menu' :
         $translated_text = __( 'Volver atrás', 'woodmart' );
         break;   
      case 'All' :
         $translated_text = __( 'Todos', 'woodmart' );
         break;  
      case 'Categories' :
         $translated_text = __( 'Categorías', 'woodmart' );
         break;  
      case 'Sort by' :
         $translated_text = __( 'Ordenar por', 'woodmart' );
         break;   
      case 'Filter' :
         $translated_text = __( 'Filtrar', 'woodmart' );
         break;  
      case 'Posted by' :
         $translated_text = __( 'Subido por:', 'woodmart' );
         break;   
      case 'Login / Register' :
         $translated_text = __( 'Acceder / Registrarse', 'woodmart' );
         break;   
      case 'Share: ' :
         $translated_text = __( 'Compartir en: ', 'woodmart' );
         break;   
      case 'Your products wishlist' :
         $translated_text = __( 'Tus productos favoritos', 'woodmart' );
         break;   
      case 'Remove' :
         $translated_text = __( 'Quitar', 'woodmart' );
         break;        
      case 'Description' :
         $translated_text = __( 'Descripción', 'woodmart' );
         break;  
      case 'Availability' :
         $translated_text = __( 'Disponibilidad', 'woodmart' );
         break;  
      case 'My Account' :
         $translated_text = __( 'Mi cuenta', 'woodmart' );
         break;  
      case 'This wishlist is empty.' :
         $translated_text = __( 'Tu listado está vacío', 'woodmart' );
         break;   
      case 'Compare list is empty.' :
         $translated_text = __( 'Tu listado está vacío', 'woodmart' );
         break;
      case 'Return to shop' :
         $translated_text = __( 'Volver a tienda', 'woodmart' );
         break;
      case 'Return To Shop' :
         $translated_text = __( 'Volver a tienda', 'woocommerce' );
         break;
      case 'Show sidebar' :
         $translated_text = __( 'Filtros', 'woocommerce' );
         break;
      case 'Menu' :
         $translated_text = __( 'Menú', 'woodmart' );
         break;
      case 'Sin existencias' :
         $translated_text = __( 'Agotado', 'woocommerce' );
         break;
      case 'In stock' :
          $translated_text = __( 'En stock', 'woodmart' );
          break;
      case '%s in stock' :
           $translated_text = __( 'En stock', 'woodmart' );
           break;          
      case 'Out of stock' :
          $translated_text = __( 'Agotado', 'woodmart' );
          break;
      case 'Quick view' :
            $translated_text = __( 'Vista rápida', 'woodmart' );
            break;
      case 'View details' :
            $translated_text = __( 'Ver detalles', 'woodmart' );
            break;
      case 'Compare' :
            $translated_text = __( 'Comparar', 'woodmart' );
            break;
      case 'Add to wishlist' :
            $translated_text = __( 'Añadir a favoritos', 'woodmart' );
            break;
      case 'New' :
            $translated_text = __( 'Nuevo', 'woodmart' );
            break;
      case 'Shop' :
            $translated_text = __( 'Tienda', 'woodmart' );
            break;
      case 'Wishlist' :
            $translated_text = __( 'Favoritos', 'woodmart' );
            break;
      case 'My Wishlist' :
            $translated_text = __( 'Mis Favoritos', 'woodmart' );
            break;
      case 'Search' :
            $translated_text = __( 'Buscar', 'woodmart' );
            break;
      case 'No products found' :
         $translated_text = __( 'No se encontraron productos', 'woodmart' );
         break;
      case 'View all results' :
         $translated_text = __( 'Ver todo', 'woodmart' );
         break;    
      case 'Search Results for: ' :
         $translated_text = __( 'Resultados para: ', 'woodmart' );
         break;    
      case 'Cart' :
            $translated_text = __( 'Carrito', 'woodmart' );
            break;
      case 'My account' :
            $translated_text = __( 'Mi cuenta', 'woodmart' );
            break;
      case 'Close' :
            $translated_text = __( 'Cerrar', 'woodmart' );
            break;
      case 'Continue reading' :
            $translated_text = __( 'Ir a leer >>', 'woodmart' );
            break;    
      case 'Sidebar' :
            $translated_text = __( 'Filtros', 'woodmart' );
            break;  
      case 'Newer' :
            $translated_text = __( 'Nuevo', 'woodmart' );
            break;  
      case 'Older' :
            $translated_text = __( 'Antiguo', 'woodmart' );
            break;  
      case 'Related Posts' :
            $translated_text = __( 'Entradas similares', 'woodmart' );
            break;    
      case 'Show' :
            $translated_text = __( 'Mostrando', 'woodmart' );
            break; 
      case 'Clear filters' :
            $translated_text = __( 'Limpiar filtros', 'woodmart' );
            break;    
      case 'Share:' :
            $translated_text = __( 'Compartir en:', 'woodmart' );
            break;  
      case 'Logout' :
            $translated_text = __( 'Cerrar sesión', 'woodmart' );
            break;      
      case 'Shopping cart' :
            $translated_text = __( 'Carrito de compras', 'woodmart' );
            break;  
      case 'Checkout' :
            $translated_text = __( 'Ir a pagar', 'woodmart' );
            break;       
      case 'Order complete' :
            $translated_text = __( 'Compra completada', 'woodmart' );
            break;     
      case 'Número de la casa y nombre de la calle' :
            $translated_text = __( 'Nombre de la calle y número de la casa ', 'woodmart' );
            break;              
      case 'Apartamento, habitación, etc. (opcional)' :
            $translated_text = __( 'Departamento, oficina, etc. (opcional)', 'woodmart' );
            break;   
      case 'Sign in' :
            $translated_text = __( 'Iniciar sesión', 'woodmart' );
            break;  
      case 'Create an Account' :
            $translated_text = __( 'Registrarse', 'woodmart' );
            break;  
      case 'Password' :
            $translated_text = __( 'Contraseña', 'woodmart' );
            break;  
      case 'Remember me' :
            $translated_text = __( 'Recuérdame', 'woodmart' );
            break;  
      case 'Lost your password?' :
            $translated_text = __( 'Olvidaste tu contraseña?', 'woodmart' );
            break;     
      case 'Log in' :
            $translated_text = __( 'Acceder', 'woodmart' );
            break;
       case 'Back to %s' :
           $translated_text = __( 'Volver a %s', 'woodmart' );
           break;
   }
   return $translated_text;
}
add_filter( 'gettext', 'bb_translate_strings', 20, 3 );

// Traducciones con contexto 
add_filter( 'gettext_with_context', 'bb_gettext_with_context', 10, 4 );
function bb_gettext_with_context( $translation, $text, $context, $domain ) {
    if ( 'woodmart' === $domain ) {
        if ( 'My account' === $text && 'toolbar' === $context ) {
            $translation = 'Mi cuenta';
        }
        if ( 'Wishlist' === $text && 'toolbar' === $context ) {
            $translation = 'Favoritos';
        }
        if ( 'Search for products' === $text && 'submit button' === $context ) {
            $translation = 'Buscar productos...';
        }
        if ( 'No products found' === $text && 'submit button' === $context ) {
            $translation = '';
        }
    }
    return $translation;
}