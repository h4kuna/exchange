<?php declare(strict_types=1);

namespace h4kuna\Exchange\Exceptions;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

final class XmlResponseFailedException extends Exception implements ClientExceptionInterface
{

}
