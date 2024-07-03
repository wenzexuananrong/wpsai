<?php

function orca_stop_the_insanity() {
	global $wpdb, $wp_object_cache, $wp_actions;
	$wpdb->queries = [];
	$wp_actions = [];

	if ( function_exists( 'wp_cache_flush_runtime' ) ) {
		wp_cache_flush_runtime();
	} else {
		if ( ! wp_using_ext_object_cache() ) {
			wp_cache_flush();
		}
	}

	if ( is_object( $wp_object_cache ) ) {
		$wp_object_cache->group_ops      = [];
		$wp_object_cache->stats          = [];
		$wp_object_cache->memcache_debug = [];

		try {
			$cache_property = new \ReflectionProperty( $wp_object_cache, 'cache' );
			if ( $cache_property->isPublic() ) {
				$wp_object_cache->cache = [];
			}
			unset( $cache_property );
		} catch ( \ReflectionException $e ) {
		}

		if ( is_callable( $wp_object_cache, '__remoteset' ) ) {
			call_user_func( [ $wp_object_cache, '__remoteset' ] );
		}
	}
}


function orca_elasticsearch_query($query) {
    // 设置 Elasticsearch 服务器的地址和端口
    $elasticsearch_url = EP_HOST.'/'.EP_POST_INDEX.'/_search';

    // 配置请求参数
    $args = array(
        'body' => json_encode($query),
        'headers' => array(
            'Content-Type' => 'application/json'
        )
    );

    // 发送 HTTP 请求
    $response = wp_remote_post($elasticsearch_url, $args);

    // 检查响应
    if (is_wp_error($response)) {
        return false;
    }

    // 获取响应主体
    $body = wp_remote_retrieve_body($response);

    // 解码 JSON 响应
    $data = json_decode($body, true);

    return $data;
}