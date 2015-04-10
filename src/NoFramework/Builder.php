<?php

namespace h4kuna\Exchange\NoFramework;

use h4kuna\Exchange\Driver\Cnb\Day,
    h4kuna\Exchange\Exchange,
    h4kuna\Exchange\Storage\Warehouse;

/**
 *
 * @author Milan Matějček
 */
class Builder
{

    /** @var Exchange */
    private $exchange;

    /** @var string */
    private $temp;

    /** @var bool */
    private $in;

    /** @var bool */
    private $out;

    /** @var float */
    private $vat;

    public function __construct($temp, $vat, $in = FALSE, $out = FALSE)
    {
        $this->temp = $temp;
        $this->vat = $vat;
        $this->in = $in;
        $this->out = $out;
    }

    /**
     *
     * @return Exchange
     */
    public function create()
    {
        if ($this->exchange !== NULL) {
            return $this->exchange;
        }

        $this->exchange = new Exchange($this->createWarehouse(), $this->createRequestManager());
        $this->exchange->setVat($this->vat, $this->in, $this->out);
        return $this->exchange;
    }

    protected function createRequestManager()
    {
        return new RequestManager();
    }

    protected function createDriver()
    {
        return new Day;
    }

    protected function createWarehouse()
    {
        return new Warehouse($this->createFactoryCache(), $this->createDriver());
    }

    protected function createFactoryCache()
    {
        return new CacheFactory(__NAMESPACE__ . '\Cache', $this->temp);
    }

}
