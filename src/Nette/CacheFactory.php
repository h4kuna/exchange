<?php

namespace h4kuna\Exchange\Nette;

use h4kuna\Exchange\Storage,
	Nette,
	Nette\Caching;

/**
 *
 * @author Milan Matějček
 */
final class CacheFactory extends Nette\Object implements Storage\IFactory
{

	/** @var Caching\IStorage */
	private $storage;

	/** @var string */
	private $storageClass;

	function __construct(Caching\IStorage $storage, $storageClass)
	{
		$this->storage = $storage;
		$this->storageClass = $storageClass;
	}

	/**
	 * @param string $name
	 * @return Storage\IStock
	 */
	public function create($name)
	{
		$class = $this->storageClass;
		$service = new $class($this->storage, $name);
		if (!($service instanceof Storage\IStock)) {
			throw new ExchangeException('Storage must be instance ' . __NAMESPACE__ . '\IStock.');
		}
		return $service;
	}

}
