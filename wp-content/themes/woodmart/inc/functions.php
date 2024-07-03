<?php use Elementor\Plugin;

if (!function_exists('woodmart_get_whb_headers_array')) {
    function woodmart_get_whb_headers_array($get_from_options = false, $new = false)
    {
        if ($get_from_options) {
            $list = get_option('whb_saved_headers');
        } else {
            $headers_list = whb_get_builder()->list;
            $list = $headers_list->get_all();
        }

        $headers = array();

        if ($new) {
            $headers['none'] = array(
                'name' => 'none',
                'value' => 'none',
            );
        } else {
            $headers['none'] = 'none';
        }

        if (!empty($list) && is_array($list)) {
            foreach ($list as $key => $header) {
                if ($new) {
                    $headers[$key] = array(
                        'name' => $header['name'],
                        'value' => $key,
                    );
                } else {
                    $headers[$key] = $header['name'];
                }
            }
        }

        return $headers;
    }
}

if (!function_exists('woodmart_pjax_with_pagination_fix')) {
    /**
     * Fix for pagination with PJAX.
     *
     * @param string $link Link.
     *
     * @return false|string
     */
    function woodmart_pjax_with_pagination_fix($link)
    {
        return remove_query_arg('_pjax', $link);
    }

    add_filter('paginate_links', 'woodmart_pjax_with_pagination_fix');
}

if (!defined('WOODMART_THEME_DIR')) exit('No direct script access allowed');

if (!function_exists('woodmart_is_data_encode')) {
    function woodmart_is_css_encode($data)
    {
        return strlen($data) > 50;
    }
}

if (!function_exists('woodmart_set_default_header')) {
    /**
     * Setup default header from theme settings
     *
     * @since 1.0.0
     */
    function woodmart_set_default_header()
    {
        if (!isset($_GET['settings-updated']) || isset($_GET['preset'])) { // phpcs:ignore
            return;
        }

        $theme_settings_header_id = woodmart_get_opt('default_header');

        if ($theme_settings_header_id) {
            update_option('whb_main_header', $theme_settings_header_id);
        }
    }

    add_filter('init', 'woodmart_set_default_header', 1000);
}

if (!function_exists('woodmart_enqueue_gallery_script')) {
    function woodmart_enqueue_gallery_script($html5)
    {
        if (woodmart_get_opt('single_post_justified_gallery')) {
            woodmart_enqueue_js_library('magnific');
            woodmart_enqueue_js_library('justified');
            woodmart_enqueue_inline_style('justified');
            woodmart_enqueue_js_script('mfp-popup');
            woodmart_enqueue_inline_style('mfp-popup');
        }

        return $html5;
    }

    add_filter('use_default_gallery_style', 'woodmart_enqueue_gallery_script');
}

if (!function_exists('woodmart_get_blog_shortcode_ajax')) {
    function woodmart_get_blog_shortcode_ajax()
    {
        if (!empty($_POST['atts'])) {
            $atts = woodmart_clean($_POST['atts']);
            $paged = (empty($_POST['paged'])) ? 2 : sanitize_text_field((int)$_POST['paged']) + 1;
            $atts['ajax_page'] = $paged;

            if (!empty($atts['offset'])) {
                $atts['offset'] = (int)$atts['offset'] + (int)$paged * (int)$atts['items_per_page'];
            }

            if (isset($atts['elementor']) && $atts['elementor']) {
                $data = woodmart_elementor_blog_template($atts);
            } else {
                $data = woodmart_shortcode_blog($atts);
            }

            wp_send_json($data);

            die();
        }
    }

    add_action('wp_ajax_woodmart_get_blog_shortcode', 'woodmart_get_blog_shortcode_ajax');
    add_action('wp_ajax_nopriv_woodmart_get_blog_shortcode', 'woodmart_get_blog_shortcode_ajax');
}

if (!function_exists('woodmart_get_portfolio_shortcode_ajax')) {
    function woodmart_get_portfolio_shortcode_ajax()
    {
        if (!empty($_POST['atts'])) {
            $atts = woodmart_clean($_POST['atts']);
            $paged = (empty($_POST['paged'])) ? 2 : sanitize_text_field((int)$_POST['paged']) + 1;
            $atts['ajax_page'] = $paged;

            if (isset($atts['elementor']) && $atts['elementor']) {
                $data = woodmart_elementor_portfolio_template($atts);
            } else {
                $data = woodmart_shortcode_portfolio($atts);
            }

            wp_send_json($data);

            die();
        }
    }

    add_action('wp_ajax_woodmart_get_portfolio_shortcode', 'woodmart_get_portfolio_shortcode_ajax');
    add_action('wp_ajax_nopriv_woodmart_get_portfolio_shortcode', 'woodmart_get_portfolio_shortcode_ajax');
}

if (!function_exists('woodmart_get_color_value')) {
    function woodmart_get_color_value($key, $default)
    {
        $color = woodmart_get_opt($key);

        if (isset($color['idle']) && $color['idle']) {
            return $color['idle'];
        } else {
            return $default;
        }
    }
}

if (!function_exists('woodmart_get_css_animation')) {
    function woodmart_get_css_animation($css_animation)
    {
        $output = '';
        if ($css_animation && $css_animation != 'none') {
            wp_enqueue_style('animate-css');
            wp_enqueue_style('vc_animate-css');

            woodmart_enqueue_js_library('waypoints');
            woodmart_enqueue_js_script('animations-offset');
            $output = ' wd-off-anim wpb_animate_when_almost_visible wpb_' . $css_animation . ' ' . $css_animation;

            $output .= ' wd-anim-name_' . $css_animation;
        }
        return $output;
    }
}

if (!function_exists('woodmart_get_user_panel_params')) {
    function woodmart_get_user_panel_params()
    {
        return apply_filters('woodmart_get_user_panel_params', array(
            array(
                'type' => 'textfield',
                'heading' => esc_html__('Title', 'woodmart'),
                'param_name' => 'title',
            )
        ));
    }
}

if (!function_exists('woodmart_vc_get_link_attr')) {
    function woodmart_vc_get_link_attr($link)
    {
        $link = ('||' === $link) ? '' : $link;
        $link = woodmart_vc_build_link($link);
        return $link;
    }
}

if (!function_exists('woodmart_get_link_attributes')) {
    function woodmart_get_link_attributes($link, $popup = false)
    {
        //parse link
        $link = woodmart_vc_get_link_attr($link);
        $use_link = false;
        if (isset($link['url']) && strlen($link['url']) > 0) {
            $use_link = true;
            $a_href = apply_filters('woodmart_extra_menu_url', $link['url']);
            if ($popup) $a_href = $link['url'];
            $a_title = $link['title'];
            $a_target = $link['target'];
            $a_rel = $link['rel'];
        }

        $attributes = array();

        if ($use_link) {
            $attributes[] = 'href="' . trim($a_href) . '"';
            $attributes[] = 'title="' . esc_attr(trim($a_title)) . '"';
            if (!empty($a_target)) {
                $attributes[] = 'target="' . esc_attr(trim($a_target)) . '"';
            }
            if (!empty($a_rel)) {
                $attributes[] = 'rel="' . esc_attr(trim($a_rel)) . '"';
            }
        }

        $attributes = implode(' ', $attributes);

        return $attributes;
    }
}

if (!function_exists('woodmart_get_taxonomies_by_ids_autocomplete')) {
    /**
     * Autocomplete by taxonomies ids.
     *
     * @param array $ids Posts ids.
     *
     * @return array
     * @since 1.0.0
     *
     */
    function woodmart_get_taxonomies_by_ids_autocomplete($ids)
    {
        $output = array();

        if (!$ids) {
            return $output;
        }

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $term = get_term($id);

            if ($term && !is_wp_error($term)) {
                $output[$term->term_id] = array(
                    'name' => $term->name,
                    'value' => $term->term_id,
                );
            }
        }

        return $output;
    }
}

if (!function_exists('woodmart_get_taxonomies_by_query_autocomplete')) {
    /**
     * Autocomplete by taxonomies.
     *
     * @since 1.0.0
     */
    function woodmart_get_taxonomies_by_query_autocomplete()
    {
        $output = array();

        $args = array(
            'number' => 5,
            'taxonomy' => $_POST['value'], // phpcs:ignore
            'search' => $_POST['params']['term'], // phpcs:ignore
            'hide_empty' => false,
        );

        $terms = get_terms($args);

        if (count($terms) > 0) { // phpcs:ignore
            foreach ($terms as $term) {
                $output[] = array(
                    'id' => $term->term_id,
                    'text' => $term->name,
                );
            }
        }

        echo wp_json_encode($output);
        die();
    }

    add_action('wp_ajax_woodmart_get_taxonomies_by_query_autocomplete', 'woodmart_get_taxonomies_by_query_autocomplete');
    add_action('wp_ajax_nopriv_woodmart_get_taxonomies_by_query_autocomplete', 'woodmart_get_taxonomies_by_query_autocomplete');
}

if (!function_exists('woodmart_get_post_by_ids_autocomplete')) {
    /**
     * Autocomplete by post ids.
     *
     * @param array $ids Posts ids.
     *
     * @return array
     * @since 1.0.0
     *
     */
    function woodmart_get_post_by_ids_autocomplete($ids)
    {
        $output = array();

        if (!$ids) {
            return $output;
        }

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $post = get_post($id);

            if ($post) {
                $output[$post->ID] = array(
                    'name' => $post->post_title . ' ID:(' . $post->ID . ')',
                    'value' => $post->ID,
                );
            }
        }

        return $output;
    }
}

if (!function_exists('woodmart_get_post_by_query_autocomplete')) {
    /**
     * Autocomplete by post.
     *
     * @since 1.0.0
     */
    function woodmart_get_post_by_query_autocomplete()
    {
        $output = array();

        $args = array(
            'post_type' => $_POST['value'],
            's' => isset($_POST['params']['term']) ? $_POST['params']['term'] : '', // phpcs:ignore
            'post_status' => 'publish',
            'numberposts' => apply_filters('woodmart_get_numberposts_by_query_autocomplete', 20),
            'exclude' => isset($_POST['selected']) ? $_POST['selected'] : array(),
        );

        $posts = get_posts($args);

        if (count($posts) > 0) { // phpcs:ignore
            foreach ($posts as $value) {
                $output[] = array(
                    'id' => $value->ID,
                    'text' => $value->post_title . ' ID:(' . $value->ID . ')',
                );
            }
        }

        echo wp_json_encode($output);
        die();
    }

    add_action('wp_ajax_woodmart_get_post_by_query_autocomplete', 'woodmart_get_post_by_query_autocomplete');
    add_action('wp_ajax_nopriv_woodmart_get_post_by_query_autocomplete', 'woodmart_get_post_by_query_autocomplete');
}

// **********************************************************************//
// ! Body classes
// **********************************************************************//

if (!function_exists('xts_get_default_value')) {
    /**
     * Get default theme settings value
     *
     * @param string $key Value key.
     *
     * @return string
     * @since 1.0.0
     *
     */
    function xts_get_default_value($key)
    {
        // $default_values = xts_get_config( 'framework-defaults' );
        // $theme_values   = xts_get_config( 'theme-defaults' );

        // if ( $theme_values ) {
        // 	$default_values = wp_parse_args( $theme_values, $default_values );
        // }

        return '';
        return isset($default_values[$key]) ? $default_values[$key] : '';
    }
}

if (!function_exists('woodmart_product_attributes_array')) {
    function woodmart_product_attributes_array()
    {

        if (!function_exists('wc_get_attribute_taxonomies')) {
            return array();
        }
        $attributes = array();

        foreach (wc_get_attribute_taxonomies() as $attribute) {
            $attributes['pa_' . $attribute->attribute_name] = array(
                'name' => $attribute->attribute_label,
                'value' => 'pa_' . $attribute->attribute_name,
            );
        }

        return $attributes;
    }
}

if (!function_exists('woodmart_get_pages_array')) {
    /**
     * Get all pages array
     *
     * @return array
     * @since 1.0.0
     *
     */
    function woodmart_get_pages_array()
    {
        $pages = array();

        foreach (get_pages() as $page) {
            $pages[$page->ID] = array(
                'name' => $page->post_title,
                'value' => $page->ID,
            );
        }

        return $pages;
    }
}

if (!function_exists('woodmart_body_class')) {
    function woodmart_body_class($classes)
    {

        $page_id = woodmart_page_ID();

        $site_width = woodmart_get_opt('site_width');
        $product_design = woodmart_product_design();
        $product_sticky = woodmart_product_sticky();

        $ajax_shop = woodmart_get_opt('ajax_shop');
        $hide_sidebar_mobile = woodmart_get_opt('shop_hide_sidebar');
        $hide_sidebar_tablet = woodmart_get_opt('shop_hide_sidebar_tablet');
        $hide_sidebar_desktop = woodmart_get_opt('shop_hide_sidebar_desktop');
        $catalog_mode = woodmart_get_opt('catalog_mode');
        $categories_toggle = woodmart_get_opt('categories_toggle');
        $sticky_footer = woodmart_get_opt('sticky_footer');
        $dark = woodmart_get_opt('dark_version');
        $form_fields_style = (woodmart_get_opt('form_fields_style')) ? woodmart_get_opt('form_fields_style') : 'square';
        $form_border_width = woodmart_get_opt('form_border_width');
        $single_post_design = woodmart_get_opt('single_post_design');
        $main_sidebar_mobile = woodmart_get_opt('hide_main_sidebar_mobile');

        if ($single_post_design == 'large_image' && is_single()) {
            $classes[] = 'single-post-large-image';
        }

        $classes[] = 'wrapper-' . $site_width;

        if ('underlined' === $form_fields_style) {
            $classes[] = 'form-style-' . $form_fields_style;
        } else {
            $classes[] = woodmart_get_old_classes(' form-style-' . $form_fields_style);
        }

        $classes[] = woodmart_get_old_classes(' form-border-width-' . $form_border_width);

        if (is_singular('product')) {
            $classes[] = 'woodmart-product-design-' . $product_design;
            if ($product_sticky) {
                $classes[] = 'woodmart-product-sticky-on';
                wp_enqueue_script('imagesloaded');
                woodmart_enqueue_js_library('sticky-kit');
                woodmart_enqueue_js_script('sticky-details');
            }
        }

        if (woodmart_woocommerce_installed() && (is_shop() || is_product_category()) && ($hide_sidebar_desktop && $sticky_footer)) {
            $classes[] = 'no-sticky-footer';
        } elseif ($sticky_footer) {
            wp_enqueue_script('imagesloaded');
            woodmart_enqueue_js_script('sticky-footer');
            $classes[] = 'sticky-footer-on';
        }

        if ($dark) {
            $classes[] = 'global-color-scheme-light';
        }

        if ($catalog_mode) {
            $classes[] = 'catalog-mode-on';
        }

        if ($categories_toggle) {
            $classes[] = 'categories-accordion-on';
        }

        if (woodmart_is_shop_archive()) {
            $classes[] = 'woodmart-archive-shop';
        } else if (woodmart_is_portfolio_archive()) {
            $classes[] = 'woodmart-archive-portfolio';
        } else if (woodmart_is_blog_archive()) {
            $classes[] = 'woodmart-archive-blog';
        }

        //Header banner
        if (!woodmart_get_opt('header_close_btn') && woodmart_get_opt('header_banner') && !isset($GLOBALS['wd_maintenance'])) {
            $classes[] = 'header-banner-display';
        }
        if (woodmart_get_opt('header_banner') && !isset($GLOBALS['wd_maintenance'])) {
            $classes[] = 'header-banner-enabled';
        }

        if ($ajax_shop) {
            $classes[] = 'woodmart-ajax-shop-on';
        }

        if ($hide_sidebar_mobile && (woodmart_woocommerce_installed() && (is_shop() || is_product_category() || is_product_tag() || woodmart_is_product_attribute_archive())) || $main_sidebar_mobile && (!woodmart_woocommerce_installed() || (!is_shop() && !is_product_category() && !is_product_tag() && !woodmart_is_product_attribute_archive()))) {
            $classes[] = 'offcanvas-sidebar-mobile';
        }

        if ($hide_sidebar_tablet) {
            $classes[] = 'offcanvas-sidebar-tablet';
        }

        if ($hide_sidebar_desktop) {
            $classes[] = 'offcanvas-sidebar-desktop';
        }

        if (!is_user_logged_in() && woodmart_get_opt('login_prices')) {
            $classes[] = 'login-see-prices';
        }

        if (woodmart_get_opt('sticky_notifications')) {
            $classes[] = 'notifications-sticky';
        }

        if (woodmart_get_opt('sticky_toolbar') && !woodmart_is_maintenance_active()) {
            $classes[] = 'sticky-toolbar-on';
        }
        if (woodmart_get_opt('hide_larger_price')) {
            $classes[] = 'hide-larger-price';
        }

        if ((is_singular('product') || is_singular('woodmart_layout')) && woodmart_get_opt('single_sticky_add_to_cart')) {
            $classes[] = 'wd-sticky-btn-on';

            if (woodmart_get_opt('mobile_single_sticky_add_to_cart')) {
                $classes[] = 'wd-sticky-btn-on-mb';
            }
        }

        $classes = array_merge($classes, woodmart_get_header_body_classes());

        return $classes;
    }

    add_filter('body_class', 'woodmart_body_class');
}


/**
 * ------------------------------------------------------------------------------------------------
 * Get header body classes
 * ------------------------------------------------------------------------------------------------
 */

if (!function_exists('woodmart_get_header_body_classes')) {
    function woodmart_get_header_body_classes()
    {
        $classes = array();
        $settings = whb_get_settings();
        if (isset($settings['overlap']) && $settings['overlap']) {
            $classes[] = 'wd-header-overlap';
            $classes[] = woodmart_get_old_classes('woodmart-header-overcontent');
        }
        if ('light' === whb_get_dropdowns_color()) {
            $classes[] = 'dropdowns-color-light';
        }
        return $classes;
    }
}

/**
 * ------------------------------------------------------------------------------------------------
 * Filter wp_title
 * ------------------------------------------------------------------------------------------------
 */

if (!function_exists('woodmart_wp_title')) {
    function woodmart_wp_title($title, $sep)
    {
        global $paged, $page;

        if (is_feed())
            return $title;

        // Add the site name.
        $title .= get_bloginfo('name');

        // Add the site description for the home/front page.
        $site_description = get_bloginfo('description', 'display');
        if ($site_description && (is_home() || is_front_page()))
            $title = "$title $sep $site_description";

        // Add a page number if necessary.
        if ($paged >= 2 || $page >= 2)
            $title = "$title $sep " . sprintf(esc_html__('Page %s', 'woodmart'), max($paged, $page));

        return $title;
    }

    add_filter('wp_title', 'woodmart_wp_title', 10, 2);

}

/**
 * ------------------------------------------------------------------------------------------------
 * Get predefined footer configuration by index
 * ------------------------------------------------------------------------------------------------
 */

if (!function_exists('woodmart_get_footer_config')) {
    function woodmart_get_footer_config($index)
    {

        if ($index > 20 || $index < 1) {
            $index = 1;
        }

        $configs = apply_filters('woodmart_footer_configs_array', array(
            1 => array(
                'cols' => array(
                    'col-12'
                ),

            ),
            2 => array(
                'cols' => array(
                    'col-12 col-sm-6',
                    'col-12 col-sm-6',
                ),
            ),
            3 => array(
                'cols' => array(
                    'col-12 col-sm-4',
                    'col-12 col-sm-4',
                    'col-12 col-sm-4',
                ),
            ),
            4 => array(
                'cols' => array(
                    'col-12 col-sm-6 col-lg-3',
                    'col-12 col-sm-6 col-lg-3',
                    'col-12 col-sm-6 col-lg-3',
                    'col-12 col-sm-6 col-lg-3',
                ),
            ),
            5 => array(
                'cols' => array(
                    'col-12 col-sm-6 col-md-4 col-lg-2',
                    'col-12 col-sm-6 col-md-4 col-lg-2',
                    'col-12 col-sm-6 col-md-4 col-lg-2',
                    'col-12 col-sm-6 col-md-4 col-lg-2',
                    'col-12 col-sm-6 col-md-4 col-lg-2',
                    'col-12 col-sm-6 col-md-4 col-lg-2',
                ),
            ),
            6 => array(
                'cols' => array(
                    'col-12 col-sm-4 col-lg-3',
                    'col-12 col-sm-4 col-lg-6',
                    'col-12 col-sm-4 col-lg-3',
                ),
            ),
            7 => array(
                'cols' => array(
                    'col-12 col-sm-4 col-lg-6',
                    'col-12 col-sm-4 col-lg-3',
                    'col-12 col-sm-4 col-lg-3',
                ),
            ),
            8 => array(
                'cols' => array(
                    'col-12 col-sm-4 col-lg-3',
                    'col-12 col-sm-4 col-lg-3',
                    'col-12 col-sm-4 col-lg-6',
                ),
            ),
            9 => array(
                'cols' => array(
                    'col-12',
                    'col-12 col-sm-6 col-lg-3',
                    'col-12 col-sm-6 col-lg-3',
                    'col-12 col-sm-6 col-lg-3',
                    'col-12 col-sm-6 col-lg-3',
                ),
            ),
            10 => array(
                'cols' => array(
                    'col-12 col-md-6',
                    'col-12 col-md-6',
                    'col-12 col-sm-6 col-lg-3',
                    'col-12 col-sm-6 col-lg-3',
                    'col-12 col-sm-6 col-lg-3',
                    'col-12 col-sm-6 col-lg-3',
                ),
            ),
            11 => array(
                'cols' => array(
                    'col-12 col-md-6',
                    'col-12 col-md-6',
                    'col-12 col-sm-6 col-md-3 col-lg-2',
                    'col-12 col-sm-6 col-md-3 col-lg-2',
                    'col-12 col-sm-6 col-md-3 col-lg-2',
                    'col-12 col-sm-6 col-md-3 col-lg-2',
                    'col-12 col-lg-4',
                ),
            ),
            12 => array(
                'cols' => array(
                    'col-12',
                    'col-12 col-sm-6 col-md-3 col-lg-2',
                    'col-12 col-sm-6 col-md-3 col-lg-2',
                    'col-12 col-sm-6 col-md-3 col-lg-2',
                    'col-12 col-sm-6 col-md-3 col-lg-2',
                    'col-12 col-lg-4',
                ),
            ),
            13 => array(
                'cols' => array(
                    'col-12 col-sm-6 col-lg-3',
                    'col-12 col-sm-6 col-lg-3',
                    'col-12 col-sm-4 col-lg-2',
                    'col-12 col-sm-4 col-lg-2',
                    'col-12 col-sm-4 col-lg-2',
                ),
            ),
        ));

        return (isset($configs[$index])) ? $configs[$index] : array();
    }
}


// **********************************************************************//
// ! Theme 3d plugins
// **********************************************************************//


if (!defined('YITH_REFER_ID')) {
    define('YITH_REFER_ID', '1040314');
}


if (!function_exists('woodmart_3d_plugins')) {
    function woodmart_3d_plugins()
    {
        if (function_exists('set_revslider_as_theme')) {
            set_revslider_as_theme();
        }
    }

    add_action('init', 'woodmart_3d_plugins');
}

if (!function_exists('woodmart_vcSetAsTheme')) {

    function woodmart_vcSetAsTheme()
    {
        if (function_exists('vc_set_as_theme')) {
            vc_set_as_theme();
        }
    }

    add_action('vc_before_init', 'woodmart_vcSetAsTheme');
}


// **********************************************************************//
// ! Obtain real page ID (shop page, blog, portfolio or simple page)
// **********************************************************************//

/**
 * This function is called once when initializing WOODMART_Layout object
 * then you can use function woodmart_page_ID to get current page id
 */
if (!function_exists('woodmart_get_the_ID')) {
    function woodmart_get_the_ID($args = array())
    {
        global $post;

        $page_id = 0;

        $page_for_posts = get_option('page_for_posts');
        $page_for_shop = get_option('woocommerce_shop_page_id');
        $page_for_projects = woodmart_get_portfolio_page_id();
        $custom_404_id = woodmart_get_opt('custom_404_page');

        if (isset($post->ID)) {
            $page_id = $post->ID;
        }

        if (isset($post->ID) && (is_singular('page') || is_singular('post'))) {
            $page_id = $post->ID;
        } elseif (is_home() || is_singular('post') || is_search() || is_tag() || is_category() || is_date() || is_author()) {
            $page_id = $page_for_posts;
        } elseif (is_archive() && get_post_type() === 'portfolio') {
            $page_id = $page_for_projects;
        }

        if (woodmart_woocommerce_installed() && function_exists('is_shop')) {
            if (isset($args['singulars']) && in_array('product', $args['singulars']) && is_singular("product")) {
                // keep post id
            } else if (is_shop() || is_product_category() || is_product_tag() || woodmart_is_product_attribute_archive()) {
                $page_id = $page_for_shop;
            }
        }

        if (is_404() && ($custom_404_id != 'default' || !empty($custom_404_id))) $page_id = $custom_404_id;

        return $page_id;
    }
}


// **********************************************************************//
// ! Function to get HTML block content
// **********************************************************************//

if (!function_exists('woodmart_get_html_block')) {
    function woodmart_get_html_block($id)
    {
        $id = apply_filters('wpml_object_id', $id, 'cms_block', true);
        $post = get_post($id);
        $content = '';

        if (!$post || $post->post_type != 'cms_block' || !$id) {
            return;
        }

        if (woodmart_is_elementor_installed() && Plugin::$instance->documents->get($id)->is_built_with_elementor()) {
            $content .= woodmart_elementor_get_content_css($id);
            $content .= woodmart_elementor_get_content($id);
        } else {
            $shortcodes_custom_css = get_post_meta($id, '_wpb_shortcodes_custom_css', true);
            $woodmart_shortcodes_custom_css = get_post_meta($id, 'woodmart_shortcodes_custom_css', true);

            if (!empty($shortcodes_custom_css) || !empty($woodmart_shortcodes_custom_css)) {
                $content .= '<style data-type="vc_shortcodes-custom-css">';
                if (!empty($shortcodes_custom_css)) {
                    $content .= $shortcodes_custom_css;
                }

                if (!empty($woodmart_shortcodes_custom_css)) {
                    $content .= $woodmart_shortcodes_custom_css;
                }
                $content .= '</style>';
            }

            $content .= do_shortcode($post->post_content);
        }

        return $content;
    }

}

if (!function_exists('woodmart_get_static_blocks_array')) {
    function woodmart_get_static_blocks_array($new = false, $empty = false)
    {
        $args = array('posts_per_page' => 500, 'post_type' => 'cms_block');
        $blocks_posts = get_posts($args);
        $array = array();
        foreach ($blocks_posts as $post) :
            if ($new) {
                if ($empty) {
                    $array[''] = array(
                        'name' => esc_html__('Select', 'woodmart'),
                        'value' => '',
                    );
                }
                $array[$post->ID] = array(
                    'name' => $post->post_title . ' (ID:' . $post->ID . ')',
                    'value' => $post->ID,
                );
            } else {
                if ($empty) {
                    $array[esc_html__('Select', 'woodmart')] = '';
                }
                $array[$post->post_title . ' (ID:' . $post->ID . ')'] = $post->ID;
            }
        endforeach;
        return $array;
    }
}

if (!function_exists('woodmart_get_theme_settings_html_blocks_array')) {
    /**
     * Function to get array of HTML Blocks in theme settings array style.
     *
     * @return array
     */
    function woodmart_get_theme_settings_html_blocks_array()
    {
        return woodmart_get_static_blocks_array(true);
    }
}

if (!function_exists('woodmart_get_html_blocks_array_with_empty')) {
    /**
     * Function to get array of HTML Blocks in WPB element array style.
     *
     * @return array
     */
    function woodmart_get_html_blocks_array_with_empty()
    {
        return woodmart_get_static_blocks_array(false, true);
    }
}

if (!function_exists('woodmart_get_theme_settings_headers_array')) {
    /**
     * Function to get array of HTML Blocks in theme settings array style.
     *
     * @return array
     */
    function woodmart_get_theme_settings_headers_array()
    {
        $list = get_option('whb_saved_headers');

        if (!$list) {
            $list = whb_get_builder()->list->get_all();
        }

        $headers = array();

        $headers['none'] = array(
            'name' => esc_html__('None', 'woodmart'),
            'value' => 'none',
        );

        if (!empty($list) && is_array($list)) {
            foreach ($list as $key => $header) {
                $headers[$key] = array(
                    'name' => $header['name'],
                    'value' => $key,
                );
            }
        }

        return $headers;
    }
}

if (!function_exists('woodmart_get_elementor_html_blocks_array')) {
    /**
     * Function to get array of HTML Blocks.
     *
     * @return array
     */
    function woodmart_get_elementor_html_blocks_array()
    {
        $output = array();

        $posts = get_posts(
            array(
                'posts_per_page' => 500, // phpcs:ignore
                'post_type' => 'cms_block',
            )
        );

        $output['0'] = esc_html__('Select', 'woodmart');

        foreach ($posts as $post) {
            $output[$post->ID] = $post->post_title;
        }

        return $output;
    }
}
// **********************************************************************//
// ! Set excerpt length and more btn
// **********************************************************************//

add_filter('excerpt_length', 'woodmart_excerpt_length', 999);

if (!function_exists('woodmart_excerpt_length')) {
    function woodmart_excerpt_length($length)
    {
        return 20;
    }
}

add_filter('excerpt_more', 'woodmart_new_excerpt_more');

if (!function_exists('woodmart_new_excerpt_more')) {
    function woodmart_new_excerpt_more($more)
    {
        return '';
    }
}

// **********************************************************************//
// ! Add scroll to top buttom
// **********************************************************************//

add_action('woodmart_before_wp_footer', 'woodmart_scroll_top_btn');

if (!function_exists('woodmart_scroll_top_btn')) {
    function woodmart_scroll_top_btn($more)
    {
        if (!woodmart_get_opt('scroll_top_btn')) return;

        woodmart_enqueue_js_script('scroll-top');
        woodmart_enqueue_inline_style('scroll-top');
        ?>
        <a href="#" class="scrollToTop" aria-label="<?php esc_html_e('Scroll to top button', 'woodmart'); ?>"></a>
        <?php
    }
}


// **********************************************************************//
// ! Return related posts args array
// **********************************************************************//

if (!function_exists('woodmart_get_related_posts_args')) {
    function woodmart_get_related_posts_args($post_id)
    {
        $taxs = wp_get_post_tags($post_id);
        $args = array();
        if ($taxs) {
            $tax_ids = array();
            foreach ($taxs as $individual_tax) $tax_ids[] = $individual_tax->term_id;

            $args = array(
                'tag__in' => $tax_ids,
                'post__not_in' => array($post_id),
                'showposts' => 12,
                'ignore_sticky_posts' => 1
            );

        }

        return $args;
    }
}

if (!function_exists('woodmart_get_related_projects_args')) {
    function woodmart_get_related_projects_args($post_id)
    {
        $taxs = wp_get_post_terms($post_id, 'project-cat');
        $args = array();
        if ($taxs) {
            $tax_ids = array();
            foreach ($taxs as $individual_tax) $tax_ids[] = $individual_tax->term_id;

            $args = array(
                'post_type' => 'portfolio',
                'post__not_in' => array($post_id),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'project-cat',
                        'terms' => $tax_ids,
                        'include_children' => false
                    ),
                )
            );
        }

        return $args;
    }
}

// **********************************************************************//
// ! Navigation walker
// **********************************************************************//

if (!class_exists('WOODMART_Mega_Menu_Walker')) {
    class WOODMART_Mega_Menu_Walker extends Walker_Nav_Menu
    {
        /**
         * Design.
         *
         * @var string
         */
        private $color_scheme;

        /**
         * Design.
         *
         * @var string
         */
        private $design = 'default';

        /**
         * ID.
         *
         * @var integer
         */
        private $id;

        /**
         * WOODMART_Mega_Menu_Walker constructor.
         */
        public function __construct()
        {
            $this->color_scheme = whb_get_dropdowns_color();
        }

        /**
         * Starts the list before the elements are added.
         *
         * @param string $output Passed by reference. Used to append additional content.
         * @param int $depth Depth of menu item. Used for padding.
         * @param mixed $args An array of arguments. @see wp_nav_menu().
         *
         * @since 3.0.0
         *
         * @see   Walker::start_lvl()
         */
        public function start_lvl(&$output, $depth = 0, $args = array())
        {
            $indent = str_repeat("\t", $depth);
            $is_nav_mobile = strstr($args->menu_class, 'wd-nav-mobile');
            $is_nav_fs = strstr($args->menu_class, 'wd-nav-fs');
            $classes = '';
            $style = get_post_meta($this->id, '_menu_item_style_' . $this->design, true);
            $scroll = get_post_meta($this->id, '_menu_item_scroll', true);

            if (0 === $depth && !$is_nav_mobile) {
                if ('default' !== $this->color_scheme) {
                    $classes .= ' color-scheme-' . $this->color_scheme;
                }

                $classes .= ' wd-design-' . $this->design;

                if (!$is_nav_fs) {
                    $classes .= ' wd-dropdown-menu wd-dropdown';
                } else {
                    $classes .= ' wd-dropdown-fs-menu';
                }

                if ($style) {
                    $classes .= ' wd-style-' . $style;
                }

                if ('full-height' === $this->design || 'yes' === $scroll) {
                    $classes .= ' wd-scroll';
                }

                $classes .= woodmart_get_old_classes(' sub-menu-dropdown');

                $output .= $indent . '<div class="' . trim($classes) . '">';

                if ('full-height' === $this->design) {
                    $output .= $indent . '<div class="wd-scroll-content">';
                    $output .= $indent . '<div class="wd-dropdown-inner">';
                }

                $output .= $indent . '<div class="container">';

                if ('aside' === $this->design) {
                    $output .= $indent . '<div class="wd-sub-menu-wrapp">';
                }
            }

            if (0 === $depth) {
                if (('full-width' === $this->design || 'sized' === $this->design || 'full-height' === $this->design) && !$is_nav_mobile) {
                    $sub_menu_class = 'wd-sub-menu row';
                    $sub_menu_class .= woodmart_get_old_classes(' sub-menu');
                } else {
                    $sub_menu_class = 'wd-sub-menu';
                    $sub_menu_class .= woodmart_get_old_classes(' sub-menu');
                }
            } else {
                if ('default' === $this->design && !$is_nav_mobile && !$is_nav_fs) {
                    $sub_menu_class = 'sub-sub-menu wd-dropdown';
                } elseif ('default' === $this->design && $is_nav_fs) {
                    $sub_menu_class = 'sub-sub-menu wd-dropdown-fs-menu';
                } else {
                    $sub_menu_class = 'sub-sub-menu';
                }
            }

            if (!$is_nav_mobile && 0 === $depth) {
                $sub_menu_class .= ' color-scheme-' . $this->color_scheme;
            } elseif (!$is_nav_mobile && 1 === $depth && 'aside' === $this->design) {
                $output .= $indent . '<div class="wd-dropdown-menu wd-dropdown wd-wp-menu">';
            }

            $output .= "\n$indent<ul class=\"$sub_menu_class\">\n";

            if ('light' === $this->color_scheme || 'dark' === $this->color_scheme) {
                $this->color_scheme = whb_get_dropdowns_color();
            }
        }

        /**
         * Ends the list of after the elements are added.
         *
         * @param string $output Passed by reference. Used to append additional content.
         * @param int $depth Depth of menu item. Used for padding.
         * @param mixed $args An array of arguments. @see wp_nav_menu().
         *
         * @since 3.0.0
         *
         * @see   Walker::end_lvl()
         */
        public function end_lvl(&$output, $depth = 0, $args = array())
        {
            $is_nav_mobile = strstr($args->menu_class, 'wd-nav-mobile');
            $indent = str_repeat("\t", $depth);
            $output .= "$indent</ul>\n";

            if (!$is_nav_mobile && 1 === $depth && 'aside' === $this->design) {
                $output .= "$indent</div>\n";
            }

            if (0 === $depth && !$is_nav_mobile) {
                if ('aside' === $this->design) {
                    $output .= "$indent</div>\n";
                } elseif ('full-height' === $this->design) {
                    $output .= $indent . '</div>';
                    $output .= $indent . '</div>';
                }

                $output .= "$indent</div>\n";
                $output .= "$indent</div>\n";
            }
        }

        /**
         * Start the element output.
         *
         * @param string $output Passed by reference. Used to append additional content.
         * @param object $item Menu item data object.
         * @param int $depth Depth of menu item. Used for padding.
         * @param mixed $args An array of arguments. @see wp_nav_menu().
         * @param int $id Current item ID.
         *
         * @since 3.0.0
         *
         * @see   Walker::start_el()
         */
        public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
        {
            $this->id = $item->ID;
            $indent = $depth ? str_repeat("\t", $depth) : '';
            $classes = empty($item->classes) ? array() : (array)$item->classes;
            $classes[] = 'menu-item-' . $item->ID;
            $classes[] = 'item-level-' . $depth;
            $label_out = '';
            $design = get_post_meta($item->ID, '_menu_item_design', true);
            $width = get_post_meta($item->ID, '_menu_item_width', true);
            $height = get_post_meta($item->ID, '_menu_item_height', true);
            $scroll = get_post_meta($item->ID, '_menu_item_scroll', true);
            $icon = get_post_meta($item->ID, '_menu_item_icon', true);
            $event = get_post_meta($item->ID, '_menu_item_event', true);
            $label = get_post_meta($item->ID, '_menu_item_label', true);
            $label_text = get_post_meta($item->ID, '_menu_item_label-text', true);
            $block = get_post_meta($item->ID, '_menu_item_block', true);
            $dropdown_ajax = get_post_meta($item->ID, '_menu_item_dropdown-ajax', true);
            $opanchor = get_post_meta($item->ID, '_menu_item_opanchor', true);
            $color_scheme = get_post_meta($item->ID, '_menu_item_colorscheme', true);
            $image_type = get_post_meta($item->ID, '_menu_item_image-type', true);

            $is_nav_mobile = false;
            if (is_object($args) && property_exists($args, 'menu_class')) {
                $is_nav_mobile = strstr($args->menu_class, 'wd-nav-mobile');
                $is_nav_fs = strstr($args->menu_class, 'wd-nav-fs');
            }

            if ('light' === $color_scheme) {
                $this->color_scheme = 'light';
            } elseif ('dark' === $color_scheme) {
                $this->color_scheme = 'dark';
            }

            if (0 === $depth && $design) {
                $this->design = $design;
            }

            if (!$design) {
                $design = 'default';
            }

            if (!$this->design) {
                $this->design = 'default';
            }

            if ('aside' === $design) {
                woodmart_enqueue_inline_style('dropdown-aside');
            }

            if ('full-height' === $design) {
                woodmart_enqueue_inline_style('dropdown-full-height');
            }

            if ('full-height' === $design || 'yes' === $scroll && ('full-width' === $design || 'sized' === $design)) {
                woodmart_enqueue_inline_style('header-mod-content-calc');
            }

            if (!is_object($args)) {
                return;
            }

            if (0 === $depth && !$is_nav_mobile) {
                $classes[] = woodmart_get_old_classes('menu-item-design-' . $design);
                if ('sized' === $design || 'full-width' === $design || 'aside' === $design || 'full-height' === $design) {
                    $classes[] = 'menu-mega-dropdown';
                } else {
                    $classes[] = 'menu-simple-dropdown';
                }
            }

            $event = empty($event) ? 'hover' : $event;

            if (!$is_nav_fs && !$is_nav_mobile) {
                $classes[] = 'wd-event-' . $event;
            }

            if (('full-width' === $this->design || 'sized' === $this->design || 'full-height' === $this->design) && 1 === $depth && !$is_nav_mobile) {
                $classes[] .= 'col-auto';
            }

            if ($block && $is_nav_mobile) {
                $classes[] = 'menu-item-has-block';
            }

            if ('enable' === $opanchor) {
                woodmart_enqueue_js_library('waypoints');
                woodmart_enqueue_js_script('one-page-menu');
                $classes[] = 'onepage-link';
                $key = array_search('current-menu-item', $classes);
                if (false !== $key) {
                    unset($classes[$key]);
                }
            }

            if (!empty($label)) {
                woodmart_enqueue_inline_style('mod-nav-menu-label');

                $classes[] = 'item-with-label';
                $classes[] = 'item-label-' . $label;
                $label_out = '<span class="menu-label menu-label-' . $label . '">' . esc_attr($label_text) . '</span>';
            }

            woodmart_enqueue_js_script('menu-offsets');
            woodmart_enqueue_js_script('menu-setup');

            if (!empty($block) && !$args->walker->has_children) {
                $classes[] = 'menu-item-has-children';
            }

            if ('yes' === $dropdown_ajax) {
                woodmart_enqueue_js_script('menu-dropdowns-ajax');
                $classes[] = 'dropdown-load-ajax';
            }

            if ($height && ('sized' === $design || 'aside' === $design || 'full-width' === $design)) {
                $classes[] = 'dropdown-with-height';
            }

            /**
             * Filter the CSS class(es) applied to a menu item's list item element.
             *
             * @param array $classes The CSS classes that are applied to the menu item's `<li>` element.
             * @param object $item The current menu item.
             * @param array $args An array of {@see wp_nav_menu()} arguments.
             * @param int $depth Depth of menu item. Used for padding.
             * @since 3.0.0
             * @since 4.1.0 The `$depth` parameter was added.
             *
             */
            $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth));
            $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

            /**
             * Filter the ID applied to a menu item's list item element.
             *
             * @param string $menu_id The ID that is applied to the menu item's `<li>` element.
             * @param object $item The current menu item.
             * @param array $args An array of {@see wp_nav_menu()} arguments.
             * @param int $depth Depth of menu item. Used for padding.
             * @since 3.0.1
             * @since 4.1.0 The `$depth` parameter was added.
             *
             */
            $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth);
            $id = $id ? ' id="' . esc_attr($id) . '"' : '';

            $styles = '';

            if (('aside' === $design || 'sized' === $design || 'full-height' === $design || 'full-width' === $design) && !$is_nav_mobile && ($height || $width)) {
                if ($height) {
                    $styles .= '--wd-dropdown-height: ' . $height . 'px;';
                }
                if ($width) {
                    $styles .= '--wd-dropdown-width: ' . $width . 'px;';
                }
            }

            if (0 === $depth && !$is_nav_mobile && 'image' !== $image_type) {
                if (has_post_thumbnail($item->ID)) {
                    $post_thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($item->ID), 'full');

                    if (!empty($post_thumbnail) && isset($post_thumbnail[0])) {
                        $styles .= '--wd-dropdown-bg-img: url(' . $post_thumbnail[0] . ');';
                    }
                }
            }

            if ($styles) {
                $styles = 'style="' . $styles . '"';
            }

            $output .= $indent . '<li' . $id . $class_names . ' ' . $styles . '>';

            $atts = array();
            $atts['title'] = !empty($item->attr_title) ? $item->attr_title : '';
            $atts['target'] = !empty($item->target) ? $item->target : '';
            $atts['rel'] = !empty($item->xfn) ? $item->xfn : '';
            $atts['href'] = !empty($item->url) ? $item->url : '';

            /**
             * Filter the HTML attributes applied to a menu item's anchor element.
             *
             * @param array $atts {
             *                       The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
             *
             * @type string $title Title attribute.
             * @type string $target Target attribute.
             * @type string $rel The rel attribute.
             * @type string $href The href attribute.
             * }
             *
             * @param object $item The current menu item.
             * @param array $args An array of {@see wp_nav_menu()} arguments.
             * @param int $depth Depth of menu item. Used for padding.
             * @since 3.6.0
             * @since 4.1.0 The `$depth` parameter was added.
             *
             */
            $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);
            $atts['class'] = 'woodmart-nav-link';

            $attributes = '';
            foreach ($atts as $attr => $value) {
                if (!empty($value)) {
                    $value = 'href' === $attr ? esc_url($value) : esc_attr($value);
                    $attributes .= ' ' . $attr . '="' . $value . '"';
                }
            }

            $image_output = '';

            if ('product_cat' === $item->object || ('image' === $image_type && has_post_thumbnail($item->ID))) {
                if ('image' === $image_type && has_post_thumbnail($item->ID)) {
                    $icon_data = array(
                        'id' => get_post_thumbnail_id($item->ID),
                        'url' => get_the_post_thumbnail_url($item->ID),
                    );
                } else {
                    $icon_data = get_term_meta($item->object_id, 'category_icon_alt', true);
                }

                $icon_attrs = apply_filters('woodmart_megamenu_icon_attrs', false);

                if ($icon_data) {
                    if (is_array($icon_data) && $icon_data['id']) {
                        if (woodmart_is_svg($icon_data['url'])) {
                            $image_output .= woodmart_get_svg_html($icon_data['id'], apply_filters('woodmart_mega_menu_icon_size_svg', '18x18'), array('class' => 'wd-nav-img'));
                        } else {
                            $image_output .= wp_get_attachment_image($icon_data['id'], apply_filters('woodmart_mega_menu_icon_size', 'thumbnail'), false, array('class' => 'wd-nav-img'));
                        }
                    } else {
                        if (isset($icon_data['url'])) {
                            $icon_data = $icon_data['url'];
                        }

                        if ($icon_data) {
                            $image_output .= '<img src="' . esc_url($icon_data) . '" alt="' . esc_attr($item->title) . '" ' . $icon_attrs . ' class="wd-nav-img' . woodmart_get_old_classes(' category-icon') . '" />';
                        }
                    }
                }
            }

            $item_output = $args->before;
            $item_output .= '<a' . $attributes . '>';
            if ($icon) {
                if ('wpb' === woodmart_get_current_page_builder()) {
                    wp_enqueue_style('vc_font_awesome_5');
                    wp_enqueue_style('vc_font_awesome_5_shims');
                } else {
                    wp_enqueue_style('elementor-icons-fa-solid');
                    wp_enqueue_style('elementor-icons-fa-brands');
                    wp_enqueue_style('elementor-icons-fa-regular');
                }
                $item_output .= '<span class="wd-nav-icon fa fa-' . $icon . '"></span>';
            }

            $item_output .= $image_output;

            /** This filter is documented in wp-includes/post-template.php */
            if (0 === $depth) {
                $item_output .= '<span class="nav-link-text">' . $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after . '</span>';
            } else {
                $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
            }

            $item_output .= $label_out;
            $item_output .= '</a>';
            $item_output .= $args->after;

            if (!$is_nav_mobile) {
                if ($block && !$args->walker->has_children) {
                    $classes = '';

                    if (!$is_nav_fs) {
                        $classes .= ' wd-dropdown-menu wd-dropdown';
                    } else {
                        $classes .= ' wd-dropdown-fs-menu';
                    }

                    $classes .= ' wd-design-' . $design;
                    $classes .= ' color-scheme-' . $this->color_scheme;
                    $classes .= woodmart_get_old_classes(' sub-menu-dropdown');

                    if ('full-height' === $this->design || 'yes' === $scroll) {
                        $classes .= ' wd-scroll';
                    }

                    $item_output .= "\n$indent<div class=\"" . trim($classes) . "\">\n";

                    if ('full-height' === $design || 'yes' === $scroll) {
                        $item_output .= "\n$indent<div class=\"wd-scroll-content\">\n";
                        $item_output .= "\n$indent<div class=\"wd-dropdown-inner\">\n";
                    }

                    $item_output .= "\n$indent<div class=\"container\">\n";
                    if ('yes' === $dropdown_ajax) {
                        $item_output .= '<div class="dropdown-html-placeholder wd-fill" data-id="' . $block . '"></div>';
                    } else {
                        $item_output .= woodmart_html_block_shortcode(array('id' => $block));
                    }
                    $item_output .= "\n$indent</div>\n";

                    if ('full-height' === $design || 'yes' === $scroll) {
                        $item_output .= "\n$indent</div>\n";
                        $item_output .= "\n$indent</div>\n";
                    }

                    $item_output .= "\n$indent</div>\n";

                    if ('light' === $this->color_scheme || 'dark' === $this->color_scheme) {
                        $this->color_scheme = whb_get_dropdowns_color();
                    }
                }
            } elseif (strstr($args->menu_class, 'wd-html-block-on') && $block && !$args->walker->has_children) {
                $item_output .= '<div class="wd-sub-menu">';
                $item_output .= woodmart_html_block_shortcode(array('id' => $block));
                $item_output .= '</div>';
            }

            /**
             * Filter a menu item's starting output.
             *
             * The menu item's starting output only includes `$args->before`, the opening `<a>`,
             * the menu item's title, the closing `</a>`, and `$args->after`. Currently, there is
             * no filter for modifying the opening and closing `<li>` for a menu item.
             *
             * @param string $item_output The menu item's starting HTML output.
             * @param object $item Menu item data object.
             * @param int $depth Depth of menu item. Used for padding.
             * @param array $args An array of {@see wp_nav_menu()} arguments.
             * @since 3.0.0
             *
             */
            $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
        }
    }
}

// **********************************************************************//
// ! Load menu drodpwns with AJAX actions
// **********************************************************************//

if (!function_exists('woodmart_load_html_dropdowns_action')) {
    function woodmart_load_html_dropdowns_action()
    {
        $response = array(
            'status' => 'error',
            'message' => 'Can\'t load HTML blocks with AJAX',
            'data' => array(),
        );

        if (class_exists('WPBMap'))
            WPBMap::addAllMappedShortcodes();

        if (isset($_POST['ids']) && is_array($_POST['ids'])) {
            $ids = woodmart_clean($_POST['ids']);
            foreach ($ids as $id) {
                $id = (int)$id;
                $content = woodmart_get_html_block($id);
                if (!$content) continue;

                $response['status'] = 'success';
                $response['message'] = 'At least one HTML block loaded';
                $response['data'][$id] = $content;
            }
        }

        wp_send_json($response);
    }

    add_action('wp_ajax_woodmart_load_html_dropdowns', 'woodmart_load_html_dropdowns_action');
    add_action('wp_ajax_nopriv_woodmart_load_html_dropdowns', 'woodmart_load_html_dropdowns_action');
}

// **********************************************************************//
// ! // Deletes first gallery shortcode and returns content (http://stackoverflow.com/questions/17224100/wordpress-remove-shortcode-and-save-for-use-elsewhere)
// **********************************************************************//

if (!function_exists('woodmart_strip_shortcode_gallery')) {
    function woodmart_strip_shortcode_gallery($content)
    {
        preg_match_all('/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER);
        if (!empty($matches)) {
            foreach ($matches as $shortcode) {
                if ('gallery' === $shortcode[2]) {
                    $pos = strpos($content, $shortcode[0]);
                    if ($pos !== false)
                        return substr_replace($content, '', $pos, strlen($shortcode[0]));
                }
            }
        }
        return $content;
    }
}


// **********************************************************************//
// ! Get exceprt from post content
// **********************************************************************//

if (!function_exists('woodmart_excerpt_from_content')) {
    function woodmart_excerpt_from_content($post_content, $limit, $shortcodes = '')
    {
        // Strip shortcodes and HTML tags
        if (empty($shortcodes)) {
            $post_content = preg_replace("/\[caption(.*)\[\/caption\]/i", '', $post_content);
            $post_content = preg_replace('`\[[^\]]*\]`', '', $post_content);
        }

        $post_content = stripslashes(wp_filter_nohtml_kses($post_content));

        if (woodmart_get_opt('blog_words_or_letters') == 'letter') {
            $excerpt = mb_substr($post_content, 0, $limit);
            if (mb_strlen($excerpt) >= $limit) {
                $excerpt .= '...';
            }
        } else {
            $limit++;
            $excerpt = explode(' ', $post_content, $limit);
            if (count($excerpt) >= $limit) {
                array_pop($excerpt);
                $excerpt = implode(" ", $excerpt) . '...';
            } else {
                $excerpt = implode(" ", $excerpt);
            }
        }

        $excerpt = strip_tags($excerpt);

        if (trim($excerpt) == '...') {
            return '';
        }

        return $excerpt;
    }
}

// **********************************************************************//
// ! Get portfolio taxonomies dropdown
// **********************************************************************//

if (!function_exists('woodmart_get_projects_cats_array')) {
    function woodmart_get_projects_cats_array()
    {
        $return = array('All' => '');

        if (!post_type_exists('portfolio')) return array();

        $cats = get_terms('project-cat');

        foreach ($cats as $key => $cat) {
            $return[$cat->name] = $cat->term_id;
        }

        return $return;
    }
}

// **********************************************************************//
// ! Get menus dropdown
// **********************************************************************//

if (!function_exists('woodmart_get_menus_array')) {
    function woodmart_get_menus_array($style = 'default')
    {
        $output = array();

        $menus = wp_get_nav_menus();

        if ('elementor' === $style) {
            $output[''] = esc_html__('Select', 'woodmart');
        }

        foreach ($menus as $menu) {
            if ('elementor' === $style) {
                $output[$menu->term_id] = $menu->name;
            } else {
                $output[$menu->name] = $menu->name;
            }
        }

        return $output;
    }
}

// **********************************************************************//
// ! Get registered sidebars dropdown
// **********************************************************************//

if (!function_exists('woodmart_get_sidebars_array')) {
    function woodmart_get_sidebars_array($new = false)
    {
        global $wp_registered_sidebars;
        $sidebars = array();
        if ($new) {
            $sidebars['none'] = array(
                'name' => esc_html__('None', 'woodmart'),
                'value' => 'none'
            );
        } else {
            $sidebars['none'] = 'none';
        }
        foreach ($wp_registered_sidebars as $id => $sidebar) {
            if ($new) {
                $sidebars[$id] = array(
                    'name' => $sidebar['name'],
                    'value' => $id
                );
            } else {
                $sidebars[$id] = $sidebar['name'];
            }
        }
        return $sidebars;
    }
}

if (!function_exists('woodmart_get_theme_settings_sidebars_array')) {
    /**
     * Get sidebars array in theme settings array style.
     *
     * @return array
     */
    function woodmart_get_theme_settings_sidebars_array()
    {
        return woodmart_get_sidebars_array(true);
    }
}

// **********************************************************************//
// ! Get page id by template name
// **********************************************************************//

if (!function_exists('woodmart_pages_ids_from_template')) {
    function woodmart_pages_ids_from_template($name)
    {
        $pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => $name . '.php'
        ));

        $return = array();

        foreach ($pages as $page) {
            $return[] = $page->ID;
        }

        return $return;
    }
}

// **********************************************************************//
// ! Get content of the SVG icon located in images/svg folder
// **********************************************************************//
if (!function_exists('woodmart_get_svg_content')) {
    function woodmart_get_svg_content($name)
    {
        $folder = WOODMART_THEMEROOT . '/images/svg';
        $file = $folder . '/' . $name . '.svg';

        return (file_exists($file)) ? woodmart_get_any_svg($file) : false;
    }
}

if (!function_exists('woodmart_get_any_svg')) {
    function woodmart_get_any_svg($file, $id = false)
    {
        $content = function_exists('woodmart_get_svg') ? woodmart_get_svg($file) : '';
        $start_tag = '<svg';
        if ($id) {
            $pattern = "/id=\"(\w)+\"/";
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "id=\"" . $id . "\"", $content, 1);
            } else {
                $content = preg_replace("/<svg/", "<svg id=\"" . $id . "\"", $content);
            }
        }
        // Strip doctype
        $position = strpos($content, $start_tag);
        $content = substr($content, $position);

        return $content;
    }
}

// **********************************************************************//
//  Function return vc_row with gradient.
// **********************************************************************//
if (!function_exists('woodmart_get_gradient_attr')) {
    function woodmart_get_gradient_attr($output, $obj, $attr)
    {
        if (isset($attr['woodmart_gradient_switch']) && $attr['woodmart_gradient_switch'] == 'yes') {
            $gradient_css = woodmart_get_gradient_css($attr['woodmart_color_gradient']);
            $output = preg_replace_callback('/wd-row-gradient-enable.*?>/',
                function ($matches) use ($gradient_css) {
                    return strtolower($matches[0] . '<div class="woodmart-row-gradient wd-fill" style="' . $gradient_css . '"></div>');
                }, $output);
        }
        return $output;
    }
}

add_filter('vc_shortcode_output', 'woodmart_get_gradient_attr', 10, 3);

// **********************************************************************//
//  Function return gradient css.
// **********************************************************************//
if (!function_exists('woodmart_get_gradient_css')) {
    function woodmart_get_gradient_css($gradient_attr)
    {
        $gradient_css = explode('|', $gradient_attr);
        $css = $gradient_css[1];
        $webkit_css = $gradient_css[1];

        $css = str_replace(array('left', 'top', 'right', 'bottom'), array('to side1', 'to side2', 'to side3', 'to side4'), $css);
        $css = str_replace(array('side1', 'side2', 'side3', 'side4'), array('right', 'bottom', 'left', 'top'), $css);

        $result = 'background-image:-webkit-' . $webkit_css . ';';
        $result .= 'background-image:' . $css . ';';
        return $result;
    }
}

// **********************************************************************//
//  Function return all images sizes
// **********************************************************************//

if (!function_exists('woodmart_get_all_image_sizes')) {
    /**
     * Retrieve available image sizes
     *
     * @return array
     * @since 1.0.0
     *
     */
    function woodmart_get_all_image_sizes()
    {
        global $_wp_additional_image_sizes;

        $default_image_sizes = array('thumbnail', 'medium', 'medium_large', 'large');
        $image_sizes = array();

        foreach ($default_image_sizes as $size) {
            $image_sizes[$size] = array(
                'width' => (int)get_option($size . '_size_w'),
                'height' => (int)get_option($size . '_size_h'),
                'crop' => (bool)get_option($size . '_crop'),
            );
        }

        if ($_wp_additional_image_sizes) {
            $image_sizes = array_merge($image_sizes, $_wp_additional_image_sizes);
        }

        $image_sizes['full'] = array();

        return $image_sizes;
    }
}

if (!function_exists('woodmart_get_image_dimensions_by_size_key')) {
    /**
     * This function return size array by size key.
     *
     * @param string $size_key enter 'thumbnail' if you want to get size thumbnail array.
     * @return array
     */
    function woodmart_get_image_dimensions_by_size_key($size_key)
    {
        global $_wp_additional_image_sizes;

        if (isset($_wp_additional_image_sizes[$size_key])) {
            $res = $_wp_additional_image_sizes[$size_key];
        } else {
            $res = woodmart_get_image_size($size_key);
        }

        if (strpos($size_key, 'x') && 'woodmart_shop_catalog_x2' !== $size_key) {
            $res = woodmart_get_explode_size($size_key, '600');
        }

        return $res;
    }
}

if (!function_exists('woodmart_get_image_size')) {
    function woodmart_get_image_size($thumb_size)
    {
        if (is_string($thumb_size) && in_array($thumb_size, array('thumbnail', 'thumb', 'medium', 'large', 'full'))) {
            $images_sizes = woodmart_get_all_image_sizes();
            $image_size = $images_sizes[$thumb_size];
            if ($thumb_size == 'full') {
                $image_size['width'] = 3000;
                $image_size['height'] = 3000;
            }
            return array($image_size['width'], $image_size['height']);
        } elseif (is_string($thumb_size)) {
            preg_match_all('/\d+/', $thumb_size, $thumb_matches);
            if (isset($thumb_matches[0])) {
                $thumb_size = array();
                if (count($thumb_matches[0]) > 1) {
                    $thumb_size[] = $thumb_matches[0][0]; // width
                    $thumb_size[] = $thumb_matches[0][1]; // height
                } elseif (count($thumb_matches[0]) > 0 && count($thumb_matches[0]) < 2) {
                    $thumb_size[] = $thumb_matches[0][0]; // width
                    $thumb_size[] = $thumb_matches[0][0]; // height
                } else {
                    $thumb_size = false;
                }
            }
        }

        return $thumb_size;
    }
}

if (!function_exists('woodmart_get_image_src')) {
    function woodmart_get_image_src($thumb_id, $thumb_size)
    {
        if (!$thumb_size) {
            return false;
        }

        $thumb_size = woodmart_get_image_size($thumb_size);
        $thumbnail = wpb_resize($thumb_id, null, $thumb_size[0], $thumb_size[1], true);
        return isset($thumbnail['url']) ? $thumbnail['url'] : '';
    }
}

// **********************************************************************//
// ! Append :hover to CSS selectors array
// **********************************************************************//
if (!function_exists('woodmart_append_hover_state')) {
    function woodmart_append_hover_state($selectors, $focus = false)
    {
        $selectors = explode(',', $selectors[0]);

        $return = array();

        foreach ($selectors as $selector) {
            $return[] = $selector . ':hover';
            ($focus) ? $return[] .= $selector . ':focus' : false;
        }

        return implode(',', $return);
    }
}


// **********************************************************************//
// Woodmart twitter process links
// **********************************************************************//
if (!function_exists('woodmart_twitter_process_links')) {
    function woodmart_twitter_process_links($tweet)
    {

        // Is the Tweet a ReTweet - then grab the full text of the original Tweet
        if (isset($tweet->retweeted_status)) {
            // Split it so indices count correctly for @mentions etc.
            $rt_section = current(explode(":", $tweet->text));
            $text = $rt_section . ": ";
            // Get Text
            $text .= $tweet->retweeted_status->text;
        } else {
            // Not a retweet - get Tweet
            $text = $tweet->text;
        }

        // NEW Link Creation from clickable items in the text
        $text = preg_replace('/((http)+(s)?:\/\/[^<>\s]+)/i', '<a href="$0" target="_blank" rel="nofollow noopener">$0</a>', $text);
        // Clickable Twitter names
        $text = preg_replace('/[@]+([A-Za-z0-9-_]+)/', '<a href="https://twitter.com/$1" target="_blank" rel="nofollow noopener">@\\1</a>', $text);
        // Clickable Twitter hash tags
        $text = preg_replace('/[#]+([A-Za-z0-9-_]+)/', '<a href="https://twitter.com/search?q=%23$1" target="_blank" rel="nofollow noopener">$0</a>', $text);
        // END TWEET CONTENT REGEX
        return $text;

    }
}

// **********************************************************************//
// Woodmart Owl Items Per Slide
// **********************************************************************//
if (!function_exists('woodmart_owl_items_per_slide')) {
    function woodmart_owl_items_per_slide($slides_per_view, $hide = array(), $post_type = false, $location = false, $custom_sizes = false)
    {
        $items = woodmart_get_owl_items_numbers($slides_per_view, $post_type, $custom_sizes);
        $classes = '';

        if (woodmart_get_opt('thums_position') == 'centered' && $location == 'main-gallery') {
            $items['desktop'] = $items['tablet_landscape'] = $items['tablet'] = $items['mobile'] = 2;
        }

        if (!in_array('lg', $hide)) {
            $classes .= 'owl-items-lg-' . $items['desktop'];
        }
        if (!in_array('md', $hide)) {
            $classes .= ' owl-items-md-' . $items['tablet_landscape'];
        }
        if (!in_array('sm', $hide)) {
            $classes .= ' owl-items-sm-' . $items['tablet'];
        }
        if (!in_array('xs', $hide)) {
            $classes .= ' owl-items-xs-' . $items['mobile'];
        }

        return $classes;
    }
}
// **********************************************************************//
// Woodmart Get Owl Items Numbers
// **********************************************************************//
if (!function_exists('woodmart_get_owl_items_numbers')) {
    function woodmart_get_owl_items_numbers($slides_per_view, $post_type = false, $custom_sizes = false)
    {
        $items = woodmart_get_col_sizes($slides_per_view);

        if ($post_type == 'product') {
            if ('auto' !== woodmart_get_opt('products_columns_tablet') && !empty($mobile_columns)) {
                $items['tablet'] = woodmart_get_opt('products_columns_tablet');
            }

            $items['mobile'] = woodmart_get_opt('products_columns_mobile');
        }

        if ($items['desktop'] == 1) {
            $items['mobile'] = 1;
        }

        if ($custom_sizes && is_array($custom_sizes)) {
            $auto_columns = woodmart_get_col_sizes($custom_sizes['desktop']);

            if (empty($custom_sizes['tablet_landscape']) || 'auto' === $custom_sizes['tablet_landscape']) {
                $custom_sizes['tablet_landscape'] = $auto_columns['tablet_landscape'];
            }

            if (empty($custom_sizes['tablet']) || 'auto' === $custom_sizes['tablet']) {
                $custom_sizes['tablet'] = $auto_columns['tablet'];
            }

            if (empty($custom_sizes['mobile']) || 'auto' === $custom_sizes['mobile']) {
                $custom_sizes['mobile'] = $auto_columns['mobile'];
            }

            return $custom_sizes;
        }

        return $items;
    }
}


if (!function_exists('woodmart_get_grid_el_columns')) {
    function woodmart_get_grid_el_columns($columns)
    {
        if (empty($columns)) {
            return false;
        }

        if (is_array($columns) && isset($columns['size'])) {
            $columns = $columns['size'];
        }

        if (in_array($columns, array(5, 7, 8, 9, 10, 11))) {
            $columns = str_replace('.', '_', round(100 / $columns, 1));
            if (!strpos($columns, '_')) {
                $columns = $columns . '_0';
            }
        } else {
            $columns = 12 / $columns;
        }

        return $columns;
    }
}

if (!function_exists('woodmart_get_col_sizes')) {
    function woodmart_get_col_sizes($desktop_columns)
    {
        $sizes = array(
            '1' => array(
                'desktop' => '1',
                'tablet' => '1',
                'tablet_landscape' => '1',
                'mobile' => '1',
            ),
            '2' => array(
                'desktop' => '2',
                'tablet_landscape' => '2',
                'tablet' => '1',
                'mobile' => '1',
            ),
            '3' => array(
                'desktop' => '3',
                'tablet_landscape' => '3',
                'tablet' => '2',
                'mobile' => '1',
            ),
            '4' => array(
                'desktop' => '4',
                'tablet_landscape' => '4',
                'tablet' => '3',
                'mobile' => '1',
            ),
            '5' => array(
                'desktop' => '5',
                'tablet_landscape' => '4',
                'tablet' => '3',
                'mobile' => '2',
            ),
            '6' => array(
                'desktop' => '6',
                'tablet_landscape' => '4',
                'tablet' => '3',
                'mobile' => '2',
            ),
            '7' => array(
                'desktop' => '7',
                'tablet_landscape' => '4',
                'tablet' => '3',
                'mobile' => '2',
            ),
            '8' => array(
                'desktop' => '8',
                'tablet_landscape' => '4',
                'tablet' => '3',
                'mobile' => '2',
            ),
            '9' => array(
                'desktop' => '9',
                'tablet_landscape' => '4',
                'tablet' => '3',
                'mobile' => '2',
            ),
            '10' => array(
                'desktop' => '10',
                'tablet_landscape' => '4',
                'tablet' => '3',
                'mobile' => '2',
            ),
            '11' => array(
                'desktop' => '11',
                'tablet_landscape' => '4',
                'tablet' => '3',
                'mobile' => '2',
            ),
            '12' => array(
                'desktop' => '12',
                'tablet_landscape' => '4',
                'tablet' => '3',
                'mobile' => '2',
            ),
        );

        return isset($sizes[$desktop_columns]) ? $sizes[$desktop_columns] : $sizes['3'];
    }
}

if (!function_exists('woodmart_get_grid_el_class_new')) {
    function woodmart_get_grid_el_class_new($loop = 0, $different_sizes = false, $desktop_columns = 3,
                                            $tablet_columns = 4, $mobile_columns = 1)
    {
        $items_wide = woodmart_get_wide_items_array($different_sizes);
        $auto_columns = woodmart_get_col_sizes($desktop_columns);
        $classes = '';

        if ('auto' === $tablet_columns || empty($tablet_columns)) {
            $tablet_columns = $auto_columns['tablet_landscape'];
        }

        if ('auto' === $mobile_columns || empty($mobile_columns)) {
            $mobile_columns = $auto_columns['mobile'];
        }

        $desktop_columns_class = woodmart_get_grid_el_columns($desktop_columns);
        $tablet_columns_class = woodmart_get_grid_el_columns($tablet_columns);

        if ($different_sizes && (in_array($loop, $items_wide, true))) {
            $desktop_columns_class *= 2;
            $tablet_columns_class *= 2;
        }

        $sizes = array(
            array(
                'name' => 'col-lg',
                'value' => $desktop_columns_class,
            ),
            array(
                'name' => 'col-md',
                'value' => $tablet_columns_class,
            ),
            array(
                'name' => 'col',
                'value' => woodmart_get_grid_el_columns($mobile_columns),
            ),
        );

        foreach ($sizes as $value) {
            $classes .= ' ' . $value['name'] . '-' . $value['value'];
        }

        if ($loop > 0 && $desktop_columns > 0) {
            if (0 === ($loop - 1) % $desktop_columns || 1 === $desktop_columns) {
                $classes .= ' first ';
            }
            if (0 === $loop % $desktop_columns) {
                $classes .= ' last ';
            }
        }

        return $classes;
    }
}

/**
 * ------------------------------------------------------------------------------------------------
 * Function to prepare classes for grid element (column)
 * ------------------------------------------------------------------------------------------------
 */

if (!function_exists('woodmart_get_grid_el_class')) {
    function woodmart_get_grid_el_class($loop = 0, $columns = 4, $different_sizes = false, $xs_size = false, $sm_size = 4, $lg_size = 3, $md_size = false)
    {
        $classes = '';

        $items_wide = woodmart_get_wide_items_array($different_sizes);

        if (!$xs_size) {
            $xs_size = apply_filters('woodmart_grid_xs_default', 6);
        }

        if ($columns > 0) {
            $lg_size = 12 / $columns;
        }

        if (!$md_size) {
            $md_size = $lg_size;
        }

        if ($columns > 4) {
            $md_size = 3;
        }

        if ($columns <= 3) {
            if ($columns == 1) {
                $sm_size = 12;
                $xs_size = 12;
            } else {
                $sm_size = 6;
            }
        }

        // every third element make 2 times larger (for isotope grid)
        if ($different_sizes && (in_array($loop, $items_wide))) {
            $lg_size *= 2;
            $md_size *= 2;
        }

        if (in_array($columns, array(5, 7, 8, 9, 10, 11))) {
            $lg_size = str_replace('.', '_', round(100 / $columns, 1));
            if (!strpos($lg_size, '_')) {
                $lg_size = $lg_size . '_0';
            }
        }

        $sizes = array(
            array(
                'name' => 'col-lg',
                'value' => $lg_size,
            ),
            array(
                'name' => 'col-md',
                'value' => $md_size,
            ),
            array(
                'name' => 'col-sm',
                'value' => $sm_size,
            ),
            array(
                'name' => 'col',
                'value' => $xs_size,
            ),
        );

        foreach ($sizes as $value) {
            $classes .= ' ' . $value['name'] . '-' . $value['value'];
        }

        if ($loop > 0 && $columns > 0) {
            if (0 == ($loop - 1) % $columns || 1 == $columns) {
                $classes .= ' first ';
            }
            if (0 == $loop % $columns) {
                $classes .= ' last ';
            }
        }

        return $classes;
    }
}


if (!function_exists('woodmart_get_wide_items_array')) {
    function woodmart_get_wide_items_array($different_sizes = false)
    {
        $items_wide = apply_filters('woodmart_wide_items', array(5, 6, 7, 8, 13, 14));

        if (is_array($different_sizes)) {
            $items_wide = apply_filters('woodmart_wide_items', $different_sizes);
        }

        return $items_wide;
    }
}


// **********************************************************************//
// Woodmart Get Settings JS
// **********************************************************************//
if (!function_exists('woodmart_settings_js')) {
    function woodmart_settings_js()
    {

        $custom_js = woodmart_get_opt('custom_js');
        $js_ready = woodmart_get_opt('js_ready');

        ob_start();

        if (!empty($custom_js) || !empty($js_ready)): ?>
            <?php if (!empty($custom_js)): ?>
                <?php echo woodmart_get_opt('custom_js'); ?>
            <?php endif; ?>
            <?php if (!empty($js_ready)): ?>
                jQuery(document).ready(function() {
                <?php echo woodmart_get_opt('js_ready'); ?>
                });
            <?php endif; ?>
        <?php endif;

        return ob_get_clean();
    }
}

// **********************************************************************//
// Header classes
// **********************************************************************//
if (!function_exists('woodmart_get_header_classes')) {
    function woodmart_get_header_classes()
    {
        $settings = whb_get_settings();
        $custom_product_header = woodmart_get_opt('single_product_header');

        $header_class = 'whb-header';
        $header_class .= ' whb-' . WOODMART_HB_Frontend::get_instance()->get_current_id();
        $header_class .= ($settings['overlap']) ? ' whb-overcontent' : '';
        $header_class .= ($settings['overlap'] && $settings['boxed']) ? ' whb-boxed' : '';
        $header_class .= ($settings['full_width']) ? ' whb-full-width' : '';
        $header_class .= ($settings['sticky_shadow']) ? ' whb-sticky-shadow' : '';
        $header_class .= ($settings['sticky_effect']) ? ' whb-scroll-' . $settings['sticky_effect'] : '';
        $header_class .= ($settings['sticky_clone'] && $settings['sticky_effect'] == 'slide') ? ' whb-sticky-clone' : ' whb-sticky-real';
        $header_class .= ($settings['hide_on_scroll']) ? ' whb-hide-on-scroll' : '';

        woodmart_enqueue_js_script('header-builder');

        if (!empty($custom_product_header) && $custom_product_header != 'none' && woodmart_woocommerce_installed() && is_product()) {
            $header_class .= ' whb-custom-header';
        }

        echo 'class="' . esc_attr($header_class) . '"';
    }
}

// **********************************************************************//
// Print script tag with content
// **********************************************************************//
if (!function_exists('woodmart_add_inline_script')) {
    function woodmart_add_inline_script($key, $content, $position = 'after')
    {
        ?>
        <script>
            <?php echo apply_filters('woodmart_inline_script', $content); ?>
        </script>
        <?php
    }
}

// **********************************************************************//
// Print text size css
// **********************************************************************//
if (!function_exists('woodmart_responsive_text_size_css')) {
    function woodmart_responsive_text_size_css($id, $class, $data, $action = 'echo')
    {
        if ('return' == $action) {
            return '#' . $id . ' .' . $class . '{font-size:' . $data . 'px;line-height:' . intval($data + 10) . 'px;}';
        } else {
            echo '#' . $id . ' .' . $class . '{font-size:' . $data . 'px;line-height:' . intval($data + 10) . 'px;}';
        }
    }
}

if (!function_exists('woodmart_custom_404_page')) {
    /**
     * Function to set custom 404 page.
     *
     * @param $template
     *
     * @return mixed|string
     */
    function woodmart_custom_404_page($template)
    {
        global $wp_query;
        $custom_404 = woodmart_get_opt('custom_404_page');

        if ($custom_404 === 'default' || empty($custom_404)) {
            return $template;
        }

        $wp_query->query('page_id=' . $custom_404);
        $wp_query->the_post();
        $template = get_page_template();
        rewind_posts();

        return $template;
    }

    add_filter('404_template', 'woodmart_custom_404_page', 999);
}

if (!function_exists('woodmart_android_browser_bar_color')) {
    /**
     * Display cart widget side
     *
     * @since 1.0.0
     */
    function woodmart_android_browser_bar_color()
    {
        $color = woodmart_get_opt('android_browser_bar_color');

        if (!empty($color['idle'])) {
            echo '<meta name="theme-color" content="' . $color['idle'] . '">';
        }
    }

    add_filter('wp_head', 'woodmart_android_browser_bar_color');
}

if (!function_exists('woodmart_settings_css')) {
    function woodmart_settings_css()
    {
        $custom_product_background = get_post_meta(get_the_ID(), '_woodmart_product-background', true);

        ob_start();

        echo '<style>';

        ?>

        <?php if (!empty($custom_product_background)): ?>
        .single-product .main-page-wrapper{
        background-color: <?php echo esc_html($custom_product_background); ?> !important;
        }
    <?php endif ?>

        <?php

        echo '</style>';

        echo ob_get_clean();
    }

    add_action('wp_head', 'woodmart_settings_css', 200);
}

if (!function_exists('woodmart_remove_jquery_migrate')) {
    /**
     * Remove JQuery migrate.
     *
     * @param WP_Scripts $scripts wp script object.
     */
    function woodmart_remove_jquery_migrate($scripts)
    {
        if (!is_admin() && isset($scripts->registered['jquery']) && woodmart_get_opt('remove_jquery_migrate', false)) {
            $script = $scripts->registered['jquery'];
            if ($script->deps) {
                $script->deps = array_diff($script->deps, array('jquery-migrate'));
            }
        }
    }

    add_action('wp_default_scripts', 'woodmart_remove_jquery_migrate');
}

if (!function_exists('curr_product_cat_html')) {
    function curr_product_cat_html()
    {
        if (is_product_category()) {
            global $wp_query;
            $term_id = $wp_query->get_queried_object()->term_id;
            $content = wpautop(get_term_meta($term_id, 'category_content_text', true));
            if (empty($content) && !empty($wp_query->get_queried_object()->parent)) {
                $term_id = $wp_query->get_queried_object()->parent;
                $content = wpautop(get_term_meta($term_id, 'category_content_text', true));
            }
            if (!empty($content)) {
                echo ' <div class="children_cat_panel">
                            <div class="top-info__title">
                                <h1 class="top-info__title-name">';
                woocommerce_page_title();
                echo '</h1></div>' . $content . '</div>';
            }
        }
    }

    ;
}


if (!function_exists('woocommerce_delivery_time_html')) {
    function woocommerce_delivery_time_html()
    {
        $sleect_html = get_country_list();
        $curr_delivery_time = get_curr_delivery_time();
        echo <<< EOT
       <div class="tips_cls">
        <a class="wd-open-popup popup_delivery_time" href="#popup_delivery_time" data-country-code="{$curr_delivery_time['country_code']}" data-lang="{$curr_delivery_time['lang']}">
            <div class="tips_content">
                <div class="tips_cls1">
                    <svg t="1703509939479" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4383" width="25" height="25"><path d="M770.24115 684.200623c-47.547529 0-86.080383 38.54103-86.080383 86.0998 0 47.557749 38.531832 86.098778 86.080383 86.098778 47.546507 0 86.079361-38.54103 86.079361-86.098778C856.319489 722.740631 817.787657 684.199601 770.24115 684.200623z" fill="#262536" p-id="4384"></path><path d="M933.255409 476.888655 856.319489 339.800399c-15.735058-18.968527-26.360335-34.43992-51.647617-34.43992L684.159745 305.360479 684.159745 202.039697c0-18.968527-15.467305-34.438898-34.431745-34.438898L98.814978 167.600798C79.850539 167.600798 64.383234 183.07117 64.383234 202.039697l0 516.598802c0 18.970571 15.467305 34.440942 34.431745 34.440942l68.661142 0C175.919521 693.748758 226.558467 647.81081 288.191617 647.81081S400.464735 693.748758 408.907114 753.079441l240.617517-0.034747c8.47406-59.325573 59.113006-105.233884 120.716519-105.233884 61.634172 0 112.272096 45.93897 120.715497 105.268631l34.229397 0C944.149461 753.079441 959.616766 737.60907 959.616766 718.639521c0 0 0-139.573653 0-172.199601C959.615745 513.814994 933.255409 476.888655 933.255409 476.888655zM718.592511 546.43992 718.592511 374.240319l84.229621 0c5.547178 0 13.249661 8.138858 13.249661 8.138858l72.493477 134.801118c6.42095 9.450028 13.383537 20.650667 13.383537 29.259625L718.592511 546.43992z" fill="#262536" p-id="4385"></path><path d="M288.191617 684.200623c-47.547529 0-86.080383 38.54103-86.080383 86.0998C202.112255 817.858172 240.64511 856.399202 288.191617 856.399202s86.080383-38.54103 86.080383-86.098778C374.272 722.740631 335.738124 684.199601 288.191617 684.200623z" fill="#262536" p-id="4386"></path></svg>
                    <span class="tips_title_1">Shipping to <span class="country_name">{$curr_delivery_time['country']}</span></span>
                   <svg viewBox="0 0 20 20" class="icon" fill="none" xmlns="http://www.w3.org/2000/svg" width="20" height="20"><circle cx="10" cy="10" r="7.708" stroke="#A1A5AB" stroke-width="1.25"></circle><path d="M10.15 5.977v-.625.625ZM8.4 8.233c0-.378.108-.789.358-1.093.232-.281.64-.538 1.392-.538v-1.25c-1.078 0-1.859.389-2.357.994-.48.584-.643 1.301-.643 1.887H8.4Zm1.75-1.631c.726 0 1.183.421 1.365.946.191.552.058 1.14-.345 1.472l.794.965c.883-.725 1.06-1.9.732-2.846-.338-.974-1.232-1.787-2.546-1.787v1.25Zm1.02 2.418c-1.316 1.082-1.845 1.887-1.845 2.814h1.25c0-.336.136-.818 1.39-1.849l-.795-.965Z" fill="#A1A5AB"></path><circle cx="10" cy="13.558" r="1.026" fill="#A1A5AB"></circle></svg>
            </div>
        </a>
        <div class="tips_content2">
            <div class="swiper-container swiper-container-initialized swiper-container-horizontal swiper-container-pointer-events">
                <div class="swiper-wrapper" style="transition-duration: 0ms; transform: translate3d(0px, 0px, 0px);">
                    <div class="swiper-slide swiper-slide-active" style="margin-right: 8px;">
                        <div>
                            <div class="delivery_time_f" style="font-size:12px"><span>Estimated Time of Arrival<!-- -->:</span><span class="s_e_delivery_time">{$curr_delivery_time['start_delivery_time']} - {$curr_delivery_time['end_delivery_time']}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="ProductTransitonModal mfp-with-anim wd-popup wd-popup-element mfp-hide" id="popup_delivery_time">
    <div class="ShippingInfoModal">
        <div class="title">Shipping Info</div>
        <div>
            <div>
                <div>
                    <span class="ShippingTo">Shipping To:</span>
                    <select class="FilterSelect">
                        {$sleect_html}
                    </select>
                </div>
                
                <div class="ShippingTableContainer">
                    <div>
                        <div>
                            <table class="ShippingTable" style="width: 100%;">
                                <thead>
                                <tr>
                                    <td>Shipping Method</td>
                                    <td>Shipping Time</td>
                                    <td>Costs</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Standard Shipping</td>
                                    <td><span class="delivery_time">{$curr_delivery_time['date_min']}-{$curr_delivery_time['date_max']}</span> business days</td>
                                    <td><span class="delivery_time_costs">≥{$curr_delivery_time['costs']}</span></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bottom_b_s">Receiving time = Processing time + Shipping Time</div>
    </div>
</div>
<style>
    .ShippingTo {
        align-items: center;
        justify-content: space-between;
        padding: 0px 15px;
        height: 40px;
        display: inline;
    }
        .LeftContainer{
            & > *{
                vertical-align: middle;
            }

            .CartIcon{
                font-size: 16px;
                color: #222;
                margin-right: 7px;
            }
        }

        .RightContainer{
            & > *{
                vertical-align: middle;
                font-size: 18px;
                color: #222222;
                cursor: pointer;
            } }

    .ShippingTableContainer {
        margin-top: 20px;

    }

    .ShippingTable {
        font-size: 14px;
        border-collapse: collapse;
        border-spacing: 0;
    }

    .ShippingTable thead {
        background-color: #f5f5f5;
    }

    .ShippingTable thead td {
        font-weight: bold;
        color: #222;

    }

    .ShippingTable tr:hover {
        background-color: #f5f5f5;

    }

    .ShippingTable td {
        border: 1px solid #dbdbdb;
        text-align: center;
        padding: 10px 32px;
        color:#000;
    }

    .ShippingTo {
        font-weight: bold;
        font-size: 18px;
    }

    .FilterSelect {
        border: none;
        appearance: none;
        -moz-appearance: none;
        -webkit-appearance: none;
        background-position: right;
        background: url("https://d7bimqy5wbg0.cloudfront.net/web/asset/common/down.png") no-repeat scroll calc(100% - 10px) center transparent;
        padding-right: 14px;
        padding-left: 5px;
        border-radius: 2px;
        box-shadow: none;
        cursor: pointer;
        font-size: 14px;
        height: 23px;
        width: 150px;
        border: 1px solid #000;
        color: #000;
        background-color: #fff;
    }

    .ProductTransitonModal {
        max-width: 560px;
        transform: translate(-50%, -50%);
        z-index: 11;
    }
    .mfp-with-anim {
        position: relative;
    }
    .ShippingInfoModal .title {
        font-family: 'Roboto-Medium';
        font-size: 24px;
        color: #222;
        text-align: center;
        margin-bottom:30px;
    }
    .tips_title_1{
        vertical-align: top;
         line-height: 1px;
         padding-left: 10px;
         height: 20px;
         padding-right: 5px;
         display: block;
         float: left;
    }
    .bottom_b_s{ margin-top:30px;color:#000;}
    
    .tips_cls1 svg{
        float:left;
    }
    .tips_cls{
        
    background: #f7f7f7;
    padding: 20px 0px;
    padding-left: 20px;
    border-radius: var(--wd-brd-radius);
    padding-top:34px;
    }
    .delivery_time_f{
    font-size: 12px;
    background: #fff;
    margin-top: 13px;
    padding-top: 10px;
    width: 93%;
    padding-bottom: 10px;
    padding-left: 10px;
    color: #000;
    }
    @media (min-width: 768px) {
    .mfp-with-anim::after {
        content: "x";
        position: absolute;
        top: -20px;
        right: -20px;
        font-size: 20px;
        width: 20px;
        height: 20px;
        color: white;
        text-align: center;
        line-height: 20px;
        cursor: pointer;
    }}
</style>
EOT;
        woodmart_enqueue_js_script('product-delivery-time');
    }
}
add_action('woocommerce_delivery_time', 'woocommerce_delivery_time_html');

function get_delivery_time_select($arr)
{
    $country_list = array_keys($arr);
    asort($country_list);
    // 初始化一个变量用于存储生成的HTML
    $html = '';
    // 遍历排序后的数组，生成HTML
    $currentLetter = '';
    foreach ($country_list as $country) {
        $firstLetter = strtoupper(substr($country, 0, 1));
        // 如果首字母不同，则开始一个新的<optgroup>
        if ($firstLetter != $currentLetter) {
            // 如果不是第一次循环，关闭前一个<optgroup>
            if ($currentLetter != '') {
                $html .= '</optgroup>';
            }
            // 开始新的<optgroup>
            $html .= '<optgroup label="' . $firstLetter . '">';
            $currentLetter = $firstLetter;
        }
        // 添加<option>标签
        $html .= '<option value="' . $arr[$country] . '">' . $country . "</option>\r\n";
    }
    // 关闭最后一个<optgroup>
    $html .= "</optgroup>\r\n";
    // 输出生成的HTML
    return $html;

}

function get_country_list()
{
    $language = isset($_GET['language']) ? $_GET['language'] : 'en';
    $lang = sanitize_text_field($language); //phpcs:ignore
    global $wpdb;

    // $select_html = wp_cache_get('orca_shop', 'country_list');

    //if(!$select_html){
    $sql = "SELECT country_code,country FROM `wp_delivery_time` where lang='{$lang}'";
    $result = array_values(json_decode(json_encode($wpdb->get_results($sql), true), true));
    $new_country_list = [];
    foreach ($result as $value) {
        $new_country_list[$value['country']] = $value['country_code'];
    };
    $select_html = get_delivery_time_select($new_country_list);

    // wp_cache_set('orca_shop',$select_html,'country_list');
    //}
    return $select_html;
}

function get_curr_delivery_time()
{
    $country_code = isset($_COOKIE['orca_shop_country_code']) ? $_COOKIE['orca_shop_country_code'] : 'US';
    $language = isset($_GET['language']) ? $_GET['language'] : 'en';
    $sql = "SELECT * FROM `wp_delivery_time` where lang='{$language}' and country_code='{$country_code}'";
    global $wpdb;
    $result = $wpdb->get_row($sql);
    $result = json_decode(json_encode($result, true), true);
    if (!empty($result)) {
        $result['start_delivery_time'] = Date('Y/m/d', time() + ($result['date_min'] * 24 * 60 * 60));
        $result['end_delivery_time'] = Date('Y/m/d', time() + ($result['date_max'] * 24 * 60 * 60));
    }
    return $result;
}

function get_delivery_time()
{

    if (!isset($_POST['country_code']) || !$_POST['country_code']) { //phpcs:ignore
        return;
    }
    $language = isset($_POST['language']) ? $_POST['language'] : 'en';
    $lang = sanitize_text_field($language); //phpcs:ignore
    $country_code = sanitize_text_field($_POST['country_code']); //phpcs:ignore
    $sql = "SELECT * FROM `wp_delivery_time` where lang='{$lang}' and country_code='{$country_code}'";
    global $wpdb;
    $result = $wpdb->get_row($sql);
    $result = json_decode(json_encode($result, true), true);
    if (!empty($result)) {
        $result['start_delivery_time'] = Date('Y/m/d', time() + ($result['date_min'] * 24 * 60 * 60));
        $result['end_delivery_time'] = Date('Y/m/d', time() + ($result['date_max'] * 24 * 60 * 60));
        setcookie('orca_shop_country_code', $country_code, 0, '/');
        $data = array(
            'status' => 200,
            'data' => $result,
        );
    } else {
        $data = array(
            'status' => -1,
            'data' => null,
        );
    }
    wp_send_json($data);
}

add_action('wp_ajax_woodmart_get_delivery_time', 'get_delivery_time');
add_action('wp_ajax_nopriv_woodmart_get_delivery_time', 'get_delivery_time');

function delay_the_delivery()
{
    $product_id=get_the_ID();
    $the_delivery_status=get_post_meta($product_id, '_woodmart_delay_the_delivery_status', true);
    $the_delivery_block_id= get_post_meta($product_id, '_woodmart_delay_the_delivery_block', true);
    $delay_the_delivery_tips=get_post_meta($product_id, '_woodmart_delay_the_delivery_tips', true);
    $delay_the_delivery_title=get_post_meta($product_id, '_woodmart_delay_the_delivery_title', true);
    if($the_delivery_status !=='on'){
        return;
    }

    echo '<div class="flex flex-col justify-start items-start self-stretch flex-grow-0 flex-shrink-0 gap-1 empty:hidden">
                <div class="flex items-center justify-between w-full" style="
    margin-top: 26px;
">
                        <div class="flex justify-start items-center flex-grow-0 flex-shrink-0 relative gap-4">
                            <div class="max-w-full flex relative justify-start items-center" style="
    display: inline-block;
    background: #FEE6EC;
    color: red;
    font-weight: normal;
    border-radius: 2px;
    float: left;
    margin-right: 9px;
">
                                <div class="max-w-full flex items-center">
                                    <strong title="Pre-sale items will be shipped separately" style="
    font-weight: 400;
    padding: 6px;
    font-size: 15px;
    color: #E64545;
    min-height: 24px;
">'.$delay_the_delivery_tips.'</strong>
                                </div>
                            </div>
                            <a href="#pop_delivery_block_'.$the_delivery_block_id.'" class="wd-open-popup">
                                <span class="cursor-pointer" style="
        display: inline-block;
    ">
                                    <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" width="20" height="20" style="
        margin-top: 2px;
    "><circle cx="10" cy="10" r="7.708" stroke="#A1A5AB" stroke-width="1.25"></circle><path d="M10.15 5.977v-.625.625ZM8.4 8.233c0-.378.108-.789.358-1.093.232-.281.64-.538 1.392-.538v-1.25c-1.078 0-1.859.389-2.357.994-.48.584-.643 1.301-.643 1.887H8.4Zm1.75-1.631c.726 0 1.183.421 1.365.946.191.552.058 1.14-.345 1.472l.794.965c.883-.725 1.06-1.9.732-2.846-.338-.974-1.232-1.787-2.546-1.787v1.25Zm1.02 2.418c-1.316 1.082-1.845 1.887-1.845 2.814h1.25c0-.336.136-.818 1.39-1.849l-.795-.965Z" fill="#A1A5AB"></path><circle cx="10" cy="13.558" r="1.026" fill="#A1A5AB"></circle></svg>
                                </span>
                            </a>
                        </div>
                </div>
                <div class="flex justify-start items-center self-stretch flex-grow-0 flex-shrink-0 relative gap-2.5 empty:hidden" style="
    background: #fff;
    padding: 5px 0;
    margin-top: 5px;
">
                    <div class="flex-grow truncate cm-activity-text leading-none" title="'.$delay_the_delivery_title.'" style="
    color: #000;
    display: inline;
">'.$delay_the_delivery_title.'
                    </div>
                </div>
         </div>';

    echo '<div id="pop_delivery_block_'.$the_delivery_block_id.'" class="mfp-with-anim wd-popup wd-popup-element mfp-hide" style="max-width:800px;"><div class="wd-popup-inner">'.woodmart_get_html_block( $the_delivery_block_id ).'</div></div>';

}

function size_guide(){
    $product_id=get_the_ID();
    $woodmart_setting_size_guide_img=get_post_meta($product_id, '_woodmart_setting_size_guide_img', true);
    if(empty($woodmart_setting_size_guide_img)){
        return;
    }
    echo <<< EOT
       <div class="size_guide"  style="display: inline-block">
        <a class="wd-open-popup popup_size_guide" href="#popup_size_guide">
            <div>
                    <svg style="display:inline-block;float: left;width:40px" t="1703750886420" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6046" width="48" height="48"><path d="M260.62559901 543.42179956c-17.28198956 0-31.42179956-14.13981003-31.42179958-31.42179956v-125.68719975c0-17.28198956 14.13981003-31.42179956 31.42179958-31.42180105s31.42179956 14.13981003 31.42180105 31.42180105v125.68719975c0 17.28198956-14.13981003 31.42179956-31.42180105 31.42179956zM609.09336219 543.42179956c-17.28198956 0-31.42179956-14.13981003-31.42179957-31.42179956v-125.68719975c0-17.28198956 14.13981003-31.42179956 31.42179957-31.42180105s31.42179956 14.13981003 31.42179958 31.42180105v125.68719975c0 17.28198956-13.82559251 31.42179956-31.42179958 31.42179956zM435.01658935 480.57820044c-17.28198956 0-31.42179956-14.13981003-31.42179958-31.42180105v-62.84359914c0-17.28198956 14.13981003-31.42179956 31.42179958-31.42180105s31.42179956 14.13981003 31.42180106 31.42180105v62.84359914c0 17.28198956-14.13981003 31.42179956-31.42180106 31.42180105zM783.48435253 480.57820044c-17.28198956 0-31.42179956-14.13981003-31.42179956-31.42180105v-62.84359914c0-17.28198956 14.13981003-31.42179956 31.42179956-31.42180105s31.42179956 14.13981003 31.42179958 31.42180105v62.84359914c0 17.28198956-14.13981003 31.42179956-31.42179958 31.42180105z" fill="#2c2c2c" p-id="6047"></path><path d="M920.48340033 669.1090008H103.51659967c-17.28198956 0-31.42179956-14.13981003-31.42180102-31.42180105v-251.3743995c0-17.28198956 14.13981003-31.42179956 31.42180102-31.42180105h816.96680066c17.28198956 0 31.42179956 14.13981003 31.42180102 31.42180105v251.3743995c0 17.28198956-14.13981003 31.42179956-31.42180102 31.42180105z m-785.54500108-62.84360061h754.1232015v-188.53080038H134.93839925v188.53080038z" fill="#2c2c2c" p-id="6048"></path></svg>
                    <span style="margin-left: 9px;" >Size Guide?</span>
            </div>
        </a>
    </div>

<div class="mfp-with-anim wd-popup wd-popup-element mfp-hide" id="popup_size_guide" style="max-width:800px">
    <div class="ShippingInfoModal">
        <div class="title">Size Chart</div>
        <div>
           <img src="{$woodmart_setting_size_guide_img}" style="text-align: center;" />
        </div>
    </div>
</div>
EOT;
}

add_action('woocommerce_delay_the_delivery', 'delay_the_delivery');
add_action('woocommerce_size_guide','size_guide');