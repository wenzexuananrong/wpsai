<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class VI_WOOCOMMERCE_COUPON_BOX_Admin_System {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu_page' ), 999 );
	}

	public function page_callback() { ?>
        <div class="wrap">
            <h2><?php esc_html_e( 'System Status', 'woocommerce-coupon-box' ) ?></h2>
            <table cellspacing="0" id="status" class="widefat">
                <tbody>
                <tr>
                    <td data-export-label="<?php esc_html_e( 'PHP Time Limit', 'woocommerce-coupon-box' ) ?>"><?php esc_html_e( 'PHP Time Limit', 'woocommerce-coupon-box' ) ?></td>
                    <td><?php echo ini_get( 'max_execution_time' ); ?></td>
                </tr>
                <tr>
                    <td data-export-label="<?php esc_html_e( 'PHP Max Input Vars', 'woocommerce-coupon-box' ) ?>"><?php esc_html_e( 'PHP Max Input Vars', 'woocommerce-coupon-box' ) ?></td>

                    <td><?php echo ini_get( 'max_input_vars' ); ?></td>
                </tr>
                <tr>
                    <td data-export-label="<?php esc_html_e( 'Memory Limit', 'woocommerce-coupon-box' ) ?>"><?php esc_html_e( 'Memory Limit', 'woocommerce-coupon-box' ) ?></td>

                    <td><?php echo ini_get( 'memory_limit' ); ?></td>
                </tr>
                </tbody>
            </table>
        </div>
	<?php }

	function menu_page() {
		add_submenu_page(
			'edit.php?post_type=wcb',
			esc_html__( 'System Status', 'woocommerce-coupon-box' ),
			esc_html__( 'System Status', 'woocommerce-coupon-box' ),
			'manage_options',
			'woocommerce_coupon_box_status',
			array( $this, 'page_callback' )
		);
	}
}