<?php

namespace h4kuna;

use \Nette\Caching;

abstract class Storage extends Caching\Cache implements IStorage {

    /**
     * one per day
     * time for refresh HH:MM, default is midnight
     * @var string
     */
    protected $hourRefresh;

    /** @var \DateTime */
    private $date;

    public function __construct(Caching\IStorage $storage) {
        parent::__construct($storage, __NAMESPACE__);
    }

    public function setDate(\DateTime $date = NULL) {
        if ($date) {
            $this->date = $date;
            parent::__construct($this->getStorage(), __NAMESPACE__ . self::NAMESPACE_SEPARATOR . $date->format('_Y-m-d'));
        }
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
            if (!$this->date && $key == $default) {
                $dp = array(Caching\Cache::EXPIRATION => new \DateTime('tomorrow'));
                if ($this->hourRefresh) {
                    list($hour, $min) = explode(':', $this->hourRefresh);
                    $dp[Caching\Cache::EXPIRATION]->setTime($hour, $min, 0);
                }
            }
            $next = next($pointer);
            $val['next'] = key($pointer); //use for load all
            $this->save($key, $val, $dp);
        }
    }

}
