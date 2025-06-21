<?php
/**
 * Settings Management for Woo Offers
 * 
 * Handles registration and management of plugin settings using WordPress Settings API.
 * Supports campaign type configurations, general settings, and advanced options.
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
 * Settings class for handling plugin configuration
 */
class Settings {
    
    /**
     * Option group name for general settings
     */
    const OPTION_GROUP_GENERAL = 'woo_offers_general';
    
    /**
     * Option group name for campaign settings
     */
    const OPTION_GROUP_CAMPAIGNS = 'woo_offers_campaigns';
    
    /**
     * Option group name for advanced settings
     */
    const OPTION_GROUP_ADVANCED = 'woo_offers_advanced';
    
    /**
     * Settings page slug
     */
    const SETTINGS_PAGE = 'woo-offers-settings';
    
    /**
     * Default settings values
     */
    private static $default_settings = [
        // General Settings
        'general' => [
            'enable_plugin' => true,
            'debug_mode' => false,
            'cache_enabled' => true,
            'cache_duration' => 3600,
            'load_assets_globally' => false
        ],
        
        // Campaign Type Settings
        'campaigns' => [
            'checkout_enabled' => true,
            'cart_enabled' => true,
            'product_page_enabled' => true,
            'exit_intent_enabled' => true,
            'post_purchase_enabled' => true,
            'max_campaigns_per_page' => 3,
            'campaign_timeout' => 300,
            'analytics_enabled' => true
        ],
        
        // Advanced Settings
        'advanced' => [
            'custom_css' => '',
            'custom_js' => '',
            'rest_api_enabled' => true,
            'webhook_endpoints' => [],
            'performance_mode' => 'balanced',
            'security_level' => 'standard',
            'primary_color' => '#0073aa',
            'secondary_color' => '#e74c3c'
        ]
    ];
    
    /**
     * Initialize the settings system
     */
    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'register_plugin_settings' ] );
        add_action( 'admin_menu', [ __CLASS__, 'add_settings_page' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_settings_assets' ] );
        
        // Import/Export handlers
        add_action( 'admin_post_woo_offers_export_settings', [ __CLASS__, 'handle_settings_export' ] );
        add_action( 'admin_post_woo_offers_import_settings', [ __CLASS__, 'handle_settings_import' ] );
        add_action( 'admin_post_woo_offers_reset_settings', [ __CLASS__, 'handle_settings_reset' ] );
        
        // Initialize default settings if they don't exist
        self::initialize_default_settings();
    }
    
    /**
     * Register all plugin settings
     */
    public static function register_plugin_settings() {
        // Register general settings
        self::register_general_settings();
        
        // Register campaign settings
        self::register_campaign_settings();
        
        // Register advanced settings
        self::register_advanced_settings();
    }
    
    /**
     * Register general settings section
     */
    private static function register_general_settings() {
        // Register setting group
        register_setting(
            self::OPTION_GROUP_GENERAL,
            'woo_offers_general_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [ __CLASS__, 'sanitize_general_settings' ],
                'default' => self::$default_settings['general']
            ]
        );
        
        // Add settings section
        add_settings_section(
            'woo_offers_general_section',
            __( 'General Settings', 'woo-offers' ),
            [ __CLASS__, 'render_general_section_description' ],
            self::SETTINGS_PAGE
        );
        
        // Add individual fields
        add_settings_field(
            'enable_plugin',
            __( 'Enable Plugin', 'woo-offers' ),
            [ __CLASS__, 'render_checkbox_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_general_section',
            [
                'option_name' => 'woo_offers_general_settings',
                'field_name' => 'enable_plugin',
                'description' => __( 'Enable or disable the Woo Offers plugin functionality.', 'woo-offers' )
            ]
        );
        
        add_settings_field(
            'debug_mode',
            __( 'Debug Mode', 'woo-offers' ),
            [ __CLASS__, 'render_checkbox_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_general_section',
            [
                'option_name' => 'woo_offers_general_settings',
                'field_name' => 'debug_mode',
                'description' => __( 'Enable debug mode to log detailed information for troubleshooting.', 'woo-offers' )
            ]
        );
        
        add_settings_field(
            'cache_enabled',
            __( 'Enable Caching', 'woo-offers' ),
            [ __CLASS__, 'render_checkbox_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_general_section',
            [
                'option_name' => 'woo_offers_general_settings',
                'field_name' => 'cache_enabled',
                'description' => __( 'Enable caching to improve performance.', 'woo-offers' )
            ]
        );
        
        add_settings_field(
            'cache_duration',
            __( 'Cache Duration (seconds)', 'woo-offers' ),
            [ __CLASS__, 'render_number_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_general_section',
            [
                'option_name' => 'woo_offers_general_settings',
                'field_name' => 'cache_duration',
                'description' => __( 'How long to cache data in seconds (default: 3600).', 'woo-offers' ),
                'min' => 60,
                'max' => 86400,
                'step' => 60
            ]
        );
        
        add_settings_field(
            'load_assets_globally',
            __( 'Load Assets Globally', 'woo-offers' ),
            [ __CLASS__, 'render_checkbox_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_general_section',
            [
                'option_name' => 'woo_offers_general_settings',
                'field_name' => 'load_assets_globally',
                'description' => __( 'Load plugin CSS and JavaScript on all pages (not recommended for performance).', 'woo-offers' )
            ]
        );
    }
    
    /**
     * Register campaign type settings section
     */
    private static function register_campaign_settings() {
        // Register setting group
        register_setting(
            self::OPTION_GROUP_CAMPAIGNS,
            'woo_offers_campaign_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [ __CLASS__, 'sanitize_campaign_settings' ],
                'default' => self::$default_settings['campaigns']
            ]
        );
        
        // Add settings section
        add_settings_section(
            'woo_offers_campaign_section',
            __( 'Campaign Type Settings', 'woo-offers' ),
            [ __CLASS__, 'render_campaign_section_description' ],
            self::SETTINGS_PAGE
        );
        
        // Campaign type enable/disable fields
        $campaign_types = [
            'checkout_enabled' => __( 'Checkout Campaigns', 'woo-offers' ),
            'cart_enabled' => __( 'Cart Campaigns', 'woo-offers' ),
            'product_page_enabled' => __( 'Product Page Campaigns', 'woo-offers' ),
            'exit_intent_enabled' => __( 'Exit Intent Campaigns', 'woo-offers' ),
            'post_purchase_enabled' => __( 'Post-Purchase Campaigns', 'woo-offers' )
        ];
        
        foreach ( $campaign_types as $field_name => $label ) {
            add_settings_field(
                $field_name,
                $label,
                [ __CLASS__, 'render_checkbox_field' ],
                self::SETTINGS_PAGE,
                'woo_offers_campaign_section',
                [
                    'option_name' => 'woo_offers_campaign_settings',
                    'field_name' => $field_name,
                    'description' => sprintf( __( 'Enable %s functionality.', 'woo-offers' ), strtolower( $label ) )
                ]
            );
        }
        
        // Campaign behavior settings
        add_settings_field(
            'max_campaigns_per_page',
            __( 'Max Campaigns Per Page', 'woo-offers' ),
            [ __CLASS__, 'render_number_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_campaign_section',
            [
                'option_name' => 'woo_offers_campaign_settings',
                'field_name' => 'max_campaigns_per_page',
                'description' => __( 'Maximum number of campaigns to display on a single page.', 'woo-offers' ),
                'min' => 1,
                'max' => 10,
                'step' => 1
            ]
        );
        
        add_settings_field(
            'campaign_timeout',
            __( 'Campaign Timeout (seconds)', 'woo-offers' ),
            [ __CLASS__, 'render_number_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_campaign_section',
            [
                'option_name' => 'woo_offers_campaign_settings',
                'field_name' => 'campaign_timeout',
                'description' => __( 'How long to wait before showing another campaign to the same user.', 'woo-offers' ),
                'min' => 30,
                'max' => 3600,
                'step' => 30
            ]
        );
        
        add_settings_field(
            'analytics_enabled',
            __( 'Enable Analytics', 'woo-offers' ),
            [ __CLASS__, 'render_checkbox_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_campaign_section',
            [
                'option_name' => 'woo_offers_campaign_settings',
                'field_name' => 'analytics_enabled',
                'description' => __( 'Track campaign performance and user interactions.', 'woo-offers' )
            ]
        );
    }
    
    /**
     * Register advanced settings section
     */
    private static function register_advanced_settings() {
        // Register setting group
        register_setting(
            self::OPTION_GROUP_ADVANCED,
            'woo_offers_advanced_settings',
            [
                'type' => 'array',
                'sanitize_callback' => [ __CLASS__, 'sanitize_advanced_settings' ],
                'default' => self::$default_settings['advanced']
            ]
        );
        
        // Add settings section
        add_settings_section(
            'woo_offers_advanced_section',
            __( 'Advanced Settings', 'woo-offers' ),
            [ __CLASS__, 'render_advanced_section_description' ],
            self::SETTINGS_PAGE
        );
        
        // Custom CSS field
        add_settings_field(
            'custom_css',
            __( 'Custom CSS', 'woo-offers' ),
            [ __CLASS__, 'render_textarea_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_advanced_section',
            [
                'option_name' => 'woo_offers_advanced_settings',
                'field_name' => 'custom_css',
                'description' => __( 'Add custom CSS to style your campaigns.', 'woo-offers' ),
                'rows' => 10,
                'class' => 'code'
            ]
        );
        
        // Custom JavaScript field
        add_settings_field(
            'custom_js',
            __( 'Custom JavaScript', 'woo-offers' ),
            [ __CLASS__, 'render_textarea_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_advanced_section',
            [
                'option_name' => 'woo_offers_advanced_settings',
                'field_name' => 'custom_js',
                'description' => __( 'Add custom JavaScript for advanced campaign functionality.', 'woo-offers' ),
                'rows' => 10,
                'class' => 'code'
            ]
        );
        
        // REST API toggle
        add_settings_field(
            'rest_api_enabled',
            __( 'Enable REST API', 'woo-offers' ),
            [ __CLASS__, 'render_checkbox_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_advanced_section',
            [
                'option_name' => 'woo_offers_advanced_settings',
                'field_name' => 'rest_api_enabled',
                'description' => __( 'Enable REST API endpoints for external integrations.', 'woo-offers' )
            ]
        );
        
        // Performance mode
        add_settings_field(
            'performance_mode',
            __( 'Performance Mode', 'woo-offers' ),
            [ __CLASS__, 'render_select_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_advanced_section',
            [
                'option_name' => 'woo_offers_advanced_settings',
                'field_name' => 'performance_mode',
                'description' => __( 'Choose the performance optimization level.', 'woo-offers' ),
                'options' => [
                    'conservative' => __( 'Conservative - Maximum compatibility', 'woo-offers' ),
                    'balanced' => __( 'Balanced - Good performance and compatibility', 'woo-offers' ),
                    'aggressive' => __( 'Aggressive - Maximum performance', 'woo-offers' )
                ]
            ]
        );
        
        // Security level
        add_settings_field(
            'security_level',
            __( 'Security Level', 'woo-offers' ),
            [ __CLASS__, 'render_select_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_advanced_section',
            [
                'option_name' => 'woo_offers_advanced_settings',
                'field_name' => 'security_level',
                'description' => __( 'Choose the security validation level.', 'woo-offers' ),
                'options' => [
                    'basic' => __( 'Basic - Standard security checks', 'woo-offers' ),
                    'standard' => __( 'Standard - Enhanced security validation', 'woo-offers' ),
                    'strict' => __( 'Strict - Maximum security (may affect performance)', 'woo-offers' )
                ]
            ]
        );
        
        // Primary color
        add_settings_field(
            'primary_color',
            __( 'Primary Color', 'woo-offers' ),
            [ __CLASS__, 'render_color_picker_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_advanced_section',
            [
                'option_name' => 'woo_offers_advanced_settings',
                'field_name' => 'primary_color',
                'description' => __( 'Choose the primary color for campaign displays and UI elements.', 'woo-offers' ),
                'default' => '#0073aa'
            ]
        );
        
        // Secondary color
        add_settings_field(
            'secondary_color',
            __( 'Secondary Color', 'woo-offers' ),
            [ __CLASS__, 'render_color_picker_field' ],
            self::SETTINGS_PAGE,
            'woo_offers_advanced_section',
            [
                'option_name' => 'woo_offers_advanced_settings',
                'field_name' => 'secondary_color',
                'description' => __( 'Choose the secondary color for accents and highlights.', 'woo-offers' ),
                'default' => '#e74c3c'
            ]
        );
    }
    
    /**
     * Add settings page to admin menu
     */
    public static function add_settings_page() {
        add_submenu_page(
            'woo-offers',
            __( 'Settings', 'woo-offers' ),
            __( 'Settings', 'woo-offers' ),
            'manage_options',
            self::SETTINGS_PAGE,
            [ __CLASS__, 'render_settings_page' ]
        );
    }
    
    /**
     * Render the main settings page
     */
    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // Handle settings updates
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error(
                'woo_offers_messages',
                'woo_offers_message',
                __( 'Settings saved successfully.', 'woo-offers' ),
                'updated'
            );
        }
        
        settings_errors( 'woo_offers_messages' );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <form action="options.php" method="post">
                <?php
                // Output security fields for the registered setting "wporg"
                settings_fields( self::OPTION_GROUP_GENERAL );
                settings_fields( self::OPTION_GROUP_CAMPAIGNS );
                settings_fields( self::OPTION_GROUP_ADVANCED );
                
                // Output setting sections and their fields
                do_settings_sections( self::SETTINGS_PAGE );
                
                // Output save settings button
                submit_button( __( 'Save Settings', 'woo-offers' ) );
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Enqueue settings page assets
     */
    public static function enqueue_settings_assets( $hook ) {
        if ( 'woo-offers_page_' . self::SETTINGS_PAGE !== $hook ) {
            return;
        }
        
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        
        wp_enqueue_script(
            'woo-offers-settings',
            WOO_OFFERS_URL . 'assets/js/admin.js',
            [ 'jquery', 'wp-color-picker' ],
            WOO_OFFERS_VERSION,
            true
        );
    }
    
    /**
     * Initialize default settings if they don't exist
     */
    private static function initialize_default_settings() {
        if ( false === get_option( 'woo_offers_general_settings' ) ) {
            add_option( 'woo_offers_general_settings', self::$default_settings['general'] );
        }
        
        if ( false === get_option( 'woo_offers_campaign_settings' ) ) {
            add_option( 'woo_offers_campaign_settings', self::$default_settings['campaigns'] );
        }
        
        if ( false === get_option( 'woo_offers_advanced_settings' ) ) {
            add_option( 'woo_offers_advanced_settings', self::$default_settings['advanced'] );
        }
    }
    
    // ===================================================================
    // SANITIZATION CALLBACKS
    // ===================================================================
    
    /**
     * Sanitize general settings
     */
    public static function sanitize_general_settings( $input ) {
        $sanitized = [];
        
        $sanitized['enable_plugin'] = ! empty( $input['enable_plugin'] );
        $sanitized['debug_mode'] = ! empty( $input['debug_mode'] );
        $sanitized['cache_enabled'] = ! empty( $input['cache_enabled'] );
        $sanitized['cache_duration'] = absint( $input['cache_duration'] ?? 3600 );
        $sanitized['load_assets_globally'] = ! empty( $input['load_assets_globally'] );
        
        // Validate cache duration
        if ( $sanitized['cache_duration'] < 60 ) {
            $sanitized['cache_duration'] = 60;
        }
        if ( $sanitized['cache_duration'] > 86400 ) {
            $sanitized['cache_duration'] = 86400;
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize campaign settings
     */
    public static function sanitize_campaign_settings( $input ) {
        $sanitized = [];
        
        // Campaign type toggles
        $sanitized['checkout_enabled'] = ! empty( $input['checkout_enabled'] );
        $sanitized['cart_enabled'] = ! empty( $input['cart_enabled'] );
        $sanitized['product_page_enabled'] = ! empty( $input['product_page_enabled'] );
        $sanitized['exit_intent_enabled'] = ! empty( $input['exit_intent_enabled'] );
        $sanitized['post_purchase_enabled'] = ! empty( $input['post_purchase_enabled'] );
        
        // Numeric settings
        $sanitized['max_campaigns_per_page'] = absint( $input['max_campaigns_per_page'] ?? 3 );
        $sanitized['campaign_timeout'] = absint( $input['campaign_timeout'] ?? 300 );
        
        // Boolean settings
        $sanitized['analytics_enabled'] = ! empty( $input['analytics_enabled'] );
        
        // Validate numeric ranges
        if ( $sanitized['max_campaigns_per_page'] < 1 ) {
            $sanitized['max_campaigns_per_page'] = 1;
        }
        if ( $sanitized['max_campaigns_per_page'] > 10 ) {
            $sanitized['max_campaigns_per_page'] = 10;
        }
        
        if ( $sanitized['campaign_timeout'] < 30 ) {
            $sanitized['campaign_timeout'] = 30;
        }
        if ( $sanitized['campaign_timeout'] > 3600 ) {
            $sanitized['campaign_timeout'] = 3600;
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize advanced settings
     */
    public static function sanitize_advanced_settings( $input ) {
        $sanitized = [];
        
        // Text fields that allow CSS/JS
        $sanitized['custom_css'] = wp_strip_all_tags( $input['custom_css'] ?? '' );
        $sanitized['custom_js'] = wp_strip_all_tags( $input['custom_js'] ?? '' );
        
        // Boolean settings
        $sanitized['rest_api_enabled'] = ! empty( $input['rest_api_enabled'] );
        
        // Validate select options
        $valid_performance_modes = [ 'conservative', 'balanced', 'aggressive' ];
        $sanitized['performance_mode'] = in_array( $input['performance_mode'] ?? '', $valid_performance_modes ) 
            ? $input['performance_mode'] 
            : 'balanced';
        
        $valid_security_levels = [ 'basic', 'standard', 'strict' ];
        $sanitized['security_level'] = in_array( $input['security_level'] ?? '', $valid_security_levels ) 
            ? $input['security_level'] 
            : 'standard';
        
        // Color fields
        $sanitized['primary_color'] = sanitize_hex_color( $input['primary_color'] ?? '#0073aa' );
        $sanitized['secondary_color'] = sanitize_hex_color( $input['secondary_color'] ?? '#e74c3c' );
        
        // Ensure color values are valid hex colors
        if ( ! $sanitized['primary_color'] ) {
            $sanitized['primary_color'] = '#0073aa';
        }
        if ( ! $sanitized['secondary_color'] ) {
            $sanitized['secondary_color'] = '#e74c3c';
        }
        
        // Webhook endpoints (for future use)
        $sanitized['webhook_endpoints'] = [];
        if ( ! empty( $input['webhook_endpoints'] ) && is_array( $input['webhook_endpoints'] ) ) {
            foreach ( $input['webhook_endpoints'] as $endpoint ) {
                if ( filter_var( $endpoint, FILTER_VALIDATE_URL ) ) {
                    $sanitized['webhook_endpoints'][] = esc_url_raw( $endpoint );
                }
            }
        }
        
        return $sanitized;
    }
    
    // ===================================================================
    // FIELD RENDERERS
    // ===================================================================
    
    /**
     * Render checkbox field
     */
    public static function render_checkbox_field( $args ) {
        $option_name = $args['option_name'];
        $field_name = $args['field_name'];
        $description = $args['description'] ?? '';
        
        $options = get_option( $option_name, [] );
        $value = $options[ $field_name ] ?? false;
        
        printf(
            '<input type="checkbox" id="%1$s_%2$s" name="%1$s[%2$s]" value="1" %3$s />',
            esc_attr( $option_name ),
            esc_attr( $field_name ),
            checked( 1, $value, false )
        );
        
        if ( $description ) {
            printf(
                '<p class="description">%s</p>',
                esc_html( $description )
            );
        }
    }
    
    /**
     * Render text field
     */
    public static function render_text_field( $args ) {
        $option_name = $args['option_name'];
        $field_name = $args['field_name'];
        $description = $args['description'] ?? '';
        $placeholder = $args['placeholder'] ?? '';
        $class = $args['class'] ?? 'regular-text';
        
        $options = get_option( $option_name, [] );
        $value = $options[ $field_name ] ?? '';
        
        printf(
            '<input type="text" id="%1$s_%2$s" name="%1$s[%2$s]" value="%3$s" class="%4$s" placeholder="%5$s" />',
            esc_attr( $option_name ),
            esc_attr( $field_name ),
            esc_attr( $value ),
            esc_attr( $class ),
            esc_attr( $placeholder )
        );
        
        if ( $description ) {
            printf(
                '<p class="description">%s</p>',
                esc_html( $description )
            );
        }
    }
    
    /**
     * Render number field
     */
    public static function render_number_field( $args ) {
        $option_name = $args['option_name'];
        $field_name = $args['field_name'];
        $description = $args['description'] ?? '';
        $min = $args['min'] ?? 0;
        $max = $args['max'] ?? 999999;
        $step = $args['step'] ?? 1;
        
        $options = get_option( $option_name, [] );
        $value = $options[ $field_name ] ?? $min;
        
        printf(
            '<input type="number" id="%1$s_%2$s" name="%1$s[%2$s]" value="%3$s" min="%4$s" max="%5$s" step="%6$s" class="small-text" />',
            esc_attr( $option_name ),
            esc_attr( $field_name ),
            esc_attr( $value ),
            esc_attr( $min ),
            esc_attr( $max ),
            esc_attr( $step )
        );
        
        if ( $description ) {
            printf(
                '<p class="description">%s</p>',
                esc_html( $description )
            );
        }
    }
    
    /**
     * Render textarea field
     */
    public static function render_textarea_field( $args ) {
        $option_name = $args['option_name'];
        $field_name = $args['field_name'];
        $description = $args['description'] ?? '';
        $rows = $args['rows'] ?? 5;
        $class = $args['class'] ?? 'large-text';
        
        $options = get_option( $option_name, [] );
        $value = $options[ $field_name ] ?? '';
        
        printf(
            '<textarea id="%1$s_%2$s" name="%1$s[%2$s]" rows="%3$s" class="%4$s">%5$s</textarea>',
            esc_attr( $option_name ),
            esc_attr( $field_name ),
            esc_attr( $rows ),
            esc_attr( $class ),
            esc_textarea( $value )
        );
        
        if ( $description ) {
            printf(
                '<p class="description">%s</p>',
                esc_html( $description )
            );
        }
    }
    
    /**
     * Render select field
     */
    public static function render_select_field( $args ) {
        $option_name = $args['option_name'];
        $field_name = $args['field_name'];
        $description = $args['description'] ?? '';
        $options_list = $args['options'] ?? [];
        
        $options = get_option( $option_name, [] );
        $value = $options[ $field_name ] ?? '';
        
        printf(
            '<select id="%1$s_%2$s" name="%1$s[%2$s]">',
            esc_attr( $option_name ),
            esc_attr( $field_name )
        );
        
        foreach ( $options_list as $option_value => $option_label ) {
            printf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr( $option_value ),
                selected( $value, $option_value, false ),
                esc_html( $option_label )
            );
        }
        
        echo '</select>';
        
        if ( $description ) {
            printf(
                '<p class="description">%s</p>',
                esc_html( $description )
            );
        }
    }
    
    /**
     * Render color picker field
     */
    public static function render_color_picker_field( $args ) {
        $option_name = $args['option_name'];
        $field_name = $args['field_name'];
        $description = $args['description'] ?? '';
        $default = $args['default'] ?? '#000000';
        
        $options = get_option( $option_name, [] );
        $value = $options[ $field_name ] ?? $default;
        
        printf(
            '<input type="text" id="%1$s_%2$s" name="%1$s[%2$s]" value="%3$s" class="wp-color-picker" data-default-color="%4$s" />',
            esc_attr( $option_name ),
            esc_attr( $field_name ),
            esc_attr( $value ),
            esc_attr( $default )
        );
        
        if ( $description ) {
            printf(
                '<p class="description">%s</p>',
                esc_html( $description )
            );
        }
    }
    
    /**
     * Render file upload field
     */
    public static function render_file_upload_field( $args ) {
        $option_name = $args['option_name'];
        $field_name = $args['field_name'];
        $description = $args['description'] ?? '';
        $accept = $args['accept'] ?? '';
        $button_text = $args['button_text'] ?? __( 'Choose File', 'woo-offers' );
        
        $options = get_option( $option_name, [] );
        $value = $options[ $field_name ] ?? '';
        
        printf(
            '<div class="woo-offers-file-upload-field">'
        );
        
        printf(
            '<input type="hidden" id="%1$s_%2$s" name="%1$s[%2$s]" value="%3$s" />',
            esc_attr( $option_name ),
            esc_attr( $field_name ),
            esc_attr( $value )
        );
        
        printf(
            '<button type="button" class="button upload-button" data-field="%1$s_%2$s" data-accept="%3$s">%4$s</button>',
            esc_attr( $option_name ),
            esc_attr( $field_name ),
            esc_attr( $accept ),
            esc_html( $button_text )
        );
        
        printf(
            '<span class="file-preview" id="preview_%1$s_%2$s">%3$s</span>',
            esc_attr( $option_name ),
            esc_attr( $field_name ),
            $value ? esc_html( basename( $value ) ) : esc_html__( 'No file selected', 'woo-offers' )
        );
        
        if ( $description ) {
            printf(
                '<p class="description">%s</p>',
                esc_html( $description )
            );
        }
        
        echo '</div>';
    }
    
    /**
     * Render multi-checkbox field
     */
    public static function render_multi_checkbox_field( $args ) {
        $option_name = $args['option_name'];
        $field_name = $args['field_name'];
        $description = $args['description'] ?? '';
        $options_list = $args['options'] ?? [];
        
        $options = get_option( $option_name, [] );
        $values = $options[ $field_name ] ?? [];
        
        if ( ! is_array( $values ) ) {
            $values = [];
        }
        
        echo '<fieldset>';
        
        foreach ( $options_list as $option_value => $option_label ) {
            printf(
                '<label><input type="checkbox" name="%1$s[%2$s][]" value="%3$s" %4$s /> %5$s</label><br>',
                esc_attr( $option_name ),
                esc_attr( $field_name ),
                esc_attr( $option_value ),
                checked( in_array( $option_value, $values ), true, false ),
                esc_html( $option_label )
            );
        }
        
        echo '</fieldset>';
        
        if ( $description ) {
            printf(
                '<p class="description">%s</p>',
                esc_html( $description )
            );
        }
    }
    
    /**
     * Render radio button field
     */
    public static function render_radio_field( $args ) {
        $option_name = $args['option_name'];
        $field_name = $args['field_name'];
        $description = $args['description'] ?? '';
        $options_list = $args['options'] ?? [];
        $default = $args['default'] ?? '';
        
        $options = get_option( $option_name, [] );
        $value = $options[ $field_name ] ?? $default;
        
        echo '<fieldset>';
        
        foreach ( $options_list as $option_value => $option_label ) {
            printf(
                '<label><input type="radio" name="%1$s[%2$s]" value="%3$s" %4$s /> %5$s</label><br>',
                esc_attr( $option_name ),
                esc_attr( $field_name ),
                esc_attr( $option_value ),
                checked( $value, $option_value, false ),
                esc_html( $option_label )
            );
        }
        
        echo '</fieldset>';
        
        if ( $description ) {
            printf(
                '<p class="description">%s</p>',
                esc_html( $description )
            );
        }
    }
    
    // ===================================================================
    // SECTION DESCRIPTIONS
    // ===================================================================
    
    /**
     * Render general section description
     */
    public static function render_general_section_description() {
        echo '<p>' . esc_html__( 'Configure general plugin settings and behavior.', 'woo-offers' ) . '</p>';
    }
    
    /**
     * Render campaign section description
     */
    public static function render_campaign_section_description() {
        echo '<p>' . esc_html__( 'Configure campaign types and their behavior settings.', 'woo-offers' ) . '</p>';
    }
    
    /**
     * Render advanced section description
     */
    public static function render_advanced_section_description() {
        echo '<p>' . esc_html__( 'Advanced settings for developers and power users.', 'woo-offers' ) . '</p>';
    }
    
    // ===================================================================
    // UTILITY METHODS
    // ===================================================================
    
    /**
     * Get a specific setting value
     */
    public static function get_setting( $group, $key, $default = null ) {
        $option_name = "woo_offers_{$group}_settings";
        $options = get_option( $option_name, [] );
        
        return $options[ $key ] ?? $default;
    }
    
    /**
     * Update a specific setting value
     */
    public static function update_setting( $group, $key, $value ) {
        $option_name = "woo_offers_{$group}_settings";
        $options = get_option( $option_name, [] );
        $options[ $key ] = $value;
        
        return update_option( $option_name, $options );
    }
    
    /**
     * Get all settings for a group
     */
    public static function get_settings( $group ) {
        $option_name = "woo_offers_{$group}_settings";
        return get_option( $option_name, self::$default_settings[ $group ] ?? [] );
    }
    
    /**
     * Check if a campaign type is enabled
     */
    public static function is_campaign_type_enabled( $type ) {
        $field_name = $type . '_enabled';
        return self::get_setting( 'campaigns', $field_name, true );
    }
    
    /**
     * Get enabled campaign types
     */
    public static function get_enabled_campaign_types() {
        $types = [ 'checkout', 'cart', 'product_page', 'exit_intent', 'post_purchase' ];
        $enabled = [];
        
        foreach ( $types as $type ) {
            if ( self::is_campaign_type_enabled( $type ) ) {
                $enabled[] = $type;
            }
        }
        
        return $enabled;
    }
    
    // ===================================================================
    // IMPORT/EXPORT HANDLERS
    // ===================================================================
    
    /**
     * Handle settings export
     */
    public static function handle_settings_export() {
        // Verify nonce and capabilities
        if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'woo_offers_export_settings' ) ||
             ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Security check failed.', 'woo-offers' ) );
        }
        
        // Collect all settings
        $settings = [
            'general' => get_option( 'woo_offers_general_settings', [] ),
            'campaigns' => get_option( 'woo_offers_campaign_settings', [] ),
            'advanced' => get_option( 'woo_offers_advanced_settings', [] ),
            'export_info' => [
                'timestamp' => current_time( 'mysql' ),
                'version' => WOO_OFFERS_VERSION,
                'site_url' => home_url(),
                'wp_version' => get_bloginfo( 'version' )
            ]
        ];
        
        // Generate filename
        $filename = 'woo-offers-settings-' . date( 'Y-m-d-H-i-s' ) . '.json';
        
        // Set headers for file download
        header( 'Content-Type: application/json' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
        
        // Output JSON
        echo json_encode( $settings, JSON_PRETTY_PRINT );
        exit;
    }
    
    /**
     * Handle settings import
     */
    public static function handle_settings_import() {
        // Verify nonce and capabilities
        if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'woo_offers_import_settings' ) ||
             ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Security check failed.', 'woo-offers' ) );
        }
        
        $redirect_url = admin_url( 'admin.php?page=' . self::SETTINGS_PAGE );
        
        try {
            // Handle file upload
            if ( ! empty( $_FILES['settings_file']['tmp_name'] ) ) {
                $file_content = file_get_contents( $_FILES['settings_file']['tmp_name'] );
                
                // Validate file size (5MB max)
                if ( $_FILES['settings_file']['size'] > 5 * 1024 * 1024 ) {
                    throw new Exception( __( 'File size too large. Maximum 5MB allowed.', 'woo-offers' ) );
                }
                
            } elseif ( ! empty( $_POST['settings_json'] ) ) {
                // Handle JSON paste
                $file_content = sanitize_textarea_field( $_POST['settings_json'] );
                
            } else {
                throw new Exception( __( 'No import data provided.', 'woo-offers' ) );
            }
            
            // Parse JSON
            $imported_settings = json_decode( $file_content, true );
            
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                throw new Exception( __( 'Invalid JSON format.', 'woo-offers' ) );
            }
            
            // Validate structure
            if ( ! is_array( $imported_settings ) ) {
                throw new Exception( __( 'Invalid settings structure.', 'woo-offers' ) );
            }
            
            // Import settings with validation
            if ( isset( $imported_settings['general'] ) && is_array( $imported_settings['general'] ) ) {
                $sanitized = self::sanitize_general_settings( $imported_settings['general'] );
                update_option( 'woo_offers_general_settings', $sanitized );
            }
            
            if ( isset( $imported_settings['campaigns'] ) && is_array( $imported_settings['campaigns'] ) ) {
                $sanitized = self::sanitize_campaign_settings( $imported_settings['campaigns'] );
                update_option( 'woo_offers_campaign_settings', $sanitized );
            }
            
            if ( isset( $imported_settings['advanced'] ) && is_array( $imported_settings['advanced'] ) ) {
                $sanitized = self::sanitize_advanced_settings( $imported_settings['advanced'] );
                update_option( 'woo_offers_advanced_settings', $sanitized );
            }
            
            // Success message
            $redirect_url = add_query_arg( [
                'settings-updated' => 'true',
                'import' => 'success'
            ], $redirect_url );
            
        } catch ( Exception $e ) {
            // Error message
            $redirect_url = add_query_arg( [
                'import' => 'error',
                'message' => urlencode( $e->getMessage() )
            ], $redirect_url );
        }
        
        wp_redirect( $redirect_url );
        exit;
    }
    
    /**
     * Handle settings reset
     */
    public static function handle_settings_reset() {
        // Verify nonce and capabilities
        if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'woo_offers_reset_settings' ) ||
             ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Security check failed.', 'woo-offers' ) );
        }
        
        // Reset to defaults
        update_option( 'woo_offers_general_settings', self::$default_settings['general'] );
        update_option( 'woo_offers_campaign_settings', self::$default_settings['campaigns'] );
        update_option( 'woo_offers_advanced_settings', self::$default_settings['advanced'] );
        
        // Redirect with success message
        $redirect_url = admin_url( 'admin.php?page=' . self::SETTINGS_PAGE );
        $redirect_url = add_query_arg( [
            'settings-updated' => 'true',
            'reset' => 'success'
        ], $redirect_url );
        
        wp_redirect( $redirect_url );
        exit;
    }
    
    /**
     * Export settings to JSON
     */
    public static function export_settings_to_json() {
        return json_encode( [
            'general' => get_option( 'woo_offers_general_settings', [] ),
            'campaigns' => get_option( 'woo_offers_campaign_settings', [] ),
            'advanced' => get_option( 'woo_offers_advanced_settings', [] ),
            'export_info' => [
                'timestamp' => current_time( 'mysql' ),
                'version' => WOO_OFFERS_VERSION,
                'site_url' => home_url()
            ]
        ], JSON_PRETTY_PRINT );
    }
    
    /**
     * Validate plugin settings
     */
    public static function validate_plugin_settings( $settings ) {
        $errors = [];
        
        // Validate general settings
        if ( isset( $settings['general'] ) ) {
            $general = $settings['general'];
            
            if ( isset( $general['cache_duration'] ) ) {
                $duration = absint( $general['cache_duration'] );
                if ( $duration < 60 || $duration > 86400 ) {
                    $errors[] = __( 'Cache duration must be between 60 and 86400 seconds.', 'woo-offers' );
                }
            }
        }
        
        // Validate campaign settings
        if ( isset( $settings['campaigns'] ) ) {
            $campaigns = $settings['campaigns'];
            
            if ( isset( $campaigns['max_campaigns_per_page'] ) ) {
                $max_campaigns = absint( $campaigns['max_campaigns_per_page'] );
                if ( $max_campaigns < 1 || $max_campaigns > 10 ) {
                    $errors[] = __( 'Max campaigns per page must be between 1 and 10.', 'woo-offers' );
                }
            }
            
            if ( isset( $campaigns['campaign_timeout'] ) ) {
                $timeout = absint( $campaigns['campaign_timeout'] );
                if ( $timeout < 30 || $timeout > 3600 ) {
                    $errors[] = __( 'Campaign timeout must be between 30 and 3600 seconds.', 'woo-offers' );
                }
            }
        }
        
        // Validate advanced settings
        if ( isset( $settings['advanced'] ) ) {
            $advanced = $settings['advanced'];
            
            if ( isset( $advanced['primary_color'] ) ) {
                if ( ! sanitize_hex_color( $advanced['primary_color'] ) ) {
                    $errors[] = __( 'Primary color must be a valid hex color.', 'woo-offers' );
                }
            }
            
            if ( isset( $advanced['secondary_color'] ) ) {
                if ( ! sanitize_hex_color( $advanced['secondary_color'] ) ) {
                    $errors[] = __( 'Secondary color must be a valid hex color.', 'woo-offers' );
                }
            }
        }
        
        return empty( $errors ) ? true : $errors;
    }
}