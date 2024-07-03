<?php

//获取订单
add_action( 'rest_api_init', 'orca_wc_api_init_add_product' );

if(!function_exists('orcaObjectToArray')){
	function orcaObjectToArray($object) {
	    $reflectionObject = new ReflectionObject($object);
	    $array = [];
	    
	    foreach($reflectionObject->getProperties() as $property) {
	        $property->setAccessible(true);
	        $propertyName = $property->getName();
	        $propertyValue = $property->getValue($object);
	        
	        $array[$propertyName] = $propertyValue;
	    }
	    
	    return $array;
	}
}

function orca_wc_api_init_add_product() {
    register_rest_route( 'orca-wc/v3', '/products', array(
        'methods' => 'POST',
        'callback' => 'orca_wc_add_product',
    ) );
}

function orca_wc_add_product( $data ) {
    global $wpdb;

    $array = orcaObjectToArray($data);


    if(!isset($array['params']['JSON']) || empty($array['params']['JSON'])){
    	$response = [
    		"code" => "PARAMS ERROR",
		    "message" => "参数为空",
		    "data" => [
		    	"status" => 1001,
		    ]
    	];
    	return new WP_REST_Response( $response, 400 );
    }

	$params = $array['params']['JSON'];
	if(!isset($params['images'])){
    	$response = [
    		"code" => "IMAGE ID ERROR",
		    "message" => "IMAGE ID 参数为空",
		    "data" => [
		    	"status" => 1002,
		    ]
    	];
    	return new WP_REST_Response( $response, 400 );
	}

	$sku = $params['sku'];
	//check sku
	if(orca_ap_sku_exists($sku)){
		$response = [
    		"code" => "product_invalid_sku",
		    "message" => "Invalid or duplicated SKU.",
		    "data" => [
		    	"status" => 1003,
		        "unique_sku" => $sku,
		    ]
    	];
    	return new WP_REST_Response( $response, 400 );
	}

	$variable_term_id = orca_ap_get_variable();
	if(empty($variable_term_id)){
    	$response = [
    		"code" => "SLUG ERROR",
		    "message" => "变体SLUG不存在",
		    "data" => [
		    	"status" => 1004,
		    ]
    	];
    	return new WP_REST_Response( $response, 400 );
	}

	$slug = strtolower($sku);
	$guid = get_site_url().'/products/'.$slug;

	$date = date('Y-m-d H:i:s');
	date_default_timezone_set('UTC');
	$utc_date = date('Y-m-d H:i:s');

	$product_data = [
		'post_author' => 1,
	    'post_date' => $date,
	    'post_date_gmt' => $utc_date,
	    'post_content' => $data['description'],
	    'post_content_filtered' => '',
	    'post_title' => $params['name'],
	    'post_excerpt' => strip_tags($params['short_description']),
	    'post_status' => 'publish',
	    'post_type' => 'product',
	    'comment_status' => 'open',
	    'ping_status' => 'closed',
	    'post_password' => '',
	    'post_name' => $slug,
	    'to_ping' => '',
	    'pinged' => '',
	    'post_modified' => $date,
	    'post_modified_gmt' => $utc_date,
	    'post_parent' => 0,
	    'guid' => $guid,
	    'menu_order' => 0,
	    'post_mime_type' => '',
	];
	$post_id = orca_ap_insert_post($product_data);

	$datas = [
		[
			'id' => $variable_term_id,
		],
	];
	orca_ap_add_tags($post_id, $datas);

	orca_ap_insert_sku_lookup( $post_id, $sku);

	$postmeta_datas = [
		'_sku' => strtoupper($sku),
		'total_sales' =>  0,
		'_tax_status' => 'taxable',
		'_tax_class' => '',
		'_manage_stock' => 'no',
		'_backorders' => 'no',
		'_sold_individually' => 'no',
		'_virtual' => 'no',
		'_downloadable' => 'no',
		'_download_limit' => -1,
		'_download_expiry' => -1,
		'_wc_average_rating' =>  0,
		'_wc_review_count' =>  0,
		'_product_version' =>  0,
	];

	if(isset($params['stock_quantity'])){
		$postmeta_datas['_stock'] = $params['stock_quantity'];
	}

	if(isset($params['stock_status'])){
		$postmeta_datas['_stock_status'] = $params['stock_status'];
	}

	if(isset($params['default_attributes'])){
		$default_attributes = [];
		foreach($params['default_attributes'] as $attr){
			$default_attributes[strtolower($attr['name'])] = $attr['option'];
		}
		$postmeta_datas['_default_attributes'] =  serialize($default_attributes);
	}

	$attributes = [];

	$color = $params['color'];
	$res = orca_ap_insert_pacolor( $post_id, $color);
	if($res !== true){
		$response = [
    		"code" => "ADD PACOLOR ERROR",
		    "message" => $res,
		    "data" => [
		    	"status" => 1009,
		    ]
    	];
    	return new WP_REST_Response( $response, 500 );
	}


	if(!empty($color)){
		$temp = [
	        'name' => 'pa_color',
	        'value' => '',
	        'position' => 0,
	        'is_visible' => 0,
	        'is_variation' => 0,
	        'is_taxonomy' => 1,
	    ];
	    $attributes['pa_color'] = $temp;
	}

	if(isset($params['attributes'])){
		foreach($params['attributes'] as $attr){
			$temp_value = implode('|', $attr['options']);
			$temp = [
            	'name' => $attr['name'],
            	'value' => $temp_value,
            	'position' => 0,
            	'is_visible' => 1,
            	'is_variation' => 1,
            	'is_taxonomy' => 0,
			];
			$attributes[$attr['name']] = $temp;
		}
	}

	if(!empty($attributes)){
		$postmeta_datas['_product_attributes'] =  serialize($attributes);
	}

	
	if(isset($params['meta_data'])){
		foreach($params['meta_data'] as $meta){
			$postmeta_datas[$meta['key']] = $meta['value'];
		}
	}

	if(isset($params['images'])){
		$images = [];
		foreach($params['images'] as $image){
			if(empty($postmeta_datas['_thumbnail_id'])) {
				$postmeta_datas['_thumbnail_id'] = $image['id'];
			} else {
				$images[] = $image['id'];
			}
		}
		
		$postmeta_datas['_product_image_gallery'] = implode(',', $images);
		orca_ap_update_image($image['id'], $post_id);
	}

	$res = orca_batch_insert_postmeta($post_id, $postmeta_datas);
	if($res !== true){
		$response = [
    		"code" => "DB ERROR",
		    "message" => $res,
		    "data" => [
		    	"status" => 1008,
		    ]
    	];
    	return new WP_REST_Response( $response, 500 );
	}

	if(!empty($params['tags'])){
		orca_ap_add_tags($post_id, $params['tags']);
	}

	if(!empty($params['categories'])){
		orca_ap_add_tags($post_id, $params['categories']);
	}

    $response = [
		'id'=> $post_id,
	];
    return new WP_REST_Response( $response, 200 );
}

function orca_ap_insert_pacolor($post_id, $color){
	global $wpdb;

	if(empty($color)) return true;

	$color = strtolower($color);

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

    $cache_key = 'orca_pa_color_term_taxonomy_slug';
    $term_taxonomy = wp_cache_get($cache_key);
    if(empty($term_taxonomy)){
	    $sql = "select b.term_taxonomy_id as term_taxonomy_id  
	        from wp_terms a 
	        inner join wp_term_taxonomy b on a.term_id=b.term_id and taxonomy='pa_color' 
	        where a.slug='%s'";
	    $term_taxonomy = $wpdb->get_row($wpdb->prepare($sql, $color), ARRAY_A);

	    if(!empty($term_taxonomy)){
		    wp_cache_set($cache_key, $term_taxonomy, 'orca', 1200);//10 min
	    }
	} 
    if(empty($term_taxonomy) || empty($term_taxonomy['term_taxonomy_id'])) {
        $data = [
            'name' => ucfirst($color),
            'slug' => $color,
        ];
        $wpdb->insert( $wpdb->terms, $data );
        if ($wpdb->last_error) {  
		    return "新增term color错误: " . $wpdb->last_error;
		}

        $term_id = (int) $wpdb->insert_id;

        if(isset($colorMap[$color])){
            $rgb = $colorMap[$color];
            $data = [
                'term_id' => $term_id,
                'meta_key' => 'color',
                'meta_value' => $rgb,
            ];
            $wpdb->insert( $wpdb->termmeta, $data );
	        if ($wpdb->last_error) {  
			    return "新增term_meta color错误: " . $wpdb->last_error;
			}
        }
        

        $data = [
            'term_id' => $term_id,
            'taxonomy' => 'pa_color',
        ];
        $wpdb->insert( $wpdb->term_taxonomy, $data );
        if ($wpdb->last_error) {  
		    return "新增term_taxonomy color错误: " . $wpdb->last_error;
		}

        $term_taxonomy_id = (int) $wpdb->insert_id;
    } else {
        $term_taxonomy_id = $term_taxonomy['term_taxonomy_id'];
    }

    //新增tax
    $data = [
        'object_id' => $post_id,
        'term_taxonomy_id' => $term_taxonomy_id,
    ];
    
    $wpdb->insert( $wpdb->term_relationships, $data );
    if ($wpdb->last_error) {  
	    return "新增pa color错误: " . $wpdb->last_error;
	}

	return true;
}

function orca_ap_get_variable(){
	global $wpdb;  
  
	$res = $wpdb->get_row($wpdb->prepare("SELECT term_id FROM $wpdb->terms WHERE slug = 'variable' and name='variable' limit 1"),   ARRAY_A);
	if(empty($res)){
		return false;
	}
	return $res['term_id'];
}


function orca_ap_insert_sku_lookup( $post_id, $sku){
    global $wpdb;

    $datas = array(
        'product_id' => $post_id,
        'sku' => $sku, 
        'min_price' => 0,
        'max_price' => 0,
    );
    $result = $wpdb->insert( $wpdb->wc_product_meta_lookup, $datas );
}

function orca_ap_sku_exists($sku){
	global $wpdb;  
  
	$res = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_name = %s and post_status='publish' limit 1",  strtolower($sku) ),   ARRAY_A);  
	  
	if ($res) {  
	   	return true; 
	} else {  
	    return false;  
	}
}


function orca_ap_add_tags($post_id, $tags){
    global $wpdb;

    $tags = array_column($tags, 'id');
    $tags = array_unique($tags);

    $sql = "INSERT INTO {$wpdb->term_relationships} (object_id, term_taxonomy_id, term_order) VALUES ";  
	$values = array(); 
	  
	foreach ($tags as $tag_id) {  
	    $values[] = "({$post_id}, {$tag_id}, 0)";

	    $wpdb->query("update wp_term_taxonomy set `count`=count+1 where term_taxonomy_id={$tag_id}");  
	}  
	  
	$sql .= implode(', ', $values);  
	  
	$wpdb->query($sql);  
	  
	if ($wpdb->last_error) {  
	    return "新增标签错误: " . $wpdb->last_error;
	}

	return true;
}

function orca_ap_update_image($image_id, $post_id){
    global $wpdb;
    $wpdb->query("update wp_posts set `post_parent` ='{$post_id}' where ID='{$image_id}'");
}


function orca_ap_insert_post($data){
    global $wpdb;

    if ( ! $wpdb->insert( $wpdb->posts, $data ) ) {
        return false;
    }

    $id = (int) $wpdb->insert_id;

    return $id;
}

function orca_ap_insert_postmeta( $post_id, $meta_key, $meta_value){
    global $wpdb;

    $datas = array(
        'post_id' => $post_id,
        'meta_key' => $meta_key, 
        'meta_value' => $meta_value,
    );
    $result = $wpdb->insert( $wpdb->postmeta, $datas );

    //$mid = (int) $wpdb->insert_id;
}

function orca_batch_insert_postmeta($post_id, $datas){
	global $wpdb;  
	  
	$values = [];
	$placeholders = [];

	foreach ($datas as $key => $val) {
	    $values[] = $post_id;
	    $values[] = $key;
	    $values[] = $val;
	    $placeholders[] = "(%d, %s, %s)";
	}

	$query = "INSERT INTO {$wpdb->postmeta} (`post_id`, `meta_key`, `meta_value`) VALUES ";
	$query .= implode(', ', $placeholders);

	$wpdb->query($wpdb->prepare("$query ", $values));

	if ($wpdb->last_error) {  
	    return "新增产品属性错误: " . $wpdb->last_error;
	}

	return true;
}