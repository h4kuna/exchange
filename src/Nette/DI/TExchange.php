<?php

namespace h4kuna\Exchange\Nette\DI;

use stdClass;
use Nette\Templating\FileTemplate;
use h4kuna\Exchange;

/**
 * @author Milan Matějček
 */
trait TExchange
{

	/** @var Exchange\Exchange */
	protected $exchange;

	/**
	 * @param Exchange\Exchange $exchange
	 */
	public function injectExchange(Exchange\Exchange $exchange)
	{
		$this->exchange = $exchange;
	}

	/**
	 * @param string $class
	 * @return FileTemplate
	 */
	protected function createTemplate($class = NULL)
	{
		/* @var $template FileTemplate|stdClass */
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
