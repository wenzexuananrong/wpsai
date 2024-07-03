<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Command;

use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Command\Error\InteractionErrorInterface;
use Inpsyde\PayoneerSdk\Api\Command\Exception\CommandException;
use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\System\SystemInterface;
use Inpsyde\PayoneerSdk\Api\Entities\System\SystemSerializerInterface;
use Inpsyde\PayoneerSdk\Client\ApiClientInterface;
use Inpsyde\PayoneerSdk\Client\JsonCodecTrait;
use Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\ResponseValidatorInterface;
use Inpsyde\PayoneerSdk\PayoneerSdkExceptionInterface;
use RuntimeException;

class UpdateListCommand extends AbstractPaymentCommand implements UpdateListCommandInterface
{
    use JsonCodecTrait;
    use PrepareRequestUrlPathTrait;

    /** @var string[] */
    protected $views = [];

    /** @var CustomerSerializerInterface */
    protected $customerSerializer;

    /** @var PaymentSerializerInterface */
    protected $paymentSerializer;

    /** @var CallbackSerializerInterface */
    protected $callbackSerializer;
    /**
     * @var string|null
     */
    protected $division;

    /** @var ?CallbackInterface */
    protected $callback;

    /** @var ?string */
    protected $country;

    /** @var ?CustomerInterface */
    protected $customer;

    /** @var ?PaymentInterface */
    protected $payment;

    /** @var SystemSerializerInterface */
    protected $systemSerializer;

    /**
     * @var ?SystemInterface
     */
    protected $system;

    /**
     * @param array<string, InteractionErrorInterface> $errors
     * @param ApiClientInterface $apiClient
     * @param string $pathTemplate
     * @param ListDeserializerInterface $listDeserializer
     * @param CustomerSerializerInterface $customerSerializer
     * @param PaymentSerializerInterface $paymentSerializer
     * @param CallbackSerializerInterface $callbackSerializer
     * @param ProductSerializerInterface $productSerializer
     * @param ResponseValidatorInterface $responseValidator
     * @param SystemSerializerInterface $systemSerializer
     * @param string $country
     */
    public function __construct(
        array $errors,
        ApiClientInterface $apiClient,
        string $pathTemplate,
        ListDeserializerInterface $listDeserializer,
        CustomerSerializerInterface $customerSerializer,
        PaymentSerializerInterface $paymentSerializer,
        CallbackSerializerInterface $callbackSerializer,
        ProductSerializerInterface $productSerializer,
        ResponseValidatorInterface $responseValidator,
        SystemSerializerInterface $systemSerializer,
        string $country
    ) {

        $this->customerSerializer = $customerSerializer;
        $this->paymentSerializer = $paymentSerializer;
        $this->callbackSerializer = $callbackSerializer;
        $this->systemSerializer = $systemSerializer;
        $this->country = $country;

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
        try {
            $this->validateCommandConfiguration();
            $url = $this->prepareRequestUrlPath();
            $bodyParams = $this->prepareBodyParams();
            $queryParams = $this->views ? ['views' => $this->views] : [];

            $response = $this->apiClient->put($url, [], $queryParams, $bodyParams);
            $this->onResponse($response);
            $parsedBody = $this->decodeJsonResponseBody($response);

            return $this->listDeserializer->deserializeList($parsedBody);
        } catch (PayoneerSdkExceptionInterface | RuntimeException $exception) {
            throw new CommandException(
                $this,
                sprintf(
                    'Failed to update list session, ApiClientException caught: %1$s.',
                    (string) $exception
                ),
                0,
                $exception
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function withViews(array $views): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->views = $views;

        return $newThis;
    }

    /**
     * @inheritDoc
     */
    public function withAddedViews(array $views): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->views = array_unique(array_merge($this->views, $views));

        return $newThis;
    }

    /**
     * @inheritDoc
     */
    public function withoutViews(array $views): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->views = array_diff($this->views, $views);

        return $newThis;
    }

    /**
     * @inheritDoc
     */
    public function withCallback(CallbackInterface $callback): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->callback = $callback;

        return $newThis;
    }

    /**
     * @inheritDoc
     */
    public function withCountry(string $country): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->country = $country;

        return $newThis;
    }

    /**
     * @inheritDoc
     */
    public function withCustomer(CustomerInterface $customer): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->customer = $customer;

        return $newThis;
    }

    /**
     * @inheritDoc
     */
    public function withDivision(string $division): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->division = $division;

        return $newThis;
    }

    /**
     * @inheritDoc
     */
    public function withSystem(SystemInterface $system): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->system = $system;

        return $newThis;
    }

    /**
     * @inheritDoc
     */
    public function withoutDivision(): ListCommandInterface
    {
        $newThis = clone $this;
        $newThis->division = null;

        return $newThis;
    }

    /**
     * @return array
     */
    protected function prepareBodyParams(): array
    {

        /**
         * @var CustomerInterface $this->customer
         * @var PaymentInterface $this->payment
         * @var CallbackInterface $this->callback
         * @var SystemInterface $this->system
         */

        return [
            'transactionId' => $this->transactionId,
            'country' => $this->country,
            'customer' => $this->customerSerializer
                ->serializeCustomer($this->customer),
            'payment' => $this->paymentSerializer
                ->serializePayment($this->payment),
            'callback' => $this->callbackSerializer
                ->serializeCallback($this->callback),
            'products' => array_map([$this->productSerializer, 'serializeProduct'], $this->products),
            'system' => $this->systemSerializer->serializeSystem($this->system),
        ];
    }

    /**
     * Check if this command completely configured and ready to be executed. Throw if it doesn't.
     *
     * @throws ApiExceptionInterface If the command is not configured.
     *
     * @psalm-assert string $this->longId,
     * @psalm-assert string $this->transactionId
     * @psalm-assert CustomerInterface $this->customer
     * @psalm-assert PaymentInterface $this->payment
     * @psalm-assert CallbackInterface $this->callback
     */
    protected function validateCommandConfiguration(): void
    {
        $errors = [];

        foreach (
            [
                'longId' => $this->longId,
                'transactionId' => $this->transactionId,
                'customer' => $this->customer,
                'country' => $this->country,
                'payment' => $this->payment,
                'callback' => $this->callback,
                'system' => $this->system,
            ] as $fieldName => $validationSubject
        ) {
            if ($validationSubject === null) {
                $errors[] = sprintf(
                    '%1$s must be set before update command can be executed',
                    $fieldName
                );
            }
        }

        if ($errors) {
            throw new CommandException(
                $this,
                $this->prepareValidationFailedMessage($errors),
                0,
                null
            );
        }
    }

    /**
     * Prepare a message about this command validation failure.
     *
     * @param string[] $errors List of found errors descriptions.
     *
     * @return string Prepared message.
     */
    protected function prepareValidationFailedMessage(array $errors): string
    {
        $baseMessage = 'UpdateListCommand validation failed. Errors found: ';

        return $baseMessage . implode(';' . PHP_EOL, $errors);
    }

    /**
     * @inheritDoc
     * @return ListAwareCommandInterface&static
     */
    public function withLongId(string $longId): ListAwareCommandInterface
    {
        $newThis = clone $this;
        $newThis->longId = $longId;

        return $newThis;
    }
}
