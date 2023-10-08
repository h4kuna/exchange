<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use DateTimeImmutable;
use h4kuna\Exchange\Currency\Property;

interface RatingListInterface
{
	/**
	 * @return self - clone or new object
	 */
	function modify(CacheEntity $cacheEntity): self;


	function get(string $code): Property;


	/**
	 * @return array<string, bool>
	 */
	function all(): array;


	function getDate(): DateTimeImmutable;

}
