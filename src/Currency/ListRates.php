<?php declare(strict_types=1);

namespace h4kuna\Exchange\Currency;

use h4kuna\Exchange;
use h4kuna\Exchange\Exceptions\FrozenMethod;

class ListRates implements \ArrayAccess, \Iterator
{

	/** @var \DateTimeInterface */
	private $date;

	/** @var Property[] */
	private $currencies = [];


	public function __construct(\DateTimeInterface $date)
	{
		$this->date = $date;
	}


	public function addProperty(Property $property): void
	{
		$this->currencies[$property->code] = $property;
	}


	/**
	 * @return Property[]
	 */
	public function getCurrencies(): array
	{
		return $this->currencies;
	}


	public function getFirst(): Property
	{
		if ($this->currencies === []) {
			throw new Exchange\Exceptions\EmptyExchangeRate();
		}
		return reset($this->currencies);
	}


	public function getDate(): \DateTimeInterface
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
		throw new FrozenMethod(__METHOD__);
	}


	public function offsetUnset($offset)
	{
		throw new FrozenMethod(__METHOD__);
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
