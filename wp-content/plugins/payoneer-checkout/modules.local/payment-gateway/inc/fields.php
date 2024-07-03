<?php

declare(strict_types=1);

use Dhii\Services\Factory;

return new Factory([
    'inpsyde_payment_gateway.token_placeholder',
    'checkout.notification_received',
    'inpsyde_payment_gateway.is_live_mode',
], static function (
    string $tokenPlaceholder,
    bool $notificationReceived,
    bool $liveMode
): array {
    $liveModeCustomAttributes = [];

    if (! $notificationReceived && ! $liveMode) {
        $liveModeCustomAttributes = [
            'disabled' => 'true',
        ];
    }

    return [
        'enabled' => [
            'title' => __('Enable/Disable', 'payoneer-checkout'),
            'type' => 'checkbox',
            'label' => __('Enable Payoneer Checkout', 'payoneer-checkout'),
            'default' => 'no',
        ],
        'live_mode' => [
            'title' => __('Live mode', 'payoneer-checkout'),
            'type' => 'checkbox',
            'label' => __('Enable live mode', 'payoneer-checkout'),
            'default' => 'no',
            'custom_attributes' => $liveModeCustomAttributes,
        ],
        'merchant_code' => [
            'title' => __('API username', 'payoneer-checkout'),
            'type' => 'text',
            'description' => __('Enter your API username here', 'payoneer-checkout'),
            'desc_tip' => true,
        ],
        'merchant_id' => [
            'type' => 'virtual',
            'group' => 'live_credentials',
            'group_role' => 'id',
        ],
        'merchant_token' => [
            'title' => __('Live API token', 'payoneer-checkout'),
            'type' => 'token',
            'description' => 'Enter your merchant token here.',
            'desc_tip' => true,
            'placeholder' => $tokenPlaceholder,
            'group' => 'live_credentials',
            'group_role' => 'token',
        ],
        'base_url' => [
            'type' => 'virtual',
            'group' => 'live_credentials',
            'group_role' => 'base_url',
        ],
        'label' => [
            'type' => 'virtual',
            'group' => 'live_credentials',
            'group_role' => 'label',
        ],
        'store_code' => [
            'title' => __('Live Store code', 'payoneer-checkout'),
            'type' => 'text',
            'description' => __('Enter your Store code here', 'payoneer-checkout'),
            'desc_tip' => true,
            'group' => 'live_credentials',
            'group_role' => 'division',
        ],
        'sandbox_merchant_id' => [
            'type' => 'virtual',
            'group' => 'sandbox_credentials',
            'group_role' => 'id',
        ],
        'sandbox_merchant_token' => [
            'title' => __('Test API token', 'payoneer-checkout'),
            'type' => 'token',
            'description' => 'Enter your sandbox merchant token here.',
            'desc_tip' => true,
            'placeholder' => $tokenPlaceholder,
            'group' => 'sandbox_credentials',
            'group_role' => 'token',
        ],
        'sandbox_base_url' => [
            'type' => 'virtual',
            'group' => 'sandbox_credentials',
            'group_role' => 'base_url',
        ],
        'sandbox_label' => [
            'type' => 'virtual',
            'group' => 'sandbox_credentials',
            'group_role' => 'label',
        ],
        'sandbox_store_code' => [
            'title' => __('Test Store code', 'payoneer-checkout'),
            'type' => 'text',
            'description' => __('Enter your Store code here', 'payoneer-checkout'),
            'desc_tip' => true,
            'group' => 'sandbox_credentials',
            'group_role' => 'division',

        ],
        'title' => [
            'title' => __('Title', 'payoneer-checkout'),
            'type' => 'text',
            'description' => __(
                'The title that the user sees at checkout',
                'payoneer-checkout'
            ),
            'default' => __('Credit / Debit Card', 'payoneer-checkout'),
            'desc_tip' => true,
        ],
        'description' => [
            'title' => __('Description', 'payoneer-checkout'),
            'type' => 'text',
            'description' => __(
                'The description that the user sees at checkout',
                'payoneer-checkout'
            ),
            'default' => '',
            'desc_tip' => true,
        ],
        'notification_received' => [
            'type' => 'virtual',
            'default' => 'no',
        ],
    ];
});
