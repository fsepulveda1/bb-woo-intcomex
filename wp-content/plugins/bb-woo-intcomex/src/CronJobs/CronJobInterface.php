<?php

namespace Bigbuda\BbWooIntcomex\CronJobs;

interface CronJobInterface {
    public static function getNiceName() :string;
    public static function getCronActionName() :string;
    public function run();
}