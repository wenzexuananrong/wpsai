<?php

namespace VIWEC\INCLUDES;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Trigger {

	protected static $instance = null;
	protected $template_id;
	protected $object;
	protected $user;
	protected $use_default_temp = false;
	protected $class_email;
	protected $heading;
	protected $unique = [];
	protected $clear_css;
	protected $disable_email_template;
	public $plain_search = array(
		"/\r/",                                                  // Non-legal carriage return.
		'/&(nbsp|#0*160);/i',                                    // Non-breaking space.
		'/&(quot|rdquo|ldquo|#0*8220|#0*8221|#0*147|#0*148);/i', // Double quotes.
		'/&(apos|rsquo|lsquo|#0*8216|#0*8217);/i',               // Single quotes.
		'/&gt;/i',                                               // Greater-than.
		'/&lt;/i',                                               // Less-than.
		'/&#0*38;/i',                                            // Ampersand.
		'/&amp;/i',                                              // Ampersand.
		'/&(copy|#0*169);/i',                                    // Copyright.
		'/&(trade|#0*8482|#0*153);/i',                           // Trademark.
		'/&(reg|#0*174);/i',                                     // Registered.
		'/&(mdash|#0*151|#0*8212);/i',                           // mdash.
		'/&(ndash|minus|#0*8211|#0*8722);/i',                    // ndash.
		'/&(bull|#0*149|#0*8226);/i',                            // Bullet.
		'/&(pound|#0*163);/i',                                   // Pound sign.
		'/&(euro|#0*8364);/i',                                   // Euro sign.
		'/&(dollar|#0*36);/i',                                   // Dollar sign.
		'/&[^&\s;]+;/i',                                         // Unknown/unhandled entities.
		'/[ ]{2,}/',                                             // Runs of spaces, post-handling.
	);

	public $plain_replace = array( '', ' ', '"', "'", '>', '<', '&', '&', '(c)', '(tm)', '(R)', '--', '-', '*', '£', 'EUR', '$', '', ' ', );
	protected $fix_default_thumbnail;

	private function __construct() {
		add_filter( 'wc_get_template', array( $this, 'replace_template_path' ), 10, 5 );
		add_action( 'viwec_email_template', array( $this, 'load_template' ), 10 );
		add_action( 'woocommerce_email', array( $this, 'get_email_template_id' ), 1 );
		add_filter( 'wp_new_user_notification_email', array( $this, 'replace_wp_new_user_email' ), 1, 3 );
		add_filter( 'retrieve_password_title', array( $this, 'replace_wp_reset_password_title' ), 1, 3 );

		$priority_retrieve_password_message = 1;
		/*Compatible with paid-memberships-pro */
		if ( function_exists( 'pmpro_gateways' ) ) {
			$priority_retrieve_password_message = 11;
		}
		add_filter( 'retrieve_password_message', array( $this, 'replace_wp_reset_password_email' ), $priority_retrieve_password_message, 4 );

		add_filter( 'woocommerce_email_styles', array( $this, 'remove_style' ), 99 );
		add_filter( 'woocommerce_email_styles', array( $this, 'custom_css' ), 99999 );


		add_filter( 'woocommerce_mail_callback_params', array( $this, 'use_default_template_email' ), 999, 2 );
		add_filter( 'woocommerce_mail_callback_params', array( $this, 'add_attachment_file' ), 99999 );
		add_filter( 'woocommerce_mail_callback_params', array( $this, 'reset_template_id' ), 999999 );

//		Email with wc_mail
		add_action( 'woocommerce_email_header', array( $this, 'send_email_via_wc_mailer' ), 0 );
		add_filter( 'woocommerce_email_get_option', [ $this, 'add_padding_for_addition_content' ], 10, 4 );

//		Minify email content
		add_filter( 'woocommerce_mail_callback_params', [ $this, 'minify_email_content' ], 99999 );

		add_shortcode( 'wec_order_meta_subject', [ $this, 'subject_shortcode' ] );

	}

	public static function init() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function remove_header_footer_hook( $bool ) {
		$remove = apply_filters( 'viwec_remove_origin_email_header_footer', $bool );
		if ( $remove ) {
			remove_all_actions( 'woocommerce_email_header' );
			remove_all_actions( 'woocommerce_email_footer' );
		}
	}

	public function get_email_template_id( $email_object ) {

		if ( is_array( $email_object->emails ) ) {
			foreach ( $email_object->emails as $email ) {
				add_filter( 'woocommerce_email_recipient_' . $email->id, array( $this, 'trigger_recipient' ), 10, 3 );
				add_filter( 'woocommerce_email_subject_' . $email->id, array( $this, 'replace_subject' ), 10, 2 );
			}
		}
		add_filter( 'woocommerce_email_recipient_customer_partially_refunded_order', array( $this, 'trigger_recipient' ), 10, 3 );
		add_filter( 'woocommerce_email_recipient_customer_invoice_pending', array( $this, 'trigger_recipient' ), 10, 3 );
	}

	public function trigger_recipient( $recipient, $object, $class_email ) {
		$this->template_id = '';

		if ( ! $object ) {
			return $recipient;
		}

		$status_options = get_option( 'viwec_emails_status', [] );
		if ( ! empty( $status_options[ $class_email->id ] ) && $status_options[ $class_email->id ] == 'disable' ) {
			$this->disable_email_template = true;

			return $recipient;
		}

		if ( is_a( $object, 'WC_Order' ) ) {
			$this->template_id = $this->get_template_id_has_order( $class_email->id, $object );
		} else {
			$this->template_id = $this->get_template_id_no_order( $class_email->id, $object );
		}

		if ( ! $this->template_id ) {
			$this->use_default_temp = $this->get_default_template();
			$this->remove_header_footer_hook( $this->use_default_temp );
			add_filter( 'woocommerce_email_order_items_args', [ $this, 'show_image' ] );
		}

		$this->clear_css   = $this->template_id || $this->use_default_temp ? true : false;
		$this->object      = $object;
		$this->class_email = $class_email;

		return $recipient;
	}

	public function show_image( $args ) {
		if ( $this->use_default_temp ) {
			$show_image         = get_post_meta( $this->use_default_temp, 'viwec_enable_img_for_default_template', true );
			$args['show_image'] = $show_image ? true : false;

			$size               = get_post_meta( $this->use_default_temp, 'viwec_img_size_for_default_template', true );
			$args['image_size'] = $size ? [ (int) $size, 300 ] : [ 80, 80 ];
		}

		return $args;
	}

	public function subject_shortcode( $args ) {
		$args                = wp_parse_args( $args, [ 'key' => '', 'meta_customer_order' => '' ] );
		$meta_key            = $args['key'];
		$meta_customer_order = $args['meta_customer_order'];
		$meta_value          = '';
		if ( $this->object && $meta_key ) {
			if ( $meta_customer_order === 'yes' ) {
				$user = $this->object->get_user();
				if ( $user ) {
					$meta_value = get_user_meta( $user->ID, $meta_key, true );
				}
			} else {
				$meta_value = get_post_meta( $this->object->get_id(), $meta_key, true );
			}
		} elseif ( $this->user && $meta_key ) {
			$user = $this->user instanceof \WC_Email ? $this->user->object : $this->user;
			if ( $user && isset( $user->ID ) ) {
				$meta_value = get_user_meta( $user->ID, $meta_key, true );
			}
		}

		if ( is_array( $meta_value ) ) {
			$meta_value = json_encode( $meta_value );
		}

		$meta_value = apply_filters( 'viwec_render_shortcode_order_meta', $meta_value, $meta_key );

		return wp_kses_post( $meta_value );
	}

	public function replace_subject( $subject, $object ) {
		if ( $this->template_id ) {
			$_subject = get_post( $this->template_id )->post_title;
			$subject  = $_subject ? $_subject : $subject;
			$subject  = Utils::replace_shortcode( $subject, [], $object );
			$subject  = preg_replace( $this->plain_search, $this->plain_replace, wp_strip_all_tags( $subject ) );
			$subject  = htmlspecialchars_decode( do_shortcode( $subject ) );
		}

		return $subject;
	}

	public function replace_template_path( $located, $template_name, $args, $template_path, $default_path ) {
		if ( ! $this->template_id ) {
			return $located;
		}

		if ( $template_name == 'emails/email-addresses.php' ) {
			return VIWEC_TEMPLATES . 'empty-file.php';
		}

		if ( $template_name == 'emails/email-styles.php' ) {
			return VIWEC_TEMPLATES . 'email-style.php';
		}

		if ( ! empty( $args['email']->id ) && in_array( $args['email']->id, $this->unique ) ) {
			return $located;
		}

		if ( ! isset( $args['email'] ) && isset( $args['order'] ) && isset( $args['email_heading'] ) ) {
			$WC_mailer = WC()->mailer();
			if ( isset( $WC_mailer->emails ) ) {
				foreach ( $WC_mailer->emails as $mailer ) {
					if ( ! empty( $mailer->object ) && $args['email_heading'] == $mailer->heading ) {
						$args['email'] = $mailer;
						break;
					} else if ( isset( $mailer->settings ) && ! empty( $mailer->settings ) ) {
						$settings = $mailer->settings;
						if ( isset( $settings['heading'] ) && $args['email_heading'] == $settings['heading'] ) {
							$args['email'] = $mailer;
							break;
						}
					}
				}
			}
		}

		if ( isset( $args['email'] ) && ! empty( $args['email']->id ) ) {
			if ( $args['plain_text'] ) {
				return $located;
			}
			if ( $this->template_id ) {
				$this->unique[] = $args['email']->id;
				$located        = VIWEC_TEMPLATES . 'email-template.php';
			}
		}

		return $located;
	}

	public function load_template( $args ) {
		if ( ! $this->template_id ) {
			return;
		}

		$email_render = Email_Render::init( [ 'template_id' => $this->template_id ] );
		$email_render->set_object( $args['email'] );
		$this->user                  = $args['email'];
		$email_render->template_args = $args;

		$data = get_post_meta( $this->template_id, 'viwec_email_structure', true );
		$data = json_decode( html_entity_decode( $data ), true );

		$email_render->render( $data );
	}

	public function get_template_id_has_order( $type, \WC_Order $order ) {
		$country_code = $order->get_billing_country();
		$lang_code    = '';
		if ( function_exists( 'pll_get_post_language' ) ) {
			$lang_code = pll_get_post_language( $order->get_id() );
		} else if ( function_exists( 'icl_get_languages' ) ) {
			$lang_code = function_exists( 'icl_get_languages' ) ? get_post_meta( $order->get_id(), 'wpml_language', true ) : '';
		}
		$line_items     = $order->get_items( 'line_item' );
		$order_price    = $order->get_total();
		$order_currency = $order->get_currency();
		$order_payment  = $order->get_payment_method();

		$bought_ids = Utils::get_bought_ids( $line_items );
		$categories = Utils::get_categories_from_bought_id( $bought_ids );
		if ( $type == 'customer_invoice' && $order->get_status() == 'pending' ) {
			$type = 'customer_invoice_pending';
		}

		$args = [
			'posts_per_page' => - 1,
			'post_type'      => 'viwec_template',
			'orderby'        => 'menu_order',
			'post_status'    => 'publish',
			'meta_key'       => 'viwec_settings_type',
			'meta_value'     => $type,
		];

		$posts = get_posts( $args );

		$filter_ids = [];

		foreach ( $posts as $post ) {
			$rules           = get_post_meta( $post->ID, 'viwec_setting_rules', true );
			$rule_countries  = $rules['countries'] ?? [];
			$rule_categories = $rules['categories'] ?? [];
			$rule_products   = $rules['products'] ?? [];
			$rule_languages  = $rules['languages'] ?? [];
			$rule_price_type = $rules['price_type'] ?? 'total';
			$rule_payment    = $rules['payment'] ?? [];

			if ( $rule_price_type == 'subtotal' ) {
				$order_price = $order->get_subtotal();
			}
			$min_price = $rules['min_price'] ?? '';
			$max_price = $rules['max_price'] ?? '';
			/*Compatible CURCY*/
			if ( class_exists( '\WOOMULTI_CURRENCY_Data' ) ) {
				$setting_curcy             = \WOOMULTI_CURRENCY_Data::get_ins();
				$get_list_currency         = $setting_curcy->get_list_currencies();
				$curcy_order_currency_data = $get_list_currency[ $order_currency ] ?? [];
				if ( isset( $curcy_order_currency_data['hide'] ) && ! $curcy_order_currency_data['hide'] ) {
					$order_price = round( floatval( $order_price ) / $curcy_order_currency_data['rate'], 2 );
				}
			} else if ( class_exists( '\WOOMULTI_CURRENCY_F_Data' ) ) {
				$setting_curcy             = \WOOMULTI_CURRENCY_F_Data::get_ins();
				$get_list_currency         = $setting_curcy->get_list_currencies();
				$curcy_order_currency_data = $get_list_currency[ $order_currency ] ?? [];
				if ( isset( $curcy_order_currency_data['hide'] ) && ! $curcy_order_currency_data['hide'] ) {
					$order_price = round( floatval( $order_price ) / $curcy_order_currency_data['rate'], 2 );
				}
			}
			$rule_categories_by_language = [];

			if ( function_exists( 'icl_get_languages' ) || function_exists( 'pll_get_post_language' ) ) {

				if ( ! empty( $rule_languages ) && is_array( $rule_languages ) ) {
					if ( ! in_array( $lang_code, $rule_languages ) ) {
						continue;
					}
				}
				if ( ! empty( $rule_categories ) ) {
					foreach ( $rule_categories as $item_id ) {
						$termID = apply_filters( 'wpml_object_id', $item_id, 'product_cat', true, $lang_code );
						if ( $termID ) {
							$rule_categories_by_language[] = $termID;
						}
					}
				}
				if ( ! empty( $rule_categories_by_language ) ) {
					$rule_categories = $rule_categories_by_language;
				}
			}

			if ( ! empty( $rule_countries ) && is_array( $rule_countries ) ) {
				if ( ! in_array( $country_code, $rule_countries ) ) {
					continue;
				}
			}
			/*Rule for product and product categories*/
			if ( ! empty( $rule_categories ) && ! empty( $rule_products ) ) {

				if (
					( ! empty( $rule_categories ) && ! count( array_intersect( $categories, (array) $rule_categories ) ) ) &&
					( ! empty( $rule_products ) && ! count( array_intersect( $bought_ids, (array) $rule_products ) ) )
				) {
					continue;
				}

			} else if ( ! empty( $rule_categories ) || ! empty( $rule_products ) ) {
				if ( ! empty( $rule_categories ) ) {
					if ( ! empty( $rule_categories ) && count( array_intersect( $categories, (array) $rule_categories ) ) <= 0 ) {
						continue;
					}
				}
				if ( ! empty( $rule_products ) ) {
					if ( ! empty( $rule_products ) && count( array_intersect( $bought_ids, (array) $rule_products ) ) <= 0 ) {
						continue;
					}
				}
			}
			/*Rule for payment method*/
			if ( ! empty( $rule_payment ) && is_array( $rule_payment ) ) {
				if ( ! in_array( $order_payment, $rule_payment ) ) {
					continue;
				}
			}

			/*Rule compatible with 3rd*/
			$rule_3rd = apply_filters( 'viwec_add_rule_template_3rd', $rules, $order );

			if ( ! $rule_3rd ) {
				continue;
			}

			/*Rule for price*/
			if ( empty( $max_price ) && ( $max_price === '' ) ) {
				if ( $order_price >= (float) $min_price ) {
					$filter_ids[] = $post->ID;
				}
			} else {
				if ( ( $order_price >= (float) $min_price ) && ( $order_price <= (float) $max_price ) ) {
					$filter_ids[] = $post->ID;

				}
			}

		}

		return apply_filters( 'viwec_find_email_template_id_with_rule_order', current( $filter_ids ), $posts, $country_code, $categories, $order_price );
	}

	public function get_template_id_no_order( $type, $object = [] ) {

		if ( ! empty( $_POST['billing_country'] ) ) {
			$country_code = sanitize_text_field( $_POST['billing_country'] );
		} else {
			$locate       = \WC_Geolocation::geolocate_ip();
			$country_code = $locate['country'];
		}

		$lang_code = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '';
		if ( class_exists( 'TRP_Translate_Press' ) ) {
			$lang_code_str = get_locale();
			$lang_code_arr = explode( "_", $lang_code_str );
			$lang_code     = $lang_code_arr[0];
		}
		$temp_id = '';

		$args = [
			'posts_per_page' => - 1,
			'post_type'      => 'viwec_template',
			'post_status'    => 'publish',
			'meta_key'       => 'viwec_settings_type',
			'meta_value'     => $type,
		];

		$posts = get_posts( $args );

		if ( $country_code ) {
			foreach ( $posts as $post ) {
				$rules          = get_post_meta( $post->ID, 'viwec_setting_rules', true );
				$rule_countries = $rules['countries'] ?? [];
				$rule_languages = $rules['languages'] ?? [];

				if ( function_exists( 'icl_get_languages' ) ) {
					if ( $lang_code !== 'all' && ! empty( $rule_languages ) && is_array( $rule_languages ) ) {
						if ( ! in_array( $lang_code, $rule_languages ) ) {
							continue;
						}
					}
				}

				if ( ! empty( $rule_countries ) && is_array( $rule_countries ) ) {
					if ( ! in_array( $country_code, $rule_countries ) ) {
						continue;
					}
				}
				/*Rule compatible with 3rd*/
				$rule_3rd = apply_filters( 'viwec_add_rule_template_3rd', $rules, $object );
				if ( ! $rule_3rd ) {
					continue;
				}
				$temp_id = $post->ID;
				break;
			}
		} else {
			$post    = current( $posts );
			$temp_id = $post->ID;
		}

		return $temp_id;
	}

	public function get_default_template() {
		$id   = '';
		$args = [
			'posts_per_page' => - 1,
			'post_type'      => 'viwec_template',
			'post_status'    => 'publish',
			'meta_key'       => 'viwec_settings_type',
			'meta_value'     => 'default',
		];

		$posts = get_posts( $args );
		if ( ! empty( $posts ) ) {
			$ids = wp_list_pluck( $posts, 'ID' );
			$id  = current( $ids );
		}

		return $id;
	}

	public function replace_wp_new_user_email( $wp_new_user_notification_email, $user, $blogname ) {
		$this->template_id = $this->get_template_id_no_order( 'customer_new_account', $user );

		if ( $this->template_id ) {

			$register_data = [];
			if ( isset( $_POST['action'] ) && $_POST['action'] == 'uael_register_user' ) {
				$data = wc_clean( $_POST['data'] );
			} else {
				$data = wc_clean( $_POST );
			}

			if ( isset( $data['user_name'] ) ) {
				$register_data['user_name'] = wp_unslash( sanitize_text_field( $data['user_name'] ) );
			}

			if ( ! empty( $data['first_name'] ) ) {
				$register_data['first_name'] = sanitize_text_field( $data['first_name'] );
			}

			if ( ! empty( $data['last_name'] ) ) {
				$register_data['last_name'] = sanitize_text_field( $data['last_name'] );
			}

			if ( ! empty( $data['password'] ) ) {
				$register_data['password'] = wp_unslash( sanitize_text_field( $data['password'] ) );
			} else {
				$key                       = get_password_reset_key( $user );
				$register_data['password'] = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' );
			}

			$user->register_data = $register_data;

			$subject = get_post( $this->template_id )->post_title;
			if ( $subject ) {
				$wp_new_user_notification_email['subject'] = Utils::replace_shortcode( $subject, '', $user );
			}

			$email_render = Email_Render::init();
			$email_render->set_user( $user );

			$data = get_post_meta( $this->template_id, 'viwec_email_structure', true );
			$data = json_decode( html_entity_decode( $data ), true );
			ob_start();
			$email_render->render( $data );
			$email_body = ob_get_clean();

			if ( $email_body ) {
				$wp_new_user_notification_email['message'] = $email_body;
			}

			$wp_new_user_notification_email['headers'] = [ "Content-Type: text/html" ];
		}

		return $wp_new_user_notification_email;
	}

	public function replace_wp_reset_password_title( $title, $user_login, $user_data ) {
		$this->template_id = $this->get_template_id_no_order( 'customer_reset_password', $user_data );
		if ( $this->template_id ) {
			$subject = get_post( $this->template_id )->post_title;
			if ( $subject ) {
				$shortcodes                 = Utils::default_shortcode_for_replace();
				$shortcodes['{user_login}'] = $user_login;

				$title = str_replace( array_keys( $shortcodes ), array_values( $shortcodes ), $subject );
			}
		}

		return $title;
	}

	public function replace_wp_reset_password_email( $message, $key, $user_login, $user_data ) {
		if ( $this->template_id ) {
			$locale    = get_user_locale( $user_data );
			$reset_url = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . '&wp_lang=' . $locale;

			$register_data             = [];
			$register_data['password'] = $reset_url;

			$user_data->register_data = $register_data;

			$email_render = Email_Render::init();
			$email_render->set_user( $user_data );

			$data = get_post_meta( $this->template_id, 'viwec_email_structure', true );
			$data = json_decode( html_entity_decode( $data ), true );
			ob_start();
			$email_render->render( $data );
			$email_body = ob_get_clean();

			if ( $email_body ) {
				$message = $email_body;
				add_filter( 'wp_mail_content_type', [ $this, 'replace_wp_reset_password_email_type' ] );
			}
		}


		return $message;
	}

	public function replace_wp_reset_password_email_type() {
		return 'text/html';
	}

	public function remove_style( $style ) {
		return $this->clear_css ? '#viwec{}' : $style;
	}

	public function custom_css( $style ) {
		if ( $this->use_default_temp || $this->template_id ) {
			$id    = $this->template_id ? $this->template_id : $this->use_default_temp;
			$style .= get_post_meta( $id, 'viwec_custom_css', true );
		}

		return $style;
	}

	public function use_default_template_email( $args, $class_email ) {

		if ( $this->use_default_temp && ! $this->template_id ) {

			add_filter( 'woocommerce_order_item_thumbnail', [ $this, 'item_thumbnail_start' ], PHP_INT_MAX );
			add_action( 'woocommerce_order_item_meta_end', [ $this, 'item_thumbnail_end' ], PHP_INT_MAX );

			$email_render = Email_Render::init();

			if ( ! $email_render->check_rendered ) {
				$email_render->set_object( $class_email );
				$this->user                          = $class_email;
				$email_render->class_email           = $this->class_email;
				$email_render->recover_heading       = $this->heading;
				$email_render->other_message_content = $args[2];
				$email_render->use_default_template  = true;
				$data                                = get_post_meta( $this->use_default_temp, 'viwec_email_structure', true );
				$data                                = json_decode( html_entity_decode( $data ), true );

				ob_start();
				$email_render->render( $data );
				$message = ob_get_clean();
				$message = $class_email->style_inline( $message );
				$args[2] = $message;

				remove_filter( 'woocommerce_order_item_thumbnail', [ $this, 'item_thumbnail_start' ] );
				remove_action( 'woocommerce_order_item_name', [ $this, 'item_thumbnail_end' ] );
			}
		}

		return $args;
	}

	public function item_thumbnail_start( $image ) {

		$this->fix_default_thumbnail = true;

		return '<table><tr><td valign="middle" style="vertical-align: middle;border: none;"> ' . $image . '</td><td valign="middle" style="vertical-align: middle;border: none;">';
	}

	public function item_thumbnail_end() {
		if ( $this->fix_default_thumbnail ) {
			?>
            </td>
            </tr>
            </table>
			<?php
			$this->fix_default_thumbnail = false;
		}
	}


	public function add_attachment_file( $args ) {
		$id = $this->template_id ? $this->template_id : $this->use_default_temp;
		if ( $id && isset( $args[4] ) ) {
			$files = get_post_meta( $id, 'viwec_attachments', true );
			if ( ! empty( $files ) && is_array( $files ) ) {
				foreach ( $files as $file_id ) {
					$args[4][] = get_attached_file( $file_id );
				}
			}
		}

		return $args;
	}

	public function reset_template_id( $wp_mail ) {
		$this->use_default_temp       = '';
		$this->template_id            = '';
		$this->disable_email_template = '';
		$this->unique                 = [];
		$email_render                 = Email_Render::init();
		$email_render->check_rendered = false;

		return $wp_mail;
	}

	public function send_email_via_wc_mailer( $heading ) {
		if ( $this->disable_email_template ) {
			return;
		}

		$this->heading          = $heading;
		$this->use_default_temp = $this->get_default_template();
		$this->remove_header_footer_hook( $this->use_default_temp );
		add_action( 'woocommerce_email_header', array( $this, 'send_email_via_wc_mailer' ), 0 );
	}

	public function add_padding_for_addition_content( $value, $_this, $_value, $key ) {
		if ( $this->use_default_temp ) {
			$value = $key == 'additional_content' ? "<div style='padding-top: 20px;'>{$value}</div>" : $value;
		}

		return $value;
	}

	public function minify_email_content( $args ) {
		$message = $args[2];
		$args[2] = Utils::minify_html( $message );

		return $args;
	}

}

