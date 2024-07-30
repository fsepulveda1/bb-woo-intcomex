<?php

namespace Bigbuda\BbWooIntcomex\CronJobs\Jobs;

use Bigbuda\BbWooIntcomex\CronJobs\CronJobInterface;
use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;
use Bigbuda\BbWooIntcomex\Services\IntcomexAPI;

class SyncProductsPrice implements CronJobInterface {

    public static function getNiceName(): string
    {
        return "Sincronización de precios";
    }
    public static function getCronActionName(): string
    {
        return "bwi_sync_products_prices";
    }

    public function run()
    {
        $intcomexAPI = IntcomexAPI::getInstance();
        $productPriceList = $intcomexAPI->getPriceList();
        $options = get_option('bwi_options');

        //TODO write log with init information

        $USD2CLP = getUsdValue();
        $profitMargin = $options['field_profit_margin'];

        foreach($productPriceList as $intcomexProduct) {
            $importerResponse = SyncHelper::syncProductPrice($intcomexProduct, $USD2CLP, $profitMargin);
            if($importerResponse->isError()) {
                //TODO Write in log
            }
        }

        //TODO write log with finish information
    }
}
