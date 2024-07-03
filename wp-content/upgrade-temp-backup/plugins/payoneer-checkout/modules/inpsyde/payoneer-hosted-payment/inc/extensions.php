<?php

declare(strict_types=1);

use Dhii\Collection\MapInterface;
use Dhii\Services\Factory;
use Inpsyde\PayoneerForWoocommerce\Checkout\Authentication\TokenGeneratorInterface;
use Inpsyde\PayoneerForWoocommerce\Checkout\MisconfigurationDetector\MisconfigurationDetectorInterface;
use Inpsyde\PayoneerForWoocommerce\HostedPayment\PaymentFieldsRenderer\DescriptionFieldRenderer;
use Inpsyde\PayoneerForWoocommerce\HostedPayment\PaymentProcessor\HostedPaymentProcessor;
use Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession\OrderBasedListCommandFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionManager;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\CommandFactory\WcOrderBasedUpdateCommandFactoryInterface;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\PaymentProcessor\PaymentProcessorInterface;
use Psr\Container\ContainerInterface;

return static function (): array {
    return [
        'checkout.flow_options' => static function (
            array $paymentFlowOptions
        ): array {
            $paymentFlowOptions['hosted'] = __('Hosted', 'payoneer-checkout');

            return $paymentFlowOptions;
        },
        'checkout.flow_options_description' => static function (
            string $paymentOptionsDescription
        ): string {
            $hostedDescription = __(
                'Hosted: customers get redirected to an external payment page.',
                'payoneer-checkout'
            );
            $paymentOptionsDescription .= '<br>' . $hostedDescription;

            return $paymentOptionsDescription;
        },
        'inpsyde_payment_gateway.payment_processor' => static function (
            PaymentProcessorInterface $previous,
            ContainerInterface $container
        ): PaymentProcessorInterface {
            $isEnabled = (bool)$container->get('hosted_payment.is_enabled');
            if (! $isEnabled) {
                return $previous;
            }
            /**
             * @var callable(ContainerInterface):PaymentProcessorInterface $factory
             */
            $factory = new Factory(
                [
                    'list_session.order_based_list_command_factory',
                    'list_session.manager',
                    'inpsyde_payment_gateway.transaction_id_field_name',
                    'hosted_payment.misconfiguration_detector',
                    'hosted_payment.order_based_update_command_factory',
                    'checkout.security_token_generator',
                    'checkout.order.security_header_field_name',
                    'hosted_payment.payment_flow_override_flag.is_set',
                ],
                static function (
                    OrderBasedListCommandFactoryInterface $listCommandFactory,
                    ListSessionManager $listSessionManager,
                    string $transactionIdFieldName,
                    MisconfigurationDetectorInterface $misconfigurationDetector,
                    WcOrderBasedUpdateCommandFactoryInterface $updateCommandFactory,
                    TokenGeneratorInterface $tokenGenerator,
                    string $tokenKey,
                    bool $fallbackToHostedModeFlag
                ): PaymentProcessorInterface {
                    return new HostedPaymentProcessor(
                        $listCommandFactory,
                        $listSessionManager,
                        $transactionIdFieldName,
                        $misconfigurationDetector,
                        $listSessionManager,
                        $updateCommandFactory,
                        $tokenGenerator,
                        $tokenKey,
                        $fallbackToHostedModeFlag
                    );
                }
            );

            return $factory($container);
        },
        'checkout.payment_field_renderers' => static function (
            array $renderers,
            ContainerInterface $container
        ): array {
            $isEnabled = (bool)$container->get('hosted_payment.is_enabled');
            if (!$isEnabled) {
                return $renderers;
            }
            /** @var MapInterface */
            $options = $container->get('inpsyde_payment_gateway.options');
            if (!$options->has('description')) {
                return $renderers;
            }

            $description = (string)$options->get('description');
            if (empty($description)) {
                return $renderers;
            }

            $renderers[] = new DescriptionFieldRenderer($description);

            return $renderers;
        },
        'inpsyde_payment_gateway.has_fields' => static function (
            bool $hasFields,
            ContainerInterface $container
        ): bool {
            $isEnabled = (bool)$container->get('hosted_payment.is_enabled');
            if ($isEnabled) {
                return false;
            }

            return $hasFields;
        },
    ];
};
