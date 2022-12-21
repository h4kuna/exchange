<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use h4kuna\Exchange\Driver\Driver;

final class RatingListLongProcess implements Accessor
{
	private ?RatingList $ratingList = null;


	/**
	 * Probably you don't need cache for param RatingList, you can choose RatingListBuilder directly
	 */
	public function __construct(private Builder $factory, private Driver $driver)
	{
	}


	public function get(): RatingList
	{
		if ($this->ratingList === null || $this->ratingList->isValid() === false) {
			$this->ratingList = $this->factory->create($this->driver);
		}

		return $this->ratingList;
	}

}
