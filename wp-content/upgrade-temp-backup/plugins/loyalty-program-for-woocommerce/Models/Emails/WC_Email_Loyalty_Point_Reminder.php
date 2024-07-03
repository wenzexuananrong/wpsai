<?php

namespace LPFW\Models\Emails;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Instance of \WC_Email that houses loyalty points reminder email logic.
 *
 * @since 1.8.4
 */
class WC_Email_Loyalty_Point_Reminder extends \WC_Email {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */
    /**
     * Property that holds customer instance.
     *
     * @since 1.0
     * @access private
     * @var \WC_Customer $customer Customer object.
     */
    protected $customer;

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.8.4
     * @access public
     *
     * @param string $id Email id.
     */
    public function __construct( $id ) {
        // Assign variables.
        $this->id             = $id;
        $this->customer_email = true;
        $this->title          = __( 'Loyalty Program - loyalty point reminder', 'loyalty-program-for-woocommerce' );
        $this->description    = __( 'Loyalty point email reminder that is sent to the customer.', 'loyalty-program-for-woocommerce' );
        $this->template_html  = 'emails/loyalty-point-reminder.php';
        $this->template_plain = 'emails/plain/loyalty-point-reminder.php';
        $this->placeholders   = array(
            '{customer_name}'           => '',
            '{customer_email}'          => '',
            '{customer_balance}'        => '',
            '{customer_balance_worth}'  => '',
            '{customer_balance_expiry}' => '',
        );

        // Call parent constructor.
        parent::__construct();
    }

    /**
     * Get email default text.
     *
     * @since 1.8.4
     * @access public
     *
     * @param string $type Email text type.
     * @return string
     */
    public function get_default_text( $type ) {
        switch ( $type ) {
            case 'subject':
                /* Translators: %s: site_title, customer_balance */
                return sprintf( __( '%1$s: You have %2$s points waiting to be used!', 'loyalty-program-for-woocommerce' ), '[{site_title}]', '{customer_balance}' );
            case 'heading':
                return __( 'You have unused loyalty points', 'loyalty-program-for-woocommerce' );
            case 'message':
                $message = sprintf(
                    /* Translators: %s: customer_name, customer_balance_worth */
                    __( 'Hi %1$s, you currently have %2$s unused loyalty points that worth %3$s and are waiting to be used! These points are due to expire on %4$s.', 'loyalty-program-for-woocommerce' ),
                    '{customer_name}',
                    '<b>{customer_balance}</b>',
                    '<b>{customer_balance_worth}</b>',
                    '<b>{customer_balance_expiry}</b>',
                );
                return $message;
            case 'balance':
                /* Translators: %s: loyalty point Balance */
                $text  = __( 'Loyalty Points', 'loyalty-program-for-woocommerce' );
                $text .= ': {customer_balance} points / {customer_balance_worth}';
                return $text;
            case 'additional_content':
                return '';
            case 'button_text':
                return __( 'View the Store', 'loyalty-program-for-woocommerce' );
        }
    }

    /**
     * Get email text either from option or default text.
     *
     * @since 1.8.4
     * @access public
     *
     * @param string $type Email text type.
     * @param string $format Whether to format the text or not.
     *
     * @retrun string
     */
    public function get_text( $type, $format = true ) {
        $message = $this->get_option( $type, $this->get_default_text( $type ) );
        $message = ( $format ) ? $this->format_string( $message ) : $message;

        return apply_filters( $this->id . '_email_' . $type, $message, $this->object, $this );
    }

    /**
     * Set wc customer.
     *
     * @since 1.8.4
     * @access public
     *
     * @param \WC_Customer $customer Customer object.
     */
    public function set_customer( \WC_Customer $customer ) {
        $balance                                        = \LPFW()->Calculate->get_user_points_balance_data( $customer->get_id() ); // Get balance.
        $this->customer                                 = $customer;
        $this->placeholders['{customer_name}']          = $customer->get_display_name();
        $this->placeholders['{customer_email}']         = $customer->get_email();
        $this->placeholders['{customer_balance}']       = apply_filters( $this->id . '_email_balance', $balance['points'] ?? 0, $this->object, $this );
        $this->placeholders['{customer_balance_worth}'] = apply_filters( $this->id . '_email_balance_worth', $balance['worth'] ?? wc_price( 0 ), $this->object, $this ); // Apply filter.
        $this->placeholders['{customer_balance_expiry}'] = apply_filters( $this->id . '_email_balance_expiry', $balance['expiry'] ?? '', $this->object, $this ); // Apply filter.
    }

    /**
     * Override setup locale function to remove customer email check.
     *
     * @since 1.8.4
     * @access public
     */
    public function setup_locale() {
        if ( apply_filters( 'woocommerce_email_setup_locale', true ) ) {
            wc_switch_to_site_locale();
        }
    }

    /**
     * Override restore locale function to remove customer email check.
     *
     * @since 1.8.4
     * @access public
     */
    public function restore_locale() {
        if ( apply_filters( 'woocommerce_email_restore_locale', true ) ) {
            wc_restore_locale();
        }
    }

    /**
     * Get email content html.
     *
     * @since 1.8.4
     * @access public
     *
     * @return string Email html content.
     */
    public function get_content_html() {
        ob_start();

        \LPFW()->Helper_Functions->load_template(
            $this->template_html,
            array(
				'email' => $this,
            )
        );

        return ob_get_clean();
    }

    /**
     * Get email plain content.
     *
     * @since 1.8.4
     * @access public
     *
     * @return string Email plain content.
     */
    public function get_content_plain() {
        ob_start();

        \LPFW()->Helper_Functions->load_template(
            $this->template_plain,
            array(
                'email' => $this,
            )
        );

        return ob_get_clean();
    }

    /**
     * Initialize email setting form fields.
     *
     * @since 1.8.4
     * @access public
     */
    public function init_form_fields() {
        /* Translators: %s: list of available placeholder tags */
        $placeholder_text  = sprintf( __( 'Available placeholders: %s', 'loyalty-program-for-woocommerce' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' );
        $this->form_fields = array(
            'enabled'            => array(
                'title'   => __( 'Enable/Disable', 'loyalty-program-for-woocommerce' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email', 'loyalty-program-for-woocommerce' ),
                'default' => 'yes',
            ),
            'subject'            => array(
                'title'       => __( 'Subject', 'loyalty-program-for-woocommerce' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_text( 'subject' ),
                'default'     => '',
            ),
            'heading'            => array(
                'title'       => __( 'Email heading', 'loyalty-program-for-woocommerce' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_text( 'heading' ),
                'default'     => '',
            ),
            'message'            => array(
                'title'       => __( 'Message', 'loyalty-program-for-woocommerce' ),
                'type'        => 'textarea',
                'css'         => 'width:400px; height: 75px;',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_text( 'message' ),
                'default'     => '',
            ),
            'additional_content' => array(
                'title'       => __( 'Additional content', 'loyalty-program-for-woocommerce' ),
                'description' => __( 'Text to appear below the main email content.', 'loyalty-program-for-woocommerce' ) . ' ' . $placeholder_text,
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __( 'N/A', 'loyalty-program-for-woocommerce' ),
                'type'        => 'textarea',
                'default'     => $this->get_default_text( 'additional_content' ),
                'desc_tip'    => true,
            ),
            'email_type'         => array(
                'title'       => __( 'Email type', 'loyalty-program-for-woocommerce' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'loyalty-program-for-woocommerce' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }
}
