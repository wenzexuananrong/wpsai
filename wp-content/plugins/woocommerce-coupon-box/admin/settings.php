<?php
/*
Class Name: VI_WOOCOMMERCE_COUPON_BOX_Admin_Admin
Author: Andy Ha (support@villatheme.com)
Author URI: http://villatheme.com
Copyright 2015 villatheme.com. All rights reserved.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_COUPON_BOX_Admin_Settings {
	protected $settings;
	protected $languages;
	protected $default_language;
	protected $languages_data;

	public function __construct() {

		$this->settings         = new VI_WOOCOMMERCE_COUPON_BOX_DATA();
		$this->languages        = array();
		$this->languages_data   = array();
		$this->default_language = '';
		add_action( 'admin_menu', array( $this, 'create_options_page' ), 998 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ), 999999999 );
		add_action( 'admin_init', array( $this, 'save_data_coupon_box' ), 99 );

		/*ajax search*/
		add_action( 'wp_ajax_wcb_search_coupon', array( $this, 'search_coupon' ) );
		add_action( 'wp_ajax_wcb_search_product', array( $this, 'search_product' ) );
		add_action( 'wp_ajax_wcb_search_cate', array( $this, 'search_cate' ) );
		add_action( 'wp_ajax_wcb_search_active_campaign_list', array( $this, 'search_active_campaign_list' ) );
		/*preview email*/
		add_action( 'media_buttons', array( $this, 'preview_emails_button' ) );
		add_action( 'wp_ajax_wcb_preview_emails', array( $this, 'preview_emails_ajax' ) );
		add_action( 'admin_footer', array( $this, 'preview_emails_html' ) );

	}

	function preview_emails_html() {
		global $pagenow;
		if ( $pagenow == 'edit.php' && isset( $_REQUEST['post_type'] ) && isset( $_REQUEST['page'] ) && $_REQUEST['post_type'] == 'wcb' && $_REQUEST['page'] == 'woocommerce_coupon_box' ) {
			?>
            <div class="preview-emails-html-container preview-html-hidden">
                <div class="preview-emails-html-overlay"></div>
                <div class="preview-emails-html"></div>
            </div>
			<?php
		}
	}

	public function preview_emails_button( $editor_id ) {
		if ( function_exists( 'get_current_screen' ) ) {
			if ( get_current_screen()->id === 'wcb_page_woocommerce_coupon_box' ) {
				$editor_ids = array( 'wcb_email_content' );
				if ( count( $this->languages ) ) {
					foreach ( $this->languages as $key => $value ) {
						$editor_ids[] = 'wcb_email_content_' . $value;
					}
				}
				if ( in_array( $editor_id, $editor_ids ) ) {
					ob_start();
					?>
                    <span class="button wcb-preview-emails-button"
                          data-wcb_language="<?php echo str_replace( 'wcb_email_content', '', $editor_id ) ?>"><?php esc_html_e( 'Preview emails', 'woocommerce-coupon-box' ) ?></span>
					<?php
					echo ob_get_clean();
				}
			}
		}
	}

	public function preview_emails_ajax() {
		$date_format          = get_option( 'date_format' );
		$content              = isset( $_GET['content'] ) ? wp_kses_post( stripslashes( $_GET['content'] ) ) : '';
		$email_heading        = isset( $_GET['heading'] ) ? ( stripslashes( $_GET['heading'] ) ) : '';
		$button_shop_url      = isset( $_GET['button_shop_url'] ) ? ( stripslashes( $_GET['button_shop_url'] ) ) : '';
		$button_shop_size     = isset( $_GET['button_shop_size'] ) ? ( stripslashes( $_GET['button_shop_size'] ) ) : '';
		$button_shop_color    = isset( $_GET['button_shop_color'] ) ? ( stripslashes( $_GET['button_shop_color'] ) ) : '';
		$button_shop_bg_color = isset( $_GET['button_shop_bg_color'] ) ? ( stripslashes( $_GET['button_shop_bg_color'] ) ) : '';
		$button_shop_title    = isset( $_GET['button_shop_title'] ) ? ( stripslashes( $_GET['button_shop_title'] ) ) : '';

		$button_shop_now = '<a href="' . $button_shop_url . '" target="_blank" style="text-decoration:none;display:inline-block;padding:10px 30px;margin:10px 0;font-size:' . $button_shop_size . 'px;color:' . $button_shop_color . ';background:' . $button_shop_bg_color . ';">' . $button_shop_title . '</a>';
		$coupon_value    = '10%';
		$coupon_code     = 'HAPPY';
		$date_expires    = strtotime( '+30 days' );
		$customer_name   = 'John';
		$content         = str_replace( '{coupon_value}', $coupon_value, $content );
		$content         = str_replace( '{customer_name}', $customer_name, $content );
		$content         = str_replace( '{coupon_code}', '<span style="font-size: x-large;">' . strtoupper( $coupon_code ) . '</span>', $content );
		$content         = str_replace( '{date_expires}', empty( $date_expires ) ? esc_html__( 'never expires', 'woocommerce-coupon-box' ) : date_i18n( $date_format, ( $date_expires ) ), $content );
		$content         = str_replace( '{last_valid_date}', empty( $date_expires ) ? esc_html__( '', 'woocommerce-coupon-box' ) : date_i18n( $date_format, ( $date_expires - 86400 ) ), $content );
		$content         = str_replace( '{shop_now}', $button_shop_now, $content );
		$email_heading   = str_replace( '{coupon_value}', $coupon_value, $email_heading );

		// load the mailer class
		$mailer = WC()->mailer();

		// create a new email
		$email = new WC_Email();

		// wrap the content with the email template and then add styles
		$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $content ) ) );

		// print the preview email
		wp_send_json(
			array(
				'html' => $message,
			)
		);
	}

	public static function search_coupon() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		ob_start();
		$keyword = filter_input( INPUT_GET, 'keyword', FILTER_SANITIZE_STRING );
		if ( empty( $keyword ) ) {
			die();
		}
		$arg            = array(
			'post_status'    => 'publish',
			'post_type'      => 'shop_coupon',
			'posts_per_page' => 50,
			's'              => $keyword,
			'meta_query'     => array(
				'ralation' => 'AND',
				array(
					'key'     => 'wcb_unique_coupon',
					'compare' => 'NOT EXISTS'
				),
				array(
					'key'     => 'kt_unique_coupon',
					'compare' => 'NOT EXISTS'
				),
			)
		);
		$the_query      = new WP_Query( $arg );
		$found_products = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$coupon = new WC_Coupon( get_the_ID() );
				if ( $coupon->get_usage_limit() > 0 && $coupon->get_usage_count() >= $coupon->get_usage_limit() ) {
					continue;
				}
				if ( $coupon->get_amount() < 1 ) {
					continue;
				}
				if ( $coupon->get_date_expires() && current_time( 'timestamp', true ) > $coupon->get_date_expires()->getTimestamp() ) {
					continue;
				}
				$product          = array( 'id' => get_the_ID(), 'text' => get_the_title() );
				$found_products[] = $product;
			}
		}
		wp_reset_postdata();
		wp_send_json( $found_products );
		die;
	}

	public static function wcb_live_preview() {
		wp_enqueue_script( 'wcb-customizer', VI_WOOCOMMERCE_COUPON_BOX_JS . 'wcb-customizer.js', array(
			'jquery',
			'customize-preview'
		), '', true );
	}

	public static function wcb_sanitize_number_absint( $number, $setting ) {
		$number = absint( $number );

		return ( $number ? $number : $setting->default );
	}

	public static function wcb_btn_close_sanitize_select( $input, $setting ) {
		$input   = sanitize_key( $input );
		$choices = $setting->manager->get_control( $setting->id )->choices;

		return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
	}

	public function search_active_campaign_list() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		ob_start();
		$keyword = filter_input( INPUT_GET, 'keyword', FILTER_SANITIZE_STRING );
		if ( empty( $keyword ) ) {
			die();
		}
		$active_campaign = new VI_WOOCOMMERCE_COUPON_BOXP_Admin_Active_Campaign();
		$list            = $active_campaign->list_list( $keyword );
		$found_products  = array();
		if ( $list['result_code'] == 1 ) {
			foreach ( $list as $key => $val ) {
				if ( is_array( $val ) ) {
					if ( isset( $val['id'] ) ) {
						$product          = array( 'id' => $val['id'], 'text' => $val['name'] );
						$found_products[] = $product;
					}
				}
			}
		}
		wp_send_json( $found_products );
		die;
	}

	public function search_cate() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		ob_start();

		$keyword = filter_input( INPUT_GET, 'keyword', FILTER_SANITIZE_STRING );

		if ( empty( $keyword ) ) {
			die();
		}
		$categories = get_terms(
			array(
				'taxonomy' => 'product_cat',
				'orderby'  => 'name',
				'order'    => 'ASC',
				'search'   => $keyword,
				'number'   => 100
			)
		);
		$items      = array();
		if ( count( $categories ) ) {
			foreach ( $categories as $category ) {
				$item    = array(
					'id'   => $category->term_id,
					'text' => $category->name
				);
				$items[] = $item;
			}
		}
		wp_send_json( $items );
		die;
	}

	public function search_product() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		ob_start();

		$keyword = filter_input( INPUT_GET, 'keyword', FILTER_SANITIZE_STRING );

		if ( empty( $keyword ) ) {
			die();
		}
		$arg            = array(
			'post_status'    => 'publish',
			'post_type'      => 'product',
			'posts_per_page' => 50,
			's'              => $keyword

		);
		$the_query      = new WP_Query( $arg );
		$found_products = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$prd = wc_get_product( get_the_ID() );

				if ( $prd->has_child() && $prd->is_type( 'variable' ) ) {
					$product_children = $prd->get_children();
					if ( count( $product_children ) ) {
						foreach ( $product_children as $product_child ) {
							if ( woocommerce_version_check() ) {
								$product = array(
									'id'   => $product_child,
									'text' => get_the_title( $product_child )
								);

							} else {
								$child_wc  = wc_get_product( $product_child );
								$get_atts  = $child_wc->get_variation_attributes();
								$attr_name = array_values( $get_atts )[0];
								$product   = array(
									'id'   => $product_child,
									'text' => get_the_title() . ' - ' . $attr_name
								);

							}
							$found_products[] = $product;
						}

					}
				} else {
					$product_id    = get_the_ID();
					$product_title = get_the_title();
					$the_product   = new WC_Product( $product_id );
					if ( ! $the_product->is_in_stock() ) {
						$product_title .= ' (out-of-stock)';
					}
					$product          = array( 'id' => $product_id, 'text' => $product_title );
					$found_products[] = $product;
				}
			}
		}
		wp_send_json( $found_products );
		die;
	}

	public function create_options_page() {
		add_submenu_page(
			'edit.php?post_type=wcb',
			esc_html__( 'Settings', 'woocommerce-coupon-box' ),
			esc_html__( 'Settings', 'woocommerce-coupon-box' ),
			'manage_options',
			'woocommerce_coupon_box',
			array( $this, 'setting_page_woo_coupon_box' )
		);
	}

	public function setting_page_woo_coupon_box() {
		$this->settings = new VI_WOOCOMMERCE_COUPON_BOX_DATA();
		/*WPML.org*/

		?>
        <div class="wrap">
            <h2><?php echo esc_html__( 'Woo Coupon Box', 'woocommerce-coupon-box' ); ?></h2>

            <div class="vi-ui raised">
                <form class="vi-ui form" method="post" action="">
					<?php
					wp_nonce_field( 'woocouponbox_action_nonce', '_woocouponbox_nonce' );
					settings_fields( 'woocommerce-coupon-box' );
					do_settings_sections( 'woocommerce-coupon-box' );
					?>
                    <div class="vi-ui top attached tabular menu">
                        <a class="item active"
                           data-tab="wcb-general"><?php esc_html_e( 'General', 'woocommerce-coupon-box' ) ?></a>

                        <a class="item"
                           data-tab="wcb-coupon"><?php esc_html_e( 'Coupon', 'woocommerce-coupon-box' ) ?></a>
                        <a class="item"
                           data-tab="wcb-email"><?php esc_html_e( 'Email', 'woocommerce-coupon-box' ) ?></a>
                        <a class="item"
                           data-tab="wcb-email-api"><?php esc_html_e( 'Email API', 'woocommerce-coupon-box' ) ?></a>
                        <a class="item"
                           data-tab="wcb-grecaptcha"><?php esc_html_e( 'Google reCAPTCHA', 'woocommerce-coupon-box' ) ?></a>

                        <a class="item"
                           data-tab="wcb-assignpage"><?php esc_html_e( 'Assign', 'woocommerce-coupon-box' ) ?></a>
                        <a class="item"
                           data-tab="wcb-design"><?php esc_html_e( 'Design', 'woocommerce-coupon-box' ) ?></a>
                        <a class="item"
                           data-tab="update"><?php esc_html_e( 'Update', 'woocommerce-coupon-box' ) ?></a>

                    </div>
                    <div class="vi-ui bottom attached tab segment wcb-container active" data-tab="wcb-general">
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_active"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_active"
                                               id="wcb_active" <?php checked( $this->settings->get_params( 'wcb_active' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_active"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                    </div>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_disable_login"><?php esc_html_e( 'Disable for logged-in users', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_disable_login"
                                               id="wcb_disable_login" <?php checked( $this->settings->get_params( 'wcb_disable_login' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_disable_login"><span
                                                    class="description"><?php esc_html_e( 'Enable to hide coupon box for all logged-in users', 'woocommerce-coupon-box' ) ?></span></label>
                                    </div>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_multi_language"><?php esc_html_e( 'Enable multi language', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_multi_language"
                                               id="wcb_multi_language" <?php checked( $this->settings->get_params( 'wcb_multi_language' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_multi_language"><span
                                                    class="description"><?php esc_html_e( 'You can use multi language if you are using WPML or Polylang. Make sure you had translated WooCommerce Coupon Box in all your languages before using this feature.', 'woocommerce-coupon-box' ) ?></span></label>
                                    </div>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="ajax_endpoint"><?php esc_html_e( 'Ajax endpoint', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <p>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="radio" name="ajax_endpoint"
                                               id="ajax_endpoint_ajax" <?php checked( $this->settings->get_params( 'ajax_endpoint' ), 'ajax' ); ?>
                                               value="ajax">
                                        <label for="ajax_endpoint_ajax">
                                            <span class="description"><?php esc_html_e( 'Ajax', 'woocommerce-coupon-box' ) ?></span></label>
                                    </div>
                                    </p>
                                    <p>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="radio" name="ajax_endpoint"
                                               id="ajax_endpoint_rest_api" <?php checked( $this->settings->get_params( 'ajax_endpoint' ), 'rest_api' ); ?>
                                               value="rest_api">
                                        <label for="ajax_endpoint_rest_api">
                                            <span class="description"><?php esc_html_e( 'REST API', 'woocommerce-coupon-box' ) ?></span></label>
                                    </div>
                                    </p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_select_popup"><?php esc_html_e( 'Popup trigger', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <select class="vi-ui fluid dropdown wcb-select-popup" name="wcb_select_popup"
                                            id="wcb_select_popup">
                                        <option value="time" <?php selected( $this->settings->get_params( 'wcb_select_popup' ), 'time' ) ?>><?php esc_html_e( 'After initial time', 'woocommerce-coupon-box' ) ?></option>
                                        <option value="scroll" <?php selected( $this->settings->get_params( 'wcb_select_popup' ), 'scroll' ) ?>><?php esc_html_e( 'When users scroll', 'woocommerce-coupon-box' ) ?></option>
                                        <option value="exit" <?php selected( $this->settings->get_params( 'wcb_select_popup' ), 'exit' ) ?>><?php esc_html_e( 'When users are about to exit', 'woocommerce-coupon-box' ) ?></option>
                                        <option value="button" <?php selected( $this->settings->get_params( 'wcb_select_popup' ), 'button' ) ?>><?php esc_html_e( 'Button is rendered via shortcode', 'woocommerce-coupon-box' ) ?></option>
                                        <option value="random" <?php selected( $this->settings->get_params( 'wcb_select_popup' ), 'random' ) ?>><?php esc_html_e( 'Random one of these above', 'woocommerce-coupon-box' ) ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Shortcode for button option: [wcb_open_popup text="Subscribe"]', 'woocommerce-coupon-box' ); ?></i></p>
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-popup-time">
                                <th scope="row">
                                    <label for="wcb_popup_time"><?php esc_html_e( 'Initial time', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcb_popup_time" id="wcb_popup_time"
                                           value="<?php echo htmlentities( $this->settings->get_params( 'wcb_popup_time' ) ); ?>">

                                    <p class="description"><?php esc_html_e( 'Enter min,max to set initial time random between min and max(seconds).', 'woocommerce-coupon-box' ); ?></i></p>
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-popup-scroll">
                                <th scope="row">
                                    <label for="wcb_popup_scroll"><?php esc_html_e( 'Scroll amount', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="number" name="wcb_popup_scroll" id="wcb_popup_scroll" min="0" max="100"
                                           value="<?php echo $this->settings->get_params( 'wcb_popup_scroll' ); ?>">

                                    <p class="description"><?php esc_html_e( 'Percentage of page height when scroll to show popup', 'woocommerce-coupon-box' ); ?></i></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_on_close"><?php esc_html_e( 'When visitors close coupon box without subscribing', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <select class="vi-ui fluid dropdown" name="wcb_on_close"
                                            id="wcb_on_close">
                                        <option value="hide" <?php selected( $this->settings->get_params( 'wcb_on_close' ), 'hide' ) ?>><?php esc_html_e( 'Hide coupon box', 'woocommerce-coupon-box' ) ?></option>
                                        <option value="top" <?php selected( $this->settings->get_params( 'wcb_on_close' ), 'top' ) ?>><?php esc_html_e( 'Minimize to top bar', 'woocommerce-coupon-box' ) ?></option>
                                        <option value="bottom" <?php selected( $this->settings->get_params( 'wcb_on_close' ), 'bottom' ) ?>><?php esc_html_e( 'Minimize to bottom bar', 'woocommerce-coupon-box' ) ?></option>
                                    </select>
                                    <p class="description">
										<?php esc_html_e( 'If a visitor subscribes when the coupon box is minimized, only field email is visible and required, all other fields are invisible even if you set them required fields.', 'woocommerce-coupon-box' ); ?>
                                    </p>
                                    <p class="description">
										<?php esc_html_e( 'This is only used for desktop.', 'woocommerce-coupon-box' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_never_reminder_enable"><?php esc_html_e( 'Never reminder if click \'No, thanks\' button', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_never_reminder_enable"
                                               id="wcb_never_reminder_enable" <?php checked( $this->settings->get_params( 'wcb_never_reminder_enable' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_never_reminder_enable"><span
                                                    class="description"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></span></label>
                                    </div>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_expire"><?php esc_html_e( 'Subscription reminder if not subscribe', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="inline field">
                                        <input type="number" name="wcb_expire" id="wcb_expire" min="1"
                                               value="<?php echo $this->settings->get_params( 'wcb_expire' ); ?>">
                                        <select name="wcb_expire_unit" class="vi-ui dropdown wcb-expire-unit">
                                            <option value="second" <?php selected( $this->settings->get_params( 'wcb_expire_unit' ), 'second' ) ?>><?php esc_html_e( 'Second', 'woocommerce-coupon-box' ) ?></option>
                                            <option value="minute" <?php selected( $this->settings->get_params( 'wcb_expire_unit' ), 'minute' ) ?>><?php esc_html_e( 'Minute', 'woocommerce-coupon-box' ) ?></option>
                                            <option value="hour" <?php selected( $this->settings->get_params( 'wcb_expire_unit' ), 'hour' ) ?>><?php esc_html_e( 'Hour', 'woocommerce-coupon-box' ) ?></option>
                                            <option value="day" <?php selected( $this->settings->get_params( 'wcb_expire_unit' ), 'day' ) ?>><?php esc_html_e( 'Day', 'woocommerce-coupon-box' ) ?></option>
                                        </select>
                                    </div>
                                    <label for="wcb_expire">
                                        <span class="description"><?php esc_html_e( 'Time to show subscription again if visitor does not subscribe', 'woocommerce-coupon-box' ); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_expire_subscribed"><?php esc_html_e( 'Subscription reminder if subscribe', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="inline field">
                                        <input type="number" name="wcb_expire_subscribed" id="wcb_expire_subscribed"
                                               min="1"
                                               value="<?php echo $this->settings->get_params( 'wcb_expire_subscribed' ); ?>"> <?php esc_html_e( 'Days', 'woocommerce-coupon-box' ); ?>
                                    </div>
                                    <label for="wcb_expire_subscribed"><span
                                                class="description"><?php esc_html_e( 'Show subscription form again after ', 'woocommerce-coupon-box' ); ?>
                                            <span class="wcb_expire_subscribed_value"><?php echo $this->settings->get_params( 'wcb_expire_subscribed' ); ?></span><?php esc_html_e( ' days if the visitor subscribes', 'woocommerce-coupon-box' ); ?></i></span></label>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_email_campaign"><?php esc_html_e( 'Email campaign', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <select class="vi-ui fluid dropdown select-email-campaign" name="wcb_email_campaign"
                                            id="wcb_email_campaign">
										<?php
										$terms_wcb = get_terms( [
											'taxonomy'   => 'wcb_email_campaign',
											'hide_empty' => false,
										] );

										if ( count( $terms_wcb ) ) {
											foreach ( $terms_wcb as $item ) {
												echo "<option value='" . $item->term_id . "' " . selected( $this->settings->get_params( 'wcb_email_campaign' ), $item->term_id ) . ">" . $item->name . "</option>";
											}
										}

										?>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_register_account"><?php esc_html_e( 'Register account', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_register_account" value="1"
                                               id="wcb_register_account" <?php checked( $this->settings->get_params( 'wcb_register_account' ), 1 ); ?> >
                                        <label for="wcb_register_account"><?php esc_html_e( 'Register account when customer subscribe', 'woocommerce-coupon-box' ) ?></label>
                                    </div>
                                    <!--                                    <select class="vi-ui fluid dropdown" name="wcb_register_account" id="wcb_register_account">-->
                                    <!--										--><?php
									//										$selected = $this->settings->get_params( 'wcb_register_account' );
									//										$options = array(
									//											'none'   => esc_html__( 'None', 'woocommerce-coupon-box' ),
									//											'auto'   => esc_html__( 'Auto register account', 'woocommerce-coupon-box' ),
									//											'accept' => esc_html__( 'Register when customer accept', 'woocommerce-coupon-box' ),
									//										);
									//										foreach ( $options as $option => $display ) {
									//											printf( '<option value="%s" %s>%s</option>', esc_attr( $option ), selected( $selected, $option, false ), esc_html( $display ) );
									//										}
									//										?>
                                    <!--                                    </select>-->
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_restrict_domain"><?php esc_html_e( 'Restrict the email domains', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcb_restrict_domain" value="<?php echo esc_html( $this->settings->get_params( 'wcb_restrict_domain' ) ); ?>"
                                           id="wcb_restrict_domain">
                                    <p class="description"><?php esc_html_e( 'Separate email domains by "|"', 'woocommerce-coupon-box' ) ?></p>
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="vi-ui bottom attached tab segment wcb-container" data-tab="wcb-coupon">
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_coupon_select"><?php esc_html_e( 'Select coupon', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <select class="vi-ui fluid dropdown wcb-coupon-select" name="wcb_coupon_select"
                                            id="wcb_coupon_select">
                                        <option value="unique" <?php selected( $this->settings->get_params( 'wcb_coupon_select' ), 'unique' ) ?>><?php esc_html_e( 'Unique coupon', 'woocommerce-coupon-box' ) ?></option>
                                        <option value="existing" <?php selected( $this->settings->get_params( 'wcb_coupon_select' ), 'existing' ) ?>><?php esc_html_e( 'Existing coupon', 'woocommerce-coupon-box' ) ?></option>
                                        <option value="custom" <?php selected( $this->settings->get_params( 'wcb_coupon_select' ), 'custom' ) ?>><?php esc_html_e( 'Custom', 'woocommerce-coupon-box' ) ?></option>
                                        <option value="non" <?php selected( $this->settings->get_params( 'wcb_coupon_select' ), 'non' ) ?>><?php esc_html_e( 'Do not use coupon', 'woocommerce-coupon-box' ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-email-restriction">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_email_restrictions"><?php esc_html_e( 'Email restriction', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_coupon_unique_email_restrictions"
                                               id="wcb_coupon_unique_email_restrictions" <?php checked( $this->settings->get_params( 'wcb_coupon_unique_email_restrictions' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_coupon_unique_email_restrictions"><span
                                                    class="description"><?php esc_html_e( 'Enable to make coupon usable for received email only', 'woocommerce-coupon-box' ) ?></span></label>
                                    </div>
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-existing">
                                <th scope="row">
                                    <label for="wcb_coupon"><?php esc_html_e( 'Existing coupon', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <select class="search-coupon" name="wcb_coupon" id="wcb_coupon">
										<?php
										if ( $this->settings->get_params( 'wcb_coupon' ) ) {
											$coupon = new WC_Coupon( $this->settings->get_params( 'wcb_coupon' ) );
											?>
                                            <option value="<?php echo $this->settings->get_params( 'wcb_coupon' ) ?>"
                                                    selected><?php echo $coupon->get_code(); ?></option>
											<?php
										}
										?>
                                    </select>
                                </td>
                            </tr>

                            <tr valign="top" class="wcb-coupon-custom">
                                <th scope="row">
                                    <label for="wcb_coupon_custom"><?php esc_html_e( 'Custom', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcb_coupon_custom" id="wcb_coupon_custom"
                                           value="<?php echo htmlentities( $this->settings->get_params( 'wcb_coupon_custom' ) ); ?>">
                                </td>
                            </tr>

                            <tr valign="top" class="wcb-coupon-unique">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_description"><?php esc_html_e( 'Description', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <textarea name="wcb_coupon_unique_description"
                                              id="wcb_coupon_unique_description"><?php echo $this->settings->get_params( 'wcb_coupon_unique_description' ) ?></textarea>
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-unique">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_discount_type"><?php esc_html_e( 'Discount type', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <select class="vi-ui fluid dropdown" name="wcb_coupon_unique_discount_type">
                                        <option value="percent" <?php selected( $this->settings->get_params( 'wcb_coupon_unique_discount_type' ), 'percent' ) ?>><?php esc_html_e( 'Percentage discount', 'woocommerce-coupon-box' ) ?></option>
                                        <option value="fixed_cart" <?php selected( $this->settings->get_params( 'wcb_coupon_unique_discount_type' ), 'fixed_cart' ) ?>><?php esc_html_e( 'Fixed cart discount', 'woocommerce-coupon-box' ) ?></option>
                                        <option value="fixed_product" <?php selected( $this->settings->get_params( 'wcb_coupon_unique_discount_type' ), 'fixed_product' ) ?>><?php esc_html_e( 'Fixed product discount', 'woocommerce-coupon-box' ) ?></option>
                                    </select>
                                </td>
                            </tr>

                            <tr valign="top" class="wcb-coupon-unique">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_prefix"><?php esc_html_e( 'Coupon code prefix', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcb_coupon_unique_prefix" id="wcb_coupon_unique_prefix"
                                           value="<?php echo htmlentities( $this->settings->get_params( 'wcb_coupon_unique_prefix' ) ); ?>">
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-unique">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_amount"><?php esc_html_e( 'Coupon amount', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="number" name="wcb_coupon_unique_amount" id="wcb_coupon_unique_amount"
                                           min="0"
                                           value="<?php echo $this->settings->get_params( 'wcb_coupon_unique_amount' ) ?>"
                                           step="0.01">
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-unique">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_free_shipping"><?php esc_html_e( 'Allow free shipping', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_coupon_unique_free_shipping"
                                               id="wcb_coupon_unique_free_shipping" <?php checked( $this->settings->get_params( 'wcb_coupon_unique_free_shipping' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_coupon_unique_free_shipping"><span
                                                    class="description"><?php printf( esc_html__( 'Enable if the coupon grants free shipping. A free shipping method must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "%s" setting).', 'woocommerce-coupon-box' ), '<a href="https://docs.woocommerce.com/document/free-shipping/"
                                               target="_blank">' . esc_html__( 'free shipping method', 'woocommerce-coupon-box' ) . '</a>' ) ?></span></label>
                                    </div>
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-unique">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_date_expires"><?php esc_html_e( 'Expires after(days)', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="number" name="wcb_coupon_unique_date_expires"
                                           id="wcb_coupon_unique_date_expires"
                                           min="0"
                                           value="<?php echo $this->settings->get_params( 'wcb_coupon_unique_date_expires' ) ?>">
                                </td>
                            </tr>

                            <tr valign="top" class="wcb-coupon-unique">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_minimum_amount"><?php esc_html_e( 'Minimum spend', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="number" name="wcb_coupon_unique_minimum_amount"
                                           id="wcb_coupon_unique_minimum_amount"
                                           min="0"
                                           value="<?php echo $this->settings->get_params( 'wcb_coupon_unique_minimum_amount' ) ?>">
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-unique">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_maximum_amount"><?php esc_html_e( 'Maximum spend', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="number" name="wcb_coupon_unique_maximum_amount"
                                           id="wcb_coupon_unique_maximum_amount"
                                           min="0"
                                           value="<?php echo $this->settings->get_params( 'wcb_coupon_unique_maximum_amount' ) ?>">
                                </td>
                            </tr>


                            <tr valign="top" class="wcb-coupon-unique">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_individual_use"><?php esc_html_e( 'Individual use only', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_coupon_unique_individual_use"
                                               id="wcb_coupon_unique_individual_use" <?php checked( $this->settings->get_params( 'wcb_coupon_unique_individual_use' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_coupon_unique_individual_use"><span
                                                    class="description"><?php esc_html_e( 'Enable if the coupon cannot be used in conjunction with other coupons.', 'woocommerce-coupon-box' ) ?></span></label>
                                    </div>
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-unique">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_exclude_sale_items"><?php esc_html_e( 'Exclude sale items', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_coupon_unique_exclude_sale_items"
                                               id="wcb_coupon_unique_exclude_sale_items" <?php checked( $this->settings->get_params( 'wcb_coupon_unique_exclude_sale_items' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_coupon_unique_exclude_sale_items"><span
                                                    class="description"><?php esc_html_e( 'Enable if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are items in the cart that are not on sale.', 'woocommerce-coupon-box' ) ?></span></label>
                                    </div>
                                </td>
                            </tr>


                            <tr valign="top" class="wcb-coupon-unique">
                                <th>
                                    <label for="wcb_coupon_unique_product_ids"><?php esc_html_e( 'Products', 'woocommerce-coupon-box' ); ?></label>
                                </th>
                                <td>
                                    <select name="wcb_coupon_unique_product_ids[]" id="wcb_coupon_unique_product_ids"
                                            class="search-product" multiple="multiple">
										<?php
										if ( is_array( $this->settings->get_params( 'wcb_coupon_unique_product_ids' ) ) && count( $this->settings->get_params( 'wcb_coupon_unique_product_ids' ) ) ) {
											foreach ( $this->settings->get_params( 'wcb_coupon_unique_product_ids' ) as $product_id ) {
												$product = wc_get_product( $product_id );
												?>
                                                <option value="<?php echo $product_id ?>" selected>
													<?php
													if ( $product ) {
														echo $product->get_title();
													} else {
														_e( 'Note*: Not found product(ID =' . $product_id . ')', 'woocommerce-coupon-box' );
													}
													?>
                                                </option>
												<?php
											}
										}
										?>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-unique">
                                <th>
                                    <label for="wcb_coupon_unique_excluded_product_ids"><?php esc_html_e( 'Exclude products', 'woocommerce-coupon-box' ); ?></label>
                                </th>
                                <td>
                                    <select name="wcb_coupon_unique_excluded_product_ids[]"
                                            id="wcb_coupon_unique_excluded_product_ids" class="search-product"
                                            multiple="multiple">
										<?php
										if ( is_array( $this->settings->get_params( 'wcb_coupon_unique_excluded_product_ids' ) ) && count( $this->settings->get_params( 'wcb_coupon_unique_excluded_product_ids' ) ) ) {
											foreach ( $this->settings->get_params( 'wcb_coupon_unique_excluded_product_ids' ) as $product_id ) {
												$product = wc_get_product( $product_id );
												?>
                                                <option value="<?php echo $product_id ?>" selected>
													<?php
													if ( $product ) {
														echo $product->get_title();
													} else {
														_e( 'Note*: Not found product(ID =' . $product_id . ')', 'woocommerce-coupon-box' );
													}
													?>
                                                </option>
												<?php
											}
										}
										?>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-unique">
                                <th>
                                    <label for="wcb_coupon_unique_product_categories"><?php esc_html_e( 'Categories', 'woocommerce-coupon-box' ); ?></label>
                                </th>
                                <td>
                                    <select name="wcb_coupon_unique_product_categories[]"
                                            id="wcb_coupon_unique_product_categories" class="search-category"
                                            multiple="multiple">
										<?php

										if ( is_array( $this->settings->get_params( 'wcb_coupon_unique_product_categories' ) ) && count( $this->settings->get_params( 'wcb_coupon_unique_product_categories' ) ) ) {
											foreach ( $this->settings->get_params( 'wcb_coupon_unique_product_categories' ) as $category_id ) {
												$category = get_term( $category_id );
												?>
                                                <option value="<?php echo $category_id ?>" selected>
													<?php
													if ( $category ) {
														echo $category->name;
													} else {
														_e( 'Note*: Not found category(ID =' . $category_id . ')', 'woocommerce-coupon-box' );
													}
													?>
                                                </option>
												<?php
											}
										}
										?>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-unique">
                                <th>
                                    <label for="wcb_coupon_unique_excluded_product_categories"><?php esc_html_e( 'Exclude categories', 'woocommerce-coupon-box' ); ?></label>
                                </th>
                                <td>
                                    <select name="wcb_coupon_unique_excluded_product_categories[]"
                                            id="wcb_coupon_unique_excluded_product_categories" class="search-category"
                                            multiple="multiple">
										<?php

										if ( is_array( $this->settings->get_params( 'wcb_coupon_unique_excluded_product_categories' ) ) && count( $this->settings->get_params( 'wcb_coupon_unique_excluded_product_categories' ) ) ) {
											foreach ( $this->settings->get_params( 'wcb_coupon_unique_excluded_product_categories' ) as $category_id ) {
												$category = get_term( $category_id );
												?>
                                                <option value="<?php echo $category_id ?>" selected>
													<?php
													if ( $category ) {
														echo $category->name;
													} else {
														_e( 'Note*: Not found category(ID =' . $category_id . ')', 'woocommerce-coupon-box' );
													}
													?>
                                                </option>
												<?php
											}
										}
										?>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-unique">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_usage_limit"><?php esc_html_e( 'Usage limit per coupon', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="number" name="wcb_coupon_unique_usage_limit"
                                           id="wcb_coupon_unique_usage_limit"
                                           min="0"
                                           value="<?php echo $this->settings->get_params( 'wcb_coupon_unique_usage_limit' ) ?>">
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-unique">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_limit_usage_to_x_items"><?php esc_html_e( 'Limit usage to X items', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="number" name="wcb_coupon_unique_limit_usage_to_x_items"
                                           id="wcb_coupon_unique_limit_usage_to_x_items"
                                           min="0"
                                           value="<?php echo $this->settings->get_params( 'wcb_coupon_unique_limit_usage_to_x_items' ) ?>">
                                </td>
                            </tr>
                            <tr valign="top" class="wcb-coupon-unique">
                                <th scope="row">
                                    <label for="wcb_coupon_unique_usage_limit_per_user"><?php esc_html_e( 'Usage limit per user', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="number" name="wcb_coupon_unique_usage_limit_per_user"
                                           id="wcb_coupon_unique_usage_limit_per_user"
                                           min="0"
                                           value="<?php echo $this->settings->get_params( 'wcb_coupon_unique_usage_limit_per_user' ) ?>">
                                </td>
                            </tr>

                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment wcb-container" data-tab="wcb-email">
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_email_enable"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_email_enable"
                                               id="wcb_email_enable" <?php checked( $this->settings->get_params( 'wcb_email_enable' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_email_enable"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                    </div>
									<?php
									?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="wcb_email_template"><?php esc_html_e( 'Email template', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
									<?php
									$email_template  = $this->settings->get_params( 'email_template' );
									$email_templates = $this->settings::get_email_templates();
									?>
                                    <select name="email_template" class="vi-ui fluid dropdown wcb_email_template"
                                            id="wcb_email_template">
                                        <option value=""><?php esc_html_e( 'None', 'woocommerce-coupon-box' ) ?></option>
										<?php
										if ( count( $email_templates ) ) {
											foreach ( $email_templates as $k => $v ) {
												echo sprintf( '<option value="%s" %s >%s</option>',
													esc_attr( $v->ID ),
													selected( $v->ID, $email_template ),
													esc_html( $v->post_title . '(#' . $v->ID . ')' )
												);
											}
										}
										?>
                                    </select>
                                    <p class="description"><?php _e( 'You can use <a href="https://1.envato.market/BZZv1" target="_blank">WooCommerce Email Template Customizer</a> or <a href="http://bit.ly/woo-email-template-customizer" target="_blank">Email Template Customizer for WooCommerce</a> to create and customize your own email template. If no email template is selected, below email will be used.', 'woocommerce-coupon-box' ) ?></p>
									<?php
									if ( $this->settings::email_template_customizer_active() ) {
										echo sprintf( '<p class="description"><a  href="edit.php?post_type=viwec_template" target="_blank">%s</a> %s <a href="post-new.php?post_type=viwec_template&sample=wcb_email&style=basic" target="_blank">%s</a></p>',
											__( 'View all Email templates', 'woocommerce-coupon-box' ),
											__( 'or', 'woocommerce-coupon-box' ),
											__( 'Create a new email template', 'woocommerce-coupon-box' )
										);
									}
									if ( count( $this->languages ) && count( $email_templates ) ) {
										foreach ( $this->languages as $key => $value ) {
											$email_template      = 'email_template_' . $value;
											$email_template_lang = $this->settings->get_params( 'email_template', $value );
											?>
                                            <p>
                                                <label for="<?php echo $email_template; ?>"><?php
													if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo $value;
													if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
														echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
													}
													?>:</label>
                                            </p>
                                            <select name="<?php echo esc_attr( $email_template ) ?>"
                                                    class="vi-ui fluid dropdown <?php echo esc_attr( $email_template ) ?>"
                                                    id="<?php echo esc_attr( $email_template ) ?>">
                                                <option value=""><?php esc_html_e( 'None', 'woocommerce-photo-reviews' ) ?></option>
												<?php
												if ( count( $email_templates ) ) {
													foreach ( $email_templates as $k => $v ) {
														echo sprintf( '<option value="%s" %s >%s</option>',
															esc_attr( $v->ID ),
															selected( $v->ID, $email_template_lang ),
															esc_html( $v->post_title . '(#' . $v->ID . ')' )
														);
													}
												}
												?>
                                            </select>
											<?php
										}
									}
									?>
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    <label for="wcb_email_template_for_register"><?php esc_html_e( 'Email template for register account', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
									<?php
									$r_email_template = $this->settings->get_params( 'email_template_for_register' );
									?>
                                    <select name="email_template_for_register"
                                            class="vi-ui fluid dropdown wcb_email_template"
                                            id="wcb_email_template_for_register">
                                        <option value=""><?php esc_html_e( 'None', 'woocommerce-coupon-box' ) ?></option>
										<?php
										if ( count( $email_templates ) ) {
											foreach ( $email_templates as $k => $v ) {
												echo sprintf( '<option value="%s" %s >%s</option>',
													esc_attr( $v->ID ),
													selected( $v->ID, $r_email_template ),
													esc_html( $v->post_title . '(#' . $v->ID . ')' )
												);
											}
										}
										?>
                                    </select>
                                    <p class="description"><?php _e( 'You can use <a href="https://1.envato.market/BZZv1" target="_blank">WooCommerce Email Template Customizer</a> or <a href="http://bit.ly/woo-email-template-customizer" target="_blank">Email Template Customizer for WooCommerce</a> to create and customize your own email template. If no email template is selected, below email will be used.', 'woocommerce-coupon-box' ) ?></p>
									<?php
									if ( $this->settings::email_template_customizer_active() ) {
										echo sprintf( '<p class="description"><a  href="edit.php?post_type=viwec_template" target="_blank">%s</a> %s <a href="post-new.php?post_type=viwec_template&sample=wcb_email&style=basic" target="_blank">%s</a></p>',
											__( 'View all Email templates', 'woocommerce-coupon-box' ),
											__( 'or', 'woocommerce-coupon-box' ),
											__( 'Create a new email template', 'woocommerce-coupon-box' )
										);
									}
									if ( count( $this->languages ) && count( $email_templates ) ) {
										foreach ( $this->languages as $key => $value ) {
											$email_template      = 'email_template_for_register' . $value;
											$email_template_lang = $this->settings->get_params( 'email_template_for_register', $value );
											?>
                                            <p>
                                                <label for="<?php echo $email_template; ?>"><?php
													if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo $value;
													if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
														echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
													}
													?>:</label>
                                            </p>
                                            <select name="<?php echo esc_attr( $email_template ) ?>"
                                                    class="vi-ui fluid dropdown <?php echo esc_attr( $email_template ) ?>"
                                                    id="<?php echo esc_attr( $email_template ) ?>">
                                                <option value=""><?php esc_html_e( 'None', 'woocommerce-photo-reviews' ) ?></option>
												<?php
												if ( count( $email_templates ) ) {
													foreach ( $email_templates as $k => $v ) {
														echo sprintf( '<option value="%s" %s >%s</option>',
															esc_attr( $v->ID ),
															selected( $v->ID, $email_template_lang ),
															esc_html( $v->post_title . '(#' . $v->ID . ')' )
														);
													}
												}
												?>
                                            </select>
											<?php
										}
									}
									?>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_email_subject"><?php esc_html_e( 'Email subject', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcb_email_subject" id="wcb_email_subject"
                                           value="<?php echo htmlentities( $this->settings->get_params( 'wcb_email_subject' ) ) ?>">
                                    <p>{coupon_value}
                                        - <?php esc_html_e( 'The value of coupon, can be percentage or currency amount depending on coupon type' ) ?></p>
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$wcb_email_subject = 'wcb_email_subject_' . $value;
											?>
                                            <p>
                                                <label for="<?php echo $wcb_email_subject; ?>"><?php
													if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo $value;
													if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
														echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
													}
													?>:</label>
                                            </p>
                                            <input type="text" name="<?php echo $wcb_email_subject ?>"
                                                   id="<?php echo $wcb_email_subject ?>"
                                                   value="<?php echo htmlentities( $this->settings->get_params( 'wcb_email_subject', $value ) ) ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_email_heading"><?php esc_html_e( 'Email heading', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcb_email_heading" id="wcb_email_heading"
                                           value="<?php echo htmlentities( $this->settings->get_params( 'wcb_email_heading' ) ) ?>">
                                    <p>{coupon_value}
                                        - <?php esc_html_e( 'The value of coupon, can be percentage or currency amount depending on coupon type' ) ?></p>
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$wcb_email_heading = 'wcb_email_heading_' . $value;
											?>
                                            <p>
                                                <label for="<?php echo $wcb_email_heading; ?>"><?php
													if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo $value;
													if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
														echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
													}
													?>:</label>
                                            </p>
                                            <input type="text" name="<?php echo $wcb_email_heading ?>"
                                                   id="<?php echo $wcb_email_heading ?>"
                                                   value="<?php echo htmlentities( $this->settings->get_params( 'wcb_email_heading', $value ) ) ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_email_content"><?php esc_html_e( 'Email content', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
									<?php
									wp_editor( stripslashes( $this->settings->get_params( 'wcb_email_content' ) ), 'wcb_email_content', array( 'editor_height' => 300 ) );
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$wcb_email_content = 'wcb_email_content_' . $value;
											?>
                                            <p>
                                                <label for="<?php echo $wcb_email_content; ?>"><?php
													if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo $value;
													if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
														echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
													}
													?>:</label>
                                            </p>
											<?php
											wp_editor( stripslashes( $this->settings->get_params( 'wcb_email_content', $value ) ), $wcb_email_content, array( 'editor_height' => 300 ) );
										}
									}
									?>
                                </td>
                            </tr>
                            <tr>
                                <th></th>
                                <td>
                                    <p>{customer_name}
                                        - <?php esc_html_e( 'Name of customer if present' ) ?></p>
                                    <p>{coupon_value}
                                        - <?php esc_html_e( 'The value of coupon, can be percentage or currency amount depending on coupon type' ) ?></p>
                                    <p>{coupon_code}
                                        - <?php esc_html_e( 'The code of coupon that will be sent to your subscribers' ) ?></p>
                                    <p>{date_expires}
                                        - <?php esc_html_e( 'From the date that given coupon will no longer be available' ) ?></p>
                                    <p>{last_valid_date}
                                        - <?php esc_html_e( 'That last day that coupon is valid' ) ?></p>
                                    <p>{site_title}
                                        - <?php esc_html_e( 'The title of your website' ) ?></p>
                                    <p>{shop_now}
                                        - <?php esc_html_e( 'Button ' );
										echo '<a class="wcb-button-shop-now" href="' . ( $this->settings->get_params( 'wcb_button_shop_now_url' ) ? $this->settings->get_params( 'wcb_button_shop_now_url' ) : get_bloginfo( 'url' ) ) . '" target="_blank" style="text-decoration:none;display:inline-block;padding:10px 30px;margin:10px 0;font-size:' . $this->settings->get_params( 'wcb_button_shop_now_size' ) . 'px;color:' . $this->settings->get_params( 'wcb_button_shop_now_color' ) . ';background:' . $this->settings->get_params( 'wcb_button_shop_now_bg_color' ) . ';">' . $this->settings->get_params( 'wcb_button_shop_now_title' ) . '</a>' ?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_register_email_content"><?php esc_html_e( 'New account content', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
									<?php
									wp_editor( stripslashes( $this->settings->get_params( 'wcb_register_email_content' ) ), 'wcb_register_email_content', array( 'editor_height' => 200 ) );
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$wcb_email_content = 'wcb_register_email_content_' . $value;
											?>
                                            <p>
                                                <label for="<?php echo $wcb_email_content; ?>"><?php
													if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo $value;
													if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
														echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
													}
													?>:</label>
                                            </p>
											<?php
											wp_editor( stripslashes( $this->settings->get_params( 'wcb_register_email_content', $value ) ), $wcb_email_content, array( 'editor_height' => 200 ) );
										}
									}
									?>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_button_shop_now_title"><?php esc_html_e( 'Button "Shop now" title', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcb_button_shop_now_title" id="wcb_button_shop_now_title"
                                           value="<?php echo htmlentities( $this->settings->get_params( 'wcb_button_shop_now_title' ) ) ?>">
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$wcb_button_shop_now_title = 'wcb_button_shop_now_title_' . $value;
											?>
                                            <p>
                                                <label for="<?php echo $wcb_button_shop_now_title; ?>"><?php
													if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo $value;
													if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
														echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
													}
													?>:</label>
                                            </p>
                                            <input type="text" name="<?php echo $wcb_button_shop_now_title ?>"
                                                   id="<?php echo $wcb_button_shop_now_title ?>"
                                                   value="<?php echo htmlentities( $this->settings->get_params( 'wcb_button_shop_now_title', $value ) ) ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_button_shop_now_url"><?php esc_html_e( 'Button "Shop now" URL', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcb_button_shop_now_url" id="wcb_button_shop_now_url"
                                           value="<?php echo htmlentities( $this->settings->get_params( 'wcb_button_shop_now_url' ) ) ?>">
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$wcb_button_shop_now_url = 'wcb_button_shop_now_url_' . $value;
											?>
                                            <p>
                                                <label for="<?php echo $wcb_button_shop_now_url; ?>"><?php
													if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
														?>
                                                        <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
														<?php
													}
													echo $value;
													if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
														echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
													}
													?>:</label>
                                            </p>
                                            <input type="text" name="<?php echo $wcb_button_shop_now_url ?>"
                                                   id="<?php echo $wcb_button_shop_now_url ?>"
                                                   value="<?php echo htmlentities( $this->settings->get_params( 'wcb_button_shop_now_url', $value ) ) ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_button_shop_now_color"><?php esc_html_e( 'Button "Shop now" color', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcb_button_shop_now_color" id="wcb_button_shop_now_color"
                                           class="color-picker"
                                           value="<?php echo $this->settings->get_params( 'wcb_button_shop_now_color' ) ?>"
                                           style="background-color: <?php echo $this->settings->get_params( 'wcb_button_shop_now_color' ) ?>;">
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_button_shop_now_bg_color"><?php esc_html_e( 'Button "Shop now" background color', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcb_button_shop_now_bg_color"
                                           id="wcb_button_shop_now_bg_color" class="color-picker"
                                           value="<?php echo $this->settings->get_params( 'wcb_button_shop_now_bg_color' ) ?>"
                                           style="background-color: <?php echo $this->settings->get_params( 'wcb_button_shop_now_bg_color' ) ?>;">
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_button_shop_now_size"><?php esc_html_e( 'Button "Shop now" font size(px)', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="number" name="wcb_button_shop_now_size" id="wcb_button_shop_now_size"
                                           min="1"
                                           value="<?php echo $this->settings->get_params( 'wcb_button_shop_now_size' ) ?>">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment wcb-container" data-tab="wcb-email-api">
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_enable_mailchimp"><?php esc_html_e( 'Mailchimp', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_enable_mailchimp"
                                               id="wcb_enable_mailchimp" <?php checked( $this->settings->get_params( 'wcb_enable_mailchimp' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_enable_mailchimp"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'Turn on to use MailChimp system', 'woocommerce-coupon-box' ) ?></p>
									<?php
									?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_mailchimp_double_optin"><?php esc_html_e( 'Mailchimp double optin', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_mailchimp_double_optin"
                                               id="wcb_mailchimp_double_optin" <?php checked( $this->settings->get_params( 'wcb_mailchimp_double_optin' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_mailchimp_double_optin"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'If enabled, a confirm subscription email will be sent to each subscriber for them to confirm that they subscribe to your list.', 'woocommerce-coupon-box' ) ?></p>
									<?php
									?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_api"></label><?php esc_html_e( 'Mailchimp API Key', 'woocommerce-coupon-box' ) ?>
                                </th>
                                <td>
                                    <input type="text" id="wcb_api" name="wcb_api"
                                           value="<?php echo $this->settings->get_params( 'wcb_api' ); ?>">

                                    <p class="description"><?php esc_html_e( ' The API key for connecting with your MailChimp account. Get your API key ', 'woocommerce-coupon-box' ) ?>
                                        <a target="_blank"
                                           href="https://admin.mailchimp.com/account/api"><?php esc_html_e( 'here', 'woocommerce-coupon-box' ) ?></a>.
                                    </p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_mlists"><?php esc_html_e( 'Mailchimp lists', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <select class="vi-ui fluid dropdown select-who" name="wcb_mlists" id="wcb_mlists">
                                        <option value="non"><?php esc_html_e( 'Select Mailchimp list', 'woocommerce-coupon-box' ) ?></option>
										<?php
										if ( class_exists( 'VI_WOOCOMMERCE_COUPON_BOX_Admin_Mailchimp' ) ) {
											$mailchimp  = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Mailchimp();
											$mail_lists = $mailchimp->get_lists();
											if ( ! $mail_lists ) {
												$mail_lists = array();
											}

											if ( count( $mail_lists ) ) {
												foreach ( $mail_lists as $key_m => $mail_list ) {
													echo "<option value='$key_m' " . selected( $this->settings->get_params( 'wcb_mlists' ), $key_m ) . ">$mail_list</option>";
												}
											}
										}
										?>
                                    </select>
									<?php
									if ( count( $this->languages ) ) {
										if ( class_exists( 'VI_WOOCOMMERCE_COUPON_BOX_Admin_Mailchimp' ) ) {
											$mailchimp = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Mailchimp();
											foreach ( $this->languages as $key => $value ) {
												?>
                                                <p>
                                                    <label for="<?php echo 'wcb_mlists_' . $value; ?>"><?php
														if ( isset( $this->languages_data[ $value ]['country_flag_url'] ) && $this->languages_data[ $value ]['country_flag_url'] ) {
															?>
                                                            <img src="<?php echo esc_url( $this->languages_data[ $value ]['country_flag_url'] ); ?>">
															<?php
														}
														echo $value;
														if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
															echo '(' . $this->languages_data[ $value ]['translated_name'] . ')';
														}
														?>:</label>
                                                </p>
                                                <select class="select-who vi-ui fluid dropdown"
                                                        name="<?php echo 'wcb_mlists_' . $value; ?>"
                                                        id="<?php echo 'wcb_mlists_' . $value; ?>">
													<?php
													$mail_lists = $mailchimp->get_lists();
													if ( ! $mail_lists ) {
														$mail_lists = array();
													}

													if ( count( $mail_lists ) ) {
														foreach ( $mail_lists as $key_m => $mail_list ) {
															echo "<option value='$key_m' " . selected( $this->settings->get_params( 'wcb_mlists', $value ), $key_m ) . ">$mail_list</option>";
														}
													}
													?>
                                                </select>
												<?php
											}
										}
									}
									?>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_enable_active_campaign"><?php esc_html_e( 'Active Campaign', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_enable_active_campaign"
                                               id="wcb_enable_active_campaign" <?php checked( $this->settings->get_params( 'wcb_enable_active_campaign' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_enable_active_campaign"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                    </div>

                                </td>
                            </tr>
                            <tr valign="top" class="vi_wbs_enable_active_campaign">
                                <th scope="row">
                                    <label for="wcb_active_campaign_api"></label><?php esc_html_e( 'Active Campaign API Key', 'woocommerce-coupon-box' ) ?>
                                </th>
                                <td>
                                    <input type="text" id="wcb_active_campaign_api" name="wcb_active_campaign_api"
                                           value="<?php echo $this->settings->get_params( 'wcb_active_campaign_api' ); ?>">

                                </td>
                            </tr>
                            <tr valign="top" class="vi_wbs_enable_active_campaign">
                                <th scope="row">
                                    <label for="wcb_active_campaign_url"></label><?php esc_html_e( 'Active Campaign API URL', 'woocommerce-coupon-box' ) ?>
                                </th>
                                <td>
                                    <input type="text" id="wcb_active_campaign_url" name="wcb_active_campaign_url"
                                           value="<?php echo $this->settings->get_params( 'wcb_active_campaign_url' ); ?>">

                                </td>
                            </tr>
                            <tr valign="top" class="vi_wbs_enable_active_campaign">
                                <th scope="row">
                                    <label for="wcb_active_campaign_list"><?php esc_html_e( 'Active Campaign list', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <select class="wcb-ac-search-list" name="wcb_active_campaign_list">
										<?php
										if ( $this->settings->get_params( 'wcb_active_campaign_list' ) ) {
											?>
                                            <option value="<?php echo $this->settings->get_params( 'wcb_active_campaign_list' ); ?>"
                                                    selected>
												<?php
												$active_campaign = new VI_WOOCOMMERCE_COUPON_BOXP_Admin_Active_Campaign();
												$result          = $active_campaign->list_view( $this->settings->get_params( 'wcb_active_campaign_list' ) );
												if ( isset( $result['name'] ) && $result['name'] ) {
													echo $result['name'];
												}
												?>
                                            </option>
											<?php
										}
										?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td></td>
                            </tr>

                            <!--SendGrid-->
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_enable_sendgrid"><?php esc_html_e( 'SendGrid', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_enable_sendgrid"
                                               id="wcb_enable_sendgrid" <?php checked( $this->settings->get_params( 'wcb_enable_sendgrid' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_enable_sendgrid"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                    </div>

                                </td>
                            </tr>
                            <tr valign="top" class="vi_wbs_enable_sendgrid">
                                <th scope="row">
                                    <label for="wcb_sendgrid_api"></label><?php esc_html_e( 'SendGrid API Key', 'woocommerce-coupon-box' ) ?>
                                </th>
                                <td>
                                    <input type="text" id="wcb_sendgrid_api" name="wcb_sendgrid_api"
                                           value="<?php echo $this->settings->get_params( 'wcb_sendgrid_api' ); ?>">
                                    <p><?php esc_html_e( '*This is the API key that\'s shown only once when your created it, not the API key ID.', 'woocommerce-coupon-box' ) ?></p>
                                    <p><?php esc_html_e( '**This API Key must have full-access permission of API Keys. You can set it ', 'woocommerce-coupon-box' ) ?>
                                        <a href="https://app.sendgrid.com/settings/api_keys"
                                           target="_blank"><?php esc_html_e( 'here.', 'woocommerce-coupon-box' ) ?></a>
                                    </p>
                                </td>
                            </tr>

                            <tr valign="top" class="vi_wbs_enable_sendgrid">
                                <th scope="row">
                                    <label for="wcb_sendgrid_list"><?php esc_html_e( 'SendGrid list', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
									<?php
									$sendgrid      = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Sendgrid();
									$mail_lists    = $sendgrid->get_lists();
									$sendgrid_list = $this->settings->get_params( 'wcb_sendgrid_list' );
									?>
                                    <select class="vi-ui fluid dropdown" name="wcb_sendgrid_list">
                                        <option value="none"><?php esc_html_e( 'Do not add to any list', 'woocommerce-coupon-box' ) ?></option>
										<?php

										if ( is_array( $mail_lists ) && count( $mail_lists ) ) {
											foreach ( $mail_lists as $key_m => $mail_list ) {
												?>
                                                <option value="<?php echo $mail_list->id ?>" <?php selected( $sendgrid_list, $mail_list->id ) ?>><?php echo $mail_list->name ?></option>
												<?php
											}
										}
										?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td></td>
                            </tr>

                            <!--Hubspot-->
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_enable_hubspot"><?php esc_html_e( 'Hubspot', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_enable_hubspot"
                                               id="wcb_enable_hubspot" <?php checked( $this->settings->get_params( 'wcb_enable_hubspot' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_enable_hubspot"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr valign="top" class="vi_wbs_enable_hubspot">
                                <th scope="row">
                                    <label for="wcb_hubspot_api"></label><?php esc_html_e( 'Hubspot API Key', 'woocommerce-coupon-box' ) ?>
                                </th>
                                <td>
                                    <input type="text" id="wcb_hubspot_api" name="wcb_hubspot_api"
                                           value="<?php echo $this->settings->get_params( 'wcb_hubspot_api' ); ?>">
                                    <p><?php esc_html_e( '**The API key for connecting with your Hubspot account. Get your API key ', 'woocommerce-coupon-box' ) ?>
                                        <a href="https://knowledge.hubspot.com/integrations/how-do-i-get-my-hubspot-api-key"
                                           target="_blank"><?php esc_html_e( 'here.', 'woocommerce-coupon-box' ) ?></a>
                                    </p></td>
                            </tr>

							<?php
							if ( class_exists( \MailPoet\API\API::class ) ) {
								$mailpoet_api   = \MailPoet\API\API::MP( 'v1' );
								$mailpoet_lists = $mailpoet_api->getLists();

								$mailpoet_selected_list = $this->settings->get_params( 'wcb_mailpoet_list' );

								?>
                                <tr>
                                    <td></td>
                                </tr>

                                <!--MailPoet-->
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="wcb_enable_mailpoet"><?php esc_html_e( 'MailPoet', 'woocommerce-coupon-box' ) ?></label>
                                    </th>
                                    <td>
                                        <div class="vi-ui toggle checkbox checked">
                                            <input type="checkbox" name="wcb_enable_mailpoet"
                                                   id="wcb_enable_mailpoet" <?php checked( $this->settings->get_params( 'wcb_enable_mailpoet' ), 1 ); ?>
                                                   value="1">
                                            <label for="wcb_enable_mailpoet"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top" class="wcb_mailpoet_list">
                                    <th scope="row">
                                        <label for="wcb_mailpoet_list">
											<?php esc_html_e( 'MailPoet list', 'woocommerce-coupon-box' ) ?>
                                        </label>
                                    </th>
                                    <td>
                                        <select class="vi-ui fluid dropdown" name="wcb_mailpoet_list[]"
                                                id="wcb_mailpoet_list" multiple>
                                            <option value=""><?php esc_html_e( 'Select lists', 'woocommerce-coupon-box' ); ?></option>
											<?php
											foreach ( $mailpoet_lists as $list ) {
												$selected = in_array( $list['id'], (array) $mailpoet_selected_list ) ? 'selected' : '';
												printf( '<option value="%s" %s>%s</option>',
													esc_attr( $list['id'] ), esc_attr( $selected ), esc_html( $list['name'] ) );
											}
											?>
                                        </select>
                                    </td>
                                </tr>
								<?php
							}

							if ( function_exists( 'mailster' ) ) {
								$mailster_lists = mailster( 'lists' )->get();

								$mailster_selected_list = $this->settings->get_params( 'wcb_mailster_list' ) ?? [];
								?>
                                <tr>
                                    <td></td>
                                </tr>

                                <!--Mailster-->
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="wcb_enable_mailster"><?php esc_html_e( 'Mailster', 'woocommerce-coupon-box' ) ?></label>
                                    </th>
                                    <td>
                                        <div class="vi-ui toggle checkbox checked">
                                            <input type="checkbox" name="wcb_enable_mailster"
                                                   id="wcb_enable_mailster" <?php checked( $this->settings->get_params( 'wcb_enable_mailster' ), 1 ); ?>
                                                   value="1">
                                            <label for="wcb_enable_mailster"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                        </div>
                                    </td>
                                </tr>
                                <tr valign="top" class="wcb_mailster_list">
                                    <th scope="row">
                                        <label for="wcb_mailster_list">
											<?php esc_html_e( 'Mailster list', 'woocommerce-coupon-box' ) ?>
                                        </label>
                                    </th>
                                    <td>
                                        <select class="vi-ui fluid dropdown" name="wcb_mailster_list[]"
                                                id="wcb_mailster_list" multiple>
                                            <option value=""><?php esc_html_e( 'Select list', 'woocommerce-coupon-box' ); ?></option>
											<?php
											foreach ( $mailster_lists as $list ) {
												$selected = in_array( $list->ID, (array) $mailster_selected_list ) ? 'selected' : '';
												printf( '<option value="%s" %s>%s</option>',
													esc_attr( $list->ID ), esc_attr( $selected ), esc_html( $list->name ) );
											}
											?>
                                        </select>
                                    </td>
                                </tr>
								<?php
							}
							?>

                            <tr>
                                <td></td>
                            </tr>

                            <!--Klaviyo-->
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_enable_klaviyo">
										<?php esc_html_e( 'Klaviyo', 'woocommerce-coupon-box' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_enable_klaviyo"
                                               id="wcb_enable_klaviyo" <?php checked( $this->settings->get_params( 'wcb_enable_klaviyo' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_enable_klaviyo"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr valign="top" class="vi_wbs_enable_klaviyo">
                                <th scope="row">
                                    <label for="wcb_klaviyo_api">
										<?php esc_html_e( 'Klaviyo API Key', 'woocommerce-coupon-box' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" id="wcb_klaviyo_api" name="wcb_klaviyo_api"
                                           value="<?php echo $this->settings->get_params( 'wcb_klaviyo_api' ); ?>">
                                    <p><?php esc_html_e( '**The API key for connecting with your Klaviyo account. Get your API key ', 'woocommerce-coupon-box' ) ?>
                                        <a href="https://developers.klaviyo.com/en/docs/retrieve-api-credentials"
                                           target="_blank"><?php esc_html_e( 'here.', 'woocommerce-coupon-box' ) ?></a>
                                    </p></td>
                            </tr>

                            <tr valign="top" class="vi_wbs_enable_klaviyo">
                                <th scope="row">
                                    <label for="wcb_klaviyo_list"><?php esc_html_e( 'Klaviyo list', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
									<?php
									$klaviyo      = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Klaviyo();
									$mail_lists   = $klaviyo->get_lists();
									$klaviyo_list = $this->settings->get_params( 'wcb_klaviyo_list' );
									?>
                                    <select class="vi-ui fluid dropdown" name="wcb_klaviyo_list">
										<?php

										if ( is_array( $mail_lists ) && ! empty( $mail_lists ) ) {
											foreach ( $mail_lists as $key_m => $mail_list ) {
												printf( '<option value="%s" %s>%s</option>',
													esc_attr( $mail_list->list_id ), selected( $klaviyo_list, $mail_list->list_id ), esc_html( $mail_list->list_name ) );
											}
										}
										?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td></td>
                            </tr>

                            <!--Sendinblue-->
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_enable_sendinblue">
										<?php esc_html_e( 'Sendinblue', 'woocommerce-coupon-box' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_enable_sendinblue"
                                               id="wcb_enable_sendinblue" <?php checked( $this->settings->get_params( 'wcb_enable_sendinblue' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_enable_sendinblue"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr valign="top" class="vi_wbs_enable_sendinblue">
                                <th scope="row">
                                    <label for="wcb_sendinblue_api">
										<?php esc_html_e( 'Sendinblue API Key', 'woocommerce-coupon-box' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" id="wcb_sendinblue_api" name="wcb_sendinblue_api"
                                           value="<?php echo $this->settings->get_params( 'wcb_sendinblue_api' ); ?>">
                                    <p><?php esc_html_e( '**The API key for connecting with your Sendinblue account. Get your API key ', 'woocommerce-coupon-box' ) ?>
                                        <a href="https://developers.sendinblue.com/docs/migration-guide-for-api-v2-users-1#get-a-new-api-v3-key"
                                           target="_blank"><?php esc_html_e( 'here.', 'woocommerce-coupon-box' ) ?></a>
                                    </p>
                                </td>
                            </tr>

                            <tr valign="top" class="vi_wbs_enable_sendinblue">
                                <th scope="row">
                                    <label for="wcb_sendinblue_list"><?php esc_html_e( 'Sendinblue list', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
									<?php
									$sendinblue      = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Sendinblue();
									$mail_lists      = $sendinblue->get_lists();
									$sendinblue_list = (array) $this->settings->get_params( 'wcb_sendinblue_list' );
									?>
                                    <select class="vi-ui fluid dropdown" name="wcb_sendinblue_list[]" multiple>
                                        <option value=""><?php esc_html_e( 'Select lists', 'woocommerce-coupon-box' ); ?></option>
										<?php

										if ( is_array( $mail_lists ) && ! empty( $mail_lists ) ) {
											foreach ( $mail_lists as $key_m => $mail_list ) {
												$selected = in_array( $mail_list->id, $sendinblue_list ) ? 'selected' : '';
												printf( '<option value="%s" %s>%s</option>',
													esc_attr( $mail_list->id ), esc_attr( $selected ), esc_html( $mail_list->name ) );
											}
										}
										?>
                                    </select>
                                </td>
                            </tr>

                            <!--Getresponse-->
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_enable_getresponse">
				                        <?php esc_html_e( 'GetResponse', 'woocommerce-coupon-box' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_enable_getresponse"
                                               id="wcb_enable_getresponse" <?php checked( $this->settings->get_params( 'wcb_enable_getresponse' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_enable_getresponse"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr valign="top" class="vi_wbs_enable_getresponse">
                                <th scope="row">
                                    <label for="wcb_getresponse_api">
				                        <?php esc_html_e( 'GetResponse API Key', 'woocommerce-coupon-box' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" id="wcb_getresponse_api" name="wcb_getresponse_api"
                                           value="<?php echo $this->settings->get_params( 'wcb_getresponse_api' ); ?>">
                                    <p><?php esc_html_e( '**The API key for connecting with your Getresponse account. Get your API key ', 'woocommerce-coupon-box' ) ?>
                                        <a href="https://app.getresponse.com/api"
                                           target="_blank"><?php esc_html_e( 'here.', 'woocommerce-coupon-box' ) ?></a>
                                    </p>
                                </td>
                            </tr>
                            <tr valign="top" class="vi_wbs_enable_getresponse">
                                <th scope="row">
                                    <label for="wcb_getresponse_list"><?php esc_html_e( 'GetResponse campaign (list)', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
			                        <?php
			                        $getresponse      = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Getresponse();
			                        $mail_lists_gr    = $getresponse->get_lists();
			                        $getresponse_list = $this->settings->get_params( 'wcb_getresponse_list' );
			                        ?>
                                    <select class="vi-ui fluid dropdown" name="wcb_getresponse_list">
                                        <option value=""><?php esc_html_e( 'Select campaign', 'woocommerce-coupon-box' ); ?></option>
				                        <?php
				                        if ( is_array( $mail_lists_gr ) && ! empty( $mail_lists_gr ) ) {
					                        foreach ( $mail_lists_gr as $key_m => $mail_list ) {
						                        $selected = $mail_list->campaignId == $getresponse_list ? 'selected' : '';
						                        printf( '<option value="%s" %s>%s</option>',
							                        esc_attr( $mail_list->campaignId ), esc_attr( $selected ), esc_html( $mail_list->name ) );
					                        }
				                        }
				                        ?>
                                    </select>
                                </td>
                            </tr>

                            <!--Constant contact-->
<!--                            <tr valign="top">-->
<!--                                <th scope="row">-->
<!--                                    <label for="wcb_enable_constantcontact">-->
<!--				                        --><?php //esc_html_e( 'Constant contact', 'woocommerce-coupon-box' ) ?>
<!--                                    </label>-->
<!--                                </th>-->
<!--                                <td>-->
<!--                                    <div class="vi-ui toggle checkbox checked">-->
<!--                                        <input type="checkbox" name="wcb_enable_constantcontact"-->
<!--                                               id="wcb_enable_constantcontact" --><?php //checked( $this->settings->get_params( 'wcb_enable_constantcontact' ), 1 ); ?>
<!--                                               value="1">-->
<!--                                        <label for="wcb_enable_constantcontact">--><?php //esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?><!--</label>-->
<!--                                    </div>-->
<!--                                </td>-->
<!--                            </tr>-->
<!--                            <tr valign="top" class="vi_wbs_enable_constantcontact">-->
<!--                                <th scope="row">-->
<!--                                    <label for="wcb_constantcontact_api">-->
<!--				                        --><?php //esc_html_e( 'Constant contact API Key', 'woocommerce-coupon-box' ) ?>
<!--                                    </label>-->
<!--                                </th>-->
<!--                                <td>-->
<!--                                    <input type="text" id="wcb_constantcontact_api" name="wcb_constantcontact_api"-->
<!--                                           value="--><?php //echo $this->settings->get_params( 'wcb_constantcontact_api' ); ?><!--">-->
<!--                                    <p>--><?php //esc_html_e( '**The API key for connecting with your Constant contact account. Create your Application and get your API key ', 'woocommerce-coupon-box' ) ?>
<!--                                        <a href="https://app.constantcontact.com/pages/dma/portal/"-->
<!--                                           target="_blank">--><?php //esc_html_e( 'here.', 'woocommerce-coupon-box' ) ?><!--</a>-->
<!--                                    </p>-->
<!--                                </td>-->
<!--                            </tr>-->
<!--                            <tr valign="top" class="vi_wbs_enable_constantcontact">-->
<!--                                <th scope="row">-->
<!--                                    <label for="wcb_constantcontact_list">--><?php //esc_html_e( 'Constant contact list', 'woocommerce-coupon-box' ) ?><!--</label>-->
<!--                                </th>-->
<!--                                <td>-->
<!--			                        --><?php
//			                        $constantcontact      = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Constantcontact();
//			                        $mail_lists      = $constantcontact->get_lists();
//			                        $constantcontact_list = (array) $this->settings->get_params( 'wcb_constantcontact_list' );
//			                        ?>
<!--                                    <select class="vi-ui fluid dropdown" name="wcb_constantcontact_list[]" multiple>-->
<!--                                        <option value="">--><?php //esc_html_e( 'Select lists', 'woocommerce-coupon-box' ); ?><!--</option>-->
<!--				                        --><?php
//
//				                        if ( is_array( $mail_lists ) && ! empty( $mail_lists ) ) {
//					                        foreach ( $mail_lists as $key_m => $mail_list ) {
//						                        $selected = in_array( $mail_list->id, $constantcontact_list ) ? 'selected' : '';
//						                        printf( '<option value="%s" %s>%s</option>',
//							                        esc_attr( $mail_list->id ), esc_attr( $selected ), esc_html( $mail_list->name ) );
//					                        }
//				                        }
//				                        ?>
<!--                                    </select>-->
<!--                                </td>-->
<!--                            </tr>-->

                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment wcb-container" data-tab="wcb-grecaptcha">
                        <table class="form-table">
                            <tr align="top">
                                <th>
                                    <label for="wcb_recaptcha"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_recaptcha" id="wcb_recaptcha" value="1"
                                               tabindex="0" <?php checked( $this->settings->get_params( 'wcb_recaptcha' ), '1' ); ?>>
                                    </div>
                                </td>
                            </tr>
                            <tr align="top">
                                <th>
                                    <label for="wcb_recaptcha_version"><?php esc_html_e( 'Version', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <select name="wcb_recaptcha_version" id="wcb_recaptcha_version"
                                            class="vi-ui fluid dropdown wcb_recaptcha_version">
                                        <option value="2" <?php selected( $this->settings->get_params( 'wcb_recaptcha_version' ), '2' ) ?>><?php esc_html_e( 'reCAPTCHA v2', 'woocommerce-coupon-box' ) ?></option>
                                        <option value="3" <?php selected( $this->settings->get_params( 'wcb_recaptcha_version' ), '3' ) ?>><?php esc_html_e( 'reCAPTCHA v3', 'woocommerce-coupon-box' ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr align="top">
                                <th>
                                    <label for="wcb_recaptcha_site_key"><?php esc_html_e( 'Site key', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcb_recaptcha_site_key"
                                           value="<?php echo $this->settings->get_params( 'wcb_recaptcha_site_key' ) ?>">
                                </td>
                            </tr>
                            <tr align="top">
                                <th>
                                    <label for="wcb_recaptcha_secret_key"><?php esc_html_e( 'Secret key', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcb_recaptcha_secret_key"
                                           value="<?php echo $this->settings->get_params( 'wcb_recaptcha_secret_key' ) ?>">
                                </td>
                            </tr>
                            <tr align="top" class="wcb-recaptcha-v2-wrap"
                                style="<?php echo $this->settings->get_params( 'wcb_recaptcha_version' ) == 2 ? '' : 'display:none;'; ?>">
                                <th>
                                    <label for="wcb_recaptcha_secret_theme"><?php esc_html_e( 'Theme', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <select name="wcb_recaptcha_secret_theme" id="wcb_recaptcha_secret_theme"
                                            class="vi-ui fluid dropdown wcb_recaptcha_secret_theme">
                                        <option value="dark" <?php selected( $this->settings->get_params( 'wcb_recaptcha_secret_theme' ), 'dark' ) ?>><?php esc_html_e( 'Dark', 'woocommerce-coupon-box' ) ?></option>
                                        <option value="light" <?php selected( $this->settings->get_params( 'wcb_recaptcha_secret_theme' ), 'light' ) ?>><?php esc_html_e( 'Light', 'woocommerce-coupon-box' ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th>
                                    <label for=""><?php esc_html_e( 'Guide', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div>
                                        <strong class="wcb-recaptcha-v2-wrap"
                                                style="<?php echo $this->settings->get_params( 'wcb_recaptcha_version' ) == 2 ? '' : 'display:none;'; ?>">
											<?php esc_html_e( 'Get Google reCAPTCHA V2 Site and Secret key', 'woocommerce-coupon-box' ) ?>
                                        </strong>
                                        <strong class="wcb-recaptcha-v3-wrap"
                                                style="<?php echo $this->settings->get_params( 'wcb_recaptcha_version' ) == 3 ? '' : 'display:none;'; ?>">
											<?php esc_html_e( 'Get Google reCAPTCHA V3 Site and Secret key', 'woocommerce-coupon-box' ) ?>
                                        </strong>
                                        <ul>
                                            <li><?php _e( '1, Visit <a target="_blank" href="http://www.google.com/recaptcha/admin">page</a> to sign up for an API key pair with your Gmail account', 'woocommerce-coupon-box' ) ?></li>

                                            <li class="wcb-recaptcha-v2-wrap"
                                                style="<?php echo $this->settings->get_params( 'wcb_recaptcha_version' ) == 2 ? '' : 'display:none;'; ?>">
												<?php esc_html_e( '2, Choose reCAPTCHA v2 checkbox ', 'woocommerce-coupon-box' ) ?>
                                            </li>
                                            <li class="wcb-recaptcha-v3-wrap"
                                                style="<?php echo $this->settings->get_params( 'wcb_recaptcha_version' ) == 3 ? '' : 'display:none;'; ?>">
												<?php esc_html_e( '2, Choose reCAPTCHA v3', 'woocommerce-coupon-box' ) ?>
                                            </li>
                                            <li><?php esc_html_e( '3, Fill in authorized domains', 'woocommerce-coupon-box' ) ?></li>
                                            <li><?php esc_html_e( '4, Accept terms of service and click Register button', 'woocommerce-coupon-box' ) ?></li>
                                            <li><?php esc_html_e( '5, Copy and paste the site and secret key into the above field', 'woocommerce-coupon-box' ) ?></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="vi-ui bottom attached tab segment wcb-container" data-tab="wcb-assignpage">
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_assign_home"><?php esc_html_e( 'Home Page', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="wcb_assign_home"
                                               id="wcb_assign_home" <?php checked( $this->settings->get_params( 'wcb_assign_home' ), 1 ); ?>
                                               value="1">
                                        <label for="wcb_assign_home"><?php esc_html_e( 'Enable', 'woocommerce-coupon-box' ) ?></label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'Turn on to show coupon box only on Home page', 'woocommerce-coupon-box' ) ?></p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wcb_assign"><?php esc_html_e( 'Assign Page', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" id="wcb_assign" name="wcb_assign"
                                           value="<?php echo $this->settings->get_params( 'wcb_assign' ) ?>"
                                           placeholder="<?php esc_html_e( 'Ex: !is_page(array(123,41,20))', 'woocommerce-coupon-box' ) ?>">

                                    <p class="description"><?php esc_html_e( 'Let you control on which pages coupon box will appear using ', 'woocommerce-coupon-box' ) ?>
                                        <a href="http://codex.wordpress.org/Conditional_Tags"><?php esc_html_e( 'WP\'s conditional tags', 'woocommerce-coupon-box' ) ?></a>
                                    </p>
                                    <p class="description">
                                        <strong>*</strong><?php esc_html_e( '"Home page" option above must be disabled to run these conditional tags.', 'woocommerce-coupon-box' ) ?>
                                    </p>
                                    <p class="description"><?php esc_html_e( 'Use ', 'woocommerce-coupon-box' ); ?>
                                        <strong>is_cart()</strong><?php esc_html_e( ' to show only on cart page', 'woocommerce-coupon-box' ) ?>
                                    </p>
                                    <p class="description"><?php esc_html_e( 'Use ', 'woocommerce-coupon-box' ); ?>
                                        <strong>is_checkout()</strong><?php esc_html_e( ' to show only on checkout page', 'woocommerce-coupon-box' ) ?>
                                    </p>
                                    <p class="description"><?php esc_html_e( 'Use ', 'woocommerce-coupon-box' ); ?>
                                        <strong>is_product_category()</strong><?php esc_html_e( 'to show only on WooCommerce category page', 'woocommerce-coupon-box' ) ?>
                                    </p>
                                    <p class="description"><?php esc_html_e( 'Use ', 'woocommerce-coupon-box' ); ?>
                                        <strong>is_shop()</strong><?php esc_html_e( ' to show only on WooCommerce shop page', 'woocommerce-coupon-box' ) ?>
                                    </p>
                                    <p class="description"><?php esc_html_e( 'Use ', 'woocommerce-coupon-box' ); ?>
                                        <strong>is_product()</strong><?php esc_html_e( ' to show only on WooCommerce single product page', 'woocommerce-coupon-box' ) ?>
                                    </p>
                                    <p class="description">
                                        <strong>**</strong><?php esc_html_e( 'Combining 2 or more conditionals using || to show coupon box if 1 of the conditionals matched. e.g use ', 'woocommerce-coupon-box' ); ?>
                                        <strong>is_cart() ||
                                            is_checkout()</strong><?php esc_html_e( ' to show only on cart page and checkout page', 'woocommerce-coupon-box' ) ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment wcb-container" data-tab="wcb-design">
                        <div><a href="customize.php?autofocus[panel]=wcb_coupon_box_design"
                                target="_blank"><?php esc_html_e( 'Go to design now', 'woocommerce-coupon-box' ) ?></a>
                        </div>
                        <p><?php _e( 'To design your WooCommerce Coupon Box in other languages, please switch your site to respecting language(WPML or Polylang) before going to design or customize. Switching language during customize mode does not work.', 'woocommerce-coupon-box' ) ?></p>
                    </div>
                    <div class="vi-ui bottom attached tab segment wcb-container" data-tab="update">
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">
                                    <label for="auto-update-key"><?php esc_html_e( 'Auto Update Key', 'woocommerce-coupon-box' ) ?></label>
                                </th>
                                <td>
                                    <div class="fields">
                                        <div class="ten wide field">
                                            <input type="text" name="wcb_purchased_code" id="auto-update-key"
                                                   class="villatheme-autoupdate-key-field"
                                                   value="<?php echo htmlentities( $this->settings->get_params( 'wcb_purchased_code' ) ) ?>">
                                        </div>
                                        <div class="six wide field">
                                        <span class="vi-ui button green villatheme-get-key-button"
                                              data-href="https://api.envato.com/authorization?response_type=code&client_id=villatheme-download-keys-6wzzaeue&redirect_uri=https://villatheme.com/update-key"
                                              data-id="22495702"><?php echo esc_html__( 'Get Key', 'woocommerce-coupon-box' ) ?></span>
                                        </div>
                                    </div>
									<?php do_action( 'woocommerce-coupon-box_key' ) ?>
                                    <p class="description"><?php echo __( 'Please fill your key what you get from <a target="_blank" href="https://villatheme.com/my-download">https://villatheme.com/my-download</a>. You can auto update WooCommerce Coupon Box plugin. See <a target="_blank" href="https://villatheme.com/knowledge-base/how-to-use-auto-update-feature/">guide</a>', 'woocommerce-coupon-box' ) ?></p>
                                </td>
                            </tr>

                        </table>
                    </div>
                    <p>
                        <input type="submit" name="wcb_save_data"
                               value="<?php esc_attr_e( 'Save', 'woocommerce-coupon-box' ); ?>"
                               class="vi-ui primary button">
                        <button class="vi-ui button labeled icon"
                                name="wcb_check_key"><i
                                    class="save icon"></i><?php esc_html_e( 'Save & Check Key', 'woocommerce-coupon-box' ); ?>
                        </button>
                    </p>
                </form>
            </div>

        </div>
		<?php
		do_action( 'villatheme_support_woocommerce-coupon-box' );
	}

	public function admin_enqueue_script() {
		$page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		if ( $page == 'woocommerce_coupon_box' ) {
			global $wp_scripts;
			$scripts = $wp_scripts->registered;
			if ( isset( $scripts['jquery-ui-tabs'] ) ) {
				unset( $scripts['jquery-ui-tabs'] );
				wp_dequeue_script( 'jquery-ui-tabs' );
			}
			foreach ( $scripts as $k => $script ) {
				preg_match( '/^\/wp-/i', $script->src, $result );
				if ( count( array_filter( $result ) ) < 1 ) {
					wp_dequeue_script( $script->handle );
				}
//				preg_match( '/select2/i', $k, $result );
//				if ( count( array_filter( $result ) ) ) {
//					unset( $wp_scripts->registered[ $k ] );
//					wp_dequeue_script( $script->handle );
//				}
//				preg_match( '/bootstrap/i', $k, $result );
//				if ( count( array_filter( $result ) ) ) {
//					unset( $wp_scripts->registered[ $k ] );
//					wp_dequeue_script( $script->handle );
//				}
			}
			// style
			wp_enqueue_style( 'woocommerce-coupon-box-icon', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'icon.min.css' );
			wp_enqueue_style( 'woocommerce-coupon-box-form', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'form.min.css' );
			wp_enqueue_style( 'woocommerce-coupon-box-button', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'button.min.css' );
			wp_enqueue_style( 'woocommerce-coupon-box-dropdown', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'dropdown.min.css' );
			wp_enqueue_style( 'woocommerce-coupon-box-checkbox', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'checkbox.min.css' );
			wp_enqueue_style( 'woocommerce-coupon-box-transition', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'transition.min.css' );
			wp_enqueue_style( 'woocommerce-coupon-box-tab', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'tab.css' );
			wp_enqueue_style( 'woocommerce-coupon-box-segment', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'segment.min.css' );
			wp_enqueue_style( 'woocommerce-coupon-box-menu', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'menu.min.css' );
			wp_enqueue_style( 'woocommerce-coupon-box-select2', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'select2.min.css' );
			wp_enqueue_style( 'woocommerce-coupon-box-admin', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'wcb-admin.css' );
			wp_enqueue_style( 'woocommerce-coupon-villatheme-support', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'villatheme-support.css' );

			//script
			/*Color picker*/
			wp_enqueue_script(
				'iris', admin_url( 'js/iris.min.js' ), array(
				'jquery-ui-draggable',
				'jquery-ui-slider',
				'jquery-touch-punch'
			), false, 1
			);
			wp_enqueue_script( 'woocommerce-coupon-box-form', VI_WOOCOMMERCE_COUPON_BOX_JS . 'form.min.js', array( 'jquery' ), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
			wp_enqueue_script( 'woocommerce-coupon-box-checkbox', VI_WOOCOMMERCE_COUPON_BOX_JS . 'checkbox.min.js', array( 'jquery' ), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
			wp_enqueue_script( 'woocommerce-coupon-box-dropdown', VI_WOOCOMMERCE_COUPON_BOX_JS . 'dropdown.min.js', array( 'jquery' ), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
			wp_enqueue_script( 'woocommerce-coupon-box-transition', VI_WOOCOMMERCE_COUPON_BOX_JS . 'transition.min.js', array( 'jquery' ), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
			wp_enqueue_script( 'woocommerce-coupon-box-tab', VI_WOOCOMMERCE_COUPON_BOX_JS . 'tab.js', array( 'jquery' ), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
			wp_enqueue_script( 'woocommerce-coupon-box-address', VI_WOOCOMMERCE_COUPON_BOX_JS . 'jquery.address-1.6.min.js', array( 'jquery' ), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
			wp_enqueue_script( 'woocommerce-coupon-box-select2', VI_WOOCOMMERCE_COUPON_BOX_JS . 'select2.js', array( 'jquery' ), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
			wp_enqueue_script( 'woocommerce-coupon-box-admin-javascript', VI_WOOCOMMERCE_COUPON_BOX_JS . 'wcb-admin.js', array( 'jquery' ), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
			wp_localize_script( 'woocommerce-coupon-box-admin-javascript', 'woo_coupon_box_params_admin', array(
				'url' => admin_url( 'admin-ajax.php' ),
			) );
		}
	}

	public function save_data_coupon_box() {
		/**
		 * Check update
		 */
		if ( class_exists( 'VillaTheme_Plugin_Check_Update' ) ) {
			$setting_url = admin_url( 'edit.php?post_type=wcb&page=woocommerce_coupon_box' );
			$key         = $this->settings->get_params( 'wcb_purchased_code' );
			new VillaTheme_Plugin_Check_Update (
				VI_WOOCOMMERCE_COUPON_BOX_VERSION,                    // current version
				'https://villatheme.com/wp-json/downloads/v3',  // update path
				'woocommerce-coupon-box/woocommerce-coupon-box.php',                  // plugin file slug
				'woocommerce-coupon-box', '5136', $key, $setting_url
			);
			new VillaTheme_Plugin_Updater( 'woocommerce-coupon-box/woocommerce-coupon-box.php', 'woocommerce-coupon-box', $setting_url );
			if ( isset( $_POST['wcb_check_key'] ) ) {
				delete_site_transient( 'update_plugins' );
				delete_transient( 'villatheme_item_5136' );
				delete_option( 'woocommerce-coupon-box_messages' );
			}
		}
		$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : '';
		$page      = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		if ( $post_type !== 'wcb' || $page !== 'woocommerce_coupon_box' ) {
			return;
		}
		/*wpml*/
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			global $sitepress;
			$default_lang           = $sitepress->get_default_language();
			$this->default_language = $default_lang;
			$languages              = icl_get_languages( 'skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str' );
			$this->languages_data   = $languages;
			if ( count( $languages ) ) {
				foreach ( $languages as $key => $language ) {
					if ( $key != $default_lang ) {
						$this->languages[] = $key;
					}
				}
			}
		} elseif ( class_exists( 'Polylang' ) ) {
			/*Polylang*/
			$languages    = pll_languages_list();
			$default_lang = pll_default_language( 'slug' );
			foreach ( $languages as $language ) {
				if ( $language == $default_lang ) {
					continue;
				}
				$this->languages[] = $language;
			}
		}
		global $coupon_box_settings;
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_POST['_woocouponbox_nonce'] ) || ! wp_verify_nonce( $_POST['_woocouponbox_nonce'], 'woocouponbox_action_nonce' ) ) {
			return;
		}

		$args                  = array(
			/*old option*/
			'wcb_active'         => '',
			'wcb_coupon'         => '',
			'wcb_email_campaign' => '',

			'wcb_enable_mailchimp'                          => '',
			'wcb_mailchimp_double_optin'                    => '',
			'wcb_api'                                       => '',
			'wcb_mlists'                                    => '',
			'wcb_assign_home'                               => '',
			'wcb_assign'                                    => '',
			/*new options*/
			'wcb_coupon_select'                             => '',
			'wcb_coupon_custom'                             => '',
			'wcb_coupon_unique_amount'                      => 0,
			'wcb_coupon_unique_date_expires'                => null,
			'wcb_coupon_unique_discount_type'               => 'fixed_cart',
			'wcb_coupon_unique_description'                 => '',
			'wcb_coupon_unique_individual_use'              => false,
			'wcb_coupon_unique_product_ids'                 => array(),
			'wcb_coupon_unique_excluded_product_ids'        => array(),
			'wcb_coupon_unique_usage_limit'                 => 0,
			'wcb_coupon_unique_usage_limit_per_user'        => 0,
			'wcb_coupon_unique_limit_usage_to_x_items'      => null,
			'wcb_coupon_unique_free_shipping'               => false,
			'wcb_coupon_unique_product_categories'          => array(),
			'wcb_coupon_unique_excluded_product_categories' => array(),
			'wcb_coupon_unique_exclude_sale_items'          => false,
			'wcb_coupon_unique_minimum_amount'              => '',
			'wcb_coupon_unique_maximum_amount'              => '',
			'wcb_coupon_unique_email_restrictions'          => '',
			'wcb_coupon_unique_prefix'                      => '',

			'wcb_email_enable'                  => 1,
			'wcb_email_subject'                 => '',
			'wcb_email_heading'                 => '',
			'wcb_email_content'                 => '',
			'wcb_register_email_content'        => '',
			'wcb_button_shop_now_title'         => '',
			'wcb_button_shop_now_url'           => '',
			'wcb_button_shop_now_size'          => '',
			'wcb_button_shop_now_color'         => '',
			'wcb_button_shop_now_bg_color'      => '',
			'wcb_button_shop_now_border_radius' => '',

			'wcb_disable_login'          => '',
			'wcb_multi_language'         => '',
			'wcb_select_popup'           => '',
			'wcb_popup_time'             => '',
			'wcb_popup_scroll'           => '',
			'wcb_popup_exit'             => '',
			'wcb_on_close'               => '',
			'wcb_expire'                 => '',
			'wcb_expire_unit'            => '',
			'wcb_expire_subscribed'      => '',
			'wcb_purchased_code'         => '',
			'wcb_enable_active_campaign' => '',
			'wcb_active_campaign_api'    => '',
			'wcb_active_campaign_url'    => '',
			'wcb_active_campaign_list'   => '',
			'wcb_enable_sendgrid'        => '',
			'wcb_sendgrid_api'           => '',
			'wcb_sendgrid_list'          => '',
			'wcb_enable_hubspot'         => '',
			'wcb_hubspot_api'            => '',
			'wcb_enable_mailpoet'        => '',
			'wcb_mailpoet_list'          => [],
			'wcb_enable_mailster'        => '',
			'wcb_mailster_list'          => [],
			'wcb_enable_klaviyo'         => '',
			'wcb_klaviyo_api'            => '',
			'wcb_klaviyo_list'           => '',
			'wcb_enable_sendinblue'      => '',
			'wcb_sendinblue_api'         => '',
			'wcb_sendinblue_list'        => [],
			'wcb_enable_getresponse'     => '',
			'wcb_getresponse_api'        => '',
			'wcb_getresponse_list'       => '',
//			'wcb_enable_constantcontact' => '',
//			'wcb_constantcontact_api'    => '',
//			'wcb_constantcontact_list'   => [],
			'ajax_endpoint'              => '',

			'wcb_recaptcha_site_key'      => '',
			'wcb_recaptcha_secret_key'    => '',
			'wcb_recaptcha_version'       => 2,
			'wcb_recaptcha_secret_theme'  => '',
			'wcb_recaptcha'               => 0,
			'wcb_never_reminder_enable'   => 0,
			'email_template'              => 0,
			'email_template_for_register' => 0,
			'wcb_register_account'        => 0,
			'wcb_restrict_domain'         => '',
		);
		$wp_kses_post_contents = array( 'wcb_email_content', 'wcb_coupon_unique_description', 'wcb_register_email_content' );

		if ( count( $this->languages ) ) {
			foreach ( $this->languages as $key => $value ) {
				$args[ 'email_template_' . $value ]              = '';
				$args[ 'email_template_for_register_' . $value ] = '';
				$args[ 'wcb_email_subject_' . $value ]           = '';
				$args[ 'wcb_email_heading_' . $value ]           = '';
				$args[ 'wcb_email_content_' . $value ]           = '';
				$args[ 'wcb_button_shop_now_title_' . $value ]   = '';
				$args[ 'wcb_button_shop_now_url_' . $value ]     = '';
				$args[ 'wcb_mlists_' . $value ]                  = '';
				$wp_kses_post_contents[]                         = 'wcb_email_content_' . $value;
			}
		}

		if ( get_option( 'wcb_active' ) !== false ) {
			foreach ( $args as $key => $arg ) {
				$args[ $key ] = get_option( $key, '' );
				delete_option( $key );
			}
			$remove_option = array(
				'wcb_facebook',
				'wcb_twitter',
				'wcb_pinterest',
				'wcb_instagram',
				'wcb_dribbble',
				'wcb_tumblr',
				'wcb_gplus',
				'wcb_vkontakte',
				'wcb_linkedin',
				'wcb_only_reg',
				'wcb_countdown_timer',
				'wcb_toggle_coupon_box',
				'wcb_initial_time',
			);
			foreach ( $remove_option as $k => $v ) {
				delete_option( $v );
			}

			update_option( 'woo_coupon_box_params', $args );
		} else {

			$keys_arr = [
				'wcb_coupon_unique_product_categories',
				'wcb_coupon_unique_excluded_product_categories',
				'wcb_coupon_unique_product_ids',
				'wcb_coupon_unique_excluded_product_ids',
				'wcb_mailpoet_list',
				'wcb_sendinblue_list',
//				'wcb_getresponse_list',
				'wcb_mailster_list',
			];

			foreach ( $args as $key => $arg ) {
				if ( in_array( $key, $keys_arr ) ) {
					$args[ $key ] = isset( $_POST[ $key ] ) ? array_map( 'sanitize_text_field', $_POST[ $key ] ) : '';

				} elseif ( in_array( $key, $wp_kses_post_contents ) ) {
					$args[ $key ] = isset( $_POST[ $key ] ) ? wp_kses_post( wp_unslash( $_POST[ $key ] ) ) : '';

				} else {
					$args[ $key ] = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
				}
			}

			$args = wp_parse_args( $args, get_option( 'woo_coupon_box_params', $coupon_box_settings ) );
			update_option( 'woo_coupon_box_params', $args );
			$coupon_box_settings = $args;
		}
	}
}