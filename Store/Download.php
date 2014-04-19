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
abstract class Download extends Object implements IDownload {

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
    final public function loadCurrencies(IStorage $storage, DateTime $date) {
        $this->setCorrection($this->correction);
        $data = $this->loadData($date);
        $code = NULL;
        foreach ($data as $row) {
            if (!$row) {
                continue;
            }
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

    /** @return string */
    public function getName() {
        return get_class($this);
    }

    /**
     * Load data for iterator
     *
     * @return array
     */
    abstract protected function loadData(DateTime $date);

    /**
     * Modify data before save to cache
     *
     * @return ICurrencyProperty
     */
    abstract protected function createCurrencyProperty($row);
}
