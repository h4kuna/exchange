<?php

namespace h4kuna\Exchange;

use stdClass;
use Nette\Templating\FileTemplate;

/**
 * @todo move to DI
 * @author Milan Matějček
 */
trait TExchange
{

	/** @var Exchange */
	public $_exchange;

	/**
	 * @param Exchange $exchange
	 */
	public function injectExchange(Exchange $exchange)
	{
		$this->_exchange = $exchange;
	}

	/**
	 * @param string $class
	 * @return FileTemplate
	 */
	protected function createTemplate($class = NULL)
	{
		/* @var $template FileTemplate|stdClass */
		$template = parent::createTemplate($class);
		$template->_exchange = $exchange = $this->_exchange;
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
