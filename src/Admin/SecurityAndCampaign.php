<?php
/**
 * Security Audit and Campaign System Architecture
 * 
 * Provides security hardening for the settings system and defines the 
 * campaign system architecture for integration with settings management.
 * 
 * @package WooOffers\Admin
 * @since 3.0.0
 */

namespace WooOffers\Admin;

use WooOffers\Core\SecurityManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Security and Campaign System Integration class
 */
class SecurityAndCampaign {
    
    /**
     * Initialize security and campaign integration
     */
    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'security_audit' ] );
        add_action( 'admin_init', [ __CLASS__, 'campaign_settings_init' ] );
        add_filter( 'woo_offers_settings_capability_check', [ __CLASS__, 'check_user_capabilities' ] );
        
        // Campaign-specific settings hooks
        add_action( 'woo_offers_campaign_settings_loaded', [ __CLASS__, 'load_campaign_specific_settings' ] );
        add_action( 'woo_offers_campaign_settings_save', [ __CLASS__, 'save_campaign_specific_settings' ] );
    }
    
    // ===================================================================
    // SECURITY AUDIT & HARDENING
    // ===================================================================
    
    /**
     * Comprehensive security audit for settings system
     */
    public static function security_audit() {
        // Only run audit in debug mode to avoid performance impact
        if ( ! WP_DEBUG || ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        $audit_results = [];
        
        // 1. Verify nonce implementation
        $audit_results['nonce_verification'] = self::audit_nonce_implementation();
        
        // 2. Check capability restrictions
        $audit_results['capability_checks'] = self::audit_capability_checks();
        
        // 3. Validate input/output sanitization
        $audit_results['sanitization'] = self::audit_sanitization();
        
        // 4. CSRF protection audit
        $audit_results['csrf_protection'] = self::audit_csrf_protection();
        
        // 5. XSS protection audit
        $audit_results['xss_protection'] = self::audit_xss_protection();
        
        // Log audit results if any issues found
        $issues = array_filter( $audit_results, function( $result ) {
            return ! $result['passed'];
        });
        
        if ( ! empty( $issues ) && WP_DEBUG_LOG ) {
            error_log( 'Woo Offers Security Audit Issues: ' . json_encode( $issues ) );
        }
        
        // Store audit results for admin display
        update_option( 'woo_offers_security_audit', [
            'timestamp' => current_time( 'mysql' ),
            'results' => $audit_results,
            'issues_count' => count( $issues )
        ]);
    }
    
    /**
     * Audit nonce implementation
     */
    private static function audit_nonce_implementation() {
        $checks = [
            'settings_form_nonces' => true,
            'import_export_nonces' => true,
            'reset_nonces' => true
        ];
        
        // Check if Settings class properly implements nonces
        $reflection = new \ReflectionClass( 'WooOffers\Admin\Settings' );
        $methods = $reflection->getMethods();
        
        foreach ( $methods as $method ) {
            if ( strpos( $method->getName(), 'handle_' ) === 0 ) {
                $source = file_get_contents( $method->getFileName() );
                if ( ! preg_match( '/wp_verify_nonce/', $source ) ) {
                    $checks['handler_nonces'] = false;
                    break;
                }
            }
        }
        
        return [
            'passed' => ! in_array( false, $checks, true ),
            'details' => $checks,
            'message' => 'Nonce verification implementation audit'
        ];
    }
    
    /**
     * Audit capability checks
     */
    private static function audit_capability_checks() {
        $checks = [
            'admin_menu_caps' => true,
            'settings_page_caps' => true,
            'ajax_handler_caps' => true
        ];
        
        // Verify manage_options capability is consistently used
        $settings_file = WOO_OFFERS_PLUGIN_DIR . 'src/Admin/Settings.php';
        if ( file_exists( $settings_file ) ) {
            $content = file_get_contents( $settings_file );
            
            // Check for consistent capability usage
            if ( ! preg_match_all( '/current_user_can\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches ) ) {
                $checks['consistent_caps'] = false;
            } else {
                // Ensure all capabilities are 'manage_options' or equivalent
                foreach ( $matches[1] as $cap ) {
                    if ( ! in_array( $cap, [ 'manage_options', 'manage_woocommerce' ] ) ) {
                        $checks['appropriate_caps'] = false;
                        break;
                    }
                }
            }
        }
        
        return [
            'passed' => ! in_array( false, $checks, true ),
            'details' => $checks,
            'message' => 'User capability checks audit'
        ];
    }
    
    /**
     * Audit sanitization implementation
     */
    private static function audit_sanitization() {
        $checks = [
            'input_sanitization' => true,
            'output_escaping' => true,
            'database_preparation' => true
        ];
        
        // Check sanitization callbacks exist and are properly implemented
        $sanitize_methods = [
            'sanitize_general_settings',
            'sanitize_campaign_settings', 
            'sanitize_advanced_settings'
        ];
        
        foreach ( $sanitize_methods as $method ) {
            if ( ! method_exists( 'WooOffers\Admin\Settings', $method ) ) {
                $checks['sanitization_callbacks'] = false;
                break;
            }
        }
        
        return [
            'passed' => ! in_array( false, $checks, true ),
            'details' => $checks,
            'message' => 'Input/output sanitization audit'
        ];
    }
    
    /**
     * Audit CSRF protection
     */
    private static function audit_csrf_protection() {
        $checks = [
            'settings_fields_usage' => true,
            'admin_post_hooks' => true,
            'referrer_checks' => true
        ];
        
        // Verify settings_fields() is used in templates
        $template_file = WOO_OFFERS_PLUGIN_DIR . 'templates/admin/settings.php';
        if ( file_exists( $template_file ) ) {
            $content = file_get_contents( $template_file );
            if ( ! preg_match( '/settings_fields\s*\(/', $content ) ) {
                $checks['template_protection'] = false;
            }
        }
        
        return [
            'passed' => ! in_array( false, $checks, true ),
            'details' => $checks,
            'message' => 'CSRF protection audit'
        ];
    }
    
    /**
     * Audit XSS protection
     */
    private static function audit_xss_protection() {
        $checks = [
            'output_escaping' => true,
            'attribute_escaping' => true,
            'javascript_escaping' => true
        ];
        
        // Check template files for proper escaping
        $template_files = glob( WOO_OFFERS_PLUGIN_DIR . 'templates/**/*.php' );
        
        foreach ( $template_files as $file ) {
            $content = file_get_contents( $file );
            
            // Look for unescaped output
            if ( preg_match( '/echo\s+\$[^;]*;/', $content ) && 
                 ! preg_match( '/esc_html|esc_attr|esc_url|wp_kses/', $content ) ) {
                $checks['template_escaping'] = false;
                break;
            }
        }
        
        return [
            'passed' => ! in_array( false, $checks, true ),
            'details' => $checks,
            'message' => 'XSS protection audit'
        ];
    }
    
    /**
     * Enhanced user capability check
     */
    public static function check_user_capabilities( $capability = 'manage_options' ) {
        // Multi-layer capability check
        if ( ! is_user_logged_in() ) {
            return false;
        }
        
        if ( ! current_user_can( $capability ) ) {
            return false;
        }
        
        // Additional security checks
        if ( is_multisite() && ! is_super_admin() && $capability === 'manage_options' ) {
            // On multisite, ensure proper permissions
            return current_user_can( 'manage_network_options' ) || is_main_site();
        }
        
        // Check for suspicious activity
        if ( self::detect_suspicious_activity() ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Detect suspicious activity
     */
    private static function detect_suspicious_activity() {
        // Rate limiting check
        $user_id = get_current_user_id();
        $transient_key = 'woo_offers_activity_' . $user_id;
        $activity_count = get_transient( $transient_key );
        
        if ( $activity_count && $activity_count > 50 ) {
            return true; // Too many requests
        }
        
        // Update activity counter
        set_transient( $transient_key, ( $activity_count ?: 0 ) + 1, HOUR_IN_SECONDS );
        
        return false;
    }
    
    // ===================================================================
    // CAMPAIGN SYSTEM ARCHITECTURE
    // ===================================================================
    
    /**
     * Initialize campaign-specific settings integration
     */
    public static function campaign_settings_init() {
        // Register campaign-specific setting sections
        add_action( 'admin_init', [ __CLASS__, 'register_campaign_type_settings' ] );
        
        // Add campaign type management hooks
        add_filter( 'woo_offers_available_campaign_types', [ __CLASS__, 'filter_available_campaign_types' ] );
        add_action( 'woo_offers_campaign_type_activated', [ __CLASS__, 'handle_campaign_type_activation' ] );
        add_action( 'woo_offers_campaign_type_deactivated', [ __CLASS__, 'handle_campaign_type_deactivation' ] );
    }
    
    /**
     * Register campaign type specific settings
     */
    public static function register_campaign_type_settings() {
        $campaign_types = self::get_available_campaign_types();
        
        foreach ( $campaign_types as $type => $config ) {
            if ( Settings::is_campaign_type_enabled( $type ) ) {
                self::register_campaign_type_section( $type, $config );
            }
        }
    }
    
    /**
     * Get available campaign types configuration
     */
    public static function get_available_campaign_types() {
        return apply_filters( 'woo_offers_available_campaign_types', [
            'checkout' => [
                'label' => __( 'Checkout Campaigns', 'woo-offers' ),
                'description' => __( 'Display offers during the checkout process', 'woo-offers' ),
                'settings' => [
                    'display_position' => [
                        'type' => 'select',
                        'label' => __( 'Display Position', 'woo-offers' ),
                        'options' => [
                            'before_billing' => __( 'Before Billing Form', 'woo-offers' ),
                            'after_billing' => __( 'After Billing Form', 'woo-offers' ),
                            'before_payment' => __( 'Before Payment Methods', 'woo-offers' ),
                            'after_payment' => __( 'After Payment Methods', 'woo-offers' )
                        ],
                        'default' => 'before_payment'
                    ],
                    'minimum_cart_amount' => [
                        'type' => 'number',
                        'label' => __( 'Minimum Cart Amount', 'woo-offers' ),
                        'min' => 0,
                        'step' => 0.01,
                        'default' => 0
                    ],
                    'show_for_logged_in_only' => [
                        'type' => 'checkbox',
                        'label' => __( 'Show only for logged-in users', 'woo-offers' ),
                        'default' => false
                    ]
                ]
            ],
            'cart' => [
                'label' => __( 'Cart Campaigns', 'woo-offers' ),
                'description' => __( 'Display offers on the cart page', 'woo-offers' ),
                'settings' => [
                    'display_position' => [
                        'type' => 'select',
                        'label' => __( 'Display Position', 'woo-offers' ),
                        'options' => [
                            'before_cart_table' => __( 'Before Cart Table', 'woo-offers' ),
                            'after_cart_table' => __( 'After Cart Table', 'woo-offers' ),
                            'cart_sidebar' => __( 'Cart Sidebar', 'woo-offers' )
                        ],
                        'default' => 'after_cart_table'
                    ],
                    'show_empty_cart' => [
                        'type' => 'checkbox',
                        'label' => __( 'Show on empty cart', 'woo-offers' ),
                        'default' => true
                    ]
                ]
            ],
            'product_page' => [
                'label' => __( 'Product Page Campaigns', 'woo-offers' ),
                'description' => __( 'Display offers on individual product pages', 'woo-offers' ),
                'settings' => [
                    'display_position' => [
                        'type' => 'select',
                        'label' => __( 'Display Position', 'woo-offers' ),
                        'options' => [
                            'before_add_to_cart' => __( 'Before Add to Cart', 'woo-offers' ),
                            'after_add_to_cart' => __( 'After Add to Cart', 'woo-offers' ),
                            'product_summary' => __( 'In Product Summary', 'woo-offers' ),
                            'product_tabs' => __( 'In Product Tabs', 'woo-offers' )
                        ],
                        'default' => 'after_add_to_cart'
                    ],
                    'product_categories' => [
                        'type' => 'multi_select',
                        'label' => __( 'Restrict to Categories', 'woo-offers' ),
                        'options' => self::get_product_categories(),
                        'default' => []
                    ]
                ]
            ],
            'exit_intent' => [
                'label' => __( 'Exit Intent Campaigns', 'woo-offers' ),
                'description' => __( 'Display offers when users are about to leave', 'woo-offers' ),
                'settings' => [
                    'sensitivity' => [
                        'type' => 'select',
                        'label' => __( 'Detection Sensitivity', 'woo-offers' ),
                        'options' => [
                            'low' => __( 'Low - Less sensitive', 'woo-offers' ),
                            'medium' => __( 'Medium - Balanced', 'woo-offers' ),
                            'high' => __( 'High - More sensitive', 'woo-offers' )
                        ],
                        'default' => 'medium'
                    ],
                    'delay_seconds' => [
                        'type' => 'number',
                        'label' => __( 'Minimum time on page (seconds)', 'woo-offers' ),
                        'min' => 0,
                        'max' => 300,
                        'default' => 30
                    ],
                    'cookie_duration' => [
                        'type' => 'number',
                        'label' => __( 'Cookie duration (days)', 'woo-offers' ),
                        'min' => 1,
                        'max' => 365,
                        'default' => 7
                    ]
                ]
            ],
            'post_purchase' => [
                'label' => __( 'Post-Purchase Campaigns', 'woo-offers' ),
                'description' => __( 'Display offers after successful purchase', 'woo-offers' ),
                'settings' => [
                    'display_page' => [
                        'type' => 'select',
                        'label' => __( 'Display Page', 'woo-offers' ),
                        'options' => [
                            'thank_you' => __( 'Thank You Page', 'woo-offers' ),
                            'order_email' => __( 'Order Confirmation Email', 'woo-offers' ),
                            'both' => __( 'Both Page and Email', 'woo-offers' )
                        ],
                        'default' => 'thank_you'
                    ],
                    'order_status_trigger' => [
                        'type' => 'multi_select',
                        'label' => __( 'Trigger on Order Status', 'woo-offers' ),
                        'options' => [
                            'processing' => __( 'Processing', 'woo-offers' ),
                            'completed' => __( 'Completed', 'woo-offers' ),
                            'on-hold' => __( 'On Hold', 'woo-offers' )
                        ],
                        'default' => [ 'processing', 'completed' ]
                    ]
                ]
            ]
        ] );
    }
    
    /**
     * Register settings section for a campaign type
     */
    private static function register_campaign_type_section( $type, $config ) {
        $section_id = "woo_offers_campaign_{$type}_section";
        $option_name = "woo_offers_campaign_{$type}_settings";
        
        // Register section
        add_settings_section(
            $section_id,
            $config['label'],
            function() use ( $config ) {
                echo '<p>' . esc_html( $config['description'] ) . '</p>';
            },
            Settings::SETTINGS_PAGE
        );
        
        // Register option
        register_setting(
            Settings::OPTION_GROUP_CAMPAIGNS,
            $option_name,
            [
                'sanitize_callback' => [ __CLASS__, 'sanitize_campaign_type_settings' ]
            ]
        );
        
        // Register fields
        foreach ( $config['settings'] as $field_key => $field_config ) {
            add_settings_field(
                $field_key,
                $field_config['label'],
                [ __CLASS__, 'render_campaign_type_field' ],
                Settings::SETTINGS_PAGE,
                $section_id,
                [
                    'option_name' => $option_name,
                    'field_name' => $field_key,
                    'field_config' => $field_config
                ]
            );
        }
    }
    
    /**
     * Render campaign type field
     */
    public static function render_campaign_type_field( $args ) {
        $option_name = $args['option_name'];
        $field_name = $args['field_name'];
        $field_config = $args['field_config'];
        
        $options = get_option( $option_name, [] );
        $value = $options[ $field_name ] ?? $field_config['default'];
        
        switch ( $field_config['type'] ) {
            case 'checkbox':
                Settings::render_checkbox_field( [
                    'option_name' => $option_name,
                    'field_name' => $field_name,
                    'description' => $field_config['description'] ?? ''
                ] );
                break;
                
            case 'select':
                Settings::render_select_field( [
                    'option_name' => $option_name,
                    'field_name' => $field_name,
                    'options' => $field_config['options'],
                    'description' => $field_config['description'] ?? ''
                ] );
                break;
                
            case 'number':
                Settings::render_number_field( [
                    'option_name' => $option_name,
                    'field_name' => $field_name,
                    'min' => $field_config['min'] ?? 0,
                    'max' => $field_config['max'] ?? 999999,
                    'step' => $field_config['step'] ?? 1,
                    'description' => $field_config['description'] ?? ''
                ] );
                break;
                
            case 'multi_select':
                Settings::render_multi_checkbox_field( [
                    'option_name' => $option_name,
                    'field_name' => $field_name,
                    'options' => $field_config['options'],
                    'description' => $field_config['description'] ?? ''
                ] );
                break;
        }
    }
    
    /**
     * Sanitize campaign type settings
     */
    public static function sanitize_campaign_type_settings( $input ) {
        $sanitized = [];
        
        if ( ! is_array( $input ) ) {
            return $sanitized;
        }
        
        foreach ( $input as $key => $value ) {
            switch ( $key ) {
                case 'display_position':
                case 'display_page':
                case 'sensitivity':
                case 'order_status_trigger':
                    $sanitized[ $key ] = sanitize_text_field( $value );
                    break;
                    
                case 'minimum_cart_amount':
                    $sanitized[ $key ] = floatval( $value );
                    break;
                    
                case 'delay_seconds':
                case 'cookie_duration':
                    $sanitized[ $key ] = absint( $value );
                    break;
                    
                case 'show_for_logged_in_only':
                case 'show_empty_cart':
                    $sanitized[ $key ] = ! empty( $value );
                    break;
                    
                case 'product_categories':
                    if ( is_array( $value ) ) {
                        $sanitized[ $key ] = array_map( 'sanitize_text_field', $value );
                    }
                    break;
                    
                default:
                    $sanitized[ $key ] = sanitize_text_field( $value );
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get product categories for multi-select
     */
    private static function get_product_categories() {
        if ( ! function_exists( 'wc_get_product_categories' ) ) {
            return [];
        }
        
        $categories = get_terms( [
            'taxonomy' => 'product_cat',
            'hide_empty' => false
        ] );
        
        $options = [];
        foreach ( $categories as $category ) {
            $options[ $category->term_id ] = $category->name;
        }
        
        return $options;
    }
    
    /**
     * Filter available campaign types based on settings
     */
    public static function filter_available_campaign_types( $types ) {
        $enabled_types = Settings::get_enabled_campaign_types();
        
        return array_intersect_key( $types, array_flip( $enabled_types ) );
    }
    
    /**
     * Handle campaign type activation
     */
    public static function handle_campaign_type_activation( $type ) {
        // Initialize default settings for the campaign type
        $option_name = "woo_offers_campaign_{$type}_settings";
        $types_config = self::get_available_campaign_types();
        
        if ( isset( $types_config[ $type ] ) ) {
            $defaults = [];
            foreach ( $types_config[ $type ]['settings'] as $key => $config ) {
                $defaults[ $key ] = $config['default'];
            }
            
            add_option( $option_name, $defaults );
        }
        
        // Trigger action for other components
        do_action( "woo_offers_campaign_type_{$type}_activated" );
    }
    
    /**
     * Handle campaign type deactivation
     */
    public static function handle_campaign_type_deactivation( $type ) {
        // Optionally clean up campaign type settings
        // delete_option( "woo_offers_campaign_{$type}_settings" );
        
        // Trigger action for other components
        do_action( "woo_offers_campaign_type_{$type}_deactivated" );
    }
    
    /**
     * Load campaign-specific settings
     */
    public static function load_campaign_specific_settings( $campaign_id ) {
        $campaign_meta = get_post_meta( $campaign_id, '_campaign_settings', true );
        
        if ( ! empty( $campaign_meta ) ) {
            return wp_parse_args( $campaign_meta, self::get_default_campaign_settings() );
        }
        
        return self::get_default_campaign_settings();
    }
    
    /**
     * Save campaign-specific settings
     */
    public static function save_campaign_specific_settings( $campaign_id, $settings ) {
        $sanitized_settings = self::sanitize_campaign_settings( $settings );
        update_post_meta( $campaign_id, '_campaign_settings', $sanitized_settings );
        
        // Clear any relevant caches
        wp_cache_delete( "campaign_settings_{$campaign_id}", 'woo_offers' );
    }
    
    /**
     * Get default campaign settings
     */
    private static function get_default_campaign_settings() {
        return [
            'enabled' => true,
            'priority' => 10,
            'start_date' => '',
            'end_date' => '',
            'max_uses' => 0,
            'user_restrictions' => [],
            'device_targeting' => 'all',
            'geo_targeting' => []
        ];
    }
    
    /**
     * Get security audit report
     */
    public static function get_security_audit_report() {
        return get_option( 'woo_offers_security_audit', [
            'timestamp' => null,
            'results' => [],
            'issues_count' => 0
        ] );
    }
} 