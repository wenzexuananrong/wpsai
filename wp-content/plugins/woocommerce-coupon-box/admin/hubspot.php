<?php
/*
Class Name: VI_WOOCOMMERCE_COUPON_BOX_Admin_Sendgrid
Author: Andy Ha (support@villatheme.com)
Author URI: http://villatheme.com
Copyright 2015 villatheme.com. All rights reserved.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_COUPON_BOX_Admin_Hubspot {
	protected $settings;
	protected $api_key;

	function __construct() {
		$this->settings = new VI_WOOCOMMERCE_COUPON_BOX_DATA();
		$this->api_key  = $this->settings->get_params( 'wcb_hubspot_api' );
	}

	public function add_recipient( $email = '', $firstname = '', $lastname = '', $phone = '' ) {
		if ( ! $this->api_key ) {
			return;
		}

		if ( ! $email ) {
			return;
		}

		$arr = array(
			'properties' => array(
				array(
					'property' => 'email',
					'value'    => $email
				),
				array(
					'property' => 'firstname',
					'value'    => $firstname
				),
				array(
					'property' => 'lastname',
					'value'    => $lastname
				),
				array(
					'property' => 'phone',
					'value'    => $phone
				)
			)
		);

		$res = wp_remote_post( 'https://api.hubapi.com/contacts/v1/contact?hapikey=' . $this->api_key, [
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => json_encode( $arr )
		] );
	}
}
