<?php

namespace h4kuna\Exchange\Storage;

/**
 *
 * @author Milan Matejcek
 */
interface IFactory
{

    /**
     * @param string $name
     * @return IStock
     */
    public function create($name);
}
