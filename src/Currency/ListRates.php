<?php

namespace h4kuna\Exchange\Currency;

use h4kuna\Exchange;

class ListRates implements \ArrayAccess, \Iterator
{

	/** @var \DateTime */
	private $date;

	/** @var Property[] */
	private $currencies = [];


	public function __construct(\DateTime $date)
	{
		$this->date = $date;
	}


	public function addProperty(Property $property)
	{
		$this->currencies[$property->code] = $property;
	}


	/**
	 * @return Property[]
	 */
	public function getCurrencies()
	{
		return $this->currencies;
	}


	public function getFirst()
	{
		if ($this->currencies === []) {
			throw new Exchange\EmptyExchangeRateException();
		}
		reset($this->currencies);
		return current($this->currencies);
	}


	/**
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}


	public function offsetExists($offset)
	{
		return isset($this->currencies[$offset]);
	}


	public function offsetGet($offset)
	{
		return $this->currencies[$offset];
	}


	public function offsetSet($offset, $value)
	{
		throw new Exchange\FrozenMethodException;
	}


	public function offsetUnset($offset)
	{
		throw new Exchange\FrozenMethodException;
	}


	public function current()
	{
		return current($this->currencies);
	}


	public function next()
	{
		next($this->currencies);
	}


	public function key()
	{
		return key($this->currencies);
	}


	public function valid()
	{
		return isset($this->currencies[$this->key()]);
	}


	public function rewind()
	{
		reset($this->currencies);
	}

}