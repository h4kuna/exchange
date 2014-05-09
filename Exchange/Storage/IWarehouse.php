<?php

namespace h4kuna\Exchange\Storage;

use DateTime;
use h4kuna\Exchange\Driver\Download;

/**
 *
 * @author Milan Matějček
 */
interface IWarehouse {

    /** @return ICurrency */
    public function loadCurrency($code);

    /**
     * Identification name
     * 
     * @return string 
     */
    public function getName();

    /**
     * Change date for currency
     * 
     * @param DateTime $date
     */
    public function setDate(DateTime $date);

    /**
     * Change driver for currency
     * 
     * @param Download $driver
     */
    public function setDriver(Download $driver);

    /** @return array */
    public function getListCurrencies();
}
