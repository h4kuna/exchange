<?php

namespace h4kuna;

use \Nette\Caching;

require_once 'IStorage.php';

class Storage extends Caching\Cache implements IStorage {

    /**
     * one per day
     * time for refresh HH:MM
     * @var string
     */
    protected $hourRefresh;

    public function __construct(Caching\IStorage $storage, $hour = '14:45') {
        parent::__construct($storage, __CLASS__);
        $this->hourRefresh = $hour;
    }

    /**
     *
     * @param array $data
     * @param string $default currency
     */
    public function import(array $data, $default) {
        if (isset($data[0])) {
            $this->save(0, $data[0]);
            unset($data[0]);
        }

        //default set first
        $def = $data[$default];
        unset($data[$default]);
        $pointer = $data = array($default => $def) + $data;

        foreach ($data as $key => $val) {
            $dp = NULL;
            if (!($val instanceof Currency)) {
                throw new ExchangeException('Must be class Currency.');
            }
            if ($key == $default) {
                $dt = new \DateTime('tomorrow');
                if ($this->hourRefresh) {
                    list($hour, $min) = explode(':', $this->hourRefresh);
                    $dt->setTime($hour, $min, 0);
                }
                $dp = array(Caching\Cache::EXPIRATION => $dt);
            }
            next($pointer);
            $val->next = key($pointer); //use for load all
            $this->save($key, $val, $dp);
        }
    }

}
