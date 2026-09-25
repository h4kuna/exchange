<?php declare(strict_types = 1);

namespace h4kuna\Exchange\Driver;

use DateTimeInterface;
use DateTimeZone;
use h4kuna\Exchange\CurrencyInterface;
use h4kuna\Exchange\Download\SourceData;
use Psr\Http\Message\ResponseInterface;

interface Source
{

	public function makeUrl(?DateTimeInterface $date): string;

	public function getTimeZone(): DateTimeZone;

	public function createSourceData(ResponseInterface $response): SourceData;

	public function createProperty(mixed $row): CurrencyInterface;

}
