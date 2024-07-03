<?php
function orca_hide_my_account_tab( $items ) {
    //unset( $items['my-coupons'] );
    unset( $items['downloads'] );
    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'orca_hide_my_account_tab', 999 );