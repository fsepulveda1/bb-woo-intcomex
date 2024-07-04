<?php

namespace Bigbuda\BbWooIntcomex\Importer\Importers;

use Bigbuda\BbWooIntcomex\Services\IceCatAPI;
use Bigbuda\BbWooIntcomex\Services\IntcomexAPI;

 class BaseImporter {
    public int $rowsPerPage = 50;
     public IntcomexAPI $intcomexAPI;
     public IceCatAPI $iceCatAPI;

    public function __construct(IntcomexAPI $intcomexAPI, IceCatAPI $iceCatAPI)
    {
        $this->intcomexAPI = $intcomexAPI;
        $this->iceCatAPI = $iceCatAPI;
    }

    public function getRowsPerPage(): int
    {
        return $this->rowsPerPage;
    }
    
}
