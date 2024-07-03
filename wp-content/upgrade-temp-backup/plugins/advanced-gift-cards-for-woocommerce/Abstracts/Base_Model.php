<?php

namespace AGCFW\Abstracts;

use AGCFW\Helpers\Helper_Functions;
use AGCFW\Helpers\Plugin_Constants;

/**
 * Model that act as based model.
 *
 * @since 1.3.6
 */
abstract class Base_Model {
    /*
    |--------------------------------------------------------------------------
    | Traits
    |--------------------------------------------------------------------------
     */
    use \AGCFW\Traits\Singleton;

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Model that houses all the plugin constants.
     *
     * @since 1.3.6
     * @access private
     * @var Plugin_Constants
     */
    protected $_constants;

    /**
     * Property that houses all the helper functions of the plugin.
     *
     * @since 1.3.6
     * @access private
     * @var Helper_Functions
     */
    protected $_helper_functions;

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Class constructor.
     *
     * @since 1.3.6
     * @access public
     *
     * @param Abstract_Main_Plugin_Class $main_plugin      Main plugin object.
     * @param Plugin_Constants           $constants        Plugin constants object.
     * @param Helper_Functions           $helper_functions Helper functions object.
     */
    public function __construct( Abstract_Main_Plugin_Class $main_plugin, Plugin_Constants $constants, Helper_Functions $helper_functions ) {
        $this->_constants        = $constants;
        $this->_helper_functions = $helper_functions;
    }
}
