<?php

function wpcli_fix_product_category($args, $assoc_args){
	global $wpdb;
		    	
    $file_path = $args[0];

    if (!file_exists($file_path)) {
        WP_CLI::error("文件 {$file_path} 不存在.");
    }
    
    $content = file_get_contents($file_path);
	$items = json_decode($content, true);

	if(empty($items)){
		WP_CLI::error( 'total 0 product fixed, fix file empty.' );
	}

	$term_ids = [];

	$esobject = \ElasticPress\Indexables::factory()->get( 'post' );

    $i = 0;
	foreach($items as $item){
	    if(empty($item)) continue;

	    foreach($item as $products){
	    	if(empty($products)) continue;

	    	foreach($products as $product_id=>$term_id){
				//获取新的分类的 term_taxonomy_id
				if(!isset($term_ids[$term_id])){
					$sql = "SELECT term_taxonomy_id FROM wp_term_taxonomy WHERE term_id = {$term_id} AND taxonomy = 'product_cat'";
					$res = $wpdb->get_row($wpdb->prepare($sql), ARRAY_A);
					if(empty($res)){
						WP_CLI::error( 'category term taxonomy id empty' );
					}
					$new_term_taxonomy_id = $res['term_taxonomy_id'];
					$term_ids[$term_id] = $new_term_taxonomy_id;
				} else {
					$new_term_taxonomy_id = $term_ids[$term_id];
				}

			    $sql = "DELETE FROM wp_term_relationships WHERE object_id = {$product_id} AND term_taxonomy_id 
			    IN (SELECT term_taxonomy_id FROM wp_term_taxonomy WHERE taxonomy = 'product_cat')";
			    $wpdb->query($sql);
				
				$data = [
					'object_id' => $product_id,
					'term_taxonomy_id' => $new_term_taxonomy_id,
				];
			    $wpdb->insert( $wpdb->term_relationships, $data );

			    $wpdb->query("update wp_term_taxonomy set `count`=count+1 where term_taxonomy_id={$new_term_taxonomy_id}");

			    $index_res = $esobject->index( $product_id, true );
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
		}
	}


    WP_CLI::success( 'total '. $i . ' product fixed successfully.' );
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
