<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\ListSession;

use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel\ProcessingModelSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Status\StatusSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleSerializerInterface;

class ListSerializer implements ListSerializerInterface
{
    /**
     * @var IdentificationSerializerInterface
     */
    protected $identificationSerializer;
    /**
     * @var PaymentSerializerInterface
     */
    protected $paymentSerializer;
    /**
     * @var CustomerSerializerInterface
     */
    protected $customerSerializer;
    /**
     * @var StyleSerializerInterface
     */
    protected $styleSerializer;
    /**
     * @var StatusSerializerInterface
     */
    protected $statusSerializer;
    /**
     * @var RedirectSerializerInterface
     */
    protected $redirectSerializer;
    /**
     * @var ProcessingModelSerializerInterface
     */
    private $processingModelSerializer;

    /**
     * @var ProductSerializerInterface
     */
    protected $productSerializer;

    /**
     * @param IdentificationSerializerInterface $identificationSerializer To serialize
     *                                                                    identification object
     * @param PaymentSerializerInterface $paymentSerializer To serialize payment object
     * @param StatusSerializerInterface $statusSerializer To serialize status object.
     * @param CustomerSerializerInterface $customerSerializer To serialize customer object.
     * @param StyleSerializerInterface $styleSerializer To serialize style instance.
     * @param RedirectSerializerInterface $redirectSerializer To serialize redirect instance.
     */
    public function __construct(
        IdentificationSerializerInterface $identificationSerializer,
        PaymentSerializerInterface $paymentSerializer,
        StatusSerializerInterface $statusSerializer,
        CustomerSerializerInterface $customerSerializer,
        StyleSerializerInterface $styleSerializer,
        RedirectSerializerInterface $redirectSerializer,
        ProductSerializerInterface $productSerializer,
        ProcessingModelSerializerInterface $processingModelSerializer
    ) {

        $this->identificationSerializer = $identificationSerializer;
        $this->paymentSerializer = $paymentSerializer;
        $this->statusSerializer = $statusSerializer;
        $this->customerSerializer = $customerSerializer;
        $this->styleSerializer = $styleSerializer;
        $this->redirectSerializer = $redirectSerializer;
        $this->processingModelSerializer = $processingModelSerializer;
        $this->productSerializer = $productSerializer;
    }

    /**
     * @inheritDoc
     */
    public function serializeListSession(ListInterface $listSession): array
    {
        $links = $listSession->getLinks();
        $identification = $listSession->getIdentification();
        $status = $listSession->getStatus();

        $listData = [
            'links' => $links,
            'identification' => $this->identificationSerializer
                ->serializeIdentification($identification),
            'status' => $this->statusSerializer->serializeStatus($status),
        ];

        try {
            $payment = $listSession->getPayment();
            $listData['payment'] = $this->paymentSerializer->serializePayment($payment);
        } catch (ApiExceptionInterface $exception) {
            //Payment is an optional parameter, so it's ok to have an exception here.
        }

        try {
            $customer = $listSession->getCustomer();
            $listData['customer'] = $this->customerSerializer->serializeCustomer($customer);
        } catch (ApiExceptionInterface $exception) {
            //Customer is an optional parameter, so it's ok to have an exception here.
        }

        try {
            $style = $listSession->getStyle();
            $listData['style'] = $this->styleSerializer->serializeStyle($style);
        } catch (ApiExceptionInterface $exception) {
            //Style is an optional parameter, so it's ok to have an exception here.
        }

        try {
            $redirect = $listSession->getRedirect();
            $listData['redirect'] = $this->redirectSerializer->serializeRedirect($redirect);
        } catch (ApiExceptionInterface $exception) {
            //Redirect is an optional parameter, so it's ok to have an exception here.
        }

        try {
            $division = $listSession->getDivision();
            $listData['division'] = $division;
        } catch (ApiExceptionInterface $exception) {
            //Division is an optional parameter, so it's ok to have an exception here.
        }

        try {
            $processingModel = $listSession->getProcessingModel();
            $listData['processingModel'] = $this->processingModelSerializer
                ->serializeProcessingModel($processingModel);
        } catch (ApiExceptionInterface $exception) {
            //Processing model is an optional parameter, so it's ok to have an exception here.
        }

        $listData['products'] = array_map(function (ProductInterface $product): array {
            return $this->productSerializer->serializeProduct($product);
        }, $listSession->getProducts());

        return $listData;
    }
}
