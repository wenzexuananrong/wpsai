<?php

declare(strict_types=1);

namespace Inpsyde\Logger\Exception;

use Psr\Log\InvalidArgumentException;

/**
 * To be thrown when provided log level not listed in the Psr\Log\LogLevel class;
 */
class InvalidLogLevelProvided extends InvalidArgumentException implements LoggerExceptionInterface
{
}
