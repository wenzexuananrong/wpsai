<?php

declare (strict_types=1);
namespace Syde\Vendor;

use Syde\Vendor\Dhii\Services\Factory;
return new Factory(['embedded_payment.is_enabled', 'embedded_payment.settings.checkout_css_custom_css.default'], static function (bool $embeddedModeEnabled, string $defaultCss) : array {
    return ['checkout_css_custom_css' => ['title' => \__('Custom CSS', 'payoneer-checkout'), 'type' => 'css', 'description' => \__('Customize the look and feel of the payment widget in embedded payment flow', 'payoneer-checkout'), 'default' => $defaultCss, 'class' => 'code css', 'sanitize_callback' => 'strip_tags', 'custom_attributes' => $embeddedModeEnabled ? [] : ['readonly' => 'readonly']]];
});
