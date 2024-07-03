<?php
namespace AGCFW\Traits;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait that houses singleton pattern function.
 *
 * @since 1.3.6
 */
trait Singleton {
    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
     */

    /**
     * Property that holds the single main instance of URL_Coupon.
     *
     * @since 1.3.6
     * @access private
     * @var mixed $_instance
     */
    protected static $_instance;

    /*
    |--------------------------------------------------------------------------
    | Class Methods
    |--------------------------------------------------------------------------
     */

    /**
     * Ensure that only one instance of this class is loaded or can be loaded ( Singleton Pattern ).
     *
     * @since 1.3.6
     * @access public
     *
     * @param mixed ...$args Arguments to pass when instantiating the class.
     * @return mixed
     */
    public static function get_instance( ...$args ) {
        if ( ! static::$_instance instanceof static ) {
            static::$_instance = new static( ...$args ); // Instantiate the class with the passed arguments.
        }

        return static::$_instance;
    }
}
