<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Callback;

use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderInterface;

/**
 * Service able to convert data array to a Callback object.
 */
interface CallbackDeserializerInterface
{
    /**
     * @param array {
     *          returnUrl: string,
     *          returnUrl: string,
     *          cancelUrl: string,
     *          summaryUrl: string,
     *          notificationUrl?: string,
     *          notificationHeaders: array{name: string, value: string}[]
     *        } $callbackData
     *
     * @return CallbackInterface Created instance.
     *
     * @throws ApiExceptionInterface If failed to deserialize data.
     */
    public function deserializeCallback(array $callbackData): CallbackInterface;
}
