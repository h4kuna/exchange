<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use DateTimeInterface;

/**
 * readonly php 8.2+
 */
final class CacheEntity
{
	public ?DateTimeInterface $date;

	public string $cacheKey;

	public string $cacheKeyTtl;

	public string $cacheKeyAll;


	public function __construct(?DateTimeInterface $date, public string $driver)
	{
		if ($date !== null && $date->format('Y-m-d') >= date('Y-m-d')) {
			$date = null;
		}

		$this->date = $date;
		$this->cacheKey = $this->makeCacheKey();
		$this->cacheKeyTtl = self::joinKey($this->cacheKey, 'ttl');
		$this->cacheKeyAll = self::joinKey($this->cacheKey, 'all');
	}


	public function keyCode(string $code): string
	{
		return self::joinKey($this->cacheKey, $code);
	}


	private function makeCacheKey(): string
	{
		$key = $this->date === null ? '' : $this->date->format('.Y-m-d');
		return str_replace('\\', '.', $this->driver) . $key;
	}


	private static function joinKey(string $str1, string $str2): string
	{
		return "$str1.$str2";
	}
}
