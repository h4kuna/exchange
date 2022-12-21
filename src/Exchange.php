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

	public function __construct(private RatingList\Accessor $ratingList, private Configuration $configuration)
	{
	}


	public function getDefault(): Currency\Property
	{
		return $this->ratingList->get()->offsetGet($this->configuration->from);
	}


	public function getOutput(): Currency\Property
	{
		return $this->ratingList->get()->offsetGet($this->configuration->to);
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
		$to = $this->ratingList->get()->offsetGet($to ?? $this->configuration->to);
		if ($price === 0.0) {
			return [0, $to];
		}

		$from = $this->ratingList->get()->offsetGet($from ?? $this->configuration->from);

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
