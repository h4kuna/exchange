<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver;

use DateTime;
use h4kuna\Exchange;

/**
 * Download currency from server.
 */
abstract class Driver
{

	/** @var \DateTimeInterface */
	private $date;


	/**
	 * Download data from remote source and save.
	 * @param array<string> $allowedCurrencies
	 */
	public function download(?\DateTimeInterface $date = null, array $allowedCurrencies = []): Exchange\Currency\ListRates
	{
		$allowedCurrencies = array_flip($allowedCurrencies);
		$source = $this->loadFromSource($date);
		$currencies = new Exchange\Currency\ListRates($this->getDate());
		foreach ($source as $row) {
			if (!$row) {
				continue;
			}
			$property = $this->createProperty($row);

			if ($property->rate === 0.0 || ($allowedCurrencies !== [] && !isset($allowedCurrencies[$property->code]))) {
				continue;
			}
			$currencies->addProperty($property);
		}
		$currencies->getFirst(); // check if is not empty
		return $currencies;
	}


	protected function setDate(string $format, string $value): void
	{
		$date = DateTime::createFromFormat($format, $value);
		if ($date === false) {
			throw new Exchange\Exceptions\InvalidState(sprintf('Can not create DateTime object from source "%s" with format "%s".', $value, $format));
		}
		$this->date = $date;
		$this->date->setTime(0, 0, 0);
	}


	public function getName(): string
	{
		return strtolower(str_replace('\\', '.', static::class));
	}


	public function getDate(): \DateTimeInterface
	{
		return $this->date;
	}


	/**
	 * Load data for iterator.
	 */
	abstract protected function loadFromSource(?\DateTimeInterface $date): iterable;


	/**
	 * Modify data before save to cache.
	 * @param mixed $row
	 * @return Exchange\Currency\Property
	 */
	abstract protected function createProperty($row);

}
