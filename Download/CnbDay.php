<?php

namespace Exchange;

use Nette,
		Nette\Object;

require_once 'ICnb.php';

class CnbDay extends Download implements ICnb {

	protected $links = array(self::CNB_DAY, self::CNB_DAY2);

	/**
	 * download resource
	 * @return string
	 */
	public function downloading() {
		$data = \explode("\n", parent::stroke2point(trim($this->getData())));
		$data[0] = explode(' #', $data[0]);
		$data[1] = self::CNB_CZK;
		$this->save($data);
	}

	protected function save(&$data) {
		$this->storage->save(IStorage::INFO_CACHE, $data[0], array(Nette\Caching\Cache::EXPIRATION => $this->refresh));
		unset($data[0]);

		$row = array(self::CODE => 0, self::COUNTRY => 0, self::NAME => 0, self::FROM1 => 0, self::TO => 0);

		$code = array();
		foreach ($data as $val) {
			$explode = explode(self::PIPE, $val);

			if (count($explode) != 5) {
				continue;
			}

			list($row[self::COUNTRY], $row[self::NAME], $row[self::FROM1],
							$row[self::CODE], $row[self::TO]) = $explode;

			if (($row[self::TO] = (double) $row[self::TO]) <= 0 || isset($code[$row[self::CODE]])) {
				continue;
			}
			$code[$row[self::CODE]] = 1;

			$row[self::FROM1] = (double) $row[self::FROM1];

			$format = $this->createFormat($row[self::CODE]);
			$format[Exchange::RATE] = $row[self::FROM1] / $row[self::TO];

			if ($row[self::CODE] != $this->default) {
				$format[Exchange::RATE] /= $this->correction;
			}

			$this->storage->save($row[self::CODE], array_intersect_key($row, $format) + $format);
		}
		ksort($code);
		reset($code);
		$this->storage->save(IStorage::ALL_CODE, array_keys($code));
	}

	/**
	 * data downloaded by CUrl
	 * @return string
	 */
	protected function & curl() {
		$curl = new CUrl;
		//$curl->setProxy();
		$cnb = NULL;
		foreach ($this->links as $key => $link) {
			$curl->setOption(\CURLOPT_URL, $this->fillDate($link));

			if ($curl->getErrorNumber() > 0) {
				if ($key == 0)
					throw new ExchangeException('Let\'s check internet connection.');
				continue;
			}
			else
				$cnb .= $curl->getResult();
		}
		return $cnb;
	}

	/**
	 * data downloaded by file_get_contents
	 * @return string
	 */
	protected function & fopen() {
		$cnb = NULL;
		foreach ($this->links as $key => $link) {
			$data = \file_get_contents($this->fillDate($link));
			if ($data === FALSE) {
				if ($key == 0)
					throw new ExchangeException('Let\'s check internet connection.');
				continue;
			}
			else
				$cnb .= $data;
		}
		return $cnb;
	}

	/**
	 * apply date for download
	 * @return void
	 */
	private function fillDate($link) {
		if ($this->date) {
			$date = $this->date->format('d.m.Y');
			$link .= self::CNB_PARAM . $date;
		}
		return $link;
	}

}
