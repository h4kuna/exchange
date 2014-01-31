<?php

namespace h4kuna\Exchange\Cnb;

use Nette\DateTime;
use h4kuna\CUrl\CurlBuilder;
use h4kuna\Exchange\Download;
use h4kuna\Exchange\Utils;

class Day extends Download {

    /** @var DateTime */
    private $date;

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
     * Load from history and testing data
     *
     * @param string $date
     * @return Day
     */
    public function setDate($date) {
        $this->date = DateTime::from($date);
        return $this;
    }

    /**
     * Load data from remote source
     *
     * @return type
     */
    protected function loadData() {
        $data = CurlBuilder::download($this->createUrl(self::CNB_DAY));

        $another = CurlBuilder::download($this->createUrl(self::CNB_DAY2));
        $another = explode("\n", Utils::stroke2point($another));
        unset($another[0], $another[1]);

        $data = explode("\n", Utils::stroke2point($data));
        $data[1] = self::CNB_CZK;
        unset($data[0]);
        return array_merge($data, $another);
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
            return $url . '?date=' . $this->date->format('d.m.Y');
        }
        return $url;
    }

}
