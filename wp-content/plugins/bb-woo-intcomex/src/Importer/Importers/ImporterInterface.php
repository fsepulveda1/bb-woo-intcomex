<?php

namespace Bigbuda\BbWooIntcomex\Importer\Importers;

interface ImporterInterface {

    public function count():int;

    /**
     * @param $page
     * @param array $options
     * @return int | array
     */
    public function process($page, array $options);
}
