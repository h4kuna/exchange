<?php declare(strict_types=1);

namespace h4kuna\Exchange\Download;

use DateTimeInterface;
use h4kuna\Exchange\Driver\Source;
use h4kuna\Exchange\RatingList\RatingList;
use Psr\Http\Client\ClientExceptionInterface;

interface SourceDownloadInterface
{

	/**
	 * @throws ClientExceptionInterface
	 */
	function execute(Source $sourceExchange, ?DateTimeInterface $date): RatingList;
}
