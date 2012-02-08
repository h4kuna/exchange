<?php

namespace Exchange25;

require_once 'ExchangePoint.php';
require_once 'CnbDb.php';

/**
 * HOW TO
 *
 * 1) connect to db with name 'exchange'
 * dibi::connect((array)Environment::getConfig('sqLite3'), 'exchange');
 *
 * 2) in presenter before register change name of class
 * Helper::$nameClass = '\Exchange25\Sandbox';
 *
 * 3) register
 * Helper::register('vat');
 */


/**
 *
 */
class Sandbox extends ExchangePoint
{
    protected function download()
    {
        $obj = __NAMESPACE__ .'\CnbDb';
        $this->download = new $obj($this);
    }
}
