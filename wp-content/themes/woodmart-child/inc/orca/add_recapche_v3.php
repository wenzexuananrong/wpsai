<?php
function get_recaptcha_field_v3() {
    echo '<input type="hidden" id="g-recaptcha-xtoken" name="woo-register-recaptcha"/>';

    wp_print_script_tag( array( "src" => "https://www.google.com/recaptcha/api.js?render=6LeLFqwpAAAAAKmbVIrxLzsZA3b1_UBYL_XWchsk" ) );

    wp_print_inline_script_tag( "
        grecaptcha.ready(function() {
            grecaptcha.execute(
                '6LeLFqwpAAAAAKmbVIrxLzsZA3b1_UBYL_XWchsk',
                { action: 'woo_recaptcha_v3' }
            ).then(function(token) {
                document.getElementById('g-recaptcha-xtoken').value=token;
            });
        });
    ", [ 'type' => 'text/javascript' ] );
}

add_action( 'woocommerce_register_form', 'get_recaptcha_field_v3' );

function woodmart_child_recaptcha_hide_style() {  
    ?>  
    <style type="text/css">  
        .grecaptcha-badge { visibility: hidden; }
    </style>  
    <?php  
}  
add_action('wp_head', 'woodmart_child_recaptcha_hide_style');


// 添加ReCAPTCHA验证的函数
function wc_recaptcha_v3_verify_token($token) {
    $siteKey = '6LeLFqwpAAAAAKmbVIrxLzsZA3b1_UBYL_XWchsk';
    $secret = '6LeLFqwpAAAAAB3agItzuFin76yErZCpI_epZi2p';
 
    // 使用cURL发送请求到ReCAPTCHA API
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$token}");
    $data = json_decode($response);
 
    // 验证结果
    if ($data->success) {
        return true;
    } else {
        return false;
    }
}
 
// 在WooCommerce的注册和登录表单中添加ReCAPTCHA验证
function wc_recaptcha_v3_on_login_register() {
    // 假设ReCAPTCHA令牌已经通过某种方式传递给服务器，例如：通过一个名为'g-recaptcha-response'的表单字段
    $token = isset($_POST['woo-register-recaptcha']) ? $_POST['woo-register-recaptcha'] : '';

    if (!empty($token)) {
        if (wc_recaptcha_v3_verify_token($token)) {
            // ReCAPTCHA验证成功
            // 继续执行登录或注册的操作
            //wp_die('访问正常');
        } else {
            // ReCAPTCHA验证失败
            // 可以抛出错误或者做其他处理
            $message .= 'click <a href="javascript:void(0)" onclick="history.back()">BACK</a> continue';
            wp_die( $message, 'error', array( 'response' => 400 ) );
        }
    } else {
        wp_die('ReCAPTCHA token error');
        // ReCAPTCHA令牌不存在
        // 可以抛出错误或者做其他处理
    }
}
add_action('woocommerce_register_post', 'wc_recaptcha_v3_on_login_register');