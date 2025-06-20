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
     * Get client IP address securely
     */
    public static function get_client_ip() {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // Proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_CLIENT_IP',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ( $ip_keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ips = explode( ',', $_SERVER[ $key ] );
                $ip = trim( $ips[0] );
                
                // Validate IP address
                if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                    return $ip;
                }
            }
        }
        
        // Fallback to any valid IP
        foreach ( $ip_keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ips = explode( ',', $_SERVER[ $key ] );
                $ip = trim( $ips[0] );
                
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }
        }
        
        return '127.0.0.1'; // Ultimate fallback
    }
    
    /**
     * Sanitize campaign data for AJAX handlers
     *
     * @param array $data Raw campaign data
     * @return array Sanitized campaign data
     */
    public static function sanitize_campaign_data( $data ) {
        $sanitized = [];
        
        // Basic string fields
        $string_fields = [
            'id', 'name', 'description', 'type', 'status'
        ];
        
        foreach ( $string_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $sanitized[ $field ] = sanitize_text_field( $data[ $field ] );
            }
        }
        
        // Integer fields
        $int_fields = [
            'priority', 'usage_limit'
        ];
        
        foreach ( $int_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $sanitized[ $field ] = (int) $data[ $field ];
            }
        }
        
        // Date fields
        $date_fields = [
            'start_date', 'end_date'
        ];
        
        foreach ( $date_fields as $field ) {
            if ( isset( $data[ $field ] ) && ! empty( $data[ $field ] ) ) {
                $sanitized[ $field ] = sanitize_text_field( $data[ $field ] );
            }
        }
        
        // JSON/Complex fields that need special handling
        $json_fields = [
            'settings', 'targeting_rules', 'schedule_config', 'design_config'
        ];
        
        foreach ( $json_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                if ( is_string( $data[ $field ] ) ) {
                    // Validate JSON string
                    $decoded = json_decode( $data[ $field ], true );
                    if ( json_last_error() === JSON_ERROR_NONE ) {
                        $sanitized[ $field ] = self::sanitize_json_data( $decoded );
                    }
                } elseif ( is_array( $data[ $field ] ) ) {
                    $sanitized[ $field ] = self::sanitize_json_data( $data[ $field ] );
                }
            }
        }
        
        // Selected products array (from the product search)
        if ( isset( $data['selected_products'] ) && is_array( $data['selected_products'] ) ) {
            $sanitized['selected_products'] = [];
            
            foreach ( $data['selected_products'] as $product_id => $product_data ) {
                $product_id = (int) $product_id;
                
                if ( $product_id > 0 && is_array( $product_data ) ) {
                    $sanitized['selected_products'][ $product_id ] = [
                        'id' => $product_id,
                        'name' => sanitize_text_field( $product_data['name'] ?? '' ),
                        'quantity' => max( 1, (int) ( $product_data['quantity'] ?? 1 ) )
                    ];
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Recursively sanitize JSON data
     *
     * @param mixed $data Data to sanitize
     * @return mixed Sanitized data
     */
    private static function sanitize_json_data( $data ) {
        if ( is_array( $data ) ) {
            $sanitized = [];
            
            foreach ( $data as $key => $value ) {
                $clean_key = sanitize_key( $key );
                $sanitized[ $clean_key ] = self::sanitize_json_data( $value );
            }
            
            return $sanitized;
        }
        
        if ( is_string( $data ) ) {
            // For strings, use appropriate sanitization based on context
            if ( filter_var( $data, FILTER_VALIDATE_URL ) ) {
                return esc_url_raw( $data );
            }
            
            if ( filter_var( $data, FILTER_VALIDATE_EMAIL ) ) {
                return sanitize_email( $data );
            }
            
            // Default text sanitization
            return sanitize_text_field( $data );
        }
        
        if ( is_numeric( $data ) ) {
            return is_float( $data ) ? (float) $data : (int) $data;
        }
        
        if ( is_bool( $data ) ) {
            return (bool) $data;
        }
        
        // For other types, convert to string and sanitize
        return sanitize_text_field( (string) $data );
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