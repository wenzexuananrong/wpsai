<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_COUPON_BOX_3RD_Viwec_Template {
	protected $settings;

	public function __construct() {
		$this->settings = new VI_WOOCOMMERCE_COUPON_BOX_DATA();
		add_filter( 'viwec_register_email_type', array( $this, 'register_email_type' ) );
		add_filter( 'viwec_sample_subjects', array( $this, 'register_email_sample_subject' ) );
		add_filter( 'viwec_sample_templates', array( $this, 'register_email_sample_template' ) );
		add_filter( 'viwec_live_edit_shortcodes', array( $this, 'register_render_preview_shortcode' ) );
		add_filter( 'viwec_register_preview_shortcode', array( $this, 'register_render_preview_shortcode' ) );
	}

	public function register_render_preview_shortcode( $sc ) {
		$date_format     = get_option( 'date_format' );
		$date_expires    = strtotime( '+30 days' );
		$sc['wcb_email'] = array(
			'{wcb_coupon_value}'    => '10%',
			'{wcb_coupon_code}'     => 'HAPPY',
			'{wcb_customer_name}'   => 'John',
			'{wcb_date_expires}'    => date_i18n( $date_format, $date_expires ),
			'{wcb_last_valid_date}' => date_i18n( $date_format, ( $date_expires - 86400 ) ),
			'{wcb_site_title}'      => get_bloginfo( 'name' ),
		);

		return $sc;
	}

	public function register_email_sample_template( $samples ) {
		$samples['wcb_email'] = [
			'basic' => [
				'name' => esc_html__( 'Basic', 'woocommerce-coupon-box' ),
				'data' => '{"style_container":{"background-color":"#f2f2f2","background-image":"none","width":600,"responsive":"380"},"rows":{"0":"216","1":{"props":{"style_outer":{"padding":"25px","background-image":"none","background-color":"#f9f9f9","border-color":"#444444","border-style":"solid","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","width":"100%"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","width":"550px"}},"elements":{"0":{"type":"html/text","style":{"width":"550px","line-height":"28px","background-image":"none","padding":"0px","border-color":"#444444","border-style":"solid","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"font-size: 24px; color: #444444;\">{wcb_coupon_value} OFF DISCOUNT COUPON CODE OFFER</span></p>"},"attrs":{"data-center_on_mobile":""},"childStyle":{}}}}}},"2":{"props":{"style_outer":{"padding":"35px 35px 10px","background-image":"none","background-color":"#ffffff","border-color":"#444444","border-style":"solid","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","width":"100%"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","width":"530px"}},"elements":{"0":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","padding":"0px","border-color":"#444444","border-style":"solid","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px"},"content":{"text":"<p>Thanks for signing up for our newsletter.</p>\n<p>&nbsp;</p>\n<p>Enjoy your discount code for {wcb_coupon_value} OFF until {wcb_last_valid_date}. Don\'t miss out this great chance on our shop.</p>"},"attrs":{"data-center_on_mobile":""},"childStyle":{}},"1":{"type":"html/button","style":{"width":"530px","font-size":"15px","font-weight":"400","font-family":"Helvetica, Arial, sans-serif","color":"#1de712","line-height":"40px","text-align":"center","padding":"20px 0px 20px 1px"},"content":{"text":"{wcb_coupon_code}"},"attrs":{"href":"{shop_url}"},"childStyle":{"a":{"border-width":"2px","border-radius":"0px","border-color":"#162447","border-style":"dashed","background-color":"#ffffff","width":"141px"}}},"2":{"type":"html/button","style":{"width":"530px","font-size":"16px","font-weight":"400","font-family":"Helvetica, Arial, sans-serif","color":"#ffffff","line-height":"40px","text-align":"center","padding":"20px 0px 20px 1px"},"content":{"text":"Shop Now"},"attrs":{"href":"{shop_url}"},"childStyle":{"a":{"border-width":"0px","border-radius":"0px","border-color":"#ffffff","border-style":"dashed","background-color":"#52d2aa","width":"141px"}}}}}}},"3":{"props":{"style_outer":{"padding":"25px 35px","background-image":"none","background-color":"#162447","border-color":"#444444","border-style":"solid","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","width":"100%"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","width":"530px"}},"elements":{"0":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","padding":"0px","border-color":"#444444","border-style":"solid","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #f5f5f5; font-size: 20px;\">Get in Touch</span></p>"},"attrs":{"data-center_on_mobile":""},"childStyle":{}},"1":{"type":"html/social","style":{"width":"530px","text-align":"center","padding":"20px 0px 0px","background-image":"none"},"content":{},"attrs":{"facebook":"' . VIWEC_IMAGES . 'fb-blue-white.png","facebook_url":"#","twitter":"' . VIWEC_IMAGES . 'twi-cyan-white.png","twitter_url":"#","instagram":"' . VIWEC_IMAGES . 'ins-white-color.png","instagram_url":"#","youtube":"' . VIWEC_IMAGES . 'yt-color-white.png","youtube_url":"","linkedin":"' . VIWEC_IMAGES . 'li-color-white.png","linkedin_url":"","whatsapp":"' . VIWEC_IMAGES . 'wa-color-white.png","whatsapp_url":"","telegram":"' . VIWEC_IMAGES . 'tele-color-white.png","telegram_url":"","tiktok":"' . VIWEC_IMAGES . 'tiktok-color-white.png","tiktok_url":"","pinterest":"' . VIWEC_IMAGES . 'pin-color-white.png","pinterest_url":"","direction":"","data-width":""},"childStyle":{}},"2":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","padding":"20px 0px","border-color":"#444444","border-style":"solid","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #f5f5f5; font-size: 12px;\">This email was sent by : <span style=\"color: #ffffff;\"><a style=\"color: #ffffff;\" href=\"{admin_email}\">{admin_email}</a></span></span></p>\n<p style=\"text-align: center;\"><span style=\"color: #f5f5f5; font-size: 12px;\">For any questions please send an email to <span style=\"color: #ffffff;\"><a style=\"color: #ffffff;\" href=\"{admin_email}\">{admin_email}</a></span></span></p>"},"attrs":{"data-center_on_mobile":""},"childStyle":{}},"3":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","padding":"0px","border-color":"#444444","border-style":"solid","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #f5f5f5;\"><span style=\"color: #f5f5f5;\"><span style=\"font-size: 12px;\"><a style=\"color: #f5f5f5;\" href=\"#\">Privacy Policy</a>&nbsp; |&nbsp; <a style=\"color: #f5f5f5;\" href=\"#\">Help Center</a></span></span></span></p>"},"attrs":{"data-center_on_mobile":""},"childStyle":{}}}}}}}}'
			],
		];

		return $samples;
	}

	public function register_email_sample_subject( $subjects ) {
		$subjects['wcb_email'] = __( 'Thank you for subscribing', 'woocommerce-coupon-box' );

		return $subjects;
	}

	public function register_email_type( $types ) {
		$types['wcb_email'] = array(
			'name'       => esc_html__( 'WooCommerce Coupon Box', 'woocommerce-coupon-box' ),
			'hide_rules' => array( 'country', 'category','products', 'min_order', 'max_order' ),
		);

		return $types;
	}
}