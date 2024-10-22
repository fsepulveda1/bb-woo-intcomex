<?php

namespace Bigbuda\BbWooIntcomex\Importer\Importers;

use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;

class PricesImporter extends BaseImporter implements ImporterInterface  {

    public int $rowsPerPage = 70;

    public function count():int
    {
        $allProducts = $this->intcomexAPI->getPriceList();
        $chunks = array_chunk($allProducts,$this->rowsPerPage);
        foreach($chunks as $key => $chunk) {
            set_transient('bwi_product_prices_chunks_'.$key, $chunks, 1800);
        }

        return count($allProducts);
    }

    public function process($page, array $options): array
    {
        $intcomexProductsChunks = get_transient('bwi_product_prices_chunks_'.$page-1);
        $errors = [];
        $processed = 0;
        $USD2CLP = getUsdValue();
        $profitMargin = $this->options['field_profit_margin'];
        $paymentMethodMargin = $this->options['field_payment_method_margin'];
        foreach ($intcomexProductsChunks[$page-1] as $intcomexProduct) {
            $processed++;
            $importerResponse = SyncHelper::syncProductPrice($intcomexProduct,$USD2CLP,$profitMargin,$paymentMethodMargin);

            if($importerResponse->isError()) {
                $errors = array_merge($errors, $importerResponse->getErrors());
            }
        }

        return [$processed, $errors];
    }
}
