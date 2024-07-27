<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use ArrayAccess;
use h4kuna\Exchange\Exceptions\UnknownCurrencyException;
use h4kuna\Exchange\RatingList\RatingListInterface;
use IteratorAggregate;

/**
 * @template T of CurrencyInterface
 * @since 2009-06-22 - version 0.5
 * @implements IteratorAggregate<string, T>
 * @implements ArrayAccess<string, T>
 * properties become readonly
 */
class Exchange implements IteratorAggregate, ArrayAccess
{
	private CurrencyInterface $from;

	private CurrencyInterface $to;


	/**
	 * @param RatingListInterface<T> $ratingList
	 */
	public function __construct(
		string|CurrencyInterface $from,
		public RatingListInterface $ratingList,
		string|CurrencyInterface|null $to = null,
	)
	{
		$this->from = $from instanceof CurrencyInterface ? $from : $this->get($from);
		$this->to = $to === null
			? $this->from
			: ($to instanceof CurrencyInterface ? $to : $this->get($to));
	}


	/**
	 * @return T
	 * @throws UnknownCurrencyException
	 */
	public function get(string $code): CurrencyInterface
	{
		/** @var T $currency */
		$currency = $this->ratingList->getSafe($code);

		return $currency;
	}


	/**
	 * Transfer number by exchange rate.
	 */
	public function change(float|int|null $price, ?string $from = null, ?string $to = null): float
	{
		if ($price == 0) { // intentionally 0, 0.0, null
			return .0;
		}

		$from = $this->getFrom($from);
		$to = $this->getTo($to);
		if ($to !== $from) {
			$price *= $from->getRate() / $to->getRate();
		}

		return (float) $price;
	}


	/**
	 * @return T
	 */
	public function getFrom(?string $from = null): CurrencyInterface
	{
		/** @var T $currency */
		$currency = $from === null ? $this->from : $this->ratingList->get($from);

		return $currency;
	}


	/**
	 * @return T
	 */
	public function getTo(?string $to = null): CurrencyInterface
	{
		/** @var T $currency */
		$currency = $to === null ? $this->to : $this->ratingList->get($to);

		return $currency;
	}


	/**
	 * @return RatingListInterface<T>
	 */
	public function getIterator(): RatingListInterface
	{
		return $this->ratingList;
	}


	public function offsetExists(mixed $offset): bool
	{
		return $this->ratingList->offsetExists($offset);
	}


	/**
	 * @return T
	 */
	public function offsetGet(mixed $offset): CurrencyInterface
	{
		return $this->get($offset);
	}


	public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->ratingList->offsetSet($offset, $value);
	}


	public function offsetUnset(mixed $offset): void
	{
		$this->ratingList->offsetUnset($offset);
	}

}
