<?php

function enqueue_magnific_popup_styles() {  
    if (is_cart()) {  
        wp_enqueue_style( 'magnific-popup-style', get_template_directory_uri() . '/css/parts/lib-magnific-popup.min.css', array(), null, 'all' );  
    }  
}  
add_action( 'wp_enqueue_scripts', 'enqueue_magnific_popup_styles' );

function enqueue_magnific_popup_scripts() {  
    if (is_cart()) {  
        wp_enqueue_script( 'magnific-popup', get_template_directory_uri() . '/js/libs/magnific-popup.min.js', array('jquery'), null, true );  
    }  
}  
add_action( 'wp_enqueue_scripts', 'enqueue_magnific_popup_scripts' );

// Enqueue your JavaScript file
function apply_coupon_code_scripts() {
    // Enqueue your JavaScript file
    wp_enqueue_script( 'my_apply_coupon_code_scripts', get_stylesheet_directory_uri() . '/apply_coupon_code_scripts.js', array( 'jquery' ), '1.0', true );

    // Localize the script with new data
    $ajax_url = admin_url( 'admin-ajax.php' );
    $nonce = wp_create_nonce('apply_coupon_code_nonce');
    wp_localize_script( 'my_apply_coupon_code_scripts', 'myAjax', array( 'ajax_url' => $ajax_url, 'nonce' => $nonce ) );
}
add_action( 'wp_enqueue_scripts', 'apply_coupon_code_scripts' );

function orca_get_selected_coupons(){
    $cart = WC()->cart;
    $selected_coupons = $cart->get_applied_coupons();
    $coupons = [];
    foreach($selected_coupons as $coupon){
        $coupons[] = strtoupper($coupon);
    }
    return $coupons;
}

function orca_apply_coupon_code() {  
    //check_ajax_referer('apply_coupon_code_nonce', 'security'); // 验证安全性检查  
      
    // 获取优惠券代码  
    $coupon_code = isset($_POST['coupon_code']) ? wc_clean($_POST['coupon_code']) : '';
    $coupon_code = strtoupper($coupon_code);
    
    $coupons = orca_get_selected_coupons();
    // 应用优惠券逻辑  wc_clear_notices();
    if (!empty($coupon_code)) {
        $cart = WC()->cart;  
        if ($cart->has_discount($coupon_code)) {
            $code = 0;
            foreach($coupons as $key=>$coupon){
                if($coupon == $coupon_code){
                   $cart->remove_coupon($coupon_code);
                   unset($coupons[$key]);
                   break;
                }
            }
            wc_add_notice( 'Coupon canceled successfully.', 'success' );
        } else {
            if (!$cart->add_discount($coupon_code)) {
                $code = 1;  // 无效的优惠券代码
            } else {
                $coupons[] = $coupon_code;
                $code = 2;// 优惠券应用成功   
            } 
        }
        if($code == 1){
            $notices = wc_get_notices();
            $temp_msg = '';
            if(isset($notices['error'])){
                foreach($notices['error'] as $item){
                    $temp_msg .= $item['notice']; 
                }
            } elseif(isset($notices['success'])){
                foreach($notices['success'] as $item){
                    $temp_msg .= $item['notice']; 
                }
            }
            if(false !== strpos($temp_msg, 'currently applied on the cart')){
                wc_clear_notices();
                foreach($coupons as $key=>$coupon){
                    if($coupon != 'FIRST'){
                       $cart->remove_coupon($coupon);
                       unset($coupons[$key]);
                       break;
                    }
                }
                if (!$cart->add_discount($coupon_code)) {
                    $code = 1;  // 无效的优惠券代码        
                } else {
                    $coupons[] = $coupon_code;
                    $code = 2;// 优惠券应用成功 
                }
            } 
        }
        ob_start(); // 开始输出缓冲
        wc_print_notices(); // 输出通知消息
        $msg = ob_get_clean(); // 获取输出缓冲并清除
        if($code == 1){
            $msg = str_replace('</li>', 'Please check items to your cart.</li>', $msg);
        }
    } else {  
        $code = 3;
        $msg = 'No coupon code provided.';// 未提供优惠券代码  
    }  

    $return = [
        'code' => $code,
        'msg' => $msg,
        'coupon_code' => $coupon_code,
        'coupons' => array_values($coupons),
    ];
    echo json_encode($return);
    wp_die();
}

// 注册AJAX端点  
add_action('wp_ajax_orca_apply_coupon_code', 'orca_apply_coupon_code');  
add_action('wp_ajax_nopriv_orca_apply_coupon_code', 'orca_apply_coupon_code'); // 对于未登录用户

function orca_get_selected_coupons_callback() {
    $coupons = orca_get_selected_coupons();
    echo json_encode($coupons);
    wp_die();
} 
add_action('wp_ajax_orca_get_selected_coupons', 'orca_get_selected_coupons_callback');
add_action('wp_ajax_nopriv_orca_get_selected_coupons', 'orca_get_selected_coupons_callback');