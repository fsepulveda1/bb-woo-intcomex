<?php

namespace Bigbuda\BbWooIntcomex\Importer\Importers;

use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;
use WP_Term;

class ExtendedCatalogImporter extends BaseImporter implements ImporterInterface  {

    public int $rowsPerPage = 50;

    public function count():int
    {
        $allProducts = $this->intcomexAPI->getExtendedCatalog();
        $total = count($allProducts);
        $chunks = array_chunk($allProducts,$this->rowsPerPage);
        unset($allProducts);
        foreach($chunks as $key => $chunk) {
            set_transient('bwi_product_extended_chunks_'.$key, $chunks, 7200);
        }
        unset($chunks);
        return $total;
    }

    public function process($page, array $options): array
    {
        $intcomexProductsChunks = get_transient('bwi_product_extended_chunks_'.$page-1);
        $errors = [];
        $processed = 0;
        foreach ($intcomexProductsChunks[$page-1] as $intcomexProduct) {
            $processed++;
            $importerResponse = SyncHelper::addExtendedProductInfo($intcomexProduct);
            if($importerResponse->isError()) {
                $errors = array_merge($errors, $importerResponse->getErrors());
            }
        }

        return [$processed, $errors];
    }
}
