<?php

namespace h4kuna\Exchange\Driver;

use DateTime,
	h4kuna\Exchange;

/**
 * Download currency from server.
 */
abstract class ADriver
{

	/** @var \DateTime */
	private $date;


	/**
	 * Download data from remote source and save.
	 * @param DateTime $date
	 * @param array $allowedCurrencies
	 * @return Exchange\Currency\ListRates
	 */
	public function download(DateTime $date = null, array $allowedCurrencies = [])
	{
		$allowedCurrencies = array_flip($allowedCurrencies);
		$source = $this->loadFromSource($date);
		$currencies = new Exchange\Currency\ListRates($this->getDate());
		foreach ($source as $row) {
			if (!$row) {
				continue;
			}
			$property = $this->createProperty($row);

			if (!$property || !$property->rate || ($allowedCurrencies !== [] && !isset($allowedCurrencies[$property->code]))) {
				continue;
			}
			$currencies->addProperty($property);
		}
		$currencies->getFirst(); // check if is not empty
		return $currencies;
	}


	protected function setDate($format, $value)
	{
		$this->date = DateTime::createFromFormat($format, $value);
		$this->date->setTime(0, 0, 0);
	}


	public function getName()
	{
		return strtolower(str_replace('\\', '.', static::class));
	}


	/**
	 * @return DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}


	/**
	 * Load data for iterator.
	 * @param DateTime|NULL $date
	 * @return array
	 */
	abstract protected function loadFromSource(DateTime $date = null);


	/**
	 * Modify data before save to cache.
	 * @return Exchange\Currency\Property|NULL
	 */
	abstract protected function createProperty($row);

}
