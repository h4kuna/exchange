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

	public function __construct(Caching\IStorage $storage)
	{
		$this->storage = $storage;
	}

	/**
	 * @param string $name
	 * @return Storage\IStock
	 */
	public function create($name)
	{
		$service = new Cache($this->storage, $name);
		if (!($service instanceof Storage\IStock)) {
			throw new ExchangeException('Storage must be instance ' . __NAMESPACE__ . '\IStock.');
		}
		return $service;
	}

}
