<?php

namespace h4kuna\Exchange\NoFramework;

use h4kuna\Exchange;

/**
 * @author Milan Matějček
 */
class ExchangeFactory
{

	/** @var string */
	private $temp;

	/** @var bool */
	private $in;

	/** @var bool */
	private $out;

	/** @var float */
	private $vat;

	public function __construct($temp, $vat = 0, $in = FALSE, $out = FALSE)
	{
		$this->temp = $temp;
		$this->setVat($vat, $in, $out);
	}

	public function setVat($vat, $in, $out)
	{
		$this->vat = $vat;
		$this->in = $in;
		$this->out = $out;
	}

	/**
	 * @return Exchange\Exchange
	 */
	public function create()
	{
		$exchange = new Exchange\Exchange($this->createWarehouse(), $this->createRequestManager());
		if ($this->vat) {
			$exchange->setVat($this->vat, $this->in, $this->out);
		}
		return $exchange;
	}

	protected function createRequestManager()
	{
		return new RequestManager();
	}

	protected function createDriver()
	{
		return new Exchange\Driver\Cnb\Day;
	}

	protected function createWarehouse()
	{
		return new Exchange\Storage\Warehouse($this->createFactoryCache(), $this->createDriver());
	}

	protected function createFactoryCache()
	{
		return new CacheFactory(__NAMESPACE__ . '\Cache', $this->temp);
	}

}
