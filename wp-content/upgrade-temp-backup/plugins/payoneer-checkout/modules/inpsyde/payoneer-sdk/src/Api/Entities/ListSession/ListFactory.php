<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\ListSession;

use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel\ProcessingModelInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Status\StatusInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleInterface;

class ListFactory implements ListFactoryInterface
{
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

        return new ListSession(
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
