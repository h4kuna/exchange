<?php

namespace h4kuna\Exchange\Storage;

use DateTime;
use Nette\DateTime AS ND;
use Nette\Caching;
use h4kuna\Exchange\Currency\IProperty;

class Stock extends Caching\Cache implements IStock {

    /** @var string represent time */
    private $refresh = '15:30';

    /**
     *
     * @return ND
     */
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
     *
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
    }

    public function loadCurrency($code) {
        return $this->load($code);
    }

}
