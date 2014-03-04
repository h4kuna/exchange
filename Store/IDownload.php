<?php

namespace h4kuna\Exchange;

/**
 * Description of IDownload
 *
 * @author Milan Matějček
 */
interface IDownload {

    /**
     * Load from remote source and save
     * 
     * @param \DateTime $date
     * @return Currencies
     */
    public function loadCurrencies(IStorage $storage, \DateTime $date);
}
