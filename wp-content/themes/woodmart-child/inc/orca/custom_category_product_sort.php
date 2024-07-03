<?php
function custom_wc_product_custom_subquery_orderby( $args ) {
    global $wpdb;

    //array( 'total_sales', '_wc_review_count' )
    $args['meta_key'] = '_super_sort';

    $args['orderby'] = 'meta_value_num';
    $args['order'] = 'DESC';
    return $args;
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'custom_wc_product_custom_subquery_orderby' );