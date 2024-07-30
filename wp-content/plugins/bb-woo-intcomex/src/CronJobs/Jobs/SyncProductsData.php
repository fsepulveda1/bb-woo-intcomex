<?php

namespace Bigbuda\BbWooIntcomex\CronJobs\Jobs;

use Bigbuda\BbWooIntcomex\CronJobs\CronJobInterface;
use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;
use Bigbuda\BbWooIntcomex\Services\IntcomexAPI;

class SyncProductsData implements CronJobInterface {


    public static function getNiceName(): string {
        return "Sincronización de catálogo de productos";
    }

    public static function getCronActionName(): string
    {
        return "bwi_sync_products_data";
    }

    public function run()
    {
        $intcomexAPI = IntcomexAPI::getInstance();
        $allProducts = $intcomexAPI->getCatalog();

        //TODO write log with init information

        foreach($allProducts as $intcomexProduct) {
            $importerResponse = SyncHelper::addProductBase($intcomexProduct);

            if($importerResponse->isError()) {
                //TODO Write in log
            }
        }

        $allProductsData = $intcomexAPI->getExtendedCatalog();
        foreach($allProductsData as $intcomexProduct) {
            $importerResponse = SyncHelper::addExtendedProductInfo($intcomexProduct);

            if($importerResponse->isError()) {
                //TODO Write in log
            }
        }

        //TODO write log with finish information
    }

}
