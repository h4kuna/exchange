<?php

namespace h4kuna\Exchange;

use DateTime;
use h4kuna\Vat;
use Nette\Object;

/**
 * Download currency from server
 *
 * @author Milan Matějček
 */
abstract class Download extends Object {

    /** @var IStorage */
    protected $storeage;

    /**
     * Percent correction
     *
     * @var Vat
     */
    protected $correction = 0;

    /**
     * Base value for rate
     *
     * @var int
     */
    protected $base = 1;

    /**
     * Download data from remote source and save
     *
     * @param IStorage $storage
     * @param DateTime $date
     */
    final public function loadCurrencies(DateTime $date) {
        $this->setCorrection($this->correction);
        $data = $this->loadFromSource($date);
        $code = NULL;
        $out = array();
        foreach ($data as $row) {
            if (!$row) {
                continue;
            }
            $currency = $this->createCurrencyProperty($row);
            if ($currency !== NULL) {
                $code = $currency->setNext($code)->getCode();
            }
        }
    }

    /**
     * Correction for rate
     *
     * @param int|float|string|Vat $float
     * @return Download
     */
    public function setCorrection($float) {
        $this->correction = Vat::create($float);
        return $this;
    }

    /**
     * Change default rate
     *
     * @param float $home
     * @param float $foreing
     * @return float
     */
    final protected function makeCorrection($home, $foreing) {
        if ($home == $foreing && $home == $this->base) {
            return $foreing;
        }
        return $foreing / $this->correction->getUpDecimal();
    }

    /** @return string */
    public function getName() {
        return get_class($this);
    }

    /**
     * Load data for iterator
     *
     * @return array
     */
    abstract protected function loadFromSource(DateTime $date);

    /**
     * Modify data before save to cache
     *
     * @return CurrencyProperty|NULL
     */
    abstract protected function createCurrencyProperty($row);

    protected function createUrl($url, DateTime $date) {
        
    }

}
