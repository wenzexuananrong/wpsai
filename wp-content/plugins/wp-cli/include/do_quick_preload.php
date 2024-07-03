<?php
use WP_CLI\Utils;
use WP_Rocket\Engine\Preload\Frontend\SitemapParser;

function wpcli_preload($args, $assoc_args){
	$sitemaps = wp_sitemaps_get_server();
	if ( ! $sitemaps ) {
		WP_CLI::error("sitemaps server为空");
	}

	$temporary_value = wp_cache_get('orca_preload_xml');
	if (empty($temporary_value)) {
		WP_CLI::error("sitemaps xml链接为空");
	} 

	$urls = unserialize($temporary_value);

	$offset = $assoc_args['offset'];
	$limit = $assoc_args['limit'];

	$slice_urls = array_slice($urls, $offset, $limit);
	foreach ( $slice_urls as $key=>$url ) {
		if($url['status'] === 0){
			wpcli_update_preload_xml_status($key, 1);

			parse_sitemap($url['url']);

			wpcli_update_preload_xml_status($key, 2);
		}
	}

	WP_CLI::success( 'successfully.' );
}

function wpcli_update_preload_xml_status($key, $status){
	$temporary_value = wp_cache_get('orca_preload_xml');
	if (empty($temporary_value)) {
		WP_CLI::error("sitemaps xml 缓存为空");
	} 

	$urls = unserialize($temporary_value);
	$urls[$key]['status'] = $status;

	wp_cache_set('orca_preload_xml', serialize($urls));
}

function wpcli_safe_remote_get($url){
	$headers = [
		'blocking'  => false,
		'timeout'   => 10,
		'sslverify' => false,
		'user-agent' => 'WP Rocket/Preload Super Orca'
	];

	wp_safe_remote_get(
		user_trailingslashit( $url ),
		$headers
	);

	#usleep( 1000000 );
}

function parse_sitemap($url) {
	$response = wp_safe_remote_get( $url );

	$data = wp_remote_retrieve_body( $response );

	if ( ! $data ) {
		echo $url;
		WP_CLI::error("sitemap 索引数据获取失败");
	}

	$filesystem = rocket_direct_filesystem();
	$rocket_cache_path = rocket_get_constant( 'WP_ROCKET_CACHE_PATH' );

	$sitemap_parser = new SitemapParser();
	$sitemap_parser->set_content( $data );
	$links = $sitemap_parser->get_links();
	if(!empty($links)){
		shuffle($links);
		foreach($links as $link){

			$parsed_url = get_rocket_parse_url($link);
			$root = $rocket_cache_path . $parsed_url['host'] . $parsed_url['path'];
			if ( $filesystem->is_file( $root ) ) {
				$filesystem->delete( $root );
			}

			wpcli_safe_remote_get($link);
		}
	}
}

