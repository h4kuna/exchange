<?php

namespace h4kuna\Exchange\Cnb;

use h4kuna\Exchange\Download;
use h4kuna\Exchange\Utils;
use h4kuna\CUrl;

class Day extends Download {

    /**
     * Url where download rating
     *
     * @var const
     */
    const CNB_DAY = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt';
    const CNB_DAY2 = 'http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_ostatnich_men/kurzy.txt';

    /**
     * Include czech rating !important
     *
     * @var const
     */
    const CNB_CZK = 'Česká Republika|koruna|1|CZK|1';

    //protected $correction = 1.04;

    /**
     * Load data from remote source
     *
     * @return type
     */
    protected function loadData() {
        $data = CUrl::download(self::CNB_DAY);

        $another = Curl::download(self::CNB_DAY2);
        $another = explode("\n", Utils::stroke2point($another));
        unset($another[0], $another[1]);

        $data = explode("\n", Utils::stroke2point($data));
        $data[1] = self::CNB_CZK;
        unset($data[0]);
        return array_merge($data, $another);
    }

    /**
     * @param string $row
     * @return \h4kuna\Exchange\Cnb\CurrencyProperty|null
     */
    protected function createCurrencyProperty($row) {
        list($country, $currency, $home, $code, $foreing) = explode('|', $row);
        if ($foreing != 0.0) {
            return new CurrencyProperty($home, $code, $this->makeCorrection($home, $foreing), $country, $currency);
        }
        return NULL;
    }

    public function getPrefix() {
        return NULL;
    }

}
