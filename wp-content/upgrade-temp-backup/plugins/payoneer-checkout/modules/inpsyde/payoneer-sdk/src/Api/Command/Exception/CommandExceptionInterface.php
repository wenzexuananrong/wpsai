<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Command\Exception;

use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Command\CommandInterface;
use RuntimeException;

/**
 * A problem with a command.
 */
interface CommandExceptionInterface extends ApiExceptionInterface
{
    /**
     * Retrieves the command associated with this instance.
     *
     * @return CommandInterface The command.
     *
     * @throws RuntimeException If problem retrieving.
     */
    public function getCommand(): CommandInterface;
}
