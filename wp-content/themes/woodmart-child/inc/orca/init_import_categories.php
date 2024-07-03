<?php

function delete_all_menus() {
    global $wpdb;

    $menus = wp_get_nav_menus();
    foreach ($menus as $menu) {
        wp_delete_nav_menu($menu->term_id);
    }

    $sql = "delete FROM wp_posts WHERE post_type = 'cms_block'";
    $wpdb->query($sql);
}

function custom_get_category_url_by_name($name, $check = false){
    $category_url = '';
    $category = get_term_by('name', $name, 'product_cat');
    if(empty($category)){
        if ($check) {
            echo '<div class="notice notice-error"><p>数据较验失败，分类不存在：'.$name.' </p></div>';
            exit;  
        }
        return '';
    }

    $category_url = get_category_link($category->term_id);
    if($check && empty($category_url)){
        echo '<div class="notice notice-error"><p>数据较验失败，分类链接未设置：'.$name.' </p></div>';
        exit;   
    }

    return $category_url;
}

function create_custom_menu_with_custom_link($menus) {
    /*echo '<pre>';
    print_r($menus);
    echo '</pre>';
    exit;
    */

    //check menus
    foreach($menus as $key=>$menu){
        $url = custom_get_category_url_by_name($menu['title']);
        $menus[$key]['url'] = $url;

        if(empty($menu['two_level'])) {
            echo '<div class="notice notice-error"><p>数据较验失败，无二级分类：'.$menu['title'].' </p></div>';
            exit;  
        }
        foreach($menu['two_level'] as $kk=>$lv2_menu){
            $url_ok = false;
            $url = custom_get_category_url_by_name($lv2_menu['title']);
            if(!empty($url)){
                $url_ok = true;
                $menus[$key]['two_level'][$kk]['url'] = $url;
            } else {
                $menus[$key]['two_level'][$kk]['url'] = '';
            }

            if(empty($lv2_menu['three_level'])) {
                echo '<div class="notice notice-error"><p>数据较验失败，无三级分类：'.$menu['title'].'-'.$lv2_menu['title'].' </p></div>';
                exit;  
            }
            $order3 = 0;
            foreach($lv2_menu['three_level'] as $kkk=>$lv3_menu){
                $url = custom_get_category_url_by_name($lv3_menu['title'], true);
                $menus[$key]['two_level'][$kk]['three_level'][$kkk]['url'] = $url;
                
                if(!$url_ok){
                    $url_ok = true;
                    $menus[$key]['two_level'][$kk]['url'] = $url;
                }
            }
        } 
    }

    #测试才启用
    #delete_all_menus();

    create_custom_menu_with_custom_link_web($menus);

    create_custom_menu_with_custom_link_mobile($menus);
}

function create_custom_menu_with_custom_link_mobile($menus) {
    $menu_name = 'Mobile navigation';
    $menu_id = wp_create_nav_menu($menu_name);
    if ($menu_id instanceof WP_Error) {
        echo '<div class="notice notice-success"><p>创建菜单失败: '.$menu_id->get_error_message().'</p></div>';
        exit;
    }

    $locations = get_theme_mod('nav_menu_locations');
    $locations['mobile-menu'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);

    $order1 = 1;
    foreach($menus as $menu){
        $item = [
            'title' => $menu['title'],
            'url' => $menu['url'],
            'menu_item_parent' => 0,
            'type' => 'custom',
            'object' => 'custom',
            'object_id' => '',
            'menu_order' => $order1,
        ];
        $menu_pid = add_custom_mobile_menu($menu_id, $item);

        if(empty($menu['two_level'])) continue;

        $order2 = 0;
        foreach($menu['two_level'] as $lv2_menu){
            $item = [
                'title' => $lv2_menu['title'],
                'url' => $lv2_menu['url'],
                'menu_item_parent' => $menu_pid,
                'type' => 'custom',
                'object' => 'custom',
                'object_id' => '',
                'menu_order' => $order2,
            ];
            $menu_pid1 = add_custom_mobile_menu($menu_id, $item);

            if(empty($lv2_menu['three_level'])) continue;

            $order3 = 0;
            foreach($lv2_menu['three_level'] as $lv3_menu){
                $item = [
                    'title' => $lv3_menu['title'],
                    'url' => $lv3_menu['url'],
                    'menu_item_parent' => $menu_pid1,
                    'type' => 'custom',
                    'object' => 'custom',
                    'object_id' => '',
                    'menu_order' => $order3,
                ];
                add_custom_mobile_menu($menu_id, $item);
                $order3++;
            }
            $order2++;
        }

        $order1++;
    }
}

function add_custom_mobile_menu($menu_id, $item){
    $update_result = wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title' => $item['title'],
        'menu-item-url' => $item['url'],
        'menu-item-status' => 'publish',
        'menu-item-parent-id' => $item['menu_item_parent'],
        'menu-item-type' => $item['type'],
        'menu-item-object' => $item['object'],
        'menu-item-position' => $item['menu_order'],
    ]);

    if ( ! is_wp_error( $update_result ) ) {
        $updated_menu_item_id = $update_result;
        $updated_menu_item_post_id = get_post_meta( $updated_menu_item_id, '_menu_item_object_id', true );
    } else {
        echo '<div class="notice notice-success"><p>创建菜单失败1: '.$update_result->get_error_message().'</p></div>';
        exit();
    }
    return $updated_menu_item_post_id;
}

function create_custom_menu_with_custom_link_web($menus) {
    $menu_name = 'Main navigation';
    $menu_id = wp_create_nav_menu($menu_name);
    if ($menu_id instanceof WP_Error) {
        echo '<div class="notice notice-success"><p>创建菜单失败2: '.$menu_id->get_error_message().'</p></div>';
        exit();
    }

    $locations = get_theme_mod('nav_menu_locations');
    $locations['main-menu'] = $menu_id;//mobile-menu
    set_theme_mod('nav_menu_locations', $locations);

    $order = 1;
    foreach($menus as $menu){
        $post_id = add_block_menu($menu);

        if(empty($post_id)) {
            continue;
        }

        add_block_menu_elementor($post_id, $menu);

        $item = [
            'title' => $menu['title'],
            'url' => $menu['url'],
            'menu_item_parent' => 0,
            'type' => 'custom',
            'object' => 'custom',
            'object_id' => $post_id,
            'menu_order' => $order,
        ];
        add_custom_menu($menu_id, $item);

        $order++;
    }
}

function add_custom_menu($menu_id, $item){
    $update_result = wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title' => $item['title'],
        'menu-item-url' => $item['url'],
        'menu-item-status' => 'publish',
        'menu-item-parent-id' => $item['menu_item_parent'],
        'menu-item-type' => $item['type'],
        'menu-item-object' => $item['object'],
        'menu-item-position' => $item['menu_order'],
    ]);

    if ( ! is_wp_error( $update_result ) ) {
        $updated_menu_item_id = $update_result;
        $updated_menu_item_post_id = get_post_meta( $updated_menu_item_id, '_menu_item_object_id', true );
        update_post_meta( $updated_menu_item_post_id, '_menu_item_block', $item['object_id'] );
        update_post_meta( $updated_menu_item_post_id, '_menu_item_design', 'full-width' );
    } else {
        echo '<div class="notice notice-success"><p>创建菜单失败2: '.$update_result->get_error_message().'</p></div>';
        exit();
    }
}

function id_general(){
    return substr(md5(uniqid()), 3, 8);
}

function add_block_menu_elementor($post_id, $menu){
    global $wpdb;

    $data = [
        0 => [
            'id' => id_general(),
            'elType' => 'container',
            'settings' => [
                'flex_direction' => 'row',
                'flex_justify_content' => 'space-around',
                'scroll_y' => -80,
            ],
            'elements' => [],
            'isInner' => false,
        ]
    ];
    $elements = [];

    if(empty($menu['two_level'])) return false;

    foreach($menu['two_level'] as $item){
        $two_temp = [
            'id' => id_general(),
            'elType' => 'widget',
            'settings' => [],
            'elements' => [], 
        ];
        

        if(empty($item['three_level'])) continue;

        $two_elements = [];
        foreach($item['three_level'] as $three_item){
            $two_elements[] = [
                'title' => $three_item['title'],
                'link' => [
                    'url' => $three_item['url'],
                    'is_external' => false,
                    'nofollow' => false,
                ],
                '_id' => id_general(),
            ];
        }
        $settings = [
           'title' => $item['title'],
           'menu_items_repeater' => $two_elements,
           'scroll_y' => -80,
           'link' => [
                'url' => $item['url'],
                'is_external' => false,
                'nofollow' => false,
            ],
        ];
        $widgetType = 'wd_extra_menu_list';

        $two_temp['settings'] = $settings;
        $two_temp['widgetType'] = $widgetType;
        $elements[] = $two_temp;
    }

    $data[0]['elements'] = $elements;
   
    update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
    update_post_meta( $post_id, '_wp_page_template', 'default' );
    update_post_meta( $post_id, '_elementor_data', json_encode($data) );
}

function add_block_menu($menu){
    global $wpdb;

    $site_url = get_site_url();
    $content = '';
    $content .= '<link rel="stylesheet" id="wd-mod-nav-menu-label-css" href="'.$site_url.'/wp-content/themes/woodmart/css/parts/mod-nav-menu-label.min.css?ver=7.3.1"
type="text/css" media="all" />';

    if(empty($menu['two_level'])) return '';

    foreach($menu['two_level'] as $item){
        $content .= '<ul><li>';
        $content .= sprintf('<a href="%s">%s</a>', $item['url'], $item['title']);

        if(!empty($item['three_level'])){
            $content .= '<ul>';
            foreach($item['three_level'] as $three_item){
                $content .= sprintf('<li> <a href="%s"> %s </a> </li>', $three_item['url'], $three_item['title']);
            }
            $content .= '</ul>';
        }

        $content .= '</li></ul>';
    }


    $table = $wpdb->prefix . 'posts';

    try{
        $data = array(
            'post_author' => 1, 
            'post_date' => current_time('mysql'),
            'post_date_gmt' => gmdate('Y-m-d H:i:s'),
            'post_content' => $content,
            'post_title' => $menu['title'],
            'post_excerpt' => '',
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_name' => str_replace('\s+', '-', str_replace('&', '', strtolower(trim($menu['title'])))),
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => gmdate('Y-m-d H:i:s'),
            'post_type' => 'cms_block',
            'post_parent' => 0,
            'menu_order' => 0,
            'post_mime_type' => '',
            'comment_count' => 0
        );


        $format = array(
            '%d', // post_author
            '%s', // post_date
            '%s', // post_date_gmt
            '%s', // post_content
            '%s', // post_title
            '%s', // post_excerpt
            '%s', // post_status
            '%s', // comment_status
            '%s', // ping_status
            '%s', // post_name
            '%s', // post_modified
            '%s', // post_modified_gmt
            '%s', // post_type
            '%d', // post_parent
            '%d', // menu_order
            '%s', // post_mime_type
            '%d'  // comment_count
        );

        $wpdb->insert($table, $data, $format);

        $post_id = $wpdb->insert_id;
    } catch(\Exception $e){
        echo 'DB ERROR:'.$e->getMessage();
        exit;
    }

    return $post_id;

}

//导出
function export_categories_to_csv() {
    // 文件名
    $filename = 'categories.csv';

    // 设置文件头
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=' . $filename);

    // 打开输出流
    $output = fopen('php://output', 'w');

    // CSV 文件头
    $header = array('Category ID', 'Category Name', 'Category Description', 'Category Slug');
    fputcsv($output, $header);

    // 获取所有分类
    $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);

    // 输出每个分类的信息
    foreach ($categories as $category) {
        $row = array(
            $category->term_id,
            $category->name,
            $category->description,
            $category->slug,
        );
        fputcsv($output, $row);
    }

    // 关闭输出流
    fclose($output);

    // 终止脚本以确保文件下载
    exit;
}

// 添加一个 WordPress 管理菜单
add_action('admin_menu', function () {
    add_submenu_page('tools.php', 'Export Categories', 'Export Categories', 'manage_options', 'export-categories', 'export_categories_to_csv');
});


// 添加分类导入页面
function add_import_categories_page() {
    add_submenu_page(
        'tools.php',              // 父菜单
        'Import Categories',      // 页面标题
        'Import Categories',      // 菜单标题
        'manage_options',         // 权限
        'import-categories',      // 路由
        'render_import_categories_page'  // 回调函数
    );
}
add_action('admin_menu', 'add_import_categories_page');

// 渲染分类导入页面
function render_import_categories_page() {
    ?>
    <div class="wrap">
        <h1>Import Categories</h1>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="categories_csv" accept=".csv"><br>
            <input type="submit" name="import_categories" value="Import Categories" class="button button-primary">
        </form>
    </div>
    <?php
}

// 处理分类导入
function handle_import_categories() {
    if (isset($_POST['import_categories'])) {
        if (isset($_FILES['categories_csv'])) {
            $csv_file = $_FILES['categories_csv'];

            // 检查文件类型
            if ($csv_file['type'] === 'text/csv' || $csv_file['type'] === 'application/vnd.ms-excel') {
                // 保存上传的CSV文件到临时目录
                $upload_dir = wp_upload_dir();
                $csv_file_path = $upload_dir['basedir'] . '/' . $csv_file['name'];
                move_uploaded_file($csv_file['tmp_name'], $csv_file_path);

                // 导入分类
                import_categories_from_csv($csv_file_path);

                // 删除临时文件
                unlink($csv_file_path);
                
                // 显示成功消息
                echo '<div class="notice notice-success"><p>Categories imported successfully!</p></div>';
            } else {
                // 显示错误消息
                echo '<div class="notice notice-error"><p>Invalid file format. Please upload a CSV file.</p></div>';
            }
        }
    }
}
add_action('admin_init', 'handle_import_categories');


function import_categories_from_csv($csv_file_path){
    if (!file_exists($csv_file_path)) {
        return;
    }

    // 打开 CSV 文件
    $csv_file = fopen($csv_file_path, 'r');

    // 跳过文件头
    fgetcsv($csv_file);

    // 导入分类
    $menus = [];
    while (($row = fgetcsv($csv_file)) !== false) {
        $top_name = trim($row[0]);
        $pname = trim($row[1]);
        $name = trim($row[2]);
        $level = trim($row[3]);

        if(empty($name)) continue;

        if($level == 1){
            //top
            $temp = [
                'title'=> $name,
            ];
            $menus[$name] = $temp;
        } elseif($level == 2) {
            //two level
            if(isset($menus[$pname])){
                $temp = [
                    'title'=> $name,
                ];
                $menus[$pname]['two_level'][$name] = $temp;
            }
        } else {
            if(!empty($top_name)){
                $temp = [
                    'title'=> $name,
                ];
                $menus[$top_name]['two_level'][$pname]['three_level'][] = $temp;
            }
        }
    }

    // 关闭文件句柄
    fclose($csv_file);

    /*$two_level = [
        [
            'title' => 'Tops',
            'three_level' => [
                [
                    'title' => 'Blouses',
                ],
                [
                    'title' => 'T-shirts',
                ],
                [
                    'title' => 'Sweaters',
                ],
            ],
        ],
        [
            'title' => 'Bottoms',
            'three_level' => [
                [
                    'title' => 'Blouses',
                ],
                [
                    'title' => 'T-shirts',
                ],
                [
                    'title' => 'Sweaters',
                ],
            ],
        ],
    ];

    $menus = [
        [
            'title'=>'Clothing',
            'two_level' => $two_level,
        ], 
        [
            'title'=>'Tops',
            'two_level' => $two_level,
        ], 
        [
            'title'=>'Dresses',
            'two_level' => $two_level,
        ], 
    ];*/

    create_custom_menu_with_custom_link($menus);
}