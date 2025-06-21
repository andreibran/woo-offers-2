<?php
/**
 * Campaign Admin Class
 * 
 * Dedicated admin class for campaign management with enhanced security and modular design
 * 
 * @package WooOffers\Admin
 * @since 3.0.0
 */

namespace WooOffers\Admin;

use WooOffers\Core\SecurityManager;
use WooOffers\Campaigns\CampaignManager;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Campaign Admin Management Class
 */
class CampaignAdmin {

    /**
     * Required capability for campaign management
     */
    const REQUIRED_CAPABILITY = 'manage_woocommerce';

    /**
     * Nonce action for campaign operations
     */
    const NONCE_ACTION = 'woo_offers_campaign_admin';

    /**
     * Initialize campaign admin
     */
    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'register_admin_hooks' ] );
        add_action( 'wp_ajax_woo_offers_campaign_admin_action', [ __CLASS__, 'handle_admin_ajax' ] );
        
        // Enhanced security hooks
        add_action( 'admin_init', [ __CLASS__, 'security_audit' ] );
        add_filter( 'wp_die_ajax_handler', [ __CLASS__, 'custom_ajax_error_handler' ], 10, 1 );
    }

    /**
     * Register admin hooks with enhanced security
     */
    public static function register_admin_hooks() {
        // Only initialize for users with proper capabilities
        if ( ! current_user_can( self::REQUIRED_CAPABILITY ) ) {
            return;
        }

        // Register secure AJAX endpoints
        add_action( 'wp_ajax_woo_offers_create_campaign_secure', [ __CLASS__, 'ajax_create_campaign' ] );
        add_action( 'wp_ajax_woo_offers_update_campaign_secure', [ __CLASS__, 'ajax_update_campaign' ] );
        add_action( 'wp_ajax_woo_offers_delete_campaign_secure', [ __CLASS__, 'ajax_delete_campaign' ] );
    }

    /**
     * Enhanced security audit for campaign operations
     */
    public static function security_audit() {
        // Rate limiting check
        $user_id = get_current_user_id();
        if ( $user_id && self::is_rate_limited( $user_id ) ) {
            wp_die( 
                __( 'Too many requests. Please wait before trying again.', 'woo-offers' ),
                __( 'Rate Limited', 'woo-offers' ),
                [ 'response' => 429 ]
            );
        }
    }

    /**
     * Rate limiting check
     */
    private static function is_rate_limited( $user_id ) {
        $transient_key = 'woo_offers_campaign_admin_rate_' . $user_id;
        $request_count = get_transient( $transient_key );
        
        if ( $request_count && $request_count > 30 ) { // 30 requests per minute
            return true;
        }
        
        set_transient( $transient_key, ( $request_count ?: 0 ) + 1, MINUTE_IN_SECONDS );
        return false;
    }

    /**
     * Enhanced AJAX handler for creating campaigns
     */
    public static function ajax_create_campaign() {
        // Security validation
        $security_check = self::validate_ajax_security( 'create_campaign' );
        if ( is_wp_error( $security_check ) ) {
            wp_send_json_error( $security_check->get_error_message() );
            return;
        }

        try {
            // Sanitize and validate input data
            $campaign_data = self::sanitize_campaign_input( $_POST['campaign_data'] ?? [] );
            
            // Create campaign using CampaignManager
            $campaign_id = CampaignManager::create_campaign( $campaign_data );
            
            if ( is_wp_error( $campaign_id ) ) {
                wp_send_json_error( $campaign_id->get_error_message() );
                return;
            }

            wp_send_json_success( [
                'campaign_id' => $campaign_id,
                'message' => __( 'Campaign created successfully.', 'woo-offers' )
            ] );

        } catch ( \Exception $e ) {
            error_log( 'WooOffers Campaign Admin Error: ' . $e->getMessage() );
            wp_send_json_error( __( 'An unexpected error occurred. Please try again.', 'woo-offers' ) );
        }
    }

    /**
     * Enhanced AJAX handler for updating campaigns
     */
    public static function ajax_update_campaign() {
        // Security validation
        $security_check = self::validate_ajax_security( 'update_campaign' );
        if ( is_wp_error( $security_check ) ) {
            wp_send_json_error( $security_check->get_error_message() );
            return;
        }

        try {
            $campaign_id = absint( $_POST['campaign_id'] ?? 0 );
            if ( ! $campaign_id ) {
                wp_send_json_error( __( 'Invalid campaign ID.', 'woo-offers' ) );
                return;
            }

            // Sanitize and validate input data
            $campaign_data = self::sanitize_campaign_input( $_POST['campaign_data'] ?? [] );
            
            // Update campaign using CampaignManager
            $result = CampaignManager::update_campaign( $campaign_id, $campaign_data );
            
            if ( is_wp_error( $result ) ) {
                wp_send_json_error( $result->get_error_message() );
                return;
            }

            wp_send_json_success( [
                'message' => __( 'Campaign updated successfully.', 'woo-offers' ),
                'campaign' => CampaignManager::get_campaign( $campaign_id )
            ] );

        } catch ( \Exception $e ) {
            error_log( 'WooOffers Campaign Admin Error: ' . $e->getMessage() );
            wp_send_json_error( __( 'An unexpected error occurred. Please try again.', 'woo-offers' ) );
        }
    }

    /**
     * Enhanced AJAX handler for deleting campaigns
     */
    public static function ajax_delete_campaign() {
        // Security validation
        $security_check = self::validate_ajax_security( 'delete_campaign' );
        if ( is_wp_error( $security_check ) ) {
            wp_send_json_error( $security_check->get_error_message() );
            return;
        }

        try {
            $campaign_id = absint( $_POST['campaign_id'] ?? 0 );
            if ( ! $campaign_id ) {
                wp_send_json_error( __( 'Invalid campaign ID.', 'woo-offers' ) );
                return;
            }

            // Additional security check for campaign deletion
            if ( ! current_user_can( 'delete_posts' ) ) {
                wp_send_json_error( __( 'You do not have permission to delete campaigns.', 'woo-offers' ) );
                return;
            }

            // Delete campaign using CampaignManager
            $result = CampaignManager::delete_campaign( $campaign_id );
            
            if ( is_wp_error( $result ) ) {
                wp_send_json_error( $result->get_error_message() );
                return;
            }

            wp_send_json_success( [
                'message' => __( 'Campaign deleted successfully.', 'woo-offers' )
            ] );

        } catch ( \Exception $e ) {
            error_log( 'WooOffers Campaign Admin Error: ' . $e->getMessage() );
            wp_send_json_error( __( 'An unexpected error occurred. Please try again.', 'woo-offers' ) );
        }
    }

    /**
     * Validate AJAX security with enhanced checks
     */
    private static function validate_ajax_security( $action ) {
        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return new \WP_Error( 'not_logged_in', __( 'You must be logged in to perform this action.', 'woo-offers' ) );
        }

        // Check user capabilities
        if ( ! current_user_can( self::REQUIRED_CAPABILITY ) ) {
            return new \WP_Error( 'insufficient_permissions', __( 'You do not have permission to perform this action.', 'woo-offers' ) );
        }

        // Verify nonce
        $nonce = $_POST['_wpnonce'] ?? $_POST['nonce'] ?? '';
        if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION . '_' . $action ) ) {
            return new \WP_Error( 'invalid_nonce', __( 'Security check failed. Please refresh the page and try again.', 'woo-offers' ) );
        }

        return true;
    }

    /**
     * Sanitize campaign input data with enhanced validation
     */
    private static function sanitize_campaign_input( $data ) {
        $sanitized = [];

        // String fields
        $string_fields = [ 'name', 'description', 'type', 'status' ];
        foreach ( $string_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $sanitized[ $field ] = sanitize_text_field( $data[ $field ] );
            }
        }

        // Integer fields
        $int_fields = [ 'priority', 'usage_limit' ];
        foreach ( $int_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $sanitized[ $field ] = absint( $data[ $field ] );
            }
        }

        return $sanitized;
    }

    /**
     * Custom AJAX error handler
     */
    public static function custom_ajax_error_handler( $function ) {
        if ( wp_doing_ajax() && strpos( $_REQUEST['action'] ?? '', 'woo_offers_' ) === 0 ) {
            return function( $message ) {
                echo json_encode( [
                    'success' => false,
                    'data' => is_string( $message ) ? $message : __( 'An error occurred.', 'woo-offers' )
                ] );
                wp_die();
            };
        }
        
        return $function;
    }

    /**
     * Get template path using new template structure
     */
    public static function get_template_path( $template_type ) {
        $template_mapping = [
            'campaign-builder' => 'templates/campaigns/campaign-builder.php',
            'campaign-wizard' => 'templates/campaigns/campaign-wizard.php',
            'admin-header' => 'templates/partials/admin-header.php',
            'metric-card' => 'templates/partials/metric-card.php',
            'empty-state' => 'templates/partials/empty-state.php'
        ];

        if ( ! isset( $template_mapping[ $template_type ] ) ) {
            return false;
        }

        return WOO_OFFERS_PLUGIN_PATH . $template_mapping[ $template_type ];
    }

    /**
     * Render template with data
     */
    public static function render_template( $template_type, $data = [] ) {
        $template_path = self::get_template_path( $template_type );
        
        if ( ! $template_path || ! file_exists( $template_path ) ) {
            return false;
        }

        // Extract data for use in template
        extract( $data );

        // Capture template output
        ob_start();
        include $template_path;
        return ob_get_clean();
    }

    /**
     * Enhanced admin page renderer using new template structure
     */
    public static function render_admin_page( $page, $data = [] ) {
        // Use new full page template structure
        $page_template_path = WOO_OFFERS_PLUGIN_PATH . 'templates/pages/full-page-template.php';
        $content_template_path = WOO_OFFERS_PLUGIN_PATH . 'templates/admin/' . $page . '.php';

        // Check if new template structure exists, fallback to old structure
        if ( file_exists( $page_template_path ) && file_exists( $content_template_path ) ) {
            // Set page template variables
            $page_title = $data['page_title'] ?? get_admin_page_title();
            $page_description = $data['page_description'] ?? '';
            $breadcrumbs = $data['breadcrumbs'] ?? [];
            $header_actions = $data['header_actions'] ?? [];
            $content_template = $content_template_path;
            $page_class = $data['page_class'] ?? '';

            // Extract additional data for content template
            extract( $data );

            include $page_template_path;
        } else {
            // Fallback to traditional wrapper approach
            $wrapper_file = WOO_OFFERS_PLUGIN_PATH . 'templates/admin/admin-wrapper.php';
            $template_file = $content_template_path;

            if ( file_exists( $wrapper_file ) ) {
                include $wrapper_file;
            } elseif ( file_exists( $template_file ) ) {
                echo '<div class="wrap"><h1>' . esc_html( get_admin_page_title() ) . '</h1>';
                include $template_file;
                echo '</div>';
            } else {
                echo '<div class="wrap"><h1>' . esc_html( get_admin_page_title() ) . '</h1>';
                echo '<p>' . __( 'Template not found.', 'woo-offers' ) . '</p></div>';
            }
        }
    }

    /**
     * Generate nonce for campaign admin actions
     */
    public static function get_admin_nonce( $action ) {
        return wp_create_nonce( self::NONCE_ACTION . '_' . $action );
    }

    /**
     * Check if current user can manage campaigns
     */
    public static function can_manage_campaigns() {
        return current_user_can( self::REQUIRED_CAPABILITY );
    }
} 