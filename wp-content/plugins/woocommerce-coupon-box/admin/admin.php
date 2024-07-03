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

class VI_WOOCOMMERCE_COUPON_BOX_Admin_Admin {
	protected $settings;
	protected $mailchimp;
	protected $activecampaign;
	protected $sendgrid;

	function __construct() {

		register_activation_hook( __FILE__, array( $this, 'install' ) );
		add_filter(
			'plugin_action_links_woocommerce-coupon-box/woocommerce-coupon-box.php', array(
				$this,
				'settings_link'
			)
		);
		$this->settings = new VI_WOOCOMMERCE_COUPON_BOX_DATA();
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_filter( 'manage_wcb_posts_columns', array( $this, 'add_column' ), 10, 1 );
		add_action( 'manage_wcb_posts_custom_column', array( $this, 'add_column_data' ), 10, 2 );
		/*filter email by campaign*/
		add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
		add_action( 'parse_query', array( $this, 'parse_query' ) );
	}

	public function add_column( $columns ) {
		$columns['campaign']     = __( 'Email campaign', 'woocommerce-coupon-box' );
		$columns['name']         = __( 'First name', 'woocommerce-coupon-box' );
		$columns['lname']        = __( 'Last name', 'woocommerce-coupon-box' );
		$columns['mobile']       = __( 'Mobile', 'woocommerce-coupon-box' );
		$columns['birthday']     = __( 'Birthday', 'woocommerce-coupon-box' );
		$columns['gender']       = __( 'Gender', 'woocommerce-coupon-box' );
		$columns['additional']   = __( 'Custom info', 'woocommerce-coupon-box' );
		$columns['coupon']       = __( 'Given coupon', 'woocommerce-coupon-box' );
		$columns['coupon_value'] = __( 'Coupon value', 'woocommerce-coupon-box' );
		$columns['expire']       = __( 'Expiry date', 'woocommerce-coupon-box' );
		$columns['ip_address']   = __( 'IP Address', 'woocommerce-coupon-box' );
//		$columns['mailchimp']      = __( 'Mailchimp list', 'woocommerce-coupon-box' );
//		$columns['activecampaign'] = __( 'ActiveCampaign list', 'woocommerce-coupon-box' );
//		$columns['sendgrid']       = __( 'SendGrid list', 'woocommerce-coupon-box' );

		return $columns;
	}

	public function add_column_data( $column, $post_id ) {
		$meta         = get_post_meta( $post_id, 'woo_coupon_box_meta', true );
		$coupon_code  = isset( $meta['coupon'] ) ? $meta['coupon'] : '';
		$coupon_value = '';
		$expire       = '';
		$left         = '';
		if ( $coupon_code ) {
			$coupon = new WC_Coupon( $coupon_code );
			if ( $coupon ) {
				$coupon_code = $coupon->get_code();
				if ( $coupon->get_discount_type() == 'percent' ) {
					$coupon_value = $coupon->get_amount() . '%';
				} else {
					$coupon_value = $this->wc_price( $coupon->get_amount() );
				}
				if ( $coupon->get_date_expires() ) {
					$date_expire = $coupon->get_date_expires();
					$left        = $date_expire->getTimestamp() - current_time( 'timestamp', true );
					$expire      = date( get_option( 'date_format' ), strtotime( $date_expire ) );
				}
			}
		}
		switch ( $column ) {
			case 'campaign':
				if ( $meta ) {
					if ( isset( $meta['campaign'] ) ) {
						$campaign = get_term_by( 'id', $meta['campaign'], 'wcb_email_campaign' );
						echo $campaign->name;
					}
				} else {
					$term_ids = get_the_terms( $post_id, 'wcb_email_campaign' );
					if ( is_array( $term_ids ) && count( $term_ids ) ) {
						foreach ( $term_ids as $term_id ) {
							echo $term_id->name;
						}
					}
				}
				break;
			case 'name':
				echo isset( $meta['name'] ) ? $meta['name'] : '';
				break;
			case 'lname':
				echo isset( $meta['lname'] ) ? $meta['lname'] : '';
				break;
			case 'mobile':
				echo isset( $meta['mobile'] ) ? $meta['mobile'] : '';
				break;
			case 'birthday':
				echo isset( $meta['birthday'] ) ? $meta['birthday'] : '';
				break;
			case 'gender':
				echo isset( $meta['gender'] ) ? $meta['gender'] : '';
				break;
			case 'additional':
				echo isset( $meta['additional'] ) ? $meta['additional'] : '';
				break;
			case 'coupon':
				echo $coupon_code;
				break;
			case 'coupon_value':
				echo $coupon_value;
				break;
			case 'expire':
				echo $expire;
				break;
			case 'ip_address':
				if ( isset( $meta['ip_address'] ) ) {
					$user_geo = \WC_Geolocation::geolocate_ip( $meta['ip_address'] );

					$from_country = "<div class='wcb-etb-from'>
                            <div class='wcb-etb-country-flag-group wcb-etb-inline-block'>";
					if ( isset( $user_geo['country'] ) ) {
						$country_code = $user_geo['country'];
						$country_name = isset( WC()->countries->countries[ $country_code ] ) ? WC()->countries->countries[ $country_code ] : '';
					} else {
						$country_name = '';
					}
					$from_country .= "<div class='wcb-etb-from-detail'>
                                    <p>" . esc_html( $country_name ) . "</p>
                                    <p><a href='https://tools.keycdn.com/geo?host=" . esc_html( $meta['ip_address'] ) .
					                 "' target='_blank'>" . esc_html( $meta['ip_address'] ) . "</a></p>
                                </div>
                            </div>
                        </div>";

					echo wp_kses_post( $from_country );
				}
				break;
			case 'mailchimp':
				if ( isset( $meta['mailchimp'] ) && $meta['mailchimp'] ) {
					if ( ! $this->mailchimp ) {
						$this->mailchimp = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Mailchimp();
					}
					$mailchimp_list = $this->mailchimp->get_list( $meta['mailchimp'] );
					echo isset( $mailchimp_list->name ) ? $mailchimp_list->name : '';
				}
				break;
			case 'activecampaign':
				if ( isset( $meta['activecampaign'] ) && $meta['activecampaign'] ) {
					if ( ! $this->activecampaign ) {
						$this->activecampaign = new VI_WOOCOMMERCE_COUPON_BOXP_Admin_Active_Campaign();

					}
					$activecampaign_list = $this->activecampaign->list_view( $meta['activecampaign'] );
					echo isset( $activecampaign_list['name'] ) ? $activecampaign_list['name'] : '';
				}
				break;
			case 'sendgrid':
				if ( isset( $meta['sendgrid'] ) && $meta['sendgrid'] ) {
					if ( ! $this->sendgrid ) {
						$this->sendgrid = new VI_WOOCOMMERCE_COUPON_BOX_Admin_Sendgrid();
					}
					$sendgrid_list = $this->sendgrid->get_list( $meta['sendgrid'] );
					echo isset( $sendgrid_list->name ) ? $sendgrid_list->name : '';
				}
				break;
		}
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

	public function restrict_manage_posts() {
		global $typenow;
		$post_type = 'wcb'; // change to your post type
		$taxonomy  = 'wcb_email_campaign'; // change to your taxonomy
		if ( $typenow == $post_type ) {
			$selected      = isset( $_GET[ $taxonomy ] ) ? $_GET[ $taxonomy ] : '';
			$info_taxonomy = get_taxonomy( $taxonomy );
			wp_dropdown_categories( array(
				'show_option_all' => __( "Show All {$info_taxonomy->label}" ),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'show_count'      => true,
				'hide_empty'      => true,
			) );
		};
	}

	public function parse_query( $query ) {
		global $pagenow;
		$post_type = 'wcb'; // change to your post type
		$taxonomy  = 'wcb_email_campaign'; // change to your taxonomy
		$q_vars    = &$query->query_vars;
		if ( $pagenow == 'edit.php' && isset( $q_vars['post_type'] ) && $q_vars['post_type'] == $post_type && isset( $q_vars[ $taxonomy ] ) && is_numeric( $q_vars[ $taxonomy ] ) && $q_vars[ $taxonomy ] != 0 ) {
			$term                = get_term_by( 'id', $q_vars[ $taxonomy ], $taxonomy );
			$q_vars[ $taxonomy ] = $term->slug;
		}
	}

	/**
	 * Update hidden note
	 */
	public function admin_init() {
		$current_time = current_time( 'timestamp' );
		$hide         = filter_input( INPUT_GET, 'wcb_hide', FILTER_SANITIZE_NUMBER_INT );
		if ( $hide ) {
			update_option( 'wcb_note', 0 );
			update_option( 'wcb_note_time', $current_time );
		}

		$time_off = get_option( 'wcb_note_time' );
		if ( ! $time_off ) {
			update_option( 'wcb_note', 1 );
		} else {
			$time_next = $time_off + 30 * 24 * 60 * 60;
			if ( $time_next < $current_time ) {
				update_option( 'wcb_note', 1 );
			}
		}

	}

	/**
	 * Link to Settings
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	function settings_link( $links ) {
		$settings_link = '<a href="edit.php?post_type=wcb&page=woocommerce_coupon_box" title="' . __( 'Settings', 'woocommerce-coupon-box' ) . '">' . __( 'Settings', 'woocommerce-coupon-box' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * When active plugin Function will be call
	 */
	public function install() {
		global $wp_version;
		If ( version_compare( $wp_version, "2.9", "<" ) ) {
			deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
			wp_die( "This plugin requires WordPress version 2.9 or higher." );
		}
	}

	/**
	 * Function init when run plugin+
	 */
	public function init() {
		/*Register taxonomy for post type*/
		$this->register_taxonomy();

		/*Register post type*/
		$this->register_post_type();
		load_plugin_textdomain( 'woocommerce-coupon-box' );
		$this->load_plugin_textdomain();

		if ( class_exists( 'VillaTheme_Support_Pro' ) ) {
			new VillaTheme_Support_Pro(
				array(
					'support'   => 'https://villatheme.com/supports/forum/plugins/woocommerce-coupon-box/',
					'docs'      => 'http://docs.villatheme.com/?item=woo-coupon-box',
					'review'    => 'https://codecanyon.net/downloads',
					'css'       => VI_WOOCOMMERCE_COUPON_BOX_CSS,
					'image'     => VI_WOOCOMMERCE_COUPON_BOX_IMAGES,
					'slug'      => 'woocommerce-coupon-box',
					'menu_slug' => 'edit.php?post_type=wcb',
					'version'   => VI_WOOCOMMERCE_COUPON_BOX_VERSION,
				)
			);
		}
	}

	/** Register taxonomy*/
	protected function register_taxonomy() {
		if ( taxonomy_exists( 'wcb_email_campaign' ) ) {
			return;
		}
		register_taxonomy(
			'wcb_email_campaign',
			'wcb',
			array(
				'hierarchical' => true,
				'label'        => 'Email Campaign',
				'public'       => false,
				'rewrite'      => false,
				'show_ui'      => true,
			)
		);

		if ( ! term_exists( 'Uncategorized', 'wcb_email_campaign' ) ) {
			wp_insert_term(
				'Uncategorized',
				'wcb_email_campaign',
				array(
					'description' => '',
					'slug'        => 'uncategorized'
				)
			);
		}
	}

	/**
	 * Register post type email
	 */
	protected function register_post_type() {
		if ( post_type_exists( 'wcb' ) ) {
			return;
		}
		$labels = array(
			'name'               => _x( 'Email', 'woocommerce-coupon-box' ),
			'singular_name'      => _x( 'Email', 'woocommerce-coupon-box' ),
			'menu_name'          => _x( 'Woo Coupon Box', 'Admin menu', 'woocommerce-coupon-box' ),
			'name_admin_bar'     => _x( 'Email', 'Add new on Admin bar', 'woocommerce-coupon-box' ),
			'add_new'            => _x( 'Add New Subscribe', 'role', 'woocommerce-coupon-box' ),
			'add_new_item'       => __( 'Add New Email Subscribe', 'woocommerce-coupon-box' ),
			'new_item'           => __( 'New Email', 'woocommerce-coupon-box' ),
			'edit_item'          => __( 'Edit Email', 'woocommerce-coupon-box' ),
			'view_item'          => __( 'View Email', 'woocommerce-coupon-box' ),
			'all_items'          => __( 'Email Subscribe', 'woocommerce-coupon-box' ),
			'search_items'       => __( 'Search Email', 'woocommerce-coupon-box' ),
			'parent_item_colon'  => __( 'Parent Email:', 'woocommerce-coupon-box' ),
			'not_found'          => __( 'No Email found.', 'woocommerce-coupon-box' ),
			'not_found_in_trash' => __( 'No Email found in Trash.', 'woocommerce-coupon-box' )
		);
		$args   = array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'email-subscribe' ),
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => false,
			),
			'map_meta_cap'        => true,
			'has_archive'         => false,
			'taxonomies'          => array( 'wcb_email_campaign' ),
			'hierarchical'        => false,
			'menu_position'       => 2,
			'supports'            => array( 'title' ),
			'menu_icon'           => "dashicons-products",
			'exclude_from_search' => true,
		);
		register_post_type( 'wcb', $args );
	}

	/**
	 * load Language translate
	 */
	function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-coupon-box' );
		// Admin Locale
		if ( is_admin() ) {
			load_textdomain( 'woocommerce-coupon-box', VI_WOOCOMMERCE_COUPON_BOX_LANGUAGES . "woocommerce-coupon-box-$locale.mo" );
		}

		// Global + Frontend Locale
		load_textdomain( 'woocommerce-coupon-box', VI_WOOCOMMERCE_COUPON_BOX_LANGUAGES . "woocommerce-coupon-box-$locale.mo" );
		load_plugin_textdomain( 'woocommerce-coupon-box', false, VI_WOOCOMMERCE_COUPON_BOX_LANGUAGES );
	}
}