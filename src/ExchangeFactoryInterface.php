<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use h4kuna\Exchange\RatingList\CacheEntity;

interface ExchangeFactoryInterface
{
	public function create(
		?string $from = null,
		?string $to = null,
		?CacheEntity $cacheEntity = null,
	): Exchange;

}
