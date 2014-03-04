<?php

namespace h4kuna\Exchange;

use DateTime;
use Nette\Object;

/**
 * Description of Store
 *
 * @author Milan Matějček
 */
class Store extends Object implements IStore {

    /** @var Storage */
    private $storage;

    /** @var Download */
    private $download;

    /** @var DateTime */
    private $date;

    public function __construct(Storage $storage, Download $download) {
        $this->storage = $storage;
        $this->download = $download;
    }

    /**
     * Load currency and property
     *
     * @param string $code
     * @return ICurrencyProperty
     */
    public function loadCurrency($code) {
        $code = strtoupper($code);
        try {
            return $this->checkCurrency($code);
        } catch (ExchangeException $e) {
            $this->download->loadCurrencies($this->storage, $this->date);
            return $this->checkCurrency($code);
        }
    }

    /**
     * 
     * @param DateTime $date
     * @return Store
     */
    public function setDate(DateTime $date) {
        $storage = $this->storage->setDate($date);
        if($this->storage === $storage) {
            return $this;
        }
        $store = new static($storage, $this->download);
        $store->date = $date;
        return $store;
    }

    /** @return DateTime */
    public function getDate() {
        if ($this->date === NULL) {
            $this->date = new DateTime('midnight');
        }
        return $this->date;
    }

    /**
     * Use for loadAll where start
     *
     * @return string
     */
    public function loadCode() {
        return $this->storage->loadLast();
    }

    /**
     * Check currency is loaded or exists
     *
     * @param string $code
     * @return ICurrencyProperty
     * @throws ExchangeException
     */
    private function checkCurrency($code) {
        if ($this->storage[$code]) {
            return $this->storage[$code];
        }

        throw new ExchangeException('Undefined currency code: ' . $code);
    }

}
