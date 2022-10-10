<?php declare(strict_types=1);

namespace h4kuna\Exchange;

use h4kuna\Exchange\Exceptions\UnknownCurrency;

/**
 * @author Milan Matějček
 * @since 2009-06-22 - version 0.5
 * @implements \ArrayAccess<string, Currency\Property>
 * @implements \IteratorAggregate<string, Currency\Property>
 */
class Exchange implements \ArrayAccess, \IteratorAggregate
{

	/** @var Caching\ICache */
	private $cache;

	/** @var Currency\ListRates */
	private $listRates;

	/**
	 * Default currency "from" input
	 * @var Currency\Property
	 */
	private $default;

	/**
	 * Display currency "to" output
	 * @var Currency\Property
	 */
	private $output;

	/** @var array<string, float> */
	private $tempRates;


	public function __construct(Caching\ICache $cache)
	{
		$this->cache = $cache;
	}


	public function getDefault(): Currency\Property
	{
		if ($this->default === null) {
			$this->default = $this->getListRates()->getFirst();
		}
		return $this->default;
	}


	public function getOutput(): Currency\Property
	{
		if ($this->output === null) {
			$this->output = $this->getDefault();
		}
		return $this->output;
	}


	/**
	 * Set default "from" currency.
	 */
	public function setDefault(string $code): void
	{
		$this->default = $this->offsetGet($code);
	}


	/**
	 * @return static
	 */
	public function setDriver(?Driver\Driver $driver = null, ?\DateTimeInterface $date = null)
	{
		if ($driver === null) {
			$driver = new Driver\Cnb\Day();
		}
		$this->listRates = $this->cache->loadListRate($driver, $date);
		if ($this->default !== null) {
			$this->setDefault($this->default->code);
		}
		if ($this->output !== null) {
			$this->setOutput($this->output->code);
		}
		return $this;
	}


	/**
	 * Set currency "to".
	 */
	public function setOutput(string $code): Currency\Property
	{
		return $this->output = $this->offsetGet($code);
	}


	/**
	 * Transfer number by exchange rate.
	 */
	public function change(float $price, ?string $from = null, ?string $to = null): float
	{
		return $this->transfer($price, $from, $to)[0];
	}


	/**
	 * @return array{float, Currency\Property}
	 */
	public function transfer(float $price, ?string $from, ?string $to): array
	{
		$to = $to === null ? $this->getOutput() : $this->offsetGet($to);
		if ($price === 0.0) {
			return [0, $to];
		}

		$from = $from === null ? $this->getDefault() : $this->offsetGet($from);

		if ($to !== $from) {
			$toRate = $this->tempRates[$to->code] ?? $to->rate;
			$fromRate = $this->tempRates[$from->code] ?? $from->rate;
			$price *= $fromRate / $toRate;
		}

		return [$price, $to];
	}


	/**
	 * Add history rate for rating
	 * @return static
	 */
	public function addRate(string $code, float $rate)
	{
		$property = $this->offsetGet($code);
		$this->tempRates[$property->code] = $rate;
		return $this;
	}


	/**
	 * Remove history rating
	 * @return static
	 */
	public function removeRate(string $code)
	{
		$property = $this->offsetGet($code);
		unset($this->tempRates[$property->code]);
		return $this;
	}


	/**
	 * Load currency property.
	 * @param string|Currency\Property $index
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($index): Currency\Property
	{
		$index = strtoupper((string) $index);
		if ($this->getListRates()->offsetExists($index)) {
			$property = $this->getListRates()->offsetGet($index);
			assert($property !== null);
			return $property;
		}
		throw new UnknownCurrency(sprintf('Undefined currency code: "%s".', $index));
	}


	#[\ReturnTypeWillChange]
	public function offsetExists($offset)
	{
		return $this->getListRates()->offsetExists(strtoupper($offset));
	}


	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value): void
	{
		$this->getListRates()->offsetSet($offset, $value);
	}


	#[\ReturnTypeWillChange]
	public function offsetUnset($offset): void
	{
		$this->getListRates()->offsetUnset($offset);
	}


	#[\ReturnTypeWillChange]
	public function getIterator()
	{
		return $this->getListRates();
	}


	protected function getListRates(): Currency\ListRates
	{
		if ($this->listRates === null) {
			$this->setDriver();
		}
		return $this->listRates;
	}

}
