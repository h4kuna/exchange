<?php

namespace h4kuna\Exchange\Storage;

/**
 * @author Milan Matějček
 */
interface IRequestManager
{

	/**
	 * Currency param in url
	 *
	 * @return string
	 */
	public function getParamCurrency();

	/**
	 * VAT param in url
	 *
	 * @return string
	 */
	public function getParamVat();

	/**
	 *
	 * @param bool $value
	 * @return void
	 */
	public function setSessionVat($value);

	/**
	 *
	 * @param string $code
	 * @return void
	 */
	public function setSessionCurrency($code);

	/**
	 *
	 * @param bool $default
	 * @return bool
	 */
	public function loadParamVat($default);

	/**
	 *
	 * @param string $code
	 * @return bool
	 */
	public function loadParamCurrency($code);
}
