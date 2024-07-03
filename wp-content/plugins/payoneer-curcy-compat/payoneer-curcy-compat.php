<?php

/**
 * Plugin Name: Payoneer Checkout & WooCommerce Multi Currency compatibility
 * Description: Provides a temporary workaround to allow Payoneer Checkout to work alongside CURCY
 * Version: 0.0.0
 */
declare(strict_types=1);

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Taxes\TaxesModule;

add_filter('payoneer-checkout.modules_list', function (array $modules){
    return array_filter($modules,function ($m){
        return !$m instanceof TaxesModule;
    });
});
