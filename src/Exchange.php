<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use h4kuna\Exchange\RatingList;

/**
 * @author Milan Matějček
 * @since 2009-06-22 - version 0.5
 * @implements \IteratorAggregate<string, Currency\Property>
 */
class Exchange implements \IteratorAggregate
{

	public function __construct(
		private string $from,
		private string $to,
		private RatingList\Accessor $ratingList,
	)
	{
	}


	public function getFrom(): string
	{
		return $this->from;
	}


	public function default(string $to, string $from = null): static
	{
		$exchange = clone $this;
		$exchange->from = strtoupper($from ?? $this->from);
		$exchange->to = strtoupper($to);

		return $exchange;
	}


	public function getTo(): string
	{
		return $this->to;
	}


	public function getDefault(): Currency\Property
	{
		return $this->ratingList->get()->offsetGet($this->from);
	}


	public function getOutput(): Currency\Property
	{
		return $this->ratingList->get()->offsetGet($this->to);
	}


	/**
	 * Transfer number by exchange rate.
	 */
	public function change(float $price, ?string $from = null, ?string $to = null): float
	{
		return $this->transfer($price, $from, $to)[0];
	}


	/**
	 * @return array{float, Currency\Property}
	 */
	public function transfer(float $price, ?string $from = null, ?string $to = null): array
	{
		$to = $this->ratingList->get()->offsetGet($to ?? $this->to);
		if ($price === 0.0) {
			return [0, $to];
		}

		$from = $this->ratingList->get()->offsetGet($from ?? $this->from);
		if ($to !== $from) {
			$price *= $from->rate / $to->rate;
		}

		return [$price, $to];
	}


	/**
	 * @return RatingList\RatingList
	 */
	public function getIterator(): \Traversable
	{
		return $this->getRatingList();
	}


	public function getRatingList(): RatingList\RatingList
	{
		return $this->ratingList->get();
	}

}
