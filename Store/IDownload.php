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
     * @return Currencies
     */
    public function loadCurrencies(IStorage $storage);

    /**
     * Prefix for Storage
     *
     * @return string
     */
    public function getPrefix();
}
