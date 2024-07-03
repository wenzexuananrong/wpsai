<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession\OrderBasedListSessionFactory;
use Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession\WcBasedListSessionFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Inpsyde\PayoneerSdk\Api\PayoneerIntegrationTypes;

class ApiListSessionProvider implements ListSessionProvider
{
    /**
     * @var WcBasedListSessionFactoryInterface
     */
    private $checkoutFactory;

    /**
     * @var OrderBasedListSessionFactory
     */
    private $paymentFactory;

    /**
     * @var PayoneerIntegrationTypes::* $integrationType
     */
    private $integrationType;

    /**
     * @var string|null
     */
    private $hostedVersion;

    /**
     * @param WcBasedListSessionFactoryInterface $checkoutFactory
     * @param OrderBasedListSessionFactory $paymentFactory
     * @param string $integrationType $integrationType
     * @param string|null $hostedVersion
     * @psalm-param PayoneerIntegrationTypes::* $integrationType
     */
    public function __construct(
        WcBasedListSessionFactoryInterface $checkoutFactory,
        OrderBasedListSessionFactory $paymentFactory,
        string $integrationType,
        string $hostedVersion = null
    ) {

        $this->checkoutFactory = $checkoutFactory;
        $this->paymentFactory = $paymentFactory;
        $this->integrationType = $integrationType;
        $this->hostedVersion = $hostedVersion;
    }

    public function provide(ContextInterface $context): ListInterface
    {
        if ($context instanceof CheckoutContext) {
            $totals = $context->getCart()->get_total('edit');
            if (! $totals) {
                throw new \RuntimeException(
                    sprintf(
                        'Invalid totals amount in %s',
                        __CLASS__
                    )
                );
            }
            $list = $this->checkoutFactory->createList(
                $context->getCustomer(),
                $context->getCart(),
                $this->integrationType,
                $this->hostedVersion
            );

            $context->offsetSet('list_just_created', true);

            return $list;
        }
        if ($context instanceof PaymentContext) {
            $list = $this->paymentFactory->createList(
                $context->getOrder(),
                $this->integrationType,
                $this->hostedVersion
            );

            $context->offsetSet('list_just_created', true);

            return $list;
        }
        throw new \RuntimeException(sprintf(
            'Unknown Context passed to %s',
            __CLASS__
        ));
    }
}
