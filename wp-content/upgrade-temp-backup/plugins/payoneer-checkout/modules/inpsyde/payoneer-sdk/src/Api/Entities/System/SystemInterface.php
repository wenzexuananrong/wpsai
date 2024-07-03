<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\System;

/**
 * Represents system information of the LIST session.
 */
interface SystemInterface
{
    /**
     * Return system type.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Return system code.
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Return system version.
     *
     * @return string
     */
    public function getVersion(): string;
}
