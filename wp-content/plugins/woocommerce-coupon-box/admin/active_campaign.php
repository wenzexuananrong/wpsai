<?php
/*
Class Name: VI_WOOCOMMERCE_COUPON_BOXP_Admin_Active_Campaign
Author: Andy Ha (support@villatheme.com)
Author URI: http://villatheme.com
Copyright 2015 villatheme.com. All rights reserved.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_COUPON_BOXP_Admin_Active_Campaign {
	protected $settings;
	protected $api_key;
	protected $url;

	function __construct() {
		$this->settings = new VI_WOOCOMMERCE_COUPON_BOX_DATA();
		$this->api_key  = $this->settings->get_params( 'wcb_active_campaign_api' );
		$this->url      = $this->settings->get_params( 'wcb_active_campaign_url' );
	}

	public function contact_add( $email, $list = '', $firstname = '', $lastname = '', $phone = '', $orgname = '', $tags = 'api' ) {
		if ( ! $this->api_key || ! $this->url ) {
			return;
		}
		// By default, this sample code is designed to get the result from your ActiveCampaign installation and print out the result
		$url = $this->url;


		$params = array(

			// the API Key can be found on the "Your Settings" page under the "API" tab.
			// replace this with your API Key
			'api_key'    => $this->api_key,

			// this is the action that adds a contact
			'api_action' => 'contact_add',

			// define the type of output you wish to get back
			// possible values:
			// - 'xml'  :      you have to write your own XML parser
			// - 'json' :      data is returned in JSON format and can be decoded with
			//                 json_decode() function (included in PHP since 5.2.0)
			// - 'serialize' : data is returned in a serialized format and can be decoded with
			//                 a native unserialize() function
			'api_output' => 'serialize',
		);

// here we define the data we are posting in order to perform an update
		$post = array(
			'email'      => $email,
			'first_name' => $firstname,
			'last_name'  => $lastname,
			'phone'      => $phone,
			'orgname'    => $orgname,
			'tags'       => $tags,
			//'ip4'                    => '127.0.0.1',

			// any custom fields
			//'field[345,0]'           => 'field value', // where 345 is the field ID
			//'field[%PERS_1%,0]'      => 'field value', // using the personalization tag instead (make sure to encode the key)

			// 1: active, 2: unsubscribed (REPLACE '123' WITH ACTUAL LIST ID, IE: status[5] = 1)
			//'form'          => 1001, // Subscription Form ID, to inherit those redirection settings
			//'noresponders[123]'      => 1, // uncomment to set "do not send any future responders"
			//'sdate[123]'             => '2009-12-07 06:00:00', // Subscribe date for particular list - leave out to use current date/time
			// use the folowing only if status=1
//			'instantresponders[123]' => 1, // set to 0 to if you don't want to sent instant autoresponders
			//'lastmessage[123]'       => 1, // uncomment to set "send the last broadcast campaign"

			//'p[]'                    => 345, // some additional lists?
			//'status[345]'            => 1, // some additional lists?
		);
		if ( $list ) {
			$post[ 'p[' . $list . ']' ]      = $list;
			$post[ 'status[' . $list . ']' ] = 1;
		}
// This section takes the input fields and converts them to the proper format
		$query = "";
		foreach ( $params as $key => $value ) {
			$query .= urlencode( $key ) . '=' . urlencode( $value ) . '&';
		}
		$query = rtrim( $query, '& ' );

// This section takes the input data and converts it to the proper format
		$data = "";
		foreach ( $post as $key => $value ) {
			$data .= urlencode( $key ) . '=' . urlencode( $value ) . '&';
		}
		$data = rtrim( $data, '& ' );

// clean up the url
		$url = rtrim( $url, '/ ' );

// This sample code uses the CURL library for php to establish a connection,
// submit your request, and show (print out) the response.
		if ( ! function_exists( 'curl_init' ) ) {
			return;
		}

// If JSON is used, check if json_decode is present (PHP 5.2.0+)
		if ( $params['api_output'] == 'json' && ! function_exists( 'json_decode' ) ) {
			return;
		}

// define a final API request - GET
		$api = $url . '/admin/api.php?' . $query;

		$request = curl_init( $api ); // initiate curl object
		curl_setopt( $request, CURLOPT_HEADER, 0 ); // set to 0 to eliminate header info from response
		curl_setopt( $request, CURLOPT_RETURNTRANSFER, 1 ); // Returns response data instead of TRUE(1)
		curl_setopt( $request, CURLOPT_POSTFIELDS, $data ); // use HTTP POST to send form data
//curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment if you get no gateway response and are using HTTPS
		curl_setopt( $request, CURLOPT_FOLLOWLOCATION, true );

		$response = (string) curl_exec( $request ); // execute curl post and store results in $response

// additional options may be required depending upon your server configuration
// you can find documentation on curl options at http://www.php.net/curl_setopt
		curl_close( $request ); // close curl object
	}

	public function list_list( $name ) {
		if ( ! $this->api_key || ! $this->url ) {
			return '';
		}
		$url = $this->url;

		$params = array(

			// the API Key can be found on the "Your Settings" page under the "API" tab.
			// replace this with your API Key
			'api_key' => $this->api_key,

			'api_action'    => 'list_list',

			// define the type of output you wish to get back
			// possible values:
			// - 'json' :      data is returned in JSON format and can be decoded with
			//                 json_decode() function (included in PHP since 5.2.0)
			'api_output'    => 'json',

			// optional: change how results are sorted (default is below)
			//'sort' => 'id', // possible values: id, datetime
			//'sort_direction' => 'DESC', // ASC or DESC
			//'page' => 2, // pagination - results are limited to 20 per page, so specify what page to view (default is 1)

			// filters: supply filters that will narrow down the results
			'filters[name]' => $name,
			// perform a pattern match (LIKE) for List Name

			// include global custom fields? by default, it does not
			//'global_fields'      => true,

		);

// This section takes the input fields and converts them to the proper format
		$query = "";
		foreach ( $params as $key => $value ) {
			$query .= $key . '=' . urlencode( $value ) . '&';
		}
		$query = rtrim( $query, '& ' );

// clean up the url
		$url = rtrim( $url, '/ ' );

// This sample code uses the CURL library for php to establish a connection,
// submit your request, and show (print out) the response.
		if ( ! function_exists( 'curl_init' ) ) {
			return '';
		}

// If JSON is used, check if json_decode is present (PHP 5.2.0+)
		if ( $params['api_output'] == 'json' && ! function_exists( 'json_decode' ) ) {
			return '';
		}

// define a final API request - GET
		$api = $url . '/admin/api.php?' . $query;

		$request = curl_init( $api ); // initiate curl object
		curl_setopt( $request, CURLOPT_HEADER, 0 ); // set to 0 to eliminate header info from response
		curl_setopt( $request, CURLOPT_RETURNTRANSFER, 1 ); // Returns response data instead of TRUE(1)
//curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment if you get no gateway response and are using HTTPS
		curl_setopt( $request, CURLOPT_FOLLOWLOCATION, true );

		$response = (string) curl_exec( $request ); // execute curl fetch and store results in $response

// additional options may be required depending upon your server configuration
// you can find documentation on curl options at http://www.php.net/curl_setopt
		curl_close( $request ); // close curl object

		if ( ! $response ) {
			return '';
		}

// This line takes the response and breaks it into an array using:
// JSON decoder
		$result = json_decode( $response, true );

//		$result = unserialize($response);
		return $result;
	}

	public function list_view( $id ) {
		if ( ! $this->api_key || ! $this->url ) {
			return '';
		}
// By default, this sample code is designed to get the result from your ActiveCampaign installation and print out the result
		$url    = $this->url;
		$params = array(

			// the API Key can be found on the "Your Settings" page under the "API" tab.
			// replace this with your API Key
			'api_key'    => $this->api_key,

			// this is the action that fetches a list info based on the ID you provide
			'api_action' => 'list_view',

			// define the type of output you wish to get back
			// possible values:
			// - 'xml'  :      you have to write your own XML parser
			// - 'json' :      data is returned in JSON format and can be decoded with
			//                 json_decode() function (included in PHP since 5.2.0)
			// - 'serialize' : data is returned in a serialized format and can be decoded with
			//                 a native unserialize() function
			'api_output' => 'serialize',

			// ID of the list you wish to fetch
			'id'         => $id,
		);

// This section takes the input fields and converts them to the proper format
		$query = "";
		foreach ( $params as $key => $value ) {
			$query .= urlencode( $key ) . '=' . urlencode( $value ) . '&';
		}
		$query = rtrim( $query, '& ' );

// clean up the url
		$url = rtrim( $url, '/ ' );

// This sample code uses the CURL library for php to establish a connection,
// submit your request, and show (print out) the response.
		if ( ! function_exists( 'curl_init' ) ) {
			return '';
		}

// If JSON is used, check if json_decode is present (PHP 5.2.0+)
		if ( $params['api_output'] == 'json' && ! function_exists( 'json_decode' ) ) {
			return '';
		}

// define a final API request - GET
		$api = $url . '/admin/api.php?' . $query;

		$request = curl_init( $api ); // initiate curl object
		curl_setopt( $request, CURLOPT_HEADER, 0 ); // set to 0 to eliminate header info from response
		curl_setopt( $request, CURLOPT_RETURNTRANSFER, 1 ); // Returns response data instead of TRUE(1)
//curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment if you get no gateway response and are using HTTPS
		curl_setopt( $request, CURLOPT_FOLLOWLOCATION, true );

		$response = (string) curl_exec( $request ); // execute curl fetch and store results in $response

// additional options may be required depending upon your server configuration
// you can find documentation on curl options at http://www.php.net/curl_setopt
		curl_close( $request ); // close curl object

		if ( ! $response ) {
			return '';
		}

// This line takes the response and breaks it into an array using:
// JSON decoder
//$result = json_decode($response);
// unserializer
		$result = unserialize( $response );

		return $result;
	}
}
