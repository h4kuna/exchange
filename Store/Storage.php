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
        if (new \DateTime > $this->refresh) {
            $this->refresh->modify('+1 day');
        }
    }

    /**
     *
     * @param ICurrency $currency
     */
    public function saveCurrency(ICurrencyProperty $currency) {
        $this->save($this->prefix . $currency->getCode(), $currency, array(self::EXPIRE => $this->refresh));
    }

    public function setPrefix($str) {
        if ($str) {
            $this->prefix = $str . '/';
        }
        return $this;
    }

    public function loadLast() {
        return $this->load(self::LAST);
    }

    public function saveLast($str) {
        $this->save(self::LAST, $str);
    }

}
