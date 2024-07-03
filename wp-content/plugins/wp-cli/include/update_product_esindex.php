<?php

function wpcli_update_product_esindex($args, $assoc_args){
	global $wpdb;
		    	
	if (isset($assoc_args['post_id'])) {
		wpcli_update_product_esindex_single($assoc_args['post_id']);
    } elseif(isset($assoc_args['file_path'])){
    	wpcli_update_product_esindex_batch($assoc_args['file_path']);
    } elseif(isset($assoc_args['reindex'])){
    	wpcli_update_product_esindex_reindex($assoc_args['reindex']);
    } else {
    	WP_CLI::error("参数未传入.");
    }
}

function wpcli_update_product_esindex_reindex($page){
	global $wpdb;

	$limit = 1000;
	$sql = "select count(*) as count from wp_posts where post_type='product' and post_status='publish'";
	$res = $wpdb->get_row($wpdb->prepare($sql), ARRAY_A);
	if(empty($res)) WP_CLI::error( ' product empty.' );

	$total = $res['count'];
	$maxpage = ceil($total/$limit);
	$page = !empty($page)?$page:1;
	$maxpage = max($maxpage, $page);

	while($page <= $maxpage){
		WP_CLI::line( 'page: ' . $page . '/'.$maxpage.' indexed begain' );

		$offset = ($page-1) * $limit;
		$sql = "select ID from wp_posts where post_type='product' and post_status='publish' order by id asc limit $offset, $limit";
		$posts = $wpdb->get_results($wpdb->prepare($sql), ARRAY_A);

		$start_time = microtime(true);
		foreach($posts as $post){

			$product_id = $post['ID'];

			$index_res = \ElasticPress\Indexables::factory()->get( 'post' )->index( $product_id, true );

		    $index_res = json_decode(json_encode($index_res), true);
		    if(!empty($index_res['error'])){
		    	WP_CLI::error( $product_id . ' index error' . json_encode($index_res) );
		    }
		}
		$use_time = microtime(true)-$start_time;

		wpcli_stop_the_insanity();
	    WP_CLI::line( 'clear memory' );

		WP_CLI::line( 'page: ' . $page . '/'.$maxpage.' indexed end, use time: '. $use_time );
		$page ++;
	}
	
    WP_CLI::success( 'all product indexed successfully.' );
}

function wpcli_update_product_esindex_single($product_id){
	$index_res = \ElasticPress\Indexables::factory()->get( 'post' )->index( $product_id, true );

    $index_res = json_decode(json_encode($index_res), true);
    if(!empty($index_res['error'])){
    	WP_CLI::error( $product_id . ' index error' . json_encode($index_res) );
    }

    WP_CLI::success( $product_id . ' indexed successfully.' );
}

function wpcli_update_product_esindex_batch($file_path){
    if (!file_exists($file_path)) {
        WP_CLI::error("文件 {$file_path} 不存在.");
    }

    $content = file_get_contents($file_path);
    $items = json_decode($content, true);

    if(empty($items)){
        WP_CLI::error( 'total 0 product imported, empty file.' );
    }

    $i = 0;
    foreach($items as $product_id){
        if(empty($product_id)) continue;
        
		$index_res = \ElasticPress\Indexables::factory()->get( 'post' )->index( $product_id, true );

	    $index_res = json_decode(json_encode($index_res), true);
	    if(!empty($index_res['error'])){
	    	WP_CLI::error( $product_id . ' index error' . json_encode($index_res) );
	    }

	    WP_CLI::line( $product_id . ' indexed' );

	    if($i%2000 == 1999){
	    	wpcli_stop_the_insanity();
	    	WP_CLI::line( 'clear memory' );
	    }
		$i++;
	}

    WP_CLI::success( 'total '. $i . ' product indexed successfully.' );
}

function wpcli_stop_the_insanity() {
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
