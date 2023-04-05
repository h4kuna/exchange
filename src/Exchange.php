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
	private Currency\Property $from;

	private Currency\Property $to;


	public function __construct(
		string $from,
		string $to,
		private RatingList\Accessor $ratingList,
	)
	{
		$this->setFrom($from);
		$this->setTo($to);
	}


	public function getFrom(): Currency\Property
	{
		return $this->from;
	}


	public function default(string $to, string $from = ''): static
	{
		$exchange = clone $this;
		if ($from !== '') {
			$exchange->setFrom($from);
		}
		$exchange->setTo($to);

		return $exchange;
	}


	public function getTo(): Currency\Property
	{
		return $this->to;
	}


	/**
	 * @deprecated use getFrom()
	 */
	public function getDefault(): Currency\Property
	{
		return $this->getFrom();
	}


	/**
	 * @deprecated use getTo()
	 */
	public function getOutput(): Currency\Property
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
	 * @return array{float, Currency\Property}
	 */
	public function transfer(float $price, ?string $from = null, ?string $to = null): array
	{
		$to = $to === null ? $this->to : $this->ratingList->get()->offsetGet($to);
		if ($price === 0.0) {
			return [0.0, $to];
		}

		$from = $from === null ? $this->from : $this->ratingList->get()->offsetGet($from);
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


	protected function setFrom(string $from): void
	{
		$this->from = $this->ratingList->get()->offsetGet(strtoupper($from));
	}


	protected function setTo(string $to): void
	{
		$this->to = $this->ratingList->get()->offsetGet(strtoupper($to));
	}

}
