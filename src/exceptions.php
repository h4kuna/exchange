<?php

namespace h4kuna\Exchange;

class ExchangeException extends \Exception {}

class DriverDoesNotSupport extends ExchangeException {}

class UnknownCurrencyException extends ExchangeException {}

class DuplicityCurrencyException extends ExchangeException {}
