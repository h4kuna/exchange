<?php

namespace h4kuna\Exchange;

/**
 * Description of Download
 *
 * @author Milan Matějček
 */
abstract class Download implements IDownload {

    /** @var IStorage */
    protected $storeage;

    /**
     * Download data from remote source and save
     *
     * @param IStorage $storage
     */
    final public function loadCurrencies(IStorage $storage) {
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
