<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\ListSession;

use Inpsyde\PayoneerSdk\Api\ApiException;
use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel\ProcessingModelDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Status\StatusDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleDeserializerInterface;

class ListDeserializer implements ListDeserializerInterface
{
    /**
     * @var ListFactoryInterface Service able to create a new instance object.
     */
    protected $listFactory;

    /**
     * @var CallbackDeserializerInterface Service able to convert array to Callback instance.
     */
    protected $callbackDeserializer;

    /**
     * @var CustomerDeserializerInterface Service able to convert array to Customer instance.
     */
    protected $customerDeserializer;

    /**
     * @var PaymentDeserializerInterface Service able to convert array to Payment instance.
     */
    protected $paymentDeserializer;

    /**
     * @var IdentificationDeserializerInterface Service able to convert array to identification instance.
     */
    protected $identificationDeserializer;

    /**
     * @var StyleDeserializerInterface
     */
    protected $styleDeserializer;

    /**
     * @var StatusDeserializerInterface
     */
    protected $statusDeserializer;
    /**
     * @var RedirectDeserializerInterface
     */
    protected $redirectDeserializer;
    /**
     * @var ProductDeserializerInterface
     */
    protected $productDeserializer;

    /**
     * @var ProcessingModelDeserializerInterface
     */
    protected $processingModelDeserializer;

    /**
     * @param ListFactoryInterface $listFactory To create List instance.
     * @param CallbackDeserializerInterface $callbackDeserializer To create callback instance from data map.
     * @param CustomerDeserializerInterface $customerDeserializer To create customer instance from data map.
     * @param PaymentDeserializerInterface $paymentDeserializer To create payment instance from data map.
     * @param StatusDeserializerInterface $statusDeserializer To create status instance form data map.
     * @param RedirectDeserializerInterface $redirectDeserializer To create a redirect instance from data map.
     * @param IdentificationDeserializerInterface $identificationDeserializer To create identification instance from data map.
     * @param StyleDeserializerInterface $styleDeserializer To create style instance from data map.
     * @param ProductDeserializerInterface $productDeserializer To create product instances.
     * @param ProcessingModelDeserializerInterface $processingModelDeserializer To create processing model instance from
     *  data map.
     */
    public function __construct(
        ListFactoryInterface $listFactory,
        CallbackDeserializerInterface $callbackDeserializer,
        CustomerDeserializerInterface $customerDeserializer,
        PaymentDeserializerInterface $paymentDeserializer,
        StatusDeserializerInterface $statusDeserializer,
        RedirectDeserializerInterface $redirectDeserializer,
        IdentificationDeserializerInterface $identificationDeserializer,
        StyleDeserializerInterface $styleDeserializer,
        ProductDeserializerInterface $productDeserializer,
        ProcessingModelDeserializerInterface $processingModelDeserializer
    ) {

        $this->listFactory = $listFactory;
        $this->callbackDeserializer = $callbackDeserializer;
        $this->customerDeserializer = $customerDeserializer;
        $this->paymentDeserializer = $paymentDeserializer;
        $this->identificationDeserializer = $identificationDeserializer;
        $this->styleDeserializer = $styleDeserializer;
        $this->statusDeserializer = $statusDeserializer;
        $this->redirectDeserializer = $redirectDeserializer;
        $this->productDeserializer = $productDeserializer;
        $this->processingModelDeserializer = $processingModelDeserializer;
    }

    /**
     * @inheritDoc
     */
    public function deserializeList(array $listData): ListInterface
    {
        if (! isset($listData['links'])) {
            throw new ApiException('Data contains no expected links element');
        }
        /** @var array{self: string, lang?: string, customer?: string} $links */
        $links = $listData['links'];

        if (! isset($listData['identification'])) {
            throw new ApiException('Data contains no expected identification element');
        }
        $identification = $this->identificationDeserializer
            ->deserializeIdentification($listData['identification']);

        $customer = isset($listData['customer']) ?
            $this->customerDeserializer->deserializeCustomer($listData['customer'])
            : null;

        if (! isset($listData['status'])) {
            throw new ApiException('Data contains no expected status element');
        }
        $status = $this->statusDeserializer->deserializeStatus($listData['status']);

        $payment = isset($listData['payment']) ?
            $this->paymentDeserializer->deserializePayment($listData['payment']) :
            null;

        $style = isset($listData['style']) ?
            $this->styleDeserializer->deserializeStyle($listData['style'])
            : null;

        $redirect = isset($listData['redirect']) ?
            $this->redirectDeserializer->deserializeRedirect($listData['redirect'])
            : null;

        $division = $listData['division'] ?? null;

        $processingModel = isset($listData['processingModel']) ?
            $this->processingModelDeserializer->deserializeProcessingModel($listData['processingModel'])
            : null;

        $products = array_map(function (array $productData) {
            return $this->productDeserializer
                ->deserializeProduct($productData);
        }, $listData['products'] ?? []);

        return $this->listFactory->createList(
            $links,
            $identification,
            $status,
            $payment,
            $customer,
            $style,
            $redirect,
            $division,
            $products,
            $processingModel
        );
    }
}
