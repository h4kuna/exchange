<?php

namespace h4kuna\Exchange\Storage;

use Nette\Object;

/**
 * @author Milan Matejcek
 */
abstract class RequestManager extends Object implements IRequestManager {

    /** @return string */
    public function getParamCurrency() {
        return 'currency';
    }

    /** @return string */
    public function getParamVat() {
        return 'vat';
    }

}
