<?php

namespace Bigbuda\BbWooIntcomex\Importer\Importers;

use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;
use WP_Term;

class ProductImporter extends BaseImporter implements ImporterInterface  {

    public int $rowsPerPage = 70;

    public function count():int
    {
        $allProducts = $this->intcomexAPI->getCatalog();
        $chunks = array_chunk($allProducts,$this->rowsPerPage);
        foreach($chunks as $key => $chunk) {
            set_transient('bwi_product_chunks_'.$key, $chunks, 1800);
        }

        return count($allProducts);
    }

    public function process($page, array $options): array
    {
        $intcomexProductsChunks = get_transient('bwi_product_chunks_'.$page-1);
        $errors = [];
        $processed = 0;
        foreach ($intcomexProductsChunks[$page-1] as $intcomexProduct) {
            $processed++;
            $importerResponse = SyncHelper::addProductBase($intcomexProduct);
            if($importerResponse->isError()) {
                $errors = array_merge($errors, $importerResponse->getErrors());
            }
        }

        return [$processed, $errors];
    }
}
