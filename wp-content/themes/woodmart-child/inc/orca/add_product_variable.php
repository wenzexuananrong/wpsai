<?php

//获取订单
add_action( 'rest_api_init', 'orca_wc_api_init_add_variable' );

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

function orca_wc_api_init_add_variable() {
    register_rest_route( 'orca-wc/v3', 'products/(?P<product_id>[\d]+)/variations/batch', array(
        'methods' => 'POST',
        'callback' => 'orca_wc_add_product_variable',
    ) );
}

function orca_wc_add_product_variable( $data ) {
    global $wpdb;

    $array = orcaObjectToArray($data);

    if(!isset($array['params']['JSON']['create']) || empty($array['params']['JSON']['create'])){
    	$response = [
    		"code" => "PARAMS ERROR",
		    "message" => "参数为空",
		    "data" => [
		    	"status" => 1001,
		    ]
    	];
    	return new WP_REST_Response( $response, 400 );
    }

    if(!isset($array['params']['URL']['product_id']) || empty($array['params']['URL']['product_id'])){
    	$response = [
    		"code" => "POST ID PARAMS ERROR",
		    "message" => "POST ID参数为空",
		    "data" => [
		    	"status" => 1002,
		    ]
    	];
    	return new WP_REST_Response( $response, 400 );
    }

	$post_id = $array['params']['URL']['product_id'];
    $post = orca_apv_get_post($post_id);
    if(empty($post)){
    	$response = [
    		"code" => "POST NOT EXISTS",
		    "message" => "POST不存在",
		    "data" => [
		    	"status" => 1003,
		    ]
    	];
    	return new WP_REST_Response( $response, 400 );
    }

    $params = $array['params']['JSON']['create'];

    $variable_ids = [];
	foreach($params as $data){
		$variable_id = orca_apv_add_product_variable($post, $data);
		$temp = [
			'id' => $variable_id,
		];
		$variable_ids[] = $temp;
	}

	$index_res = \ElasticPress\Indexables::factory()->get( 'post' )->index( $post_id, true );
    $index_res = json_decode(json_encode($index_res), true);
    if(!empty($index_res['error'])){
    	$response = [
    		"code" => "POST INDEX FAILED",
		    "message" => "索引创建失败",
		    "data" => [
		    	"status" => 1009,
		    ]
    	];
    	return new WP_REST_Response( $response, 500 );
    }

    $response = [
		'create'=> $variable_ids,
	];
    return new WP_REST_Response( $response, 200 );
}

function orca_apv_add_product_variable($post, $params){
	$post_id = $post['ID'];
	$date = date('Y-m-d H:i:s');
	date_default_timezone_set('UTC');
	$utc_date = date('Y-m-d H:i:s');

	$guid = get_site_url().'?post_type=product_variation&p='.$post_id;

	$postmeta_datas = [
		'_backorders' => 'no',
		'_download_limit' => -1,
		'_download_expiry' => -1,
		'_downloadable' => 'no',
		'_manage_stock' => 'no',
		'_price' =>  $params['sale_price'],
		'_sale_price' =>  $params['sale_price'],
		'_regular_price' =>  $params['regular_price'],
		'_product_version' => '8.6.1',
		'_sold_individually' => 'no',

		'total_sales' =>  0,
		'_stock' =>  0,
		'_stock_status' =>  'instock',
		'_tax_status' => 'taxable',
		'_tax_class' => 'parent',
		'_thumbnail_id' =>  $params['image']['id'],
		'_variation_description' => '',
		'_virtual' => 'no',
	
		'_wc_average_rating' =>  0,
		'_wc_review_count' =>  0,
		'_wc_pinterest_condition' =>  '',
	];

	$attributes = [];
	$attributes1 = [];
	if(isset($params['attributes'])){
		foreach($params['attributes'] as $attr){
			$attributes[] = $attr['option'];
			$attributes1[] = $attr['name'].': '.$attr['option'];

			$attr_key = 'attribute_'.strtolower($attr['name']);
			$postmeta_datas[$attr_key] = $attr['option'];
		}
	}

	$post_title = $post['post_title'].' - '. implode(', ', $attributes);
	$post_excerpt = implode(', ', $attributes1);
	$post_name = strip_tags($post_title);
	$post_name = str_replace(['(', ')', '（', '）'], '', $post_name);
	$post_name = str_replace([',', '-', '#', '!', '?', '(', ')', '（', '）'], ' ', $post_name);
	$post_name = trim($post_name);
	$post_name = preg_replace('/\s+/', '-', strtolower($post_name));

	$product_data = [
		'post_author' => 1,
	    'post_date' => $date,
	    'post_date_gmt' => $utc_date,
	    'post_content' => '',
	    'post_content_filtered' => '',
	    'post_title' => $post_title,
	    'post_excerpt' => $post_excerpt,
	    'post_status' => 'publish',
	    'post_type' => 'product_variation',
	    'comment_status' => 'open',
	    'ping_status' => 'closed',
	    'post_password' => '',
	    'post_name' => $post_name,
	    'to_ping' => '',
	    'pinged' => '',
	    'post_modified' => $date,
	    'post_modified_gmt' => $utc_date,
	    'post_parent' => $post_id,
	    'guid' => $guid,
	    'menu_order' => 0,
	    'post_mime_type' => '',
	];

	$variable_id = orca_apv_insert_post($product_data);

	
	orca_apv_insert_variable_lookup( $variable_id, $params['sale_price'], $params['regular_price']);
	
	if(isset($params['meta_data'])){
		foreach($params['meta_data'] as $meta){
			$postmeta_datas[$meta['key']] = $meta['value'];
		}
	}

	$res = orca_apv_batch_insert_postmeta($variable_id, $postmeta_datas);
	if($res !== true){
		$response = [
    		"code" => "DB ERROR",
		    "message" => $res,
		    "data" => [
		    	"status" => 1008,
		    ]
    	];
    	return new WP_REST_Response( $response, 400 );
	}

	return $variable_id;
}

function orca_apv_insert_variable_lookup( $post_id, $min_price, $max_price){
    global $wpdb;

    $datas = array(
        'product_id' => $post_id,
        'sku' => '', 
        'min_price' => $min_price,
        'max_price' => $max_price,
    );
    $result = $wpdb->insert( $wpdb->wc_product_meta_lookup, $datas );
}

function orca_apv_get_post($post_id){
	global $wpdb;  
  
	$post = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d", $post_id),   ARRAY_A);

	return $post;
}


function orca_apv_insert_post($data){
    global $wpdb;

    if ( ! $wpdb->insert( $wpdb->posts, $data ) ) {
        return false;
    }

    $id = (int) $wpdb->insert_id;

    return $id;
}

function orca_apv_batch_insert_postmeta($post_id, $datas){
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
	    return "新增产品变体属性错误: " . $wpdb->last_error;
	} 

	return true;
}