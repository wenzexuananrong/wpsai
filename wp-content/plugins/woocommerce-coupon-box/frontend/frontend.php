<?php

/**
 * Show Coupon box
 * Class VI_WOOCOMMERCE_COUPON_BOX_Frontend_Coupon
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_COUPON_BOX_Frontend_Frontend {
	protected static $settings;
	protected $existing_coupons;
	protected $characters_array;
	protected $language;
	protected $language_ajax;
	protected $new_user = false;

	public function __construct() {
		self::$settings      = new VI_WOOCOMMERCE_COUPON_BOX_DATA();
		$this->language      = '';
		$this->language_ajax = '';
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		/*Ajax add email*/
		if ( self::$settings->get_params( 'ajax_endpoint' ) === 'ajax' ) {
			add_action( 'wp_ajax_nopriv_wcb_email', array( $this, 'wcb_email' ) );
			add_action( 'wp_ajax_wcb_email', array( $this, 'wcb_email' ) );
		} else {
			add_action( 'rest_api_init', array( $this, 'register_api' ) );
		}
		add_action( 'wcb_schedule_add_recipient_to_list', array( $this, 'add_recipient_to_list' ), 10, 2 );

		add_shortcode( 'wcb_open_popup', array( $this, 'button_open_popup' ) );
	}

	/**
	 * Register API json
	 */
	public function register_api() {
		/*Auto update plugins*/
		register_rest_route(
			'woocommerce_coupon_box', '/subscribe', array(
				'methods'  => 'POST',
				'callback' => array( $this, 'wcb_email' ),
			)
		);
	}

	public function add_recipient_to_list( $email, $list_id ) {
		$sendgrid = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Sendgrid();
		$sendgrid->add_recipient_to_list( $email, $list_id );
	}

	public static function validate_recaptcha() {
		$g_validate_response = isset( $_POST['g_validate_response'] ) ? sanitize_text_field( $_POST['g_validate_response'] ) : '';
		if ( ! $g_validate_response && self::$settings->get_params( 'wcb_recaptcha' ) ) {
			$msg            = array(
				'status'              => '',
				'message'             => '',
				'warning'             => '',
				'g_validate_response' => '1',
			);
			$msg['status']  = 'invalid';
			$msg['warning'] = esc_html__( '*No g_validate_response', 'woocommerce-coupon-box' );
			wp_send_json( $msg );
		}
		if ( $g_validate_response && self::$settings->get_params( 'wcb_recaptcha' ) ) {
			$msg = array(
				'status'              => '',
				'message'             => '',
				'warning'             => '',
				'g_validate_response' => '1',
			);
			if ( ! $g_validate_response ) {
				$msg['status']  = 'invalid';
				$msg['warning'] = esc_html__( '*Invalid google reCAPTCHA!', 'woocommerce-coupon-box' );
				wp_send_json( $msg );
			}
			$wcb_recaptcha_secret_key = self::$settings->get_params( 'wcb_recaptcha_secret_key' );
			if ( ! $wcb_recaptcha_secret_key ) {
				$msg['status']  = 'invalid';
				$msg['warning'] = esc_html__( '*Invalid google reCAPTCHA secret key!', 'woocommerce-coupon-box' );
				wp_send_json( $msg );
			}
			$url  = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $wcb_recaptcha_secret_key . '&response=' . $g_validate_response;
			$curl = curl_init();
			curl_setopt_array( $curl, array(
				CURLOPT_URL            => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => "",
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => "POST",
				CURLOPT_POSTFIELDS     => '{}',
				CURLOPT_HTTPHEADER     => array(
					"content-type: application/json"
				),
			) );

			$response = curl_exec( $curl );
			$err      = curl_error( $curl );
			curl_close( $curl );
			if ( $err ) {
				$msg['status']  = 'invalid';
				$msg['warning'] = "*reCAPTCHA cURL Error #:" . $err;
				wp_send_json( $msg );
			} else {
				$data = json_decode( $response, true );
				if ( self::$settings->get_params( 'wcb_recaptcha_version' ) == 2 ) {
					if ( ! $data['success'] ) {
						$msg['status']  = 'invalid';
						$msg['warning'] = esc_html__( '*reCAPTCHA verification failed', 'woocommerce-coupon-box' );
						$msg['message'] = $data;
						wp_send_json( $msg );
					}
				} else {
					$g_score = isset( $data['score'] ) ? $data['score'] : 0;
					if ( $g_score < 0.5 ) {
						$msg['status']  = 'invalid';
						$msg['warning'] = esc_html__( '*reCAPTCHA score ' . $g_score . ' lower than threshold 0.5 ', 'woocommerce-coupon-box' );
						$msg['message'] = $data;
						wp_send_json( $msg );
					}
				}
			}
		}
	}

	/**
	 * Process ajax add email
	 */
	public function wcb_email() {
		if ( self::$settings->get_params( 'ajax_endpoint' ) === 'rest_api' ) {
			header( "Access-Control-Allow-Origin: *" );
			header( 'Access-Control-Allow-Methods: POST' );
		}
		self::validate_recaptcha();
		$language_ajax                   = isset( $_POST['language_ajax'] ) ? sanitize_text_field( $_POST['language_ajax'] ) : '';
		$wcb_enable_mailchimp            = self::$settings->get_params( 'wcb_enable_mailchimp' );
		$wcb_email_campaign              = self::$settings->get_params( 'wcb_email_campaign' );
		$wcb_footer_text_after_subscribe = self::$settings->get_params( 'wcb_footer_text_after_subscribe', $language_ajax );
		$email                           = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_EMAIL );
		$ip                              = \WC_Geolocation::get_ip_address();
		$msg                             = array(
			'status'           => '',
			'message'          => '',
			'warning'          => '',
			'code'             => '',
			'thankyou'         => '',
			'wcb_current_time' => current_time( 'timestamp', true ),
		);
		$meta                            = array(
			'coupon'              => '',
			'campaign'            => '',
			'mailchimp'           => '',
			'mailchimp_list'      => '',
			'activecampaign'      => '',
			'activecampaign_list' => '',
			'sendgrid'            => '',
			'sendgrid_list'       => '',
			'name'                => isset( $_POST['wcb_input_name'] ) ? sanitize_text_field( $_POST['wcb_input_name'] ) : '',
			'lname'               => isset( $_POST['wcb_input_lname'] ) ? sanitize_text_field( $_POST['wcb_input_lname'] ) : '',
			'mobile'              => isset( $_POST['wcb_input_mobile'] ) ? sanitize_text_field( $_POST['wcb_input_mobile'] ) : '',
			'birthday'            => isset( $_POST['wcb_input_birthday'] ) ? sanitize_text_field( $_POST['wcb_input_birthday'] ) : '',
			'gender'              => isset( $_POST['wcb_input_gender'] ) ? sanitize_text_field( $_POST['wcb_input_gender'] ) : '',
			'additional'          => isset( $_POST['wcb_input_additional'] ) ? sanitize_text_field( $_POST['wcb_input_additional'] ) : '',
			'ip_address'          => sanitize_text_field( $ip ),
		);
		ob_start();
		?>
        <div class="wcb-footer-text-after-subscribe"><?php _e( $wcb_footer_text_after_subscribe ) ?></div>
		<?php
		$msg['thankyou'] = ob_get_clean();

		if ( is_email( $email ) ) {
//		    check if email already subscribed
			$this->save_email_to_session( $email );

			$emails_args = array(
				'post_type'      => 'wcb',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'title'          => $email,
				'post_status'    => array( // (string | array) - use post status. Retrieves posts by Post Status, default value i'publish'.
					'publish', // - a published post or page.
					'pending', // - post is pending review.
					'draft',  // - a post in draft status.
					'auto-draft', // - a newly created post, with no content.
					'future', // - a post to publish in the future.
					'private', // - not visible to users who are not logged in.
					'inherit', // - a revision. see get_children.
					'trash', // - post is in trashbin (available with Version 2.9).
				)
			);
			$the_query   = new WP_Query( $emails_args );
			if ( $the_query->have_posts() ) {
				$msg['status']  = 'existed';
				$msg['warning'] = esc_html__( '*This email already subscribed!', 'woocommerce-coupon-box' );
				wp_send_json( $msg );
			}
			wp_reset_postdata();

			// Create post object
			if ( $wcb_enable_mailchimp && class_exists( 'VI_WOOCOMMERCE_COUPON_BOX_Admin_Mailchimp' ) ) {
				/*Add mailchimp*/
				$mailchimp      = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Mailchimp();
				$mailchimp_list = self::$settings->get_params( 'wcb_mlists', $language_ajax );
				if ( $mailchimp_list != 'non' ) {
					$data = apply_filters( 'wcb_data_to_mailchimp', array(
						'FNAME' => $meta['name'],
						'LNAME' => $meta['lname'],
						'PHONE' => $meta['mobile']
					), $email );
					$mailchimp->add_email( $email, $mailchimp_list, $data );
					$meta['mailchimp'] = $mailchimp_list;
				}
			}

			if ( self::$settings->get_params( 'wcb_enable_active_campaign' ) && class_exists( 'VI_WOOCOMMERCE_COUPON_BOXP_Admin_Active_Campaign' ) ) {
				$active_campaign      = new VI_WOOCOMMERCE_COUPON_BOXP_Admin_Active_Campaign();
				$active_campaign_list = self::$settings->get_params( 'wcb_active_campaign_list' );
				if ( $active_campaign_list ) {
					$active_campaign->contact_add( $email, $active_campaign_list, $meta['name'], $meta['lname'], $meta['mobile'] );
					$meta['activecampaign'] = $active_campaign_list;
				} else {
					$active_campaign->contact_add( $email, '', $meta['name'], '', $meta['mobile'] );
				}
			}

			if ( self::$settings->get_params( 'wcb_enable_sendgrid' ) && class_exists( 'VI_WOOCOMMERCE_COUPON_BOX_Admin_Sendgrid' ) ) {
				$sendgrid = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Sendgrid();
				$sendgrid->add_recipient( $email, $meta['name'], $meta['lname'] );
				$sendgrid_list = self::$settings->get_params( 'wcb_sendgrid_list' );
				if ( $sendgrid_list && $sendgrid_list != 'none' ) {
					$meta['sendgrid'] = $sendgrid_list;
					$time             = time() + 60;
					wp_schedule_single_event( $time, 'wcb_schedule_add_recipient_to_list', [ $email, $sendgrid_list ] );
				}
			}

			if ( self::$settings->get_params( 'wcb_enable_hubspot' ) && class_exists( 'VI_WOOCOMMERCE_COUPON_BOX_Admin_Hubspot' ) ) {
				$hubspot = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Hubspot();
				$hubspot->add_recipient( $email, $meta['name'], $meta['lname'], $meta['mobile'] );
			}


			if ( self::$settings->get_params( 'wcb_enable_mailpoet' ) && class_exists( \MailPoet\API\API::class ) ) {
				$mailpoet_api           = \MailPoet\API\API::MP( 'v1' );
				$mailpoet_selected_list = (array) self::$settings->get_params( 'wcb_mailpoet_list' );
				try {
					$mailpoet_api->addSubscriber( [
						'email'  => $email,
						'status' => 'subscribed'
					], $mailpoet_selected_list );
				} catch ( \MailPoet\API\MP\v1\APIException $e ) {
				}
			}

			/*Mailster*/
			if ( self::$settings->get_params( 'wcb_enable_mailster' ) && function_exists( 'mailster' ) ) {
				// define to overwrite existing users
				$overwrite = true;

				// add with double opt in
				$double_opt_in = true;

				// prepare the userdata from a $_POST request. only the email is required
				$user_mailster_data = array(
					'email'     => $email,
					'firstname' => $meta['name'],
					'lastname'  => $meta['lname'],
					'status'    => 1,
				);

				// add a new subscriber and $overwrite it if exists
				$subscriber_mailster_id = mailster( 'subscribers' )->add( $user_mailster_data, $overwrite );

				// if result isn't a WP_error assign the lists
				if ( ! is_wp_error( $subscriber_mailster_id ) ) {

					// your list ids
					$list_mailster_ids = self::$settings->get_params( 'wcb_mailster_list' ) ?? [];
					if ( ! empty( $list_mailster_ids ) ) {
						mailster( 'subscribers' )->assign_lists( $subscriber_mailster_id, $list_mailster_ids );
					}

				} else {
					// actions if adding fails. $subscriber_id is a WP_Error object
				}
			}

			if ( self::$settings->get_params( 'wcb_enable_klaviyo' ) && class_exists( 'VI_WOOCOMMERCE_COUPON_BOX_Admin_Klaviyo' ) ) {
				$klaviyo      = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Klaviyo();
				$klaviyo_list = self::$settings->get_params( 'wcb_klaviyo_list' );
				$klaviyo->add_recipient( $email, $klaviyo_list, $meta['name'], $meta['lname'] );
			}

			if ( self::$settings->get_params( 'wcb_enable_sendinblue' ) && class_exists( 'VI_WOOCOMMERCE_COUPON_BOX_Admin_Sendinblue' ) ) {
				$sendinblue      = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Sendinblue();
				$sendinblue_list = self::$settings->get_params( 'wcb_sendinblue_list' );
				$sendinblue->add_recipient( $email, $sendinblue_list, $meta['name'], $meta['lname'] );
			}

			if ( self::$settings->get_params( 'wcb_enable_getresponse' ) && class_exists( 'VI_WOOCOMMERCE_COUPON_BOX_Admin_Getresponse' ) ) {
				$getresponse      = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Getresponse();
				$getresponse_list = self::$settings->get_params( 'wcb_getresponse_list' );
				$getresponse->add_recipient( $email, $getresponse_list, $meta['name'], $meta['lname'], $ip );
			}

			// Insert the post into the database
			$my_post = array(
				'post_title'  => $email,
				'post_type'   => 'wcb',
				'post_status' => 'publish',
			);

			$post_id = wp_insert_post( $my_post );

			$this->new_user = $this->register_account( $email );

			$uncategory = get_term_by( 'slug', 'uncategorized', 'wcb_email_campaign' );

			if ( $wcb_email_campaign ) {
				$my_post['tax_input'] = array(
					'wcb_email_campaign' => $wcb_email_campaign
				);
				$meta['campaign']     = $wcb_email_campaign;
				wp_set_post_terms( $post_id, array( $wcb_email_campaign ), 'wcb_email_campaign' );
			} elseif ( $uncategory ) {
				$term_id              = $uncategory->term_id;
				$my_post['tax_input'] = array(
					'wcb_email_campaign' => $term_id
				);
				$meta['campaign']     = $term_id;
				wp_set_post_terms( $post_id, array( $term_id ), 'wcb_email_campaign' );
			}
			$code           = $this->create_coupon( $email );
			$meta['coupon'] = $code;
			update_post_meta( $post_id, 'woo_coupon_box_meta', $meta );
			$msg['message'] = self::$settings->get_params( 'wcb_message_after_subscribe', $language_ajax );

			$coupon_select = self::$settings->get_params( 'wcb_coupon_select' );
			$show_coupon   = self::$settings->get_params( 'wcb_show_coupon' );
			$customer_name = trim( $meta['name'] . ' ' . $meta['lname'] );

			if ( $coupon_select == 'non' ) {
				/*Only subscribe*/
				$this->send_email( $email, $customer_name, '', '', '', $language_ajax );
			} elseif ( in_array( $coupon_select, array( 'existing', 'unique' ) ) ) {
				/*Send a WooCommerce coupon*/
				if ( $code ) {
					$coupon = new WC_Coupon( $code );
					if ( $coupon->get_discount_type() == 'percent' ) {
						$coupon_value = $coupon->get_amount() . '%';
					} else {
						$coupon_value = $this->wc_price( $coupon->get_amount() );
					}
					$this->send_email( $email, $customer_name, strtoupper( trim( $coupon->get_code() ) ), $coupon->get_date_expires(), $coupon_value, $language_ajax );

					if ( $show_coupon ) {
						ob_start();
						?>
                        <div class="wcb-coupon-treasure-container">
                            <span><input type="text" readonly="readonly"
                                         value="<?php echo strtoupper( trim( $coupon->get_code() ) ); ?>"
                                         class="wcb-coupon-treasure"/></span>
                        </div>
                        <span class="wcb-guide">
						<?php esc_html_e( 'Enter this promo code at checkout page.', 'woocommerce-coupon-box' ) ?>
					</span>
						<?php
						$msg['code'] = ob_get_clean();
					}
				}

			} else {
				/*Send a custom coupon code*/
				$this->send_email( $email, $customer_name, $code, '', '', $language_ajax );
				if ( $show_coupon ) {
					ob_start();
					?>
                    <div class="wcb-coupon-treasure-container">
                        <span><input type="text" readonly="readonly" value="<?php echo $code; ?>"
                                     class="wcb-coupon-treasure"/></span>
                    </div>
                    <span class="wcb-guide">
						<?php esc_html_e( 'Enter this promo code at checkout page.', 'woocommerce-coupon-box' ) ?>
					</span>
					<?php
					$msg['code'] = ob_get_clean();
				}

			}
			$msg['status'] = 'subscribed';
			wp_send_json( $msg );
			die;
		} else {
			$msg['status']  = 'invalid';
			$msg['warning'] = esc_html__( '*Invalid email!', 'woocommerce-coupon-box' );
			wp_send_json( $msg );
			die;
		}
	}

	public function register_account( $email ) {
		$register_account = self::$settings->get_params( 'wcb_register_account' );

		if ( ! $register_account ) {
			return false;
		}

		$register_account_checkbox = self::$settings->get_params( 'wcb_register_account_checkbox' );
		$accept_register_account   = isset( $_POST['accept_register_account'] ) ? sanitize_text_field( $_POST['accept_register_account'] ) : '';

		if ( $register_account_checkbox && ! $accept_register_account ) {
			return false;
		}

		return $this->create_new_customer( $email );

	}

	public function create_new_customer( $email ) {
		if ( empty( $email ) || ! is_email( $email ) ) {
			return false;
		}

		if ( email_exists( $email ) ) {
			return false;
		}

		$username = sanitize_user( wc_create_new_customer_username( $email ) );

		if ( empty( $username ) || ! validate_username( $username ) ) {
			return false;
		}

		if ( username_exists( $username ) ) {
			return false;
		}

		$password = wp_generate_password();

		$new_customer_data = array(
			'user_login' => $username,
			'user_pass'  => $password,
			'user_email' => $email,
			'role'       => 'customer',
		);
		$customer_id       = wp_insert_user( $new_customer_data );

		if ( is_wp_error( $customer_id ) ) {
			return false;
		}

		return $new_customer_data;
	}


	public function save_email_to_session( $email ) {
		if ( WC()->session ) {
			if ( ! WC()->session->has_session() ) {
				WC()->session->set_customer_session_cookie( true );
				wc()->customer->set_billing_email( $email );
			}
		}
	}

	protected function rand() {
		if ( $this->characters_array === null ) {
			$this->characters_array = array_merge( range( 0, 9 ), range( 'a', 'z' ) );
		}
		$rand = rand( 0, count( $this->characters_array ) - 1 );

		return $this->characters_array[ $rand ];
	}

	protected function create_code() {
		wp_reset_postdata();

		$code = self::$settings->get_params( 'wcb_coupon_unique_prefix' );
		for ( $i = 0; $i < 6; $i ++ ) {
			$code .= $this->rand();
		}
		$args      = array(
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'title'          => $code
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			$code = $this->create_code();
		}
		wp_reset_postdata();

		return $code;
	}

	public function create_coupon( $email ) {
		$code = '';
		switch ( self::$settings->get_params( 'wcb_coupon_select' ) ) {
			case 'existing':
				$code = self::$settings->get_params( 'wcb_coupon' );
				if ( self::$settings->get_params( 'wcb_coupon_unique_email_restrictions' ) ) {
					$coupon = new WC_Coupon( $code );
					$er     = $coupon->get_email_restrictions();
					if ( ! in_array( $email, $er ) ) {
						$er[] = $email;
						$coupon->set_email_restrictions( $er );
						$coupon->save();
					}
				}
				break;
			case 'custom':
				$code = self::$settings->get_params( 'wcb_coupon_custom' );
				break;
			case 'unique':
				$code         = $this->create_code();
				$coupon       = new WC_Coupon( $code );
				$today        = strtotime( date( 'Ymd' ) );
				$date_expires = ( self::$settings->get_params( 'wcb_coupon_unique_date_expires' ) ) ? ( ( self::$settings->get_params( 'wcb_coupon_unique_date_expires' ) + 1 ) * 86400 + $today ) : '';
				$coupon->set_amount( self::$settings->get_params( 'wcb_coupon_unique_amount' ) );
				$coupon->set_date_expires( $date_expires );
				$coupon->set_discount_type( self::$settings->get_params( 'wcb_coupon_unique_discount_type' ) );
				$coupon->set_description( self::$settings->get_params( 'wcb_coupon_unique_description' ) );
				$coupon->set_individual_use( self::$settings->get_params( 'wcb_coupon_unique_individual_use' ) );
				if ( self::$settings->get_params( 'wcb_coupon_unique_product_ids' ) ) {
					$coupon->set_product_ids( self::$settings->get_params( 'wcb_coupon_unique_product_ids' ) );
				}
				if ( self::$settings->get_params( 'wcb_coupon_unique_excluded_product_ids' ) ) {
					$coupon->set_excluded_product_ids( self::$settings->get_params( 'wcb_coupon_unique_excluded_product_ids' ) );
				}
				$coupon->set_usage_limit( self::$settings->get_params( 'wcb_coupon_unique_usage_limit' ) );
				$coupon->set_usage_limit_per_user( self::$settings->get_params( 'wcb_coupon_unique_usage_limit_per_user' ) );
				$coupon->set_limit_usage_to_x_items( self::$settings->get_params( 'wcb_coupon_unique_limit_usage_to_x_items' ) );
				$coupon->set_free_shipping( self::$settings->get_params( 'wcb_coupon_unique_free_shipping' ) );
				$coupon->set_product_categories( self::$settings->get_params( 'wcb_coupon_unique_product_categories' ) );
				$coupon->set_excluded_product_categories( self::$settings->get_params( 'wcb_coupon_unique_excluded_product_categories' ) );
				$coupon->set_exclude_sale_items( self::$settings->get_params( 'wcb_coupon_unique_exclude_sale_items' ) );
				$coupon->set_minimum_amount( self::$settings->get_params( 'wcb_coupon_unique_minimum_amount' ) );
				$coupon->set_maximum_amount( self::$settings->get_params( 'wcb_coupon_unique_maximum_amount' ) );
				if ( self::$settings->get_params( 'wcb_coupon_unique_email_restrictions' ) ) {
					$coupon->set_email_restrictions( array( $email ) );
				}
				$coupon->save();
				$code = $coupon->get_code();
			default:
		}

		return $code;
	}

	public function send_email( $user_email, $customer_name, $coupon_code, $date_expires = '', $coupon_value = '', $language = '' ) {
		if ( ! self::$settings->get_params( 'wcb_email_enable' ) ) {
			return;
		}
		$date_format = get_option( 'date_format' );
		$headers     = "Content-Type: text/html\r\n";
		$mailer      = WC()->mailer();
		$email       = new WC_Email();

		$new_user_name = $new_user_pass = $new_user_email = '';
		if ( $this->new_user !== false && is_array( $this->new_user ) ) {
			$new_user_name  = $this->new_user['user_login'] ?? '';
			$new_user_pass  = $this->new_user['user_pass'] ?? '';
			$new_user_email = $this->new_user['user_email'] ?? '';
		}

		$email_template = self::$settings->get_params( 'email_template', $language );
		if ( $this->new_user ) {
			$r_email_template = self::$settings->get_params( 'email_template_for_register', $language );
			$email_template   = $r_email_template ? $r_email_template : $email_template;
		}

		if ( $email_template && self::$settings::email_template_customizer_active() ) {
			$viwec_email = new VIWEC_Render_Email_Template( array( 'template_id' => $email_template ) );
			$subject     = $viwec_email->get_subject();
			$subject     = str_replace( '{wcb_coupon_value}', $coupon_value, $subject );
			ob_start();
			$viwec_email->get_content();
			$content = ob_get_clean();
			$content = str_replace( array(
				'{wcb_coupon_value}',
				'{wcb_coupon_code}',
				'{wcb_customer_name}',
				'{wcb_date_expires}',
				'{wcb_last_valid_date}',
				'{wcb_site_title}',
				'{wcb_user_name}',
				'{wcb_password}',
				'{wcb_email}',
			),
				array(
					$coupon_value,
					strtoupper( $coupon_code ),
					$customer_name,
					empty( $date_expires ) ? esc_html__( 'never expires', 'woocommerce-coupon-box' ) : date( $date_format, strtotime( $date_expires ) ),
					empty( $date_expires ) ? '' : date( $date_format, strtotime( $date_expires ) - 86400 ),
					get_bloginfo( 'name' ),
					$new_user_name,
					$new_user_pass,
					$new_user_email,
				),
				$content );
		} else {
			$button_shop_now_url = self::$settings->get_params( 'wcb_button_shop_now_url', $language );
			$button_shop_now     = '<a href="' . ( $button_shop_now_url ? $button_shop_now_url : get_bloginfo( 'url' ) ) . '" target="_blank" style="line-height:normal;text-decoration:none;display: inline-flex;padding:10px 30px;margin:10px 0;font-size:' . self::$settings->get_params( 'wcb_button_shop_now_size' ) . 'px;color:' . self::$settings->get_params( 'wcb_button_shop_now_color' ) . ';background:' . self::$settings->get_params( 'wcb_button_shop_now_bg_color' ) . ';border-radius:' . self::$settings->get_params( 'wcb_button_shop_now_border_radius' ) . 'px">' . self::$settings->get_params( 'wcb_button_shop_now_title', $language ) . '</a>';

			$content = stripslashes( self::$settings->get_params( 'wcb_email_content', $language ) );

			if ( $this->new_user ) {
				$content .= self::$settings->get_params( 'wcb_register_email_content', $language );
			}

			$content = str_replace( '{coupon_value}', $coupon_value, $content );
			$content = str_replace( '{customer_name}', $customer_name, $content );
			$content = str_replace( '{coupon_code}', '<span style="font-size: x-large;">' . strtoupper( $coupon_code ) . '</span>', $content );
			$content = str_replace( '{date_expires}', empty( $date_expires ) ? esc_html__( 'never expires', 'woocommerce-coupon-box' ) : date( $date_format, strtotime( $date_expires ) ), $content );
			$content = str_replace( '{last_valid_date}', empty( $date_expires ) ? '' : date( $date_format, strtotime( $date_expires ) - 86400 ), $content );
			$content = str_replace( '{site_title}', get_bloginfo( 'name' ), $content );
			$content = str_replace( '{shop_now}', $button_shop_now, $content );
			$content = str_replace( [ '{wcb_user_name}', '{wcb_password}', '{wcb_email}' ], [
				$new_user_name,
				$new_user_pass,
				$new_user_email,
			], $content );

			$subject = stripslashes( self::$settings->get_params( 'wcb_email_subject', $language ) );
			$subject = str_replace( '{coupon_value}', $coupon_value, $subject );

			$heading = stripslashes( self::$settings->get_params( 'wcb_email_heading', $language ) );
			$heading = str_replace( '{coupon_value}', $coupon_value, $heading );

			$content = $email->style_inline( $mailer->wrap_message( $heading, $content ) );
		}
		$email->send( $user_email, $subject, $content, $headers, array() );
	}

	public function wc_price( $price, $args = array() ) {
		extract(
			apply_filters(
				'wc_price_args', wp_parse_args(
					$args, array(
						'ex_tax_label'       => false,
						'currency'           => get_option( 'woocommerce_currency' ),
						'decimal_separator'  => get_option( 'woocommerce_price_decimal_sep' ),
						'thousand_separator' => get_option( 'woocommerce_price_thousand_sep' ),
						'decimals'           => get_option( 'woocommerce_price_num_decimals', 2 ),
						'price_format'       => get_woocommerce_price_format(),
					)
				)
			)
		);
		$currency_pos = get_option( 'woocommerce_currency_pos' );
		$price_format = '%1$s%2$s';

		switch ( $currency_pos ) {
			case 'left' :
				$price_format = '%1$s%2$s';
				break;
			case 'right' :
				$price_format = '%2$s%1$s';
				break;
			case 'left_space' :
				$price_format = '%1$s&nbsp;%2$s';
				break;
			case 'right_space' :
				$price_format = '%2$s&nbsp;%1$s';
				break;
		}

		$negative = $price < 0;
		$price    = apply_filters( 'raw_woocommerce_price', floatval( $negative ? $price * - 1 : $price ) );
		$price    = apply_filters( 'formatted_woocommerce_price', number_format( $price, $decimals, $decimal_separator, $thousand_separator ), $price, $decimals, $decimal_separator, $thousand_separator );

		if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $decimals > 0 ) {
			$price = wc_trim_zeros( $price );
		}

		$formatted_price = ( $negative ? '-' : '' ) . sprintf( $price_format, $currency, $price );

		return $formatted_price;
	}

	public function woo_coupon_box_params_language( $language, $name ) {

		if ( in_array( $name, array(
			'wcb_title',
			'wcb_title_after_subscribing',
			'wcb_message',
			'wcb_message_after_subscribe',
			'wcb_button_text',
			'wcb_email_input_placeholder',
			'wcb_footer_text',
			'wcb_footer_text_after_subscribe',
			'wcb_gdpr_message',
			'wcb_follow_us',
			'wcb_email_subject',
			'wcb_email_heading',
			'wcb_email_content',
			'wcb_button_shop_now_title',
			'wcb_no_thank_button_title',
		) ) ) {
			$language = $this->language_ajax;
		}

		return $language;
	}

	public static function enqueue_recaptcha() {
		wp_enqueue_script( 'wcb-recaptcha' );
		wp_localize_script( 'wcb-recaptcha', 'wcb_recaptcha_params', array(
			'wcb_recaptcha_site_key'     => self::$settings->get_params( 'wcb_recaptcha_site_key' ),
			'wcb_recaptcha_version'      => self::$settings->get_params( 'wcb_recaptcha_version' ),
			'wcb_recaptcha_secret_theme' => self::$settings->get_params( 'wcb_recaptcha_secret_theme' ),
			'wcb_recaptcha'              => self::$settings->get_params( 'wcb_recaptcha' ),
			'wcb_layout'                 => self::$settings->get_params( 'wcb_layout' ),
		) );
	}

	/**
	 * Init Style and Script
	 */
	public function enqueue_scripts() {
		if ( is_admin() || is_customize_preview() ) {
			return;
		}
		if ( ! self::$settings->get_params( 'wcb_active' ) ) {
			return;
		}

		if ( self::$settings->get_params( 'wcb_multi_language' ) ) {
			if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
				$default_lang   = apply_filters( 'wpml_default_language', null );
				$this->language = apply_filters( 'wpml_current_language', null );
				if ( $this->language && $this->language !== $default_lang ) {
					$this->language_ajax = $this->language;
					add_filter( 'woo_coupon_box_params_language', array(
						$this,
						'woo_coupon_box_params_language'
					), 10, 2 );
				}
			} else if ( class_exists( 'Polylang' ) ) {
				$default_lang   = pll_default_language( 'slug' );
				$this->language = pll_current_language( 'slug' );
				if ( $this->language && $this->language !== $default_lang ) {
					$this->language_ajax = $this->language;
					add_filter( 'woo_coupon_box_params_language', array(
						$this,
						'woo_coupon_box_params_language'
					), 10, 2 );
				}
			}
		}
		$enable_recaptcha = false;
		if ( self::$settings->get_params( 'wcb_recaptcha' ) ) {
			if ( self::$settings->get_params( 'wcb_recaptcha_version' ) == 2 ) {
				$enable_recaptcha = true;
				?>
                <script src='https://www.google.com/recaptcha/api.js?hl=<?php echo esc_attr( $this->language ? $this->language : get_locale() ) ?>&render=explicit'
                        async
                        defer></script>
				<?php
			} elseif ( self::$settings->get_params( 'wcb_recaptcha_site_key' ) ) {
				$enable_recaptcha = true;
				?>
                <script src="https://www.google.com/recaptcha/api.js?hl=<?php echo esc_attr( $this->language ? $this->language : get_locale() ) ?>&render=<?php echo self::$settings->get_params( 'wcb_recaptcha_site_key' ); ?>"></script>
				<?php
			}
		}
		if ( $enable_recaptcha ) {
			wp_register_script( 'wcb-recaptcha', VI_WOOCOMMERCE_COUPON_BOX_JS . 'wcb-recaptcha.js', array( 'jquery' ), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
		}
		if ( self::$settings->get_params( 'wcb_disable_login' ) && is_user_logged_in() ) {
			return;
		}
		if ( isset( $_COOKIE['woo_coupon_box'] ) ) {
			$cookies = explode( ':', $_COOKIE['woo_coupon_box'] );
			if ( ! isset( $cookies[0] ) || ! in_array( $cookies[0], array( 'subscribed', 'closed' ) ) ) {
				add_action( 'wp_footer', array( $this, 'popup_icon_html' ) );
			}
		}
		$wcb_assign_home = self::$settings->get_params( 'wcb_assign_home' );
		$logic_value     = self::$settings->get_params( 'wcb_assign' );
		if ( $wcb_assign_home ) {
			if ( ! is_front_page() ) {
				return;
			}
		} else {
			if ( $logic_value ) {
				if ( stristr( $logic_value, "return" ) === false ) {
					$logic_value = "return (" . $logic_value . ");";
				}
				try {
					if ( ! eval( $logic_value ) ) {
						return;
					}
				} catch ( Error $e ) {
					trigger_error( $e->getMessage(), E_USER_WARNING );

					return;
				} catch ( Exception $e ) {
					trigger_error( $e->getMessage(), E_USER_WARNING );

					return;
				}
			}
		}
		$wcb_layout = self::$settings->get_params( 'wcb_layout' );

		$poup_type = self::$settings->get_params( 'wcb_popup_type' );
		if ( $poup_type ) {
			wp_enqueue_style( 'woocommerce-coupon-box-popup-type-' . $poup_type, VI_WOOCOMMERCE_COUPON_BOX_CSS . '/popup-effect/' . $poup_type . '.css', array(), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
		}
		$wcb_effect = self::$settings->get_params( 'wcb_effect' );
		// script

		$wcb_popup_time = 0;
		if ( self::$settings->get_params( 'wcb_popup_time' ) ) {
			$wcb_popup_time_val = explode( ',', self::$settings->get_params( 'wcb_popup_time' ) );
			if ( count( $wcb_popup_time_val ) < 2 ) {
				$wcb_popup_time = absint( $wcb_popup_time_val[0] );
			} else {
				$wcb_popup_time = ( absint( $wcb_popup_time_val[0] ) > absint( $wcb_popup_time_val[1] ) ) ? rand( $wcb_popup_time_val[1], $wcb_popup_time_val[0] ) : rand( $wcb_popup_time_val[0], $wcb_popup_time_val[1] );
			}
		}
		$wcb_select_popup = self::$settings->get_params( 'wcb_select_popup' );
		if ( $wcb_select_popup == 'random' ) {
			$ran = rand( 1, 3 );
			if ( $ran == 1 ) {
				$wcb_select_popup = 'time';
			} elseif ( $ran == 2 ) {
				$wcb_select_popup = 'scroll';
			} else {
				$wcb_select_popup = 'exit';
			}
		}
		$wcb_expire = intval( self::$settings->get_params( 'wcb_expire' ) );
		switch ( self::$settings->get_params( 'wcb_expire_unit' ) ) {
			case 'day':
				$wcb_expire *= 86400;
				break;
			case 'hour':
				$wcb_expire *= 3600;
				break;
			case 'minute':
				$wcb_expire *= 60;
				break;
			default:
		}
		$data = array(
			'ajaxurl'                     => self::$settings->get_params( 'ajax_endpoint' ) == 'ajax' ? ( admin_url( 'admin-ajax.php' ) . '?action=wcb_email' ) : site_url() . '/wp-json/woocommerce_coupon_box/subscribe',
			'wcb_select_popup'            => $wcb_select_popup,
			'wcb_popup_time'              => $wcb_popup_time,
			'wcb_popup_scroll'            => self::$settings->get_params( 'wcb_popup_scroll' ),
			'wcb_popup_exit'              => self::$settings->get_params( 'wcb_popup_exit' ),
			'wcb_on_close'                => self::$settings->get_params( 'wcb_on_close' ),
			'wcb_current_time'            => current_time( 'timestamp', true ),
			'wcb_show_coupon'             => self::$settings->get_params( 'wcb_show_coupon' ),
			'wcb_expire'                  => $wcb_expire,
			'wcb_expire_subscribed'       => self::$settings->get_params( 'wcb_expire_subscribed' ) * 86400,
			'wcb_gdpr_checkbox'           => self::$settings->get_params( 'wcb_gdpr_checkbox' ),
			'wcb_popup_type'              => $poup_type,
			'wcb_empty_email_warning'     => esc_html__( '*Please enter your email and subscribe.', 'woocommerce-coupon-box' ),
			'wcb_invalid_email_warning'   => esc_html__( '*Invalid email!', 'woocommerce-coupon-box' ),
			'i18n_copied_to_clipboard'    => esc_html__( 'The coupon code is copied to clipboard.', 'woocommerce-coupon-box' ),
			'wcb_title_after_subscribing' => self::$settings->get_params( 'wcb_title_after_subscribing' ),
			'wcb_layout'                  => $wcb_layout,
			'wcb_popup_position'          => in_array( self::$settings->get_params( 'wcb_popup_icon_position' ), array(
					'top-left',
					'bottom-left'
				)
			) ? 'left' : 'right',
			'wcb_input_name'              => self::$settings->get_params( 'wcb_input_name' ),
			'wcb_input_name_required'     => self::$settings->get_params( 'wcb_input_name_required' ),
			'wcb_input_lname'             => self::$settings->get_params( 'wcb_input_lname' ),
			'wcb_input_lname_required'    => self::$settings->get_params( 'wcb_input_lname_required' ),
			'wcb_input_mobile'            => self::$settings->get_params( 'wcb_input_mobile' ),
			'wcb_input_mobile_required'   => self::$settings->get_params( 'wcb_input_mobile_required' ),
			'wcb_input_birthday'          => self::$settings->get_params( 'wcb_input_birthday' ),
			'wcb_input_birthday_required' => self::$settings->get_params( 'wcb_input_birthday_required' ),
			'wcb_input_gender'            => self::$settings->get_params( 'wcb_input_gender' ),
			'wcb_input_gender_required'   => self::$settings->get_params( 'wcb_input_gender_required' ),
			'wcb_input_additional'          => self::$settings->get_params( 'wcb_input_additional' ),
			'wcb_input_additional_required' => self::$settings->get_params( 'wcb_input_additional_required' ),
			'language_ajax'               => $this->language_ajax,
			'wcb_never_reminder_enable'   => self::$settings->get_params( 'wcb_never_reminder_enable' ),
			'wcb_restrict_domain'         => self::$settings->get_params( 'wcb_restrict_domain' ),
			'enable_recaptcha'            => $enable_recaptcha ? 1 : '',
		);
		if ( $enable_recaptcha ) {
			self::enqueue_recaptcha();
		}

		switch ( $wcb_effect ) {
			case 'wcb-falling-leaves':
			case 'wcb-falling-leaves-1':
			case 'wcb-falling-heart':
				wp_enqueue_style( 'wcb-falling-leaves-style', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'wcb-falling-leaves.css', array(), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
				wp_enqueue_script( 'wcb-falling-leaves-script', VI_WOOCOMMERCE_COUPON_BOX_JS . 'wcb-falling-leaves.js', array( 'jquery' ), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
				break;
			case 'wcb-falling-snow':
				wp_enqueue_style( 'wcb-falling-snow-style', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'wcb-falling-snow.css', array(), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
				wp_enqueue_script( 'wcb-falling-snow-script', VI_WOOCOMMERCE_COUPON_BOX_JS . 'wcb-falling-snow.js', array( 'jquery' ), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
				break;
			case 'snowflakes':
			case 'snowflakes-1':
				wp_enqueue_style( 'wcb-'.$wcb_effect . '-style', VI_WOOCOMMERCE_COUPON_BOX_CSS . $wcb_effect . '.css', array(), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
				break;
			case 'snowflakes-2-1':
			case 'snowflakes-2-2':
			case 'snowflakes-2-3':
				wp_enqueue_style( 'wcb-'.$wcb_effect . '-style', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'snowflakes-2.css', array(), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
				break;
			default:
				wp_enqueue_style( 'wcb-weather-style', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'weather.css', array(), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
		}

		wp_enqueue_script( 'woocommerce-coupon-box-script', VI_WOOCOMMERCE_COUPON_BOX_JS . 'wcb.js', array( 'jquery' ), VI_WOOCOMMERCE_COUPON_BOX_VERSION, true );
		wp_localize_script( 'woocommerce-coupon-box-script', 'wcb_params', $data );
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
		if ( $wcb_layout > 0 && $wcb_layout < 6 ) {
			wp_enqueue_style( 'woocommerce-coupon-box-template-' . $wcb_layout, VI_WOOCOMMERCE_COUPON_BOX_CSS . 'layout-' . $wcb_layout . '.css', array(), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
		}
		// style
		$header_font        = json_decode( self::$settings->get_params( 'wcb_header_font' ) );
		$header_font_f      = '';
		$header_font_handle = '';
		if ( $wcb_layout != 2 && isset( $header_font->font ) && $header_font->font ) {
			$header_font_f      = $header_font->font;
			$header_font_f      = str_replace( ' ', '+', $header_font_f );
			$src                = '//fonts.googleapis.com/css?family=' . $header_font_f . ':300,400,700';
			$header_font_handle = 'wcb-customizer-google-font-header-' . strtolower( str_replace( '+', '-', $header_font_f ) );
			wp_enqueue_style( $header_font_handle, $src );
			$css_inline = '.wcb-coupon-box .wcb-md-content .wcb-modal-header{font-family:' . $header_font->font . '}';
			wp_add_inline_style( $header_font_handle, $css_inline );
		}
		$body_font = json_decode( self::$settings->get_params( 'wcb_body_font' ) );
		if ( isset( $body_font->font ) && $body_font->font ) {

			$body_font_f = $body_font->font;
			$body_font_f = str_replace( ' ', '+', $body_font_f );
			if ( $body_font_f != $header_font_f ) {
				$src              = '//fonts.googleapis.com/css?family=' . $body_font_f . ':300,400,700';
				$body_font_handle = 'wcb-customizer-google-font-body-' . strtolower( str_replace( '+', '-', $body_font_f ) );
				wp_enqueue_style( $body_font_handle, $src );
				$css_inline = '.wcb-coupon-box .wcb-md-content .wcb-modal-body{font-family:' . $body_font->font . '}';
				wp_add_inline_style( $body_font_handle, $css_inline );
			} else {
				$css_inline = '.wcb-coupon-box .wcb-md-content .wcb-modal-body{font-family:' . $body_font->font . '}';
				wp_add_inline_style( $header_font_handle, $css_inline );
			}
		}
		wp_enqueue_style( 'woocommerce-coupon-box-giftbox-icons', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'wcb_giftbox.css', array(), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
		wp_enqueue_style( 'woocommerce-coupon-box-social-icons', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'wcb_social_icons.css', array(), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
		wp_enqueue_style( 'woocommerce-coupon-box-close-icons', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'wcb_button_close_icons.css', array(), VI_WOOCOMMERCE_COUPON_BOX_VERSION );
		wp_enqueue_style( 'woocommerce-coupon-box-basic', VI_WOOCOMMERCE_COUPON_BOX_CSS . 'basic.css', array(), VI_WOOCOMMERCE_COUPON_BOX_VERSION );

		$css                         = '';
		$wcb_button_close_position_x = self::$settings->get_params( 'wcb_button_close_position_x' ) * ( - 1 );
		$wcb_button_close_position_y = self::$settings->get_params( 'wcb_button_close_position_y' ) * ( - 1 );
		/*button close*/
		$css .= '.wcb-coupon-box span.wcb-md-close{';
		$css .= 'font-size:' . self::$settings->get_params( 'wcb_button_close_size' ) . 'px;';
		$css .= 'width:' . self::$settings->get_params( 'wcb_button_close_width' ) . 'px;';
		$css .= 'line-height:' . self::$settings->get_params( 'wcb_button_close_width' ) . 'px;';
		$css .= 'color:' . self::$settings->get_params( 'wcb_button_close_color' ) . ';';
		$css .= 'background:' . self::$settings->get_params( 'wcb_button_close_bg_color' ) . ';';
		$css .= 'border-radius:' . self::$settings->get_params( 'wcb_button_close_border_radius' ) . 'px;';
		$css .= 'right:' . $wcb_button_close_position_x . 'px;';
		$css .= 'top:' . $wcb_button_close_position_y . 'px;';
		$css .= '}';

		if ( $wcb_layout == 2 ) {
			/*coupon box border radius*/
			$css .= '.wcb-coupon-box .wcb-content-wrap .wcb-content-wrap-child{border-radius:' . self::$settings->get_params( 'wcb_border_radius' ) . 'px;}';

			/*right column*/
			$css .= '.wcb-coupon-box .wcb-content-wrap .wcb-md-content-right{';
			if ( self::$settings->get_params( 'wcb_right_column_bg' ) ) {
				$css .= 'background-color:' . self::$settings->get_params( 'wcb_right_column_bg' ) . ';';
			}
			if ( self::$settings->get_params( 'wcb_right_column_bg_img' ) ) {
				$css .= 'background-image:url(' . self::$settings->get_params( 'wcb_right_column_bg_img' ) . ');';
				$css .= 'background-repeat:' . self::$settings->get_params( 'wcb_right_column_bg_img_repeat' ) . ';';
				$css .= 'background-size:' . self::$settings->get_params( 'wcb_right_column_bg_img_size' ) . ';';
				$css .= 'background-position:' . self::$settings->get_params( 'wcb_right_column_bg_img_position' ) . ';';
			}
			$css .= '}';
		} elseif ( $wcb_layout == 5 ) {
			/*coupon box border radius*/
			$css .= '.wcb-coupon-box .wcb-content-wrap .wcb-content-wrap-child{border-radius:' . self::$settings->get_params( 'wcb_border_radius' ) . 'px;}';

			/*left column*/
			$css .= '.wcb-coupon-box .wcb-content-wrap .wcb-md-content-left{';
			if ( self::$settings->get_params( 'wcb_right_column_bg' ) ) {
				$css .= 'background-color:' . self::$settings->get_params( 'wcb_right_column_bg' ) . ';';
			}
			if ( self::$settings->get_params( 'wcb_right_column_bg_img' ) ) {
				$css .= 'background-image:url(' . self::$settings->get_params( 'wcb_right_column_bg_img' ) . ');';
				$css .= 'background-repeat:' . self::$settings->get_params( 'wcb_right_column_bg_img_repeat' ) . ';';
				$css .= 'background-size:' . self::$settings->get_params( 'wcb_right_column_bg_img_size' ) . ';';
				$css .= 'background-position:' . self::$settings->get_params( 'wcb_right_column_bg_img_position' ) . ';';
			}
			$css .= '}';

		} else {
			/*coupon box border radius*/
			$css .= '.wcb-coupon-box .wcb-content-wrap .wcb-md-content{border-radius:' . self::$settings->get_params( 'wcb_border_radius' ) . 'px;}';
		}
		/*header*/
		$css .= '.wcb-coupon-box .wcb-md-content .wcb-modal-header{';
		if ( self::$settings->get_params( 'wcb_bg_header' ) ) {
			$css .= 'background-color:' . self::$settings->get_params( 'wcb_bg_header' ) . ';';
		}
		if ( self::$settings->get_params( 'wcb_color_header' ) ) {
			$css .= 'color:' . self::$settings->get_params( 'wcb_color_header' ) . ';';
		}
		$css .= 'font-size:' . self::$settings->get_params( 'wcb_title_size' ) . 'px;';
		$css .= 'line-height:' . self::$settings->get_params( 'wcb_title_size' ) . 'px;';
		$css .= 'padding-top:' . self::$settings->get_params( 'wcb_title_space' ) . 'px;';
		$css .= 'padding-bottom:' . self::$settings->get_params( 'wcb_title_space' ) . 'px;';
		if ( self::$settings->get_params( 'wcb_header_bg_img' ) ) {
			$css .= 'background-image:url(' . self::$settings->get_params( 'wcb_header_bg_img' ) . ');';
			$css .= 'background-repeat:' . self::$settings->get_params( 'wcb_header_bg_img_repeat' ) . ';';
			$css .= 'background-size:' . self::$settings->get_params( 'wcb_header_bg_img_size' ) . ';';
			$css .= 'background-position:' . self::$settings->get_params( 'wcb_header_bg_img_position' ) . ';';
		}
		$css .= '}';

		/*body*/
		$css .= '.wcb-coupon-box .wcb-md-content .wcb-modal-body{';
		$css .= 'background-color:' . self::$settings->get_params( 'wcb_body_bg' ) . ';';
		$css .= 'color:' . self::$settings->get_params( 'wcb_body_text_color' ) . ';';
		if ( self::$settings->get_params( 'wcb_body_bg_img' ) ) {
			$css .= 'background-image:url(' . self::$settings->get_params( 'wcb_body_bg_img' ) . ');';
			$css .= 'background-repeat:' . self::$settings->get_params( 'wcb_body_bg_img_repeat' ) . ';';
			$css .= 'background-size:' . self::$settings->get_params( 'wcb_body_bg_img_size' ) . ';';
			$css .= 'background-position:' . self::$settings->get_params( 'wcb_body_bg_img_position' ) . ';';
		}
		$css .= '}';

		$css .= '.wcb-coupon-box .wcb-md-content .wcb-modal-body .wcb-coupon-message{color:' . self::$settings->get_params( 'wcb_color_message' ) . ';font-size:' . self::$settings->get_params( 'wcb_message_size' ) . 'px;text-align:' . self::$settings->get_params( 'wcb_message_align' ) . '}';

		/*text follow us*/
		$css .= $this->generate_css( '.wcb-coupon-box .wcb-md-content .wcb-text-title', 'color', 'wcb_color_follow_us', '', '' );
		/*email input*/
		$css .= '.wcb-coupon-box .wcb-newsletter input.wcb-email{';
		$css .= 'border-radius:' . self::$settings->get_params( 'wcb_email_input_border_radius' ) . 'px;';
		$css .= 'color:' . self::$settings->get_params( 'wcb_email_input_color' ) . ' !important;';
		$css .= 'background:' . self::$settings->get_params( 'wcb_email_input_bg_color' ) . ' !important;';
		$css .= '}';
		$css .= '.wcb-coupon-box .wcb-newsletter .wcb-input-group ::placeholder{color:' . self::$settings->get_params( 'wcb_email_input_color' ) . ' !important;}';

		$css .= '.wcb-coupon-box .wcb-custom-input-fields .wcb-input-field-item{border-radius:' . self::$settings->get_params( 'wcb_custom_input_border_radius' ) . 'px;}';
		$css .= '.wcb-coupon-box .wcb-custom-input-fields .wcb-input-field-item input,.wcb-coupon-box .wcb-custom-input-fields .wcb-input-field-item select{';
		$css .= 'color:' . self::$settings->get_params( 'wcb_custom_input_color' ) . ' !important;';
		$css .= 'background:' . self::$settings->get_params( 'wcb_custom_input_bg_color' ) . ' !important;';
		$css .= '}';
		$css .= '.wcb-coupon-box .wcb-custom-input-fields .wcb-input-field-item ::placeholder{color:' . self::$settings->get_params( 'wcb_custom_input_color' ) . ' !important;}';
		$css .= '.wcb-coupon-box .wcb-modal-body .wcb-coupon-box-newsletter .wcb-newsletter-form input{margin-right:' . self::$settings->get_params( 'wcb_email_button_space' ) . 'px;}';

		if ( self::$settings->get_params( 'wcb_gdpr_checkbox' ) ) {
			$css .= '.wcb-coupon-box.wcb-collapse-after-close .wcb-coupon-box-newsletter{padding-bottom:0 !important;}';
		}
		/*button subscribe*/
		$css .= '.wcb-coupon-box .wcb-newsletter span.wcb-button{';
		$css .= 'color:' . self::$settings->get_params( 'wcb_button_text_color' ) . ';';
		$css .= 'background-color:' . self::$settings->get_params( 'wcb_button_bg_color' ) . ';';
		$css .= 'border-radius:' . self::$settings->get_params( 'wcb_button_border_radius' ) . 'px;';
		$css .= '}';
		/*overlay*/
		$css .= $this->generate_css( '.wcb-md-overlay', 'background', 'alpha_color_overlay', '', '' );
		/*social*/
		$css .= '.wcb-coupon-box .wcb-sharing-container .wcb-list-socials .wcb-social-icon{';
		$css .= 'font-size:' . self::$settings->get_params( 'wcb_social_icons_size' ) . 'px;';
		$css .= 'line-height:' . self::$settings->get_params( 'wcb_social_icons_size' ) . 'px;';
		$css .= '}';
		/*social-color*/
		$css .= $this->generate_css( '.wcb-coupon-box .wcb-sharing-container .wcb-list-socials .wcb-facebook-follow .wcb-social-icon', 'color', 'wcb_social_icons_facebook_color', '', '' );
		$css .= $this->generate_css( '.wcb-coupon-box .wcb-sharing-container .wcb-list-socials .wcb-twitter-follow .wcb-social-icon', 'color', 'wcb_social_icons_twitter_color', '', '' );
		$css .= $this->generate_css( '.wcb-coupon-box .wcb-sharing-container .wcb-list-socials .wcb-pinterest-follow .wcb-social-icon', 'color', 'wcb_social_icons_pinterest_color', '', '' );
		$css .= $this->generate_css( '.wcb-coupon-box .wcb-sharing-container .wcb-list-socials .wcb-instagram-follow .wcb-social-icon', 'color', 'wcb_social_icons_instagram_color', '', '' );
		$css .= $this->generate_css( '.wcb-coupon-box .wcb-sharing-container .wcb-list-socials .wcb-dribbble-follow .wcb-social-icon', 'color', 'wcb_social_icons_dribbble_color', '', '' );
		$css .= $this->generate_css( '.wcb-coupon-box .wcb-sharing-container .wcb-list-socials .wcb-tumblr-follow .wcb-social-icon', 'color', 'wcb_social_icons_tumblr_color', '', '' );
		$css .= $this->generate_css( '.wcb-coupon-box .wcb-sharing-container .wcb-list-socials .wcb-google-follow .wcb-social-icon', 'color', 'wcb_social_icons_google_color', '', '' );
		$css .= $this->generate_css( '.wcb-coupon-box .wcb-sharing-container .wcb-list-socials .wcb-vkontakte-follow .wcb-social-icon', 'color', 'wcb_social_icons_vkontakte_color', '', '' );
		$css .= $this->generate_css( '.wcb-coupon-box .wcb-sharing-container .wcb-list-socials .wcb-linkedin-follow .wcb-social-icon', 'color', 'wcb_social_icons_linkedin_color', '', '' );
		$css .= $this->generate_css( '.wcb-coupon-box .wcb-sharing-container .wcb-list-socials .wcb-youtube-follow .wcb-social-icon', 'color', 'wcb_social_icons_youtube_color', '', '' );
		$css .= self::$settings->get_params( 'wcb_custom_css' );
		/*popup icon*/
		$css .= '.wcb-coupon-box-small-icon{';
		$css .= 'font-size:' . self::$settings->get_params( 'wcb_popup_icon_size' ) . 'px;';
		$css .= 'line-height:' . self::$settings->get_params( 'wcb_popup_icon_size' ) . 'px;';
		$css .= 'color:' . self::$settings->get_params( 'wcb_popup_icon_color' ) . ';';
		$css .= '}';
		$css .= '.wcb-coupon-box-small-icon-wrap{';
		$css .= 'background-color:' . self::$settings->get_params( 'wcb_popup_icon_bg_color' ) . ';';
		$css .= 'border-radius:' . self::$settings->get_params( 'wcb_popup_icon_border_radius' ) . 'px;';
		$css .= '}';
		/*button no, thanks*/
		$css .= '.wcb-coupon-box .wcb-md-close-never-reminder-field .wcb-md-close-never-reminder{';
		$css .= 'color:' . self::$settings->get_params( 'wcb_no_thank_button_color' ) . ';';
		$css .= 'background-color:' . self::$settings->get_params( 'wcb_no_thank_button_bg_color' ) . ';';
		$css .= 'border-radius:' . self::$settings->get_params( 'wcb_no_thank_button_border_radius' ) . 'px;';
		$css .= '}';
		wp_add_inline_style( 'woocommerce-coupon-box-basic', $css );
	}

	public function generate_css( $selector, $style, $mod_name, $prefix = '', $postfix = '', $echo = false ) {
		$return = '';
		$mod    = self::$settings->get_params( $mod_name );
		if ( ! empty( $mod ) ) {
			$return = sprintf( '%s { %s:%s; }',
				$selector,
				$style,
				$prefix . $mod . $postfix
			);
			if ( $echo ) {
				echo $return;
			}
		}

		return $return;
	}

	public function popup_icon_html() {
		$hide = '';
		if ( ! self::$settings->get_params( 'wcb_popup_icon_mobile' ) ) {
			$hide = ' wcb-coupon-box-small-icon-hidden-mobile';
		}
		if ( self::$settings->get_params( 'wcb_popup_icon_enable' ) ) {
			if ( isset( $_COOKIE['woo_coupon_box'] ) ) {
				?>
                <div class="wcb-coupon-box-small-icon-wrap wcb-coupon-box-small-icon-position-<?php echo self::$settings->get_params( 'wcb_popup_icon_position' );
				echo $hide; ?>">
                    <div class="wcb-coupon-box-small-icon-container">
                        <span class="wcb-coupon-box-small-icon-close wcb_button_close_icons-cancel"
                              title="<?php esc_html_e( 'Do not show again', 'woocommerce-coupon-box' ) ?>"></span>
                        <span class="wcb-coupon-box-small-icon <?php echo self::$settings->get_params( 'wcb_popup_icon' ) ?>"></span>
                    </div>
                </div>
				<?php
			} else {
				if ( in_array( self::$settings->get_params( 'wcb_popup_icon_position' ), array(
					'top-left',
					'bottom-left'
				) ) ) {
					$hide .= ' wcb-coupon-box-small-icon-hide-left';
				} else {
					$hide .= ' wcb-coupon-box-small-icon-hide-right';
				}
				?>
                <div class="wcb-coupon-box-small-icon-wrap wcb-coupon-box-small-icon-position-<?php echo self::$settings->get_params( 'wcb_popup_icon_position' );
				echo $hide; ?>">
                    <div class="wcb-coupon-box-small-icon-container">
                        <span class="wcb-coupon-box-small-icon-close wcb_button_close_icons-cancel"
                              title="<?php esc_html_e( 'Do not show again', 'woocommerce-coupon-box' ) ?>"></span>
                        <span class="wcb-coupon-box-small-icon <?php echo self::$settings->get_params( 'wcb_popup_icon' ) ?>"></span>
                    </div>
                </div>
				<?php
			}
		}
	}

	public function wp_footer() {
		echo $this->get_template( 'basic' );
	}

	/**
	 * Get template data
	 *
	 * @param $name
	 *
	 * @return string
	 */
	protected function get_template( $name ) {
		$title   = self::$settings->get_params( 'wcb_title' );
		$message = self::$settings->get_params( 'wcb_message' );
		$socials = $this->get_socials();

		$parten  = array(
			'/\{title\}/',
			'/\{message\}/',
			'/\{socials\}/'
		);
		$replace = array(
			esc_html( $title ),
			esc_html( $message ),
			ent2ncr( $socials )
		);

		ob_start();
		require_once VI_WOOCOMMERCE_COUPON_BOX_TEMPLATES . $name . '.php';
		$html = ob_get_clean();
		$html = preg_replace( $parten, $replace, $html );

		return ent2ncr( $html );
	}

	/**
	 * Get socials
	 */
	protected function get_socials() {
		$link_target = self::$settings->get_params( 'wcb_social_icons_target' );

		$facebook_url  = self::$settings->get_params( 'wcb_social_icons_facebook_url' );
		$twitter_url   = self::$settings->get_params( 'wcb_social_icons_twitter_url' );
		$pinterest_url = self::$settings->get_params( 'wcb_social_icons_pinterest_url' );
		$instagram_url = self::$settings->get_params( 'wcb_social_icons_instagram_url' );
		$dribbble_url  = self::$settings->get_params( 'wcb_social_icons_dribbble_url' );
		$tumblr_url    = self::$settings->get_params( 'wcb_social_icons_tumblr_url' );
		$google_url    = self::$settings->get_params( 'wcb_social_icons_google_url' );
		$vkontakte_url = self::$settings->get_params( 'wcb_social_icons_vkontakte_url' );
		$linkedin_url  = self::$settings->get_params( 'wcb_social_icons_linkedin_url' );
		$youtube_url   = self::$settings->get_params( 'wcb_social_icons_youtube_url' );

		$facebook_select  = self::$settings->get_params( 'wcb_social_icons_facebook_select' );
		$twitter_select   = self::$settings->get_params( 'wcb_social_icons_twitter_select' );
		$pinterest_select = self::$settings->get_params( 'wcb_social_icons_pinterest_select' );
		$instagram_select = self::$settings->get_params( 'wcb_social_icons_instagram_select' );
		$dribbble_select  = self::$settings->get_params( 'wcb_social_icons_dribbble_select' );
		$tumblr_select    = self::$settings->get_params( 'wcb_social_icons_tumblr_select' );
		$google_select    = self::$settings->get_params( 'wcb_social_icons_google_select' );
		$vkontakte_select = self::$settings->get_params( 'wcb_social_icons_vkontakte_select' );
		$linkedin_select  = self::$settings->get_params( 'wcb_social_icons_linkedin_select' );
		$youtube_select   = self::$settings->get_params( 'wcb_social_icons_youtube_select' );

		$html = '<ul class="wcb-list-socials wcb-list-unstyled" id="wcb-sharing-accounts">';

		if ( $facebook_url ) {
			ob_start();
			?>
            <a <?php if ( $link_target == '_blank' )
				echo esc_attr( 'target=_blank' ) ?> href="//www.facebook.com/<?php esc_attr_e( $facebook_url ) ?>"
                                                    class="wcb-social-button wcb-facebook"
                                                    title="<?php esc_html_e( 'Follow Facebook', 'woocommerce-coupon-box' ) ?>">
                <span class="wcb-social-icon <?php echo esc_attr( $facebook_select ) ?>"></span></a>
			<?php $facebook_html = ob_get_clean();

			$html .= '<li class="wcb-facebook-follow">' . $facebook_html . '</li>';
		}
		if ( $twitter_url ) {
			ob_start();
			?>
            <a <?php if ( $link_target == '_blank' )
				echo esc_attr( 'target=_blank' ) ?> href="//twitter.com/<?php esc_attr_e( $twitter_url ) ?>"
                                                    class="wcb-social-button wcb-twitter"
                                                    title="<?php esc_html_e( 'Follow Twitter', 'woocommerce-coupon-box' ) ?>">
                <span class="wcb-social-icon <?php echo esc_attr( $twitter_select ) ?>"></span>
            </a>
			<?php
			$twitter_html = ob_get_clean();
			$html         .= '<li class="wcb-twitter-follow">' . $twitter_html . '</li>';
		}
		if ( $pinterest_url ) {
			ob_start();
			?>
            <a <?php if ( $link_target == '_blank' )
				echo esc_attr( 'target=_blank' ) ?> href="//www.pinterest.com/<?php esc_attr_e( $pinterest_url ) ?>"
                                                    class="wcb-social-button wcb-pinterest"
                                                    title="<?php esc_html_e( 'Follow Pinterest', 'woocommerce-coupon-box' ) ?>">
                <span class="wcb-social-icon <?php echo esc_attr( $pinterest_select ) ?>"></span>
            </a>
			<?php
			$pinterest_html = ob_get_clean();
			$html           .= '<li class="wcb-pinterest-follow">' . $pinterest_html . '</li>';
		}
		if ( $instagram_url ) {
			ob_start();
			?>
            <a <?php if ( $link_target == '_blank' )
				echo esc_attr( 'target=_blank' ) ?> href="//www.instagram.com/<?php esc_attr_e( $instagram_url ) ?>"
                                                    class="wcb-social-button wcb-instagram"
                                                    title="<?php esc_html_e( 'Follow Instagram', 'woocommerce-coupon-box' ) ?>">
                <span class="wcb-social-icon <?php echo esc_attr( $instagram_select ) ?>"></span>
            </a>
			<?php
			$instagram_html = ob_get_clean();
			$html           .= '<li class="wcb-instagram-follow">' . $instagram_html . '</li>';
		}
		if ( $dribbble_url ) {
			ob_start();
			?>
            <a <?php if ( $link_target == '_blank' )
				echo esc_attr( 'target=_blank' ) ?> href="//dribbble.com/<?php esc_attr_e( $dribbble_url ) ?>"
                                                    class="wcb-social-button wcb-dribbble"
                                                    title="<?php esc_html_e( 'Follow Dribbble', 'woocommerce-coupon-box' ) ?>">
                <span class="wcb-social-icon <?php echo esc_attr( $dribbble_select ) ?>"></span>
            </a>
			<?php
			$dribbble_html = ob_get_clean();
			$html          .= '<li class="wcb-dribbble-follow">' . $dribbble_html . '</li>';
		}
		if ( $tumblr_url ) {
			ob_start();
			?>
            <a <?php if ( $link_target == '_blank' )
				echo esc_attr( 'target=_blank' ) ?> href="//www.tumblr.com/follow/<?php esc_attr_e( $tumblr_url ) ?>"
                                                    class="wcb-social-button wcb-tumblr"
                                                    title="<?php esc_html_e( 'Follow Tumblr', 'woocommerce-coupon-box' ) ?>">
                <span class="wcb-social-icon <?php echo esc_attr( $tumblr_select ) ?>"></span>
            </a>
			<?php
			$tumblr_html = ob_get_clean();
			$html        .= '<li class="wcb-tumblr-follow">' . $tumblr_html . '</li>';
		}
		if ( $google_url ) {
			ob_start();
			?>
            <a <?php if ( $link_target == '_blank' )
				echo esc_attr( 'target=_blank' ) ?> href="//plus.google.com/+<?php esc_attr_e( $google_url ) ?>"
                                                    class="wcb-social-button wcb-google-plus"
                                                    title="<?php esc_html_e( 'Follow Google Plus', 'woocommerce-coupon-box' ) ?>">
                <span class="wcb-social-icon <?php echo esc_attr( $google_select ) ?>"></span>
            </a>
			<?php
			$google_html = ob_get_clean();
			$html        .= '<li class="wcb-google-follow">' . $google_html . '</li>';
		}
		if ( $vkontakte_url ) {
			ob_start();
			?>
            <a <?php if ( $link_target == '_blank' )
				echo esc_attr( 'target=_blank' ) ?> href="//vk.com/<?php esc_attr_e( $vkontakte_url ) ?>"
                                                    class="wcb-social-button wcb-vk"
                                                    title="<?php esc_html_e( 'Follow VK', 'woocommerce-coupon-box' ) ?>">
                <span class="wcb-social-icon <?php echo esc_attr( $vkontakte_select ) ?>"></span>
            </a>
			<?php
			$vkontakte_html = ob_get_clean();
			$html           .= '<li class="wcb-vkontakte-follow">' . $vkontakte_html . '</li>';
		}
		if ( $linkedin_url ) {
			ob_start();
			?>
            <a <?php if ( $link_target == '_blank' )
				echo esc_attr( 'target=_blank' ) ?> href="//www.linkedin.com/in/<?php esc_attr_e( $linkedin_url ) ?>"
                                                    class="wcb-social-button wcb-linkedin"
                                                    title="<?php esc_html_e( 'Follow Linkedin', 'woocommerce-coupon-box' ) ?>">
                <span class="wcb-social-icon <?php echo esc_attr( $linkedin_select ) ?>"></span>
            </a>
			<?php
			$linkedin_html = ob_get_clean();
			$html          .= '<li class="wcb-linkedin-follow">' . $linkedin_html . '</li>';
		}

		if ( $youtube_url ) {
			ob_start();
			?>
            <a <?php if ( $link_target == '_blank' )
				echo esc_attr( 'target=_blank' ) ?> href="<?php echo esc_url_raw( $youtube_url ) ?>"
                                                    class="wcb-social-button wcb-youtube"
                                                    title="<?php esc_html_e( 'Follow Youtube', 'woocommerce-coupon-box' ) ?>">
                <span class="wcb-social-icon <?php echo esc_attr( $youtube_select ) ?>"></span>
            </a>
			<?php
			$youtube_html = ob_get_clean();
			$html         .= '<li class="wcb-youtube-follow">' . $youtube_html . '</li>';
		}

		$html = apply_filters( 'wcb_after_socials_html', $html );
		$html .= '</ul>';

		return $html;
	}

	public function button_open_popup( $attrs ) {
		shortcode_atts( array( 'text' => 'Subscribe', ), $attrs );
		$type = self::$settings->get_params( 'wcb_select_popup' );

		return $type == 'button' ? sprintf( "<a class='wcb-open-popup'>%s</a>", esc_html( $attrs['text'] ) ) : '';
	}

}