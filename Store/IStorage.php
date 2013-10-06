<?php

namespace h4kuna\Exchange;

/**
 *
 * @author Milan Matějček
 */
interface IStorage {

    const LAST = 'last_code';

    /**
     * @return ICurrency
     */
    public function load($code);

    /**
     * Save Currency to cache
     *
     * @param ICurrencyProperty $currency
     * @return ICurrencyProperty
     */
    public function saveCurrency(ICurrencyProperty $currency);

    /**
     * ???
     *
     * @param string $str
     * @return ICurrencyProperty
     */
    public function setPrefix($str);

    /**
     * Use for reference to last currency for loadAll
     *
     * @param string $str
     * @return ICurrencyProperty
     */
    public function saveLast($str);

    /**
     * Code of last currency
     *
     * @return string
     */
    public function loadLast();
}
