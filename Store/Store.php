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

    /** @var IStorageFactory */
    private $storageFactory;

    /** @var Download */
    private $download;

    /** @var DateTime */
    protected $date;

    /**
     * History instances
     *
     * @var array
     */
    private static $history = array();

    public function __construct(IStorageFactory $storage, Download $download) {
        $this->storageFactory = $storage;
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
            $this->download->loadCurrencies($this->date);
            return $this->checkCurrency($code);
        }
    }

    /**
     * New instance
     *
     * @return Storage
     */
    protected function createStorage($name) {
        return $this->storageFactory->create($name);
    }

    /**
     *
     * @return Storage
     */
    public function getStorage() {
        if ($this->storage === NULL) {
            $this->storage = $this->createStorage($this->getName());
        }
        return $this->storage;
    }

    /**
     *
     * @param DateTime $date
     * @return Store
     */
    public function setDate(DateTime $date) {
        $key = $this->loadNameByDate($date);
        if (isset(self::$history[$key])) {
            return self::$history[$key];
        }

        $store = new static($this->storageFactory, $this->download);
        $store->date = $date;
        return $store;
    }

    /**
     * Change driver runtime
     *
     * @param Download $driver
     * @return Store
     */
    public function setDriver(Download $driver) {
        $key = $this->loadNameByDriver($driver);
        if (isset(self::$history[$key])) {
            return self::$history[$key];
        }
        $store = new static($this->storageFactory, $driver);
        $store->date = $this->getDate();
        return self::$history[$key] = $store;
    }

    /** @return DateTime */
    public function getDate() {
        if ($this->date === NULL) {
            $this->date = new DateTime('midnight');
        }
        return $this->date;
    }

    /** @return string */
    public function getName() {
        return $this->getNameOf($this->getDate()) . $this->download->getName();
    }

    /**
     *
     * @param Download $driver
     * @return string
     */
    private function loadNameByDriver(Download $driver) {
        return $this->getNameOf($this->getDate()) . $driver->getName();
    }

    /**
     * @param DateTime $date
     * @return string
     */
    private function loadNameByDate(DateTime $date) {
        return $this->getNameOf($date) . $this->download->getName();
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

    /**
     *
     * @param DateTime $date
     * @return string|NULL
     */
    private function getNameOf(DateTime $date) {
        $ymd = $date->format('Y-m-d');
        if (date('Y-m-d') > $ymd) {
            return '\\' . $ymd;
        }
        return NULL;
    }

    public function __toString() {
        return (string) $this->getName();
    }

}
