<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\PaymentFieldsRenderer;

use Inpsyde\PayoneerForWoocommerce\Checkout\CheckoutExceptionInterface;
use Inpsyde\PayoneerForWoocommerce\Checkout\HashProvider\HashProviderInterface;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentFieldsRenderer\PaymentFieldsRendererInterface;

class CheckoutHashFieldRenderer implements PaymentFieldsRendererInterface
{
    /**
     * @var HashProviderInterface
     */
    protected $hashProvider;
    /**
     * @var string
     */
    protected $hashContainerId;

    public function __construct(
        HashProviderInterface $hashProvider,
        string $hashContainerId
    ) {

        $this->hashProvider = $hashProvider;
        $this->hashContainerId = $hashContainerId;
    }

    public function renderFields(): string
    {
        try {
            $checkoutHash = $this->hashProvider->provideHash();
        } catch (CheckoutExceptionInterface $checkoutException) {
            //We shouldn't break checkout because of missing hash.
            $checkoutHash = '';
        }

        return sprintf(
            '<script id="%1$s" type="text/plain">%2$s</script>',
            esc_attr($this->hashContainerId),
            esc_attr($checkoutHash)
        );
    }
}
