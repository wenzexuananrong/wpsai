<?php
namespace LPFW\Interfaces;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Abstraction that provides contract relating to plugin models.
 * All "regular models" should implement this interface.
 *
 * @since 1.0.0
 */
interface Model_Interface {

    /**
     * Contract for running the model.
     *
     * @since 1.0.0
     * @access public
     */
    public function run();

}
