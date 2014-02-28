<?php

namespace h4kuna\Exchange;

use DateTime;
use Nette\DateTime AS ND;
use Nette\Caching;

class Storage extends Caching\Cache implements IStorage {

    /** @var string */
    protected $prefix;

    /** @var string represent time */
    protected $refresh = '14:45';

    public function __construct(Caching\IStorage $storage, $date = NULL) {
        if ($date) {
            $date = '\\' . ND::from($date)->format('Y-m-d');
        }
        parent::__construct($storage, __CLASS__ . $date);
    }

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

    /**
     * 
     * @param string $hour
     * @return Storage
     */
    public function setRefresh($hour) {
        if ($hour === FALSE) {
            $this->refresh = FALSE;
            return $this;
        }
        return $this;
    }

    /**
     * 
     * @param mixed $date
     * @return Storage
     */
    public function setDate($date) {
        $storege = new static($this->getStorage(), $date);
        return $storege->setRefresh(FALSE);
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
     * Prefix for date or driver
     *
     * @param string $str
     * @return Storage
     */
    public function setPrefix($str) {
        if ($str) {
            $this->prefix = $str . '/';
        }
        return $this;
    }

    public function load($key, $fallback = NULL) {
        return parent::load($this->prefix . $key, $fallback);
    }

    public function save($key, $data, array $dependencies = NULL) {
        return parent::save($this->prefix . $key, $data, $dependencies);
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
