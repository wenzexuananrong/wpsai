<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\EmbeddedPayment\PaymentFieldsRenderer;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionManager;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\PaymentGateway\Gateway\PaymentFieldsRenderer\PaymentFieldsRendererInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListSerializerInterface;
class ListDebugFieldRenderer implements PaymentFieldsRendererInterface
{
    /**
     * @var ListSessionProvider
     */
    protected $listSessionProvider;
    /**
     * @var ListSerializerInterface
     */
    protected $serializer;
    public function __construct(ListSessionProvider $listSessionProvider, ListSerializerInterface $serializer)
    {
        $this->listSessionProvider = $listSessionProvider;
        $this->serializer = $serializer;
    }
    public function renderFields() : string
    {
        $context = ListSessionManager::determineContextFromGlobals();
        $listSession = $this->listSessionProvider->provide($context);
        $json = (string) json_encode($this->serializer->serializeListSession($listSession), \JSON_PRETTY_PRINT);
        return '<pre>' . $json . '</pre>';
    }
}
