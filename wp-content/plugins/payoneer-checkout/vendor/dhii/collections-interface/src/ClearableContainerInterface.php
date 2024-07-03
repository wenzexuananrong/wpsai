<?php

declare (strict_types=1);
namespace Syde\Vendor\Dhii\Collection;

use Syde\Vendor\Psr\Container\ContainerExceptionInterface;
use Syde\Vendor\Psr\Container\ContainerInterface as PsrContainerInterface;
interface ClearableContainerInterface extends PsrContainerInterface
{
    /**
     * Removes all members from this container.
     *
     * @psalm-suppress InvalidThrow In PSR-11, this interface does not extend `Throwable`.
     * @throws ContainerExceptionInterface If problem removing.
     */
    public function clear() : void;
}
