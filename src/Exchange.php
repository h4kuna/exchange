<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use ArrayAccess;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Exceptions\UnknownCurrencyException;
use h4kuna\Exchange\RatingList\RatingListInterface;
use IteratorAggregate;

/**
 * @since 2009-06-22 - version 0.5
 * @implements IteratorAggregate<string, Property>
 * @implements ArrayAccess<string, Property>
 * properties become readonly
 */
class Exchange implements IteratorAggregate, ArrayAccess
{
	private Property $from;

	private Property $to;


	public function __construct(
		string $from,
		public RatingListInterface $ratingList,
		?string $to = null,
	)
	{
		$this->from = $this->get($from);
		$this->to = $to === null ? $this->from : $this->get($to);
	}


	/**
	 * @throws UnknownCurrencyException
	 */
	public function get(string $code): Property
	{
		return $this->ratingList->getSafe($code);
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
			$price *= $from->rate / $to->rate;
		}

		return (float) $price;
	}


	public function getFrom(?string $from = null): Property
	{
		return $from === null ? $this->from : $this->ratingList->get($from);
	}


	public function getTo(?string $to = null): Property
	{
		return $to === null ? $this->to : $this->ratingList->get($to);
	}


	public function getIterator(): RatingListInterface
	{
		return $this->ratingList;
	}


	public function offsetExists(mixed $offset): bool
	{
		return $this->ratingList->offsetExists($offset);
	}


	public function offsetGet(mixed $offset): Property
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
