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

	/**
	 * int - unix time
	 * @var string|int
	 */
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
	 * Invalid by cron.
	 * @param Driver\ADriver $driver
	 * @param \DateTime|NULL $date
	 */
	public function invalidForce(Driver\ADriver $driver, \DateTime $date = NULL)
	{
		$this->refresh = time() + Utils\DateTime::DAY;
		$file = $this->createFileInfo($driver, $date);
		$this->saveCurrencies($driver, $file, $date);
	}

	/**
	 * @param array $allowedCurrencies
	 * @return static
	 */
	public function setAllowedCurrencies(array $allowedCurrencies)
	{
		$this->allowedCurrencies = $allowedCurrencies;
		return $this;
	}

	/**
	 * @param string $hour
	 * @return static
	 */
	public function setRefresh($hour)
	{
		$this->refresh = $hour;
		return $this;
	}

	private function saveCurrencies(Driver\ADriver $driver, \SplFileInfo $file, \DateTime $date = NULL)
	{
		$listRates = $driver->download($date, $this->allowedCurrencies);

		file_put_contents(Utils\SafeStream::PROTOCOL . '://' . $file->getPathname(), serialize($listRates));
		if (self::isFileCurrent($file)) {
			touch($file->getPathname(), $this->getRefresh());
		}

		return $listRates;
	}

	private function createListRate(Driver\ADriver $driver, \SplFileInfo $file, \DateTime $date = NULL)
	{
		if ($this->isFileValid($file)) {
			Utils\FileSystem::createDir($file->getPath(), 0755);
			$handle = fopen(Utils\SafeStream::PROTOCOL . '://' . $this->temp . DIRECTORY_SEPARATOR . 'lock', 'w');

			if ($this->isFileValid($file)) {
				$listRate = $this->saveCurrencies($driver, $file, $date);
				fclose($handle);
				return $listRate;
			}
			fclose($handle);
		}

		return unserialize(file_get_contents($file->getPathname()));
	}

	/**
	 * @param \SplFileInfo $file
	 * @return bool
	 */
	private function isFileValid(\SplFileInfo $file)
	{
		return !$file->isFile() || (self::isFileCurrent($file) && $file->getMTime() < time());
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
