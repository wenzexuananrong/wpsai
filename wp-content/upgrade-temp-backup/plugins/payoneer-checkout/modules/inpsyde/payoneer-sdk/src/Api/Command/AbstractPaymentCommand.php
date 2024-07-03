<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Command;

use Inpsyde\PayoneerSdk\Api\Command\Error\InteractionErrorInterface;
use Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\ResponseValidatorInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductSerializerInterface;
use Inpsyde\PayoneerSdk\Client\ApiClientInterface;

/**
 * This is a payment-related command, that is aware of payment itself and products this payment is for.
 */
abstract class AbstractPaymentCommand extends AbstractCommand implements PaymentCommandInterface
{
    /**
     * @var ProductSerializerInterface
     */
    protected $productSerializer;

    /** @var ?PaymentInterface */
    protected $payment;

    /** @var ProductInterface[] */
    protected $products = [];

    /**
     * @param ProductSerializerInterface $productSerializer
     * @param ApiClientInterface $apiClient
     * @param ListDeserializerInterface $listDeserializer
     * @param string $pathTemplate
     * @param ResponseValidatorInterface $responseValidator
     * @param array<string, InteractionErrorInterface> $errors
     */
    public function __construct(
        ProductSerializerInterface $productSerializer,
        ApiClientInterface $apiClient,
        ListDeserializerInterface $listDeserializer,
        string $pathTemplate,
        ResponseValidatorInterface $responseValidator,
        array $errors
    ) {

        $this->productSerializer = $productSerializer;
        $this->apiClient = $apiClient;

        parent::__construct(
            $apiClient,
            $listDeserializer,
            $pathTemplate,
            $responseValidator,
            $errors
        );
    }

    /**
     * Return a new instance with provided products.
     *
     * @param ProductInterface[] $products
     *
     * @return static
     */
    public function withProducts(array $products): PaymentCommandInterface
    {
        $newThis = clone $this;
        $newThis->products = $products;

        return $newThis;
    }

    /**
     * Return a new instance with provided payment.
     *
     * @param PaymentInterface $payment
     *
     * @return static
     */
    public function withPayment(PaymentInterface $payment): PaymentCommandInterface
    {
        $newThis = clone $this;
        $newThis->payment = $payment;

        return $newThis;
    }

    /**
     * @return array
     */
    protected function prepareProducts(): array
    {
        $serializedProducts = [];

        foreach ($this->products as $product) {
            $serializedProducts[] = $this->productSerializer->serializeProduct($product);
        }

        return $serializedProducts;
    }
}
