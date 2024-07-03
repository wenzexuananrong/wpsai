<?php
namespace LPFW\Helpers\Traits;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait that houses all the helper functions specifically for validation.
 *
 * @since 1.8.4
 */
trait Validation {
    /**
     * Validate user roles.
     *
     * @since 1.0
     * @since 1.5 Deprecate $is_for_message paramater.
     * @since 1.8.4 Migrated to Trait Validation.
     * @access public
     *
     * @global wpdb $wpdb Object that contains a set of functions used to interact with a database.
     *
     * @param int   $user_id User ID.
     * @param mixed $deprecated Deprecated property.
     *
     * @return bool True if valid, false otherwise.
     */
    public function validate_user_roles( $user_id = 0, $deprecated = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
        global $wpdb;

        // Validate User ID.
        $user_id = $user_id ? $user_id : get_current_user_id();
        if ( ! $user_id ) {
            return false;
        }

        // Validate Role Restriction.
        $disallowed_roles = $this->get_option( $this->_constants->DISALLOWED_ROLES, array() );
        if ( ! empty( $disallowed_roles ) ) {
            $user_caps  = get_user_meta( $user_id, $wpdb->prefix . 'capabilities', true );
            $user_roles = is_array( $user_caps ) && ! empty( $user_caps ) ? array_keys( $user_caps ) : array();
            $intersect  = array_intersect( $disallowed_roles, $user_roles );

            if ( ! empty( $intersect ) ) {
                return false;
            }
        }

        // Validate User Restriction.
        $disallowed_users = array_map(
            function ( $user ) {
                return $user['value'];
            },
            $this->get_option( $this->_constants->DISALLOWED_USERS, array() )
        );
        if ( ! empty( $disallowed_users ) && in_array( $user_id, $disallowed_users, true ) ) {
            return false;
        }

        // If everything is valid, return true.
        return true;
    }

    /**
     * Check if role is valid or not.
     *
     * @since 1.0
     * @since 1.8.4 Migrated to Trait Validation.
     * @access public
     *
     * @param string $role Role slug.
     *
     * @return bool True if valid, false otherwise.
     */
    public function is_role_valid( $role ) {
        $disallowed_roles = $this->get_option( $this->_constants->DISALLOWED_ROLES, array() );
        return ! in_array( $role, $disallowed_roles, true );
    }
}
