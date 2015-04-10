<?php

namespace h4kuna\Exchange\Driver\Ecb;

use DateTime;
use h4kuna\CUrl\CurlBuilder;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Driver\Download;
use h4kuna\Exchange\ExchangeException;

/**
 * @author Petr Poupě <pupe.dupe@gmail.com>
 */
class Day extends Download
{

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
    protected function loadFromSource(DateTime $date = NULL)
    {
        // @todo kdyby curl
        $data = CurlBuilder::download($this->createUrl(self::URL_DAY, $date));
        $xml = simplexml_load_string($data);

        // including EUR
        $eur = $xml->Cube->Cube->addChild("Cube");
        $eur->addAttribute('currency', 'EUR');
        $eur->addAttribute('rate', '1');
        return $xml->Cube->Cube->Cube;
    }

    /**
     * @param string $row
     * @return Property|NULL
     */
    protected function createProperty($row)
    {
        return new Property(1, $row['currency'], $row['rate']);
    }

    protected function createUrlDay($url, DateTime $date)
    {
        throw new ExchangeException('This driver does not support history.');
    }

}
