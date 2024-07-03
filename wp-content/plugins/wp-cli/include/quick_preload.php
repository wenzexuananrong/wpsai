<?php
use WP_CLI\Utils;
use WP_Rocket\Engine\Preload\Frontend\SitemapParser;

function wpcli_preload($args, $assoc_args){
	$sitemaps = wp_sitemaps_get_server();
	if ( ! $sitemaps ) {
		WP_CLI::error("sitemaps server为空");
	}

	if(isset($assoc_args['query'])){
		$temporary_value = wp_cache_get('orca_preload_xml');
		if (empty($temporary_value)) {
			WP_CLI::error("sitemaps xml缓存为空");
		} 
		$urls = unserialize($temporary_value);
		var_dump($urls);
		WP_CLI::success( 'query successfully.' );exit;
	}

	wp_cache_delete('orca_preload_xml');

	$thread = isset($assoc_args['thread'])?$assoc_args['thread']:1;

	$sitemap_index_url = $sitemaps->index->get_index_url();
	parse_sitemap_index($sitemap_index_url, $thread);

	WP_CLI::success( 'successfully.' );
}

function parse_sitemap_index($url, $thread = 1) {
	$response = wp_safe_remote_get( $url );

	$code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		echo $url;

		//$data = wp_remote_retrieve_body( $response );

		WP_CLI::error("sitemap 索引页获取失败, code:".$code);
	}

	$data = wp_remote_retrieve_body( $response );

	if ( ! $data ) {
		echo $url;
		WP_CLI::error("sitemap 索引数据获取失败");
	}

	$sitemap_parser = new SitemapParser();
	$sitemap_parser->set_content( $data );

	$children = $sitemap_parser->get_children();
	$urls = [];
	foreach($children as $child){
		$temp = [
			'url' => $child,
			'status' => 0,
		];
		$urls[] = $temp;
	}
	shuffle($urls);
	wp_cache_set('orca_preload_xml', serialize($urls));

	$limit = ceil(count($urls)/$thread);
	for($i=1; $i<=$thread; $i++){
		$command = "wp custom do_quick_preload --offset={$i} --limit={$limit} > /dev/null 2>&1 &";
		shell_exec($command);
	}
}