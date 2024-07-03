<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Status;

use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;

interface StatusDeserializerInterface
{
    /**
     * Create an array from Status instance.
     *
     * @param array{code: string, reason: string} $statusData Resulting array.
     *
     * @return StatusInterface Object containing data.
     *
     * @throws ApiExceptionInterface If something went wrong.
     */
    public function deserializeStatus(array $statusData): StatusInterface;
}
