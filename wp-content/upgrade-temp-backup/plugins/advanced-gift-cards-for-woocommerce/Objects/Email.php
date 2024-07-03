<?php

namespace AGCFW\Objects;

/**
 * Model that houses the data model of an advanced gift card email.
 *
 * @since 1.0
 */
class Email extends \WC_Email {

    /**
     * Gift card object.
     *
     * @since 1.3.4
     * @var Advanced_Gift_Card|null
     */
    private $_gift_card = null;

    /**
     * Design image.
     *
     * @since 1.3.4
     * @var string
     */
    private $_design_image = '';

    /**
     * Message.
     *
     * @since 1.3.4
     * @var string
     */
    private $_message = '';

    /**
     * Class constructor.
     *
     * @since 1.0
     * @access public
     */
    public function __construct() {
        $this->id             = 'advanced_gift_card';
        $this->customer_email = true;
        $this->title          = __( 'Advanced Gift Card', 'advanced-gift-cards-for-woocommerce' );
        $this->description    = __( 'Advanced gift card emails are sent to the set recipient after the order has been paid.', 'advanced-gift-cards-for-woocommerce' );
        $this->template_html  = 'emails/email-advanced-gift-card.php';
        $this->template_plain = 'emails/plain/email-advanced-gift-card.php';
        $this->placeholders   = array(
            '{gift_card_code}'     => '',
            '{gift_card_value}'    => '',
            '{gift_card_expire}'   => '',
            '{gift_card_receiver}' => '',
            '{gift_card_sender}'   => '',
        );

        add_action( 'agcfw_after_create_gift_card_for_order', array( $this, 'trigger' ), 10, 3 );
        add_action( 'agcfw_after_create_manual_gift_card', array( $this, 'manual_gift_card_trigger' ), 10, 2 );

        parent::__construct();
    }

    /**
     * Get email subject.
     *
     * @since 1.0
     * @access public
     *
     * @return string
     */
    public function get_default_subject() {
        return __( '[{site_title}]: You have received a gift card', 'advanced-gift-cards-for-woocommerce' );
    }

    /**
     * Get email subject.
     *
     * @since 1.0
     * @access public
     *
     * @return string
     */
    public function get_default_heading() {
        return '{site_title}';
    }

    /**
     * Default content to show below main email content.
     *
     * @since 1.0
     * @access public
     *
     * @return string
     */
    public function get_default_additional_content() {
        return '';
    }

    /**
     * Set gift card instance.
     *
     * @since 1.0
     * @access public
     *
     * @param Advanced_Gift_card $gift_card Gift card object.
     */
    public function set_gift_card( Advanced_Gift_card $gift_card ) {
        $this->_gift_card = $gift_card;

        $recipient_data  = \AGCFW()->Helper_Functions->get_gift_card_recipient_data( $gift_card );
        $datetime_format = \AGCFW()->Helper_Functions->get_wp_datetime_format();
        $date_expire     = $gift_card->get_date( 'date_expire' );
        $first_name      = ( $this->object && $this->object->get_billing_first_name() ) ? $this->object->get_billing_first_name() : '';
        $last_name       = ( $this->object && $this->object->get_billing_last_name() ) ? $this->object->get_billing_last_name() : '';

        // update placeholders.
        $this->placeholders['{gift_card_code}']     = $gift_card->get_code();
        $this->placeholders['{gift_card_value}']    = \ACFWF()->Helper_Functions->api_wc_price( $gift_card->get_value() );
        $this->placeholders['{gift_card_expire}']   = $date_expire ? $gift_card->get_date( 'date_expire' )->date_i18n( $datetime_format ) : '';
        $this->placeholders['{gift_card_receiver}'] = $recipient_data['name'] ?? '';
        $this->placeholders['{gift_card_sender}']   = $first_name && $last_name ? sprintf( '%s %s', $first_name, $last_name ) : '';
    }

    /**
     * Set design image.
     *
     * @since 1.0
     * @access public
     *
     * @param string $image_src Image src.
     */
    public function set_design_image( $image_src ) {
        $this->_design_image = $image_src;
    }

    /**
     * Set message.
     *
     * @since 1.0
     * @access public
     *
     * @param string $message Message text.
     */
    public function set_message( $message ) {
        $this->_message = $message;
    }

    /**
     * Trigger the sending of this email.
     *
     * @since 1.0
     * @access public
     *
     * @param Advanced_Gift_Card    $gift_card Gift card object.
     * @param WC_Order_Item_Product $item      Product order item object.
     * @param WC_Order              $order     Order object.
     */
    public function trigger( $gift_card, $item, $order ) {
        do_action( 'agcfw_before_send_gift_card_email', $gift_card, $item, $order );

        $this->object = $order;
        $this->setup_locale();
        $this->set_gift_card( $gift_card );

        $this->_design_image = $item->get_product()->get_gift_card_design_image_src();
        $this->_message      = '';

        $email_already_sent = $item->get_meta( \AGCFW()->Plugin_Constants->EMAIL_ALREADY_SENT_META );

        /**
         * Controls if gift card emails can be resend multiple times.
         */
        if ( 'yes' === $email_already_sent && ! apply_filters( 'agcfw_allow_resend_gift_card_email', true ) ) {
            return;
        }

        $recipient_data  = \AGCFW()->Helper_Functions->get_gift_card_recipient_data( $gift_card );
        $this->_message  = $recipient_data['short_message'] ?? '';
        $this->recipient = $recipient_data['email'] ?? $order->get_billing_email();

        if ( $this->is_enabled() && $this->get_recipient() ) {

            $this->send(
                $this->get_recipient(),
                $this->get_subject(),
                $this->get_content(),
                $this->get_headers(),
                $this->get_attachments()
            );

            // update delivery date order item meta value to the actual timestamp the email was sent.
            if ( $item->get_meta( \AGCFW()->Plugin_Constants->GIFT_CARD_DELIVERY_DATE_META ) ) {
                $item->update_meta_data( \AGCFW()->Plugin_Constants->GIFT_CARD_DELIVERY_DATE_META, time() );
            }

            $item->update_meta_data( \AGCFW()->Plugin_Constants->EMAIL_ALREADY_SENT_META, 'yes' );
            $item->save();
        }

        $this->restore_locale();

        do_action( 'agcfw_after_send_gift_card_email', $gift_card, $item, $order );
    }

    /**
     * Trigger sending an email for a gift card manually created in the admin.
     *
     * @since 1.3.7
     * @access public
     *
     * @param Advanced_Gift_Card $gift_card Gift card object.
     * @param array              $recipient_data Recipient data.
     */
    public function manual_gift_card_trigger( $gift_card, $recipient_data ) {
        do_action( 'agcfw_before_send_manual_gift_card_email', $gift_card, $recipient_data );

        $this->setup_locale();
        $this->set_gift_card( $gift_card );

        $this->_message  = $recipient_data['message'] ?? '';
        $this->recipient = $recipient_data['email'] ?? '';

        if ( $this->is_enabled() && $this->get_recipient() ) {
            $this->send(
                $this->get_recipient(),
                $this->get_subject(),
                $this->get_content(),
                $this->get_headers(),
                $this->get_attachments()
            );
        }

        $this->restore_locale();

        do_action( 'agcfw_after_send_manual_gift_card_email', $gift_card, $recipient_data );
    }

    /**
     * Override setup locale function to remove customer email check.
     *
     * @since 1.0
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
     * @since 1.0
     * @access public
     */
    public function restore_locale() {
        if ( apply_filters( 'woocommerce_email_restore_locale', true ) ) {
            wc_restore_locale();
        }
    }

    /**
     * Get content html.
     *
     * @since 1.0
     * @access public
     */
    public function get_content_html() {
        ob_start();
        \AGCFW()->Helper_Functions->load_template(
            $this->template_html,
            array(
                'gift_card'          => $this->_gift_card,
                'design_image'       => $this->_design_image,
                'message'            => $this->_message,
                'order'              => $this->object,
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'email'              => $this,
            )
        );
        return ob_get_clean();
    }

    /**
     * Get content plain
     *
     * @since 1.0
     * @access public
     */
    public function get_content_plain() {
        ob_start();
        \AGCFW()->Helper_Functions->load_template(
            $this->template_plain,
            array(
                'gift_card'          => $this->_gift_card,
                'design_image'       => $this->_design_image,
                'message'            => $this->_message,
                'order'              => $this->object,
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'email'              => $this,
            )
        );
        return ob_get_clean();
    }

    /**
     * Initialize settings form fields.
     *
     * @since 1.0
     * @access public
     */
    public function init_form_fields() {
        /* translators: %s is the list of available placeholders */
        $placeholder_text  = sprintf( __( 'Available placeholders: %s', 'advanced-gift-cards-for-woocommerce' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' );
        $this->form_fields = array(
            'enabled'            => array(
                'title'   => __( 'Enable/Disable', 'advanced-gift-cards-for-woocommerce' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'advanced-gift-cards-for-woocommerce' ),
                'default' => 'yes',
            ),
            'subject'            => array(
                'title'       => __( 'Subject', 'advanced-gift-cards-for-woocommerce' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'            => array(
                'title'       => __( 'Email heading', 'advanced-gift-cards-for-woocommerce' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'additional_content' => array(
                'title'       => __( 'Additional content', 'advanced-gift-cards-for-woocommerce' ),
                'description' => __( 'Text to appear below the main email content.', 'advanced-gift-cards-for-woocommerce' ) . ' ' . $placeholder_text,
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __( 'N/A', 'advanced-gift-cards-for-woocommerce' ),
                'type'        => 'textarea',
                'default'     => $this->get_default_additional_content(),
                'desc_tip'    => true,
            ),
            'email_type'         => array(
                'title'       => __( 'Email type', 'advanced-gift-cards-for-woocommerce' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'advanced-gift-cards-for-woocommerce' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }
}
