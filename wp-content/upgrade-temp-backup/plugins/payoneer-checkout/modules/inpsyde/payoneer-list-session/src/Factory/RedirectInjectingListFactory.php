<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\ListSession\Factory;

use Inpsyde\PayoneerSdk\Api\ApiException;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel\ProcessingModelInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Status\StatusInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleInterface;

class RedirectInjectingListFactory implements ListFactoryInterface
{
    /**
     * @var ListFactoryInterface
     */
    protected $listFactory;

    public function __construct(ListFactoryInterface $listFactory)
    {

        $this->listFactory = $listFactory;
    }

    /**
     * @inheritDoc
     */
    public function createList(
        array $links,
        IdentificationInterface $identification,
        StatusInterface $status,
        PaymentInterface $payment = null,
        CustomerInterface $customer = null,
        StyleInterface $style = null,
        RedirectInterface $redirect = null,
        string $division = null,
        array $products = null,
        ProcessingModelInterface $processingModel = null
    ): ListInterface {

        $redirect = $redirect ?? apply_filters(
            'payoneer-checkout.fallback_redirect',
            $redirect,
            $identification
        );

        if (! $redirect instanceof RedirectInterface && $redirect !== null) {
            throw new ApiException(
                'Redirect must be instance of RedirectInterface or null'
            );
        }

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
