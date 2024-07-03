<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Client;

use Inpsyde\PayoneerSdk\PayoneerSdkExceptionInterface;

/**
 * Should be thrown if an API request failed or an error code received as a response.
 */
interface ApiClientExceptionInterface extends PayoneerSdkExceptionInterface
{
    /**
     * Return the client thrown this exception.
     *
     * @return ApiClientInterface Instance of the client.
     */
    public function getClient(): ApiClientInterface;
}
