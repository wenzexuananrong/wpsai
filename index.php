<?php
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define( 'WP_USE_THEMES', true );

/** Loads the WordPress Environment and Template */
require __DIR__ . '/wp-blog-header.php';

/*if(SAVEQUERIES){

	global $wpdb;
	
	$temp = '';
	$queries = $wpdb->queries;
	foreach ($queries as $query) {
		if(is_array($query)){
			foreach($query as $t){
				if(is_array($t)){
					$temp .= json_encode($t)."\n";
				} else {
					$temp .= $t."\n";
				}
			}
		} else {
			$temp .= $query;
		}
	}
	$temp .= "\n\n ---------------xxxx-----------\n\n";
	 
	file_put_contents('D:\phpstudy_pro\WWW\wordps.com\sql.log', $temp, FILE_APPEND);
}
*/