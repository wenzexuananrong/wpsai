<?php

namespace VIWEC\INCLUDES;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Render {

	protected static $instance = null;
	public $preview;
	public $demo;
	public $sent_to_admin;
	public $render_data = [];
	public $plain_text;
	public $template_args;
	public $order;
	public $other_message_content;
	public $class_email;
	public $use_default_template;
	public $recover_heading;
	public $custom_css;
	public $check_rendered;
	protected $template_id;
	protected $direction;
	protected $props;
	protected $order_currency;
	protected $user;
	protected $email_id;
	protected $use_hook_order_download;

	public function __construct( $data ) {

		$data = wp_parse_args( $data, [ 'template_id' => '' ] );

		$this->template_id = $data['template_id'];

		add_action( 'viwec_render_content', [ $this, 'render_content' ], 10, 4 );
		add_filter( 'gettext', [ $this, 'recover_text' ], 10, 3 );
		add_action( 'viwec_order_item_parts', [ $this, 'order_download' ], 10, 4 );
		add_filter( 'woocommerce_order_shipping_to_display_shipped_via', [ $this, 'remove_shipping_method' ] );

		remove_filter( 'woocommerce_order_item_name', 'besa_woocommerce_order_item_name', 10 );

		if ( class_exists( 'WC_customer_email_verification_email' ) ) {
			add_action( 'viwec_woocommerce_email_footer', [ \WC_customer_email_verification_email::get_instance(), 'append_content_before_woocommerce_footer' ] );
		}

		add_filter( 'safe_style_css', [ $this, 'safe_style_css' ] );

		add_shortcode( 'wec_order_meta', [ $this, 'shortcode' ] );

		add_filter( 'woocommerce_order_item_get_formatted_meta_data', [ $this, 'remove_wpautop' ], 20 );


		//Fix deposit missing payment method
//		if ( method_exists( $order, 'get_payment_method' ) ) {
//			$payment_method = $order->get_payment_method();
//			$gateways       = WC_Payment_Gateways::instance()->payment_gateways();
//			if ( isset( $gateways[ $payment_method ] ) ) {
//				$rows['payment_method']['value'] = $gateways[ $payment_method ]->get_title();
//			}
//		}

	}

	public static function init( $data = [] ) {
		if ( null == self::$instance ) {
			self::$instance = new self( $data );
		}

		return self::$instance;
	}

	public function shortcode( $args ) {
		$args                = wp_parse_args( $args, [ 'key' => '', 'meta_customer_order' => '' ] );
		$meta_key            = $args['key'];
		$meta_customer_order = $args['meta_customer_order'];
		$meta_value          = '';
		if ( $this->order && $meta_key ) {
			if ( $meta_customer_order === 'yes' ) {
				$user = $this->order->get_user();
				if ( $user ) {
					$meta_value = get_user_meta( $user->ID, $meta_key, true );
				}
			} else {
				$meta_value = get_post_meta( $this->order->get_id(), $meta_key, true );
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


	public function recover_text( $translation, $text, $domain ) {
		if ( in_array( $text, [ 'Order fully refunded.', 'Refund' ] ) ) {
			$translation = $text;
		}

		return $translation;
	}

	public function set_object( $email ) {
		$this->class_email = $email;
		$this->email_id    = $email->id;
		$object            = $email->object;
		if ( is_a( $object, 'WC_Order' ) ) {
			$this->order          = $object;
			$this->order_currency = $this->order->get_currency();
		} elseif ( is_a( $object, 'WP_User' ) ) {
			$this->user = $email;
		}
	}

	public function set_user( $user ) {
		$this->user = $user;
	}

	public function order( $order_id ) {
		if ( $order_id ) {
			$this->order = wc_get_order( $order_id );
			if ( $this->order ) {
				$this->order_currency = $this->order->get_currency();
			}
		}
	}

	public function demo_order() {
		$this->demo = true;

		$order = new \WC_Order();
		$order->set_id( 123456 );
		$order->set_billing_first_name( 'John' );
		$order->set_billing_last_name( 'Doe' );
		$order->set_billing_email( 'johndoe@domain.com' );
		$order->set_billing_country( 'US' );
		$order->set_billing_city( 'Azusa' );
		$order->set_billing_state( 'NY' );
		$order->set_payment_method( 'paypal' );
		$order->set_payment_method_title( 'Paypal' );
		$order->set_billing_postcode( 10001 );
		$order->set_billing_phone( '0123456789' );
		$order->set_billing_address_1( 'Ap #867-859 Sit Rd.' );
		$order->set_shipping_total( 10 );
		$order->set_total( 60 );
		$this->order = $order;
	}

	public function demo_new_user() {
		$user             = new \WP_User();
		$user->user_login = 'johndoe';
		$user->user_pass  = '$P$BKpFUPNogZw6kAv/dMrk6CjSmlFI8l0';
		$this->user       = $user;
	}

	public function parse_styles( $data ) {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return $data;
		}

		$style      = '';
		$ignore_arr = [
			'border-top-width',
			'border-right-width',
			'border-bottom-width',
			'border-left-width',
			'border-style',
			'border-color',
			'border-top-color',
			'border-width',
		];

		foreach ( $data as $key => $value ) {
			if ( in_array( $key, $ignore_arr ) ) {
				continue;
			}

			$style .= "{$key}:{$value};";
		}

		$border_style = $data['border-style'] ?? 'solid';
		$border_color = $data['border-color'] ?? ( $data['border-top-color'] ?? 'transparent' );

		if ( isset( $data['border-top-width'] ) ) {
			foreach ( [ 'top', 'right', 'bottom', 'left' ] as $pos ) {
				$style .= ! isset( $data["border-{$pos}-width"] ) || $data["border-{$pos}-width"] === '0px'
					? "border-{$pos}:0 hidden;"
					: "border-{$pos}:{$data["border-{$pos}-width"]} {$border_style} {$border_color};";
			}
		} elseif ( isset( $data['border-width'] ) ) {
			$border_width = $data['border-width'];
			if ( $border_width !== '0px' ) {
				$style .= " border-width: {$border_width}; ";
				$style .= " border-style: {$border_style}; ";
				$style .= " border-color: {$border_color}; ";
			} else {
				$style .= " border:0 hidden; ";
			}
		}

		return esc_attr( $style );
	}

	public function safe_style_css( $styles ) {
		$styles[] = 'display';
		$styles[] = 'direction';

		return $styles;
	}

	public function render( $data ) {
		$this->check_rendered = true;
		add_filter( 'woocommerce_email_styles', array( $this, 'custom_style' ), 9999 );

		$bg_style   = '';
		$width      = 600;
		$responsive = 414;

		if ( isset( $data['style_container'] ) ) {
			$width      = ! empty( $data['style_container']['width'] ) ? $data['style_container']['width'] : $width;
			$responsive = ! empty( $data['style_container']['responsive'] ) ? $data['style_container']['responsive'] : $responsive;
			unset( $data['style_container']['width'] );
			unset( $data['style_container']['responsive'] );
			$bg_style = isset( $data['style_container'] ) ? $this->parse_styles( $data['style_container'] ) : '';
		}

		$this->direction = get_post_meta( $this->template_id, 'viwec_settings_direction', true );

		if ( $this->preview ) {
			$this->direction = isset( $_POST['direction'] ) ? sanitize_text_field( $_POST['direction'] ) : 'ltr';
		}

		$this->email_header( $bg_style, $width, $responsive );
		ob_start();
		?>
        <div id="wrapper" style="box-sizing:border-box;padding:0;margin:0;">
            <table border="0" cellpadding="0" cellspacing="0" height="100%" align="center" width="100%" style="margin: 0;<?php echo esc_attr( $bg_style ); ?>">
                <tbody>
                <tr>
                    <td style="padding: 20px 10px;">
                        <table border="0" cellpadding="0" cellspacing="0" height="100%" align="center"
                               style="font-size: 15px; margin: 0 auto; padding: 0; border-collapse: collapse;font-family: Helvetica, Arial, sans-serif;">
                            <tbody>
                            <tr>
                                <td align="center" valign="top" id="body_content">
                                    <div class="viwec-responsive-min-width">
                                        <table align='center' width='100%' border='0' cellpadding='0' cellspacing='0' border="0">
											<?php
											if ( ! empty( $data['rows'] ) && is_array( $data['rows'] ) ) {
												foreach ( $data['rows'] as $row ) {
													if ( ! empty( $row ) && is_array( $row ) ) {
														$this->render_row( $row );
													} else {
														$this->custom_css .= get_post_meta( $row, 'viwec_custom_css', true );

														$block = get_post_meta( $row, 'viwec_email_structure', true );
														$block = json_decode( html_entity_decode( $block ), true );
														$rows  = $block['rows'] ?? '';

														if ( ! empty( $rows ) && is_array( $rows ) ) {
															foreach ( $rows as $item_row ) {
																$this->render_row( $item_row );
															}
														}
													}
												}
											} ?>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
		<?php
		$email_body = ob_get_clean();

		$email_body_minify = Utils::minify_html( $email_body);
		echo '<div class="viwec-ios-preview-hidden" style="display: none;color: #ffffff;size: 2pt;">' . strip_tags($email_body_minify ) . '</div>';
		echo $email_body;
		$this->email_footer();
		if ( class_exists( 'WooCommerce_Germanized' ) ) {
			add_action( 'woocommerce_email_order_details', [ \WC_Emails::instance(), 'order_downloads' ], 10 );
			add_action( 'woocommerce_email_order_details', [ \WC_Emails::instance(), 'order_details' ], 10 );
		}
	}

	public function render_row( $row ) {
		$row_outer_style           = ! empty( $row['props']['style_outer'] ) ? $this->parse_styles( $row['props']['style_outer'] ) : '';
		$left_align                = $this->direction == 'rtl' ? 'right' : 'left';
		$background_image_property = '';
		/*Compatible with Outlook >2013*/
		if ( key_exists( 'background-image', $row['props']['style_outer'] ) && ! empty( $row['props']['style_outer']['background-image'] ) ) {
			$background_image_property_url = str_replace( 'url(', '', $row['props']['style_outer']['background-image'] );
			$background_image_property_url = str_replace( ')', '', $background_image_property_url );
			$background_image_property     = "background=$background_image_property_url";
		}
		?>
        <tr>
            <td class="viwec-row" valign='top' <?php echo esc_attr( $background_image_property ) ?> width='100%' style='<?php echo esc_attr( $row_outer_style ) ?>'>
                <table align='center' width='100%' border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse;margin: 0; padding:0'>
                    <tr>
                        <td valign='top' width='100%' class='viwec-responsive-padding viwec-inline-block' border='0' cellpadding='0' cellspacing='0'
                            style='width: 100%; font-size: 0 !important;border-collapse: collapse;margin: 0; padding:0; '>
							<?php
							$end_array = array_keys( $row );
							$end_array = end( $end_array );

							if ( ! empty( $row['cols'] && is_array( $row['cols'] ) ) ) {
								$arr_key    = array_keys( $row['cols'] );
								$start      = current( $arr_key );
								$end        = end( $arr_key );
								$col_number = count( $row['cols'] );

								$width = ( 100 / $col_number ) . '%';

								foreach ( $row['cols'] as $key => $col ) {
									$col_style = ! empty( $col['props']['style'] ) ? $this->parse_styles( $col['props']['style'] ) : '';

									if ( $start == $key ) { ?>
                                        <!--[if (gte mso 9)|(IE)]>
                                        <style>table.viwec-responsive {
                                            width: 100%;
                                        }</style>
                                        <table align="center" width="100%" role="presentation" border="0" cellpadding="0"
                                               cellspacing="0" style="border-collapse: collapse;">

                                            <tr>
                                                <td valign='top' style="vertical-align:top;width:<?php echo esc_attr( $width ) ?>;">
                                        <![endif]-->
									<?php } ?>

                                    <table align="<?php echo esc_attr( $left_align ) ?>" width="<?php echo esc_attr( $width ) ?>" class="viwec-responsive" border="0" cellpadding="0" cellspacing="0"
                                           style='display:inline-block;margin:0; padding:0;border-collapse: collapse; <!--[if (gte mso 9)|(IE)]>width:100%;<![endif]-->'>
                                        <tr>
                                            <td>
                                                <table width='100%' align='left' border='0' cellpadding='0' cellspacing='0' style='margin:0; padding:0;border-collapse: separate;width: 100%'>
                                                    <tr>
                                                        <td class="viwec-column" valign='top' width='100%' style='line-height: 1.5;box-sizing: border-box;<?php echo esc_attr( $col_style ) ?>'>
															<?php
															if ( ! empty( $col['elements'] && is_array( $col['elements'] ) ) ) {
																foreach ( $col['elements'] as $el ) {
																	$type = isset( $el['type'] ) ? str_replace( '/', '_', $el['type'] ) : '';

																	if ( $type == 'html_recover_heading' && $this->use_default_template && ! $this->recover_heading && ! $this->class_email ) {
																		continue;
																	}

																	$content_style = isset( $el['style'] ) ? $this->parse_styles( str_replace( "'", '', $el['style'] ) ) : '';
																	$el_style      = ! empty( $el['props']['style'] ) ? $this->parse_styles( str_replace( "'", '', $el['props']['style'] ) ) : '';

																	?>
                                                                    <table align='center' width='100%' border='0' cellpadding='0' cellspacing="0" style='border-collapse: collapse;'>
                                                                        <tr>
                                                                            <td valign='top' style='<?php echo esc_attr( $el_style ); ?>'>
                                                                                <table class="<?php echo esc_attr( $type ) ?>" align='center' width='100%' border='0' cellpadding='0' cellspacing="0" style='border-collapse:
                                                                                                separate;'>
                                                                                    <tr>
                                                                                        <td valign='top' dir="<?php echo esc_attr( $this->direction ) ?>"
                                                                                            style='font-size: 15px;<?php echo esc_attr( $content_style ) ?>'>
																							<?php
																							$this->props = $el;
																							do_action( 'viwec_render_content', $type, $el, $this, $el['style'] );
																							?>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
																	<?php
																}
															}
															?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
									<?php
									if ( $end == $key ) {
										?>
                                        <!--[if (gte mso 9)|(IE)]></td></tr></table><![endif]-->
										<?php
									} else {
										?>
                                        <!--[if (gte mso 9)|(IE)]></td>
                                        <td valign='top' style="vertical-align:top;width:<?php echo esc_attr( $width ) ?>;"><![endif]-->
										<?php
									}
								}
							} ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
		<?php
	}

	public function email_header( $bg_style, $width, $responsive ) {
		wc_get_template( 'email-header.php',
			[ 'bg_style' => $bg_style, 'width' => $width, 'responsive' => $responsive, 'direction' => $this->direction ],
			'',
			VIWEC_TEMPLATES );
	}

	public function email_footer() {
        if(class_exists('EMTMPL_Email_Templates_Designer')){
            ?>
            <div style="width: 0; font-size: 0;opacity: 0;overflow:hidden;visibility: hidden;color:transparent;">{{ignore_9mail}}</div>
	        <?php
        }
		?>
        </body></html>
		<?php
	}

	public function render_content( $type, $props, $_this, $arr_style = '' ) {
		$func = 'render_' . $type;
		if ( method_exists( $this, $func ) ) {
			$this->$func( $props, $arr_style );
		}
	}

	public function custom_style() {
		return $this->custom_css ? $this->custom_css : '';
	}

	public function replace_shortcode( $text ) {
		$object = '';

		if ( $this->order ) {
			$object = $this->order;
		} elseif ( $this->user ) {
			$object = $this->user;
		}

		$text = Utils::replace_shortcode( $text, $this->template_args, $object, $this->preview );

		return $text;
	}

	public function render_html_image( $props, $arr_style ) {
		$src      = isset( $props['attrs']['src'] ) ? $props['attrs']['src'] : '';
		$width    = isset( $props['childStyle']['img'] ) ? $this->parse_styles( $props['childStyle']['img'] ) : '';
		$ol_width = ! empty( $props['childStyle']['img']['width'] ) ? str_replace( 'px', '', $props['childStyle']['img']['width'] ) : '100%';
		$href     = ! empty( $props['attrs']['data-href'] ) ? $props['attrs']['data-href'] : '';
		$alt      = ! empty( $props['attrs']['data-alt'] ) ? $props['attrs']['data-alt'] : '';

		if ( $href ) {
			?>
            <a href="<?php echo esc_attr( $href ) ?>" target="_blank">
                <img alt="<?php echo esc_attr( $alt ); ?>"
                     width="<?php echo esc_attr( $ol_width ) ?>"
                     src='<?php echo esc_url( $src ) ?>' max-width='100%'
                     style='max-width: 100%;vertical-align: middle;<?php echo esc_attr( $width ) ?>'/>
            </a>
			<?php
		} else {
			?>
            <img alt="<?php echo esc_attr( $alt ); ?>"
                 width="<?php echo esc_attr( $ol_width ) ?>"
                 src='<?php echo esc_url( $src ) ?>' max-width='100%'
                 style='max-width: 100%;vertical-align: middle;<?php echo esc_attr( $width ) ?>'/>
			<?php
		}
	}

	public function render_html_text( $props, $arr_style ) {
		$center_on_mobile = ! empty( $props['attrs']['data-center_on_mobile'] ) && $props['attrs']['data-center_on_mobile'] == 'true' ? 'viwec-center-on-mobile' : '';
		$content          = isset( $props['content']['text'] ) ? $props['content']['text'] : '';
		$content          = $this->replace_shortcode( $content );
		$content          = $this->custom_hook( $content );
		$content          = "<div class='text-item {$center_on_mobile}'>{$content}</div>";
		echo do_shortcode( $content );
	}

	public function custom_hook( $text ) {
		$exist_custom_hook = strpos( $text, '{customer_email_verification_plugin}' );
		if ( $exist_custom_hook !== false ) {
			$replace = [ '{customer_email_verification_plugin}' => $this->customer_email_verification_plugin() ];
			$text    = str_replace( array_keys( $replace ), array_values( $replace ), $text );
		}

		return $text;
	}

	public function customer_email_verification_plugin() {
		ob_start();
		do_action( 'viwec_woocommerce_email_footer', $this->class_email );
		$text = ob_get_clean();

		return $text;
	}

	public function render_html_order_detail( $props, $arr_style = [] ) {
		if ( $this->order ) {

			$temp    = ! empty( $props['attrs']['data-template'] ) ? $props['attrs']['data-template'] : 1;
			$preview = $this->demo ? 'pre-' : '';

			if ( $preview ) {
				$this->direction = isset( $_POST['direction'] ) ? sanitize_text_field( $_POST['direction'] ) : 'ltr';
			}

			if ( is_file( VIWEC_TEMPLATES . "order-items/{$preview}style-{$temp}.php" ) ) {
				if ( isset( $props['attrs']['use_hook_order_download'] ) && $props['attrs']['use_hook_order_download'] == 'true' ) {
					$this->use_hook_order_download = true;
				}
//				add_filter( 'woocommerce_order_item_get_formatted_meta_data', [ $this, 'remove_autop' ] );
				?>
                <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center'>
                    <tr>
                        <td valign='top' class="order-detail">
							<?php
							$sent_to_admin = $this->template_args['sent_to_admin'] ?? '';
							wc_get_template( "order-items/{$preview}style-{$temp}.php", [
								'order'               => $this->order,
								'items'               => $this->order->get_items(),
								'show_sku'            => $sent_to_admin,
								'show_download_links' => $this->order->is_download_permitted() && ! $sent_to_admin,
								'show_purchase_note'  => $this->order->is_paid() && ! $sent_to_admin,
								'props'               => $props,
								'render'              => $this,
								'direction'           => $this->direction,
							], 'email-template-customizer', VIWEC_TEMPLATES );
							?>
                        </td>
                    </tr>
                </table>
				<?php


//				remove_filter( 'woocommerce_order_item_get_formatted_meta_data', [ $this, 'remove_autop' ] );
			}
		}
	}

	public function remove_autop( $formatted_meta ) {
		if ( ! empty( $formatted_meta ) ) {

			foreach ( $formatted_meta as $id => $meta ) {
				$meta->display_value   = make_clickable( strip_tags( $meta->display_value ) );
				$formatted_meta[ $id ] = $meta;
			}
		}

		return $formatted_meta;
	}

	public function get_p_inherit_style( $props ) {

		$inherit_style = ! empty( $props['style'] ) ? $props['style'] : [];
		$font_weight   = $inherit_style['font-weight'] ?? 'inherit';
		$font_size     = $inherit_style['font-size'] ?? 'inherit';
		$line_height   = $inherit_style['line-height'] ?? 'inherit';
		$color         = $inherit_style['color'] ?? 'inherit';

		$p_style = [
			"font-weight:{$font_weight}",
			"font-size:{$font_size}",
			"line-height:{$line_height}",
			"color:{$color}",
		];

		return implode( ';', $p_style );
	}

	public function render_html_order_subtotal( $props, $arr_style = [] ) {
		$html = '';
		if ( $this->order ) {

			$left_style      = isset( $props['childStyle']['.viwec-td-left'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-left'] ) : 'text-align:left;';
			$right_style     = isset( $props['childStyle']['.viwec-td-right'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-right'] ) : 'text-align:right; width:40%;';
			$el_style        = isset( $props['childStyle']['.viwec-order-subtotal-style'] ) ? $this->parse_styles( $props['childStyle']['.viwec-order-subtotal-style'] ) : '';
			$remove_shipping = isset( $props['attrs']['data-remove_shipping'] ) && $props['attrs']['data-remove_shipping'] == 'true' ? true : false;
			$font_family     = $props['style']['font-family'] ?? 'inherit';

			$p_style = $this->get_p_inherit_style( $props );

			$item_totals = $this->order->get_order_item_totals();
			if ( ! empty( $item_totals ) && is_array( $item_totals ) ) {
				foreach ( $item_totals as $id => $item ) {

					if ( $remove_shipping && $id == 'shipping' ) {
						$shipping_total = $this->order->get_shipping_total();
						if ( ! $shipping_total ) {
							continue;
						}
					}

					switch ( $id ) {
						case 'cart_subtotal':
							$id = 'subtotal';
							break;
						default:
							break;
					}
					if ( apply_filters( 'viwec_hidden_subtotal_epmty_discount', __return_false() ) ) {
						if ( ( $id == 'subtotal' ) && ! isset( $item_totals['discount'] ) ) {
							continue;
						}
					}
					$label = str_replace( ':', '', $item['label'] ?? '' );

					if ( $label == 'Order fully refunded.' && ! empty( $props['content']['refund-full'] ) ) {
						$label = $props['content']['refund-full'];
					}

					if ( $label == 'Refund' && ! empty( $props['content']['refund-part'] ) ) {
						$label = $props['content']['refund-part'];
					}

					if ( in_array( $id, [ 'order_total', 'payment_method' ] ) ) {
						continue;
					}
					$p_style_label = apply_filters( 'subtotal_label_p_style', $p_style );
					$p_style_value = apply_filters( 'subtotal_value_p_style', $p_style );
					$text          = $props['content'][ $id ] ?? $label;
					$html          .= "<tr>";
					$html          .= "<td valign='top'  class='viwec-mobile-50 {$id}_label' style='font-family: {$font_family};{$el_style}{$left_style}'><p style='{$p_style_label}'>{$text}</p></td>";
					$html          .= "<td valign='top'  class='viwec-mobile-50 {$id}_value' style='font-family: {$font_family};{$el_style}{$right_style}'><p style='{$p_style_value}'>{$item['value']}</p></td>";
					$html          .= "</tr>";
				}
			}
		}

		$this->table( $html );
	}

	public function render_html_order_total( $props, $arr_style ) {
		$html = '';
		if ( $this->order ) {
			$left_style  = isset( $props['childStyle']['.viwec-td-left'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-left'] ) : 'text-align:left;';
			$right_style = isset( $props['childStyle']['.viwec-td-right'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-right'] ) : 'text-align:right; width:40%;';
			$el_style    = isset( $props['childStyle']['.viwec-order-total-style'] ) ? $this->parse_styles( $props['childStyle']['.viwec-order-total-style'] ) : '';
			$font_family = $props['style']['font-family'] ?? 'inherit';

			$trans_total = $props['content']['order_total'] ?? esc_html__( 'Total', 'viwec-email-template-customizer' );

			$p_style = $this->get_p_inherit_style( $props );

			$tax_display   = get_option( 'woocommerce_tax_display_cart' );
			$p_style_label = apply_filters( 'order_total_label_p_style', $p_style );
			$p_style_value = apply_filters( 'order_total_value_p_style', $p_style );
			$total_html    = "<tr><td valign='top' class='viwec-mobile-50' style='font-family: {$font_family};{$el_style}{$left_style}{$p_style}'><p style='{$p_style_label}'>{$trans_total}</p></td>";
			$total_html    .= "<td valign='top' class='viwec-mobile-50' style='font-family: {$font_family};{$el_style}{$right_style}'><p style='{$p_style_value}'>{$this->order->get_formatted_order_total($tax_display)}</p></td></tr>";

			$html .= $total_html;
		}
		$this->table( $html );
	}

	public function render_html_order_note( $props, $arr_style ) {
		if ( $this->order && $this->order->get_customer_note() ) {
			$customer_note = wpautop( $this->order->get_customer_note() );
			$html          = '';
			$left_style    = isset( $props['childStyle']['.viwec-td-left'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-left'] ) : 'text-align:left;';
			$right_style   = isset( $props['childStyle']['.viwec-td-right'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-right'] ) : 'text-align:right; width:40%;';
			$el_style      = isset( $props['childStyle']['.viwec-order-total-style'] ) ? $this->parse_styles( $props['childStyle']['.viwec-order-total-style'] ) : '';
			$font_family   = $props['style']['font-family'] ?? 'inherit';

			$p_style = $this->get_p_inherit_style( $props );

			$trans_note = $props['content']['order_note'] ?? 'Note';
			$note_html  = "<tr><td valign='top' class='viwec-mobile-50' style='{$font_family}: inherit;{$el_style}{$left_style}'><p style='{$p_style}'>{$trans_note}</p></td>";
			$note_html  .= "<td valign='top' class='viwec-mobile-50' style='font-family: {$font_family};{$el_style}{$right_style}'><p style='{$p_style}'>{$customer_note}</p></td></tr>";

			$html .= $note_html;
			$this->table( $html );
		}
	}

	public function render_html_shipping_method( $props, $arr_style = [] ) {
		if ( $this->order ) {
			if ( $shipping_method = $this->order->get_shipping_method() ?? '' ) {
				$shipping_method_html = '';
				$left_style           = isset( $props['childStyle']['.viwec-td-left'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-left'] ) : 'text-align:left;';
				$right_style          = isset( $props['childStyle']['.viwec-td-right'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-right'] ) : 'text-align:right; width:40%;';
				$el_style             = isset( $props['childStyle']['.viwec-shipping-method-style'] ) ? $this->parse_styles( $props['childStyle']['.viwec-shipping-method-style'] ) : '';
				$font_family          = $props['style']['font-family'] ?? 'inherit';

				$p_style = $this->get_p_inherit_style( $props );

				$trans_shipping_method = $props['content']['shipping_method'] ?? esc_html__( 'Shipping method', 'viwec-email-template-customizer' );
				$shipping_method_html  .= "<tr><td  valign='top' class='viwec-mobile-50' style='font-family: {$font_family};{$el_style}{$left_style}'><p style='{$p_style}'>{$trans_shipping_method}</p></td>";
				$shipping_method_html  .= "<td  valign='top' class='viwec-mobile-50' style='font-family: {$font_family};{$el_style}{$right_style}'><p style='{$p_style}'>{$shipping_method}</p></td></tr>";
				$this->table( $shipping_method_html );
			}
		}
	}

	public function render_html_payment_method( $props, $arr_style ) {
		$html = '';
		if ( $this->order ) {
			$payment_method_html = '';
			$left_style          = isset( $props['childStyle']['.viwec-td-left'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-left'] ) : 'text-align:left;';
			$right_style         = isset( $props['childStyle']['.viwec-td-right'] ) ? $this->parse_styles( $props['childStyle']['.viwec-td-right'] ) : 'text-align:right; width:40%;';
			$el_style            = isset( $props['childStyle']['.viwec-payment-method-style'] ) ? $this->parse_styles( $props['childStyle']['.viwec-payment-method-style'] ) : '';
			$font_family         = $props['style']['font-family'] ?? 'inherit';

			$payment_method = $this->order->get_total() > 0 && $this->order->get_payment_method_title() && 'other' !== $this->order->get_payment_method_title() ? $this->order->get_payment_method_title() : '';

			$p_style = $this->get_p_inherit_style( $props );

			$trans_payment_method = $props['content']['payment_method'] ?? esc_html__( 'Payment method', 'viwec-email-template-customizer' );
			if ( $payment_method ) {
				$payment_method_html = "<tr><td  valign='top' class='viwec-mobile-50' style='font-family: {$font_family};{$el_style}{$left_style}'><p style='{$p_style}'>{$trans_payment_method}</p></td>";
				$payment_method_html .= "<td  valign='top' class='viwec-mobile-50' style='font-family: {$font_family};{$el_style}{$right_style}'><p style='{$p_style}'>{$payment_method}</p></td></tr>";
			}
			$html .= $payment_method_html;
		}
		$this->table( $html );
	}

	public function render_html_billing_address( $props, $arr_style = [] ) {

		if ( ! $this->order ) {
			return;
		}

		if ( $this->preview ) {
			$this->render_html_billing_address_via_hook( '', '', '', '', $props, $arr_style );
		} else {
			if ( empty( $this->template_args ) && ( is_plugin_active( 'woocommerce-germanized/woocommerce-germanized.php' ) ||
			                                        is_plugin_active( 'woocommerce-germanized-pro/woocommerce-germanized-pro.php' ) ) ) {
				return;
			}
			add_action( 'woocommerce_email_customer_details', [ $this, 'render_html_billing_address_via_hook' ], 20, 6 );
			$args = $this->template_args;
			do_action( 'woocommerce_email_customer_details', $args['order'], $args['sent_to_admin'], $args['plain_text'], $args['email'], $props, $arr_style );
			remove_action( 'woocommerce_email_customer_details', [ $this, 'render_html_billing_address_via_hook' ], 20 );
		}

	}

	public function render_html_billing_address_via_hook( $order, $sent_to_admin, $plain_text, $email, $props, $arr_style = [] ) {
		if ( $props['type'] !== 'html/billing_address' ) {
			return;
		}

		$color       = $props['style']['color'] ?? 'inherit';
		$font_weight = $props['style']['font-weight'] ?? 'inherit';
		$align       = $props['style']['text-align'] ?? 'center';
		$font_family = $props['style']['font-family'] ?? 'inherit';
		$font_size   = $arr_style['font-size'] ?? 'inherit';
		$line_height = $arr_style['line-height'] ?? 'inherit';

		$billing_address = $this->order->get_formatted_billing_address();
		$billing_address = str_replace( '<br/>', "</td></tr><tr><td valign='top' style='color: {$color}; font-weight: {$font_weight};font-family: {$font_family};font-size:{$font_size};line-height:{$line_height};'>", $billing_address );
		$billing_email   = $billing_phone = '';


		if ( $phone = $this->order->get_billing_phone() ) {
			$billing_phone = "<tr><td valign='top' ><a href='tel:$phone' style='color: {$color}; font-weight: {$font_weight};font-size:{$font_size};line-height:{$line_height};'>$phone</a></td></tr>";
			if ( apply_filters( 'viwec_remove_billing_phone_link', __return_false() ) ) {
				$billing_phone = "<tr><td valign='top' ><span style='color: {$color}; font-weight: {$font_weight};font-size:{$font_size};line-height:{$line_height};'>$phone</span></td></tr>";
			}
		}

		if ( $this->order->get_billing_email() ) {
			$billing_email = "<tr><td valign='top' ><a style='color:{$color}; font-weight: {$font_weight};font-size:{$font_size};line-height:{$line_height};' href='mailto:{$this->order->get_billing_email()}'>{$this->order->get_billing_email()}</a></td></tr>";
			if ( apply_filters( 'viwec_remove_billing_email_link', __return_false() ) ) {
				$billing_email = "<tr><td valign='top' ><span style='color:{$color}; font-weight: {$font_weight};font-size:{$font_size};line-height:{$line_height};'>{$this->order->get_billing_email()}</span></td></tr>";
			}
		}

		$html = "<tr><td valign='top' style='color: {$color}; font-weight: {$font_weight}; font-size:{$font_size};font-family: {$font_family};line-height:{$line_height};'>{$billing_address}</td></tr>{$billing_phone}{$billing_email}";

		$style = "text-align:{$align};";
		$this->table( $html, $style );
	}

	public function render_html_shipping_address( $props, $arr_style = [] ) {
		if ( ! $this->order ) {
			return;
		}

		if ( $this->preview ) {
			$this->render_html_shipping_address_via_hook( '', '', '', '', $props, $arr_style );
		} else {
			if ( empty( $this->template_args ) && ( is_plugin_active( 'woocommerce-germanized/woocommerce-germanized.php' ) ||
			                                        is_plugin_active( 'woocommerce-germanized-pro/woocommerce-germanized-pro.php' ) ) ) {
				//plugin germanized trigger email with use_default_template_email without template_args data
				return;
			}
			add_action( 'woocommerce_email_customer_details', [ $this, 'render_html_shipping_address_via_hook' ], 20, 6 );
			$args = $this->template_args;
			do_action( 'woocommerce_email_customer_details', $args['order'], $args['sent_to_admin'], $args['plain_text'], $args['email'], $props, $arr_style );
			remove_action( 'woocommerce_email_customer_details', [ $this, 'render_html_shipping_address_via_hook' ], 20 );
		}
	}

	public function render_html_shipping_address_via_hook( $order, $sent_to_admin, $plain_text, $email, $props, $arr_style = [] ) {
		if ( $props['type'] !== 'html/shipping_address' || ! $this->order ) {
			return;
		}

		$hidden_ids    = $props['attrs']['data-shipping_instance_id'] ?? '';
		$hidden_ids    = trim( $hidden_ids );
		$order_methods = $this->order->get_shipping_methods();
		$method_hidden = false;

		if ( ! empty( $order_methods ) && is_array( $order_methods ) ) {
			foreach ( $order_methods as $method ) {
				$ins = $method->get_instance_id();
				if ( in_array( $ins, explode( ',', $hidden_ids ) ) ) {
					$method_hidden = true;
					break;
				}
			}
		}

		if ( $method_hidden ) {
			$text_if_hidden = $props['attrs']['data-text_local_pickup'] ?? '';
			echo esc_html( $text_if_hidden );

			return;
		}
		if ( isset( $props['attrs']['data-hide_if_shipping_empty'] ) && $props['attrs']['data-hide_if_shipping_empty'] == 'true' ) {
			$b = $this->order->get_formatted_billing_address();
			$s = $this->order->get_formatted_shipping_address();
			if ( $b == $s ) {
				return;
			}
		}

		$color       = $props['style']['color'] ?? 'inherit';
		$font_weight = $props['style']['font-weight'] ?? 'inherit';
		$align       = $props['style']['text-align'] ?? 'center';
		$font_family = $props['style']['font-family'] ?? 'inherit';
		$font_size   = $arr_style['font-size'] ?? 'inherit';
		$line_height = $arr_style['line-height'] ?? 'inherit';


		$shipping_address = $this->order->get_formatted_shipping_address();
		$shipping_address = empty( $shipping_address ) ? $this->order->get_formatted_billing_address() : $shipping_address;
		$shipping_address = str_replace( '<br/>', "</td></tr><tr><td valign='top' style='color: {$color}; font-weight: {$font_weight}; font-family: {$font_family};font-size:{$font_size};line-height:{$line_height};'>", $shipping_address );


		$shipping_phone = '';
		$order_data     = $this->order->get_data();
		if ( ! empty( $order_data['shipping']['phone'] ) ) {
			$phone          = $order_data['shipping']['phone'];
			$shipping_phone = "<tr><td valign='top' ><a href='tel:$phone' style='color: {$color}; font-weight: {$font_weight};font-size:{$font_size};line-height:{$line_height};'>$phone</a></td></tr>";
			if ( apply_filters( 'viwec_remove_shipping_phone_link', __return_false() ) ) {
				$shipping_phone = "<tr><td valign='top' ><span style='color: {$color}; font-weight: {$font_weight};font-size:{$font_size};line-height:{$line_height};'>$phone</span></td></tr>";
			}
		}

		$html = "<tr><td valign='top' style='color: {$color}; font-weight: {$font_weight};font-size:{$font_size};line-height:{$line_height};'>{$shipping_address}{$shipping_phone}</td></tr>";

		$style = "text-align:{$align};";

		$this->table( $html, $style );
	}

	public function render_html_suggest_product( $props, $arr_style = [] ) {

		$font_size    = $props['style']['font-size'] ?? 0;
		$font_weight  = $props['style']['font-weight'] ?? 'normal';
		$color        = $props['style']['color'] ?? 'inherit';
		$line_height  = $props['style']['line-height'] ?? 'inherit';
		$suggest_type = $props['attrs']['data-product_type'] ?? 'newest';
		$row          = $props['attrs']['data-max_row'] ?? '';
		$column       = $props['attrs']['data-column'] ?? '';
		$char_limit   = $props['attrs']['character-limit'] ?? '';
		$limit        = $row * $column;
		$style        = "line-height:{$line_height};color: {$color};font-size: {$font_size};font-weight: $font_weight;";
		$name_style   = isset( $props['childStyle'] ['.viwec-product-name'] ) ? $this->parse_styles( $props['childStyle']['.viwec-product-name'] ) : '';
		$price_style  = isset( $props['childStyle']['.viwec-product-price'] ) ? $this->parse_styles( $props['childStyle']['.viwec-product-price'] ) : '';

		$v_distance = isset( $props['childStyle']['.viwec-product-distance']['padding'] ) ? $props['childStyle']['.viwec-product-distance']['padding'] : '';
		$v_distance = explode( ' ', $v_distance );
		$v_distance = str_replace( 'px', '', end( $v_distance ) );
		$v_distance = intval( $v_distance );

		$h_distance = isset( $props['childStyle']['.viwec-product-h-distance'] ) ? $this->parse_styles( $props['childStyle']['.viwec-product-h-distance'] ) : '';

		$full_width = isset( $props['style']['width'] ) ? str_replace( 'px', '', $props['style']['width'] ) : 600;

		if ( $suggest_type && $row && $column ) {
			$products       = $this->get_suggest_products( $suggest_type, $limit, $props );
			$products_count = count( $products );
			$row            = ceil( $products_count / $column );

			if ( $row == 1 && $products_count < $column ) {
				$column = $products_count;
			}

			$col_width = ( ( (int) $full_width - ( (int) $v_distance * ( $column - 1 ) ) ) / (int) $column );

			for ( $i = 0; $i < $row; $i ++ ) { ?>
                <table width='100%' border='0' cellpadding='0' cellspacing='0' style="margin: 0; border-collapse: collapse">
                    <tr>
                        <td valign='top' style='font-size: 0;' border='0' cellpadding='0' cellspacing='0'>
							<?php
							for ( $j = 0; $j < $column; $j ++ ) {
								$index  = $i * $column + $j;
								$p_name = ! empty( $products[ $index ]['name'] ) ? $products[ $index ]['name'] : '';

								if ( $char_limit ) {
									$name_length = strlen( $p_name );
									$p_name      = $name_length <= $char_limit ? $p_name : substr( $p_name, 0, $char_limit ) . '...';
								}

								$p_price = ! empty( $products[ $index ]['price'] ) ? $products[ $index ]['price'] : '';
								$p_url   = ! empty( $products[ $index ]['url'] ) ? $products[ $index ]['url'] : '';
								$p_image = '';
								if ( isset( $products[ $index ] ) ) {
									$p_image = ! empty( $products[ $index ]['image'] ) ? $products[ $index ]['image'] : wc_placeholder_img_src();
								}

								$hidden = $p_name . $p_price . $p_image ? '' : 'viwec-mobile-hidden';
								$width  = $j != 0 ? $col_width + $v_distance : $col_width;
								$width  = floor( $width );

								$v_gap = $j != 0 ? $v_distance : 0;
								$v_gap = $this->direction === 'rtl' ? "padding-right:{$v_gap}px;" : "padding-left:{$v_gap}px;";

								$h_gap     = $i != $row - 1 ? $h_distance : '';
								$img_width = '100%';
								if ( $j == 0 ) {
									?>
                                    <!--[if (gte mso 9)|(IE)]>
                                    <table width='100%' role='presentation' border='0' cellpadding='0' cellspacing='0'>
                                        <tr>
                                            <td valign='top' style="width: <?php echo esc_attr( $width ); ?>px; vertical-align:top;">
                                    <![endif]-->
									<?php
								} else {
									?>
                                    <!--[if (gte mso 9)|(IE)]>
                                    </td>
                                    <td valign='top' style='vertical-align:top;width:<?php echo esc_attr( $width ) ?>px;'>
                                    <![endif]-->
									<?php
								}

								?>
                                <!--[if (gte mso 9)|(IE)]>
                                <?php $img_width = $width; ?>
                                <![endif]-->
                                <table align="left" valign="top" class="viwec-responsive" width="<?php echo esc_attr( $width ); ?>px" border='0' cellpadding='0' cellspacing='0'
                                       style="border-collapse: collapse; display: inline-block;">
                                    <tr>
                                        <td class="suggest-item <?php echo esc_attr( $index ) ?>">
                                            <table width='<?php echo esc_attr( $img_width ) ?>' valign='top' border='0' cellpadding='0' cellspacing='0' style="border-collapse: collapse;">
                                                <tr>
													<?php
													if ( $this->direction !== 'rtl' ) {
														?>
                                                        <td valign='top' class='viwec-mobile-hidden' style='<?php echo esc_attr( $v_gap ) ?>'></td>
														<?php
													}
													?>
                                                    <td valign='top'
                                                        style='vertical-align: top; text-align: center;font-size: <?php echo esc_attr( $font_size ) ?>;font-weight: <?php echo esc_attr( $font_weight ) ?>'>
                                                        <a href='<?php echo esc_url( $p_url ) ?>' target="_blank" style='text-decoration: none; <?php echo esc_attr( $style ) ?>'>
                                                            <img alt='<?php echo esc_attr( $p_name ) ?>' width='100%'
                                                                 style='padding-bottom: 5px;' src='<?php echo esc_url( $p_image ) ?>'>
                                                            <div style='overflow: hidden;<?php echo esc_attr( $name_style ) ?>'>
																<?php echo esc_html( $p_name ) ?>
                                                            </div>
                                                            <div style='<?php echo esc_attr( $price_style ) ?>'>
																<?php
																$p_price = str_replace( [ '<del', '</del>' ], [ '<span style="text-decoration:line-through;"', '</span>' ], $p_price );
																echo wp_kses( $p_price, viwec_allowed_html() )
																?>
                                                            </div>
                                                        </a>
                                                    </td>

													<?php
													if ( $this->direction === 'rtl' ) {
														?>
                                                        <td valign='top' class='viwec-mobile-hidden' style='<?php echo esc_attr( $v_gap ) ?>'></td>
														<?php
													}
													?>
                                                </tr>
                                            </table>
                                            <div style='<?php echo esc_attr( $h_gap ) ?>'></div>
                                        </td>
                                    </tr>
                                </table>
								<?php
								if ( $j == $column - 1 ) {
									?>
                                    <!--[if (gte mso 9)|(IE)]></td></tr></table><![endif]-->
									<?php
								}
							}
							?>
                        </td>
                    </tr>
                </table>
			<?php }
		}
	}

	public function render_html_social( $props, $arr_style = [] ) {
		if ( empty( Init::$img_map['social_icons'] ) ) {
			return;
		}

		$align   = $props['style']['text-align'] ?? 'left';
		$socials = array_keys( (array) Init::$img_map['social_icons'] );
		$html    = '';
		$width   = ! empty( $props['attrs']['data-width'] ) ? $props['attrs']['data-width'] : 32;

		if ( isset( $props['attrs']['direction'] ) && $props['attrs']['direction'] === 'vertical' ) {
			foreach ( $socials as $index => $social ) {
				$link = isset( $props['attrs'][ $social . '_url' ] ) ? esc_url( $props['attrs'][ $social . '_url' ] ) : '';
				$img  = isset( $props['attrs'][ $social ] ) ? esc_url( $props['attrs'][ $social ] ) : '';
				if ( ! empty( $img ) && ! empty( $link ) ) {
					$html .= "<tr><td valign='top' class='social-item {$index}'><a href='{$link}'><img style='vertical-align: middle' src='{$img}' width='{$width}'></a></td></tr>";
				}
			}
		} else {
			$html = '<tr>';
			foreach ( $socials as $index => $social ) {
				$link = isset( $props['attrs'][ $social . '_url' ] ) ? esc_url( $props['attrs'][ $social . '_url' ] ) : '';
				$img  = isset( $props['attrs'][ $social ] ) ? esc_url( $props['attrs'][ $social ] ) : '';
				if ( ! empty( $img ) && ! empty( $link ) ) {
					$html .= "<td class='social-item {$index}' valign='top' style='padding: 0 3px;'><a href='{$link}'><img src='{$img}' width='{$width}'></a></td>";
				}
			}
			$html .= '</tr>';
		}

		$html = "<table class='viwec-no-full-width-on-mobile' align='{$align}' border='0' cellpadding='0' cellspacing='0' style='width: auto !important;'>$html</table>";
		echo wp_kses( $html, viwec_allowed_html() );
	}

	public function render_html_button( $props, $arr_style = [] ) {
		$url           = isset( $props['attrs']['href'] ) ? $this->replace_shortcode( $props['attrs']['href'] ) : '';
		$text          = isset( $props['content']['text'] ) ? $this->replace_shortcode( $props['content']['text'] ) : '';
		$text          = str_replace( [ '<p>', '</p>' ], [ '', '' ], $text );
		$align         = $props['style']['text-align'] ?? 'left';
		$style         = isset( $props['childStyle']['a'] ) ? $props['childStyle']['a'] : [];
		$padding       = ! empty( $style['padding'] ) ? $style['padding'] : '';
		$border_radius = ! empty( $style['border-radius'] ) ? $style['border-radius'] : '';
		$bg_color      = ! empty( $style['background-color'] ) ? $style['background-color'] : 'inherit';
		unset( $style['padding'] );

		$style       = $this->parse_styles( $style );
		$text_color  = $props['style']['color'] ?? 'inherit';
		$font_weight = $props['style']['font-weight'] ?? 'normal';
		$width       = $props['childStyle']['a']['width'] ?? '';

		$line_height = $props['style']['line-height'] ?? 1;
		$height      = str_replace( 'px', '', $line_height );

		$a_style = [
			"color:{$text_color} !important",
			"font-weight:{$font_weight}",
			"display:block;text-decoration:none;text-transform:none;margin:0;text-align: center;max-width: 100%",
			"background-color:{$bg_color}",
			"line-height:{$line_height}",
			"height:{$line_height}",
		];

		?>
        <table align='<?php echo esc_attr( $align ) ?>' width='<?php echo esc_attr( $width ) ?>' height="<?php echo esc_attr( $height ) ?>"
               class='viwec-button-responsive' border='0' cellpadding='0' cellspacing='0' role='presentation'
               style='border-collapse:separate;width: <?php echo esc_attr( $width ) ?>;
                       background-color: <?php echo esc_attr( $bg_color ) ?>;
                       border-radius: <?php echo esc_attr( $border_radius ) ?>;'>
            <tr>
                <td class='viwec-mobile-button-padding' align='center' valign='middle' role='presentation'
                    height="<?php echo esc_attr( $height ) ?>" style='<?php echo esc_attr( $style ) ?>;'>
                    <a href='<?php echo esc_url( do_shortcode( $url ) ) ?>' target='_blank'
                       style='<?php echo esc_attr( implode( ';', $a_style ) ) ?>'>
                          <span style='color: <?php echo esc_attr( $text_color ) ?>'>
                              <?php echo wp_kses( do_shortcode( $text ), viwec_allowed_html() ) ?>
                          </span>
                    </a>
                </td>
            </tr>
        </table>
		<?php
	}

	public function render_html_menu( $props, $arr_style = [] ) {
		$color       = $props['style']['color'] ?? 'inherit';
		$font_weight = $props['style']['font-weight'] ?? 'inherit';
		$align       = $props['style']['text-align'] ?? 'inherit';
		$font_size   = $props['style']['font-size'] ?? 'inherit';
		?>
        <table align='center' width='100%' border='0' cellpadding='0' cellspacing='0' style='border-collapse: collapse;margin: 0; padding:0'>
			<?php
			if ( isset( $props['content'] ) && is_array( $props['content'] ) ) {
				$count_text = count( array_filter( $props['content'] ) );
				$count_link = count( array_filter( $props['attrs'] ) );
				$col        = min( $count_text, $count_link ) ? 100 / min( $count_text, $count_link ) . '%' : '';

				if ( isset( $props['attrs']['direction'] ) && $props['attrs']['direction'] === 'vertical' ) {
					foreach ( $props['content'] as $key => $value ) {

						$link = isset( $props['attrs'][ $key ] ) ? $this->replace_shortcode( $props['attrs'][ $key ] ) : '';

						if ( empty( $value ) || ! $link ) {
							continue;
						} ?>
                        <tr>
                            <td valign='top' align="<?php echo esc_attr( $align ) ?>">
                                <a href='<?php echo esc_url( $link ) ?>'
                                   style='font-size:<?php echo esc_attr( $font_size ) ?>;color: <?php echo esc_attr( $color ) ?>; font-weight: <?php echo esc_attr( $font_weight ) ?>;font-style:inherit;'>
                                    <strong style="font-weight: <?php echo esc_attr( $font_weight ) ?>; color: <?php echo esc_attr( $color ) ?>;">
										<?php echo wp_kses( $value, viwec_allowed_html() ) ?>
                                    </strong>
                                </a>
                            </td>
                        </tr>
					<?php }
				} else { ?>
                    <tr>
						<?php
						foreach ( $props['content'] as $key => $value ) {

							$link = isset( $props['attrs'][ $key ] ) ? $this->replace_shortcode( $props['attrs'][ $key ] ) : '';

							if ( empty( $value ) || ! $link ) {
								continue;
							}
							?>
                            <td valign='top' width='<?php echo esc_attr( $col ) ?>' align="<?php echo esc_attr( $align ) ?>">
                                <a href='<?php echo esc_url( $link ) ?>'
                                   style='font-size:<?php echo esc_attr( $font_size ) ?>;color: <?php echo esc_attr( $color ) ?>; font-weight: <?php echo esc_attr( $font_weight ) ?>; font-style: inherit'>
                                    <strong style="font-weight: <?php echo esc_attr( $font_weight ) ?>; color: <?php echo esc_attr( $color ) ?>;">
										<?php echo wp_kses( $value, viwec_allowed_html() ) ?>
                                    </strong>
                                </a>
                            </td>
						<?php } ?>
                    </tr>
				<?php }
			} ?>
        </table>
		<?php
	}

	public function render_html_divider( $props, $arr_style = [] ) {
		$style = isset( $props['childStyle']['hr'] ) ? $this->parse_styles( $props['childStyle']['hr'] ) : '';

		?>
        <table width='100%' border='0' cellpadding='0' cellspacing='0' style="margin: 10px 0;">
            <tr>
                <td valign='top' style="border-width: 0;<?php echo esc_attr( $style ) ?>"></td>
            </tr>
        </table>
		<?php
	}

	public function render_html_spacer( $props, $arr_style = [] ) {
		$style         = isset( $props['childStyle']['.viwec-spacer'] ) ? $this->parse_styles( $props['childStyle']['.viwec-spacer'] ) : '';
		$mobile_hidden = ! empty( $props['attrs']['mobile-hidden'] ) && $props['attrs']['mobile-hidden'] == 'true' ? 'viwec-mobile-hidden' : '';
		?>
        <table width='100%' border='0' cellpadding='0' cellspacing='0' style='font-size:0 !important;margin:0;' class='<?php echo esc_attr( $mobile_hidden ) ?>'>
            <tr>
                <td valign='top' style='<?php echo esc_attr( $style ) ?>'></td>
            </tr>
        </table>
		<?php
	}

	public function render_html_contact( $props, $arr_style = [] ) {
		$align       = $props['style']['text-align'] ?? 'left';
		$color       = $props['style']['color'] ?? 'inherit';
		$font_size   = $props['style']['font-size'] ?? 'inherit';
		$font_weight = $props['style']['font-weight'] ?? 'inherit';
		$style       = "color: {$color};font-size: {$font_size};font-weight: {$font_weight};vertical-align:sub;";
		?>
        <table align='<?php echo esc_attr( $align ) ?>'>
			<?php
			if ( ! empty( $props['attrs']['home'] ) && ! empty( $props['content']['home_text'] ) ) {
				$url  = isset( $props['attrs']['home_link'] ) ? $this->replace_shortcode( $props['attrs']['home_link'] ) : '';
				$text = isset( $props['content']['home_text'] ) ? $this->replace_shortcode( $props['content']['home_text'] ) : '';
				?>
                <tr>
                    <td valign='top'><img src='<?php echo esc_url( $props['attrs']['home'] ) ?>' style='padding-right: 3px;'></td>
                    <td valign='top'><a style='<?php echo esc_attr( $style ) ?>' href='<?php echo esc_url( $url ) ?>'>
							<?php echo wp_kses( $text, viwec_allowed_html() ) ?>
                        </a>
                    </td>
                </tr>
				<?php
			}

			if ( ! empty( $props['attrs']['email'] ) && ! empty( $props['attrs']['email_link'] ) ) {
				$email_url = $this->replace_shortcode( $props['attrs']['email_link'] );
				?>
                <tr>
                    <td valign='top'><img src='<?php echo esc_url( $props['attrs']['email'] ) ?>' style='padding-right: 3px;'></td>
                    <td valign='top'>
                        <a style='<?php echo esc_attr( $style ) ?>' href='mailto:<?php echo esc_attr( $email_url ) ?>'>
							<?php echo esc_html( $email_url ) ?>
                        </a>
                    </td>
                </tr>
				<?php
			}

			if ( ! empty( $props['attrs']['phone'] ) && ! empty( $props['content']['phone_text'] ) ) {
				?>
                <tr>
                    <td valign='top'><img src='<?php echo esc_url( $props['attrs']['phone'] ) ?>' style='padding-right: 3px;'></td>
                    <td valign='top'><a style='<?php echo esc_attr( $style ) ?>' href='tel:<?php echo esc_attr( $props['content']['phone_text'] ) ?>'>
							<?php echo wp_kses( $props['content']['phone_text'], viwec_allowed_html() ) ?>
                        </a>
                    </td>
                </tr>
				<?php
			}
			?>
        </table>
		<?php
	}

	public function render_html_coupon( $props, $arr_style = [] ) {

		$type        = $props['attrs']['data-coupon-type'] ?? '';
		$coupon_code = $props['content']['data-coupon-code'] ?? '';
		$align       = $props['style']['text-align'] ?? 'left';
		$text_color  = $props['style']['color'] ?? 'inherit';
		$font_weight = $props['style']['font-weight'] ?? 'normal';
		$width       = $props['childStyle']['.viwec-coupon']['width'] ?? '';
		$style       = isset( $props['childStyle']['.viwec-coupon'] ) ? $this->parse_styles( $props['childStyle']['.viwec-coupon'] ) : '';

		/*Check thankyou page created coupon*/
		$thank_you_page_coupon = '';
		if ( is_a( $this->order, 'WC_Order' ) ) {
			$order                 = $this->order;
			$order_id              = $order->get_id();
			$thank_you_page_coupon = get_post_meta( $order_id, 'woo_thank_you_page_coupon_code', true );
		}
		if ( ! empty( $thank_you_page_coupon ) ) {
			$coupon_code = $thank_you_page_coupon;
		} else {
			if ( $type == 2 ) {
				$coupon_code = $this->preview ? 's0kvk4kp' : $this->generate_coupon( $props );
			}
		}

		if ( ! $coupon_code || $coupon_code === 'COUPONCODE' ) {
			return;
		}

		$coupon_code = strtoupper( $coupon_code );

		$this->render_data['coupon'] = $coupon_code;

		$coupon_obj = new \WC_Coupon( $coupon_code );

		$coupon_ex_date = $coupon_obj->get_date_expires();

		if ( $coupon_ex_date ) {
			$coupon_ex_date = $coupon_ex_date->date_i18n( wc_date_format() );
		}

		$this->template_args['coupon_expire_date'] = $coupon_ex_date;

		?>
        <table align='<?php echo esc_attr( $align ) ?>' width='<?php echo esc_attr( $width ) ?>'
               class='viwec-responsive' border='0' cellpadding='0' cellspacing='0' role='presentation'
               style='border-collapse:separate;width:<?php echo esc_attr( $width ) ?>;'>
            <tr>
                <td class='viwec-mobile-button-padding' align='center' valign='middle' role='presentation' style='<?php echo esc_attr( $style ) ?>'>
                    <div style='color:<?php echo esc_attr( $text_color ) ?> !important;font-weight:<?php echo esc_attr( $font_weight ) ?>;
                            display:inline-block;margin:0;text-align: center; max-width:100%;'>
						<?php echo esc_html( $coupon_code ) ?>
                    </div>
                </td>
            </tr>
        </table>
		<?php
	}

	public function render_html_post( $props, $arr_style = [] ) {
		$cat           = ! empty( $props['attrs']['data-post-category'] ) ? $props['attrs']['data-post-category'] : '';
		$title_style   = ! empty( $props['childStyle']['.viwec-post-title'] ) ? $this->parse_styles( $props['childStyle']['.viwec-post-title'] ) : '';
		$content_style = ! empty( $props['childStyle']['.viwec-post-content'] ) ? $this->parse_styles( $props['childStyle']['.viwec-post-content'] ) : '';
		$title_limit   = ! empty( $props['attrs']['data-title-limit'] ) ? $props['attrs']['data-title-limit'] : 0;
		$content_limit = isset( $props['attrs']['data-content-limit'] ) ? $props['attrs']['data-content-limit'] : 80;
		$row           = ! empty( $props['attrs']['data-max_row'] ) ? $props['attrs']['data-max_row'] : 1;
		$col           = ! empty( $props['attrs']['data-column'] ) ? $props['attrs']['data-column'] : 2;
		$include       = ! empty( $props['attrs']['data-include-post-id'] ) ? explode( ',', $props['attrs']['data-include-post-id'] ) : [];
		$exclude       = ! empty( $props['attrs']['data-exclude-post-id'] ) ? explode( ',', $props['attrs']['data-exclude-post-id'] ) : [];

		$distance = ! empty( $props['childStyle']['.viwec-post-distance']['padding'] ) ? $props['childStyle']['.viwec-post-distance']['padding'] : 10;
		$distance = explode( ' ', $distance );
		$distance = (int) str_replace( 'px', '', end( $distance ) );

		$h_distance = ! empty( $props['childStyle']['.viwec-post-h-distance']['padding'] ) ?
			(int) str_replace( 'px', '', current( explode( ' ', $props['childStyle']['.viwec-post-h-distance']['padding'] ) ) ) : 10;

		$full_width = ! empty( $props['childStyle']['.viwec-post']['width'] ) ?
			str_replace( [ 'px' ], [ '' ], $props['childStyle']['.viwec-post']['width'] ) : 600;

		if ( $row && $col ) {
			$arg_posts = [
				'numberposts' => $row * $col,
				'category'    => $cat,
				'include'     => $include,
				'exclude'     => $exclude,
			];
			if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
				$get_languages                 = wpml_get_current_language();
				$arg_posts['suppress_filters'] = 0;
				do_action( 'wpml_switch_language', $get_languages ); // switch the content language

			} elseif ( class_exists( 'Polylang' ) ) {
				$get_languages     = pll_current_language( 'slug' );
				$arg_posts['lang'] = $get_languages;
			}
			$posts = get_posts( $arg_posts );

			if ( empty( $posts ) ) {
				return;
			}

			$count_posts = count( $posts );
			$real_row    = ceil( $count_posts / $col );

			if ( ! $real_row ) {
				return;
			}

			$real_col  = ceil( $count_posts / $real_row );
			$col_width = ( (int) $full_width - ( $distance * ( $real_col - 1 ) ) ) / (int) $real_col;

			for ( $i = 0; $i < $real_row; $i ++ ) {
				?>
                <tr>
                    <td valign="top" style="font-size: 0">
                        <!--[if (gte mso 9)|(IE)]>
                        <table width="100%" role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr><![endif]-->
						<?php
						for ( $j = 0; $j < $real_col; $j ++ ) {
							$k = $j + $i * $real_col;

							if ( ! isset( $posts[ $k ] ) ) {
								continue;
							}

							$post = $posts[ $k ];

							/*if ( ! empty( $get_languages ) ) {
								if ( in_array( $post->ID, $wpml_products ) ) {
									continue;
								} else {
									$new_post_id = 0;
									if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
										$new_post_id = $wpml_products[] = apply_filters(
											'wpml_object_id', $post->ID, 'post', false, $get_languages
										);

									} elseif ( class_exists( 'Polylang' ) ) {

										$new_post_id = $wpml_products[] = pll_get_post( $post->ID, $get_languages );
									}
									$post = get_post( $new_post_id );
								}
							}*/

							$post_title   = $post->post_title;
							$post_content = get_the_excerpt( $post );
							$link         = get_permalink( $post );
							$img_src      = get_the_post_thumbnail_url( $post, 'woocommerce_thumbnail' );
							$img          = $img_src ? "<img width='100%' src='{$img_src}'>" : '';

							if ( $title_limit != 0 ) {
								$post_title = $title_limit < strlen( $post_title ) ? substr( $post_title, 0, $title_limit ) . '...' : $post_title;
							}

							if ( $content_limit ) {
								$post_content = $content_limit < strlen( $post_content ) ? substr( $post_content, 0, $content_limit ) . '...' : $post_content;
							} else {
								$post_content = '';
							}

							$title_content = "<div style='{$title_style}'>{$post_title}</div><div style='{$content_style}'>{$post_content}</div>";
							$gap           = $j != 0 ? "width:{$distance}px;" : '';
							$_col_width    = $j != 0 ? $col_width + $distance : $col_width;
							?>
                            <!--[if (gte mso 9)|(IE)]>
                            <td valign='top' style='width:<?php echo esc_attr( $_col_width ) ?>px;'><![endif]-->
                            <div class='viwec-responsive '
                                 style='display: inline-block;vertical-align: bottom; width:<?php echo esc_attr( $_col_width ) ?>px;font-size: 15px;'>
                                <table width='100%' border='0' cellpadding='0' cellspacing='0'>
                                    <tr>
                                        <td valign='top' class='viwec-mobile-hidden' style='<?php echo esc_attr( $gap ) ?>'></td>
                                        <td valign='top'>
                                            <a href='<?php echo esc_url( $link ) ?>'>
												<?php
												if ( $real_col == 1 ) {
													$img_width = $img ? '25%' : '0';
													$padding   = $img ? 'padding-left:10px;' : '';
													?>
                                                    <table width='100%' border='0' cellpadding='0' cellspacing='0'>
                                                        <tr>
                                                            <td valign='top' style='width:<?php echo esc_attr( $img_width ) ?>;'>
																<?php echo wp_kses_post( $img ) ?>
                                                            </td>
                                                            <td valign='top' style='<?php echo esc_attr( $padding ) ?>'>
																<?php echo wp_kses_post( $title_content ) ?>
                                                            </td>
                                                        </tr>
                                                    </table>
													<?php
												} else {
													echo wp_kses_post( $img ); ?>
                                                    <div style='padding: 5px;'></div>
													<?php echo wp_kses_post( $title_content );
												} ?>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
								<?php
								if ( $i != $real_row ) { ?>
                                    <div style='padding-top:<?php echo esc_attr( $h_distance ) ?>px;'></div>
								<?php } ?>
                            </div>
                            <!--[if (gte mso 9)|(IE)]></td><![endif]-->
						<?php } ?>
                        <!--[if (gte mso 9)|(IE)]></tr></table><![endif]--></td>
                </tr>
			<?php }
		}
	}

	public function render_html_wc_hook( $props, $arr_style = [] ) {
		$filter_hooks = [ 'woocommerce_email_order_details', 'woocommerce_email_before_order_table', 'woocommerce_email_after_order_table', 'woocommerce_email_order_meta' ];
		$hook         = ! empty( $props['attrs']['data-wc-hook'] ) && in_array( $props['attrs']['data-wc-hook'], $filter_hooks ) ? $props['attrs']['data-wc-hook'] : 'woocommerce_email_before_order_table';

		$h2         = ! empty( $props['childStyle']['h2'] ) ? $this->parse_styles( $props['childStyle']['h2'] ) . 'margin:0;' : '';
		$class_td   = ! empty( $props['childStyle']['.td'] ) ? $this->parse_styles( $props['childStyle']['.td'] ) : '';
		$table_head = ! empty( $props['childStyle']['.head.td'] ) ? $this->parse_styles( $props['childStyle']['.head.td'] ) : '';
		$table_body = ! empty( $props['childStyle']['.body.td'] ) ? $this->parse_styles( $props['childStyle']['.body.td'] ) : '';

		$this->custom_css .= $h2 ? ".{$hook} h1,.{$hook} h2,.{$hook} h3,.{$hook} h4,.{$hook} h5,.{$hook} h6{{$h2}}" : '';
		$this->custom_css .= $h2 ? ".{$hook} h1 a,.{$hook} h2 a,.{$hook} h3 a,.{$hook} h4 a,.{$hook} h5 a,.{$hook} h6 a {{$h2}}" : '';
		$this->custom_css .= $class_td ? ".{$hook} th.td,.{$hook} td.td{padding:8px;{$class_td}}" : '';
		$this->custom_css .= $table_body ? ".{$hook} td.td{{$table_body}}" : '';
		$this->custom_css .= $table_head ? ".{$hook} thead .td{{$table_head}}" : '';
		$this->custom_css .= $this->custom_css ? ".{$hook} table{border-collapse:separate; border:none !important;} blockquote{margin:5px 20px;} .{$hook} img{padding-right:8px;} ul, li{margin:0;} div{margin-bottom:0 !important;}" : '';

		if ( $this->preview ) {
			$mailer      = wc()->mailer();
			$class_email = new \WC_Email();
			remove_action( 'woocommerce_email_order_details', [ $mailer, 'order_details' ], 10 );
			remove_action( 'woocommerce_email_order_details', [ $mailer, 'order_downloads' ], 10 );

			ob_start();
			do_action( $hook, $this->order, false, false, '' );
			$content = ob_get_clean();

			if ( ! $content ) {
				ob_start();
				?>
                <div style="margin-bottom: 20px;">
                    <h2><?php esc_html_e( 'Other plugin information', 'viwec-email-customizer' ); ?></h2>
                    <table width="100%" cellspacing="0" cellpadding="0" border="0">
                        <thead>
                        <tr>
                            <th class="td" align="left">ID</th>
                            <th class="td" align="left">Items</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="td">1</td>
                            <td class="td">Item</td>
                        </tr>
                        <tr>
                            <td class="td">2</td>
                            <td class="td">Item</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
				<?php
				$content = ob_get_clean();
			}

			$content = $class_email->style_inline( "<div class='{$hook}'>" . $content . '</div>' );
			echo str_replace( [ 'margin-bottom: 40px;' ], [ '' ], $content );
		} else {
			$args = $this->template_args;
			remove_action( 'woocommerce_email_order_details', [ \WC_Emails::instance(), 'order_details' ], 10 );

			if ( ! $this->use_hook_order_download ) {
				remove_action( 'woocommerce_email_order_details', [ \WC_Emails::instance(), 'order_downloads' ], 10 );
			}

			echo "<div class='{$hook}'>";
			do_action( $hook, $args['order'], $args['sent_to_admin'], $args['plain_text'], $args['email'] );
			echo "<div>";
		}
	}

	public function render_html_recover_heading( $props, $arr_style = [] ) {
		if ( $this->preview ) {
			echo esc_html__( 'Thank you for your order', 'viwec-email-template-customizer' );
		}

		if ( ! $this->use_default_template ) {
			return;
		}

		$rendered_heading = false;
		if ( $this->class_email && $this->class_email->get_heading() ) {
			echo wp_kses( $this->class_email->get_heading(), viwec_allowed_html() );
			$rendered_heading = true;
		}

		if ( $this->recover_heading && ! $rendered_heading ) {
			echo wp_kses_post( $this->recover_heading );
		}
	}

	public function render_html_recover_content( $props, $arr_style = [] ) {
		$link_color  = $props['childStyle']['p']['color'] ?? '#444444';
		$p           = ! empty( $props['childStyle']['p'] ) ? $this->parse_styles( $props['childStyle']['p'] ) : '';
		$h2          = ! empty( $props['childStyle']['h2'] ) ? $this->parse_styles( $props['childStyle']['h2'] ) : '';
		$h2          = str_replace( 'padding', 'margin', $h2 );
		$class_td    = ! empty( $props['childStyle']['.td'] ) ? $this->parse_styles( $props['childStyle']['.td'] ) : '';
		$table_head  = ! empty( $props['childStyle']['.head.td'] ) ? $this->parse_styles( $props['childStyle']['.head.td'] ) : '';
		$table_body  = ! empty( $props['childStyle']['.body.td'] ) ? $this->parse_styles( $props['childStyle']['.body.td'] ) : '';
		$label_items = ! empty( $props['childStyle']['th.body.td'] ) ? $this->parse_styles( $props['childStyle']['th.body.td'] ) : '';
		$value_items = ! empty( $props['childStyle']['td.body.td'] ) ? $this->parse_styles( $props['childStyle']['td.body.td'] ) : '';

		$this->custom_css .= "#viwec-transferred-content address{padding:0 !important; border:none !important;}";
		$this->custom_css .= $p ? "#viwec-transferred-content p, #viwec-transferred-content #addresses td{{$p}}" : '';
		$this->custom_css .= $h2 ? "#viwec-transferred-content h1,#viwec-transferred-content h2,#viwec-transferred-content h3,
		                            #viwec-transferred-content h4,#viwec-transferred-content h5,#viwec-transferred-content h6{{$h2}}" : '';
		$this->custom_css .= $h2 ? "#viwec-transferred-content h1 a,#viwec-transferred-content h2 a,#viwec-transferred-content h3 a,
                                    #viwec-transferred-content h4 a,#viwec-transferred-content h5 a,#viwec-transferred-content h6 a {{$h2}}" : '';
		$this->custom_css .= $class_td ? "#viwec-transferred-content th.td,#viwec-transferred-content td.td{padding:8px;{$class_td}}" : '';
		$this->custom_css .= $table_body ? "#viwec-transferred-content tbody td, #viwec-transferred-content tbody .td{{$table_body}}" : '';
		$this->custom_css .= $table_body ? "#viwec-transferred-content tfoot td, #viwec-transferred-content tfoot .td {{$table_body}}" : '';
		$this->custom_css .= $table_head ? "#viwec-transferred-content thead td, #viwec-transferred-content thead .td{{$table_head}}" : '';
		$this->custom_css .= $label_items ? "#viwec-transferred-content tfoot th, #viwec-transferred-content tfoot th.td{{$label_items}}" : '';
		$this->custom_css .= $value_items ? "#viwec-transferred-content tbody td, #viwec-transferred-content tfoot td,
		                                    #viwec-transferred-content tbody td.td, #viwec-transferred-content tfoot td.td{{$value_items}}" : '';
		$this->custom_css .= $this->custom_css ? '#viwec-transferred-content table{ border:none !important;} blockquote{margin:5px 20px;} 
                                                  #viwec-transferred-content img{padding-right:8px;} ul, li{margin:0;} ul.wc-item-meta{padding: 10px 0;}' : '';
		$this->custom_css .= "a{color:{$link_color} !important;}";

		if ( $this->preview ) {
			add_filter( 'woocommerce_email_order_items_args', [ $this, 'show_image' ] );
			wc()->mailer();
			$class_email = new \WC_Email();
			ob_start();
			printf( "<p>Hi %s, Just to let you know — we've received your order #%s, and it is now being processed:</p>", $this->order->get_billing_first_name(), $this->order->get_id() );
			do_action( 'woocommerce_email_order_details', $this->order, false, false, '' );
			do_action( 'woocommerce_email_order_meta', $this->order, false, false, '' );
			do_action( 'woocommerce_email_customer_details', $this->order, false, false, '' );
			$content = ob_get_clean();
			$content = preg_replace( '/border=[\'\"]\d+[\'\"]/', 'border="0"', $content );
			$content = '<div id="viwec-transferred-content">' . wp_kses_post( $content ) . '</div>';
			$content = $class_email->style_inline( $content );
			$content = str_replace( [ 'margin-bottom: 40px;', 'border-top-width: 4px;' ], '', $content );
			echo wp_kses_post( $content );
		}

		if ( ! $this->use_default_template ) {
			return;
		}

		if ( $this->other_message_content ) {
			$content = str_replace( [ 'margin-bottom: 40px;', 'border-top-width: 4px;' ], '', $this->other_message_content );
			$content = preg_replace( '/border=[\'"]\d+[\'"]/', 'border="0"', $content );
			echo '<div id="viwec-transferred-content">' . $content . '</div>'; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
		}
	}

	public function show_image( $args ) {
		$args['show_image'] = isset( $this->props['attrs']['show_img'] ) && $this->props['attrs']['show_img'] == 'true' ? true : false;
		$args['image_size'] = ! empty( $this->props['childStyle']['img']['width'] ) ? [ (int) $this->props['childStyle']['img']['width'], 300 ] : [ 32, 32 ];

		return $args;
	}

	public function render_html_wc_subscriptions( $props, $arr_style ) {

		$border  = $props['childStyle']['.viwec-subscription-border'] ? $this->parse_styles( $props['childStyle']['.viwec-subscription-border'] ) : '';
		$headers = [
			'ID',
			$props['content']['start_date'] ?? esc_html__( 'Start date', 'viwec-email-template-customizer' ),
			$props['content']['end_date'] ?? esc_html__( 'End date', 'viwec-email-template-customizer' ),
			$props['content']['recurring_total'] ?? esc_html__( 'Recurring total', 'viwec-email-template-customizer' ),
		];

		$header_style = $props['childStyle']['.viwec-subscription-header'] ? $this->parse_styles( $props['childStyle']['.viwec-subscription-header'] ) : '';
		$header_style .= 'padding:10px;' . $border;


		ob_start();

		$subscriptions         = $is_parent_order = '';
		$has_automatic_renewal = false;
		$args                  = $this->template_args;
		$is_admin_email        = $args['sent_to_admin'] ?? '';

		if ( function_exists( 'wcs_get_subscriptions_for_order' ) ) {
			$subscriptions   = wcs_get_subscriptions_for_order( $this->order, array( 'order_type' => 'any' ) );
			$is_parent_order = wcs_order_contains_subscription( $this->order, 'parent' );
		}


		if ( ! empty( $subscriptions && is_array( $subscriptions ) ) ) {
			?>
            <tr>
				<?php
				foreach ( $headers as $header ) {
					printf( "<th style='%s'>%s</th>", esc_attr( $header_style ), esc_html( $header ) );
				}
				?>
            </tr>
			<?php

			$i = 0;
			foreach ( $subscriptions as $subscription ) {
				$has_automatic_renewal = $has_automatic_renewal || ! $subscription->is_manual();

				$next_payment = '';
				if ( $is_parent_order && $subscription->get_time( 'next_payment' ) > 0 ) {
					$next_payment = sprintf( "<br><small>%s %s</small>", esc_html__( 'Next payment:', 'viwec-email-template-customizer' ), esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'next_payment', 'site' ) ) ) );
				}

				$cells = [
					sprintf( "<a style='color:inherit; text-decoration: underline;' href='%s'>#%s</a>", esc_url( ( $is_admin_email ) ? wcs_get_edit_post_link( $subscription->get_id() ) : $subscription->get_view_order_url() ), esc_html( $subscription->get_order_number() ) ),
					esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'start_date', 'site' ) ) ),
					esc_html( ( 0 < $subscription->get_time( 'end' ) ) ? date_i18n( wc_date_format(), $subscription->get_time( 'end', 'site' ) ) : _x( 'When cancelled', 'Used as end date for an indefinite subscription', 'woocommerce-subscriptions' ) ),
					wp_kses_post( $subscription->get_formatted_order_total() ) . $next_payment
				];

				$bg_color = $i % 2 ? ( $props['childStyle']['.viwec-subscription-body-even']['background-color'] ?? 'transparent' ) : ( $props['childStyle']['.viwec-subscription-body-odd']['background-color'] ?? 'transparent' );
				$style    = ! empty( $props['childStyle']['.viwec-subscription-body'] ) ? $this->parse_styles( $props['childStyle']['.viwec-subscription-body'] ) : '';
				$style    .= "padding:10px;text-align:center;background-color:{$bg_color};" . $border;
				?>
                <tr>
					<?php
					foreach ( $cells as $cell_content ) {
						printf( "<td style='%s'>%s</td>", esc_attr( $style ), $cell_content );
					}
					?>
                </tr>
				<?php
				$i ++;
			}
		}

		$content = ob_get_clean();
		if ( $content ) {
			$outer_style = ! empty( $props['childStyle']['.viwec-wc-subscriptions-outer'] ) ? $this->parse_styles( $props['childStyle']['.viwec-wc-subscriptions-outer'] ) : '';
			$outer_style .= 'overflow:hidden;';
			printf( "<table  width='100%%' border='0' cellpadding='0' cellspacing='0'><tr><td style='%s'>", esc_attr( $outer_style ) );
			$this->table( $content );
			echo '</td></tr></table>';
		}
	}

	public function render_html_wc_subscriptions_switched( $props, $arr_style ) {
		$subscriptions = ! empty( $this->template_args['subscriptions'] ) ? $this->template_args['subscriptions'] : '';
		if ( ! $subscriptions ) {
			return;
		}
		foreach ( $subscriptions as $subscription ) {

			if ( is_file( VIWEC_TEMPLATES . "order-items/subscriptions-switched-items.php" ) ) {
				$title = ! empty( $props['content']['title'] ) ? esc_html( $props['content']['title'] ) : '';
				if ( $title ) {
					$title .= $subscription->get_id();
					$style = $this->get_style( $props, 'childStyle', '.viwec-wc-subscriptions-title' );
					$style .= 'font-weight:bold;padding-bottom:6px;';
					printf( '<div style="%s">%s</div>', $style, $title );
				}
				?>
                <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center'>
                    <tr>
                        <td valign='top'>
							<?php
							$sent_to_admin          = $this->template_args['sent_to_admin'] ?? '';
							$order_items_table_args = array(
								'show_download_links' => ( $sent_to_admin ) ? false : $subscription->is_download_permitted(),
								'show_sku'            => $sent_to_admin,
								'show_purchase_note'  => ( $sent_to_admin ) ? false : $subscription->has_status( apply_filters( 'woocommerce_order_is_paid_statuses', array(
									'processing',
									'completed'
								) ) ),
							);
							wc_get_template( "order-items/subscriptions-switched-items.php", [
								'order'                  => $subscription,
								'items'                  => $subscription->get_items(),
								'order_type'             => 'subscription',
								'order_items_table_args' => $order_items_table_args,
								'sent_to_admin'          => $sent_to_admin,
								'plain_text'             => $this->template_args['plain_text'],
								'props'                  => $props,
								'render'                 => $this
							], '', VIWEC_TEMPLATES );
							?>
                        </td>
                    </tr>
                </table>
				<?php
			}
		}
	}

	public function table( $content, $style = '' ) {
		?>
        <table class="viwec-responsive" border='0' cellpadding='0' cellspacing='0' align='left' style='width: 100%; border-collapse: collapse;margin: 0; font-size: inherit;<?php echo esc_attr( $style ) ?>'>
			<?php echo wp_kses( $content, viwec_allowed_html() ) ?>
        </table>
		<?php
	}

	public function generate_coupon( $props ) {
		$option = $props['attrs'];
		$amount = isset( $option['data-coupon-amount'] ) && floatval( $option['data-coupon-amount'] ) >= 0 ? $option['data-coupon-amount'] : 0;
		$type   = ! empty( $option['data-discount-type'] ) ? $option['data-discount-type'] : 'percent';
		$prefix = ! empty( $option['data-discount-prefix'] ) ? $option['data-discount-prefix'] : '';

		if ( ! in_array( $type, array_keys( wc_get_coupon_types() ) ) || ( $type == 'percent' && $amount > 100 ) ) {
			return '';
		}

		$code   = $this->generate_coupon_code( $prefix );
		$coupon = new \WC_Coupon( $code );

		$coupon->set_discount_type( $type );

		$coupon->set_amount( $amount );

		if ( ! empty( $option['data-coupon-expiry-date'] ) ) {
			$coupon->set_date_expires( current_time( 'U' ) + $option['data-coupon-expiry-date'] * DAY_IN_SECONDS );
		}

		if ( ! empty( $option['data-coupon-min-spend'] ) ) {
			$coupon->set_minimum_amount( $option['data-coupon-min-spend'] );
		}

		if ( ! empty( $option['data-coupon-max-spend'] ) ) {
			$coupon->set_maximum_amount( $option['data-coupon-max-spend'] );
		}

		if ( ! empty( $option['data-coupon-individual'] ) ) {
			$coupon->set_individual_use( $option['data-coupon-individual'] );
		}

		if ( ! empty( $option['data-coupon-exclude-sale'] ) ) {
			$coupon->set_exclude_sale_items( $option['data-coupon-exclude-sale'] );
		}

		if ( ! empty( $option['data-coupon-allow-free-shipping'] ) ) {
			$coupon->set_free_shipping( $option['data-coupon-allow-free-shipping'] );
		}

		if ( ! empty( $option['data-coupon-include-product'] ) ) {
			$coupon->set_product_ids( explode( ',', $option['data-coupon-include-product'] ) );
		}

		if ( ! empty( $option['data-coupon-exclude-product'] ) ) {
			$coupon->set_excluded_product_ids( explode( ',', $option['data-coupon-exclude-product'] ) );
		}

		if ( ! empty( $option['data-coupon-include-categories'] ) ) {
			$coupon->set_product_categories( explode( ',', $option['data-coupon-include-categories'] ) );
		}

		if ( ! empty( $option['data-coupon-exclude-categories'] ) ) {
			$coupon->set_excluded_product_categories( explode( ',', $option['data-coupon-exclude-categories'] ) );
		}

		if ( ! empty( $option['data-coupon-limit-quantity'] ) ) {
			$coupon->set_usage_limit( $option['data-coupon-limit-quantity'] );
		}

		if ( ! empty( $option['data-coupon-limit-items'] ) ) {
			$coupon->set_limit_usage_to_x_items( $option['data-coupon-limit-items'] );
		}

		if ( ! empty( $option['data-coupon-limit-users'] ) ) {
			$coupon->set_usage_limit_per_user( $option['data-coupon-limit-users'] );
		}

		$coupon_id = $coupon->save();
		if ( $coupon_id && class_exists( 'Dokan_Pro' ) ) {
			update_post_meta( $coupon_id, 'admin_coupons_enabled_for_vendor', 'yes' );
		}

		return $code;
	}

	public function generate_coupon_code( $prefix = '' ) {
		$code          = $prefix;
		$character_arr = array_merge( range( 'a', 'z' ), range( 0, 9 ) );

		for ( $i = 0; $i < 8; $i ++ ) {
			$rand = rand( 0, count( $character_arr ) - 1 );
			$code .= $character_arr[ $rand ];
		}

		$args = array(
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'title'          => $code
		);

		$the_query = new \WP_Query( $args );

		if ( $the_query->have_posts() ) {
			$code = $this->generate_coupon_code( $prefix );
		}

		wp_reset_postdata();

		return $code;
	}

	public function get_suggest_products( $suggest_type, $limit, $props, $first_variation = false ) {
		$auto_atc         = ! empty( $props['attrs']['data-auto-atc'] ) ? $props['attrs']['data-auto-atc'] : '';
		$suggest_products = $return_products = $categories = $bought_ids = [];
		$orderby          = 'rand';

		if ( $this->order ) {
			$bought_ids = Utils::get_bought_ids( $this->order->get_items( 'line_item' ) );
		}

		$bought_ids = apply_filters( 'viwec_ids_to_suggest_products', $bought_ids );

		switch ( $suggest_type ) {
			case 'related':
				foreach ( $bought_ids as $id ) {
					$suggest_products = array_merge( $suggest_products, wc_get_related_products( $id ) );
				}
				break;

			case 'on_sale':
				$suggest_products = wc_get_product_ids_on_sale();
				break;

			case 'up_sell':
				foreach ( $bought_ids as $id ) {
					$upsel_ids = get_post_meta( $id, '_upsell_ids', true );
					if ( is_array( $upsel_ids ) && count( $upsel_ids ) ) {
						$suggest_products = array_merge( $suggest_products, $upsel_ids );
					}
				}
				break;

			case 'cross_sell':
				foreach ( $bought_ids as $id ) {
					$crosssell_ids = get_post_meta( $id, '_crosssell_ids', true );
					if ( is_array( $crosssell_ids ) && count( $crosssell_ids ) ) {
						$suggest_products = array_merge( $suggest_products, $crosssell_ids );
					}
				}
				break;

			case 'category':
				foreach ( $bought_ids as $id ) {
					$cats = get_the_terms( $id, 'product_cat' );
					if ( is_array( $cats ) && count( $cats ) ) {
						foreach ( $cats as $cat ) {
							$categories[] = $cat->slug;
						}
					}
				}
				break;

			case 'featured':
				$suggest_products = wc_get_featured_product_ids();
				break;

			case 'best_seller':
				$args = [ 'post_type' => 'product', 'meta_key' => 'total_sales', 'orderby' => 'meta_value_num', 'posts_per_page' => $limit ];
				if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
					$get_languages            = wpml_get_current_language();
					$args['suppress_filters'] = 0;
					do_action( 'wpml_switch_language', $get_languages ); // switch the content language

				} elseif ( class_exists( 'Polylang' ) ) {
					$get_languages = pll_current_language( 'slug' );
					$args['lang']  = $get_languages;
				}
				$query = new \WP_Query( $args );
				if ( is_wp_error( $query ) ) {
					break;
				}
				$products = $query->get_posts();
				if ( is_array( $products ) && count( $products ) ) {
					foreach ( $products as $product ) {
						$suggest_products[] = $product->ID;
					}
				}
				break;

			case 'best_rated':
				$args = [ 'post_type' => 'product', 'meta_key' => '_wc_average_rating', 'orderby' => 'meta_value_num', 'posts_per_page' => $limit ];
				if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
					$get_languages            = wpml_get_current_language();
					$args['suppress_filters'] = 0;
					do_action( 'wpml_switch_language', $get_languages ); // switch the content language

				} elseif ( class_exists( 'Polylang' ) ) {
					$get_languages = pll_current_language( 'slug' );
					$args['lang']  = $get_languages;
				}
				$query = new \WP_Query( $args );
				if ( is_wp_error( $query ) ) {
					break;
				}
				$products = $query->get_posts();
				if ( is_array( $products ) && count( $products ) ) {
					foreach ( $products as $product ) {
						$suggest_products[] = $product->ID;
					}
				}
				break;

			case 'newest':
				$orderby = 'date';
				break;
		}

		$categories       = array_unique( $categories );
		$suggest_products = array_slice( array_unique( $suggest_products ), 0, $limit );

		/*Get rule language*/

		$args = [
			'status'       => 'publish',
			'limit'        => $limit,
			'include'      => $suggest_products,
			'category'     => $categories,
			'orderby'      => $orderby,
			'order'        => 'DESC',
			'stock_status' => 'instock',
		];

		$suggest_products = wc_get_products( $args );

		if ( ! empty( $suggest_products ) ) {
			foreach ( $suggest_products as $product ) {
				$thumb_name = apply_filters( 'viwec_suggest_product_thumb', 'woocommerce_thumbnail' );
				$image      = wp_get_attachment_image_url( $product->get_image_id(), $thumb_name );
				$image_url  = $image ? $image : wc_placeholder_img_src( 'woocommerce_thumbnail' );
				$url        = $product->get_permalink();
				$pid        = $product->get_id();

				$query_args = [ 'viwec_rt' => $pid ];

				if ( $product->is_type( 'simple' ) && $auto_atc == 'true' ) {
					$query_args['add-to-cart'] = $pid;
				}

				$url = add_query_arg( $query_args, $url );

				$return_products[] = [
					'name'  => $product->get_name(),
					'price' => $product->get_price_html(),
					'image' => $image_url,
					'url'   => $url
				];
			}
		}

		return $return_products;
	}

	public function order_download( $item_id, $item, $order, $props ) {
		if ( isset( $props['attrs']['use_hook_order_download'] ) && $props['attrs']['use_hook_order_download'] == 'true' ) {
			return;
		}

		$show_downloads = $order->has_downloadable_item() && $order->is_download_permitted();
		if ( ! $show_downloads ) {
			return;
		}

		$item_data = $item->get_data();
		$pid       = ! empty( $item_data['variation_id'] ) ? $item_data['variation_id'] : $item_data['product_id'];
		$downloads = $order->get_downloadable_items();

		foreach ( $downloads as $download ) {
			if ( $pid == $download['product_id'] ) {
				$href    = esc_url( $download['download_url'] );
				$display = esc_html( $download['download_name'] );
				$expires = '';
				if ( ! empty( $download['access_expires'] ) ) {
					$datetime     = esc_attr( date( 'Y-m-d', strtotime( $download['access_expires'] ) ) );
					$title        = esc_attr( strtotime( $download['access_expires'] ) );
					$display_time = esc_html( date_i18n( get_option( 'date_format' ), strtotime( $download['access_expires'] ) ) );
					$expires      = "- <time datetime='$datetime' title='$title'>$display_time</time>";
				}
				$style = apply_filters( 'viwec_download_product_link_style', "" );
				echo "<p><a href='$href' style='" . $style . "'>" . apply_filters( 'viwec_download_product_link_text', $display ) . "</a> $expires</p>";
			}
		}
	}

	public function remove_shipping_method( $shipping_display ) {
		if ( $this->order ) {
			return '';
		}

		return $shipping_display;
	}

	public function get_style( $props, $layer1, $layer2 = '' ) {
		if ( ! $props || ! $layer1 ) {
			return '';
		}

		if ( $layer2 ) {
			$data = $props[ $layer1 ][ $layer2 ] ?? '';
		} else {
			$data = $props[ $layer1 ] ?? '';
		}

		return $this->parse_styles( $data );
	}

	public function remove_wpautop( $formatted_meta ) {
		if ( ! empty( $formatted_meta ) ) {
			foreach ( $formatted_meta as $pid => $item ) {
				$item->display_value    = Utils::unautop( $item->display_value );
				$formatted_meta[ $pid ] = $item;
			}
		}

		return $formatted_meta;
	}
}

