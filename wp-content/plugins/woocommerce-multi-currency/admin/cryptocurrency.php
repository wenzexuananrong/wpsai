<?php

/**
 * Class WOOMULTI_CURRENCY_Admin_Cryptocurrency
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOOMULTI_CURRENCY_Admin_Cryptocurrency {
	protected $settings;

	public function __construct() {
		$this->settings = WOOMULTI_CURRENCY_Data::get_ins();
		add_filter( 'woocommerce_currencies', array( $this, 'woocommerce_currencies' ) );
		add_filter( 'woocommerce_currency_symbols', array( $this, 'woocommerce_currency_symbols' ) );
	}

	/**
	 * @param $currency
	 *
	 * @return mixed
	 */
	public function woocommerce_currencies( $currency ) {
		if ( is_admin() || $this->settings->get_enable() ) {
			$currency['LTC'] = __( 'Litecoin', 'woocommerce-multi-currency' );
			$currency['ETH'] = __( 'Ethereum', 'woocommerce-multi-currency' );
			$currency['ZWD'] = __( 'Zimbabwe Dollar', 'woocommerce-multi-currency' );
			$currency['ZWL'] = __( 'Zimbabwe', 'woocommerce-multi-currency' );
		}

		return $currency;
	}

	/**
	 * @param $symbols
	 *
	 * @return mixed
	 */
	public function woocommerce_currency_symbols( $symbols ) {
		if ( is_admin() || $this->settings->get_enable() ) {
			$symbols['LTC'] = "LTC";
			$symbols['ETH'] = "ETH";
			$symbols['ZWD'] = "ZWD";
			$symbols['ZWL'] = "ZWL";
		}

		return $symbols;
	}
}