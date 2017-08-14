<?php

namespace h4kuna\Exchange\Caching;

use h4kuna\Exchange\Currency,
	h4kuna\Exchange\Driver,
	Nette\Utils;

class Cache implements ICache
{
	const FILE_CURRENT = 'current';

	/** @var string */
	private $temp;

	/** @var Currency\ListRates[] */
	private $listRates;

	/** @var array */
	private $allowedCurrencies = [];

	private $refresh = '15:00';

	public function __construct($temp)
	{
		$this->temp = $temp;
	}

	public function loadListRate(Driver\ADriver $driver, \DateTime $date = NULL)
	{
		$file = $this->createFileInfo($driver, $date);
		if (isset($this->listRates[$file->getPathname()])) {
			return $this->listRates[$file->getPathname()];
		}
		return $this->listRates[$file->getPathname()] = $this->createListRate($driver, $file, $date);
	}

	public function flushCache(Driver\ADriver $driver, \DateTime $date = NULL)
	{
		$file = $this->createFileInfo($driver, $date);
		$file->isFile() && unlink($file->getPathname());
	}

	/**
	 * @param array $allowedCurrencies
	 */
	public function setAllowedCurrencies(array $allowedCurrencies)
	{
		$this->allowedCurrencies = $allowedCurrencies;
	}

	/**
	 * @param string $hour
	 * @return Storage
	 */
	public function setRefresh($hour)
	{
		$this->refresh = $hour;
		return $this;
	}

	private function saveCurrencies(Driver\ADriver $driver, \SplFileInfo $file, \DateTime $date = NULL)
	{
		Utils\FileSystem::createDir($file->getPath(), 0755);

		$handle = fopen(Utils\SafeStream::PROTOCOL . '://' . $this->temp . DIRECTORY_SEPARATOR . 'lock', 'w');
		$listRates = $driver->download($date, $this->allowedCurrencies);

		if (self::isFileCurrent($file) || !$file->isFile()) {
			file_put_contents($file->getPathname(), serialize($listRates));
			touch($file->getPathname(), $this->getRefresh());
		}

		fclose($handle);

		return $listRates;
	}

	private function createListRate(Driver\ADriver $driver, \SplFileInfo $file, \DateTime $date = NULL)
	{
		return !$file->isFile() || (self::isFileCurrent($file) && $file->getMTime() < time()) ?
			$this->saveCurrencies($driver, $file, $date) :
			$listRate = unserialize(file_get_contents($file->getPathname()));

	}

	/** @return int */
	private function getRefresh()
	{
		if (!is_int($this->refresh)) {
			$this->refresh = (new \DateTime('today ' . $this->refresh))->format('U');
			if (time() >= $this->refresh) {
				$this->refresh += Utils\DateTime::DAY;
			}
		}
		return $this->refresh;
	}

	private function createFileInfo(Driver\ADriver $driver, \DateTime $date = NULL)
	{
		$filename = $date === NULL ? self::FILE_CURRENT : $date->format('Y-m-d');
		return new \SplFileInfo($this->temp . DIRECTORY_SEPARATOR . $driver->getName() . DIRECTORY_SEPARATOR . $filename);
	}

	private static function isFileCurrent(\SplFileInfo $file)
	{
		return $file->getFilename() === self::FILE_CURRENT;
	}

}
