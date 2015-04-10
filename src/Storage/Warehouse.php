<?php

namespace h4kuna\Exchange\Storage;

use DateTime;
use Nette\Object;
use h4kuna\Exchange\Driver\Download;
use h4kuna\Exchange\ExchangeException;

/**
 *
 * @author Milan Matějček
 */
class Warehouse extends Object implements IWarehouse
{

    /** @var Stock */
    private $stock;

    /** @var IFactory */
    private $storageFactory;

    /** @var Download */
    private $download;

    /** @var DateTime */
    protected $date;

    public function __construct(IFactory $factory, Download $download)
    {
        $this->storageFactory = $factory;
        $this->download = $download;
    }

    /**
     * Load currency and property
     *
     * @param string $code
     * @return IProperty
     */
    public function loadCurrency($code)
    {
        try {
            $code = strtoupper($code);
            return $this->checkCurrency($code);
        } catch (ExchangeException $e) {
            $this->getStock()->saveCurrencies($this->download->loadCurrencies($this->date));
            return $this->checkCurrency($code);
        }
    }

    /**
     * Check currency is loaded or exists
     *
     * @param string $code
     * @return IProperty
     * @throws ExchangeException
     */
    private function checkCurrency($code)
    {
        $stock = $this->getStock();
        if ($stock[$code]) {
            return $stock[$code];
        }

        throw new ExchangeException('Undefined currency code: ' . $code);
    }

    /**
     *
     * @return Stock
     */
    private function getStock()
    {
        if ($this->stock === NULL) {
            $this->stock = $this->storageFactory->create($this->getName());
        }
        return $this->stock;
    }

    /**
     *
     * @param DateTime $date
     * @return Store
     */
    public function setDate(DateTime $date)
    {
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
    public function setDriver(Download $driver)
    {
        $store = new static($this->storageFactory, $driver);
        $store->date = $this->date;
        return $store;
    }

    /** @return string */
    public function getName()
    {
        return $this->getNameOf($this->date) . $this->download->getName();
    }

    /**
     *
     * @param Download $driver
     * @return string
     */
    public function loadNameByDriver(Download $driver)
    {
        return $this->getNameOf($this->date) . $driver->getName();
    }

    /**
     * @param DateTime $date
     * @return string
     */
    public function loadNameByDate(DateTime $date)
    {
        return $this->getNameOf($date) . $this->download->getName();
    }

    /**
     *
     * @param DateTime $date
     * @return string|NULL
     */
    private function getNameOf(DateTime $date = NULL)
    {
        if ($date === NULL) {
            return NULL;
        }

        $ymd = $date->format('Y-m-d');
        if (date('Y-m-d') > $ymd) {
            return $ymd . '\\';
        }

        return NULL;
    }

    public function __toString()
    {
        return (string) $this->getName();
    }

    public function getListCurrencies()
    {
        return $this->getStock()->getListCurrencies();
    }

}
