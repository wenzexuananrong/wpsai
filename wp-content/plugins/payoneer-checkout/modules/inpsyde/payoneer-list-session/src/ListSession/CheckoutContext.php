<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

class CheckoutContext extends AbstractContext
{
    public function getCart() : \WC_Cart
    {
        return WC()->cart;
    }
    public function getCustomer() : \WC_Customer
    {
        return WC()->customer;
    }
    public function getSession() : \WC_Session
    {
        return WC()->session;
    }
    /**
     * Returns true if WooCommerce is about to hand over to a payment gateway
     *
     * @return bool
     */
    public function isProcessing() : bool
    {
        return did_action('woocommerce_before_checkout_process') > 0;
    }
}
