<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Wt_Advanced_Order_Number {

    protected $loader;
    protected $plugin_name;
    protected $version;
    protected $plugin_base_name;
    public $plugin_common = null;

    public function __construct() {
        if (defined('WT_SEQUENCIAL_ORDNUMBER_VERSION')) {
            $this->version = WT_SEQUENCIAL_ORDNUMBER_VERSION;
        } else {
            $this->version = '1.6.3';
        }
        $this->plugin_name = 'wt-advanced-order-number';
        $this->plugin_base_name = WT_SEQUENCIAL_ORDNUMBER_BASE_NAME;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

        if ( is_admin() ) {
            $settings_pages_priority = PHP_INT_MAX;
            $settings_pages_priority = apply_filters('wt_alter_get_settings_pages_priority',$settings_pages_priority);
            add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab'), $settings_pages_priority);
        }
    }

    private function load_dependencies() {

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wt-advanced-order-number-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wt-advanced-order-number-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wt-advanced-order-number-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wt-advanced-order-number-public.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wt-advanced-order-number-review_request.php';
        //add other solutions section
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wt-advanced-order-number-other-solution.php';
        
        /**
		 * The class responsible for defining all actions that occur in common for public and admin -facing
		 * side of the site.
		 */ 
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/class-wt-advanced-order-number_common.php';

        $this->loader = new Wt_Advanced_Order_Number_Loader();
        $this->plugin_common = new Wt_Advanced_Order_Number_Common($this->get_plugin_name(), $this->get_version());

    }

    private function set_locale() {

        $plugin_i18n = new Wt_Advanced_Order_Number_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

        /**
         * @since   1.5.4
         * Added new filter 'woocommerce_order_table_search_query_meta_keys' to fix issue in searching order number while HPOS is enabled and WooCommerce orders tables is being used.
         */ 

    private function define_admin_hooks() {

        $plugin_admin = new Wt_Advanced_Order_Number_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_filter('plugin_action_links_' . $this->get_plugin_base_name(), $plugin_admin, 'add_plugin_links_wt_wtsequentialordnum');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_footer', $plugin_admin, 'add_settings_page_popup');
        if ( 'yes' === get_option( 'wt_custom_order_number_search', 'yes' ) ) {
            $this->loader->add_filter('woocommerce_shop_order_search_fields', $plugin_admin, 'custom_ordernumber_search_field');

            // ensure that admin order table search by order number works.
            $this->loader->add_filter('woocommerce_order_table_search_query_meta_keys', $plugin_admin, 'custom_ordernumber_search_field');
        }

        add_action('plugins_loaded', array($this, 'setup_sequential_number'));    
    }

    public function define_common_hooks() {
        $this->plugin_common= Wt_Advanced_Order_Number_Common::get_instance( $this->get_plugin_name(), $this->get_version() );
    }

    public function add_woocommerce_settings_tab( $settings ) {
        if ( is_object( $settings ) ) {
            $settings = (array) $settings;
        }
        $settings[] = include plugin_dir_path( __FILE__ ) .'class-wt-advanced-order-number-settings.php';
        return $settings;
    }

     /**
     * Added new filter 'wt_sequential_reset_start_number'.
     * @since   1.4.4
     */

    public static function save_settings() {

        $new_start_num = get_option('wt_sequence_order_number_start');
        $last_start_num = get_option('wt_last_sequence_start');
        $prefix = sanitize_text_field(get_option('wt_sequence_order_number_prefix', ''));
        $last_prefix = get_option('wt_last_prefix');
        $date_prefix = get_option('wt_sequence_order_date_prefix');
        $last_date_prefix = get_option('wt_last_date_prefix');
        $wt_renumerate = get_option('wt_renumerate','no');
        $order_no_format = get_option('wt_sequence_order_number_format');
        $last_order_no_format = get_option('wt_sequence_last_order_number_format');
        $order_no_length = get_option('wt_sequence_order_number_padding');
        $last_order_no_length = get_option('wt_sequence_last_order_number_padding');
        $reset_start_num = apply_filters('wt_sequential_reset_start_number',false);
        if( 'yes' === $wt_renumerate  || $new_start_num !== $last_start_num || TRUE === $reset_start_num || $prefix !== $last_prefix || $date_prefix !== $last_date_prefix || $order_no_format !== $last_order_no_format || $order_no_length !== $last_order_no_length)
        {
            self::initial_setup(TRUE);
        }
    }

     /**
     * Replaced the wcs_subscription_meta hook with wcs_subscription_meta_query to avoid a Array to string conversion PHP warning.
     * @since   1.4.4
     * Added new filter 'wt_sequential_alter_shop_order_meta_priority'.
     * @since   1.5.2
     */

    public function setup_sequential_number() {

        //add order number as a search option in order listing page .
        add_action('woocommerce_order_list_table_prepare_items_query_args', array($this, 'wt_order_list_table_prepare_items_query_args'));

        //add_action('wp_insert_post', array($this, 'set_sequential_number'), 10, 2);
        //add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'set_sequential_number' ), 10, 1 );
        add_action( 'woocommerce_new_order', array( $this, 'set_sequential_number' ), 1, 1 );
        
        $manual_order_priority = 35;
        $manual_order_priority = apply_filters('wt_sequential_alter_shop_order_meta_priority',$manual_order_priority);
        //sets sequential order number when order created through admin
        add_action( 'woocommerce_process_shop_order_meta',    array( $this, 'set_sequential_number' ), $manual_order_priority, 2 );
        add_action( 'woocommerce_before_resend_order_emails', array( $this, 'set_sequential_number' ), 10, 1 );
    
        // sets sequential order number when the order status is changed from draft.
        add_action('woocommerce_order_status_changed', array( $this, 'wt_seq_order_status_changed' ), 10, 4);

        //REST API
        add_action( 'woocommerce_api_create_order', array( $this, 'set_sequential_number' ), 10, 1 );

        // Show sequential order number
        add_filter('woocommerce_order_number', array($this, 'display_sequence_number'), PHP_INT_MAX, 2);

        // WooCommerce QuickPay support
        if(in_array( 'woocommerce-quickpay/woocommerce-quickpay.php',apply_filters('active_plugins',get_option('active_plugins'))) || array_key_exists( 'woocommerce-quickpay/woocommerce-quickpay.php', apply_filters( 'active_plugins', get_site_option( 'active_sitewide_plugins', array() ) ) )) 
        {
            add_filter('woocommerce_quickpay_order_number_for_api', array( $this, 'wt_quickpay_order_number_for_api' ), 100, 3);
        }
        // WooCommerce Amazon Pay support
        if(in_array( 'woocommerce-gateway-amazon-payments-advanced/woocommerce-gateway-amazon-payments-advanced.php',apply_filters('active_plugins',get_option('active_plugins'))) || array_key_exists( 'woocommerce-gateway-amazon-payments-advanced/woocommerce-gateway-amazon-payments-advanced.php', apply_filters( 'active_plugins', get_site_option( 'active_sitewide_plugins', array() ) ) )) 
        {
            add_filter( 'woocommerce_amazon_pa_merchant_metadata_reference_id', array( $this, 'wt_amazon_pay_order_number'), 10, 1 );
            add_filter( 'woocommerce_amazon_pa_merchant_metadata_reference_id_reverse',array( $this, 'wt_order_id_from_order_number'), 10, 1 );
        }

        // WC Subscriptions support
            add_filter( 'wcs_subscription_meta_query', array( $this, 'subscriptions_remove_renewal_order_number_meta' ) );
            add_filter( 'wcs_renewal_order_created', array( $this, 'subscriptions_sequential_order_number' ), 10, 2 );
            $subscription_version = class_exists( 'WC_Subscriptions' ) && ! empty( WC_Subscriptions::$version ) ? WC_Subscriptions::$version : null;

            // Prevent data being copied to subscriptions
            if ( null !== $subscription_version && version_compare( $subscription_version, '2.5.0', '>=' ) ) {
                add_filter( 'wc_subscriptions_renewal_order_data', array( $this, 'wt_seq_remove_renewal_order_meta'), 10 );
            } else {
                add_filter( 'wcs_renewal_order_meta_query', array( $this, 'subscriptions_remove_renewal_order_number_meta' ), 10 );
            }

        // Webtoffee Subscriptions support
        add_filter( 'hf_subscription_meta_query', array( $this, 'subscriptions_remove_renewal_order_number_meta' ) );
        add_filter( 'hf_renewal_order_meta_query', array( $this, 'subscriptions_remove_renewal_order_number_meta' ) );
        add_filter( 'hf_renewal_order_created',    array( $this, 'subscriptions_sequential_order_number' ), 10, 2 );

        // [woocommerce_order_tracking] shortcode support
        if ( get_option( 'wt_custom_order_number_tracking_enabled', 'yes' ) === 'yes' ) {
            add_action( 'init', array( $this, 'remove_order_id_tracking_filter' ) );
            add_filter( 'woocommerce_shortcode_order_tracking_order_id', array( $this,'wt_order_id_from_order_number'), 10, 1 );
        }
        if (is_admin() && (!defined('DOING_AJAX'))) {
           // self::initial_setup();
        }
    }

    public static function get_sequence_prefix($order_id) {

        $prefix = sanitize_text_field(get_option('wt_sequence_order_number_prefix', ''));
        $prefix = apply_filters('wt_order_number_sequence_prefix', $prefix,$order_id);
        update_option('wt_last_prefix',$prefix);
        return $prefix;
    }

    public static function initial_setup($rerun = FALSE) {


        $wt_advanced_order_number_version = get_option('wt_advanced_order_number_version');

        $wt_renumerate = get_option('wt_renumerate','no');

        if ( ( !$wt_advanced_order_number_version || $rerun === TRUE ) && $wt_renumerate === 'yes') {

            $offset = (int) get_option('wt_advanced_order_number_offset', 0);

            $start = get_option('wt_sequence_order_number_start', 1) ? (int) get_option('wt_sequence_order_number_start', 1) : 1;

            $posts_per_page = 50;

            do {

                $order_ids = Wt_Advanced_Order_Number_Common::get_orders(array(
                    'post_type' => 'shop_order', 
                    'offset' => $offset,
                    'posts_per_page' => $posts_per_page, 
                    'post_status' => 'any', 
                    'orderby' => 'date', 
                    'order' => 'ASC'));

                if (!empty($order_ids)) {
                    foreach ($order_ids as $order_id) {
                        $order = wc_get_order( $order_id );
                        $order_status = $order ? $order->get_status() : '';
                        
                        $is_draft = self::wt_seq_is_draft_order( $order_status );
                        $is_draft= apply_filters('wt_sequential_is_draft_order',$is_draft,$order);
                        // If order is a draft order skip sequential order number generation.
                        if( true === $is_draft ) {
                            continue ;
                        } else {
                            if (Wt_Advanced_Order_Number_Common::get_order_meta($order_id, '_order_number') === '' || $rerun === TRUE) {
                                $prefix = self::get_sequence_prefix($order_id);
                                $start_no_padding=self::add_order_no_padding($start);
                                $order_number = self::add_prefix_suffix($start_no_padding,$order_id);
                                $order_number = apply_filters('wt_order_number_sequence_data', $order_number, $prefix, $order_id);
                                Wt_Advanced_Order_Number_Common::update_order_meta($order_id, '_order_number', $order_number);
                                $start++;
                            }
                        }
                    }
                }

                $offset += $posts_per_page;
            } while (count($order_ids) === $posts_per_page);


            update_option('wt_advanced_order_number_version', WT_SEQUENCIAL_ORDNUMBER_VERSION);
            update_option('wt_last_order_number', $start - 1);
        } else {
            update_option('wt_advanced_order_number_version', WT_SEQUENCIAL_ORDNUMBER_VERSION);
            $start=get_option('wt_sequence_order_number_start', 1);
            update_option('wt_last_order_number', !empty($start) ? $start-1 : 0);
        }
        update_option('wt_last_sequence_start', get_option('wt_sequence_order_number_start', 1));
    }

    /**
     * Sets an order number on a subscriptions-created order.
     *
     * @since 1.2.5
     *
     * @param $renewal_order the new renewal order object
     * @param $subscription Post ID of a 'shop_subscription' post, or instance of a WC_Subscription object or HF_Woocommerce_Subscription
     * @return \WC_Order renewal order instance
     */

    public function subscriptions_sequential_order_number( $renewal_order, $subscription ) {

        if ( $renewal_order instanceof WC_Order ) {

            $this->set_sequential_number( $renewal_order->get_id() );
        }

        return $renewal_order;
    }

    /**
     * Don't copy over order number meta when creating a parent or child renewal order
     *
     * Prevents unnecessary order meta from polluting parent renewal orders,
     * and set order number for subscription orders
     *
     * @since 1.2.5
     * @param array $order_meta_query query for pulling the metadata
     * @return string
     */

    public function subscriptions_remove_renewal_order_number_meta( $order_meta_query ) {
        return $order_meta_query . " AND meta_key NOT IN ( '_order_number' )";
    }

    /**
     * Remove the WooCommerce filter which convers the order numbers to integers by removing the * * characters or prefix.
     * @since   1.2.6
     */
    public function remove_order_id_tracking_filter() {
        remove_filter( 'woocommerce_shortcode_order_tracking_order_id', 'wc_sanitize_order_id' );
    }

    /**
     * @param string $order_number.
     * 
     * @since   1.2.6
     * 
     * Add woocommerce_order_number_to_tracking compatibility.
     *
     * @since   1.4.7
     * 
     * Translate the merchant ref ID (order number) back to the database order_id
     *
     * @hooked woocommerce_amazon_pa_merchant_metadata_reference_id_reverse - 10
     * 
     * @since   1.5.4
     * 
     * Added HPOS Compatibility
     * 
     * @return int post_id for the order identified by $order_number
     */
    public function wt_order_id_from_order_number( $order_id ) {
	    if(Wt_Advanced_Order_Number_Common::is_wc_hpos_enabled()) {
		    $orders = wc_get_orders([
			    'return'     => 'ids',
			    'limit'      => 1,
			    'meta_query' => [
				    [
					    'key'        => '_order_number',
					    'value'      => $order_id,
					    'comparison' => '='
				    ],
			    ],
		    ]);
	    } else {
		    $orders = get_posts( [
			    'numberposts' => 1,
			    'meta_key'    => '_order_number',
			    'meta_value'  => $order_id,
			    'post_type'   => 'shop_order',
			    'post_status' => 'any',
			    'fields'      => 'ids',
		    ] );
	    }

	    $order_id = $orders ? current($orders) : null;

	    if ( $order_id !== null ) {
		    return $order_id;
	    }
        return $order_id;
    }

    /**
    *   @since 1.3.3
    *   Adding padding number to sequential number
    *   @return $order_number with padding
    */
    public static function add_order_no_padding($order_number) 
    {
        $padding = '';
        $padding_no=get_option('wt_sequence_order_number_padding',0);
        $padding_count =(int) $padding_no - strlen($order_number);
        if ($padding_count > 0) 
        {
            for ($i = 0; $i < $padding_count; $i++)
            {
                $padding .= '0';
            }
        }
        update_option('wt_sequence_last_order_number_padding',$padding_no);
        return $padding.$order_number;
    }

     /**
    *   @since 1.3.6
    *   Replace date shortcode from sequential number prefix/suffix data
    *   @return string
    */
    public static function get_date_from_shortcode($shortcode_text, $order_id) 
    {   
        preg_match_all("/\[([^\]]*)\]/", $shortcode_text, $matches);
        if(!empty($matches[1]))
        { 
            foreach($matches[1] as $date_shortcode) 
            { 
                $match=array();
                $date_val=time();
                $order_date_format=$date_shortcode;
                $order_date_format=apply_filters('wt_order_number_date_format',$order_date_format);
                $order = new WC_Order($order_id);
                if(!empty($order))
                { 
                    $order_date = $order->get_date_created();
                }
                $date_val = $order_date->format($order_date_format);

                $shortcode_text=str_replace("[$date_shortcode]", $date_val, $shortcode_text); 
            }
        }
        return $shortcode_text;
    }

    /** 
    *   @since 1.3.6
    *   Add Prefix/Suffix to sequential number
    *   @return string
    */
    public static function add_prefix_suffix($padded_order_number,$order_id) 
    {          
        $order_template = get_option('wt_sequence_order_number_format');
        $prefix = self::get_sequence_prefix($order_id);
        $date_prefix = self::get_sequence_prefix_date($order_id);
        $suffix='';
        $date_suffix='';
        if($order_template=="")
        {
            if($prefix!='' && $date_prefix!='')
            {
                $order_template='[prefix][date][number]';
            }
            elseif($prefix!='')
            {
                $order_template = '[prefix][number]'; 
            }
            elseif($date_prefix!= '')
            {
                $order_template = '[date][number]'; 
            }
            elseif($date_prefix == '' && $prefix == '' )
            {
                $order_template = '[number]'; 
            }
        }
        if($date_prefix != '')
        {
            $date_prefix=self::get_date_from_shortcode($date_prefix, $order_id);
        }

        update_option('wt_sequence_last_order_number_format',$order_template);

        return str_replace(array('[prefix]','[date]','[number]','[suffix]','[date_suffix]'),array($prefix,$date_prefix,$padded_order_number,$suffix,$date_suffix),$order_template); 
    }

    /**
     * Set order date prefix to sequential order number.
     *
     * @since 1.4.5
     *
     * @param $order_id
     * @return $date_prefix
     */
    public static function get_sequence_prefix_date($order_id) {

        $date_prefix = sanitize_text_field(get_option('wt_sequence_order_date_prefix', ''));
        $date_prefix = apply_filters('wt_order_number_date_prefix', $date_prefix,$order_id);
        update_option('wt_last_date_prefix',$date_prefix);
        return $date_prefix;
    }

    /**
    * @since 1.4.5
    * @since 1.5.2 Added HPOS Compatibility
    * Check sequential ordernumber already exists.
    * @return boolean
    */

    public static function wt_sequential_number_already_exists($order_number) 
    {
        global $wpdb;
        $key='_order_number';

        if("order_table" === Wt_Advanced_Order_Number_Common::which_table_to_take()){
			$table_name = $wpdb->prefix.'wc_orders_meta';
			$r = $wpdb->get_col($wpdb->prepare("
			SELECT COUNT(om.meta_value) AS num_exists FROM {$table_name} om
			WHERE om.meta_key = '%s' AND om.meta_value = '%s'", $key,$order_number));
			return (isset($r[0]) && $r[0]>0) ? true : false;
		}
        else
        {
            $post_type = 'shop_order';
            $r = $wpdb->get_col($wpdb->prepare("
            SELECT COUNT(pm.meta_value) AS num_exists FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = '%s' 
            AND p.post_type = '%s' AND pm.meta_value = '%s'", $key, $post_type,$order_number));
            return (isset($r[0]) && $r[0]>0) ? true : false;
        }
    }

    /**
     * Add WooCommerce QuickPay Support
     *
     * @since 1.4.6
     * @since 1.5.2 Added HPOS Compatibility
     *
     * @param string $order_number
     * @param WC_Order $order
     * @param bool $recurring
     * @return $order_number
     */

    public function wt_quickpay_order_number_for_api( $order_number, $order, $recurring ) {

        $order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();

        $sequence_number = Wt_Advanced_Order_Number_Common::get_order_meta($order_id, '_order_number');

        if( !empty( $sequence_number ) )
        {
            $order_number = $sequence_number;
        }
        return $order_number;
    }

    /**
     * Add WooCommerce Amazon Pay Support
     *
     * @since 1.4.7
     * @since 1.5.2 Added HPOS Compatibility
     * 
     * Filter the merchant reference ID sent to Amazon
     * 
     * @hooked woocommerce_amazon_pa_merchant_metadata_reference_id 
     *
     * @param int $order_id Order ID of the current order
     * @return string Order number from Sequential Order Number plugin
     */
    function wt_amazon_pay_order_number( $order_id ) {

        $sequence_number = Wt_Advanced_Order_Number_Common::get_order_meta($order_id, '_order_number');

        if( !empty( $sequence_number )) 
        {
            return $sequence_number;
        }
        return $order_id;
    }
    /**
    *   @since 1.4.7
    *   Check whether the order is created before plugin installation.
    * 
    *   @since 1.4.8 [Bug fix]: Uses current_time() instead of time() to avoid timezone conflicts.
    *   
    *   @return boolean
    */
    public static function is_old_order($order_id){

        $order = new WC_Order($order_id);
        $order_date_format='Y-m-d h:i:s';
        $date_val = $order->get_date_created();   
        $order_date = $date_val->format($order_date_format); 
        
        if(get_option('wt_seq_basic_installation_date') === false)
        {
            if(get_option('wt_seq_basic_start_date'))
            {
                $install_date = get_option('wt_seq_basic_start_date',current_time( 'timestamp', true ));
            }
            else
            {
                $install_date = current_time( 'timestamp', true );
            }
            update_option('wt_seq_basic_installation_date',$install_date);
        }
        $utc_timestamp = get_option('wt_seq_basic_installation_date');
        $utc_timestamp_converted = date( 'Y-m-d h:i:s', $utc_timestamp );
        $local_timestamp = get_date_from_gmt( $utc_timestamp_converted, 'Y-m-d h:i:s' );
        if($order_date < $local_timestamp)
        {
            return true;
        }
        return false;
    }

    /**
     * Don't copy over order number meta when creating a parent or child renewal order when WC subscritions version greater than 2.5.0
     *
     * Prevents unnecessary order meta from polluting parent renewal orders,
     * and set order number for subscription orders
     *
     * @since 1.5.0
     * @param array $order_meta_query query for pulling the metadata
     * @return string
     */

    public function wt_seq_remove_renewal_order_meta( $order_meta ) {
        unset( $order_meta['_order_number'] );
        return $order_meta;
    }

    private function define_public_hooks() {

        $plugin_public = new Wt_Advanced_Order_Number_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    public function wt_seq_order_status_changed($order_id, $old_status, $new_status, $order) {
        
        $order = wc_get_order( $order_id );
        $order_status = $order ? $order->get_status() : '';
        
        $is_draft = self::wt_seq_is_draft_order( $order_status );
        $is_draft = apply_filters('wt_sequential_is_draft_order',$is_draft,$order);

        if( false === $is_draft ) {
            $this->set_sequential_number( $order_id );
        }
    }

    public static function wt_seq_is_draft_order($order_status) {

        $is_draft = false;

        if( 'checkout-draft' === $order_status) {
            $is_draft = true;
        }   elseif ( 'draft' === $order_status ) {
            $is_draft = true;
        }   else {
            $is_draft = false;
        }
        return $is_draft;
    }

    /**
     * Added new filter 'wt_sequential_change_last_order_number'.
     * @since   1.4.4
     * Added new filter 'wt_sequential_alter_order_number'.
     * @since   1.5.2
     * Added additional loop to avoid order number duplication in heavy traffic site
     * @since 1.5.0
     * Added conditionalx check to avoid sequential order number generation for subscription orders.
     * @since 1.5.0
     * @since 1.5.2 Added HPOS Compatibility
     * @since 1.5.7 Fixed order number duplication issue by using file lock.
     */

    public function set_sequential_number($post_id, $post = array() ) {

        global $wpdb;
        if ( is_array( $post ) || is_null( $post ) || ( is_object( $post ) && (( isset( $post->post_type )) || isset( $post->type) ) && (( ( 'shop_order' === $post->post_type ) ) || ( 'shop_order' === $post->type ) ) ) ){
            $order = $post instanceof \WC_Order ? $post : ($post_id instanceof \WC_Order ? $post_id :wc_get_order( $post_id ));

            // checks whether the order is subscription order
            if ( is_object( $order ) && is_a( $order, 'WC_Subscription' ) ) {
                $is_subscription = true;
            } elseif ( is_numeric( $order ) && 'shop_subscription' === get_post_type( $order ) ) {
                $is_subscription = true;
            } elseif ( is_object( $order ) && is_a( $order, 'HF_Subscription' ) ) {
                $is_subscription = true;
            } elseif ( is_numeric( $order ) && 'hf_shop_subscription' == get_post_type( $order ) ) {
                $is_subscription = true;
            } else {
                $is_subscription = false;
            }

            $is_subscription=apply_filters('wt_sequential_is_subscription_order',$is_subscription,$order);
            // If order is subscription skip sequential order number generation.
            if( true === $is_subscription)
            {
                return ;
            }

            $order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
            $order_number = Wt_Advanced_Order_Number_Common::get_order_meta($order_id, '_order_number');
            $increment_counter = !empty((int) get_option('wt_sequence_increment_counter', 1)) ? (int) get_option('wt_sequence_increment_counter', 1) : 1;
            $is_old_order = self::is_old_order($order_id);
            $is_old_order = apply_filters('wt_sequential_is_old_order',$is_old_order,$order_id);
            $order_status = $order ? $order->get_status() : '';
            
            $is_draft     = self::wt_seq_is_draft_order( $order_status );
            $is_draft     = apply_filters('wt_sequential_is_draft_order',$is_draft,$order);

            // If order is a draft order skip sequential order number generation.
            if( true === $is_draft ) {
                return ;
            }
            
            $lockFolderPath = self::get_temp_dir();
            if ( !is_dir( $lockFolderPath ) ) {

                wp_mkdir_p( $lockFolderPath );
            }
            $lockFilePath	= $lockFolderPath.'/wt_sequential_order_number.lock';
            $file 			= fopen($lockFilePath,"w");

            if (flock($file,LOCK_EX)) {

                if (empty($order_number) && false === $is_old_order) { 

                    $prefix = self::get_sequence_prefix($order_id);

                    $last_order_num = get_option('wt_last_order_number', 0);

                    $query_array = [
                        "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES (%d,%s,%s)",
                    ];
                        
                    if(Wt_Advanced_Order_Number_Common::is_wc_hpos_enabled()) {
                            
                        $table_name = $wpdb->prefix.'wc_orders_meta';

                        $query_array[] = "INSERT INTO {$table_name} (order_id, meta_key, meta_value) VALUES (%d,%s,%s)";  
                    }

                    $wt_last_order_number = get_option('wt_last_order_number', $last_order_num);

                    $wt_last_order_number = apply_filters('wt_sequential_change_last_order_number',$wt_last_order_number,$order_id);

                    $next_insert_id = $wt_last_order_number + 1;

                    update_option('wt_last_order_number', $next_insert_id,'no');

                    $next_insert_id_padding=self::add_order_no_padding($next_insert_id);

                    $next_order_number = self::add_prefix_suffix($next_insert_id_padding,$order_id);

                    while(self::wt_sequential_number_already_exists($next_order_number)) { 

                        $next_insert_id = $next_insert_id + $increment_counter;

                        update_option('wt_last_order_number', $next_insert_id,'no');
                            
                        $next_insert_id_padding=self::add_order_no_padding($next_insert_id);

                        $next_order_number = self::add_prefix_suffix($next_insert_id_padding,$order_id);               
                    }
                    foreach ($query_array as $sql) {
                            
                        $query = $wpdb->prepare($sql, $post_id, '_order_number', $next_order_number); 

                        $res = $wpdb->query($query);
                    }
                    $order->save();
                } 
                flock($file,LOCK_UN);
            } else {

                sleep(2);
                self::set_sequential_number( $post_id, $post );
            }
        }
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_plugin_base_name() {
        return $this->plugin_base_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }

    public function display_sequence_number($order_number, $order) {

        $order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
        $sequential_order_number = Wt_Advanced_Order_Number_Common::get_order_meta($order_id, '_order_number');
        $sequential_order_number = apply_filters('wt_alter_sequence_number',$sequential_order_number,$order_id);
        return ($sequential_order_number) ? $sequential_order_number : $order_number;
    }

	/**
	* 	@since 1.5.8
	*	Get upload Path
	*	@return string
	*/
	public static function get_temp_dir()
	{
		$uploads_dir = wp_upload_dir();

        $folder_name = WT_SEQUENCIAL_ORDNUMBER_NAME . '-lock';

        // Construct the full path for the new folder
        $folder_path = $uploads_dir['basedir'] . '/' . $folder_name;

        // Check if the folder already exists
        if (!file_exists($folder_path)) {
            // Create the folder and its parent directories
            wp_mkdir_p($folder_path);
        }
        return $folder_path;
    }

    public function run() {
        $this->loader->run();
    }

    /**
	* 	@since 1.6.3
	*	Alter query parameters for adding order number as a serch option in order listing page.
    *
    *   @param array $query Arguments to be passed to `wc_get_orders()`.
    *
	*	@return array
	*/
    public function wt_order_list_table_prepare_items_query_args($query){
        if((isset($query['search_filter']) && $query['search_filter'] === 'order_number') && (isset($query['s']) && ! empty( $query['s']))){
            $query['meta_query'][] = array(
                'key'   => '_order_number',
                'value' => sanitize_text_field( $query['s']),
                'compare' => 'LIKE',         
            );
            unset($query['search_filter']);
            unset($query['s']);
        }
        return $query;
    }

}