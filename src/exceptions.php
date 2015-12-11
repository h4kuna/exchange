<?php

namespace h4kuna\Exchange;

abstract class ExchangeException extends \Exception {}

class InvalidArgumentException extends ExchangeException {}

class RuntimeException extends ExchangeException {}

class DriverDoesNotSupport extends ExchangeException {}

class UnknownCurrencyException extends ExchangeException {}

class DuplicityCurrencyException extends ExchangeException {}
