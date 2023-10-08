<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use ArrayAccess;
use DateTimeImmutable;
use Generator;
use h4kuna\Exchange\Currency\Property;
use h4kuna\Exchange\Exceptions\FrozenMethodException;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<string, Property>
 * @implements ArrayAccess<string, Property>
 */
final class RatingList implements RatingListInterface, IteratorAggregate, ArrayAccess
{
	/**
	 * @var array<string, bool>|null
	 */
	private ?array $all = null;

	private RatingListBuilder $ratingListBuilder;

	private ?DateTimeImmutable $date = null;


	public function __construct(private CacheEntity $cacheEntity, private RatingListCache $ratingListCache)
	{
		$this->ratingListBuilder = new RatingListBuilder();
		$this->ratingListBuilder->setDefault(function (string|int $key): Property {
			$this->getDate(); // init cache
			assert(is_string($key));
			return $this->ratingListCache->currency($this->cacheEntity, $key);
		});
	}


	public function modify(CacheEntity $cacheEntity): self
	{
		return new self($cacheEntity, $this->ratingListCache);
	}


	public function get(string $code): Property
	{
		return $this->ratingListBuilder->get($code);
	}


	public function all(): array
	{
		if ($this->all === null) {
			$this->all = $this->ratingListCache->all($this->cacheEntity);
		}

		return $this->all;
	}


	public function getDate(): DateTimeImmutable
	{
		if ($this->date === null) {
			$this->date = $this->ratingListCache->build($this->cacheEntity);
		}
		return $this->date;
	}


	/**
	 * @return Generator<string, Property>
	 * @deprecated moved to class Exchange
	 */
	public function getIterator(): Generator
	{
		foreach ($this->all() as $code => $exists) {
			yield $code => $this->get($code);
		}
	}


	/**
	 * @deprecated moved to class Exchange
	 */
	public function offsetExists(mixed $offset): bool
	{
		return isset($this->all()[$offset]);
	}


	/**
	 * @deprecated moved to class Exchange
	 */
	public function offsetGet(mixed $offset): Property
	{
		return $this->get($offset);
	}


	/**
	 * @deprecated moved to class Exchange
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new FrozenMethodException('not supported');
	}


	/**
	 * @deprecated moved to class Exchange
	 */
	public function offsetUnset(mixed $offset): void
	{
		throw new FrozenMethodException('not supported');
	}

}
