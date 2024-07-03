<?php
namespace LPFW\Abstracts;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Abstraction that provides email hook schema.
 *
 * @since 1.8.4
 */
abstract class Email_Model extends Base_Model {
    /**
     * Trigger email (Action Scheduler).
     * - Email instance should have a trigger method.
     * - This method will be executed by Action Scheduler.
     *
     * @since 1.8.4
     *
     * @param array $user_id User ID.
     */
    abstract public function trigger( $user_id );

    /**
     * Preview email.
     * - Email instance should have a preview method to preview the email.
     *
     * @since 1.8.4
     * @access public
     */
    abstract public function preview();

    /**
     * Send email.
     * - Email instance should have a send method to send the email.
     *
     * @since 1.8.4
     * @access public
     *
     * @param \WC_Customer $customer Customer object.
     */
    abstract public function send( $customer );
}
