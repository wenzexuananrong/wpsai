<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<dl>
    <dt><?php esc_html_e( 'User:', 'loyalty-program-for-woocommerce' ); ?></dt>
    <dd><a href="<?php echo esc_url( get_edit_user_link( $this->_user->ID ) ); ?>"><?php echo esc_html( $this->_user->user_nicename ); ?></a></dd>
   
    <dt><?php esc_html_e( 'Points:', 'loyalty-program-for-woocommerce' ); ?></dt>
    <dd><?php echo esc_html( $points ); ?></dd>

    <dt><?php esc_html_e( 'Status:', 'loyalty-program-for-woocommerce' ); ?></dt>
    <dd><?php echo esc_html( $status ); ?></dd>
</dl>
