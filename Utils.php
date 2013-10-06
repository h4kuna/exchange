<?php

namespace h4kuna\Exchange;

/**
 * Description of Utils
 *
 * @author Milan Matějček
 */
class Utils {

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
