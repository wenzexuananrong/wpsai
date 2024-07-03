<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Command;

use Inpsyde\PayoneerSdk\Api\Command\Error\InteractionErrorInterface;
use Inpsyde\PayoneerSdk\Api\Command\Exception\CommandException;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductSerializerInterface;
use Inpsyde\PayoneerSdk\Client\ApiClientInterface;
use Inpsyde\PayoneerSdk\Client\DecodeJsonResponseBodyTrait;
use Inpsyde\PayoneerSdk\Client\JsonCodecTrait;
use Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\ResponseValidatorInterface;
use Inpsyde\PayoneerSdk\PayoneerSdkExceptionInterface;
use RuntimeException;

/**
 * @psalm-import-type PaymentType from PaymentSerializerInterface
 */
class PayoutCommand extends AbstractPaymentCommand implements PayoutCommandInterface
{
    use DecodeJsonResponseBodyTrait;
    use JsonCodecTrait;
    use PrepareRequestUrlPathTrait;

    /** @var PaymentSerializerInterface */
    protected $paymentSerializer;

    /**
     * @param array<string, InteractionErrorInterface> $errors
     * @param ApiClientInterface $apiClient To make API calls.
     * @param string $pathTemplate To prepare API request URL.
     * @param ListDeserializerInterface $listDeserializer To prepare LIST from API response.
     * @param PaymentSerializerInterface $paymentSerializer
     * @param ResponseValidatorInterface $responseValidator
     * @param ProductSerializerInterface $productSerializer
     */
    public function __construct(
        array $errors,
        ApiClientInterface $apiClient,
        string $pathTemplate,
        ListDeserializerInterface $listDeserializer,
        PaymentSerializerInterface $paymentSerializer,
        ResponseValidatorInterface $responseValidator,
        ProductSerializerInterface $productSerializer
    ) {

        $this->paymentSerializer = $paymentSerializer;

        parent::__construct(
            $productSerializer,
            $apiClient,
            $listDeserializer,
            $pathTemplate,
            $responseValidator,
            $errors
        );
    }

    /**
     * @inheritDoc
     */
    public function execute(): ListInterface
    {
        if (! $this->longId) {
            throw new CommandException(
                $this,
                'The longId field must be set before PayoutCommand can be executed.',
                0,
                null
            );
        }

        $urlPath = $this->prepareRequestUrlPath();
        $bodyParams = $this->prepareBodyParams();

        try {
            $response = $this->apiClient->post($urlPath, [], [], $bodyParams);
            $this->onResponse($response);
            $parsedBody = $this->decodeJsonResponseBody($response);
            return $this->listDeserializer->deserializeList($parsedBody);
        } catch (PayoneerSdkExceptionInterface | RuntimeException $exception) {
            throw new CommandException(
                $this,
                sprintf(
                    'Failed to do payout (refund). Exception caught: %1$s',
                    $exception->getMessage()
                ),
                0,
                null
            );
        }
    }

    /**
     * @psalm-return array{transactionId?: string, payment?: PaymentType}
     */
    protected function prepareBodyParams(): array
    {
        $params = [];

        if (is_string($this->transactionId)) {
            $params['transactionId'] = $this->transactionId;
        }

        if ($this->payment instanceof PaymentInterface) {
            $params['payment'] = $this->paymentSerializer
                ->serializePayment($this->payment);
        }

        return $params;
    }

    /**
     * @inheritDoc
     *
     * @return ListAwareCommandInterface&static
     */
    public function withLongId(string $longId): ListAwareCommandInterface
    {
        $newThis = clone $this;
        $newThis->longId = $longId;

        return $newThis;
    }
}
