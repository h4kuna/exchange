<?php

namespace h4kuna\Exchange;

/**
 *
 * @author Milan Matejcek
 */
interface IStorageFactory {

    /**
     * @param string $name
     * @return Storage
     */
    public function create($name);
}
