<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\Settings;

use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentGateway;

class VirtualField implements SettingsFieldRendererInterface
{
    public function render(string $fieldId, array $fieldConfig, PaymentGateway $gateway): string
    {
        return '';
    }
}
