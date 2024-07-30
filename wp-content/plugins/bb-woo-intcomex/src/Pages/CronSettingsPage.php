<?php

namespace Bigbuda\BbWooIntcomex\Pages;
use Bigbuda\BbWooIntcomex\CronJobs\CronJobInterface;
use Bigbuda\BbWooIntcomex\CronJobs\CronJobs;

/**
 * If this file is called directly, abort.
 *
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

class CronSettingsPage {

    public $options;

    public function __construct()
    {
        add_action('admin_menu',[$this,'settings_menu']);
        add_action( 'admin_init', [$this,'settings_init'] );

    }

    public function settings_menu() {

        add_submenu_page(
            'bwi-settings',
            'Cron',
            'Cron',
            'manage_options',
            'bwi-cron-settings',
            [$this,'settings_page']
        );

    }

    public function settings_init()
    {
        register_setting('bwi_cron', 'bwi_cron_options', [$this, 'bwi_validate_options']);

        /** @var CronJobInterface $job */
        foreach(CronJobs::getJobs() as $key => $job) {
            add_settings_section(
                'bwi_cron_section_'.$key,
                __($job::getNiceName(), 'bwi'),
                [$this, 'bwi_section_cb'],
                'bwi-cron-settings'
            );

            add_settings_field(
                $job::getCronActionName().'_start_time',
                __('Hora de ejecución', 'bwi'),
                [$this, 'field_text'],
                'bwi-cron-settings',
                'bwi_cron_section_'.$key,
                [
                    'type' => 'time',
                    'label_for' => $job::getCronActionName().'_start_time',
                    'class' => 'row',
                ]
            );

            add_settings_field(
                $job::getCronActionName().'_interval',
                __('Periodicidad', 'bwi'),
                [$this, 'field_select'],
                'bwi-cron-settings',
                'bwi_cron_section_'.$key,
                [
                    'label_for' => $job::getCronActionName().'_interval',
                    'class' => 'row',
                    'placeholder' => 'Seleccione una opción',
                    'options' => [
                        'hourly' => 'Cada 1 hora',
                        'hourly_3' => 'Cada 3 horas',
                        'hourly_6' => 'Cada 6 horas',
                        'hourly_12' => 'Cada 12 horas',
                        'daily' => 'Diariamente',
                        'weekly' => 'Semanalmente',
                    ]
                ]
            );

        }

    }

    public function field_text($args) {
        ?>
        <input
                type="<?= esc_attr($args['type'] ?? 'text') ?>"
            <?= isset($args['min'])? 'min="'.esc_attr($args['min']).'"' : ""; ?>
            <?= isset($args['max']) ? 'max="'.esc_attr($args['max']).'"' : ""; ?>
                class="regular-text"
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                name="bwi_cron_options[<?= esc_attr( $args['label_for'] ); ?>]"
                value='<?= $this->options[$args['label_for']] ?? "" ?>'
        >

        <?php
    }

    public function field_select($args) {
        ?>
        <select
                class=""
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                name="bwi_cron_options[<?= esc_attr( $args['label_for'] ); ?>]"
        >
            <option value=""><?php echo esc_attr( $args['placeholder'] ); ?></option>
            <?php foreach($args['options'] as $key => $option): ?>
                <option value="<?= $key ?>" <?= isset($this->options[$args['label_for']]) && $this->options[$args['label_for']] == $key ? "selected" : "" ?>><?= $option ?></option>
            <?php endforeach; ?>
        </select>

        <?php
    }

    public function field_checkbox($args) {
        ?>
        <label for="<?php echo esc_attr( $args['label_for'] ); ?>">
            <input
                    type="checkbox"
                    class="regular-text"
                    id="<?php echo esc_attr( $args['label_for'] ); ?>"
                    name="bwi_cron_options[<?= esc_attr( $args['label_for'] ); ?>]"
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

        $this->options = get_option( 'bwi_cron_options' );

        settings_errors( 'bwi_cron_messages' );
        ?>
        <div class="wrap h-100 container">
            <h1 class="mb-3"><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <div class="bg-light rounded h-100 p-5">
                <div class="row">
                    <div class="col-md-8">
                        <form action="options.php" method="post" class="bwi_config_form">
                            <?php
                            settings_fields( 'bwi_cron' );
                            do_settings_sections( 'bwi-cron-settings' );
                            submit_button( 'Guardar Configuración' );
                            ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

}
