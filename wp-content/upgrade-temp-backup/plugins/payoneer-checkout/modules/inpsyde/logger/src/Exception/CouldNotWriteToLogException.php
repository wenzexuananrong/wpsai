<?php

declare(strict_types=1);

namespace Inpsyde\Logger\Exception;

use Mockery\Exception\RuntimeException;

/**
 * To be thrown when writing to the log was failed.
 */
class CouldNotWriteToLogException extends RuntimeException implements LoggerExceptionInterface
{
}
