<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\PaymentFieldsRenderer;

use Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionManager;
use Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentFieldsRenderer\PaymentFieldsRendererInterface;

class ListUrlFieldRenderer implements PaymentFieldsRendererInterface
{
    /**
     * @var ListSessionProvider
     */
    protected $listSessionProvider;
    /**
     * @var string
     */
    protected $listUrlContainerId;
    /**
     * @var string
     */
    protected $listUrlContainerAttributeName;

    public function __construct(
        ListSessionProvider $listSessionProvider,
        string $listUrlContainerId,
        string $listUrlContainerAttributeName
    ) {

        $this->listSessionProvider = $listSessionProvider;
        $this->listUrlContainerId = $listUrlContainerId;
        $this->listUrlContainerAttributeName = $listUrlContainerAttributeName;
    }

    protected function getListUrl(): string
    {
        $context = ListSessionManager::determineContextFromGlobals();
        $list = $this->listSessionProvider->provide($context);
        return $list->getLinks()['self'] ?? '';
    }

    public function renderFields(): string
    {
        $listSessionUrl = $this->getListUrl();
        $listIdContainer = sprintf(
            '<input type="hidden" name="%1$s" id="%1$s" value="%2$s">',
            esc_attr($this->listUrlContainerId),
            esc_url_raw($listSessionUrl)
        );

        return $listIdContainer;
    }
}
