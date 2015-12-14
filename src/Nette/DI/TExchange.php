<?php

namespace h4kuna\Exchange\Nette\DI;

use h4kuna\Exchange,
	Nette\Bridges\ApplicationLatte;

/**
 * @author Milan Matějček
 */
trait TExchange
{

	/** @var Exchange\Exchange */
	protected $exchange;

	public function injectExchange(Exchange\Exchange $exchange)
	{
		$this->exchange = $exchange;
	}

	/**
	 * @param string $class
	 * @return ApplicationLatte\Template
	 */
	protected function createTemplate($class = NULL)
	{
		/* @var $template ApplicationLatte\Template */
		$template = parent::createTemplate($class);
		$template->exchange = $exchange = $this->exchange;
		$template->registerHelper('formatVat', function () use ($exchange) {
			return $exchange->formatVat();
		});
		$template->registerHelper('currency', function ($number, $from = NULL, $to = NULL, $vat = NULL) use ($exchange) {
			return $exchange->format($number, $from, $to, $vat);
		});
		$template->registerHelper('formatTo', function ($number, $to = NULL, $vat = NULL) use ($exchange) {
			return $exchange->formatTo($number, $to, $vat);
		});
		return $template;
	}

}
