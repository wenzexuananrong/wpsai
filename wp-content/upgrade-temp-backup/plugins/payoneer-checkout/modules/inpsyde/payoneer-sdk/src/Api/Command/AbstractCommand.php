<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Command;

use Inpsyde\PayoneerSdk\Api\Command\Error\InteractionErrorInterface;
use Inpsyde\PayoneerSdk\Api\Command\Exception\CommandException;
use Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\InteractionCodeFailureInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Inpsyde\PayoneerSdk\Client\ApiClientInterface;
use Inpsyde\PayoneerSdk\Client\DecodeJsonResponseBodyTrait;
use Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\ResponseValidatorInterface;
use Psr\Http\Message\ResponseInterface;
use RangeException;
use RuntimeException;
use Inpsyde\PayoneerSdk\Client\Command\ValidationFailure;
use Inpsyde\PayoneerSdk\Client\Command\ValidatorFailureInterface;

/**
 * phpcs:disable Inpsyde.CodeQuality.ElementNameMinimalLength
 */
abstract class AbstractCommand implements CommandInterface
{
    use DecodeJsonResponseBodyTrait;

    /** @var ApiClientInterface */
    protected $apiClient;

    /** @var ListDeserializerInterface */
    protected $listDeserializer;

    /** @var string A template of path part of request URL. */
    protected $pathTemplate;

    /** @var ResponseValidatorInterface */
    protected $responseValidator;

    /** @var ?string Unique session id set from the Payoneer side. */
    protected $longId;

    /** @var ?string TransactionId defined by merchant. */
    protected $transactionId;

    /** @var array<string, InteractionErrorInterface> */
    protected $errors;

    /**
     * @param ApiClientInterface $apiClient
     * @param ListDeserializerInterface $listDeserializer
     * @param string $pathTemplate
     * @param ResponseValidatorInterface $responseValidator
     * @param array<string, InteractionErrorInterface> $errors
     */
    public function __construct(
        ApiClientInterface $apiClient,
        ListDeserializerInterface $listDeserializer,
        string $pathTemplate,
        ResponseValidatorInterface $responseValidator,
        array $errors
    ) {

        $this->apiClient = $apiClient;
        $this->listDeserializer = $listDeserializer;
        $this->pathTemplate = $pathTemplate;
        $this->responseValidator = $responseValidator;
        $this->errors = $errors;
    }

    /**
     * @inheritDoc
     */
    public function withTransactionId(string $transactionId): CommandInterface
    {
        $newThis = clone $this;
        $newThis->transactionId = $transactionId;

        return $newThis;
    }

    /**
     * @inheritDoc
     */
    public function withApiClient(ApiClientInterface $apiClient): CommandInterface
    {

        $newThis = clone $this;
        $newThis->apiClient = $apiClient;

        return $newThis;
    }

    /**
     * Does something with a response.
     *
     * @param ResponseInterface $response The response.
     *
     * @throws CommandExceptionInterface If something goes wrong.
     */
    protected function onResponse(ResponseInterface $response): void
    {
        try {
            $this->validateResponse($response);
        } catch (InteractionCodeFailureInterface $e) {
            $interactionCode = $e->getInteractionCode();

            if ($error = $this->errors[$interactionCode] ?? null) {
                $exception = $error->withInteractionCode($interactionCode)
                    ->withCommand($this)
                    ->withInnerException($e)
                    ->createException();

                throw $exception;
            }
        } catch (RuntimeException $e) {
            throw new CommandException($this, 'Problem validating response', 0, $e);
        }
    }

    /**
     * Validates a response.
     *
     * @param ResponseInterface $response The response to validate.
     *
     * @psalm-suppress MissingThrowsDocblock ValidatorFailureInterface|ValidationFailureInterface
     * @throws RangeException If invalid.
     * @throws RuntimeException If problem validating.
     */
    protected function validateResponse(ResponseInterface $response): void
    {
        $this->responseValidator->validateResponse($response);
    }

    /**
     * Decodes a JSON string.
     *
     * @param string $json The JSON to decode.
     *
     * @return mixed The decoded value. Objects are represented as arrays.
     *
     * @throws RuntimeException If problem decoding.
     */
    abstract protected function jsonDecode(string $json);
}
