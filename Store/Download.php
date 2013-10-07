<?php

namespace h4kuna\Exchange;

use h4kuna\Vat;

/**
 * Description of Download
 *
 * @author Milan Matějček
 */
abstract class Download implements IDownload {

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
     */
    final public function loadCurrencies(IStorage $storage) {
        $this->setCorrection($this->correction);
        $data = $this->loadData();
        $storage->setPrefix($this->getPrefix());
        $code = NULL;
        foreach ($data as $row) {
            $currency = $this->createCurrencyProperty($row);
            if ($currency !== NULL) {
                $code = $currency->setNext($code)->getCode();
                $storage->saveCurrency($currency);
            }
        }
        $storage->saveLast($code);
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

    /**
     * Load data for iterator
     *
     * @return array
     */
    abstract protected function loadData();

    /**
     * Modify data before save to cache
     *
     * @return ICurrencyProperty
     */
    abstract protected function createCurrencyProperty($row);
}
