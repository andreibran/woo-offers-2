<?php

namespace WooOffers\Core;

/**
 * Role-based permissions system
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Permissions class for managing role-based access control
 */
class Permissions {

    /**
     * Plugin capabilities
     */
    const CAPABILITIES = [
        'manage_woo_offers',           // Full access to plugin
        'view_woo_offers',             // View offers and basic data
        'create_woo_offers',           // Create new offers
        'edit_woo_offers',             // Edit existing offers
        'delete_woo_offers',           // Delete offers
        'view_woo_offers_analytics',   // View analytics and reports
        'manage_woo_offers_ab_tests',  // Create and manage A/B tests
        'configure_woo_offers',        // Access plugin settings
    ];

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'init', [ $this, 'init_capabilities' ] );
        add_filter( 'user_has_cap', [ $this, 'filter_user_capabilities' ], 10, 4 );
    }

    /**
     * Initialize capabilities on plugin activation
     */
    public static function add_capabilities() {
        // Define role capabilities mapping
        $role_caps = [
            'administrator' => [
                'manage_woo_offers',
                'view_woo_offers',
                'create_woo_offers',
                'edit_woo_offers',
                'delete_woo_offers',
                'view_woo_offers_analytics',
                'manage_woo_offers_ab_tests',
                'configure_woo_offers',
            ],
            'shop_manager' => [
                'view_woo_offers',
                'create_woo_offers',
                'edit_woo_offers',
                'delete_woo_offers',
                'view_woo_offers_analytics',
                'manage_woo_offers_ab_tests',
            ],
            'editor' => [
                'view_woo_offers',
                'view_woo_offers_analytics',
            ],
        ];

        // Add capabilities to roles
        foreach ( $role_caps as $role_name => $capabilities ) {
            $role = get_role( $role_name );
            if ( $role ) {
                foreach ( $capabilities as $cap ) {
                    $role->add_cap( $cap );
                }
            }
        }
    }

    /**
     * Remove capabilities on plugin deactivation
     */
    public static function remove_capabilities() {
        $roles = [ 'administrator', 'shop_manager', 'editor', 'author', 'contributor', 'subscriber' ];

        foreach ( $roles as $role_name ) {
            $role = get_role( $role_name );
            if ( $role ) {
                foreach ( self::CAPABILITIES as $cap ) {
                    $role->remove_cap( $cap );
                }
            }
        }
    }

    /**
     * Initialize capabilities
     */
    public function init_capabilities() {
        // Check if capabilities need to be added
        if ( ! get_option( 'woo_offers_capabilities_added', false ) ) {
            self::add_capabilities();
            update_option( 'woo_offers_capabilities_added', true );
        }
    }

    /**
     * Filter user capabilities
     */
    public function filter_user_capabilities( $allcaps, $caps, $args, $user ) {
        // Allow super admins to have all capabilities
        if ( is_multisite() && is_super_admin( $user->ID ) ) {
            foreach ( self::CAPABILITIES as $cap ) {
                $allcaps[ $cap ] = true;
            }
        }

        return $allcaps;
    }

    /**
     * Check if current user can perform action
     */
    public static function current_user_can( $capability, $object_id = null ) {
        return current_user_can( $capability, $object_id );
    }

    /**
     * Check if user can manage offers
     */
    public static function can_manage_offers( $user_id = null ) {
        if ( $user_id ) {
            return user_can( $user_id, 'manage_woo_offers' );
        }
        return current_user_can( 'manage_woo_offers' );
    }

    /**
     * Check if user can view offers
     */
    public static function can_view_offers( $user_id = null ) {
        if ( $user_id ) {
            return user_can( $user_id, 'view_woo_offers' );
        }
        return current_user_can( 'view_woo_offers' );
    }

    /**
     * Check if user can create offers
     */
    public static function can_create_offers( $user_id = null ) {
        if ( $user_id ) {
            return user_can( $user_id, 'create_woo_offers' );
        }
        return current_user_can( 'create_woo_offers' );
    }

    /**
     * Check if user can edit offers
     */
    public static function can_edit_offers( $user_id = null ) {
        if ( $user_id ) {
            return user_can( $user_id, 'edit_woo_offers' );
        }
        return current_user_can( 'edit_woo_offers' );
    }

    /**
     * Check if user can delete offers
     */
    public static function can_delete_offers( $user_id = null ) {
        if ( $user_id ) {
            return user_can( $user_id, 'delete_woo_offers' );
        }
        return current_user_can( 'delete_woo_offers' );
    }

    /**
     * Check if user can view analytics
     */
    public static function can_view_analytics( $user_id = null ) {
        if ( $user_id ) {
            return user_can( $user_id, 'view_woo_offers_analytics' );
        }
        return current_user_can( 'view_woo_offers_analytics' );
    }

    /**
     * Check if user can manage A/B tests
     */
    public static function can_manage_ab_tests( $user_id = null ) {
        if ( $user_id ) {
            return user_can( $user_id, 'manage_woo_offers_ab_tests' );
        }
        return current_user_can( 'manage_woo_offers_ab_tests' );
    }

    /**
     * Check if user can configure plugin
     */
    public static function can_configure_plugin( $user_id = null ) {
        if ( $user_id ) {
            return user_can( $user_id, 'configure_woo_offers' );
        }
        return current_user_can( 'configure_woo_offers' );
    }

    /**
     * Get user permissions for frontend
     */
    public static function get_user_permissions( $user_id = null ) {
        $user_id = $user_id ?: get_current_user_id();
        
        if ( ! $user_id ) {
            return [];
        }

        $permissions = [];
        foreach ( self::CAPABILITIES as $cap ) {
            $permissions[ $cap ] = user_can( $user_id, $cap );
        }

        return $permissions;
    }

    /**
     * Get role permissions mapping
     */
    public static function get_role_permissions() {
        return [
            'administrator' => [
                'label' => __( 'Administrator', 'woo-offers' ),
                'capabilities' => [
                    'manage_woo_offers' => __( 'Full plugin access', 'woo-offers' ),
                    'view_woo_offers' => __( 'View offers', 'woo-offers' ),
                    'create_woo_offers' => __( 'Create offers', 'woo-offers' ),
                    'edit_woo_offers' => __( 'Edit offers', 'woo-offers' ),
                    'delete_woo_offers' => __( 'Delete offers', 'woo-offers' ),
                    'view_woo_offers_analytics' => __( 'View analytics', 'woo-offers' ),
                    'manage_woo_offers_ab_tests' => __( 'Manage A/B tests', 'woo-offers' ),
                    'configure_woo_offers' => __( 'Configure plugin', 'woo-offers' ),
                ]
            ],
            'shop_manager' => [
                'label' => __( 'Shop Manager', 'woo-offers' ),
                'capabilities' => [
                    'view_woo_offers' => __( 'View offers', 'woo-offers' ),
                    'create_woo_offers' => __( 'Create offers', 'woo-offers' ),
                    'edit_woo_offers' => __( 'Edit offers', 'woo-offers' ),
                    'delete_woo_offers' => __( 'Delete offers', 'woo-offers' ),
                    'view_woo_offers_analytics' => __( 'View analytics', 'woo-offers' ),
                    'manage_woo_offers_ab_tests' => __( 'Manage A/B tests', 'woo-offers' ),
                ]
            ],
            'editor' => [
                'label' => __( 'Editor', 'woo-offers' ),
                'capabilities' => [
                    'view_woo_offers' => __( 'View offers', 'woo-offers' ),
                    'view_woo_offers_analytics' => __( 'View analytics', 'woo-offers' ),
                ]
            ],
        ];
    }

    /**
     * Check permission and die if access denied
     */
    public static function check_permission( $capability, $message = '' ) {
        if ( ! current_user_can( $capability ) ) {
            $default_message = __( 'You do not have permission to perform this action.', 'woo-offers' );
            wp_die( $message ?: $default_message );
        }
    }

    /**
     * Check AJAX permission
     */
    public static function check_ajax_permission( $capability, $nonce_action = 'woo_offers_nonce' ) {
        // Check nonce
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', $nonce_action ) ) {
            wp_send_json_error( [
                'message' => __( 'Invalid security token.', 'woo-offers' )
            ] );
        }

        // Check capability
        if ( ! current_user_can( $capability ) ) {
            wp_send_json_error( [
                'message' => __( 'You do not have permission to perform this action.', 'woo-offers' )
            ] );
        }
    }

    /**
     * Check REST API permission
     */
    public static function check_rest_permission( $capability ) {
        return current_user_can( $capability );
    }

    /**
     * Get capability requirement for admin page
     */
    public static function get_page_capability( $page ) {
        $page_caps = [
            'woo-offers' => 'view_woo_offers',
            'woo-offers-offers' => 'view_woo_offers',
            'woo-offers-create' => 'create_woo_offers',
            'woo-offers-analytics' => 'view_woo_offers_analytics',
            'woo-offers-ab-tests' => 'manage_woo_offers_ab_tests',
            'woo-offers-settings' => 'configure_woo_offers',
        ];

        return $page_caps[ $page ] ?? 'manage_woo_offers';
    }

    /**
     * Filter admin menu items based on capabilities
     */
    public static function filter_admin_menu() {
        if ( ! self::can_view_offers() ) {
            remove_menu_page( 'woo-offers' );
            return;
        }

        // Remove submenu items based on capabilities
        if ( ! self::can_create_offers() ) {
            remove_submenu_page( 'woo-offers', 'woo-offers-create' );
        }

        if ( ! self::can_view_analytics() ) {
            remove_submenu_page( 'woo-offers', 'woo-offers-analytics' );
        }

        if ( ! self::can_manage_ab_tests() ) {
            remove_submenu_page( 'woo-offers', 'woo-offers-ab-tests' );
        }

        if ( ! self::can_configure_plugin() ) {
            remove_submenu_page( 'woo-offers', 'woo-offers-settings' );
        }
    }
}
