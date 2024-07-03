<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Command\ResponseValidator;

use Inpsyde\PayoneerSdk\Client\JsonCodecTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * Validates a request based on configured error interaction codes.
 */
class InteractionCodeValidator implements ResponseValidatorInterface
{
    use JsonCodecTrait;

    /** @var string[] */
    protected $errorCodes;

    /**
     * @param string[] $errorCodes The list of interaction codes that are considered invalid.
     */
    public function __construct(array $errorCodes)
    {
        $this->errorCodes = $errorCodes;
    }

    /**
     * @inheritDoc
     */
    public function validateResponse(ResponseInterface $response): void
    {
        $body = $response->getBody();
        $body->rewind();
        $body = (string) $body;
        $data = $this->jsonDecode($body);

        if (!is_array($data)) {
            throw new ValidationFailure($response, $this, 'Response data is not an object');
        }

        $code = $data['interaction']['code'] ?? null;
        if ($code === null) {
            return;
        }

        if (in_array($code, $this->errorCodes)) {
            throw new InteractionCodeFailure(
                $code,
                $response,
                $this,
                sprintf('Interaction code "%1$s" is invalid', $code)
            );
        }
    }
}
