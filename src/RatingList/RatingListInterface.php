<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use ArrayAccess;
use DateTime;
use DateTimeImmutable;
use h4kuna\Exchange\CurrencyInterface;
use h4kuna\Exchange\Exceptions\UnknownCurrencyException;
use IteratorAggregate;

/**
 * @extends IteratorAggregate<string, CurrencyInterface>
 * @extends ArrayAccess<string, CurrencyInterface>
 */
interface RatingListInterface extends IteratorAggregate, ArrayAccess
{

	/**
	 * check currency if exist before use, then error undefined index
	 */
	function get(string $code): CurrencyInterface;


	/**
	 * @throws UnknownCurrencyException
	 */
	function getSafe(string $code): CurrencyInterface;


	function getRequest(): ?DateTimeImmutable;


	function getDate(): DateTimeImmutable;


	function getExpire(): ?DateTime;

	function isValid(): bool;

}
