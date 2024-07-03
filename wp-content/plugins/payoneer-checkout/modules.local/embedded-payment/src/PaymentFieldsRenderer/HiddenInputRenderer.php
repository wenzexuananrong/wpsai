<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\PaymentFieldsRenderer;

use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentFieldsRenderer\PaymentFieldsRendererInterface;

class HiddenInputRenderer implements PaymentFieldsRendererInterface
{
    /**
     * @var string
     */
    protected $inputName;
    /**
     * @var string
     */
    protected $value;

    public function __construct(
        string $inputName,
        string $value = "true"
    ) {

        $this->inputName = $inputName;
        $this->value = $value;
    }

    public function renderFields(): string
    {
        return sprintf(
            '<input type="hidden" name="%1$s" value="%2$s">',
            esc_attr($this->inputName),
            esc_attr($this->value)
        );
    }
}
