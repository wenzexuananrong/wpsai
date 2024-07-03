<?php
function disable_rocket_preload_delay_delete_non_accessed( $value ) {
    return false;
}
add_filter('rocket_preload_delay_delete_non_accessed', 'disable_rocket_preload_delay_delete_non_accessed')