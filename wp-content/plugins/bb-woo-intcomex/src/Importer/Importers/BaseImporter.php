<?php

namespace Bigbuda\BbWooIntcomex\Importer\Importers;

use Bigbuda\BbWooIntcomex\Services\IceCatAPI;
use Bigbuda\BbWooIntcomex\Services\IceCatJsonAPI;
use Bigbuda\BbWooIntcomex\Services\IntcomexAPI;

class BaseImporter {

    public array $options;
    public int $rowsPerPage = 50;
    public IntcomexAPI $intcomexAPI;
    public IceCatJsonAPI $iceCatAPI;

    public function __construct(IntcomexAPI $intcomexAPI, IceCatJsonAPI $iceCatAPI, array $options)
    {
        $this->intcomexAPI = $intcomexAPI;
        $this->iceCatAPI = $iceCatAPI;
        $this->options = $options;
    }

    public function getRowsPerPage(): int
    {
        return $this->rowsPerPage;
    }

}
