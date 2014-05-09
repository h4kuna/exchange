<?php

namespace h4kuna\Exchange\Driver;

use DateTime;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\ExchangeException;
use Nette\Object;

/**
 * Download currency from server
 *
 * @author Milan Matějček
 */
abstract class Download extends Object {

    /**
     * Download data from remote source and save
     *
     * @param DateTime $date
     * @return Property
     */
    final public function loadCurrencies(DateTime $date = NULL) {
        $currencies = array();
        foreach ($this->loadFromSource($date) as $row) {
            if (!$row) {
                continue;
            }
            $property = $this->createProperty($row);

            if (!$property || !$property->getHome() || !$property->getForeing()) {
                continue;
            }
            if (isset($currencies[$property->getCode()])) {
                throw new ExchangeException('In source is duplicity code ' . $property->getCode());
            }
            $currencies[$property->getCode()] = $property;
        }
        return $currencies;
    }

    /** @return string */
    public function getName() {
        return str_replace(__NAMESPACE__ . '\\', '', get_class($this));
    }

    /**
     * 
     * @param string $url
     * @param DateTime $date
     * @return string
     */
    protected function createUrl($url, DateTime $date = NULL) {
        if ($date === NULL || $date->format('Y-m-d') === date('Y-m-d')) {
            return $url;
        }
        return $this->createUrlDay($url, $date);
    }

    /**
     * Load data for iterator
     *
     * @return array
     */
    abstract protected function loadFromSource(DateTime $date = NULL);

    /**
     * Modify data before save to cache
     *
     * @return Property|NULL
     */
    abstract protected function createProperty($row);

    /**
     * 
     * @param string $url
     * @param DateTime $date
     * @return string
     */
    abstract protected function createUrlDay($url, DateTime $date);
}
