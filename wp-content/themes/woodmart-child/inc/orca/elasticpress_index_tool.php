<?php

add_filter('ep_feature_is_visible',
    function ( $is_visible, $feature_slug ) {
        return 'comments' === $feature_slug ? true : $is_visible;
    }, 10, 2
);
add_filter('ep_feature_is_visible',function ( $is_visible, $feature_slug ) {
        return 'terms' === $feature_slug ? true : $is_visible;
    }, 10, 2
);
