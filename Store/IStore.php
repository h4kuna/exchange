<?php

namespace h4kuna\Exchange;

/**
 *
 * @author Milan Matějček
 */
interface IStore {

    /** @return ICurrency */
    public function loadCurrency($code);

    /** @return string code */
    public function loadCode();
}
