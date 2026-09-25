<?php declare(strict_types = 1);

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
	public function get(string $code): CurrencyInterface;

	/**
	 * @throws UnknownCurrencyException
	 */
	public function getSafe(string $code): CurrencyInterface;

	public function getRequest(): ?DateTimeImmutable;

	public function getDate(): DateTimeImmutable;

	public function getExpire(): ?DateTime;

	public function isValid(): bool;

}
