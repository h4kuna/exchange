<?php declare(strict_types=1);

namespace h4kuna\Exchange\Driver;

use DateTimeInterface;
use DateTimeZone;
use h4kuna\Exchange\CurrencyInterface;
use h4kuna\Exchange\Download\SourceData;
use Psr\Http\Message\ResponseInterface;

interface Source
{
	function makeUrl(?DateTimeInterface $date): string;


	function getTimeZone(): DateTimeZone;


	function createSourceData(ResponseInterface $response): SourceData;


	function createProperty(mixed $row): CurrencyInterface;

}
