<?php

namespace Exchange;

use Nette;

/**
 * Description of Storage
 *
 * @author Milan Matějček
 */
abstract class Storage extends Nette\Caching\Cache implements IStorage {

	/**
	 * time for refresh HH:MM:SS
	 * @var string
	 */
	protected $hourRefresh = '00:00:00';


	public function __construct(Nette\Caching\IStorage $storage, \DateTime $date = NULL) {
		$suffix = ($date === NULL)? NULL: $date->format('Y-m-d');
		parent::__construct($storage, __NAMESPACE__ . $suffix);
	}

	public function getAll()
	{
		return $this->offsetGet(self::ALL);
	}
}
