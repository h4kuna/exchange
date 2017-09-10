<?php

namespace h4kuna\Exchange\Currency;

/**
 * @property-read float $home
 * @property-read int $foreign
 * @property-read float $rate
 * @property-read string $code
 */
class Property extends \h4kuna\DataType\Immutable\Messenger
{

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
		return (string) $this->code;
	}

}
