<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Middleware;

use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\Checkout\HashProvider\HashProviderInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\FactoryExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\Factory\ListSession\WcBasedUpdateCommandFactoryInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\CheckoutContext;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ContextInterface;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionPersistor;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProvider;
use Syde\Vendor\Inpsyde\PayoneerForWoocommerce\ListSession\ListSession\ListSessionProviderMiddleware;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
class UpdatingMiddleware implements ListSessionProviderMiddleware
{
    /**
     * @var ListSessionPersistor
     */
    private $persistor;
    /**
     * @var WcBasedUpdateCommandFactoryInterface
     */
    protected $wcBasedListSessionFactory;
    /**
     * @var HashProviderInterface
     */
    private $hashProvider;
    /**
     * @var string
     */
    private $sessionHashKey;
    public function __construct(ListSessionPersistor $persistor, WcBasedUpdateCommandFactoryInterface $wcBasedListSessionFactory, HashProviderInterface $hashProvider, string $sessionHashKey)
    {
        $this->persistor = $persistor;
        $this->wcBasedListSessionFactory = $wcBasedListSessionFactory;
        $this->hashProvider = $hashProvider;
        $this->sessionHashKey = $sessionHashKey;
    }
    public function provide(ContextInterface $context, ListSessionProvider $next) : ListInterface
    {
        $list = $next->provide($context);
        /**
         * We are not interested in pay-for-order contexts
         */
        if (!$context instanceof CheckoutContext) {
            return $list;
        }
        /**
         * We don't want to update List before this hook. It is fired after cart totals is
         * calculated. Before this moment, cart returns 0 for totals and List update will obviously
         * get 'ABORT' because no payment networks support 0 amount.
         *
         * In particular, this is called from Taxes module when we are filtering available tax
         * rates. This is too early, and cart totals just cannot be calculated before the tax rates
         * retrieved.
         */
        if (!did_action('woocommerce_after_calculate_totals')) {
            return $list;
        }
        /**
         * If we are already at the payment stage,
         * we will let the gateway deal with final updates
         */
        if ($context->isProcessing()) {
            return $list;
        }
        /**
         * Grab the cart hash to check if there have been changes that require an update
         */
        $currentHash = $this->hashProvider->provideHash();
        /**
         * No need to update List if it was created on current request with current context.
         * We write the current hash to prevent an unneeded update next time the LIST is requested
         */
        if ($context->offsetExists('list_just_created')) {
            $context->getSession()->set($this->sessionHashKey, $currentHash);
            return $list;
        }
        /**
         * Compare the cart hash.
         * If it has not changed, return the existing LIST
         */
        $storedHash = $context->getSession()->get($this->sessionHashKey);
        if ($storedHash === $currentHash) {
            return $list;
        }
        try {
            $updated = $this->updateExistingListSession($list, $context->getCart(), $context->getCustomer());
        } catch (\Throwable $exception) {
            /**
             * Clear any stored LIST downstream. The existing one is no longer usable
             */
            $this->persistor->persist(null, $context);
            /**
             * Re-run the stack.
             * With persisted LISTs now cleared, we should get a fresh one from the API
             */
            return $next->provide($context);
        }
        /**
         * Update checkout hash since the LIST has now changed
         */
        $context->getSession()->set($this->sessionHashKey, $currentHash);
        /**
         * Store the updated LIST
         */
        $this->persistor->persist($updated, $context);
        return $updated;
    }
    /**
     * @throws FactoryExceptionInterface
     * @throws CommandExceptionInterface
     */
    protected function updateExistingListSession(ListInterface $list, \WC_Cart $cart, \WC_Customer $customer) : ListInterface
    {
        $command = $this->wcBasedListSessionFactory->createUpdateCommand($list->getIdentification(), $customer, $cart);
        do_action('payoneer-checkout.before_update_list', ['longId' => $list->getIdentification()->getLongId(), 'list' => $list]);
        $updatedList = $command->execute();
        do_action('payoneer-checkout.list_session_updated', ['longId' => $updatedList->getIdentification()->getLongId(), 'list' => $updatedList]);
        return $updatedList;
    }
}
