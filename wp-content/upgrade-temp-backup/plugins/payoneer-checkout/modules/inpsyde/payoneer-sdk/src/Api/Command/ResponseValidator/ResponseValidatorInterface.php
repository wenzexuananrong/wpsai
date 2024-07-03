<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Command\ResponseValidator;

use Psr\Http\Message\ResponseInterface;
use RangeException;
use RuntimeException;

/**
 * Validates a response.
 */
interface ResponseValidatorInterface
{
    /**
     * Validates a response.
     *
     * @param ResponseInterface $response The response to validate.
     *
     * @throws ValidatorFailureInterface|RuntimeException If response invalid.
     * @throws ValidationFailureInterface|ValidatorFailureInterface|RangeException If problem validating.
     */
    public function validateResponse(ResponseInterface $response): void;
}
