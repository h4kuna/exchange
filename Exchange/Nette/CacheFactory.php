<?php

namespace h4kuna\Exchange\Nette;

use h4kuna\Exchange\ExchangeException;
use Nette\Caching\IStorage;
use Nette\Object;
use h4kuna\Exchange\Storage\IFactory;
use h4kuna\Exchange\Storage\IStock;

/**
 *
 * @author Milan Matejcek
 */
final class CacheFactory extends Object implements IFactory {

    /** @var IStorage */
    private $storage;

    /** @var string */
    private $storageClass;

    public function __construct(IStorage $storage, $storageClass) {
        $this->storage = $storage;
        $this->storageClass = $storageClass;
    }

    /**
     *
     * @param string $name
     * @return IStock
     */
    public function create($name) {
        $class = $this->storageClass;
        $service = new $class($this->storage, $name);
        if (!($service instanceof IStock)) {
            throw new ExchangeException('Storage must be instance ' . __NAMESPACE__ . '\IStock.');
        }
        return $service;
    }

}
