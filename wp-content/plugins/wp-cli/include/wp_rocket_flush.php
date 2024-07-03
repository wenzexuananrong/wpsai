<?php
function wpcli_wp_rocket_flush($args, $assoc_args){
	set_transient( 'rocket_clear_cache', 'all', HOUR_IN_SECONDS );
	// Remove all cache files.
	$lang = '';
	rocket_clean_domain( $lang );

	if ( '' === $lang ) {
		// Remove all minify cache files.
		rocket_clean_minify();
		rocket_clean_cache_busting();

		// Generate a new random key for minify cache file.
		$options                   = get_option( WP_ROCKET_SLUG );
		$options['minify_css_key'] = create_rocket_uniqid();
		$options['minify_js_key']  = create_rocket_uniqid();
		remove_all_filters( 'update_option_' . WP_ROCKET_SLUG );
		update_option( WP_ROCKET_SLUG, $options );
	}

	if ( get_rocket_option( 'manual_preload' ) && ( ! defined( 'WP_ROCKET_DEBUG' ) || ! WP_ROCKET_DEBUG ) ) {
		$home_url = get_rocket_i18n_home_url( $lang );

		$args = (array) apply_filters(
			'rocket_preload_after_purge_cache_request_args',
			[
				'blocking'   => false,
				'timeout'    => 0.01,
				'user-agent' => 'WP Rocket/Homepage_Preload_After_Purge_Cache',
				'sslverify'  => apply_filters( 'https_local_ssl_verify', false ),
			]
		);

		wp_safe_remote_get( $home_url, $args );

		do_action( 'rocket_after_preload_after_purge_cache', $home_url, $lang, $args );
	}

    WP_CLI::success( 'rocket cache clear successfully.' );
}