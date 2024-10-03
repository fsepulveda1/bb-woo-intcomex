<?php

namespace Bigbuda\BbWooIntcomex\Importer;

use Bigbuda\BbWooIntcomex\Importer\Importers\ExtendedCatalogImporter;
use Bigbuda\BbWooIntcomex\Importer\Importers\IceCatImagesImporter;
use Bigbuda\BbWooIntcomex\Importer\Importers\ImporterInterface;
use Bigbuda\BbWooIntcomex\Importer\Importers\InventoryImporter;
use Bigbuda\BbWooIntcomex\Importer\Importers\PricesImporter;
use Bigbuda\BbWooIntcomex\Importer\Importers\ProductImporter;
use Bigbuda\BbWooIntcomex\Services\IceCatAPI;
use Bigbuda\BbWooIntcomex\Services\IceCatJsonAPI;
use Bigbuda\BbWooIntcomex\Services\IntcomexAPI;
use http\Exception;
use ReflectionClass;

/**
 * If this file is called directly, abort.
 *
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

class ImporterFactory {

    public ImporterInterface $importer;
    public IntcomexAPI $intcomexAPI;
    public IceCatJsonAPI $iceCatAPI;

    public array $options;

    /**
     * @throws \Exception
     */
    public function __construct($type) {
        $this->intcomexAPI = IntcomexAPI::getInstance();
        $this->iceCatAPI = new IceCatJsonAPI();
        $this->options = get_option('bwi_options');
        $importerClass = self::getImporters()[$type];
        if($importerClass) {
            $this->setImporter($importerClass);
        }
        else {
            throw new \Exception('El importador "'.$type.'" no existe.');
        }
    }

    /**
     * TODO Refactor the list of importers using reflection class
     * @return string[]
     */
    public static function getImporters() {
        return [
            'product' => ProductImporter::class,
            'icecat_images' => IceCatImagesImporter::class,
            'product_inventory' => InventoryImporter::class,
            'product_prices' => PricesImporter::class,
            'extended_catalog' => ExtendedCatalogImporter::class
        ];
    }

    public function setImporter($importerClass) {
        $this->importer = new $importerClass($this->intcomexAPI, $this->iceCatAPI, $this->options);
    }

    public function importBatch($form_data): array
    {
        @ini_set('max_execution_time',600);
        @ini_set('memory_limit',-1);
        $page = $form_data['page'];
        $rowsPerPage = $this->importer->getRowsPerPage();

        if(empty($form_data['total_pages'])) {
            $total_rows = $this->importer->count();
            $form_data['total_pages'] = ceil($total_rows / $rowsPerPage);
            $form_data['total_rows'] = $total_rows;
        }

        $response = $this->importer->process($page, $form_data);

        if(is_array($response)) {
            list ($processed, $errors) = $response;
        }
        else {
            $processed = $response;
        }

        $form_data['current_row'] = (int) ($form_data['current_row'] ?? 0) + $processed;

        if ($page >= $form_data['total_pages'] ) {
            $response = [
                'result' => 'COMPLETE',
                'form_data' => json_encode($form_data),
                'errors' => !empty($errors) ? json_encode($errors) : null
            ];

            //plugin_log('Sincronización finalizada','a',$form_data['log_file']);
        }
        else {
            $form_data['page'] = $page + 1;
            $response = [
                'result' => 'NEXT',
                'form_data' => json_encode($form_data),
                'errors' => !empty($errors) ? json_encode($errors) : null
            ];
        }

        return $response;
    }
}
