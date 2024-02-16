<?php declare(strict_types=1);

namespace h4kuna\Exchange\Download;

use DateTimeImmutable;

final class SourceData
{
	/**
	 * @param iterable<mixed> $properties
	 */
	public function __construct(
		public DateTimeImmutable $date,
		public string $refresh,
		public iterable $properties,
	)
	{
	}

}
