<?php
function filter_category_query($query) {
    // 检查是否为主查询，并且是否为分类页面
    if ($query->is_main_query() && is_product_category()) {
        $pb_color = isset($_GET['pb_color']) ? sanitize_text_field($_GET['pb_color']) : '';

        // 构建 tax_query
        if (!empty($pb_color)) {
            $pb_color_list = explode(',', $pb_color);
            $tax_query = [
                [
                    'taxonomy' => 'pa_color',
                    'field'    => 'slug',
                    'terms'    => $pb_color_list,
                    'operator' => 'IN'
                ],
            ];

            $query->set('tax_query', $tax_query);
        }
    }
}
add_action('pre_get_posts', 'filter_category_query');


class Orca_Product_Filter_Colors_Widget extends WP_Widget {
    // 构造函数
    function __construct() {
        parent::__construct(
            'orca_product_filter_colors_widget', // 挂件 ID
            'Product Filter Color Widget', // 挂件名称
            array( 'description' => 'This is a  Product Filter Color widget.' ) // 挂件描述
        );
    }

    // 前端输出挂件内容
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        echo '<h5 class="widget-title">Colors</h5>';
        echo '<div class="wd-widget sidebar-widget">';

        $domain = defined('SITE_CND_DOMAIN') ? SITE_CND_DOMAIN : 'https://cdn.fredar.com';
        echo '<div class="sidebar-color-content">';

        $colors = $this->get_colors_from_elasticsearch();

        if(!empty($colors)){
            foreach($colors as $color){
                $color_html = '<div class="color-button" ><img src="%s/cdn/color/%s.webp" alt="%s" title="%s" data-color="%s"></div>';
                $color_ucfirst = ucfirst($color);
                echo sprintf($color_html, $domain, $color_ucfirst, $color_ucfirst, $color_ucfirst, $color);
            }
        }

        echo '</div>';
        echo '</div>';


        echo $args['after_widget'];
    }

    // 后台挂件表单处理
    public function form( $instance ) {

    }


    public function update( $new_instance, $old_instance ) {
        $instance = array();
        return $instance;
    }

    private function get_colors_from_elasticsearch() {
        global $wpdb, $wp_query;

        $category_slug = '';
        $cache_key = 'orca_product_filter_colors';
        $current_category = get_queried_object();
        if(!empty($current_category)){
            $category_slug = $current_category->slug;
            if(!empty($category_slug)){
                $cache_key = $cache_key . '_'.$category_slug;
            }
        }

        $cache_colors = wp_cache_get($cache_key, 'orca');
        if(!empty($cache_colors)){
            return $cache_colors;
        }

        $allow_colors = [
            'purple', 'grey', 'khaki', 'brown', 'black', 'orange', 'red', 'pink', 'white', 'blue', 'yellow', 'green', 'multi',
        ];

        $sql = "select t.slug as slug, t.term_id as term_id
        from wp_terms t 
        INNER join wp_term_taxonomy tt on tt.term_id = t.term_id 
        where tt.taxonomy = 'pa_color'";

        $results = $wpdb->get_results( $sql, ARRAY_A );

        if(empty($results)) {
            return $allow_colors;
        }

        $all_colors = [];
        foreach($results as $result){
            $temp = strtolower($result['slug']);
            if(in_array($temp, $allow_colors)){
                $all_colors[] = $temp;
            }
        }

        $query = array(
            'size' => 0,
            'aggs' => array(
                'pp_color' => array(
                    'terms' => array(
                        'field' => 'terms.pa_color.slug'
                    )
                )
            )
        );

        if(!empty($category_slug)){
            $query['query'] = [
                'bool' => [
                    'must' => [
                        'term' => ['terms.product_cat.slug' => strtolower($category_slug)]
                    ]
                ]
            ];
        }

        $colors = orca_elasticsearch_query($query);

        if(empty($colors) || !isset($colors['aggregations']['pp_color']['buckets'])){
            return $allow_colors;
        }

        $buckets = $colors['aggregations']['pp_color']['buckets'];


        /*$query_vars = $wp_query->query_vars;
        if(isset($query_vars['ep_aggregations']['terms']['pa_color']['buckets'])){
            $buckets = $query_vars['ep_aggregations']['terms']['pa_color']['buckets'];
        }*/

        $filter_colors = [];
        if(empty($buckets)) {
            return $allow_colors;
        }

        foreach($buckets as $bucket){
            $filter_colors[] = strtolower($bucket['key']);
        }
        
        $display_filter_colors = [];
        foreach($all_colors as $color){
            if(in_array($color, $filter_colors)){
                $display_filter_colors[] = $color;
            }
        }

        //sort
        $orderMap = array_flip($allow_colors);
        $orderValues = array_map(function($item) use ($orderMap) {
            return $orderMap[$item];
        }, $display_filter_colors);
        array_multisort($orderValues, SORT_ASC, $display_filter_colors);

        wp_cache_set($cache_key, $display_filter_colors, 'orca', 600);//10 min

        return $display_filter_colors;
    }
}

function register_orca_product_filter_colors_widget() {
    register_widget( 'Orca_Product_Filter_Colors_Widget' );
}
add_action( 'widgets_init', 'register_orca_product_filter_colors_widget' );


function enqueue_orca_product_filter_colors_widge_scripts() {
    if (is_active_widget(false, false, 'orca_product_filter_colors_widget', true)) {
        wp_enqueue_style('orca-product-filter-colors-style', get_stylesheet_directory_uri() . '/css/orca-product-filter-colors-widget-style.css');
        wp_enqueue_script('orca-product-filter-colors-script', get_stylesheet_directory_uri() . '/js/orca-product-filter-colors-widget-script.js', array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_orca_product_filter_colors_widge_scripts');
