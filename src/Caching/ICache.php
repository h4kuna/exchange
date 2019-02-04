<?php declare(strict_types=1);

namespace h4kuna\Exchange\Caching;

use h4kuna\Exchange\Currency\ListRates;
use h4kuna\Exchange\Driver;

interface ICache
{

	function loadListRate(Driver\Driver $driver, \DateTimeInterface $date = null): ListRates;


	function flushCache(Driver\Driver $driver, \DateTimeInterface $date = null): void;


	/**
	 * @return static
	 */
	function setAllowedCurrencies(array $allowed);


	/**
	 * @return static
	 */
	function setRefresh(string $hour);

}
