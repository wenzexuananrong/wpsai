<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Command\Error;

use Inpsyde\PayoneerSdk\Api\Command\Exception\InteractionExceptionInterface;
use RuntimeException;

/**
 * Can create an interaction error with a message.
 *
 * @template E of InteractionExceptionInterface
 */
interface InteractionErrorFactoryInterface
{
    /**
     * Configures a new interaction error.
     *
     * @param string $code The interaction code.
     * @param string $message The message.
     *
     * @return InteractionErrorInterface<E> The new error.
     *
     * @throws RuntimeException If problem configuring.
     */
    public function createInteractionError(string $code, string $message): InteractionErrorInterface;
}
