<?php
namespace LPFW\Models;

use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Abstracts\Base_Model;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Activatable_Interface;
use LPFW\Interfaces\Deactivatable_Interface;
use LPFW\Interfaces\Initiable_Interface;
use LPFW\Interfaces\Model_Interface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Model that houses the logic of 'Bootstraping' the plugin.
 *
 * @since 1.0.0
 */
class Bootstrap extends Base_Model implements Model_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Array of models implementing the LPFW\Interfaces\Activatable_Interface.
     *
     * @since 1.0.0
     * @access private
     * @var array
     */
    private $_activatables;

    /**
     * Array of models implementing the LPFW\Interfaces\Initiable_Interface.
     *
     * @since 1.0.0
     * @access private
     * @var array
     */
    private $_initiables;

    /**
     * Array of models implementing the LPFW\Interfaces\Deactivatable_Interface.
     *
     * @since 1.0.0
     * @access private
     * @var array
     */
    private $_deactivatables;

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.0.0
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     * @param array                      $activatables     Array of models implementing LPFW\Interfaces\Activatable_Interface.
     * @param array                      $initiables       Array of models implementing LPFW\Interfaces\Initiable_Interface.
     * @param array                      $deactivatables   Array of models implementing LPFW\Interfaces\Deactivatable_Interface.
     */
    public function __construct( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions, array $activatables = array(), array $initiables = array(), array $deactivatables = array() ) {
        parent::__construct( $main_plugin, $constants, $helper_functions );
        $this->_activatables   = $activatables;
        $this->_initiables     = $initiables;
        $this->_deactivatables = $deactivatables;

        $main_plugin->add_to_all_plugin_models( $this );
        $this->_register_custom_database_tables();
    }

    /**
     * Load plugin text domain.
     *
     * @since 1.0.0
     * @access public
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain( $this->_constants->TEXT_DOMAIN, false, $this->_constants->PLUGIN_DIRNAME . '/languages' );
    }

    /**
     * Register custom database tables through WC filter.
     *
     * @since 1.7
     * @access public
     */
    private function _register_custom_database_tables() {
        global $wpdb;

        $custom_tables = array(
            $this->_constants->DB_TABLE_NAME, // loyalty point entries.
        );

        foreach ( $custom_tables as $table ) {
            $wpdb->$table   = $wpdb->prefix . $table;
            $wpdb->tables[] = $table;
        }
    }

    /**
     * Method that houses the logic relating to activating the plugin.
     *
     * @since 1.0.0
     * @access public
     *
     * @global wpdb $wpdb Object that contains a set of functions used to interact with a database.
     *
     * @param boolean $network_wide Flag that determines whether the plugin has been activated network wid ( on multi site environment ) or not.
     */
    public function activate_plugin( $network_wide ) {
        global $wpdb;

        if ( is_multisite() ) {

            update_site_option( $this->_constants->INSTALLED_VERSION, $this->_constants->VERSION );
            update_site_option( $this->_constants->OPTION_LPFW_ACTIVATION_CODE_TRIGGERED, 'yes' );

            if ( $network_wide ) {

                // get ids of all sites.
                $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

                foreach ( $blog_ids as $blog_id ) {

                    switch_to_blog( $blog_id );
                    $this->_activate_plugin( $blog_id );

                }

                restore_current_blog();

            } else {
                $this->_activate_plugin( $wpdb->blogid );
            }
            // activated on a single site, in a multi-site.

        } else {
            $this->_activate_plugin( $wpdb->blogid );
        }
        // activated on a single site.
    }

    /**
     * Method to initialize the plugin in a newly created site in a multi site set up.
     *
     * @since 1.0.0
     * @access public
     *
     * @param int    $blog_id Blog ID of the created blog.
     * @param int    $user_id User ID of the user creating the blog.
     * @param string $domain  Domain used for the new blog.
     * @param string $path    Path to the new blog.
     * @param int    $site_id Site ID. Only relevant on multi-network installs.
     * @param array  $meta    Meta data. Used to set initial site options.
     */
    public function new_mu_site_init( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        if ( $this->_helper_functions->is_plugin_active_for_network( 'loyalty-program-for-woocommerce/loyalty-program-for-woocommerce.php' ) ) {

            switch_to_blog( $blog_id );
            $this->_activate_plugin( $blog_id );
            restore_current_blog();

        }
    }

    /**
     * Initialize plugin settings options.
     * This is a compromise to my idea of 'Modularity'. Ideally, bootstrap should not take care of plugin settings stuff.
     * However due to how WooCommerce do its thing, we need to do it this way. We can't separate settings on its own.
     *
     * @since 1.0.0
     * @access private
     */
    private function _initialize_plugin_settings_options() {
        // Help settings section options.

        // Set initial value of 'no' for the option that sets the option that specify whether to delete the options on plugin uninstall. Optionception.
        if ( ! get_option( $this->_constants->OPTION_CLEAN_UP_PLUGIN_OPTIONS, false ) ) {
            update_option( $this->_constants->OPTION_CLEAN_UP_PLUGIN_OPTIONS, 'no' );
        }

        /**
         * Force disable module option upon activation.
         */
        update_option( $this->_constants->MODULE_OPTION, 'no' );
    }

    /**
     * Create Loyal Programs DB Table.
     *
     * @since 1.0
     * @access private
     *
     * @global wpdb $wpdb Object that contains a set of functions used to interact with a database.
     */
    private function _create_loyalty_program_db_table() {
        global $wpdb;

        if ( get_option( $this->_constants->DB_TABLES_CREATED ) !== 'yes' ) {
            $lp_entries_db   = $wpdb->prefix . $this->_constants->DB_TABLE_NAME;
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $lp_entries_db (
                entry_id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                entry_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                entry_type varchar(20) NOT NULL,
                entry_action varchar(20) NOT NULL,
                entry_amount bigint(20) NOT NULL,
                object_id bigint(20) NOT NULL,
                PRIMARY KEY (entry_id)
            ) $charset_collate;\n";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );

            update_option( $this->_constants->DB_TABLES_CREATED, 'yes' );
        }

        $db_version = get_option( $this->_constants->DB_TABLE_VERSION, '1.0' );

        // Add a new notes column which is intended to store notes for loyalty point.
        if ( version_compare( $db_version, '1.8.6', '<' ) ) {
            $wpdb->query( "ALTER TABLE {$wpdb->acfw_loyalprog_entries} ADD COLUMN entry_notes TEXT NULL" );
        }

        update_option( $this->_constants->DB_TABLE_VERSION, $this->_constants->DB_VERSION );
    }

    /**
     * Actual function that houses the code to execute on plugin activation.
     *
     * @since 1.0.0
     * @access private
     *
     * @param int $blogid Blog ID of the created blog.
     */
    private function _activate_plugin( $blogid ) {
        // Initialize settings options.
        $this->_initialize_plugin_settings_options();

        // Create custom database table.
        $this->_create_loyalty_program_db_table();

        // Execute 'activate' contract of models implementing LPFW\Interfaces\Activatable_Interface.
        foreach ( $this->_activatables as $activatable ) {
            if ( $activatable instanceof Activatable_Interface ) {
                $activatable->activate();
            }
        }

        flush_rewrite_rules();

        update_option( $this->_constants->INSTALLED_VERSION, $this->_constants->VERSION );
        update_option( $this->_constants->OPTION_LPFW_ACTIVATION_CODE_TRIGGERED, 'yes' );
    }

    /**
     * Method that houses the logic relating to deactivating the plugin.
     *
     * @since 1.0.0
     * @access public
     *
     * @global wpdb $wpdb Object that contains a set of functions used to interact with a database.
     *
     * @param boolean $network_wide Flag that determines whether the plugin has been activated network wid ( on multi site environment ) or not.
     */
    public function deactivate_plugin( $network_wide ) {
        global $wpdb;

        // check if it is a multisite network.
        if ( is_multisite() ) {

            // check if the plugin has been activated on the network or on a single site.
            if ( $network_wide ) {

                // get ids of all sites.
                $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

                foreach ( $blog_ids as $blog_id ) {

                    switch_to_blog( $blog_id );
                    $this->_deactivate_plugin( $wpdb->blogid );

                }

                restore_current_blog();

            } else {
                $this->_deactivate_plugin( $wpdb->blogid );
            }
            // activated on a single site, in a multi-site.

        } else {
            $this->_deactivate_plugin( $wpdb->blogid );
        }
        // activated on a single site.
    }

    /**
     * Actual method that houses the code to execute on plugin deactivation.
     *
     * @since 1.0.0
     * @access private
     *
     * @param int $blogid Blog ID of the created blog.
     */
    private function _deactivate_plugin( $blogid ) {
        // Execute 'deactivate' contract of models implementing LPFW\Interfaces\Deactivatable_Interface.
        foreach ( $this->_deactivatables as $deactivatable ) {
            if ( $deactivatable instanceof Deactivatable_Interface ) {
                $deactivatable->deactivate();
            }
        }

        flush_rewrite_rules();
    }

    /**
     * Method that houses codes to be executed on init hook.
     *
     * @since 1.0.0
     * @access public
     */
    public function initialize() {
        /**
         * Execute activation codebase if not yet executed.
         * This occurs when plugin is activated and plugin requirements fails.
         * It just enables the plugin but won't execute activation code.
         * When you meet the plugin requirements, since the plugin is already enabled, it won't execute the
         * activation codebase anymore.
         *
         * Also there are cases where activation codebase isn't executed after update.
         * This ensures the activation codebase is executed after plugin update.
         */
        if (
            version_compare( get_option( $this->_constants->INSTALLED_VERSION, false ), $this->_constants->VERSION, '!=' ) ||
            get_option( $this->_constants->OPTION_LPFW_ACTIVATION_CODE_TRIGGERED, false ) !== 'yes'
        ) {

            $network_wide = $this->_helper_functions->is_plugin_active_for_network( 'advanced-coupons-for-woocommerce-premium/advanced-coupons-for-woocommerce-premium.php' );
            $this->activate_plugin( $network_wide );

        }

        // Execute 'initialize' contract of models implementing LPFW\Interfaces\Initiable_Interface.
        foreach ( $this->_initiables as $initiable ) {
            if ( $initiable instanceof Initiable_Interface ) {
                $initiable->initialize();
            }
        }
    }

    /**
     * Add settings link to plugin action links.
     *
     * @since 1.0.0
     * @access public
     *
     * @param array $links Array of plugin action links.
     * @return array Array of modified plugin action links.
     */
    public function plugin_settings_action_link( $links ) {
        $settings_link = '<a href="' . admin_url( 'admin.php?page=acfw-loyalty-program' ) . '">' . __( 'Settings', 'loyalty-program-for-woocommerce' ) . '</a>';
        array_unshift( $links, $settings_link );

        if ( 'yes' !== get_site_option( $this->_constants->OPTION_LICENSE_ACTIVATED ) ) {

            if ( is_multisite() ) {
                $license_form_link = current_user_can( 'manage_network_plugins' ) ? network_admin_url( 'admin.php?page=advanced-coupons&tab=lpfw_license' ) : '';
            } else {
                $license_form_link = admin_url( 'admin.php?page=acfw-license&tab=LPFW' );
            }

            if ( $license_form_link ) {
                $add_license_link = sprintf( '<a href="%s">%s</a>', $license_form_link, __( 'Add license key', 'loyalty-program-for-woocommerce' ) );
                array_unshift( $links, $add_license_link );
            }
        }

        return $links;
    }

    /**
     * Force disable loyalty program module
     *
     * @since 1.0
     * @access public
     *
     * @param mixed  $value Option value.
     * @param string $module Module key.
     * @return string Explict 'no' value.
     */
    public function force_disable_acfwp_loyalty_program_module( $value, $module ) {
        if ( $this->_constants->MODULE_OPTION === $module ) {
            return false;
        }

        return $value;
    }

    /**
     * Initialize REST API Controllers.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     */
    public function rest_api_init() {     }

    /**
     * Remove the old setting for loyalty program under ACFW modules.
     *
     * @since 1.0
     * @access public
     *
     * @param array $fields Module fields.
     * @return array Filtered module fields.
     */
    public function remove_acfw_old_loyalty_program_module_settings( $fields ) {
        $fields = array_filter(
            $fields,
            function ( $f ) {
            return $f['id'] !== $this->_constants->MODULE_OPTION;
            }
        );

        return $fields;
    }

    /**
     * Declare high performance order storage compatibility.
     *
     * @since 1.8.3
     * @access public
     */
    public function declare_hpos_compatibility() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                $this->_constants->MAIN_PLUGIN_FILE_PATH,
                true
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Interface contract functions
    |--------------------------------------------------------------------------
     */

    /**
     * Execute plugin bootstrap code.
     *
     * @since 1.0.0
     * @access public
     */
    public function run() {
        // remove old loyalty program module setting.
        add_filter( 'acfw_modules_section_options', array( $this, 'remove_acfw_old_loyalty_program_module_settings' ) );

        // Forcefully disable loyalty program module in ACFWP.
        add_filter( 'acfw_is_module_enabled', array( $this, 'force_disable_acfwp_loyalty_program_module' ), 10, 2 );

        // Internationalization.
        add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

        // Execute plugin activation/deactivation.
        register_activation_hook( $this->_constants->MAIN_PLUGIN_FILE_PATH, array( $this, 'activate_plugin' ) );
        register_deactivation_hook( $this->_constants->MAIN_PLUGIN_FILE_PATH, array( $this, 'deactivate_plugin' ) );

        // Execute plugin initialization ( plugin activation ) on every newly created site in a multi site set up.
        add_action( 'wpmu_new_blog', array( $this, 'new_mu_site_init' ), 10, 6 );

        // Execute codes that need to run on 'init' hook.
        add_action( 'init', array( $this, 'initialize' ) );

        // Add settings link to plugin action links.
        add_filter( 'plugin_action_links_' . $this->_constants->PLUGIN_BASENAME, array( $this, 'plugin_settings_action_link' ), 10, 2 );

        // Initialize API.
        add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );

        // Declare HPOS compatibility with WooCommerce.
        add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
    }
}
