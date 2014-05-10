<?php

namespace h4kuna\Exchange;

use Nette\StaticClassException;

/**
 * Static helpers
 *
 * @author Milan Matějček
 */
class Utils {

    /**
     * Czech currency code
     */
    const CZK = 'CZK';

    public function __construct() {
        throw new StaticClassException;
    }

    /**
     * Stroke replace by point
     *
     * @param string $str
     * @return string
     */
    public static function stroke2point($str) {
        return trim(str_replace(',', '.', $str));
    }

}
