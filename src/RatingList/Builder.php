<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use h4kuna\Exchange\Driver\Driver;

interface Builder
{

	function create(Driver $driver, ?\DateTimeInterface $date = null): RatingList;

}
