<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use ArrayAccess;
use DateTime;
use DateTimeImmutable;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Exceptions\UnknownCurrencyException;
use IteratorAggregate;

/**
 * @extends IteratorAggregate<string, Property>
 * @extends ArrayAccess<string, Property>
 */
interface RatingListInterface extends IteratorAggregate, ArrayAccess
{

	/**
	 * check currency if exist before use, then error undefined index
	 */
	function get(string $code): Property;


	/**
	 * @throws UnknownCurrencyException
	 */
	function getSafe(string $code): Property;


	function getRequest(): ?DateTimeImmutable;


	function getDate(): DateTimeImmutable;


	function getExpire(): ?DateTime;

}
