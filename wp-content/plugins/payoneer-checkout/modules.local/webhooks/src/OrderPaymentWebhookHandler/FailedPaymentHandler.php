<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Webhooks\OrderPaymentWebhookHandler;

use WC_Order;
use WP_REST_Request;

class FailedPaymentHandler implements OrderPaymentWebhookHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function accepts(WP_REST_Request $request, WC_Order $order): bool
    {
        $status = (string)$request->get_param('statusCode');
        $failureStatuses = ['failed', 'canceled', 'declined', 'rejected', 'aborted'];

        return in_array($status, $failureStatuses, true);
    }
    /**
     * Handle a notification about payment failed.
     *
     * @param WP_REST_Request $request Incoming request.
     * @param WC_Order $order The order payment is failed for.
     */
    public function handlePayment(WP_REST_Request $request, WC_Order $order): void
    {
        $notificationId = (string)$request->get_param('notificationId');
        $resultInfo = (string)$request->get_param('resultInfo');
        do_action('payoneer-checkout.payment_processing_failure', ['exception' => $resultInfo]);
        $order->add_order_note(
            sprintf(
                'Failure message is %1$s. Notification ID is %2$s',
                $resultInfo,
                $notificationId
            )
        );
        /**
         * The order could already be failed.
         * Or if a redirect to the cancelUrl has already taken place, it could
         * be cancelled as well
         */
        if (! $order->has_status(['failed', 'cancelled'])) {
            $order->update_status('failed');
        }
        $order->save();
    }
}
