<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Exceptions\FrozenMethodException;
use h4kuna\Exchange\Exceptions\UnknownCurrencyException;

/**
 * @implements \ArrayAccess<string, Property>
 * @implements \Iterator<string, Property>
 */
class RatingList implements \ArrayAccess, \Iterator
{

	/** @var array<string, Property> */
	private array $currencies = [];

	private int $ttl = 0;


	public function __construct(private \DateTimeImmutable $date)
	{
	}


	public function isValid(): bool
	{
		return $this->currencies !== [] && $this->ttl === 0 || $this->ttl >= time();
	}


	public function extendTTL(int $seconds = 300): void
	{
		$this->ttl += $seconds; // default is 5 minutes
	}


	public function setTTL(int $ttl): void
	{
		$this->ttl = $ttl;
	}


	public function addProperty(Property $property): void
	{
		$this->currencies[self::normalizeKey($property->code)] = $property;
	}


	/**
	 * @return array<Property>
	 */
	public function getCurrencies(): array
	{
		return $this->currencies;
	}


	public function getDate(): \DateTimeImmutable
	{
		return $this->date;
	}


	public function offsetExists($offset): bool
	{
		assert($this->currencies !== []);

		return isset($this->currencies[self::normalizeKey($offset)]);
	}


	public function offsetGet($offset): Property
	{
		assert($this->currencies !== []);
		$key = self::normalizeKey($offset);

		if (!isset($this->currencies[$key])) {
			throw new UnknownCurrencyException($key);
		}

		return $this->currencies[$key];
	}


	public function offsetSet($offset, $value): void
	{
		throw new FrozenMethodException(__METHOD__);
	}


	public function offsetUnset($offset): void
	{
		throw new FrozenMethodException(__METHOD__);
	}


	public function current(): Property
	{
		assert($this->currencies !== []);

		return current($this->currencies);
	}


	public function next(): void
	{
		assert($this->currencies !== []);
		next($this->currencies);
	}


	public function key(): mixed
	{
		assert($this->currencies !== []);

		return key($this->currencies);
	}


	public function valid(): bool
	{
		return key($this->currencies) !== null;
	}


	public function rewind(): void
	{
		reset($this->currencies);
	}


	private static function normalizeKey(string $key): string
	{
		return strtolower($key);
	}

}
