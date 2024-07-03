<?php

declare (strict_types=1);
namespace Syde\Vendor\Dhii\Container\Exception;

use Syde\Vendor\Psr\Container\NotFoundExceptionInterface;
use Throwable;
class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
    /**
     * @param string         $message  The error message.
     * @param int            $code     The error code.
     * @param Throwable|null $previous The inner error, if any.
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
