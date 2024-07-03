<?php

add_action( 'woocommerce_checkout_update_order_meta', 'orca_product_link_track_record', 10, 1);
function orca_product_link_track_record( $order_id ) {
    $pid = WC()->session->get( 'viwec_come_from_orca_track' );
    if ( $pid ) {
        update_post_meta( $order_id, '_viwec_from_orca_track_id', $pid );
        WC()->session->__unset( 'viwec_come_from_orca_track' );
    }
}

add_action( 'woocommerce_init', 'orca_save_clicked' );
function orca_save_clicked() {
    if ( ! empty( $_REQUEST['_viwec_from_product_track_id'] ) ) {
        $pid = sanitize_text_field( $_REQUEST['_viwec_from_product_track_id'] );
        if ( ! is_user_logged_in() && isset( WC()->session ) && ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }
        WC()->session->set( 'viwec_come_from_orca_track', $pid );
    }
}

//获取订单
add_action( 'rest_api_init', 'orca_wc_api_init_get_orders' );

function orca_wc_api_init_get_orders() {
    register_rest_route( 'orca-wc/v3', '/orders', array(
        'methods' => 'GET',
        'callback' => 'orca_wc_get_orders',
    ) );
}

function orca_wc_get_orders( $data ) {
    global $wpdb;

    $page = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;
    $per_page = 30; 
    $offset = ( $page - 1 ) * $per_page; 

    $query = $wpdb->prepare( "
        SELECT p.ID AS order_id, pm.meta_value AS meta_value
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'shop_order'
        AND p.post_status IN ('wc-processing', 'wc-completed')
        AND pm.meta_key = '_viwec_from_orca_track_id'
        ORDER BY p.post_date DESC
        LIMIT %d OFFSET %d;
    ", $per_page, $offset );

    $results = $wpdb->get_results( $query );

    $orders = array();
    foreach ( $results as $result ) {
        $orders[] = array(
            'order_id'   => $result->order_id,
            'meta_value' => $result->meta_value,
        );
    }

    // 获取订单总数
    $count_query = $wpdb->prepare( "
        SELECT COUNT(p.ID)
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'shop_order'
        AND p.post_status IN ('wc-processing', 'wc-completed')
        AND pm.meta_key = '_viwec_from_orca_track_id'
    " );
    $total_items = $wpdb->get_var( $count_query );

    $total_pages = ceil( $total_items / $per_page );

    $response = array(
        'orders'     => $orders,
        'pagination' => array(
            'page'        => $page,
            'total_pages' => $total_pages,
        ),
    );

    return new WP_REST_Response( $response, 200 );
}