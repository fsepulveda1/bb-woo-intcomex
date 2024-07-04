<?php
namespace Bigbuda\BbWooIntcomex\Importer\Importers;

interface SingleImporterInterface {
    public function processSingle(array $options);
}
