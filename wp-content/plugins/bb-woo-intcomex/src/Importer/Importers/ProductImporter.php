<?php

namespace Bigbuda\BbWooIntcomex\Importer\Importers;

use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;
use WP_Term;

class ProductImporter extends BaseImporter implements ImporterInterface  {

    public int $rowsPerPage = 100;
    public array $firstChunk = [];

    public function count():int
    {
        $allProducts = $this->intcomexAPI->getCatalog();
        $chunks = array_chunk($allProducts,$this->rowsPerPage);
        $total = count($allProducts);
        unset($allProducts);
        foreach($chunks as $key => $chunk) {
            if($key == 0) {
                $this->firstChunk = $chunk;
            }
            else {
                set_transient('bwi_product_chunks_'.$key, $chunk, 1800);
            }
        }

        return $total;
    }

    public function process($page, array $options): array
    {
        $intcomexProductsChunks = $this->firstChunk ?: get_transient('bwi_product_chunks_'.$page-1);
        $errors = [];
        $processed = 0;
        foreach ($intcomexProductsChunks as $intcomexProduct) {
            $processed++;
            $importerResponse = SyncHelper::addProductBase($intcomexProduct);
            $errors[] = json_encode($intcomexProduct);

            if($importerResponse->isError()) {
                $errors = array_merge($errors, $importerResponse->getErrors());
            }
        }

        return [$processed, $errors];
    }
}
