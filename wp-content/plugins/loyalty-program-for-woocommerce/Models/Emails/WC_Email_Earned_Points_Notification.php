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
class WC_Email_Earned_Points_Notification extends \WC_Email {
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
        $this->title          = __( 'Loyalty Program - earned points notification', 'loyalty-program-for-woocommerce' );
        $this->description    = __( 'Earned points email notification that is sent to the customer.', 'loyalty-program-for-woocommerce' );
        $this->template_html  = 'emails/earned-points-notification.php';
        $this->template_plain = 'emails/plain/earned-points-notification.php';
        $this->placeholders   = array(
            '{customer_name}'                 => '',
            '{customer_email}'                => '',
            '{customer_balance}'              => '',
            '{customer_balance_worth}'        => '',
            '{customer_balance_expiry}'       => '',
            '{customer_earned_points}'        => '',
            '{customer_earned_points_worth}'  => '',
            '{customer_earned_points_action}' => '',
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
                return sprintf( __( '%1$s: You have earned %2$s points!', 'loyalty-program-for-woocommerce' ), '[{site_title}]', '{customer_earned_points}' );
            case 'heading':
                return sprintf(
                    /* Translators: %s: customer_balance */
                    __( 'You have earned %1$s points!', 'loyalty-program-for-woocommerce' ),
                    '{customer_earned_points}'
                );
            case 'message':
                $message = sprintf(
                    /* Translators: %s: customer_name, customer_earned_points, customer_earned_points_action */
                    __( 'Hi %1$s, you have earned %2$s loyalty points that is worth %3$s for doing: %4$s.', 'loyalty-program-for-woocommerce' ),
                    '{customer_name}',
                    '{customer_earned_points}',
                    '{customer_earned_points_worth}',
                    '{customer_earned_points_action}',
                );
                return $message;
            case 'additional_content':
                return '';
            case 'button_text':
                return __( 'Earn more loyalty points', 'loyalty-program-for-woocommerce' );
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
     * Set earned points.
     *
     * @since 1.8.4
     * @access public
     *
     * @param array $earned_points Earned points data.
     */
    public function set_earned_points( $earned_points ) {
        // Get earned points action.
        $customer_earned_points_action = $earned_points['customer_earned_points_action'] ?? '';
        if ( $customer_earned_points_action ) {
            $customer_earned_points_action = \LPFW()->Types->get_point_earn_source_types( $customer_earned_points_action );
            $customer_earned_points_action = $customer_earned_points_action->name ?? $earned_points['customer_earned_points_action'];
        }

        // Set placeholders.
        $this->placeholders['{customer_earned_points}']        = apply_filters( $this->id . '_email_earned_points', $earned_points['customer_earned_points'] ?? 0, $this->object, $this ); // Apply filter.
        $this->placeholders['{customer_earned_points_action}'] = apply_filters( $this->id . '_email_earned_points_action', $customer_earned_points_action, $this->object, $this ); // Apply filter.
        $this->placeholders['{customer_earned_points_worth}']  = \LPFW()->Helper_Functions->api_wc_price(
            \LPFW()->Calculate->calculate_redeem_points_worth( $this->placeholders['{customer_earned_points}'] )
        );
    }

    /**
     * Get customer earned points action list.
     *
     * @since 1.8.4
     *
     * @access public
     *
     * @return array
     */
    public function get_customer_earned_points_action_list() {
        $point_settings     = \LPFW()->API_Settings->get_point_amount_fields_and_options();
        $point_options      = $point_settings['options']; // Get action_earn_point_option setting.
        $point_source_types = \LPFW()->Types->get_point_earn_source_types();

        // Extract action list.
        $customer_earned_points_action_list = array();
        foreach ( $point_source_types as $key => $type ) {
            // Validate action.
            if (
                ! isset( $point_options[ $key ] ) || // Check if option exists.
                'yes' !== $point_options[ $key ]['value'] || // Check if option is enabled.
                ! isset( $type['info'] ) // Check if info exists or needs to be displayed.
            ) {
                continue;
            }

            // Add action to list.
            $customer_earned_points_action_list[ $key ] = array(
                'name' => $type['name'],
                'info' => is_array( $type['info'] ) ? $type['info'] : array( $type['info'] ), // We need to convert info to array to support multiple info fields such as high_spend and within_period.
            );
        }

        return apply_filters( 'lpfw_get_customer_earned_points_action_list', $customer_earned_points_action_list );
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
                'default'     => $this->get_default_text( 'additional_content' ),
                'type'        => 'textarea',
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
