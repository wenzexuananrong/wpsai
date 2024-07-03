<?php

declare(strict_types=1);

namespace Inpsyde\Logger\Exception;

use Exception;

/**
 * The most general logger exception.
 */
class LoggerException extends Exception implements LoggerExceptionInterface
{
}
