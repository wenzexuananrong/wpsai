<?php

declare (strict_types=1);
namespace Syde\Vendor;

return static function () : array {
    return ['inpsyde_payment_gateway.settings_fields' => static function (array $previous) : array {
        $analyticsFields = ['analytics_fieldset_title' => ['title' => \__('Analytics', 'payoneer-checkout'), 'type' => 'title'], 'analytics_enabled' => ['title' => \__('Enable analytics', 'payoneer-checkout'), 'type' => 'checkbox', 'default' => 'yes']];
        return \array_merge($previous, $analyticsFields);
    }];
};
