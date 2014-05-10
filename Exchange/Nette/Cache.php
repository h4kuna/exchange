<?php

namespace h4kuna\Exchange\Nette;

use DateTime;
use Nette\Caching;
use h4kuna\Exchange\Currency\IProperty;
use h4kuna\Exchange\Storage\IStock;

final class Cache extends Caching\Cache implements IStock {

    /** @var string represent time */
    private $refresh = '15:30';

    /** @return DateTime */
    private function getRefresh() {
        if (is_string($this->refresh)) {
            $this->refresh = new DateTime('today ' . $this->refresh);
            if (new DateTime >= $this->refresh) {
                $this->refresh->modify('+1 day');
            }
        }
        return $this->refresh;
    }

    /**
     *
     * @param string $hour
     * @return Storage
     */
    public function setRefresh($hour) {
        $this->refresh = $hour;
        return $this;
    }

    /**
     * @param IProperty $currency
     */
    public function saveCurrency(IProperty $currency) {
        $refresh = $this->getRefresh();
        $expire = NULL;
        if ($refresh) {
            $expire = array(self::EXPIRE => $refresh);
        }

        $this->save($currency->getCode(), $currency, $expire);
    }

    /**
     * 
     * @param array $currencies
     */
    public function saveCurrencies(array $currencies) {
        foreach ($currencies as $currency) {
            $this->saveCurrency($currency);
        }
        $this->save(self::ALL_CURRENCIES, array_keys($currencies));
    }

    public function loadCurrency($code) {
        return $this->load($code);
    }

    /** @return array */
    public function getListCurrencies() {
        return $this->loadCurrency(self::ALL_CURRENCIES);
    }

}
