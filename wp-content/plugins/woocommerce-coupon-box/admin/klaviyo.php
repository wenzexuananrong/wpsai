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

class VI_WOOCOMMERCE_COUPON_BOX_Admin_Klaviyo {
	protected $settings;
	protected $api_key;

	function __construct() {
		$this->settings = new VI_WOOCOMMERCE_COUPON_BOX_DATA();
		$this->api_key  = $this->settings->get_params( 'wcb_klaviyo_api' );
	}

	public function get_lists() {
		if ( ! $this->api_key ) {
			return array();
		}

		try {
			$r = wp_remote_get( 'https://a.klaviyo.com/api/v2/lists?api_key=' . $this->api_key );

			$body = wp_remote_retrieve_body( $r );

			return json_decode( $body );

		} catch ( \Exception $e ) {

		}

		return [];
	}

	public function add_recipient( $email = '', $list_id = '', $firstname = '', $lastname = '' ) {
		if ( ! $this->api_key || ! $email || ! $list_id ) {
			return;
		}

		$body = json_encode( [
			'profiles' => [
				[
					'email'      => $email,
					'first_name' => $firstname,
					'last_name'  => $lastname
				],
			]
		] );

		$r = wp_remote_post( "https://a.klaviyo.com/api/v2/list/{$list_id}/members?api_key=" . $this->api_key, [
			'body'    => $body,
			'headers' => [
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json',
			],
		] );
	}

}
