<?php

namespace h4kuna\Exchange\Nette;

use DateTime,
	h4kuna\Exchange\Currency,
	h4kuna\Exchange\Storage,
	Nette\Caching;

final class Cache extends Caching\Cache implements Storage\IStock
{

	/** @var string represent time */
	private $refresh = '15:30';

	/** @return DateTime */
	private function getRefresh()
	{
		if (is_string($this->refresh)) {
			$this->refresh = new DateTime('today ' . $this->refresh);
			if (new DateTime >= $this->refresh) {
				$this->refresh->modify('+1 day');
			}
		}
		return $this->refresh;
	}

	/**
	 * @param string $hour
	 * @return Storage
	 */
	public function setRefresh($hour)
	{
		$this->refresh = $hour;
		return $this;
	}

	/**
	 * @param Currency\IProperty $currency
	 */
	public function saveCurrency(Currency\IProperty $currency)
	{
		$refresh = $this->getRefresh();
		$expire = NULL;
		if ($refresh) {
			$expire = [self::EXPIRE => $refresh];
		}

		$this->save($currency->getCode(), $currency, $expire);
	}

	/**
	 * @param array $currencies
	 */
	public function saveCurrencies(array $currencies)
	{
		foreach ($currencies as $currency) {
			$this->saveCurrency($currency);
		}
		$this->save(self::ALL_CURRENCIES, array_keys($currencies));
	}

	public function loadCurrency($code)
	{
		return $this->load($code);
	}

	/** @return array */
	public function getListCurrencies()
	{
		return $this->loadCurrency(self::ALL_CURRENCIES);
	}

	/** Nette 2.4 interface fix **/

    public function offsetSet($key, $data)
	{
		$this->save($key, $data);
	}

	public function offsetGet($key)
	{
		return $this->load($key);
	}

	public function offsetExists($key)
	{
        return $this->load($key);
	}

    public function offsetUnset($key)
	{
        return $this->remove($key);
    }

}
