<?php

namespace h4kuna\Exchange\DI;

use Nette\PhpGenerator\ClassType;
use Nette\DI\CompilerExtension;
use Nette\Configurator;
use Nette\DI\Compiler;

if (defined('\Nette\Framework::VERSION_ID') || Framework::VERSION_ID < 20100) {
    if (!class_exists('Nette\DI\CompilerExtension')) {
        class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
    }

    if (!class_exists('Nette\DI\Compiler')) {
        class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
    }

    if (!class_exists('Nette\PhpGenerator\ClassType')) {
        class_alias('Nette\Utils\PhpGenerator\ClassType', 'Nette\PhpGenerator\ClassType');
    }
}

class ExchangeExtension extends CompilerExtension {

    public $defaults = array(
        'vat' => 21,
        'vatIn' => FALSE,
        'vatOut' => FALSE,
        'currencies' => array(
            'czk' => array('decimal' => 0, 'symbol' => 'Kč'),
            'eur'
        )
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
                ->setClass('h4kuna\Exchange\Cnb\Day');

        // session section
        $builder->addDefinition($this->prefix('sessionSection'))
                ->setClass('Nette\Http\SessionSection')
                ->setArguments(array('@session', $this->name))
                ->setShared(FALSE)->setAutowired(FALSE);

        // storage
        $builder->addDefinition($this->prefix('storage'))
                ->setClass('h4kuna\Exchange\Storage')
                ->setArguments(array('@cacheStorage'))
                ->setShared(FALSE)->setAutowired(FALSE);

        // store
        $builder->addDefinition($this->prefix('store'))
                ->setClass('h4kuna\Exchange\Store')
                ->setArguments(array($this->prefix('@storage'), $this->prefix('@driver')))
                ->setShared(FALSE)->setAutowired(FALSE);

        // main class Exchange
        $exchange = $builder->addDefinition($this->prefix('exchange'))
                ->setClass('h4kuna\Exchange\Exchange')
                ->setArguments(array($this->prefix('@store'), '@httpRequest', $this->prefix('@sessionSection')));

        if ($config['vat']) {
            $exchange->addSetup('setVat', array($config['vat'], $config['vatIn'], $config['vatOut']));
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

    /**
     * @param \Nette\Configurator $configurator
     */
    public static function register(Configurator $configurator) {
        $that = new static;
        $configurator->onCompile[] = function ($config, Compiler $compiler) use ($that) {
            $compiler->addExtension('exchangeExtension', $that);
        };
    }

}
