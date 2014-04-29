<?php

namespace h4kuna\Exchange;

use Nette\Caching\IStorage;
use Nette\Object;

/**
 *
 * @author Milan Matejcek
 */
class StorageFactory extends Object implements IStorageFactory {

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
        return new $class($this->storage, $name);
    }

}
