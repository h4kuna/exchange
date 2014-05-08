<?php

namespace h4kuna\Exchange\Driver\Ecb;

use DateTime;
use h4kuna\CUrl\CurlBuilder;
use h4kuna\Exchange\Download;

class Day extends Download {

    /**
     * Url where download rating
     *
     * @var const
     */
    const URL_DAY = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    /**
     * Load data from remote source
     * 
     * @param DateTime $date
     * @return array
     */
    protected function loadFromSource(DateTime $date) {
        $data = CurlBuilder::download(self::URL_DAY);
        $xml = simplexml_load_string($data);

        // including EUR
        $eur = $xml->Cube->Cube->addChild("Cube");
        $eur->addAttribute('currency', 'EUR');
        $eur->addAttribute('rate', '1');
        return $xml->Cube->Cube->Cube;
    }

    /**
     * @param string $row
     * @return CurrencyProperty|NULL
     */
    protected function createCurrencyProperty($row) {
        $home = 1;
        $currCode = (string) $row["currency"];
        $foreing = (float) $row["rate"];
        if ($foreing > 0 && !empty($currCode)) {
            $foreignRate = 1 / $foreing;
            return new \h4kuna\Exchange\CurrencyProperty($home, $currCode, $this->makeCorrection($home, $foreignRate));
        }
        return NULL;
    }

}
