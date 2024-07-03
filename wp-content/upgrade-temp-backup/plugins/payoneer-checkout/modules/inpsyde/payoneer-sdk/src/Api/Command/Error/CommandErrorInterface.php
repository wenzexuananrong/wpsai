<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Command\Error;

use Inpsyde\PayoneerSdk\Api\Command\CommandInterface;
use Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use RuntimeException;
use Inpsyde\PayoneerSdk\Api\Error\ErrorInterface;

/**
 * A builder of command exception.
 *
 * @template E of CommandExceptionInterface
 *
 * @extends ErrorInterface<E>
 */
interface CommandErrorInterface extends ErrorInterface
{
    /**
     * Configures a new instance with the specified command.
     *
     * @param CommandInterface $command The command.
     *
     * @return static The new instance.
     *
     * @throws RuntimeException If problem configuring.
     */
    public function withCommand(CommandInterface $command): self;
}
