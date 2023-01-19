<?php declare(strict_types=1);

namespace h4kuna\Exchange\Currency;

class Property
{
	public float $rate;


	public function __construct(
		public int $foreign,
		public float $home,
		public string $code,
	)
	{
		$this->rate = $this->foreign === 0 ? 0.0 : $this->home / $this->foreign;
	}


	public function __toString()
	{
		return $this->code;
	}


	/**
	 * @return array<string, bool|float|int|string>
	 */
	public function __serialize(): array
	{
		/** @var array<string, bool|float|int|string> $data */
		$data = get_object_vars($this);

		return $data;
	}


	/**
	 * @param array<string, bool|float|int|string> $data
	 */
	public function __unserialize(array $data): void
	{
		foreach ($data as $name => $value) {
			$this->$name = $value;
		}
	}

}
