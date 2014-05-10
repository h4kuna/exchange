<?php

namespace h4kuna\Exchange\Storage;

use DateTime;
use h4kuna\Exchange\Driver\Download;

/**
 *
 * @author Milan Matějček
 */
interface IWarehouse {

    public function __construct(IFactory $factory, Download $download);

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

    /**
     * 
     * @param Download $river
     * @return string
     */
    public function loadNameByDriver(Download $river);

    /**
     * 
     * @param DateTime $date
     * @retrun string
     */
    public function loadNameByDate(DateTime $date);
}
