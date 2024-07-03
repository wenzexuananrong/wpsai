<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Checkout\GatewayIconsRenderer;

use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\GatewayIconsRenderer\GatewayIconsRendererInterface;

/**
 * Render payment fields that should be displayed on checkout.
 */
class GatewayIconsRenderer implements GatewayIconsRendererInterface
{
    /**
     * @var string[]
     */
    protected $iconElements;

    public function __construct(
        array $iconElements
    ) {
        /** @var string[] $iconElements */
        $this->iconElements = $iconElements;
    }

    /**
     * @inheritDoc
     */
    public function renderIcons(): string
    {
        $iconImages = $this->prepareIconImgElements();
        $icons = implode('', $iconImages);
        return sprintf(
            '<span id="gateway-icons-payoneer">%1$s</span>',
            $icons
        );
    }

    /**
     * @return string[]
     */
    protected function prepareIconImgElements(): array
    {
        return array_map(static function (string $imgPath): string {
            return sprintf('<img src="%1$s" style="padding: 2px;">', $imgPath);
        }, $this->iconElements);
    }
}
