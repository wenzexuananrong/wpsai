<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class VIWCB_Elementor_Widget extends Elementor\Widget_Base {

	public static $slug = 'viwcb-elementor-widget';

	public function get_name() {
		return 'woocommerce-coupon-box';
	}

	public function get_title() {
		return esc_html__( 'Woo Coupon Box', 'woocommerce-coupon-box' );
	}

	public function get_icon() {
		return 'fas fa-gift';
	}

	public function get_categories() {
		return [ 'woocommerce-elements' ];
	}

	protected function _register_controls() {
		$settings = new  VI_WOOCOMMERCE_COUPON_BOX_DATA();
		$this->start_controls_section(
			'general',
			[
				'label' => esc_html__( 'General', 'woocommerce-coupon-box' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'always_visible',
			[
				'label'        => esc_html__( 'Always Visible', 'woocommerce-coupon-box' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => '1',
				'description'  => esc_html__( 'Display subscription form everywhere even the user is logged or subscribed', 'woocommerce-coupon-box' ),
				'label_on'     => esc_html__( 'Yes', 'woocommerce-coupon-box' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-coupon-box' ),
				'return_value' => '1',
			]
		);
		$this->add_control(
			'bt_color',
			[
				'label'     => esc_html__( 'Button Color', 'woocommerce-coupon-box' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $settings->get_params( 'wcb_button_text_color' ),
				'selectors' => [
					'{{WRAPPER}} .woocommerce-coupon-box-widget .wcbwidget-newsletter span.wcbwidget-button' => 'color: {{VALUE}};',
				],
				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->add_control(
			'bt_bg_color',
			[
				'label'     => esc_html__( 'Button Background Color', 'woocommerce-coupon-box' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $settings->get_params( 'wcb_button_bg_color' ),
				'selectors' => [
					'{{WRAPPER}} .woocommerce-coupon-box-widget .wcbwidget-newsletter span.wcbwidget-button' => 'background-color: {{VALUE}};',
				],
				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->add_control(
			'bt_border_radius',
			[
				'label'     => esc_html__( 'Button Border Radius', 'woocommerce-coupon-box' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 0,
				'step'      => 1,
				'default'   => $settings->get_params( 'wcb_button_border_radius' ),
				'selectors' => [
					'{{WRAPPER}} .woocommerce-coupon-box-widget .wcbwidget-newsletter span.wcbwidget-button' => 'border-radius: {{VALUE}}px;',
				],
			]
		);
		$this->add_control(
			'input_border_radius',
			[
				'label'     => esc_html__( 'Input Border Radius', 'woocommerce-coupon-box' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 0,
				'step'      => 1,
				'default'   => $settings->get_params( 'wcb_email_input_border_radius' ),
				'selectors' => [
					'{{WRAPPER}} .woocommerce-coupon-box-widget .wcbwidget-newsletter input.wcbwidget-email' => 'border-radius: {{VALUE}}px;',
				],
			]
		);
		$this->end_controls_section();

	}

	protected function render() {
		$settings  = $this->get_settings_for_display();
		$shortcode = $this->viwcb_get_shortcode( $settings );
		$shortcode = do_shortcode( shortcode_unautop( $shortcode ) );
		echo $shortcode;
	}

	public function render_plain_content() {
		$settings  = $this->get_settings_for_display();
		$shortcode = $this->viwcb_get_shortcode( $settings );
		echo $shortcode;
	}

	private function viwcb_get_shortcode( $settings ) {
		$shortcode = "[wcb_widget always_visible='{$settings['always_visible']}' bt_color='{$settings['bt_color']}' bt_bg_color='{$settings['bt_bg_color']}' 
		 bt_border_radius='{$settings['bt_border_radius']}' input_border_radius='{$settings['input_border_radius']}']";

		return $shortcode;
	}
}