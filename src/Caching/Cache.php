<?php declare(strict_types=1);

namespace h4kuna\Exchange\Caching;

use h4kuna\Exchange\Currency;
use h4kuna\Exchange\Driver;
use h4kuna\Exchange\Exceptions\InvalidState;
use Nette\Utils;

class Cache implements ICache
{

	private const FILE_SCHEME = Utils\SafeStream::PROTOCOL . '://';
	private const FILE_CURRENT = 'current';

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


	public function loadListRate(Driver\Driver $driver, \DateTimeInterface $date = null): Currency\ListRates
	{
		$file = $this->createFileInfo($driver, $date);
		if (isset($this->listRates[$file->getPathname()])) {
			return $this->listRates[$file->getPathname()];
		}
		return $this->listRates[$file->getPathname()] = $this->createListRate($driver, $file, $date);
	}


	public function flushCache(Driver\Driver $driver, \DateTimeInterface $date = null): void
	{
		$file = $this->createFileInfo($driver, $date);
		$file->isFile() && unlink($file->getPathname());
	}


	/**
	 * Invalid by cron.
	 */
	public function invalidForce(Driver\Driver $driver, ?\DateTimeInterface $date = null): void
	{
		$this->refresh = time() + Utils\DateTime::DAY;
		$file = $this->createFileInfo($driver, $date);
		$this->saveCurrencies($driver, $file, $date);
	}


	/**
	 * @return static
	 */
	public function setAllowedCurrencies(array $allowedCurrencies)
	{
		$this->allowedCurrencies = $allowedCurrencies;
		return $this;
	}


	/**
	 * @return static
	 */
	public function setRefresh(string $hour)
	{
		$this->refresh = $hour;
		return $this;
	}


	private function saveCurrencies(Driver\Driver $driver, \SplFileInfo $file, ?\DateTimeInterface $date): Currency\ListRates
	{
		$listRates = $driver->download($date, $this->allowedCurrencies);

		file_put_contents(self::FILE_SCHEME . $file->getPathname(), serialization($listRates));
		if (self::isFileCurrent($file)) {
			touch($file->getPathname(), $this->getRefresh());
		}

		return $listRates;
	}


	private function createListRate(Driver\Driver $driver, \SplFileInfo $file, ?\DateTimeInterface $date): Currency\ListRates
	{
		if ($this->isFileValid($file)) {
			Utils\FileSystem::createDir($file->getPath(), 0755);
			$lockFile = $this->temp . DIRECTORY_SEPARATOR . 'lock';
			$handle = fopen(self::FILE_SCHEME . $lockFile, 'w');

			if ($handle === false) {
				throw new InvalidState('Could not write to lock file. ' . $lockFile);
			}

			if ($this->isFileValid($file)) {
				$listRate = $this->saveCurrencies($driver, $file, $date);
				fclose($handle);
				return $listRate;
			}
			fclose($handle);
		}

		$content = file_get_contents($file->getPathname());
		if ($content === false) {
			throw new InvalidState(sprintf('Read from file "%s" failed.', $file->getPathname()));
		}
		return deserialization($content);
	}


	private function isFileValid(\SplFileInfo $file): bool
	{
		return !$file->isFile() || (self::isFileCurrent($file) && $file->getMTime() < time());
	}


	private function getRefresh(): int
	{
		if (!is_int($this->refresh)) {
			$this->refresh = (int) (new \DateTime('today ' . $this->refresh))->format('U');
			if (time() >= $this->refresh) {
				$this->refresh += Utils\DateTime::DAY;
			}
		}
		return $this->refresh;
	}


	private function createFileInfo(Driver\Driver $driver, ?\DateTimeInterface $date): \SplFileInfo
	{
		$filename = $date === null ? self::FILE_CURRENT : $date->format('Y-m-d');
		return new \SplFileInfo($this->temp . DIRECTORY_SEPARATOR . $driver->getName() . DIRECTORY_SEPARATOR . $filename);
	}


	private static function isFileCurrent(\SplFileInfo $file): bool
	{
		return $file->getFilename() === self::FILE_CURRENT;
	}

}
