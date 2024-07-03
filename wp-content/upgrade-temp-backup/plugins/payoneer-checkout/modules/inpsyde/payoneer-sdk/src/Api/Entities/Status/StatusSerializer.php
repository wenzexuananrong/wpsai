<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Status;

class StatusSerializer implements StatusSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeStatus(StatusInterface $status): array
    {
        return [
            'code' => $status->getCode(),
            'reason' => $status->getReason(),
        ];
    }
}
