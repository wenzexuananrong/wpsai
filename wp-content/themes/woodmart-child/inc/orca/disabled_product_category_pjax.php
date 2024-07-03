<?php

function add_disable_pjax_class_to_pagination_links( $link ) {
    $link = str_replace( 'class="page-numbers"', 'class="page-numbers disable-pjax"', $link );
    return $link;
}
add_filter( 'woocommerce_pagination_args', 'add_disable_pjax_class_to_pagination_links' );

function orca_wp_print_inline_script_tag(){
    wp_print_inline_script_tag( "
        jQuery(document).ready(function() {
            jQuery('.woocommerce-pagination .page-numbers').addClass('disable-pjax');
            
            // 监听分页链接点击事件
            jQuery(document).on('click', '.woocommerce-pagination li a', function(event) {
                if (jQuery(this).hasClass('disable-pjax')) {
                    event.preventDefault();
                    let href = jQuery(this).attr('href');
                    window.location = href;
                }
            });
        });
    ", [ 'type' => 'text/javascript' ] );
}

add_action('wp_head', 'orca_wp_print_inline_script_tag');