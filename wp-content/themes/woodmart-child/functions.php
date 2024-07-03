<?php
/**
 * Enqueue script and styles for child theme
 */
function woodmart_child_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), woodmart_get_theme_info( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010 );

######################### 公共方法 ###############################
require_once get_stylesheet_directory() . '/inc/orca/common.php';

############### Email switch #########################
if(defined('ORCA_EMAIL_DEBUG')) require_once get_stylesheet_directory() . '/inc/orca/email_controller.php';

######################### 初始化分类导入功能 ###########
//require_once get_stylesheet_directory() . '/inc/orca/init_import_categories.php';

######################### 44. Add reCapche v3 ###########
//require_once get_stylesheet_directory() . '/inc/orca/add_recapche_v3.php';


######################### 46. 优惠券联动 ###########
#require_once get_stylesheet_directory() . '/inc/orca/add_product_coupon.php';


###############gg########## 48. 隐藏帐号中心TAB ###########
require_once get_stylesheet_directory() . '/inc/orca/hide_my_account.php';

############### 列表禁用PJAX #########################
require_once get_stylesheet_directory() . '/inc/orca/disabled_product_category_pjax.php';


############### 优化分类页面左分类导航长度显示问题 #########################
#require_once get_stylesheet_directory() . '/inc/orca/promote_product_page_bug.php';

############### 我的订单物流跟踪按钮 #########################
require_once get_stylesheet_directory() . '/inc/orca/orders_tracking_button.php';


############### 结算记录订单来源标识 #########################
//require_once get_stylesheet_directory() . '/inc/orca/product_link_to_order_tracking.php';

############### S3图片地址修正 #########################
require_once get_stylesheet_directory() . '/inc/orca/orca_s3_attachment_url.php';


############### elasticpress 扩展 #########################
#require_once get_stylesheet_directory() . '/inc/orca/elasticpress_index_tool.php';
require_once get_stylesheet_directory() . '/inc/orca/elasticpress_category_page.php';

############### 产品上传 #########################
require_once get_stylesheet_directory() . '/inc/orca/add_product_single.php';
require_once get_stylesheet_directory() . '/inc/orca/add_product_variable.php';

############### 给指定IP发送图形验证码 #########################
#require_once get_stylesheet_directory() . '/inc/orca/send_recapche.php';

############### timer cutdown #########################
require_once get_stylesheet_directory() . '/inc/orca/checkout_before_customer_details_cutdown.php';
