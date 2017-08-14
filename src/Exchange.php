<?php

namespace h4kuna\Exchange;

use DateTime;

/**
 * @author Milan Matějček
 * @since 2009-06-22 - version 0.5
 */
class Exchange implements \ArrayAccess, \IteratorAggregate
{

	/** @var Caching\ICache */
	private $cache;

	/** @var Currency\ListRates */
	private $listRates;

	/**
	 * Default currency "from" input
	 * @var Currency\Property
	 */
	private $default;

	/**
	 * Display currency "to" output
	 * @var Currency\Property
	 */
	private $output;

	/** @var Currency\Property[] */
	private $tempRates;

	public function __construct(Caching\ICache $cache)
	{
		$this->cache = $cache;
	}

	/** @return Currency\Property */
	public function getDefault()
	{
		if ($this->default === NULL) {
			$this->default = $this->getListRates()->getFirst();
		}
		return $this->default;
	}

	/** @return Currency\Property */
	public function getOutput()
	{
		if ($this->output === NULL) {
			$this->output = $this->getDefault();
		}
		return $this->output;
	}

	/**
	 * Set default "from" currency.
	 * @param string $code
	 */
	public function setDefault($code)
	{
		$this->default = $this->offsetGet($code);
	}

	/**
	 * @param Driver\ADriver|NULL $driver
	 * @param DateTime|NULL $date
	 * @return static
	 */
	public function setDriver(Driver\ADriver $driver = NULL, DateTime $date = NULL)
	{
		if ($driver === NULL) {
			$driver = new Driver\Cnb\Day();
		}
		$this->listRates = $this->cache->loadListRate($driver, $date);
		if ($this->default) {
			$this->setDefault($this->default->code);
		}
		if ($this->output) {
			$this->setOutput($this->output->code);
		}
		return $this;
	}

	/**
	 * Set currency "to".
	 * @param string $code
	 * @return Currency\Property
	 */
	public function setOutput($code)
	{
		return $this->output = $this->offsetGet($code);
	}

	/**
	 * Transfer number by exchange rate.
	 * @param float|int|string $price number
	 * @param string|NULL
	 * @param string $to
	 * @return float|int
	 */
	public function change($price, $from = NULL, $to = NULL)
	{
		return $this->transfer($price, $from, $to)[0];
	}

	/**
	 * @param int|float $price
	 * @param string $from
	 * @param string $to
	 * @return array
	 */
	public function transfer($price, $from, $to)
	{
		$to = $to === NULL ? $this->getOutput() : $this->offsetGet($to);
		if (((float) $price) === 0.0) {
			return [0, $to];
		}

		$from = $from === NULL ? $this->getDefault() : $this->offsetGet($from);

		if ($to !== $from) {
			$toRate = isset($this->tempRates[$to->code]) ? $this->tempRates[$to->code] : $to->rate;
			$fromRate = isset($this->tempRates[$from->code]) ? $this->tempRates[$from->code] : $from->rate;
			$price *= $fromRate / $toRate;
		}

		return [$price, $to];
	}

	/**
	 * Add history rate for rating
	 * @param string $code
	 * @param float $rate
	 * @return self
	 */
	public function addRate($code, $rate)
	{
		$property = $this->offsetGet($code);
		$this->tempRates[$property->code] = $rate;
		return $this;
	}

	/**
	 * Remove history rating
	 * @param string $code
	 * @return self
	 */
	public function removeRate($code)
	{
		$property = $this->offsetGet($code);
		unset($this->tempRates[$property->code]);
		return $this;
	}

	/**
	 * Load currency property.
	 * @param string|Currency\Property $index
	 * @return Currency\Property
	 */
	public function offsetGet($index)
	{
		$index = strtoupper($index);
		if ($this->getListRates()->offsetExists($index)) {
			return $this->getListRates()->offsetGet($index);
		}
		throw new UnknownCurrencyException('Undefined currency code: "' . $index . '".');
	}

	public function offsetExists($offset)
	{
		return $this->getListRates()->offsetExists(strtoupper($offset));
	}

	public function offsetSet($offset, $value)
	{
		return $this->getListRates()->offsetSet($offset, $value);
	}

	public function offsetUnset($offset)
	{
		return $this->getListRates()->offsetUnset($offset);
	}

	public function getIterator()
	{
		return $this->getListRates();
	}

	/**
	 * @return Currency\ListRates
	 */
	protected function getListRates()
	{
		if ($this->listRates === NULL) {
			$this->setDriver();
		}
		return $this->listRates;
	}

}
