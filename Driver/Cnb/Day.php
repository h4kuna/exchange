<?php

namespace h4kuna\Exchange\Cnb;

use DateTime;
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
     * @param DateTime $date
     * @return array
     */
    protected function loadData(DateTime $date) {
        $data = $this->downloadList(self::CNB_DAY, $date);
        $data[1] = self::CNB_CZK;
        unset($data[0]);

        $another = $this->downloadList(self::CNB_DAY2, $date);
        unset($another[0], $another[1]);
        dd();
        return array_merge($data, $another);
    }

    /**
     * @param string $url
     * @param DateTime $date
     * @return array
     */
    private function downloadList($url, DateTime $date) {
        $data = CurlBuilder::download($this->createUrl($url, $date));
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

    /**
     *
     * @param string $url
     * @param DateTime $date
     * @return string
     */
    private function createUrl($url, DateTime $date) {
        return $url . '?date=' . urlencode($date->format('d.m.Y'));
    }

}
