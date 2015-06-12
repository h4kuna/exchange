<?php

namespace h4kuna\Exchange\Storage;

use DateTime,
	h4kuna\Exchange,
	Nette\Object;

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

	/** @var Exchange\Driver\Download */
	private $download;

	/** @var DateTime */
	protected $date;

	public function __construct(IFactory $factory, Exchange\Driver\Download $download)
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
		} catch (Exchange\ExchangeException $e) {
			$this->getStock()->saveCurrencies($this->download->loadCurrencies($this->date));
			return $this->checkCurrency($code);
		}
	}

	/**
	 * Check currency is loaded or exists
	 *
	 * @param string $code
	 * @return IProperty
	 * @throws Exchange\UnknownCurrencyException
	 */
	private function checkCurrency($code)
	{
		$stock = $this->getStock();
		$property = $stock->load($code);
		if ($property !== NULL) {
			return $property;
		}

		throw new Exchange\UnknownCurrencyException($code);
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
	 * @param Exchange\Driver\Download $driver
	 * @return Store
	 */
	public function setDriver(Exchange\Driver\Download $driver)
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
	 * @param Exchange\Driver\Download $driver
	 * @return string
	 */
	public function loadNameByDriver(Exchange\Driver\Download $driver)
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
