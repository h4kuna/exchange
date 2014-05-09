<?php

namespace h4kuna\Exchange\Storage;

use h4kuna\Exchange\ExchangeException;
use Nette\Caching\IStorage;
use Nette\Object;

/**
 *
 * @author Milan Matejcek
 */
class Factory extends Object implements IFactory {

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
     * @return Storage
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
