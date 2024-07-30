<?php

namespace Bigbuda\BbWooIntcomex\CronJobs\Jobs;

use Bigbuda\BbWooIntcomex\CronJobs\CronJobInterface;
use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;
use Bigbuda\BbWooIntcomex\Services\IntcomexAPI;

class SyncProductsInventory implements CronJobInterface {

    public static function getNiceName(): string
    {
        return "Sincronización de inventario";
    }
    public static function getCronActionName(): string
    {
        return "bwi_sync_products_inventory";
    }

    public function run()
    {
        $intcomexAPI = IntcomexAPI::getInstance();
        $productInventory = $intcomexAPI->getInventory();

        //TODO write log with init information

        foreach($productInventory as $intcomexProduct) {
            $importerResponse = SyncHelper::syncProductInventory($intcomexProduct);
            if($importerResponse->isError()) {
                //TODO Write in log
            }
        }

        //TODO write log with finish information
    }
}
