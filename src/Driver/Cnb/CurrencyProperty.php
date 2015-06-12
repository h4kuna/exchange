<?php

namespace h4kuna\Exchange\Driver\Cnb;

use h4kuna\Exchange\Currency\Property;

/**
 * @author Milan Matějček
 */
class CurrencyProperty extends Property
{

	/** @var string */
	private $country;

	/** @var string */
	private $name;

	public function __construct($foreing, $code, $home, $country, $name)
	{
		parent::__construct($home, $code, $foreing);
		$this->country = $country;
		$this->name = $name;
	}

	/**
	 * Country in czech language
	 *
	 * @return string
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * Name in czech language
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

}
