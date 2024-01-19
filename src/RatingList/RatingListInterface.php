<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use ArrayAccess;
use DateTimeImmutable;
use h4kuna\Exchange\Currency\Property;
use IteratorAggregate;

/**
 * @extends IteratorAggregate<string, Property>
 * @extends ArrayAccess<string, Property>
 */
interface RatingListInterface extends IteratorAggregate, ArrayAccess
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


	function getExpire(): ?DateTimeImmutable;

}
