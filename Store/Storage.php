<?php

namespace h4kuna\Exchange;

use Nette\Caching;

class Storage extends Caching\Cache implements IStorage {

    /** @var \DateTime */
    protected $refresh;

    /** @var string */
    protected $prefix;

    public function __construct(Caching\IStorage $storage, $hour = '14:45') {
        parent::__construct($storage, __CLASS__);

        $this->refresh = new \DateTime('today ' . $hour);
        if (new \DateTime >= $this->refresh) {
            $this->refresh->modify('+1 day');
        }
    }

    /**
     *
     * @param ICurrency $currency
     */
    public function saveCurrency(ICurrencyProperty $currency) {
        $this->save($currency->getCode(), $currency, array(self::EXPIRE => $this->refresh));
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
