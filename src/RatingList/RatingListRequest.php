<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

final class RatingListRequest implements Accessor
{
	public function __construct(private RatingList $ratingList)
	{
	}


	public function get(): RatingList
	{
		return $this->ratingList;
	}

}
