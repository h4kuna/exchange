<?php

namespace h4kuna\Exchange\Nette\DI;

use Nette\DI as NDI;

/**
 * Do not forget set alias for nette < 2.1
 *
 * Nette\Config\CompilerExtension, Nette\DI\CompilerExtension
 * Nette\Config\Compiler, Nette\DI\Compiler
 * Nette\Utils\PhpGenerator\ClassType, Nette\PhpGenerator\ClassType
 */
final class ExchangeExtension extends NDI\CompilerExtension
{

	public $defaults = [
		'vat' => [
			'value' => 21,
			'in' => FALSE,
			'out' => FALSE
		],
		'currencies' => [
			'czk' => ['decimal' => 0, 'symbol' => 'Kč'],
			'eur'
		],
		'filterName' => 'currency',
		'driver' => 'h4kuna\Exchange\Driver\Cnb\Day',
		'storage' => 'h4kuna\Exchange\Nette\Cache'
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$currencies = $this->defaults['currencies'];
		unset($this->defaults['currencies']);
		$config = $this->getConfig($this->defaults);
		if (!isset($config['currencies'])) {
			$config['currencies'] = $currencies;
		}

		// driver
		$builder->addDefinition($this->prefix('driver'))
			->setClass($config['driver']);

		// request manager
		$builder->addDefinition($this->prefix('requestManager'))
			->setClass('h4kuna\Exchange\Nette\RequestManager')
			->setArguments(['@httpRequest', '@session'])
			->setAutowired(FALSE);

		// storage factory
		$builder->addDefinition($this->prefix('cacheFactory'))
			->setClass('h4kuna\Exchange\Nette\CacheFactory')
			->setArguments(['@cacheStorage', $config['storage']])
			->setAutowired(FALSE);

		// warehouse
		$builder->addDefinition($this->prefix('warehouse'))
			->setClass('h4kuna\Exchange\Storage\Warehouse')
			->setArguments([$this->prefix('@cacheFactory'), $this->prefix('@driver')])
			->setAutowired(FALSE);

		// main class Exchange
		$exchange = $builder->addDefinition($this->prefix('exchange'))
			->setClass('h4kuna\Exchange\Exchange')
			->setArguments([$this->prefix('@warehouse'), $this->prefix('@requestManager')]);

		if ($config['vat']['value']) {
			$exchange->addSetup('setVat', [$config['vat']['value'], $config['vat']['in'], $config['vat']['out']]);
		}

		foreach ($config['currencies'] as $code => $currency) {
			if (is_array($currency)) {
				$exchange->addSetup('loadCurrency', [$code, $currency]);
			} else {
				$exchange->addSetup('loadCurrency', [$currency]);
			}
		}

		$builder->getDefinition('latte.latteFactory')
			->addSetup('addFilter', [$config['filterName'], new NDI\Statement('function ($number, $from = NULL, $to = NULL, $vat = NULL) { return ?->format($number, $from, $to, $vat);}', [$exchange])]);

		return $builder;
	}

}
