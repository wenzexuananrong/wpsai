<?php
use WP_CLI\Utils;


function wpcli_add_product_attribute_pacolor($args, $assoc_args){       
    $file_path = $args[0];

    if (!file_exists($file_path)) {
        WP_CLI::error("文件 {$file_path} 不存在.");
    }
    
    $content = file_get_contents($file_path);
    $items = json_decode($content, true);

    if(empty($items)){
        WP_CLI::error( 'total 0 product fixed, fix file empty.' );
    }

    if (isset($assoc_args['reindex'])) {
        wpcli_add_product_attribute_pacolor_reindex($items, $assoc_args);
    } else {
        wpcli_add_product_attribute_pacolor_deal($items, $assoc_args);
    }
}

function wpcli_add_product_attribute_pacolor_deal($items, $assoc_args){
    global $wpdb;

    $find_from = 1;
    if(isset($assoc_args['offset']) && !empty($assoc_args['offset'])){
        $from_sku = $assoc_args['offset'];
        $find_from = 0;
    }

    $colorMap = [  
        'black' => 'rgb(0,0,0)',
        'yellow' => 'rgb(255,255,0)',  
        'purple' => 'rgb(128,0, 128)',  
        'pink' => 'rgb(255,192,203)', 
        'green' => 'rgb(0,128,0)',  
        'red' => 'rgb(255,0,0)',  
        'blue' => 'rgb(0,0,255)',  
        'khaki' => 'rgb(240,230,140)',  
        'brown' => 'rgb(165,42,42)',  
        'orange' => 'rgb(255,165,0)',  
        'multi' => 'rgb(50,205,50)', 
        'grey' => 'rgb(128,128,128)',  
        'white' => 'rgb(255,255,255)',  
    ];  

    $esobject = \ElasticPress\Indexables::factory()->get( 'post' );

    $i = 0;
    foreach($items as $sku=>$color){
        if($sku == $from_sku) {
            $find_from = 1;
        }
        if($find_from == 0) {
            continue;
        }

        $color = strtolower($color);

        //修改属性
        $sql = "select ID from wp_posts where post_name='".strtolower($sku)."'";
        $post = $wpdb->get_row($wpdb->prepare($sql), ARRAY_A);
        if(empty($post)) continue;

        $post_id = $post['ID'];

        $sql = "select meta_value from wp_postmeta where meta_key='%s' and post_id='%d'";
        $postmeta = $wpdb->get_row($wpdb->prepare($sql, '_product_attributes', $post_id), ARRAY_A);
        if(empty($postmeta)) continue;

        $sql = "select b.term_taxonomy_id as term_taxonomy_id  
            from wp_terms a 
            inner join wp_term_taxonomy b on a.term_id=b.term_id and taxonomy='pa_color' 
            where a.slug='%s'";
        $term_taxonomy = $wpdb->get_row($wpdb->prepare($sql, $color), ARRAY_A);
        if(empty($term_taxonomy) || empty($term_taxonomy['term_taxonomy_id'])) {
            $data = [
                'name' => ucfirst($color),
                'slug' => $color,
            ];
            if ( ! $wpdb->insert( $wpdb->terms, $data ) ) {
                WP_CLI::log(sprintf("insert term color error,  %s.", $color));
            }

            $term_id = (int) $wpdb->insert_id;

            if(isset($colorMap[$color])){
                $rgb = $colorMap[$color];
                $data = [
                    'term_id' => $term_id,
                    'meta_key' => 'color',
                    'meta_value' => $rgb,
                ];
                if ( ! $wpdb->insert( $wpdb->termmeta, $data ) ) {
                    WP_CLI::log(sprintf("insert term color error,  %s.", $color));
                }
            }
            

            $data = [
                'term_id' => $term_id,
                'taxonomy' => 'pa_color',
            ];
            if ( ! $wpdb->insert( $wpdb->term_taxonomy, $data ) ) {
                WP_CLI::log(sprintf("insert term_taxonomy color error,  %s.", $color));
            }

            $term_taxonomy_id = (int) $wpdb->insert_id;
        } else {
            $term_taxonomy_id = $term_taxonomy['term_taxonomy_id'];
        }


        $attributes = unserialize($postmeta['meta_value']);

        $pa_color = [
            'name' => 'pa_color',
            'value' => '',
            'position' => 0,
            'is_visible' => 0,
            'is_variation' => 0,
            'is_taxonomy' => 1,
        ];

        if(!empty($attributes)){
            $attributes['pa_color'] = $pa_color;
        } else {
            $attributes = [
                'pa_color' => $pa_color,
            ];
        }
        $_product_attributes =  serialize($attributes);

        $sql = "update wp_postmeta set `meta_value` ='%s' where post_id=%d and meta_key='_product_attributes'";
        $wpdb->query($wpdb->prepare($sql, $_product_attributes, $post_id));

        //新增tax
        $sql = "select * from wp_term_relationships where object_id=%d and term_taxonomy_id=%d";
        $relationship = $wpdb->get_row($wpdb->prepare($sql, $post_id, $term_taxonomy_id), ARRAY_A);
        if(empty($relationship)){
            $data = [
                'object_id' => $post_id,
                'term_taxonomy_id' => $term_taxonomy_id,
            ];
            if ( ! $wpdb->insert( $wpdb->term_relationships, $data ) ) {
                WP_CLI::log(sprintf("insert error, ID %d, SKU %s.", $post_id, $sku));
            }

            //清理元数据缓存
            wp_cache_delete($post_id, 'post_meta');

            $index_res = $esobject->index( $post_id, true );
            $index_res = json_decode(json_encode($index_res), true);
            if(!empty($index_res['error'])){
                WP_CLI::error( $post_id . ' index error' . json_encode($index_res) );

                WP_CLI::log(sprintf("index error, ID %d, SKU %s.", $post_id, $sku));
            }

            WP_CLI::line( sprintf("product indexed, ID %d, SKU %s.", $post_id, $sku) );

            if($i%2000 == 1999){
                wpcli_stop_the_insanity();
                WP_CLI::line( 'clear memory' );
            }

            $i++;
        }
    }

    WP_CLI::log(sprintf("successfully"));

    WP_CLI::success( 'total '.$i.' product deal successfully.' );
}

function wpcli_add_product_attribute_pacolor_reindex($items, $assoc_args){
    global $wpdb;

    $find_from = 1;
    if(isset($assoc_args['offset']) && !empty($assoc_args['offset'])){
        $from_sku = $assoc_args['offset'];
        $find_from = 0;
    }

    $esobject = \ElasticPress\Indexables::factory()->get( 'post' );
    $i = 0;
    foreach($items as $sku=>$color){
        if($sku == $from_sku) {
            $find_from = 1;
        }
        if($find_from == 0) {
            continue;
        }

        $color = strtolower($color);

        $sql = "select ID from wp_posts where post_name='".strtolower($sku)."'";
        $post = $wpdb->get_row($wpdb->prepare($sql), ARRAY_A);
        if(empty($post)) continue;

        $post_id = $post['ID'];

    
        $index_res = $esobject->index( $post_id, true );
        $index_res = json_decode(json_encode($index_res), true);
        if(!empty($index_res['error'])){
            WP_CLI::error( $post_id . ' index error' . json_encode($index_res) );

            WP_CLI::log(sprintf("index error, ID %d, SKU %s.", $post_id, $sku));
        }

        WP_CLI::line( sprintf("product indexed, ID %d, SKU %s.", $post_id, $sku) );

        if($i%2000 == 1999){
            wpcli_stop_the_insanity();
            WP_CLI::line( 'clear memory' );
        }

        $i++;
    }

    WP_CLI::log(sprintf("successfully"));

    WP_CLI::success( 'total '.$i.' product deal successfully.' );
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