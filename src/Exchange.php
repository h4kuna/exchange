<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use DateTimeImmutable;
use Generator;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Exceptions\FrozenMethodException;
use h4kuna\Exchange\RatingList;
use h4kuna\Exchange\RatingList\CacheEntity;

/**
 * @since 2009-06-22 - version 0.5
 * @implements \IteratorAggregate<string, Property>
 * @implements \ArrayAccess<string, Property>
 */
class Exchange implements \IteratorAggregate, \ArrayAccess
{
	private Property $from;

	private Property $to;


	public function __construct(
		string|Property $from,
		private RatingList\RatingListInterface $ratingList,
		string|Property|null $to = null,
	)
	{
		$this->setFrom($from);
		if ($to === null) {
			$this->to = $this->from;
		} else {
			$this->setTo($to);
		}
	}


	public function getFrom(): Property
	{
		return $this->from;
	}


	public function modify(?string $to = null, ?string $from = null, ?CacheEntity $cacheEntity = null): static
	{
		$exchange = clone $this;
		if ($cacheEntity !== null) {
			$exchange->ratingList = $this->ratingList->modify($cacheEntity);
		}

		$exchange->setFrom($from ?? $this->from->code);
		$exchange->setTo($to ?? $this->to->code);

		return $exchange;
	}


	public function getTo(): Property
	{
		return $this->to;
	}


	/**
	 * @deprecated use getFrom()
	 */
	public function getDefault(): Property
	{
		return $this->getFrom();
	}


	/**
	 * @deprecated use getTo()
	 */
	public function getOutput(): Property
	{
		return $this->getTo();
	}


	/**
	 * Transfer number by exchange rate.
	 */
	public function change(float $price, ?string $from = null, ?string $to = null): float
	{
		return $this->transfer($price, $from, $to)[0];
	}


	/**
	 * @return array{float, Property}
	 */
	public function transfer(float $price, ?string $from = null, ?string $to = null): array
	{
		$to = $to === null ? $this->to : $this->ratingList->get($to);
		if ($price === 0.0) {
			return [0.0, $to];
		}

		$from = $from === null ? $this->from : $this->ratingList->get($from);
		if ($to !== $from) {
			$price *= $from->rate / $to->rate;
		}

		return [$price, $to];
	}


	/**
	 * @return Generator<string, Property>
	 */
	public function getIterator(): Generator
	{
		foreach ($this->ratingList->all() as $code => $exists) {
			yield $code => $this->ratingList->get($code);
		}
	}


	public function offsetExists(mixed $offset): bool
	{
		return isset($this->ratingList->all()[$offset]);
	}


	public function offsetGet(mixed $offset): Property
	{
		return $this->ratingList->get($offset);
	}


	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new FrozenMethodException('not supported');
	}


	public function offsetUnset(mixed $offset): void
	{
		throw new FrozenMethodException('not supported');
	}


	/**
	 * @deprecated method will remove
	 */
	public function getRatingList(): RatingList\RatingList
	{
		assert($this->ratingList instanceof RatingList\RatingList);
		return $this->ratingList;
	}


	public function getDate(): DateTimeImmutable
	{
		return $this->ratingList->getDate();
	}


	protected function setFrom(string|Property $from): void
	{
		$this->from = $from instanceof Property ? $from : $this->ratingList->get(strtoupper($from));
	}


	public function setTo(string|Property $to): void
	{
		$this->to = $to instanceof Property ? $to : $this->ratingList->get(strtoupper($to));
	}

}
