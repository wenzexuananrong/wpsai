<?php

namespace Inpsyde\PayoneerSdk\Api\Command;

/**
 * A command sending API requests for an existing LIST session
 */
interface ListAwareCommandInterface extends CommandInterface
{
    /**
     * @param string $longId
     *
     * @return static
     */
    public function withLongId(string $longId): self;
}
