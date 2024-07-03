<?php
/*
Class Name: VI_WOOCOMMERCE_COUPON_BOX_Admin_Mailchimp
Author: Andy Ha (support@villatheme.com)
Author URI: http://villatheme.com
Copyright 2015 villatheme.com. All rights reserved.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_COUPON_BOX_Admin_Mailchimp {
	protected $settings;
	protected $api_key;

	function __construct() {
		$this->settings = new VI_WOOCOMMERCE_COUPON_BOX_DATA();
		$this->api_key=$this->settings->get_params('wcb_api');
	}

	function get_list($list_id) {
		if(!$this->api_key || !$list_id){
			return '';
		}
		$dataCenter = substr( $this->api_key, strpos( $this->api_key, '-' ) + 1 );
		$url        = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/'.$list_id;
		$ch = curl_init( $url );

		curl_setopt( $ch, CURLOPT_USERPWD, 'user:' . $this->api_key );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json'
		) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, '' );

		$response = curl_exec( $ch );
		$err      = curl_error( $ch );
		curl_close( $ch );
		if ( $err ) {
			return '';
		} else {
			$data = json_decode( $response );
			return $data;
		}
	}
	function get_lists() {
		if ( $this->api_key ) {
			$dash_position = strpos( $this->api_key, '-' );
			if ( $dash_position !== false ) {
				$api_url = 'https://' . substr( $this->api_key, $dash_position + 1 ) . '.api.mailchimp.com/3.0/lists?fields=lists.name,lists.id&count=1000';

				$ch = curl_init( $api_url . '&apikey=' . $this->api_key ); //set the url

				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "GET" );  //specify this as a POST

				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); //specify return value as string
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				curl_setopt(
					$ch, CURLOPT_HTTPHEADER, array(
						'Content-Type: application/json'
					)
				);

				$server_output = curl_exec( $ch ); //get server output if you wish to error handle / debug
				curl_close( $ch );
				$data = json_decode( $server_output, true );

				if ( isset( $data['status'] ) ) {
					return false;
				} else {
					if ( isset( $data['lists'] ) ) {
						$data  = $data['lists'];
						$lists = array();
						if ( count( $data ) ) {

							foreach ( $data as $list ) {
								$lists[ $list['id'] ] = $list['name'];
							}

						} else {
							return false;
						}

						return $lists;
					} else {
						return false;
					}
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function add_email( $email, $list_id, $merge_fields ) {

		if ( $this->api_key && $list_id ) {

			$merge_fields = wp_parse_args( $merge_fields, array(
				'FNAME'    => '',
				'LNAME'    => '',
				'PHONE'    => '',
				'BIRTHDAY' => ''
			) );

			$data = array(
				'email_address' => $email,
				'status'        => $this->settings->get_params( 'wcb_mailchimp_double_optin' ) ? 'pending' : 'subscribed',
				'merge_fields'  => $merge_fields
			);

			$dataCenter = substr( $this->api_key, strpos( $this->api_key, '-' ) + 1 );
			$url        = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/';
			$ch         = curl_init( $url );

			curl_setopt( $ch, CURLOPT_USERPWD, 'user:' . $this->api_key );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json'
			) );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );

			$result = curl_exec( $ch );
			curl_close( $ch );
			$data = json_decode( $result );

			switch ( $data->status ) {
				case 'subscribed':
					return true;
					break;
				default:
					return false;
			}
		} else {
			return false;
		}
	}
}
