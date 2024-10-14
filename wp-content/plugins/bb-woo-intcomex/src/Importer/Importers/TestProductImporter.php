<?php

namespace Bigbuda\BbWooIntcomex\Importer\Importers;

use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;
use WP_Term;

class TestProductImporter extends BaseImporter implements ImporterInterface  {

    public int $rowsPerPage = 100;
    public array $firstChunk = [];

    public function count():int
    {
        return 1;
    }

    public function process($page, array $options): array
    {
        $sku = $options['product_sku'];
        $product = $this->intcomexAPI->getProduct($sku);
        $errors[] = json_encode($product);
        return [1, $errors];
    }
}
