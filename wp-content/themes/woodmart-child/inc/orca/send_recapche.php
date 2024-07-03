<?php
function orca_add_recaptcha_script() {
    echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
}
add_action('wp_head', 'orca_add_recaptcha_script');

function orca_get_visitor_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function orca_is_ip_in_list($ip) {
    global $wpdb;
    $spider_ips = [];
    #$spider_ips = wp_cache_get('orca_oms_spiders_log_iptable', 'orca');
    $sql = "SELECT * FROM {$wpdb->options} WHERE option_name = '_orca_spider_iptables'";
    $options = $wpdb->get_row($sql, ARRAY_A);
    if (!empty($options)) {
        $spider_ips = json_decode($options['option_value'], true);
    }
    #$spider_ips[] = '198.181.41.149';
    if (empty($spider_ips)) {
        return false;
    }
    return in_array($ip, $spider_ips);
}

function orca_verify_recaptcha($response) {
    $secret = '6Ld__PYpAAAAAMut5uoWlc669z3lk8P96lo5LHSj';
    $remoteip = $_SERVER['REMOTE_ADDR'];
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $secret,
        'response' => $response,
        'remoteip' => $remoteip,
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $result = json_decode($result, true);

    return $result['success'];
}

function orca_send_recaptcha_log($ip, $hander = ''){
    $log = date('Y-m-d H:i:s') . " {$ip} {$hander} \n";
    file_put_contents('send_recapche.log', $log, FILE_APPEND);
}

function orca_handle_recaptcha_verification() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ip = orca_get_visitor_ip();
        $recaptcha_response = $_POST['g-recaptcha-response'];
        if (orca_verify_recaptcha($recaptcha_response)) {
            session_start();
            $_SESSION['recaptcha_verified'] = true;
            $redirect_url = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : home_url();

            orca_send_recaptcha_log($ip, 'success verify');
            echo json_encode(['success' => true, 'redirect_url' => $redirect_url]);
        } else {
            orca_send_recaptcha_log($ip, 'fail verify');
            echo json_encode(['success' => false]);
        }
        exit();
    }
}
add_action('admin_post_nopriv_verify_recaptcha', 'orca_handle_recaptcha_verification');
add_action('admin_post_verify_recaptcha', 'orca_handle_recaptcha_verification');


function orca_check_recaptcha_verification() {
    if (is_home() || is_front_page() || is_category() || is_archive()
        || function_exists('is_product') && is_product()) {

        $ip = orca_get_visitor_ip();

        $visitor_ip = orca_is_ip_in_list($ip);
        if (!$visitor_ip) return false;

        session_start();
        #$_SESSION['recaptcha_verified'] = false;
        if (!isset($_SESSION['recaptcha_verified']) || $_SESSION['recaptcha_verified'] !== true) {
            $current_page_id = get_queried_object_id();

            $recaptcha_page = get_page_by_path('recaptcha-verification');
            if ($recaptcha_page) {
                $recaptcha_page_id = $recaptcha_page->ID;
            }

            if (!isset($recaptcha_page_id) || $current_page_id != $recaptcha_page_id) {
                orca_send_recaptcha_log($ip, 'send recaptcha');
                wp_redirect(site_url('/recaptcha-verification'));
                exit();
            }
        }
    }
}
add_action('template_redirect', 'orca_check_recaptcha_verification');

//添加禁用IP
add_action( 'rest_api_init', 'orca_wc_api_add_spider_iptables' );

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

function orca_wc_api_add_spider_iptables() {
    register_rest_route( 'orca-wc/v3', '/spider_iptables', array(
        'methods' => 'POST',
        'callback' => 'orca_wc_add_spider_iptables',
    ) );
}

function orca_wc_add_spider_iptables( $data ){
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
    if(!isset($params['ips'])){
        $response = [
            "code" => "PARAMS ERROR",
            "message" => "IPS 参数为空",
            "data" => [
                "status" => 1002,
            ]
        ];
        return new WP_REST_Response( $response, 400 );
    }

    $data = [
        'option_name' => '_orca_spider_iptables',
        'option_value' => json_encode($params['ips']),
    ];
    if ( ! $wpdb->insert( $wpdb->options, $data ) ) {
        $response = [
            "code" => "DATABASE ERROR",
            "message" => "数据库异常，存储失败",
            "data" => [
                "status" => 1003,
            ]
        ];
        return new WP_REST_Response( $response, 500 );
    }

    $response = [
        'status'=> 200,
    ];
    return new WP_REST_Response( $response, 200 );

}
