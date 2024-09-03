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
        $logfile = self::getCronActionName();
        $intcomexAPI = IntcomexAPI::getInstance();
        $productInventory = $intcomexAPI->getInventory();

        plugin_log('Iniciando sincronización de inventario',$logfile,'w');

        foreach($productInventory as $intcomexProduct) {
            $importerResponse = SyncHelper::syncProductInventory($intcomexProduct);
            if($importerResponse->isError()) {
                plugin_log([
                    'error' => $importerResponse->getErrors(),
                    'intcomexProduct' => $intcomexProduct->Sku
                ], $logfile);
            }
        }

        plugin_log('Sincronización de inventario finalizada',$logfile);
    }
}
