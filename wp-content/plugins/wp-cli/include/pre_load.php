<?php
use WP_CLI\Utils;
use WP_Rocket\Engine\Preload\Frontend\SitemapParser;

function wpcli_preload($args, $assoc_args){
	$sitemaps = wp_sitemaps_get_server();
	if ( ! $sitemaps ) {
		WP_CLI::error("sitemaps server为空");
	}

	$sitemap_index_url = $sitemaps->index->get_index_url();
	parse_sitemap($sitemap_index_url);

	WP_CLI::success( 'successfully.' );
}

function wpcli_safe_remote_get($url){
	$headers = [
		'blocking'  => false,
		'timeout'   => 0.01,
		'sslverify' => false,
	];

	wp_safe_remote_get(
		user_trailingslashit( $url ),
		$headers
	);

	#usleep( 1000000 );
}

function parse_sitemap($url) {
	$response = wp_safe_remote_get( $url );

	if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		echo $url;
		WP_CLI::error("sitemap 索引页获取失败");
	}

	$data = wp_remote_retrieve_body( $response );

	if ( ! $data ) {
		echo $url;
		WP_CLI::error("sitemap 索引数据获取失败");
	}

	$sitemap_parser = new SitemapParser();
	$sitemap_parser->set_content( $data );
	$links = $sitemap_parser->get_links();
	if(!empty($links)){
		krsort($links);
		foreach($links as $link){
			wpcli_safe_remote_get($link);
		}
	}

	$children = $sitemap_parser->get_children();

	foreach ( $children as $child ) {
		parse_sitemap($child);
	}
}