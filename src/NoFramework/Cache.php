<?php

use h4kuna\Exchange\Currency\IProperty,
	h4kuna\Exchange\Storage\IStock;

namespace h4kuna\Exchange\NoFramework;

/**
 *
 * @author Milan Matějček
 */
class Cache implements IStock
{

    /** @var string */
    private $path;
    private $refresh = '15:30';

    public function __construct($name, $temp)
    {
        $this->path = $temp . DIRECTORY_SEPARATOR . str_replace('\\', '-', $name) . DIRECTORY_SEPARATOR;
        @mkdir($this->path, 0777, TRUE);
    }

    public function getListCurrencies()
    {
        return $this->offsetGet(self::ALL_CURRENCIES);
    }

    public function loadCurrency($code)
    {
        return $this->offsetGet($code);
    }

    public function saveCurrencies(array $currencies)
    {
        foreach ($currencies as $currency) {
            $this->saveCurrency($currency);
        }
        $this->offsetSet(self::ALL_CURRENCIES, array_keys($currencies));
    }

    public function saveCurrency(IProperty $currency)
    {
        $this->offsetSet($currency->getCode(), $currency);
        return $currency;
    }

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
     *
     * @param string $hour
     * @return Storage
     */
    public function setRefresh($hour)
    {
        $this->refresh = $hour;
        return $this;
    }

    public function offsetExists($offset)
    {
        return is_file($this->path . $offset) && (!$this->getRefresh() || date(DateTime::ISO8601, filemtime($this->path . $offset)) < $this->refresh->format(DateTime::ISO8601));
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return unserialize(file_get_contents($this->path . $offset));
        }
        return NULL;
    }

    public function offsetSet($offset, $value)
    {
        file_put_contents($this->path . $offset, serialize($value));
    }

    public function offsetUnset($offset)
    {
        @unlink($this->path . $offset);
    }

//put your code here
}
