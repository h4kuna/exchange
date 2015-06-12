<?php

namespace h4kuna\Exchange;

class ExchangeException extends \Exception {}

class UnknownCurrencyException extends ExchangeException {}

class DuplicityCurrencyException extends ExchangeException {}
