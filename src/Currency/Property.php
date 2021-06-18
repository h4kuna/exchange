<?php declare(strict_types=1);

namespace h4kuna\Exchange\Currency;

use h4kuna\DataType\Immutable;

/**
 * @property-read float $home
 * @property-read int $foreign
 * @property-read float $rate
 * @property-read string $code
 */
class Property extends Immutable\Messenger
{

	/**
	 * @param array<string, int|float|string> $data
	 */
	public function __construct(array $data)
	{
		$data['foreign'] = (int) $data['foreign'];
		$data['home'] = (float) $data['home'];
		$data['code'] = (string) $data['code'];
		$data['rate'] = $data['foreign'] ? ($data['home'] / $data['foreign']) : 0;
		parent::__construct($data);
	}


	public function __toString()
	{
		return $this->code;
	}

}
