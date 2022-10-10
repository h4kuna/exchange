<?php declare(strict_types=1);

namespace h4kuna\Exchange\Currency;

use h4kuna\Exchange;
use h4kuna\Exchange\Exceptions\FrozenMethod;

/**
 * @implements \ArrayAccess<string, Property>
 * @implements \Iterator<string, Property>
 */
class ListRates implements \ArrayAccess, \Iterator
{

	/** @var \DateTimeInterface */
	private $date;

	/** @var array<string, Property> */
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


	#[\ReturnTypeWillChange]
	public function offsetExists($offset)
	{
		assert($this->currencies !== []);
		return isset($this->currencies[$offset]);
	}


	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		assert($this->currencies !== []);
		return $this->currencies[$offset];
	}


	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value)
	{
		throw new FrozenMethod(__METHOD__);
	}


	#[\ReturnTypeWillChange]
	public function offsetUnset($offset)
	{
		throw new FrozenMethod(__METHOD__);
	}

	#[\ReturnTypeWillChange]
	public function current()
	{
		assert($this->currencies !== []);
		return current($this->currencies);
	}


	#[\ReturnTypeWillChange]
	public function next()
	{
		assert($this->currencies !== []);
		next($this->currencies);
	}


	#[\ReturnTypeWillChange]
	public function key()
	{
		assert($this->currencies !== []);
		return key($this->currencies);
	}


	#[\ReturnTypeWillChange]
	public function valid()
	{
		return isset($this->currencies[$this->key()]);
	}


	#[\ReturnTypeWillChange]
	public function rewind()
	{
		reset($this->currencies);
	}

}
