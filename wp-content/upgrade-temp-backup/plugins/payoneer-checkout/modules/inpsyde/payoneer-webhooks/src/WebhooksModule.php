<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Webhooks;

use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ExtendingModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Psr\Container\ContainerInterface;
use WP_REST_Request;

class WebhooksModule implements ExecutableModule, ServiceModule, ExtendingModule
{
    use ModuleClassNameIdTrait;

    /**
     * @inheritDoc
     */
    public function run(ContainerInterface $container): bool
    {
        add_action('rest_api_init', static function () use ($container) {
            /** @var string $namespace */
            $namespace = $container->get('webhooks.namespace');
            /** @var string $route */
            $route = $container->get('webhooks.rest_route');
            /** @var string[] $methods */
            $methods = $container->get('webhooks.allowed_methods');
            /** @var callable $callback */
            $callback = $container->get('webhooks.callback');
            /** @var callable(): bool $permissionCallback */
            $permissionCallback = $container->get('webhooks.permission_callback');

            register_rest_route(
                $namespace,
                $route,
                [
                    'methods' => $methods,
                    'callback' => $callback,
                    'permission_callback' => $permissionCallback,
                ]
            );
        });

        /** @var callable():void $addTransactionIdFieldSupport */
        $addTransactionIdFieldSupport = $container->get('webhooks.add_transaction_id_field_support');
        $addTransactionIdFieldSupport();

        /** @var callable():void $addPayoutIdFieldSupport */
        $addPayoutIdFieldSupport = $container->get('webhooks.add_payout_id_field_support');
        $addPayoutIdFieldSupport();

        /** @var callable(WP_Rest_Request):void $logIncomingWebhookRequest */
        $logIncomingWebhookRequest = $container->get('webhooks.log_incoming_webhooks_request');

        add_action('payoneer-checkout.webhook_request', $logIncomingWebhookRequest);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function services(): array
    {

        static $services;

        if ($services === null) {
            $services = require_once dirname(__DIR__) . '/inc/services.php';
        }

        /** @var callable(): array<string, callable(\Psr\Container\ContainerInterface $container):mixed> $services */
        return $services();
    }

    /**
     * @inheritDoc
     */
    public function extensions(): array
    {
        return [
            'inpsyde_payment_gateway.settings_fields' => static function (
                array $previous,
                ContainerInterface $container
            ): array {
                /** @var array $settingsFields */
                $settingsFields = $container->get('webhooks.settings.fields');

                return array_merge(
                    $previous,
                    $settingsFields
                );
            },
        ];
    }
}
