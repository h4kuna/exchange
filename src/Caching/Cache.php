<?php declare(strict_types=1);

namespace h4kuna\Exchange\Caching;

use Psr\SimpleCache\CacheInterface;

final class Cache implements CacheInterface
{
	/**
	 * @var array<string, string>
	 */
	private array $keys = [];


	public function __construct(private string $tempDir)
	{
	}


	public function get(string $key, mixed $default = null): mixed
	{
		if ($this->has($key) === false) {
			return $default;
		}

		return file_get_contents($this->file($key));
	}


	public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
	{
		return (bool) file_put_contents($this->file($key), $value);
	}


	public function delete(string $key): bool
	{
		if ($this->has($key)) {
			unlink($this->file($key));
		}

		return true;
	}


	public function clear(): bool
	{
		throw new \RuntimeException('not implemented');
	}


	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		throw new \RuntimeException('not implemented');
	}


	/**
	 * @param iterable<mixed> $values
	 */
	public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
	{
		throw new \RuntimeException('not implemented');
	}


	public function deleteMultiple(iterable $keys): bool
	{
		throw new \RuntimeException('not implemented');
	}


	public function has(string $key): bool
	{
		return is_file($this->file($key));
	}


	private function file(string $key): string
	{
		if (!isset($this->keys[$key])) {
			$this->keys[$key] = $this->tempDir . '/' . md5($key);
		}

		return $this->keys[$key];
	}

}
