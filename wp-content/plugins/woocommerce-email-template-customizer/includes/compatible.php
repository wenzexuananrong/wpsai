<?php

namespace VIWEC\INCLUDES;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Compatible {

	protected static $instance = null;
	protected $correios_tracking_code;
	protected $automatewoo_heading;

	private function __construct() {
		/*-------------------Claudio Sanches - Correios for WooCommerce-------------------*/
		add_filter( 'viwec_register_replace_shortcode', [ $this, 'woocommerce_correios' ], 10, 3 );
		add_filter( 'woocommerce_api_create_order', array( $this, 'legacy_orders_update' ), 200, 2 );
		add_filter( 'woocommerce_api_edit_order', array( $this, 'legacy_orders_update' ), 200, 2 );
		add_filter( 'viwec_register_email_type', array( $this, 'register_email_type' ), 10, 2 );
		add_filter( 'viwec_live_edit_shortcodes', array( $this, 'register_render_preview_shortcode' ), 20 );
		add_filter( 'viwec_register_preview_shortcode', array( $this, 'register_render_preview_shortcode' ), 20 );
		/*^-------------------Claudio Sanches - Correios for WooCommerce-------------------*/


		/*-------------------AutomateWoo-------------------*/
		add_filter( 'automatewoo_email_templates', [ $this, 'register_custom_email_templates' ] );

		add_filter( 'automatewoo_action_option', [ $this, 'automatewoo_heading' ], 10, 4 );

		/*-------------------AutomateWoo-------------------*/

		/*------------------TrackShip for WooCommerce-----------------*/
		add_filter( 'trackship_mail_content', [ $this, 'trackship_mail_content' ], 99, 2 );
		/*------------------TrackShip for WooCommerce-----------------*/

	}

	public static function init() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/*begin-------------------Claudio Sanches - Correios for WooCommerce-------------------*/

	public function register_email_type( $emails, $has_order_elements ) {
		if ( class_exists( 'WC_Correios' ) ) {
			$emails['correios_tracking'] = [
				'name' => __( 'Correios Tracking Code', 'woo-abandoned-cart-recovery' ),
			];
		}

//		if ( class_exists( 'WCFMmp_Email_Store_New_Order' ) ) {
//			$emails['store-new-order'] = [
//				'name'            => __( 'Store New Order', 'wc-multivendor-marketplace' ),
//				'accept_elements' => $has_order_elements
//			];
//		}

		return $emails;
	}

	public function register_render_preview_shortcode( $sc ) {
		if ( class_exists( 'WC_Correios' ) ) {
			$sc['correios_tracking'] = array(
				'{correios_tracking_code}' => 'WC_Correios_Tracking_Code',
			);
		}

		return $sc;
	}

	public function legacy_orders_update( $order_id, $data ) {
		if ( isset( $data['correios_tracking_code'] ) ) {
			$this->correios_tracking_code = $data['correios_tracking_code'];
		}
	}

	public function woocommerce_correios( $shortcodes, $object, $args ) {
		if ( empty( $args ) ) {
			return $shortcodes;
		}

		if ( isset( $args['email'] ) && is_a( $args['email'], 'WC_Correios_Tracking_Email' ) ) {

			$tracking_code = ! empty( $_POST['tracking_code'] ) ? sanitize_text_field( $_POST['tracking_code'] ) : $this->correios_tracking_code;

			if ( empty( $tracking_code ) ) {
				$tracking_codes = wc_correios_get_tracking_codes( $object );
			} else {
				$tracking_codes = array( $tracking_code );
			}

//			$tracking_codes = $args['email']->get_tracking_codes( $tracking_codes );

			$_tracking_code = implode( $tracking_codes );

			$shortcodes['correios_tracking_code'] = [ '{correios_tracking_code}' => $_tracking_code ];
		}

		return $shortcodes;
	}

	/*end-------------------Claudio Sanches - Correios for WooCommerce-------------------*/


	public function register_custom_email_templates( $templates ) {
		$templates['viwec_template'] = 'WooCommerce Email Template Customizer';

		return $templates;
	}

	public function automatewoo_heading( $value, $field_name, $process_variables, $automate_email ) {
		if ( $field_name === 'email_heading' ) {
			$this->automatewoo_heading = $value;
		}

		if ( $field_name === 'template' && $value == 'viwec_template' ) {
			add_filter( 'woocommerce_mail_content', [ $this, 'use_default_template_for_wp_mail' ], 99 );
		}

		return $value;
	}


	public function use_default_template_for_wp_mail( $message ) {
		$default_temp_id = Email_Trigger::init()->get_default_template();
		$email_render    = Email_Render::init();

		$email_render->recover_heading       = $this->automatewoo_heading;
		$email_render->other_message_content = $message;
		$email_render->use_default_template  = true;
		$data                                = get_post_meta( $default_temp_id, 'viwec_email_structure', true );
		$data                                = json_decode( html_entity_decode( $data ), true );

		ob_start();
		$email_render->render( $data );
		$message = ob_get_clean();

		return $message;
	}

	/*Begin ---------------TrackShip for WooCommerce-----------------*/
	public function trackship_mail_content( $message, $trackship_heading ) {
		$default_temp_id = Email_Trigger::init()->get_default_template();
		$email_render    = Email_Render::init();


		$email_render->recover_heading       = $trackship_heading;
		$email_render->other_message_content = $message;
		$email_render->use_default_template  = true;
		$data                                = get_post_meta( $default_temp_id, 'viwec_email_structure', true );
		$data                                = json_decode( html_entity_decode( $data ), true );


		ob_start();
		$email_render->render( $data );
		$message = ob_get_clean();
		return $message;
	}
	/*End ---------------TrackShip for WooCommerce-----------------*/
}
