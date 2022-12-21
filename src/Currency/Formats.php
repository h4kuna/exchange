<?php declare(strict_types=1);

namespace h4kuna\Exchange\Currency;

use h4kuna\Exchange;
use h4kuna\Number;
use h4kuna\Number\NumberFormat;

class Formats
{
	/** @var array<NumberFormat> */
	private array $formats = [];

	/** @var array<string, array<string, bool|int|string|null>> */
	private array $rawFormats = [];

	private ?NumberFormat $default = null;


	public function __construct(private Number\NumberFormatFactory $numberFormatFactory)
	{
	}


	/**
	 * @param array<string, bool|int|string|null>|NumberFormat $setup
	 */
	public function setDefaultFormat(array|NumberFormat $setup): void
	{
		if ($this->default !== null) {
			throw new Exchange\Exceptions\InvalidStateException('Default format could be setup only onetime.');
		}

		if (is_array($setup)) {
			$setup = $this->numberFormatFactory->createUnit($setup);
		}

		$this->default = $setup;
	}


	/**
	 * @param array<string, string|bool|int|null> $setup
	 */
	public function addFormat(string $code, array $setup): void
	{
		$code = strtoupper($code);
		$this->rawFormats[$code] = $setup;
		unset($this->formats[$code]);
	}


	public function getFormat(string $code): NumberFormat
	{
		if (isset($this->formats[$code])) {
			return $this->formats[$code];
		} elseif (isset($this->rawFormats[$code])) {
			if (isset($this->rawFormats[$code]['unit'])) {
				$format = $this->numberFormatFactory->createUnitPersistent('', $this->rawFormats[$code]); // first parameter is ignored
			} else {
				$format = $this->numberFormatFactory->createUnit($this->rawFormats[$code]);
			}
			$this->formats[$code] = $format;
			unset($this->rawFormats[$code]);
		} else {
			$this->formats[$code] = $this->getDefaultFormat();
		}

		return $this->formats[$code];
	}


	private function getDefaultFormat(): NumberFormat
	{
		if ($this->default === null) {
			$this->default = $this->numberFormatFactory->createUnit();
		}

		return $this->default;
	}

}
