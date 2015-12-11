<?php

namespace h4kuna\Exchange\Storage;

use h4kuna\Exchange\Currency\IProperty;

/**
 *
 * @author Milan Matějček
 */
interface IStock extends \ArrayAccess
{

	const ALL_CURRENCIES = 'all';

	/** @return ICurrency */
	public function loadCurrency($code);

	/**
	 * Save Currency to cache
	 *
	 * @param IProperty $currency
	 * @return IProperty
	 */
	public function saveCurrency(IProperty $currency);

	/**
	 * Iterative save
	 *
	 * @param array $currencies
	 */
	public function saveCurrencies(array $currencies);

	/**
	 * Refresh time
	 *
	 * @param mixed $hour
	 */
	public function setRefresh($hour);

	/** @return array */
	public function getListCurrencies();
}
