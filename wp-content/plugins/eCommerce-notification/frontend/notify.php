<?php

/**
 * Class ECOMMERCE_NOTIFICATION_Frontend_Notify
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ECOMMERCE_NOTIFICATION_Frontend_Notify {
	protected $settings;
	protected $lang;
	protected $params;

	public function __construct() {
		global $ecommerce_notification_settings;

		if ( ! $ecommerce_notification_settings ) {
			$ecommerce_notification_settings = get_option( 'ecommerce_notification_params', array() );
		}
		$this->params   = $ecommerce_notification_settings;
		$this->settings = new ECOMMERCE_NOTIFICATION_Admin_Settings();
		add_action( 'wp_enqueue_scripts', array( $this, 'init_scripts' ), 9999999999 );

		add_action( 'wp_ajax_nopriv_woonotification_get_product', array( $this, 'product_html' ) );
		add_action( 'wp_ajax_woonotification_get_product', array( $this, 'product_html' ) );

	}

	public function product_html() {
		$enable = $this->settings->get_field( 'enable' );
		if ( $enable ) {
			$products = $this->get_product();

			if ( is_array( $products ) && count( $products ) ) {
				echo json_encode( $products );
				die;
			}
		}
		echo json_encode( array() );
		die;
	}

	public function wp_footer() {
		$sound_enable = $this->settings->get_field( 'sound_enable' );
		$sound        = $this->settings->get_field( 'sound' );

		echo $this->show_product(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $sound_enable ) {
			?>
            <audio id="ecommerce-notification-audio">
                <source src="<?php echo esc_url( ECOMMERCE_NOTIFICATION_SOUNDS_URL . $sound ) ?>">
            </audio>
			<?php
		}
	}

	public function show_product() {
		$image_position = $this->settings->get_field( 'image_position' );
		$position       = $this->settings->get_field( 'position' );

		$background_image = $this->settings->get_field( 'background_image' );
		$class[]          = $image_position ? 'img-right' : '';

		switch ( $position ) {
			case  1:
				$class[] = 'bottom_right';
				break;
			case  2:
				$class[] = 'top_left';
				break;
			case  3:
				$class[] = 'top_right';
				break;
		}
		if ( $background_image ) {
			$class[] = 'wn-background-template-type-2';
			$class[] = 'wn-extended';
			$class[] = 'wn-' . $background_image;
		}
		$item_id = 0;
		if ( is_single() ) {
			global $post;
			$post_type = $this->settings::get_field( 'post_type' );
			$item_id   = $post->post_type == $post_type ? $post->ID : '';
		}
		ob_start();

		?>
        <div id="message-purchased" class=" <?php echo implode( ' ', $class ) ?>" style="display: none;"
             data-product_id="<?php echo esc_attr( $item_id ); ?>">

        </div>
		<?php


		return ob_get_clean();
	}

	protected function get_product() {
		$prefix                = ecommerce_notification_prefix();
		$enable_single_product = $this->settings::get_field( 'enable_single_product' );
		$product_thumb         = $this->settings::get_field( 'product_sizes', 'thumbnail' );
		$item_id               = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		$post_type             = $this->settings::get_field( 'post_type' );
		$non_ajax              = $this->settings::get_field( 'non_ajax' );
		$current_lang          = '';
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			$current_lang = wpml_get_current_language();
		} elseif ( class_exists( 'Polylang' ) ) {
			$current_lang = pll_current_language( 'slug' );
		}
		$prefix .= $current_lang;
		if ( $enable_single_product ) {
			if ( is_single() ) {
				global $post;
				$item_id = $post->post_type == $post_type ? $post->ID : '';
			}
			if ( $item_id ) {
				$products = get_transient( $prefix . 'wn_product_child' . $item_id );
				if ( is_array( $products ) && count( $products ) ) {
					return $products;
				}
				$item = get_post( $item_id );
				$p_id = $item->ID;
				$link = get_permalink( $p_id );

				if ( $this->settings::get_field( 'save_logs' ) ) {
					$link = wp_nonce_url( $link, 'wocommerce_notification_click', 'link' );
				}

				$products = array(
					array(
						'title' => get_the_title( $p_id ),
						'url'   => $link,
						'thumb' => has_post_thumbnail( $p_id ) ? get_the_post_thumbnail_url( $p_id, $product_thumb ) : '',
					)
				);
				if ( $non_ajax ) {
					set_transient( $prefix . 'wn_product_child' . $item_id, $products, 3600 );
				}

				return $products;
			}
		}
		/*Params from Settings*/
		$products = get_transient( $prefix );
		if ( is_array( $products ) && count( $products ) ) {
			return $products;
		} else {
			$products = array();
		}
		$archive_products = $this->settings::get_field( 'archive_products' );
		$archive_products = is_array( $archive_products ) ? $archive_products : array();
		if ( count( array_filter( $archive_products ) ) < 1 ) {
			$args = array(
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC'
			);

		} else {
			$args = array(
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => '50',
				'orderby'        => 'date',
				'post__in'       => $archive_products,
				'order'          => 'DESC'
			);
		}
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$item_id = get_the_ID();
				$link    = get_permalink( $item_id );

				if ( $this->settings::get_field( 'save_logs' ) ) {
					$link = wp_nonce_url( $link, 'wocommerce_notification_click', 'link' );
				}

				$product_tmp = array(
					'title' => get_the_title( $item_id ),
					'url'   => $link,
					'thumb' => has_post_thumbnail( $item_id ) ? get_the_post_thumbnail_url( $item_id, $product_thumb ) : '',
				);
				$products[]  = $product_tmp;
			}
			// Reset Post Data
			wp_reset_postdata();
		}
		if ( count( $products ) ) {
			if ( $non_ajax ) {
				set_transient( $prefix, $products, 3600 );
			}

			return $products;
		} else {
			return false;
		}
	}

	/**
	 * Get message purchased with shortcode
	 * @return mixed|void
	 */
	public function get_message_purchased() {
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			$current_lang = wpml_get_current_language();
			if ( isset( $this->params[ 'message_purchased_' . $current_lang ] ) ) {
				return apply_filters( 'ECOMMERCE_NOTIFICATION_get_message_purchased_' . $current_lang, $this->params[ 'message_purchased_' . $current_lang ] );
			}
		} elseif ( class_exists( 'Polylang' ) ) {
			$current_lang = pll_current_language( 'slug' );
			if ( isset( $this->params[ 'message_purchased_' . $current_lang ] ) ) {
				return apply_filters( 'ECOMMERCE_NOTIFICATION_get_message_purchased_' . $current_lang, $this->params[ 'message_purchased_' . $current_lang ] );
			}
		}

		return apply_filters( 'ECOMMERCE_NOTIFICATION_get_message_purchased', $this->params['message_purchased'] );
	}

	/**
	 * Get Virtual Time
	 * @return mixed|void
	 */
	public function get_virtual_name() {
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			$current_lang = wpml_get_current_language();
			if ( isset( $this->params[ 'virtual_name_' . $current_lang ] ) ) {
				return apply_filters( 'ECOMMERCE_NOTIFICATION_get_virtual_name_' . $current_lang, $this->params[ 'virtual_name_' . $current_lang ] );
			}
		} elseif ( class_exists( 'Polylang' ) ) {
			$current_lang = pll_current_language( 'slug' );
			if ( isset( $this->params[ 'virtual_name_' . $current_lang ] ) ) {
				return apply_filters( 'ECOMMERCE_NOTIFICATION_get_virtual_name_' . $current_lang, $this->params[ 'virtual_name_' . $current_lang ] );
			}
		}

		return apply_filters( 'ECOMMERCE_NOTIFICATION_get_virtual_name', $this->params['virtual_name'] );
	}

	/**
	 * Get Virtual City
	 * @return mixed|void
	 */
	public function get_virtual_city() {
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			$current_lang = wpml_get_current_language();
			if ( isset( $this->params[ 'virtual_city_' . $current_lang ] ) ) {
				return apply_filters( 'ECOMMERCE_NOTIFICATION_get_virtual_city_' . $current_lang, $this->params[ 'virtual_city_' . $current_lang ] );
			}
		} elseif ( class_exists( 'Polylang' ) ) {
			$current_lang = pll_current_language( 'slug' );
			if ( isset( $this->params[ 'virtual_city_' . $current_lang ] ) ) {
				return apply_filters( 'ECOMMERCE_NOTIFICATION_get_virtual_city_' . $current_lang, $this->params[ 'virtual_city_' . $current_lang ] );
			}
		}

		return apply_filters( 'ECOMMERCE_NOTIFICATION_get_virtual_city', $this->params['virtual_city'] );
	}

	/**
	 * Get Virtual Country
	 * @return mixed|void
	 */
	public function get_virtual_country() {
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			$current_lang = wpml_get_current_language();
			if ( isset( $this->params[ 'virtual_country_' . $current_lang ] ) ) {
				return apply_filters( 'ECOMMERCE_NOTIFICATION_get_virtual_country_' . $current_lang, $this->params[ 'virtual_country_' . $current_lang ] );
			} elseif ( class_exists( 'Polylang' ) ) {
				$current_lang = pll_current_language( 'slug' );
				if ( isset( $this->params[ 'virtual_country_' . $current_lang ] ) ) {
					return apply_filters( 'ECOMMERCE_NOTIFICATION_get_virtual_country_' . $current_lang, $this->params[ 'virtual_country_' . $current_lang ] );
				}
			}
		}

		return apply_filters( 'ECOMMERCE_NOTIFICATION_get_virtual_country', $this->params['virtual_country'] );
	}

	public function init_scripts() {
		$enable = $this->settings->get_field( 'enable' );
		if ( ! $enable ) {
			return;
		}
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			$this->lang = wpml_get_current_language();

		} elseif ( class_exists( 'Polylang' ) ) {
			$this->lang = pll_current_language( 'slug' );
		} else {
			$this->lang = '';
		}
		$is_home     = $this->settings->get_field( 'is_home' );
		$is_checkout = $this->settings->get_field( 'is_checkout' );
		$is_cart     = $this->settings->get_field( 'is_cart' );
		/*Conditional tags*/
		$logic_value = $this->settings->get_field( 'conditional_tags' );
		/*Assign page*/
		if ( $is_home && ( is_home() || is_front_page() ) ) {
			return;
		}
		if ( $is_checkout && is_checkout() ) {
			return;
		}
		if ( $is_cart && is_cart() ) {
			return;
		}
		if ( $logic_value ) {
			if ( stristr( $logic_value, "return" ) === false ) {
				$logic_value = "return (" . $logic_value . ");";
			}
			if ( ! eval( $logic_value ) ) {
				return;
			}
		}
		$detect = new VillaTheme_Mobile_Detect;

		// Any mobile device (phones or tablets).
		if ( $detect->isMobile() && ! $this->settings::get_field( 'enable_mobile' ) ) {
			return false;
		}
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
		if ( WP_DEBUG ) {
			wp_enqueue_style( 'ecommerce-notification', ECOMMERCE_NOTIFICATION_CSS . 'ecommerce-notification.css', array(), ECOMMERCE_NOTIFICATION_VERSION );

			wp_enqueue_script( 'ecommerce-notification', ECOMMERCE_NOTIFICATION_JS . 'ecommerce-notification.js', array( 'jquery' ), ECOMMERCE_NOTIFICATION_VERSION );
		} else {
			wp_enqueue_style( 'ecommerce-notification', ECOMMERCE_NOTIFICATION_CSS . 'ecommerce-notification.min.css', array(), ECOMMERCE_NOTIFICATION_VERSION );

			wp_enqueue_script( 'ecommerce-notification', ECOMMERCE_NOTIFICATION_JS . 'ecommerce-notification.min.js', array( 'jquery' ), ECOMMERCE_NOTIFICATION_VERSION );
		}
		if ( $this->settings->get_field( 'background_image' ) ) {
			wp_enqueue_style( 'ecommerce-notification-templates', ECOMMERCE_NOTIFICATION_CSS . 'ecommerce-notification-templates.css', array(), ECOMMERCE_NOTIFICATION_VERSION );
		}
		$prefix        = ecommerce_notification_prefix();
		$non_ajax      = $this->settings::get_field( 'non_ajax' );
		$options_array = get_transient( $prefix . '_head' . $this->lang );
		if ( ! is_array( $options_array ) || empty( $options_array ) ) {
			$options_array     = array(
				'loop'                  => $this->settings::get_field( 'loop' ),
				'display_time'          => $this->settings::get_field( 'display_time' ),
				'next_time'             => $this->settings::get_field( 'next_time' ),
				'notification_per_page' => $this->settings::get_field( 'notification_per_page' ),
				'display_effect'        => $this->settings::get_field( 'message_display_effect' ),
				'hidden_effect'         => $this->settings::get_field( 'message_hidden_effect' ),
				'show_close'            => $this->settings::get_field( 'show_close_icon' ),
			);
			$message_purchased = $this->get_message_purchased();
			if ( ! is_array( $message_purchased ) ) {
				$message_purchased = array( $message_purchased );
			}
			$options_array['messages']           = $message_purchased;
			$options_array['message_custom']     = $this->settings::get_field( 'custom_shortcode' );
			$options_array['message_number_min'] = $this->settings::get_field( 'min_number', 0 );
			$options_array['message_number_max'] = $this->settings::get_field( 'max_number', 0 );
			$options_array['time']               = $this->settings::get_field( 'virtual_time' );
			$options_array['detect']             = $this->settings::get_field( 'country' );
			$virtual_name                        = $this->get_names( 50 );
			if ( is_array( $virtual_name ) && count( $virtual_name ) ) {
				$options_array['names'] = array_map( 'base64_encode', $virtual_name );
			}
			if ( ! empty( $options_array['detect'] ) ) {
				$cities = $this->get_cities( 50 );
				if ( is_array( $cities ) && count( $cities ) ) {
					$options_array['cities'] = array_map( 'base64_encode', $cities );
				}
				$options_array['country'] = $this->get_virtual_country();
			}
			if ( $non_ajax ) {
				set_transient( $prefix . '_head' . $this->lang, $options_array, 86400 );
			}
		}

		$options_array = array_merge( array(
			'str_about'   => esc_html__( 'About', 'ecommerce-notification' ),
			'str_ago'     => esc_html__( 'ago', 'ecommerce-notification' ),
			'str_day'     => esc_html__( 'day', 'ecommerce-notification' ),
			'str_days'    => esc_html__( 'days', 'ecommerce-notification' ),
			'str_hour'    => esc_html__( 'hour', 'ecommerce-notification' ),
			'str_hours'   => esc_html__( 'hours', 'ecommerce-notification' ),
			'str_min'     => esc_html__( 'minute', 'ecommerce-notification' ),
			'str_mins'    => esc_html__( 'minutes', 'ecommerce-notification' ),
			'str_secs'    => esc_html__( 'secs', 'ecommerce-notification' ),
			'str_few_sec' => esc_html__( 'a few seconds', 'ecommerce-notification' ),
		),
			$options_array );
		/*Notification options*/
		$initial_delay        = $this->settings::get_field( 'initial_delay' );
		$initial_delay_random = $this->settings::get_field( 'initial_delay_random' );
		if ( $initial_delay_random ) {
			$initial_delay_min = $this->settings::get_field( 'initial_delay_min' );
			$initial_delay     = rand( $initial_delay_min, $initial_delay );
		}
		$options_array['initial_delay'] = $initial_delay;
		if ( empty( $options_array['detect'] ) && $this->settings::get_field( 'ipfind_auth_key' ) ) {
			$options_array['detect']  = 1;
			$detect_data              = $this->detect_country();
			$options_array['country'] = $detect_data['country'] ?? '';
			$options_array['cities']  = $detect_data['city'] ?? '';

			$names = $this->get_name_by_country( $detect_data['country_code'] ?? '' );

			if ( ! empty( $names ) ) {
				$options_array['names'] = $names;
			}
		}
		/*Load products*/
		if ( $non_ajax ) {
			$options_array['ajax_url'] = '';
			$products                  = $this->get_product();
		} else {
			$options_array['ajax_url'] = admin_url( 'admin-ajax.php' );
			$products                  = array();
		}
		if ( is_array( $products ) && count( $products ) ) {
			$options_array['products'] = $products;
		}
		wp_localize_script( 'ecommerce-notification', '_vi_ecommerce_notification_params', $options_array );
		$highlight_color  = $this->settings::get_field( 'highlight_color' );
		$text_color       = $this->settings::get_field( 'text_color' );
		$background_color = $this->settings::get_field( 'background_color' );
		$custom_css       = "
                #message-purchased{
                        background-color: {$background_color} !important;
                        color:{$text_color} !important;
                }
                 #message-purchased a{
                        color:{$highlight_color} !important;
                }
                ";
		$background_image = $this->settings::get_field( 'background_image' );
		if ( $background_image ) {
			$border_radius        = 0;
			$background_image_url = vi_ecommerce_notification_background_images( $background_image );

			$custom_css .= "#message-purchased.wn-extended::before{
				background-image: url('{$background_image_url}');  
				 border-radius:{$border_radius};
			}";
		}
		$custom_css .= $this->settings::get_field( 'custom_css', '' );
		wp_add_inline_style( 'ecommerce-notification', $custom_css );
	}

	public function get_name_by_country( $country ) {
		if ( ! $country ) {
			return false;
		}

		$name_by_country = $this->settings::get_field( 'name_by_country' );

		if ( empty( $name_by_country ) || ! is_array( $name_by_country ) ) {
			return false;
		}

		$names = '';
		foreach ( $name_by_country as $rule ) {
			if ( empty( $rule['countries'] ) ) {
				continue;
			}

			if ( in_array( $country, $rule['countries'] ) && ! empty( $rule['names'] ) ) {
				$names = $rule['names'];
				break;
			}

		}

		if ( ! empty( $names ) ) {
			$names = explode( "\n", $names );
			$names = array_filter( $names );
			shuffle( $names );
			$names = array_slice( $names, 0, 50 );
			$names = array_map( 'base64_encode', $names );
		}

		return $names;
	}

	/**
	 * Get virtual names
	 *
	 * @param int $limit
	 *
	 * @return array|mixed|void
	 */
	public function get_names( $limit = 0 ) {
		$virtual_name = $this->get_virtual_name();

		if ( $virtual_name ) {
			$virtual_name = explode( "\n", $virtual_name );
			$virtual_name = array_filter( $virtual_name );
			if ( $limit ) {
				if ( count( $virtual_name ) > $limit ) {
					shuffle( $virtual_name );

					return array_slice( $virtual_name, 0, $limit );
				}
			}
		}

		return $virtual_name;
	}

	/**
	 * Get virtual cities
	 *
	 * @param int $limit
	 *
	 * @return array|mixed|void
	 */
	public function get_cities( $limit = 0 ) {
		/*Change city*/
		$city = $this->get_virtual_city();
		if ( $city ) {
			$city = explode( "\n", $city );
			$city = array_map( 'trim', $city );
			$city = array_filter( $city );
			if ( $limit ) {
				if ( count( $city ) > $limit ) {
					shuffle( $city );

					return array_slice( $city, 0, $limit );
				}
			}
		}

		return $city;
	}

	/**
	 * Detect country and city
	 *
	 * @return array
	 */
	protected function detect_country() {
		$ip = isset( $_COOKIE['ip'] ) ? sanitize_text_field( $_COOKIE['ip'] ) : '';
		if ( $ip || isset( $_COOKIE['ip'] ) ) {
			$data['city'] = isset( $_COOKIE['city'] ) ? sanitize_text_field( $_COOKIE['city'] ) : '';
			if ( ! $data['city'] && isset( $_COOKIE['city'] ) ) {
				$data['city'] = sanitize_text_field( $_COOKIE['city'] );
			}
			$data['country'] = isset( $_COOKIE['country'] ) ? sanitize_text_field( $_COOKIE['country'] ) : '';
			if ( ! $data['country'] && isset( $_COOKIE['country'] ) ) {
				$data['country'] = sanitize_text_field( $_COOKIE['country'] );
			}
		} else {
			$ip = $this->getIP();
//			$ip='14.190.52.110';//test
			if ( $ip ) {
				$data = $this->geoCheckIP( $ip );
			} else {
				$data = array();
			}
		}

		return $data;
	}

	/**
	 * Get ip of client
	 *
	 * @return mixed ip of client
	 */
	protected function getIP() {
		if ( getenv( 'HTTP_CLIENT_IP' ) ) {
			$ipaddress = getenv( 'HTTP_CLIENT_IP' );
		} else if ( getenv( 'HTTP_X_REAL_IP' ) ) {
			$ipaddress = getenv( 'HTTP_X_REAL_IP' );
		} else if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
		} else if ( getenv( 'HTTP_X_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED' );
		} else if ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
		} else if ( getenv( 'HTTP_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED' );
		} else if ( getenv( 'REMOTE_ADDR' ) ) {
			$ipaddress = getenv( 'REMOTE_ADDR' );
		} else {
			$ipaddress = 0;
		}


		return $ipaddress;
	}

	/**
	 * Get an array with geoip-infodata
	 *
	 * @param $ip
	 *
	 * @return bool
	 */
	protected function geoCheckIP( $ip ) {
		$params   = new ECOMMERCE_NOTIFICATION_Admin_Settings();
		$auth_key = $params->get_field( 'ipfind_auth_key' );
		if ( ! $auth_key ) {
			return false;
		}
		//check, if the provided ip is valid
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			throw new InvalidArgumentException( "IP is not valid" );
		}

		//contact ip-server
		@$response = wp_remote_request( apply_filters( 'viwpen_url_geo_check_ip', 'https://ipfind.co?ip=' . $ip . '&auth=' . trim( $auth_key ) ) );
		if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
			$response = json_decode( $response['body'], true );
		} else {
			return false;
			throw new InvalidArgumentException( "Error contacting Geo-IP-Server" );
		}

		$ipInfo["city"]         = $response['city'] ?? '';
		$ipInfo["country"]      = $response['country'] ?? '';
		$ipInfo["country_code"] = $response['country_code'] ?? '';

		return $ipInfo;
	}
}