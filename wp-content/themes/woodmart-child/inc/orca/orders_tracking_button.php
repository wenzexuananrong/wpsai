<?php
remove_all_actions( 'woocommerce_my_account_my_orders_column_woo-orders-tracking' );
add_action( 'woocommerce_my_account_my_orders_column_woo-orders-tracking', 'orca_my_account_orders_tracking_column', 10, 1 );
    
function orca_my_account_orders_tracking_column( $order ) {
    $order_id = $order->get_id();
    $tracking_url = get_site_url().'/track-order/?search_type=order_num&keyword='.$order_id; 
    $full_tracking_url = $tracking_url . $order_id;
    echo '<a href="' . esc_url( $full_tracking_url ) . '"  class="orca_order_tracking_btn" target="_blank">Track</a>';
}