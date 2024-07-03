<?php
/**
 * Plugin Name: wp-cli
 * Description: wp cli command.
 * Version: 1.0
 */
defined( 'ABSPATH' ) || exit;
define( 'WP_WPCLI_FILE',                  __FILE__ );
define( 'WP_WPCLI_PATH',                  realpath( plugin_dir_path( WP_WPCLI_FILE ) ) . '/' );
define( 'WP_WPCLI_INC_PATH',              realpath( WP_WPCLI_PATH . 'include/' ) . '/' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {

    class Custom_Command extends WP_CLI_Command {

    	//测试
        public function test( $args, $assoc_args ) {
	     
            // Your command logic here.
            WP_CLI::success( json_encode($assoc_args).'Your command executed successfully.' );
        }

        //导入评论 wp custom import_comment "{$file_path}"
        public function import_comment($args, $assoc_args ){
        	require_once WP_WPCLI_INC_PATH . 'import_comment.php';
        	wpcli_import_comment($args, $assoc_args);
        }

        //注册图片 custom add_media "{image_url}" --width={width} --height={height} --size={size}
		public function add_media($args, $assoc_args ){
			require_once WP_WPCLI_INC_PATH . 'add_media.php';
			wpcli_add_media($args, $assoc_args);
		}

		//清缓存 wp custom wp_rocket_flush
		public function wp_rocket_flush($args, $assoc_args){
			require_once WP_WPCLI_INC_PATH . 'wp_rocket_flush.php';
			wpcli_wp_rocket_flush($args, $assoc_args);
		}

		//图片修复 wp custom fix_pic
		public function fix_pic($args, $assoc_args ){
			require_once WP_WPCLI_INC_PATH . 'fix_pic.php';
			wpcli_fix_pic($args, $assoc_args);
		}

		//修复产品分类 wp custom fix_product_category
		public function fix_product_category($args, $assoc_args ){
			require_once WP_WPCLI_INC_PATH . 'fix_product_category.php';
			wpcli_fix_product_category($args, $assoc_args);
		}

		//预加载 wp custom pre_load
		public function pre_load($args, $assoc_args ){
			require_once WP_WPCLI_INC_PATH . 'pre_load.php';
			wpcli_preload($args, $assoc_args);
		}

		//多线程版预加载-1 wp custom do_quick_preload --thread=5
		public function quick_preload($args, $assoc_args ){
			require_once WP_WPCLI_INC_PATH . 'quick_preload.php';
			wpcli_preload($args, $assoc_args);
		}

		//多线程版预加载-2 wp custom do_quick_preload
		public function do_quick_preload($args, $assoc_args ){
			require_once WP_WPCLI_INC_PATH . 'do_quick_preload.php';
			wpcli_preload($args, $assoc_args);
		}

		//更新产品索引 wp custom update_product_esindex --post_id=123432 --file_path=/path/file.json --reindex=1
		public function update_product_esindex($args, $assoc_args ){
			require_once WP_WPCLI_INC_PATH . 'update_product_esindex.php';
			wpcli_update_product_esindex($args, $assoc_args);
		}

		//获取orcaoms spider ip: wp custom get_orcaoms_spider_iptables
		public function get_orcaoms_spider_iptables($args, $assoc_args ){
			require_once WP_WPCLI_INC_PATH . 'get_orcaoms_spider_iptables.php';
			wpcli_get_orcaoms_spider_iptables($args, $assoc_args);
		}

		//新增产品pa_color term relationship and attribute:  wp custom add_product_attribute_pacolor "file_path"
		public function add_product_attribute_pacolor($args, $assoc_args ){
			require_once WP_WPCLI_INC_PATH . 'add_product_attribute_pacolor.php';
			wpcli_add_product_attribute_pacolor($args, $assoc_args);
		}
    }

    WP_CLI::add_command( 'custom', 'Custom_Command' );
}

