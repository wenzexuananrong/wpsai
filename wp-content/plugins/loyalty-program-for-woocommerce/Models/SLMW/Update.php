<?php
namespace LPFW\Models\SLMW;

use LPFW\Abstracts\Abstract_Main_Plugin_Class;
use LPFW\Abstracts\Base_Model;
use LPFW\Helpers\Helper_Functions;
use LPFW\Helpers\Plugin_Constants;
use LPFW\Interfaces\Deactivatable_Interface;
use LPFW\Interfaces\Initiable_Interface;
use LPFW\Interfaces\Model_Interface;
use LPFW\Models\SLMW\License;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model that houses the logic of extending the coupon system of woocommerce.
 * It houses the logic of handling coupon url.
 * Public Model.
 *
 * @since 1.0
 */
class Update extends Base_Model implements Model_Interface, Initiable_Interface, Deactivatable_Interface {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that holds the single main instance of License.
     *
     * @since 1.8.7
     * @access private
     * @var License
     */
    private $_license;

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.0
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     */
    public function __construct( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        parent::__construct( $main_plugin, $constants, $helper_functions );
        $this->_license = License::get_instance( $main_plugin, $constants, $helper_functions );
        $main_plugin->add_to_all_plugin_models( $this );
    }

    /**
     * Hijack the WordPress 'set_site_transient' function for 'update_plugins' transient.
     * So now we don't have our own cron to check for updates, we just rely on when WordPress check updates for plugins and themes,
     * and if WordPress does then sets the 'update_plugins' transient, then we hijack it and check for our own plugin update.
     *
     * @since 1.0.0
     * @access public
     *
     * @param object $update_plugins Update plugins data.
     */
    public function update_check( $update_plugins ) {
        /**
         * Function wp_update_plugins calls set_site_transient( 'update_plugins' , ... ) twice, yes twice
         * so we need to make sure we are on the last call before checking plugin updates
         * the last call will have the checked object parameter
         *
         * @since 1.1.2 Added flag condition to forcefully run update check via settings.
         */
        if ( isset( $update_plugins->checked ) ) {
            $this->ping_for_new_version();
        }
        // Check plugin for updates.

        /**
         * We try to inject plugin update data if it has any
         * This is to fix the issue about plugin info appearing/disappearing
         * when update page in WordPress is refreshed
         */
        $result = $this->inject_plugin_update(); // Inject new update data if there are any.

        if ( $result && isset( $update_plugins->response ) && is_array( $update_plugins->response ) && ! array_key_exists( $result['key'], $update_plugins->response ) ) {
            $update_plugins->response[ $result['key'] ] = $result['value'];
        }

        $this->_validate_update_data_version( $update_plugins );

        return $update_plugins;
    }

    /**
     * Validate the plugin's update data to make sure that the version is higher than the current one installed.
     *
     * @since 1.8.2
     * @access private
     *
     * @param object $update_plugins Update plugins data.
     */
    private function _validate_update_data_version( &$update_plugins ) {

        // Skip if plugin is not present in the list of updates.
        if ( ! isset( $update_plugins->response[ $this->_constants->PLUGIN_BASENAME ] ) ) {
            return;
        }

        $new_version = $update_plugins->response[ $this->_constants->PLUGIN_BASENAME ]->new_version;

        // Unset the plugin from the list of updates when the version is lower or equal to the current one installed.
        if ( version_compare( $new_version, $this->_constants->VERSION, '<=' ) ) {
            unset( $update_plugins->response[ $this->_constants->PLUGIN_BASENAME ] );
        }
    }

    /**
     * Ping plugin for new version. Ping static file first, if indeed new version is available, trigger update data request.
     *
     * @since 1.0.0
     * @since 1.8   Refactor and improve support for multisite setup.
     * @since 1.1.2 Add flag to force check new version and fetch update data.
     * @access public
     *
     * @param bool $force Flag to force ping new version.
     */
    public function ping_for_new_version( $force = false ) {
        $license_activated = get_site_option( $this->_constants->OPTION_LICENSE_ACTIVATED );

        if ( 'yes' !== $license_activated ) {
            delete_site_option( $this->_constants->OPTION_UPDATE_DATA );
            return;
        }

        $retrieving_update_data = get_site_option( $this->_constants->OPTION_RETRIEVING_UPDATE_DATA );

        if ( 'yes' === $retrieving_update_data ) {
            return;
        }

        /**
         * Only attempt to get the existing saved update data when the operation is not forced.
         * Else, if it is forced, we ignore the existing update data if any
         * and forcefully fetch the latest update data from our server.
         *
         * @since 2.7.2
         */
        $update_data = ! $force ? get_site_option( $this->_constants->OPTION_UPDATE_DATA ) : null;

        /**
         * Even if the update data is still valid, we still go ahead and do static json file ping.
         * The reason is on WooCommerce 3.3.x , it seems WooCommerce do not regenerate the download url every time you change the downloadable zip file on WooCommerce store.
         * The side effect is, the download url is still valid, points to the latest zip file, but the update info could be outdated coz we check that if the download url
         * is still valid, we don't check for update info, and since the download url will always be valid even after subsequent release of the plugin coz WooCommerce is reusing the url now
         * then there will be a case our update info is outdated. So that is why we still need to check the static json file, even if update info is still valid.
         */

        $option = array(
            'timeout' => 10, // seconds coz only static json file ping.
            'headers' => array( 'Accept' => 'application/json' ),
        );

        $response = wp_remote_retrieve_body( wp_remote_get( apply_filters( 'lpfw_plugin_new_version_ping_url', $this->_constants->STATIC_PING_FILE ), $option ) );
        $response = ! empty( $response ) ? json_decode( $response ) : null;

        // Skip if the response is empty.
        if ( ! is_object( $response ) || ! property_exists( $response, 'version' ) ) {
            return;
        }

        $installed_version = get_site_option( $this->_constants->INSTALLED_VERSION );

        if ( ( ! $update_data && version_compare( $response->version, $installed_version, '>' ) ) ||
            ( $update_data && version_compare( $response->version, $update_data->latest_version, '>' ) ) ) {

            update_site_option( $this->_constants->OPTION_RETRIEVING_UPDATE_DATA, 'yes' );

            $activation_email = get_site_option( $this->_constants->OPTION_ACTIVATION_EMAIL );
            $license_key      = get_site_option( $this->_constants->OPTION_LICENSE_KEY );

            // Fetch software product update data.
            $this->_fetch_software_product_update_data( $activation_email, $license_key, home_url() );

            delete_site_option( $this->_constants->OPTION_RETRIEVING_UPDATE_DATA );

        } elseif ( $update_data && version_compare( $update_data->latest_version, $installed_version, '<=' ) ) {
            /**
             * We delete the option data if update is already installed
             * We encountered a bug when updating the plugin via the dashboard updates page
             * The update is successful but the update notice does not disappear
             */
            delete_site_option( $this->_constants->OPTION_UPDATE_DATA );
        }
    }

    /**
     * Fetch software product update data.
     *
     * @since 1.0.0
     * @since 1.8 Refactor and improve support for multisite setup.
     * @access public
     *
     * @param string $activation_email Activation email.
     * @param string $license_key      License key.
     * @param string $site_url         Site url.
     */
    private function _fetch_software_product_update_data( $activation_email, $license_key, $site_url ) {
        $update_check_url = add_query_arg(
            array(
                'activation_email' => urlencode( $activation_email ), // phpcs:ignore
                'license_key'      => $license_key,
                'site_url'         => $site_url,
                'multisite'        => is_multisite() ? 1 : 0,
            ),
            apply_filters( 'lpfw_software_product_update_data_url', $this->_constants->UPDATE_DATA_ENDPOINT )
        );

        $option = array(
            'timeout' => 30, // seconds for worst case the server is choked and takes little longer to get update data ( this is an ajax end point ).
            'headers' => array( 'Accept' => 'application/json' ),
        );

        $response = json_decode( wp_remote_retrieve_body( wp_remote_get( $update_check_url, $option ) ) );

        // Skip if the result is empty.
        if ( empty( $response ) ) {
            return;
        }

        // Process license options data.
        $this->_license->process_license_response( $response, 'update_data' );

        do_action( 'lpfw_software_product_update_data', $response, $activation_email, $license_key );
    }

    /**
     * Inject plugin update info to plugin update details page.
     * Note, this is only triggered when there is a new update and the "View version <new version here> details" link is clicked.
     * In short, the pure purpose for this is to provide details and info the update info popup.
     *
     * @since 1.0.0
     * @since 1.8 Refactor and improve support for multisite setup.
     * @access public
     *
     * @param false|object|array $result The result object or array. Default false.
     * @param string             $action The type of information being requested from the Plugin Install API.
     * @param object             $args   Plugin API arguments.
     * @return array Plugin update info.
     */
    public function inject_plugin_update_info( $result, $action, $args ) {
        if ( 'plugin_information' === $action && isset( $args->slug ) && 'loyalty-program-for-woocommerce' === $args->slug ) {

            $software_update_data = get_site_option( $this->_constants->OPTION_UPDATE_DATA );

            if ( $software_update_data ) {

                $update_info = new \StdClass();

                $update_info->name               = 'Loyalty Program for WooCommerce';
                $update_info->slug               = 'loyalty-program-for-woocommerce';
                $update_info->version            = $software_update_data->latest_version;
                $update_info->tested             = $software_update_data->tested_up_to;
                $update_info->last_updated       = $software_update_data->last_updated;
                $update_info->homepage           = $software_update_data->home_page;
                $update_info->author             = sprintf( '<a href="%s" target="_blank">%s</a>', $software_update_data->author_url, $software_update_data->author );
                $update_info->download_link      = $software_update_data->download_url;
                $update_info->disable_autoupdate = true;
                $update_info->sections           = array(
                    'description'  => $software_update_data->description,
                    'installation' => $software_update_data->installation,
                    'changelog'    => $software_update_data->changelog,
                    'support'      => $software_update_data->support,
                );

                $update_info->icons = array(
                    '1x'      => 'https://advancedcouponsplugin.com/assets/plugin-images/lpfw-icon-128x128.jpg',
                    '2x'      => 'https://advancedcouponsplugin.com/assets/plugin-images/lpfw-icon-256x256.jpg',
                    'default' => 'https://advancedcouponsplugin.com/assets/plugin-images/lpfw-icon-256x256.jpg',
                );

                $update_info->banners = array(
                    'low'  => 'https://advancedcouponsplugin.com/assets/plugin-images/lpfw-banner-772x250.jpg',
                    'high' => 'https://advancedcouponsplugin.com/assets/plugin-images/lpfw-banner-1544x500.jpg',
                );

                return $update_info;

            }
        }

        return $result;
    }

    /**
     * When WordPress fetch 'update_plugins' transient ( Which holds various data regarding plugins, including which have updates ),
     * we inject our plugin update data in, if any. It is saved on $this->_constants->OPTION_UPDATE_DATA option.
     * It is important we dont delete this option until the plugin have successfully updated.
     * The reason is we are hooking ( and we should do it this way ), on transient read.
     * So if we delete this option on first transient read, then subsequent read will not include our plugin update data.
     *
     * It also checks the validity of the update url. There could be edge case where we stored the update data locally as an option,
     * then later on the store, the product was deleted or any action occurred that would deem the update data invalid.
     * So we check if update url is still valid, if not, we remove the locally stored update data.
     *
     * @since 1.0.0
     * @since 1.2.3
     * Refactor codebase to adapt being called on set_site_transient function.
     * We don't need to check for software update data validity as its already been checked on ping_for_new_version
     * and this function is immediately called right after that.
     * @since 1.8 Refactor and improve support for multisite setup.
     * @access public
     *
     * @return array Filtered plugin updates data.
     */
    public function inject_plugin_update() {
        $software_update_data = get_site_option( $this->_constants->OPTION_UPDATE_DATA );

        if ( $software_update_data ) {

            $update = new \stdClass();

            $update->id                 = $software_update_data->download_id;
            $update->slug               = 'loyalty-program-for-woocommerce';
            $update->plugin             = $this->_constants->PLUGIN_BASENAME;
            $update->new_version        = $software_update_data->latest_version;
            $update->url                = $this->_constants->PLUGIN_SITE_URL;
            $update->package            = $software_update_data->download_url;
            $update->tested             = $software_update_data->tested_up_to;
            $update->disable_autoupdate = true;

            $update->icons = array(
                '1x'      => 'https://advancedcouponsplugin.com/assets/plugin-images/lpfw-icon-128x128.jpg',
                '2x'      => 'https://advancedcouponsplugin.com/assets/plugin-images/lpfw-icon-256x256.jpg',
                'default' => 'https://advancedcouponsplugin.com/assets/plugin-images/lpfw-icon-256x256.jpg',
            );

            $update->banners = array(
                '1x'      => 'https://advancedcouponsplugin.com/assets/plugin-images/lpfw-banner-772x250.jpg',
                '2x'      => 'https://advancedcouponsplugin.com/assets/plugin-images/lpfw-banner-1544x500.jpg',
                'default' => 'https://advancedcouponsplugin.com/assets/plugin-images/lpfw-banner-1544x500.jpg',
            );

            return array(
                'key'   => $this->_constants->PLUGIN_BASENAME,
                'value' => $update,
            );

        }

        return false;
    }

    /**
     * Delete the plugin update data after the plugin successfully updated.
     *
     * References:
     * https://stackoverflow.com/questions/24187990/plugin-update-hook
     * https://codex.wordpress.org/Plugin_API/Action_Reference/upgrader_process_complete
     *
     * @since 1.0.0
     * @since 1.8 Refactor and improve support for multisite setup.
     * @access public
     *
     * @param Plugin_Upgrader $upgrader_object Plugin_Upgrader instance.
     * @param array           $options         Options.
     */
    public function after_plugin_update( $upgrader_object, $options ) {
        if ( 'update' === $options['action'] && 'plugin' === $options['type'] && isset( $options['plugins'] ) && is_array( $options['plugins'] ) ) {
            foreach ( $options['plugins'] as $each_plugin ) {
                if ( $each_plugin === $this->_constants->PLUGIN_BASENAME ) {
                    delete_site_option( $this->_constants->OPTION_UPDATE_DATA );
                    break;
                }
            }
        }
    }

    /**
     * Disable auto update for plugin.
     * Servers are bombarded with download requests for plugin updates from the same customers with each requests gap are only a few minutes.
     * There's a chance that this issue is caused by WP auto update script. This will ensure that our premium plugins will not be auto updated.
     *
     * @since 1.1.2
     * @access public
     *
     * @param bool   $update Flag to check if plugin should be auto updated.
     * @param object $item   Plugin item update package.
     * @return bool Filtered flag to check if plugin should be auto updated.
     */
    public function disable_auto_update( $update, $item ) {
        if ( isset( $item->plugin ) && $this->_constants->PLUGIN_BASENAME === $item->plugin ) {
            return false;
        }

        return $update;
    }

    /**
     * Force fetch update data.
     * This will be run when the button under "help" section on the settings is clicked.
     *
     * @since 2.7.2
     * @access private
     */
    private function _force_fetch_update_data() {
        // force ping for new version.
        // refetch update data when new version is higher than current version installed.
        $this->ping_for_new_version( true );

        // get the key and formatted update data value.
        $result = $this->inject_plugin_update();

        if ( ! $result ) {
            return array(
                'status'    => 'fail',
                'error_msg' => __( 'There was an issue trying to refetch the update data. Please make sure that there is an available update and that your license is activated.', 'loyalty-program-for-woocommerce' ),
            );
        }

        // get update_plugins transient value via get_site_option so we don't trigger any hooks.
        $update_plugins = get_site_option( '_site_transient_update_plugins' );

        /**
         * Overwrite update data for our plugin.
         * We make sure that response property is present first before overwriting.
         */
        if ( $update_plugins && isset( $update_plugins->response ) && is_array( $update_plugins->response ) ) {

            $update_plugins->response[ $result['key'] ] = $result['value'];

            // save update_plugins transient value via update_site_options so we don't trigger any hooks.
            update_site_option( '_site_transient_update_plugins', $update_plugins );

            return array(
                'status'  => 'success',
                'message' => __( 'Plugin update data has been refetched successfully.', 'loyalty-program-for-woocommerce' ),
            );
        }

        return array(
            'status'  => 'warning',
            'message' => __( 'Plugin updates transient is not yet present. Please visit Dashboard > Updates page and try again.', 'loyalty-program-for-woocommerce' ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX Functions.
    |--------------------------------------------------------------------------
     */

    /**
     * AJAX refetch update data.
     *
     * @since 1.1.2
     * @access public
     */
    public function ajax_refetch_update_data() {
        $post_data = $this->_helper_functions->validate_ajax_request(
            array(
                'nonce_value_key' => 'nonce',
                'nonce_action'    => 'lpfw_slmw_refetch_update_data',
                'user_capability' => 'manage_woocommerce',
            )
        );

        if ( is_wp_error( $post_data ) ) {
            wp_send_json(
                array(
                    'status'         => 'fail',
                    'message'        => $post_data->get_error_message(),
                    'license_status' => 'invalid',
                )
            );
        }

        $response = $this->_force_fetch_update_data();

        wp_send_json( $response );
    }

    /*
    |--------------------------------------------------------------------------
    | Fulfill implemented interface contracts
    |--------------------------------------------------------------------------
     */

    /**
     * Execute codes that needs to run plugin deactivation.
     *
     * @since 1.0
     * @access public
     * @implements LPFW\Interfaces\Deactivatable_Interface
     */
    public function deactivate() {
        // Delete plugin update option data.
        delete_site_option( $this->_constants->OPTION_UPDATE_DATA );
    }

    /**
     * Execute codes that needs to run plugin activation.
     *
     * @since 1.1.2
     * @access public
     * @implements LPFW\Interfaces\Initializable_Interface
     */
    public function initialize() {
        add_action( 'wp_ajax_lpfw_slmw_refetch_update_data', array( $this, 'ajax_refetch_update_data' ) );
    }

    /**
     * Execute Update class.
     *
     * @since 1.0
     * @access public
     * @inherit LPFW\Interfaces\Model_Interface
     */
    public function run() {
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_check' ) );
        add_filter( 'plugins_api', array( $this, 'inject_plugin_update_info' ), 10, 3 );
        add_action( 'upgrader_process_complete', array( $this, 'after_plugin_update' ), 10, 2 );
        add_filter( 'auto_update_plugin', array( $this, 'disable_auto_update' ), 10, 2 );
    }
}
