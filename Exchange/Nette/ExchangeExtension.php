<?php

namespace h4kuna\Exchange\Nette;

use Nette\DI\CompilerExtension;

/**
 * Do not forget set alias for nette < 2.1
 * 
 * Nette\Config\CompilerExtension, Nette\DI\CompilerExtension
 * Nette\Config\Compiler, Nette\DI\Compiler
 * Nette\Utils\PhpGenerator\ClassType, Nette\PhpGenerator\ClassType
 */
class ExchangeExtension extends CompilerExtension {

    public $defaults = array(
        'vat' => array(
            'value' => 21,
            'in' => FALSE,
            'out' => FALSE
        ),
        'currencies' => array(
            'czk' => array('decimal' => 0, 'symbol' => 'Kč'),
            'eur'
        ),
        'driver' => 'h4kuna\Exchange\Driver\Cnb\Day',
        'storage' => 'h4kuna\Exchange\Nette\Cache'
    );

    public function loadConfiguration() {
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

        // session section
        $builder->addDefinition($this->prefix('sessionSection'))
                ->setClass('Nette\Http\SessionSection')
                ->setArguments(array('@session', $this->name))
                ->setShared(FALSE)->setAutowired(FALSE);

        // request manager
        $builder->addDefinition($this->prefix('requestManager'))
                ->setClass('h4kuna\Exchange\Nette\RequestManager')
                ->setArguments(array('@httpRequest', $this->prefix('@sessionSection')))
                ->setShared(FALSE)->setAutowired(FALSE);

        // storage factory
        $builder->addDefinition($this->prefix('cacheFactory'))
                ->setClass('h4kuna\Exchange\Nette\CacheFactory')
                ->setArguments(array('@cacheStorage', $config['storage']))
                ->setShared(FALSE)->setAutowired(FALSE);

        // warehouse
        $builder->addDefinition($this->prefix('warehouse'))
                ->setClass('h4kuna\Exchange\Storage\Warehouse')
                ->setArguments(array($this->prefix('@cacheFactory'), $this->prefix('@driver')))
                ->setShared(FALSE)->setAutowired(FALSE);

        // main class Exchange
        $exchange = $builder->addDefinition($this->prefix('exchange'))
                ->setClass('h4kuna\Exchange\Exchange')
                ->setArguments(array($this->prefix('@warehouse'), $this->prefix('@requestManager')));

        if ($config['vat']) {
            $exchange->addSetup('setVat', array($config['vat']['value'], $config['vat']['in'], $config['vat']['out']));
        }

        foreach ($config['currencies'] as $code => $currency) {
            if (is_array($currency)) {
                $exchange->addSetup('loadCurrency', array($code, $currency));
            } else {
                $exchange->addSetup('loadCurrency', array($currency));
            }
        }
        return $builder;
    }

}
