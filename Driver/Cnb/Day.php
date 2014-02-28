<?php

namespace h4kuna\Exchange\Cnb;

use h4kuna\CUrl\CurlBuilder;
use h4kuna\Exchange\Download;
use h4kuna\Exchange\Utils;

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

    /**
     * Load data from remote source
     *
     * @return array
     */
    protected function loadData() {
        $data = $this->downloadList(self::CNB_DAY);
        $data[1] = self::CNB_CZK;
        unset($data[0]);

        $another = $this->downloadList(self::CNB_DAY2);
        unset($another[0], $another[1]);

        return array_merge($data, $another);
    }

    /**
     * @param string $url
     * @return array
     */
    private function downloadList($url) {
        $data = CurlBuilder::download($this->createUrl($url));
        return explode("\n", Utils::stroke2point($data));
    }

    /**
     * @param string $row
     * @return CurrencyProperty|NULL
     */
    protected function createCurrencyProperty($row) {
        list($country, $currency, $home, $code, $foreing) = explode('|', $row);
        if ($foreing != 0.0) {
            return new CurrencyProperty($home, $code, $this->makeCorrection($home, $foreing), $country, $currency);
        }
        return NULL;
    }

    public function getPrefix() {
        return 'cnb';
    }

    /**
     *
     * @param string $url
     * @return string
     */
    private function createUrl($url) {
        if ($this->date) {
            return $url . '?date=' . urlencode($this->date->format('d.m.Y'));
        }
        return $url;
    }

}
