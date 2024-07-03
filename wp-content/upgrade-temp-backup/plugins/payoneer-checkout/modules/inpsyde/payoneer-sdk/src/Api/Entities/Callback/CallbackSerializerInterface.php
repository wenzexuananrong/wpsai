<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Callback;

use Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderInterface;

/**
 * Service able to convert CallbackInterface instance into array.
 */
interface CallbackSerializerInterface
{
    /**
     * @return array{cancelUrl: string,
     *     notificationHeaders: array{name: string, value: string}[],
     *     notificationUrl?: string,
     *     returnUrl: string,
     *     summaryUrl?: string
     * }
     */
    public function serializeCallback(CallbackInterface $callback): array;
}
