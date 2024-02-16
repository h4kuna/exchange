<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use DateTimeInterface;
use h4kuna\Exchange\Driver\Cnb\Day;
use h4kuna\Exchange\Driver\Source;
use h4kuna\Exchange\Utils;

/**
 * readonly php 8.2+
 */
final class CacheEntity
{
	public ?DateTimeInterface $date;

	public string $cacheKeyTtl;

	public string $cacheKeyAll;

	public Source $source;


	public function __construct(?DateTimeInterface $date = null, ?Source $source = null)
	{
		$this->source = $source ?? new Day();
		$this->date = $date !== null && Utils::isTodayAndFuture($date, $this->source->getTimeZone()) ? null : $date;

		$cacheKey = self::makeCacheKey($this->source, $this->date);
		$this->cacheKeyTtl = self::joinKey($cacheKey, 'ttl');
		$this->cacheKeyAll = self::joinKey($cacheKey, 'all.v7.1');
	}


	private static function makeCacheKey(Source $source, ?DateTimeInterface $date): string
	{
		$key = $date === null ? '' : $date->format('.' . Utils::DateFormat);
		return str_replace('\\', '.', $source::class) . $key;
	}


	private static function joinKey(string $str1, string $str2): string
	{
		return "$str1.$str2";
	}
}
