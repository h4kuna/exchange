<?php declare(strict_types=1);

namespace h4kuna\Exchange\Currency;

use h4kuna\Exchange;
use h4kuna\Number;
use h4kuna\Number\NumberFormat;

class Formats
{

	/** @var Number\NumberFormatFactory */
	private $numberFormatFactory;

	/** @var NumberFormat[] */
	private $formats = [];

	/** @var array */
	private $rawFormats = [];

	/** @var NumberFormat */
	private $default;


	public function __construct(Number\NumberFormatFactory $numberFormatFactory)
	{
		$this->numberFormatFactory = $numberFormatFactory;
	}


	/**
	 * @param array|NumberFormat $setup
	 */
	public function setDefaultFormat($setup): void
	{
		if (is_array($setup)) {
			$setup = $this->numberFormatFactory->createUnit($setup);
		} elseif (!$setup instanceof NumberFormat) {
			throw new Exchange\Exceptions\InvalidState(sprintf('$setup must be array or "%s"', Number\NumberFormat::class));
		}

		$this->default = $setup;
	}


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
