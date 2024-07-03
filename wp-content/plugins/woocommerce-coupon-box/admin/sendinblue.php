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

class VI_WOOCOMMERCE_COUPON_BOX_Admin_Sendinblue {
	protected $settings;
	protected $api_key;

	function __construct() {
		$this->settings = new VI_WOOCOMMERCE_COUPON_BOX_DATA();
		$this->api_key  = $this->settings->get_params( 'wcb_sendinblue_api' );
	}

	public function get_lists() {
		if ( ! $this->api_key ) {
			return array();
		}

		try {
			$r = wp_remote_get( 'https://api.sendinblue.com/v3/contacts/lists?limit=50&offset=0&sort=desc', [
				'headers' => [
					'api-key'      => $this->api_key,
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json',
				]
			] );

			$body = wp_remote_retrieve_body( $r );

			return json_decode( $body )->lists;

		} catch ( \Exception $e ) {

		}

		return [];
	}

	public function add_recipient( $email = '', $list_id = [], $firstname = '', $lastname = '' ) {
		if ( ! $this->api_key || ! $email ) {
			return;
		}

		$list_id = array_map( 'absint', (array) $list_id );

		$body = json_encode( [
			'email'         => $email,
			"attributes"    => [
				"FIRSTNAME" => $firstname,
				"LASTNAME"  => $lastname
			],
			"listIds"       => $list_id,
			"updateEnabled" => false,
		] );

		$r = wp_remote_post( 'https://api.sendinblue.com/v3/contacts', [
			'body'    => $body,
			'headers' => [
				'api-key'      => $this->api_key,
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json',
			],
		] );
	}

}
