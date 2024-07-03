<?php

namespace VIWEC\INCLUDES;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Email_Builder {

	protected static $instance = null;
	protected $send_test_error_message;

	private function __construct() {
		add_action( 'init', array( $this, 'register_custom_post_type' ), 0 );
		add_action( 'dbx_post_sidebar', array( $this, 'builder_page' ) );
		add_filter( 'get_sample_permalink_html', array( $this, 'delete_permalink' ) );
		add_filter( 'post_row_actions', array( $this, 'delete_view_action' ) );
		add_action( 'save_post_viwec_template', array( $this, 'save_post' ) );
		add_action( 'save_post_viwec_template_block', array( $this, 'save_post_block' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'background_image_style' ), 20 );
		add_filter( 'manage_viwec_template_posts_columns', array( $this, 'add_column_header' ) );
		add_action( 'manage_viwec_template_posts_custom_column', array( $this, 'add_column_content' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'post_row_actions' ), 10, 2 );
		add_action( 'post_action_viwec_duplicate', array( $this, 'duplicate_template' ) );
		add_action( 'restrict_manage_posts', array( $this, 'add_filter_dropdown' ) );
		add_filter( 'parse_query', array( $this, 'parse_query_filter' ) );
		add_filter( 'enter_title_here', array( $this, 'change_text_add_title' ) );
		add_action( 'admin_head', array( $this, 'remove_action' ), 9999 );
		add_filter( 'woocommerce_email_setting_columns', array( $this, 'add_edit_buttons_columns' ) );
		add_action( 'woocommerce_email_setting_column_viwec-edit', array( $this, 'add_edit_buttons' ) );
		add_filter( 'viwec_register_email_type', [ $this, 'add_other_type' ], 9999 );
		add_action( 'edit_form_after_title', [ $this, 'use_note' ] );
		add_action( 'edit_form_top', [ $this, 'remove_meta_boxes' ] );
		add_action( 'woocommerce_checkout_update_order_meta', [ $this, 'set_order_language' ] );

		//Ajax
		add_action( 'wp_ajax_viwec_preview_template', array( $this, 'preview_template' ) );
		add_action( 'wp_ajax_viwec_send_test_email', array( $this, 'send_test_email' ) );
		add_action( 'wp_ajax_viwec_change_admin_bar_stt', array( $this, 'change_admin_bar_stt' ) );
		add_action( 'wp_ajax_viwec_search_coupon', array( $this, 'search_coupon' ) );
		add_action( 'wp_ajax_viwec_search_post', array( $this, 'search_post' ) );
		add_action( 'wp_ajax_viwec_set_email_status', array( $this, 'set_email_status' ) );
		add_action( 'wp_ajax_nopriv_viwec_set_email_status', array( $this, 'set_email_status' ) );

//	    Send test result
		add_action( 'wp_mail_failed', [ $this, 'get_error_send_mail' ] );

		//Update
		add_action( 'admin_init', [ $this, 'update_button' ] );
	}

	public function remove_meta_boxes() {
		if ( get_current_screen()->id == 'viwec_template' ) {
			global $wp_meta_boxes;
			$wp_meta_boxes = [];
		}
	}

	public function remove_action() {
		if ( in_array( get_current_screen()->id, [ 'viwec_template', 'viwec_template_block' ] ) ) {
			remove_all_actions( 'admin_notices' );
		}
	}

	public static function init() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function delete_view_action( $actions ) {
		global $post_type;
		if ( 'viwec_template' === $post_type ) {
			unset( $actions['view'] );
		}

		return $actions;
	}

	public function delete_permalink( $link ) {
		global $post_type;
		if ( 'viwec_template' === $post_type ) {
			$link = '';
		}

		return $link;
	}

	public function register_custom_post_type() {

		$labels = array(
			'name'               => _x( 'Email Templates', 'Post Type General Name', 'viwec-email-template-customizer' ),
			'singular_name'      => _x( 'Email Templates', 'Post Type Singular Name', 'viwec-email-template-customizer' ),
			'menu_name'          => esc_html__( 'Email Templates', 'viwec-email-template-customizer' ),
			'all_items'          => esc_html__( 'All Emails', 'viwec-email-template-customizer' ),
			'add_new_item'       => esc_html__( 'Add New Email Template', 'viwec-email-template-customizer' ),
			'add_new'            => esc_html__( 'Add New', 'viwec-email-template-customizer' ),
			'edit_item'          => esc_html__( 'Edit Email Templates', 'viwec-email-template-customizer' ),
			'update_item'        => esc_html__( 'Update Email Templates', 'viwec-email-template-customizer' ),
			'search_items'       => esc_html__( 'Search Email Templates', 'viwec-email-template-customizer' ),
			'not_found'          => esc_html__( 'Not Found', 'viwec-email-template-customizer' ),
			'not_found_in_trash' => esc_html__( 'Not found in Trash', 'viwec-email-template-customizer' ),
		);

		$args = array(
			'label'               => esc_html__( 'Email Templates', 'viwec-email-template-customizer' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => false,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'product',
			'query_var'           => true,
			'capabilities'        => apply_filters( 'viwec_capabilities_role', array() ),
			'create_posts'        => apply_filters( 'viwec_create_posts_role', '' ),
			'menu_position'       => 2,
			'map_meta_cap'        => true,
			'rewrite'             => array( 'slug' => VIWEC_SLUG ),
			'menu_icon'           => 'dashicons-email'
		);

		// Registering your Custom Post Type
		register_post_type( 'viwec_template', $args );

		$labels = array(
			'name'               => _x( 'Blocks', 'Post Type General Name', 'viwec-email-template-customizer' ),
			'singular_name'      => _x( 'Block', 'Post Type Singular Name', 'viwec-email-template-customizer' ),
			'menu_name'          => esc_html__( 'Blocks', 'viwec-email-template-customizer' ),
			'parent_item_colon'  => esc_html__( 'Parent Email', 'viwec-email-template-customizer' ),
			'all_items'          => esc_html__( 'Blocks', 'viwec-email-template-customizer' ),
			'add_new_item'       => esc_html__( 'Add New Block', 'viwec-email-template-customizer' ),
			'add_new'            => esc_html__( 'Add New', 'viwec-email-template-customizer' ),
			'edit_item'          => esc_html__( 'Edit Block', 'viwec-email-template-customizer' ),
			'update_item'        => esc_html__( 'Update', 'viwec-email-template-customizer' ),
			'search_items'       => esc_html__( 'Search', 'viwec-email-template-customizer' ),
			'not_found'          => esc_html__( 'Not Found', 'viwec-email-template-customizer' ),
			'not_found_in_trash' => esc_html__( 'Not found in Trash', 'viwec-email-template-customizer' ),
		);

		$args = array(
			'label'               => esc_html__( 'Blocks', 'viwec-email-template-customizer' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=viwec_template',
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => false,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'product',
			'query_var'           => true,
			'capabilities'        => apply_filters( 'viwec_capabilities_role', array() ),
			'create_posts'        => apply_filters( 'viwec_create_posts_role', '' ),
			'menu_position'       => 2,
			'map_meta_cap'        => true,
			'rewrite'             => array( 'slug' => VIWEC_SLUG ),
		);

		// Registering your Custom Post Type
		register_post_type( 'viwec_template_block', $args );
	}

	public function builder_page( $post ) {
		if ( ! in_array( $post->post_type, [ 'viwec_template', 'viwec_template_block' ] ) ) {
			return;
		}

		$this->email_builder_box( $post );

		?>
        <div id="viwec-right-sidebar">
			<?php
			$boxes = [
				'type'       => esc_html__( 'Email type', 'viwec-email-customizer' ),
				'rule'       => esc_html__( 'Rules', 'viwec-email-customizer' ),
				'attachment' => esc_html__( 'Attachment files', 'viwec-email-customizer' ),
				'testing'    => esc_html__( 'Testing', 'viwec-email-customizer' ),
				'admin_note' => esc_html__( "Admin's note for this template", 'viwec-email-customizer' ),
				'exim_data'  => esc_html__( "Data", 'viwec-email-customizer' ),
			];

			if ( $post->post_type == 'viwec_template_block' ) {
				unset( $boxes['type'] );
				unset( $boxes['rule'] );
				unset( $boxes['attachment'] );
			}

			foreach ( $boxes as $key => $title ) {
				?>
                <div id='viwec-box-<?php echo esc_attr( $key ) ?>' class='viwec-setting-box'>
                    <div class='viwec-box-title'><?php echo esc_html( $title ) ?></div>
					<?php
					if ( method_exists( $this, 'viwec_right_sidebar_' . $key ) ) {
						$func = 'viwec_right_sidebar_' . $key;
						$this->$func( $post );
					}
					?>
                </div>
				<?php
			}

			$enable = get_post_meta( $post->ID, 'viwec_enable_img_for_default_template', true );
			$size   = get_post_meta( $post->ID, 'viwec_img_size_for_default_template', true );
			?>
            <input type="hidden" name="viwec_enable_img_for_default_template" value="<?php echo esc_attr( $enable ) ?>">
            <input type="hidden" name="viwec_img_size_for_default_template" value="<?php echo esc_attr( $size ) ?>">
        </div>
		<?php
	}

	public function email_builder_box( $post ) {
		$admin_bar_stt = Utils::get_admin_bar_stt();
		$custom_css    = get_post_meta( $post->ID, 'viwec_custom_css', true );
		$direction     = get_post_meta( $post->ID, 'viwec_settings_direction', true );
		wc_get_template( 'email-editor.php', [ 'admin_bar_stt' => $admin_bar_stt, 'custom_css' => $custom_css, 'direction' => $direction ],
			'woocommerce-email-template-customizer', VIWEC_TEMPLATES );
	}

	public function viwec_right_sidebar_type( $post ) {
		wc_get_template( 'email-type.php',
			[
				'type_selected'      => get_post_meta( $post->ID, 'viwec_settings_type', true ),
				'direction_selected' => get_post_meta( $post->ID, 'viwec_settings_direction', true ),
			],
			'woocommerce-email-template-customizer',
			VIWEC_TEMPLATES );
	}

	public function viwec_right_sidebar_rule( $post ) {
		$settings = get_post_meta( $post->ID, 'viwec_setting_rules', true );

		$params = apply_filters( 'viwec_accept_email_template_rules', [
			'template_ID'                  => $post->ID,
			'type_selected'       => get_post_meta( $post->ID, 'viwec_settings_type', true ),
			'categories_selected' => $settings['categories'] ?? [],
			'products_selected'   => $settings['products'] ?? [],
			'countries_selected'  => $settings['countries'] ?? [],
			'languages_selected'  => $settings['languages'] ?? [],
			'payments_selected'   => $settings['payment'] ?? [],
			'price_type'          => $settings['price_type'] ?? '',
			'min_price'           => $settings['min_price'] ?? '',
			'max_price'           => $settings['max_price'] ?? '',
			'priority'            => $post->menu_order ?? absint( 0 )
		] );

		wc_get_template( 'email-rules.php', $params, 'woocommerce-email-template-customizer', VIWEC_TEMPLATES );
	}

	public function viwec_right_sidebar_testing( $post ) {
		$_orders  = [];
		$statuses = wc_get_order_statuses();
		if ( ! empty( $statuses ) && is_array( $statuses ) ) {
			foreach ( $statuses as $status => $name ) {
				$arg    = [ 'numberposts' => 1, 'status' => $status ];
				$orders = wc_get_orders( $arg );
				if ( ! empty( $orders ) ) {
					$_orders[] = current( $orders );
				}
			}
		}
		wc_get_template( 'email-testing.php', [ 'orders' => $_orders ], '', VIWEC_TEMPLATES );
	}

	public function viwec_right_sidebar_exim_data() {
		?>
        <div>
            <textarea id="viwec-exim-data"></textarea>
            <div class="vi-ui buttons viwec-btn-group">
                <button type="button" class="vi-ui button tiny attached viwec-import-data"><?php esc_html_e( 'Import' ); ?></button>
                <button type="button" class="vi-ui button tiny attached viwec-export-data"><?php esc_html_e( 'Export' ); ?></button>
                <button type="button" class="vi-ui button tiny attached viwec-copy-data"><?php esc_html_e( 'Copy' ); ?></button>
            </div>
        </div>
		<?php
	}

	public function viwec_right_sidebar_admin_note( $post ) {
		$note = get_post_meta( $post->ID, 'viwec_admin_note', true );
		?>
        <div><textarea id="viwec-admin-note" name="viwec_admin_note"><?php echo wp_kses_post( $note ) ?></textarea></div>
		<?php
	}

	public function viwec_right_sidebar_attachment( $post ) {
		?>
        <div class="viwec-attachments-list">
			<?php
			$files = get_post_meta( $post->ID, 'viwec_attachments', true );
			if ( ! empty( $files ) && is_array( $files ) ) {
				foreach ( $files as $file_id ) {
					$file = get_post( $file_id );
					if ( ! $file ) {
						continue;
					}
					$href = admin_url( "upload.php?item={$file->ID}" );
					?>
                    <div class="viwec-attachment-el vi-ui button tiny">
                        <a href="<?php echo esc_url( $href ) ?>" target="_blank"><?php echo esc_html( $file->post_title ) ?></a>
                        <input type="hidden" name="viwec_attachments[]" value="<?php echo esc_attr( $file->ID ) ?>">
                        <i class="viwec-remove-attachment dashicons dashicons-no-alt"> </i>
                    </div>
					<?php
				}
			}
			?>
        </div>
        <div>
            <span class="vi-ui button tiny viwec-add-attachment-file">
                <?php esc_html_e( 'Add file', 'viwec-email-customizer' ); ?>
            </span>
        </div>
		<?php
	}

	public function save_post( $post_id ) {
		if ( ! current_user_can( 'manage_woocommerce' ) || ! isset( $_POST['post_status'] ) || ! in_array( $_POST['post_status'], [ 'publish', 'draft' ] ) ) {
			return;
		}

		$keys = [
			'viwec_settings_subject',
			'viwec_settings_type',
			'viwec_settings_direction',
			'viwec_email_structure',
			'viwec_setting_rules',
			'viwec_admin_note',
			'viwec_enable_img_for_default_template',
			'viwec_img_size_for_default_template',
			'viwec_custom_css',
			'viwec_attachments',
		];

		foreach ( $keys as $key ) {
			$value = '';
			if ( isset( $_POST[ $key ] ) ) {
				$value = $key == 'viwec_email_structure' ? wp_filter_post_kses( htmlentities( $_POST[ $key ] ) ) : wc_clean( $_POST[ $key ] );
			}
			update_post_meta( $post_id, $key, $value );
		}
	}

	public function save_post_block( $post_id ) {
		if ( ! current_user_can( 'manage_woocommerce' ) || ! isset( $_POST['post_status'] ) || ! in_array( $_POST['post_status'], [ 'publish', 'draft' ] ) ) {
			return;
		}
		$keys = [ 'viwec_email_structure', 'viwec_custom_css' ];

		foreach ( $keys as $key ) {
			$value = '';
			if ( isset( $_POST[ $key ] ) ) {
				$value = $key == 'viwec_email_structure' ? wp_filter_post_kses( htmlentities( $_POST[ $key ] ) ) : wc_clean( $_POST[ $key ] );
			}
			update_post_meta( $post_id, $key, $value );
		}
	}

	public function preview_template() {
		if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'viwec_nonce' ) ) ) {
			return;
		}

		$data = isset( $_POST['data'] ) ? json_decode( wp_unslash( html_entity_decode( wp_filter_post_kses( htmlentities( $_POST['data'] ) ) ) ), true ) : '';

		$order_id     = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
		$email_render = Email_Render::init();

		$email_render->preview = true;
		$order_id ? $email_render->order( $order_id ) : $email_render->demo_order();
		$email_render->demo_new_user();
		$email_render->render( $data );

		$custom_css = isset( $_POST['custom_css'] ) ? sanitize_text_field( $_POST['custom_css'] ) : '';
		printf( '<style type="text/css">%s</style>', wp_kses_post( $custom_css ) );

		wp_die();
	}

	public function preview_custom_css() {
		$custom_css = isset( $_POST['custom_css'] ) ? sanitize_text_field( $_POST['custom_css'] ) : '';

		return $custom_css;
	}

	public function get_error_send_mail( $message ) {
		$this->send_test_error_message = json_encode( $message->errors );
	}

	public function send_test_email() {
		if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'viwec_nonce' ) ) ) {
			return;
		}

		$data     = isset( $_POST['data'] ) ? json_decode( stripslashes( html_entity_decode( sanitize_text_field( htmlentities( $_POST['data'] ) ) ) ), true ) : '';
		$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
		$files    = ! empty( $_POST['attachments'] ) ? wc_clean( $_POST['attachments'] ) : '';

		$email_render          = Email_Render::init();
		$email_render->preview = true;
		$order_id ? $email_render->order( $order_id ) : $email_render->demo_order();
		$email_render->demo_new_user();

		add_filter( 'viwec_after_render_style', [ $this, 'preview_custom_css' ] );

		ob_start();
		$email_render->render( $data );
		$email = ob_get_clean();

		remove_filter( 'viwec_after_render_style', [ $this, 'preview_custom_css' ] );

		$headers [] = "Content-Type: text/html";
		$subject    = esc_html__( 'WooCommerce Email Customizer test email template', 'viwec-email-template-customizer' );

		$mail_to = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$result  = false;

		if ( is_email( $mail_to ) ) {
			$attachments = [];
			if ( ! empty( $files ) && is_array( $files ) ) {
				foreach ( $files as $id ) {
					$attachments[] = get_attached_file( $id );
				}
			}
			$result = wp_mail( $mail_to, $subject, $email, $headers, $attachments );
		}

		$error_mess = esc_html__( "Mailing Error Found:", 'viwec-email-template-customizer' );
		if ( $this->send_test_error_message ) {
			$error_mess .= $this->send_test_error_message;
		}

		$message = $result ? esc_html__( 'Email was sent successfully', 'viwec-email-template-customizer' ) : $error_mess;
		$result ? wp_send_json_success( $message ) : wp_send_json_error( $message );
	}

	public function background_image_style() {
		if ( get_current_screen()->id == 'viwec_template' ) {
			$img_map = Init::$img_map;
			$css     = '';

			foreach ( $img_map['social_icons'] as $type => $data ) {
				if ( is_array( $data ) && ! empty( $data ) ) {
					foreach ( $data as $slug => $name ) {
						$img = VIWEC_IMAGES . $slug . '.png';
						$css .= ".mce-i-{$slug}{background: url('{$img}') !important; background-size: cover !important;}";
					}
				}
			}
			foreach ( $img_map['infor_icons'] as $type => $data ) {
				if ( is_array( $data ) && ! empty( $data ) ) {
					foreach ( $data as $slug => $name ) {
						$img = VIWEC_IMAGES . $slug . '.png';
						$css .= ".mce-i-{$slug}{background: url('{$img}') !important; background-size: cover !important;}";
					}
				}
			}

			wp_register_style( 'viwec-inline-style', false );
			wp_enqueue_style( 'viwec-inline-style' );
			wp_add_inline_style( 'viwec-inline-style', $css );
		}

		if ( isset( $_GET['page'], $_GET['tab'] ) && $_GET['page'] == 'wc-settings' && $_GET['tab'] == 'email' ) {
			$css = ".viwec-edit-button{background-color:#ffffff; color:#333333; padding:4px 10px; border-radius:3px;display:inline-flex;border:1px solid #999999;}";
			$css .= ".viwec-email-status{line-height:1 !important;}";
			wp_add_inline_style( 'woocommerce_admin_styles', $css );

			ob_start();
			?>
            <script>
                jQuery(document).ready(function ($) {
                    $('.viwec-email-status').on('change', function () {
                        let $this = $(this),
                            status = $this.val(),
                            id = $this.data('id');
                        $('.viwec-email-status').prop('disabled', true);
                        $this.after('<span class="spinner is-active"></span>');
                        $.ajax({
                            url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
                            type: 'post',
                            dataType: 'json',
                            data: {action: 'viwec_set_email_status', status, id, nonce: '<?php echo wp_create_nonce( 'viwec_set_email_status' ) ?>'},
                            success: function () {
                                $('.viwec-email-status').prop('disabled', false);
                                $this.next('.spinner').remove();
                            }
                        });
                    });
                })
            </script>
			<?php
			$script = ob_get_clean();
			$script = str_replace( [ '<script>', '</script>' ], '', $script );
			wp_add_inline_script( 'woocommerce_admin', $script, 'after' );
		}
	}

	public function add_column_header( $cols ) {
		$cols = [
			'cb'        => '<input type="checkbox">',
			'title'     => esc_html__( 'Email subject', 'viwec-email-template-customizer' ),
			'type'      => esc_html__( 'Type', 'viwec-email-template-customizer' ),
			'recipient' => esc_html__( 'Recipient', 'viwec-email-template-customizer' ),
			'note'      => esc_html__( 'Note', 'viwec-email-template-customizer' ),
			'rules'     => esc_html__( 'Rules', 'viwec-email-template-customizer' ),
			'date'      => esc_html__( 'Date', 'viwec-email-template-customizer' )
		];

		return $cols;
	}

	public function add_column_content( $col, $post_id ) {
		$wc_mails  = Utils::get_email_ids();
		$recipient = Utils::get_email_recipient();
		$type      = get_post_meta( $post_id, 'viwec_settings_type', true );

		switch ( $col ) {
			case 'type':
				$type = $wc_mails[ $type ] ?? '';
				echo esc_html( $type );
				break;

			case 'recipient':
				echo ! empty( $recipient[ $type ] ) ? esc_html( $recipient[ $type ] ) : esc_html__( 'Customer', 'viwec-email-template-customizer' );
				break;

			case 'note':
				$note = get_post_meta( $post_id, 'viwec_admin_note', true );
				echo esc_html( $note );
				break;
			case 'rules':
				$rules = get_post_meta( $post_id, 'viwec_setting_rules', true );

				if ( ! empty( $rules ) ) {
					foreach ( $rules as $rule_key => $rule ) {
						if ( ! empty( $rule ) ) {
							echo esc_html( str_replace( '_', ' ', $rule_key ) ) . '';
							if ( ! empty( $rule ) && is_array( $rule ) ) {
								echo ': ' . esc_html( implode( ',', $rule ) );
							} else {
								echo ': ' . esc_html( $rule );
							}
							echo '<br>';
						}
					}
				}
				break;

		}

	}

	public function post_row_actions( $action, $post ) {
		if ( $post->post_type === 'viwec_template' ) {
			unset( $action['inline hide-if-no-js'] );
			$href   = admin_url( "post.php?action=viwec_duplicate&id={$post->ID}" );
			$action = [ 'viwec-duplicate' => "<a href='{$href}' onclick='this.style.visibility=\"hidden\";'>" . esc_html__( 'Duplicate', 'viwec-email-template-customizer' ) . "</a>" ] + $action;
		}

		return $action;
	}

	public function duplicate_template() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$dup_id = ! empty( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : '';
		if ( $dup_id ) {
			$current_post = get_post( $dup_id );

			$args   = [
				'post_title' => 'Copy of ' . $current_post->post_title,
				'post_type'  => $current_post->post_type,
			];
			$new_id = wp_insert_post( $args );

			$email_type       = get_post_meta( $dup_id, 'viwec_settings_type', true );
			$email_structure  = get_post_meta( $dup_id, 'viwec_email_structure', true );
			$email_categories = get_post_meta( $dup_id, 'viwec_settings_categories', true );
			$email_countries  = get_post_meta( $dup_id, 'viwec_settings_countries', true );
			update_post_meta( $new_id, 'viwec_settings_type', $email_type );
			update_post_meta( $new_id, 'viwec_email_structure', str_replace( '\\', '\\\\', $email_structure ) );
			update_post_meta( $new_id, 'viwec_settings_categories', $email_categories );
			update_post_meta( $new_id, 'viwec_settings_countries', $email_countries );
			wp_safe_redirect( admin_url( "post.php?post={$new_id}&action=edit" ) );
			exit;
		}
	}

	public function add_filter_dropdown() {
		if ( get_current_screen()->id === 'edit-viwec_template' ) {
			$emails       = Utils::get_email_ids();
			$selected_val = isset( $_GET['viwec_template_filter'] ) ? sanitize_text_field( $_GET['viwec_template_filter'] ) : '';
			echo '<select name="viwec_template_filter">';
			echo "<option value=''>" . esc_html__( 'Filter by type', 'viwec-email-template-customizer' ) . "</option>";
			foreach ( $emails as $key => $name ) {
				$selected = ( $key === $selected_val ) ? ' selected' : '';
				echo "<option value='{$key}' {$selected}>{$name}</option>";
			}
			echo '</select>';
		}
	}

	public function parse_query_filter( $query ) {
		global $pagenow;
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';
		if ( is_admin() && $pagenow == 'edit.php' && $post_type == 'viwec_template' && ! empty( $_GET['viwec_template_filter'] ) ) {
			$query->query_vars['meta_key']     = 'viwec_settings_type';
			$query->query_vars['meta_value']   = sanitize_text_field( $_GET['viwec_template_filter'] );
			$query->query_vars['meta_compare'] = '=';
		}

		return $query;
	}

	public function change_text_add_title( $title ) {
		if ( get_current_screen()->id == 'viwec_template' ) {
			$title = esc_html__( 'Add Email Subject', 'viwec-email-template-customizer' );
			echo "<div class='viwec-subject-quick-shortcode'><i class='dashicons dashicons-menu'> </i><ul> </ul></div>";
		}

		return $title;
	}

	public function change_admin_bar_stt() {
		$current_stt = Utils::get_admin_bar_stt();
		$new_stt     = $current_stt ? false : true;
		$result      = update_option( 'viwec_admin_bar_stt', $new_stt );
		if ( $result ) {
			wp_send_json_success( $new_stt );
		} else {
			wp_send_json_error();
		}
		wp_die();
	}

	public function add_edit_buttons( $email ) {
		$email_ids = Utils::get_email_ids();
		echo '<td>';

		if ( in_array( $email->id, array_keys( $email_ids ) ) ) {
			$href  = admin_url( "edit.php?post_type=viwec_template&viwec_template_filter={$email->id}" );
			$title = esc_html__( 'Edit with WC Email Template Customizer', 'viwec-email-template-customizer' );
		} else {
			$href  = admin_url( "edit.php?post_type=viwec_template&viwec_template_filter=default" );
			$title = esc_html__( 'Edit with WC Email Template Customizer - Default template', 'viwec-email-template-customizer' );
		}
		printf( "<a href='%1s' class='viwec-edit-button' title='%s'><i class='dashicons dashicons-edit'> </i></a>", esc_url( $href ), $title );
		$email_statuses = [
			'enable'  => esc_html__( 'Enable', 'viwec-email-template-customizer' ),
			'disable' => esc_html__( 'Disable', 'viwec-email-template-customizer' )
		];

		$options = get_option( 'viwec_emails_status', [] );
		$status  = $options[ $email->id ] ?? 'enable';
		?>
        <select class="viwec-email-status" data-id="<?php echo esc_html( $email->id ) ?>">
			<?php
			foreach ( $email_statuses as $value => $name ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $status, $value, false ), $name );
			}
			?>
        </select>
		<?php
		echo '</td>';
	}

	public function add_edit_buttons_columns( $columns ) {
		unset( $columns['actions'] );
		$columns['viwec-edit'] = esc_html__( 'Email Template Customize', 'viwec-email-template-customizer' );
		$columns['actions']    = '';

		return $columns;
	}

	public function add_other_type( $email_ids ) {
		return $email_ids;
	}

	public function search_coupon() {
		if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'viwec_nonce' ) ) ) {
			return;
		}
		$q = ! empty( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : '';
		if ( $q ) {
			$args    = [
				'numberposts' => - 1,
				'post_type'   => 'shop_coupon',
				's'           => $q
			];
			$coupons = get_posts( $args );
			if ( ! empty( $coupons ) && is_array( $coupons ) ) {
				$result = [];
				foreach ( $coupons as $coupon ) {
					$result[] = [ 'id' => strtoupper( $coupon->post_title ), 'text' => strtoupper( $coupon->post_title ) ];
				}

				wp_send_json( $result );
			}
		}
		wp_die();
	}

	public function search_post() {
		if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'viwec_nonce' ) ) ) {
			return;
		}
		$q = ! empty( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : '';

		if ( $q ) {
			$args  = [
				'numberposts' => - 1,
				'post_type'   => 'post',
				's'           => $q
			];
			$posts = get_posts( $args );
			if ( ! empty( $posts ) && is_array( $posts ) ) {
				$result = [];
				foreach ( $posts as $post ) {
					$result[] = [ 'id' => $post->ID, 'text' => strtoupper( $post->post_title ), 'content' => do_shortcode( $post->post_content ) ];
				}

				wp_send_json( $result );
			}
		}
		wp_die();
	}

	public function set_email_status() {
		if ( ! current_user_can( 'manage_woocommerce' ) || ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'viwec_set_email_status' ) ) ) {
			wp_send_json( false );
		}
		$id     = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
		$status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';

		if ( $id && $status ) {
			$options        = get_option( 'viwec_emails_status', [] );
			$old_option     = $options;
			$options[ $id ] = $status;
			$new_option     = $options;
			$result         = update_option( 'viwec_emails_status', $options );
			wp_send_json( [
				'result'     => $result,
				'old_option' => $old_option,
				'new_option' => $new_option,
			] );
		}
	}

	public function use_note( $post ) {
		if ( $post->post_type == 'viwec_template' ) {
			printf( "<div><p><strong>%s</strong>: %s <a href='%s' target='_blank'>%s</a></p></div>",
				esc_html__( 'Note', 'viwec-email-template-customizer' ),
				esc_html__( 'To display the content of the 3rd plugins (Checkout Field Editor, Flexible Checkout Fields,...), drag and drop the WC Hook element to the position which you want to display it in email template.', 'viwec-email-template-customizer' ),
				esc_url( 'https://docs.villatheme.com/woocommerce-email-template-customizer/#configuration_child_menu_4783' ),
				esc_html__( 'View detail in document.', 'viwec-email-template-customizer' )
			);
		}
	}

	public function set_order_language( $order_id ) {
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$orderObject = wc_get_order( $order_id );
			$orderObject->update_meta_data( 'wpml_language', ICL_LANGUAGE_CODE );
			$orderObject->save_meta_data();
		}
	}

	public function update_button() {
		$check_updated = get_option( 'viwec_email_update_button' );
		if ( $check_updated ) {
			return;
		}

		$posts = get_posts( [ 'post_type' => 'viwec_template', 'post_status' => 'any', 'posts_per_page' => - 1 ] );
		if ( ! empty( $posts ) && is_array( $posts ) ) {
			$end = count( $posts );
			foreach ( $posts as $i => $post ) {
				$post_id = $post->ID;
				$old     = get_post_meta( $post_id, 'viwec_email_structure', true );
				$data    = json_decode( html_entity_decode( $old ), true );

				foreach ( $data['rows'] as $row_key => $row ) {
					if ( ! empty( $row['cols'] ) ) {
						foreach ( $row['cols'] as $col_key => $cols ) {
							if ( ! empty( $cols['elements'] ) ) {
								foreach ( $cols['elements'] as $el_key => $element ) {
									if ( $element['type'] == 'html/button' ) {
										$new_el                         = $element;
										$new_el['style']['line-height'] = '46px';
										unset( $new_el['childStyle']['a']['padding'] );

										$data['rows'][ $row_key ]['cols'][ $col_key ]['elements'][ $el_key ] = $new_el;
									}
								}
							}
						}
					}
				}
				$data = htmlentities( json_encode( $data ) );
				$data = str_replace( '\\', '\\\\', $data );
				update_post_meta( $post_id, 'viwec_email_structure', $data );

				if ( $i + 1 == $end ) {
					update_option( 'viwec_email_update_button', true, 'no' );
				}
			}
		}

	}
}

