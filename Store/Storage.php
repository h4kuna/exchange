<?php

namespace h4kuna\Exchange;

use DateTime;
use Nette\DateTime AS ND;
use Nette\Caching;

class Storage extends Caching\Cache implements IStorage {

    /** @var string represent time */
    protected $refresh = '15:30';

    /**
     *
     * @return ND
     */
    protected function getRefresh() {
        if (is_string($this->refresh)) {
            $this->refresh = new DateTime('today ' . $this->refresh);
            if (new DateTime >= $this->refresh) {
                $this->refresh->modify('+1 day');
            }
        }
        return $this->refresh;
    }

    public function setDriver($name) {
        return new static($this->getStorage(), $name);
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
     * @return Storage
     */
    protected function offRefresh() {
        return $this->setRefresh(FALSE);
    }

    /**
     *
     * @param string $name
     * @return Storage
     */
    public function createStorage($name) {
        return new static($this->getStorage(), $name);
    }

    /**
     *
     * @param ICurrency $currency
     */
    public function saveCurrency(ICurrencyProperty $currency) {
        $refresh = $this->getRefresh();
        $expire = NULL;
        if ($refresh) {
            $expire = array(self::EXPIRE => $refresh);
        }

        $this->save($currency->getCode(), $currency, $expire);
    }

    /**
     *
     * @see IStorage
     */
    public function loadLast() {
        return $this->load(self::LAST);
    }

    /**
     *
     * @see IStorage
     */
    public function saveLast($str) {
        return $this->save(self::LAST, $str);
    }

}
