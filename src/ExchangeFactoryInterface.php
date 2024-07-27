<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use h4kuna\Exchange\RatingList\CacheEntity;

/**
 * @template T of CurrencyInterface
 */
interface ExchangeFactoryInterface
{
	/**
	 * @return Exchange<T>
	 */
	public function create(
		?string $from = null,
		?string $to = null,
		?CacheEntity $cacheEntity = null,
	): Exchange;

}
