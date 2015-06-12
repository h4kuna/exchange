<?php

namespace h4kuna\Exchange\Driver;

use DateTime,
	h4kuna\Exchange,
	Nette;

/**
 * Download currency from server.
 * @author Milan Matějček
 */
abstract class Download extends Nette\Object
{

	/**
	 * Download data from remote source and save.
	 * @param DateTime $date
	 * @return Exchange\Currency\Property
	 */
	final public function loadCurrencies(DateTime $date = NULL)
	{
		$currencies = array();
		foreach ($this->loadFromSource($date) as $row) {
			if (!$row) {
				continue;
			}
			$property = $this->createProperty($row);

			if (!$property || !$property->getHome() || !$property->getForeing()) {
				continue;
			}
			if (isset($currencies[$property->getCode()])) {
				throw new Exchange\DuplicityCurrencyException('Downloaded duplicity: ' . $property->getCode());
			}
			$currencies[$property->getCode()] = $property;
		}
		return $currencies;
	}

	/** @return string */
	public function getName()
	{
		return str_replace(__NAMESPACE__ . '\\', '', get_class($this));
	}

	/**
	 * @param string $url
	 * @param DateTime $date
	 * @return string
	 */
	protected function createUrl($url, DateTime $date = NULL)
	{
		if ($date === NULL || $date->format('Y-m-d') === date('Y-m-d')) {
			return $url;
		}
		return $this->createUrlDay($url, $date);
	}

	/**
	 * Load data for iterator.
	 * @return array
	 */
	abstract protected function loadFromSource(DateTime $date = NULL);

	/**
	 * Modify data before save to cache.
	 * @return Exchange\Currency\Property|NULL
	 */
	abstract protected function createProperty($row);

	/**
	 * @param string $url
	 * @param DateTime $date
	 * @return string
	 */
	abstract protected function createUrlDay($url, DateTime $date);
}
