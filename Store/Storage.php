<?php

namespace h4kuna\Exchange;

use DateTime;
use Nette\DateTime AS ND;
use Nette\Caching;

class Storage extends Caching\Cache implements IStorage {

    /** @var string represent time */
    protected $refresh = '15:30';

    /** @var string id of class */
    private $name;
    
    /** @var Download */
    private $download;

    public function __construct(Caching\IStorage $storage, Download $download, $date = 'now') {
        $this->download = $download;
        $this->name = get_class($download);        
        $date = ND::from($date);
        $date->setTime(0, 0, 0);
        $ymd = $date->format('Y-m-d');
        if (date('Y-m-d') > $ymd) {
            $this->name .= '\\' . $ymd;
            $this->offRefresh();
        }
        parent::__construct($storage, $this->name);
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

    public function getName() {
        return $this->name;
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
     * @param mixed $date
     * @return Storage
     */
    public function setDate($date) {
        $storage = new static($this->getStorage(), $this->download, $date);
        if ($storage->getName() == $this->name) {
            return $this;
        }
        return $storage;
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
