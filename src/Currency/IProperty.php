<?php

namespace h4kuna\Exchange\Currency;

use h4kuna\Number;

/**
 * @author Milan Matějček
 */
interface IProperty
{

	/** @return int */
	public function getHome();

	/** @return string */
	public function getCode();

	/** @return float */
	public function getForeing();

	/** @return float */
	public function getRate();

	/**
	 * Set how render currency
	 * @param Number\INumberFormat $nf
	 * @return self
	 */
	public function setFormat(Number\INumberFormat $nf);

	/** @return INumberFormat */
	public function getFormat();

	/**
	 * Set history rate
	 * @param float $number
	 * @return self
	 */
	public function pushRate($number);

	/**
	 * @return self
	 */
	public function popRate();

	/**
	 * Set last value in stack and clear stack
	 * @return self
	 */
	public function revertRate();

	/**
	 * Default currency for count rate
	 * @return self
	 */
	// public function setDefault($property);
}
