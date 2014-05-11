<?php

namespace h4kuna\Exchange\NoFramework;

use h4kuna\Exchange\Storage\IFactory;

/**
 *
 * @author Milan Matejcek
 */
class CacheFactory implements IFactory {

    private $cacheClass;
    private $temp;

    public function __construct($class, $temp) {
        $this->cacheClass = $class;
        $this->temp = $temp;
    }

    public function create($name) {
        $class = $this->cacheClass;
        return new $class($name, $this->temp);
    }

}
