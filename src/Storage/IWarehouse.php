<?php

namespace h4kuna\Exchange\Storage;

use DateTime,
	h4kuna\Exchange\Driver;

/**
 *
 * @author Milan Matějček
 */
interface IWarehouse
{

	public function __construct(IFactory $factory, Driver\Download $download);

	/** @return ICurrency */
	public function loadCurrency($code);

	/**
	 * Identification name
	 * @return string
	 */
	public function getName();

	/**
	 * Change date for currency
	 *
	 * @param DateTime $date
	 */
	public function setDate(DateTime $date);

	/**
	 * Change driver for currency
	 * @param Driver\Download $driver
	 */
	public function setDriver(Driver\Download $driver);

	/** @return array */
	public function getListCurrencies();

	/**
	 * @param Driver\Download $river
	 * @return string
	 */
	public function loadNameByDriver(Driver\Download $river);

	/**
	 * @param DateTime $date
	 * @retrun string
	 */
	public function loadNameByDate(DateTime $date);
}
