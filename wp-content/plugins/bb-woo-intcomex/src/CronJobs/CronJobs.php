<?php

namespace Bigbuda\BbWooIntcomex\CronJobs;

use Bigbuda\BbWooIntcomex\CronJobs\Jobs\SyncProductsData;
use Bigbuda\BbWooIntcomex\CronJobs\Jobs\SyncProductsInventory;
use Bigbuda\BbWooIntcomex\CronJobs\Jobs\SyncProductsPrice;
use BMI\SyncJobs\Jobs\ProductETACronJob;

/**
 * If this file is called directly, abort.
 *
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

class CronJobs {

    public static function getJobs(): array
    {
        return [
            SyncProductsData::class,
            SyncProductsInventory::class,
            SyncProductsPrice::class
        ];
    }

    public function __construct() {

        add_filter( 'cron_schedules', [$this,'add_cron_schedules'] );
        $options = get_option('bwi_cron_options');
        $now = time();
        /** @var CronJobInterface $job */
        foreach($this::getJobs() as $job) {
            $job = new $job();
            if($job instanceof CronJobInterface) {
                $action = $job->getCronActionName();
                add_action($action, [$job, 'run']);
                if ( ! wp_next_scheduled( $action ) ) {
                    if(isset($options[$action.'_interval'])) {
                        wp_schedule_event(
                            !empty($options[$action . '_start_time']) ? strtotime($options[$action . '_start_time']) : $now,
                            $options[$action . '_interval'],
                            $action
                        );
                    }
                }
            }
        }

    }

    public function add_cron_schedules( $schedules ) {

        $schedules['every_five_minutes'] = array(
            'interval'  => MINUTE_IN_SECONDS * 5,
            'display'   => __( 'Cada 5 minutos', 'int' )
        );

        $schedules['every_ten_minutes'] = array(
            'interval'  => MINUTE_IN_SECONDS * 10,
            'display'   => __( 'Cada 10 minutos', 'int' )
        );

        $schedules['hourly'] = array(
            'interval'  => HOUR_IN_SECONDS,
            'display'   => __( 'Cada 1 hora', 'int' )
        );

        $schedules['hourly_3'] = array(
            'interval'  => HOUR_IN_SECONDS * 3,
            'display'   => __( 'Cada 12 horas', 'int' )
        );

        $schedules['hourly_6'] = array(
            'interval'  => HOUR_IN_SECONDS * 6,
            'display'   => __( 'Cada 12 horas', 'int' )
        );

        $schedules['hourly_12'] = array(
            'interval'  => HOUR_IN_SECONDS * 12,
            'display'   => __( 'Cada 12 horas', 'int' )
        );

        $schedules['daily'] = array(
            'interval'  => HOUR_IN_SECONDS * 24,
            'display'   => __( 'Una vez al día', 'int' )
        );

        $schedules['weekly'] = array(
            'interval'  => HOUR_IN_SECONDS * 24 * 7,
            'display'   => __( 'Una vez a la semana', 'int' )
        );

        $schedules['monthly'] = array(
            'interval'  => HOUR_IN_SECONDS * 24 * 7 * 4,
            'display'   => __( 'Una vez al mes', 'int' )
        );

        return $schedules;
    }
}
