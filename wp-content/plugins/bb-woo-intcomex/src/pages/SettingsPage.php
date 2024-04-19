<?php

namespace Bigbuda\BbWooIntcomex\Pages;
/**
 * If this file is called directly, abort.
 *
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

class SettingsPage {

    public $options;

    public function __construct()
    {
        add_action('admin_menu',[$this,'settings_menu']);
        add_action( 'admin_init', [$this,'settings_init'] );

    }

    public function settings_menu() {

        add_menu_page(
            'Intcomex',
            'Intcomex',
            'manage_options',
            'bwi-settings',
            [$this,'settings_page']
        );

    }

    public function settings_init()
    {
        register_setting('bwi', 'bwi_options', [$this, 'bwi_validate_options']);

        add_settings_section(
            'bwi_section',
            __('Configuración API Intcomex', 'bwi'),
            [$this, 'bwi_section_cb'],
            'bwi-settings'
        );

        add_settings_field(
            'field_api_host',
            __('Host', 'bwi'),
            [$this, 'field_text'],
            'bwi-settings',
            'bwi_section',
            [
                'label_for' => 'field_api_host',
                'class' => 'row',
            ]
        );

        add_settings_field(
            'field_api_key',
            __('API Key', 'bwi'),
            [$this, 'field_text'],
            'bwi-settings',
            'bwi_section',
            [
                'label_for' => 'field_api_key',
                'class' => 'row',
            ]
        );

        add_settings_field(
            'field_api_secret',
            __('API Secret', 'bwi'),
            [$this, 'field_text'],
            'bwi-settings',
            'bwi_section',
            [
                'label_for' => 'field_api_secret',
                'class' => 'row',
            ]
        );


        add_settings_section(
            'bwi_section_redis',
            __('Configuraciones generales de la integración', 'bwi'),
            [$this, 'bwi_section_cb'],
            'bwi-settings'
        );

        add_settings_field(
            'field_redis_host',
            __('Margen de ganancia', 'bwi'),
            [$this, 'field_text'],
            'bwi-settings',
            'bwi_section_redis',
            [
                'label_for' => 'field_redis_host',
                'class' => 'row',
            ]
        );

    }


    public function field_text($args) {
        ?>
        <input
            type="<?= esc_attr($args['type'] ?? 'text') ?>"
            class="regular-text"
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            name="bwi_options[<?= esc_attr( $args['label_for'] ); ?>]"
            value='<?= $this->options[$args['label_for']] ?? "" ?>'
        >

        <?php
    }

    public function field_checkbox($args) {
        ?>
        <label for="<?php echo esc_attr( $args['label_for'] ); ?>">
            <input
                type="checkbox"
                class="regular-text"
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                name="bwi_options[<?= esc_attr( $args['label_for'] ); ?>]"
                value='yes'
                <?php echo isset($this->options[$args['label_for']]) ? 'checked' : '' ?>
            >
            Activar
        </label>
        <?php
    }

    public function bwi_section_cb() {

    }

    public function bwi_validate_options($input) {
        return $input;
    }

    public function settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $this->options = get_option( 'bwi_options' );

        settings_errors( 'bwi_messages' );
        ?>


        <div class="notice notice-warning is-dismissible">
            <p>Completa los campos a continuación para conectar Woocommerce con Intcomex.</p>
        </div>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'bwi' );
                do_settings_sections( 'bwi-settings' );
                submit_button( 'Guardar Configuración' );
                ?>
            </form>
        </div>
        <?php
    }


}
