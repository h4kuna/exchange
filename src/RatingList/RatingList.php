<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use ArrayIterator;
use DateTime;
use DateTimeImmutable;
use h4kuna\Exchange\CurrencyInterface;
use h4kuna\Exchange\Exceptions\FrozenMethodException;
use h4kuna\Exchange\Exceptions\UnknownCurrencyException;

/**
 * Serializable, remember if you want to rename!
 */
final class RatingList implements RatingListInterface
{
	/**
	 * @param array<string, CurrencyInterface> $properties
	 */
	public function __construct(
		private DateTimeImmutable $date,
		private ?DateTimeImmutable $request, // null is today
		private ?DateTime $expire, // not null is for current
		private array $properties,
	)
	{
	}


	public function getRequest(): ?DateTimeImmutable
	{
		return $this->request;
	}


	/**
	 * @return ArrayIterator<string, CurrencyInterface>
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->properties);
	}


	public function offsetGet(mixed $offset): CurrencyInterface
	{
		return $this->get($offset);
	}


	public function get(string $code): CurrencyInterface
	{
		// no check if exist for fast
		return $this->properties[$code];
	}


	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new FrozenMethodException('deny, readonly');
	}


	public function offsetUnset(mixed $offset): void
	{
		throw new FrozenMethodException('deny, readonly');
	}


	public function getSafe(string $code): CurrencyInterface
	{
		$code = strtoupper($code);
		if ($this->offsetExists($code) === false) {
			throw new UnknownCurrencyException($code === '' ? '[empty string]' : $code);
		}

		return $this->get($code);
	}


	public function offsetExists(mixed $offset): bool
	{
		return isset($this->properties[$offset]);
	}


	public function getDate(): DateTimeImmutable
	{
		return $this->date;
	}


	public function getExpire(): ?DateTime
	{
		return $this->expire;
	}


	public function isValid(): bool
	{
		return $this->expire === null || $this->expire <= new DateTime();
	}

}
