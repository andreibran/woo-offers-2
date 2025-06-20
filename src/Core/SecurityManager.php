<?php
/**
 * Security Manager
 * Comprehensive security framework for Woo Offers
 *
 * @package WooOffers
 * @version 3.0.0
 */

namespace WooOffers\Core;

defined( 'ABSPATH' ) || exit;

/**
 * SecurityManager class
 * 
 * Provides comprehensive security features:
 * - Nonce verification for AJAX calls
 * - Capability verification 
 * - Rate limiting
 * - Input sanitization
 * - Logging and monitoring
 */
class SecurityManager {
    
    /**
     * Rate limiting cache group
     */
    const RATE_LIMIT_GROUP = 'woo_offers_rate_limit';
    
    /**
     * Default rate limits per action type
     */
    const DEFAULT_RATE_LIMITS = [
        'product_search' => [ 'limit' => 30, 'window' => 60 ], // 30 requests per minute
        'save_offer' => [ 'limit' => 10, 'window' => 60 ],     // 10 saves per minute
        'preview_offer' => [ 'limit' => 20, 'window' => 60 ],  // 20 previews per minute
        'default' => [ 'limit' => 60, 'window' => 60 ]         // 60 requests per minute default
    ];

    /**
     * Verify AJAX nonce with comprehensive security checks
     *
     * @param string $action The nonce action
     * @param string $nonce_field The nonce field name (default: 'nonce')
     * @return void
     * @throws \Exception If security verification fails
     */
    public static function verify_ajax_nonce( $action = 'woo_offers_ajax', $nonce_field = 'nonce' ) {
        // Check if AJAX request
        if ( ! wp_doing_ajax() ) {
            self::log_security_event( 'non_ajax_request', [
                'action' => $action,
                'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
            ] );
            wp_send_json_error( [
                'message' => __( 'Invalid request method.', 'woo-offers' ),
                'code' => 'INVALID_REQUEST_METHOD'
            ] );
        }

        // Verify nonce
        if ( ! wp_verify_nonce( $_REQUEST[ $nonce_field ] ?? '', $action ) ) {
            self::log_security_event( 'invalid_nonce', [
                'action' => $action,
                'nonce_field' => $nonce_field,
                'user_id' => get_current_user_id(),
                'ip' => self::get_client_ip()
            ] );
            wp_send_json_error( [
                'message' => __( 'Security verification failed.', 'woo-offers' ),
                'code' => 'INVALID_NONCE'
            ] );
        }
    }

    /**
     * Verify user capability with granular permission checking
     *
     * @param string $capability Required capability (default: 'manage_woocommerce')
     * @return void
     * @throws \Exception If capability verification fails
     */
    public static function verify_capability( $capability = 'manage_woocommerce' ) {
        if ( ! current_user_can( $capability ) ) {
            self::log_security_event( 'insufficient_permissions', [
                'required_capability' => $capability,
                'user_id' => get_current_user_id(),
                'user_capabilities' => wp_get_current_user()->allcaps ?? [],
                'ip' => self::get_client_ip()
            ] );
            wp_send_json_error( [
                'message' => __( 'Insufficient permissions.', 'woo-offers' ),
                'code' => 'INSUFFICIENT_PERMISSIONS'
            ] );
        }
    }

    /**
     * Check rate limits for specific actions
     *
     * @param string $action The action being rate limited
     * @param int $limit Custom limit (optional)
     * @param int $window Custom time window in seconds (optional)
     * @return void
     * @throws \Exception If rate limit exceeded
     */
    public static function check_rate_limit( $action, $limit = null, $window = null ) {
        $rate_config = self::DEFAULT_RATE_LIMITS[ $action ] ?? self::DEFAULT_RATE_LIMITS['default'];
        
        $limit = $limit ?? $rate_config['limit'];
        $window = $window ?? $rate_config['window'];
        
        $user_id = get_current_user_id();
        $ip = self::get_client_ip();
        
        // Create unique key for user + IP + action
        $key = sprintf( 'woo_offers_rate_%s_%d_%s', 
            sanitize_key( $action ), 
            $user_id, 
            md5( $ip ) 
        );
        
        $current_count = get_transient( $key ) ?: 0;
        
        if ( $current_count >= $limit ) {
            self::log_security_event( 'rate_limit_exceeded', [
                'action' => $action,
                'user_id' => $user_id,
                'ip' => $ip,
                'current_count' => $current_count,
                'limit' => $limit,
                'window' => $window
            ] );
            wp_send_json_error( [
                'message' => __( 'Rate limit exceeded. Please try again later.', 'woo-offers' ),
                'code' => 'RATE_LIMIT_EXCEEDED'
            ] );
        }
        
        // Increment counter
        set_transient( $key, $current_count + 1, $window );
    }

    /**
     * Sanitize campaign data with comprehensive validation
     *
     * @param array $data Raw campaign data
     * @return array Sanitized campaign data
     */
    public static function sanitize_campaign_data( $data ) {
        if ( ! is_array( $data ) ) {
            return [];
        }

        return [
            'name' => sanitize_text_field( $data['name'] ?? '' ),
            'description' => wp_kses_post( $data['description'] ?? '' ),
            'type' => self::sanitize_campaign_type( $data['type'] ?? '' ),
            'status' => self::sanitize_status( $data['status'] ?? '' ),
            'settings' => self::sanitize_json_settings( $data['settings'] ?? [] ),
            'targeting_rules' => self::sanitize_targeting_rules( $data['targeting_rules'] ?? [] ),
            'schedule_config' => self::sanitize_schedule_config( $data['schedule_config'] ?? [] ),
            'design_config' => self::sanitize_design_config( $data['design_config'] ?? [] )
        ];
    }

    /**
     * Sanitize campaign type
     *
     * @param string $type Campaign type
     * @return string Sanitized campaign type
     */
    private static function sanitize_campaign_type( $type ) {
        $allowed_types = [
            'checkout_upsell',
            'cart_upsell', 
            'product_upsell',
            'exit_intent',
            'post_purchase'
        ];
        
        return in_array( $type, $allowed_types ) ? $type : 'product_upsell';
    }

    /**
     * Sanitize status
     *
     * @param string $status Status value
     * @return string Sanitized status
     */
    private static function sanitize_status( $status ) {
        $allowed_statuses = [ 'draft', 'active', 'paused', 'expired' ];
        return in_array( $status, $allowed_statuses ) ? $status : 'draft';
    }

    /**
     * Sanitize JSON settings
     *
     * @param array $settings Settings array
     * @return array Sanitized settings
     */
    private static function sanitize_json_settings( $settings ) {
        if ( ! is_array( $settings ) ) {
            return [];
        }
        
        // Add specific sanitization rules for different setting types
        $sanitized = [];
        
        foreach ( $settings as $key => $value ) {
            $key = sanitize_key( $key );
            
            if ( is_string( $value ) ) {
                $sanitized[ $key ] = sanitize_text_field( $value );
            } elseif ( is_numeric( $value ) ) {
                $sanitized[ $key ] = is_float( $value ) ? floatval( $value ) : intval( $value );
            } elseif ( is_array( $value ) ) {
                $sanitized[ $key ] = self::sanitize_json_settings( $value );
            } elseif ( is_bool( $value ) ) {
                $sanitized[ $key ] = (bool) $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize targeting rules
     *
     * @param array $rules Targeting rules
     * @return array Sanitized targeting rules
     */
    private static function sanitize_targeting_rules( $rules ) {
        // Implementation for targeting rules sanitization
        return self::sanitize_json_settings( $rules );
    }

    /**
     * Sanitize schedule config
     *
     * @param array $config Schedule configuration
     * @return array Sanitized schedule config
     */
    private static function sanitize_schedule_config( $config ) {
        if ( ! is_array( $config ) ) {
            return [];
        }
        
        return [
            'start_date' => sanitize_text_field( $config['start_date'] ?? '' ),
            'end_date' => sanitize_text_field( $config['end_date'] ?? '' ),
            'timezone' => sanitize_text_field( $config['timezone'] ?? 'UTC' ),
            'active_days' => array_map( 'intval', $config['active_days'] ?? [] ),
            'active_hours' => [
                'start' => intval( $config['active_hours']['start'] ?? 0 ),
                'end' => intval( $config['active_hours']['end'] ?? 23 )
            ]
        ];
    }

    /**
     * Sanitize design config
     *
     * @param array $config Design configuration
     * @return array Sanitized design config
     */
    private static function sanitize_design_config( $config ) {
        if ( ! is_array( $config ) ) {
            return [];
        }
        
        return [
            'template' => sanitize_key( $config['template'] ?? 'default' ),
            'colors' => [
                'primary' => sanitize_hex_color( $config['colors']['primary'] ?? '#e92d3b' ),
                'secondary' => sanitize_hex_color( $config['colors']['secondary'] ?? '#333333' ),
                'background' => sanitize_hex_color( $config['colors']['background'] ?? '#ffffff' )
            ],
            'typography' => [
                'font_family' => sanitize_text_field( $config['typography']['font_family'] ?? 'inherit' ),
                'font_size' => intval( $config['typography']['font_size'] ?? 14 )
            ],
            'layout' => sanitize_key( $config['layout'] ?? 'default' )
        ];
    }

    /**
     * Get client IP address securely
     *
     * @return string Client IP address
     */
    public static function get_client_ip() {
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // Load balancers/proxies
            'HTTP_X_FORWARDED',          // Load balancers/proxies
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster environments
            'HTTP_CLIENT_IP',            // Some proxies
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ( $headers as $header ) {
            if ( ! empty( $_SERVER[ $header ] ) ) {
                $ips = explode( ',', $_SERVER[ $header ] );
                $ip = trim( $ips[0] );
                
                if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Log security events for monitoring
     *
     * @param string $event Event type
     * @param array $data Event data
     */
    private static function log_security_event( $event, $data = [] ) {
        $log_data = [
            'timestamp' => current_time( 'mysql' ),
            'event' => $event,
            'user_id' => get_current_user_id(),
            'ip' => self::get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'data' => $data
        ];
        
        // Log to WordPress error log
        error_log( 'WooOffers Security Event: ' . wp_json_encode( $log_data ) );
        
        // Store in option for dashboard (keep last 100 events)
        $events = get_option( 'woo_offers_security_events', [] );
        array_unshift( $events, $log_data );
        $events = array_slice( $events, 0, 100 ); // Keep only last 100 events
        update_option( 'woo_offers_security_events', $events );
    }

    /**
     * Get security events for admin dashboard
     *
     * @param int $limit Number of events to retrieve
     * @return array Security events
     */
    public static function get_security_events( $limit = 50 ) {
        $events = get_option( 'woo_offers_security_events', [] );
        return array_slice( $events, 0, $limit );
    }

    /**
     * Clear security events log
     */
    public static function clear_security_events() {
        delete_option( 'woo_offers_security_events' );
    }

    /**
     * Validate and sanitize product search query
     *
     * @param string $query Search query
     * @return string Sanitized query
     * @throws \Exception If query is invalid
     */
    public static function sanitize_product_search_query( $query ) {
        $query = sanitize_text_field( trim( $query ) );
        
        if ( empty( $query ) ) {
            throw new \Exception( __( 'Search query is required.', 'woo-offers' ) );
        }
        
        if ( strlen( $query ) < 2 ) {
            throw new \Exception( __( 'Search query must be at least 2 characters.', 'woo-offers' ) );
        }
        
        if ( strlen( $query ) > 100 ) {
            throw new \Exception( __( 'Search query is too long.', 'woo-offers' ) );
        }
        
        return $query;
    }
} 