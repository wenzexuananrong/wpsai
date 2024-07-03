<?php
/*
Class Name: VI_WOOCOMMERCE_COUPON_BOX_Admin_Getresponse
Author: Andy Ha (support@villatheme.com)
Author URI: http://villatheme.com
Copyright 2015 villatheme.com. All rights reserved.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_COUPON_BOX_Admin_Getresponse {
	protected $settings;
	protected $api_key;

	function __construct() {
		$this->settings = new VI_WOOCOMMERCE_COUPON_BOX_DATA();
		$this->api_key  = $this->settings->get_params( 'wcb_getresponse_api' );
	}

	public function get_lists() {
		if ( ! $this->api_key ) {
			return array();
		}

		try {
			$r = wp_remote_get( 'https://api.getresponse.com/v3/campaigns', [
				'headers' => [
					'X-Auth-Token' => 'api-key ' . $this->api_key,
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json',
				]
			] );

			$body = wp_remote_retrieve_body( $r );

			return json_decode( $body );

		} catch ( \Exception $e ) {

		}

		return [];
	}

	public function add_recipient( $email = '', $list_id = '', $firstname = '', $lastname = '', $ip_address = '' ) {
		if ( ! $this->api_key || ! $email || empty( $list_id ) ) {
			return;
		}

//		$list_id = array_map( 'absint', (array) $list_id );
//		$list_object = [];

		$body_object = [
			'email'     => $email,
			'campaign'  => [
				'campaignId' => $list_id
			]
		];

		if ( ! empty( $firstname ) || ! empty( $lastname ) ) {
			$body_object['name'] = $firstname . ' ' . $lastname;
		}

		if ( ! empty( $ip_address ) ) {
			$body_object['ipAddress'] = $ip_address;
		}

		$body = json_encode( $body_object );

		$r = wp_remote_post( 'https://api.getresponse.com/v3/contacts', [
			'body'    => $body,
			'headers' => [
				'X-Auth-Token' => 'api-key ' . $this->api_key,
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json',
			],
		] );

	}

}