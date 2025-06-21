<?php

namespace WooOffers\Core;

/**
 * Assets management
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Assets class for managing scripts and styles
 */
class Assets {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load on WooCommerce pages
        if ( ! $this->should_load_frontend_assets() ) {
            return;
        }

        wp_enqueue_style(
            'woo-offers-frontend',
            self::get_asset_url( 'assets/css/frontend.css' ),
            [],
            $this->get_asset_version( 'assets/css/frontend.css' )
        );

        wp_enqueue_script(
            'woo-offers-frontend',
            self::get_asset_url( 'assets/js/frontend.js' ),
            [ 'jquery', 'wc-add-to-cart' ],
            $this->get_asset_version( 'assets/js/frontend.js' ),
            true
        );

        // Localize frontend script
        wp_localize_script( 'woo-offers-frontend', 'wooOffers', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'restUrl' => rest_url( 'woo-offers/v1/' ),
            'nonce' => wp_create_nonce( 'woo_offers_nonce' ),
            'settings' => $this->get_frontend_settings(),
            'assets' => $this->get_asset_manifest(),
            'strings' => [
                'loading' => __( 'Loading...', 'woo-offers' ),
                'error' => __( 'An error occurred', 'woo-offers' ),
                'addedToCart' => __( 'Added to cart!', 'woo-offers' ),
            ]
        ]);

        // Add inline CSS for custom colors
        $this->add_inline_css();
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on Woo Offers admin pages
        if ( strpos( $hook, 'woo-offers' ) === false ) {
            return;
        }

        // Enqueue WordPress standard admin styles
        wp_enqueue_style( 'wp-admin' );
        wp_enqueue_style( 'colors' );
        wp_enqueue_style( 'common' );
        wp_enqueue_style( 'forms' );
        wp_enqueue_style( 'dashboard' );
        
        // Enqueue our custom admin styles with enhanced versioning
        wp_enqueue_style(
            'woo-offers-admin',
            self::get_asset_url( 'assets/css/admin.css' ),
            [ 'wp-admin', 'colors', 'common', 'forms' ],
            $this->get_asset_version( 'assets/css/admin.css' )
        );

        // Enqueue WordPress standard admin scripts
        wp_enqueue_script( 'common' );
        wp_enqueue_script( 'wp-lists' );
        wp_enqueue_script( 'postbox' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_media(); // For media uploader

        // Special handling for Create/Edit Offer page
        if ( strpos( $hook, 'woo-offers-create' ) !== false ) {
            // Enqueue WooCommerce's enhanced select for product search
            wp_enqueue_script( 'wc-enhanced-select' );
            
            // Enqueue jQuery UI datepicker for date fields
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_style( 'jquery-ui-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css' );

            // ===================== WooCommerce Enhanced Select Fix =====================
            // Localize the WooCommerce enhanced select script with required parameters
            // This is crucial for the product search functionality to work
            $wc_enhanced_select_params = [
                'i18n_matches_1'         => _x( 'One result is available, press enter to select it.', 'enhanced select', 'woocommerce' ),
                'i18n_matches_n'         => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'woocommerce' ),
                'i18n_no_matches'        => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
                'i18n_searching'         => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
                'i18n_load_more'         => _x( 'Load more results', 'enhanced select', 'woocommerce' ),
                'ajax_url'               => admin_url( 'admin-ajax.php' ),
                'search_products_nonce'  => wp_create_nonce( 'search-products' ),
                'search_customers_nonce' => wp_create_nonce( 'search-customers' ),
            ];
            wp_localize_script( 'wc-enhanced-select', 'wc_enhanced_select_params', $wc_enhanced_select_params );
            // ========================================================================
        }
        
        // Enqueue our main admin script with enhanced dependencies
        $admin_dependencies = [ 'jquery', 'common', 'wp-lists', 'postbox', 'jquery-ui-sortable' ];
        
        // Add WooCommerce dependencies if on create offer page
        if ( strpos( $hook, 'woo-offers-create' ) !== false ) {
            $admin_dependencies[] = 'wc-enhanced-select';
            $admin_dependencies[] = 'jquery-ui-datepicker';
        }
        
        wp_enqueue_script(
            'woo-offers-admin',
            self::get_asset_url( 'assets/js/admin.js' ),
            $admin_dependencies,
            $this->get_asset_version( 'assets/js/admin.js' ),
            true
        );

        // Enqueue admin settings script for settings pages
        if ( strpos( $hook, 'woo-offers-settings' ) !== false ) {
            wp_enqueue_script(
                'woo-offers-admin-settings',
                self::get_asset_url( 'assets/js/admin-settings.js' ),
                [ 'jquery', 'woo-offers-admin' ],
                $this->get_asset_version( 'assets/js/admin-settings.js' ),
                true
            );
        }

        // Enqueue analytics tracker for analytics pages
        if ( strpos( $hook, 'woo-offers-analytics' ) !== false ) {
            wp_enqueue_script(
                'woo-offers-analytics-tracker',
                self::get_asset_url( 'assets/js/analytics-tracker.js' ),
                [ 'jquery', 'woo-offers-admin' ],
                $this->get_asset_version( 'assets/js/analytics-tracker.js' ),
                true
            );
            
            // Localize analytics script with specific data
            wp_localize_script( 'woo-offers-analytics-tracker', 'wooOffersAnalytics', [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'woo_offers_analytics_nonce' ),
                'apiUrl' => rest_url( 'woo-offers/v1/analytics/' ),
                'dateRange' => $this->get_analytics_date_range(),
                'strings' => [
                    'loading' => __( 'Loading analytics...', 'woo-offers' ),
                    'error' => __( 'Error loading analytics data', 'woo-offers' ),
                    'noData' => __( 'No data available for the selected period', 'woo-offers' ),
                ]
            ]);
        }

        // Localize main admin script for WordPress-native interface
        wp_localize_script( 'woo-offers-admin', 'wooOffersAdmin', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'woo_offers_nonce' ),
            'pluginUrl' => WOO_OFFERS_PLUGIN_URL,
            'currentPage' => $_GET['page'] ?? '',
            'settings' => get_option( 'woo_offers_settings', [] ),
            'assets' => $this->get_asset_manifest(),
            'strings' => [
                'confirmDelete' => __( 'Are you sure you want to delete this offer?', 'woo-offers' ),
                'confirmBulkDelete' => __( 'Are you sure you want to delete the selected offers?', 'woo-offers' ),
                'offerSaved' => __( 'Offer saved successfully!', 'woo-offers' ),
                'offerDeleted' => __( 'Offer deleted successfully!', 'woo-offers' ),
                'error' => __( 'An error occurred. Please try again.', 'woo-offers' ),
                'selectItems' => __( 'Please select at least one item.', 'woo-offers' ),
                'filtering' => __( 'Filtering...', 'woo-offers' ),
                'filter' => __( 'Filter', 'woo-offers' ),
                'searching' => __( 'Searching...', 'woo-offers' ),
                'noResults' => __( 'No offers found.', 'woo-offers' ),
                'loading' => __( 'Loading...', 'woo-offers' ),
                'save' => __( 'Save', 'woo-offers' ),
                'cancel' => __( 'Cancel', 'woo-offers' ),
                'edit' => __( 'Edit', 'woo-offers' ),
                'delete' => __( 'Delete', 'woo-offers' ),
                'preview' => __( 'Preview', 'woo-offers' ),
            ]
        ]);
    }

    /**
     * Check if frontend assets should be loaded
     */
    private function should_load_frontend_assets() {
        $settings = get_option( 'woo_offers_settings', [] );
        
        // If load scripts everywhere is enabled
        if ( ! empty( $settings['load_scripts_everywhere'] ) ) {
            return true;
        }

        // Only load on specific pages
        return is_shop() || 
               is_product_category() || 
               is_product_tag() || 
               is_product() || 
               is_cart() || 
               is_checkout() || 
               is_account_page();
    }

    /**
     * Get frontend settings for JavaScript
     */
    private function get_frontend_settings() {
        $settings = get_option( 'woo_offers_settings', [] );
        
        return [
            'primary_color' => $settings['primary_color'] ?? '#e92d3b',
            'enable_analytics' => $settings['enable_analytics'] ?? true,
            'enable_animations' => $settings['enable_animations'] ?? true,
            'mobile_optimization' => $settings['enable_mobile_optimization'] ?? true,
        ];
    }

    /**
     * Add inline CSS for customization
     */
    private function add_inline_css() {
        $settings = get_option( 'woo_offers_settings', [] );
        $primary_color = $settings['primary_color'] ?? '#e92d3b';
        
        $custom_css = "
            :root {
                --woo-offers-primary: {$primary_color};
                --woo-offers-primary-rgb: " . $this->hex_to_rgb( $primary_color ) . ";
            }
            
            .woo-offers-container {
                --primary-color: var(--woo-offers-primary);
            }
            
            .woo-offers-button {
                background-color: var(--woo-offers-primary);
            }
            
            .woo-offers-button:hover {
                background-color: rgba(var(--woo-offers-primary-rgb), 0.9);
            }
            
            .woo-offers-badge {
                background-color: var(--woo-offers-primary);
            }
        ";
        
        wp_add_inline_style( 'woo-offers-frontend', $custom_css );
    }

    /**
     * Convert hex color to RGB
     */
    private function hex_to_rgb( $hex ) {
        $hex = str_replace( '#', '', $hex );
        
        if ( strlen( $hex ) === 3 ) {
            $hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . 
                   str_repeat( substr( $hex, 1, 1 ), 2 ) . 
                   str_repeat( substr( $hex, 2, 1 ), 2 );
        }
        
        $rgb = array_map( 'hexdec', str_split( $hex, 2 ) );
        
        return implode( ', ', $rgb );
    }

    /**
     * Get asset URL with cache busting
     */
    public static function get_asset_url( $asset_path ) {
        $full_path = WOO_OFFERS_PLUGIN_DIR . $asset_path;
        $url = WOO_OFFERS_PLUGIN_URL . $asset_path;
        
        if ( file_exists( $full_path ) ) {
            $version = filemtime( $full_path );
            return add_query_arg( 'v', $version, $url );
        }
        
        return $url;
    }

    /**
     * Get asset version with cache busting (instance method)
     */
    private function get_asset_url_instance( $asset_path ) {
        return self::get_asset_url( $asset_path );
    }

    /**
     * Get asset version with cache busting
     */
    private function get_asset_version( $asset_path ) {
        $full_path = WOO_OFFERS_PLUGIN_DIR . $asset_path;
        if ( file_exists( $full_path ) ) {
            return filemtime( $full_path );
        }
        return WOO_OFFERS_VERSION; // Fallback to plugin version if not found
    }

    /**
     * Get asset manifest for JavaScript
     */
    private function get_asset_manifest() {
        return [
            'css' => [
                'woo-offers-frontend' => WOO_OFFERS_PLUGIN_URL . 'assets/css/frontend.css',
                'woo-offers-admin' => WOO_OFFERS_PLUGIN_URL . 'assets/css/admin.css',
            ],
            'js' => [
                'woo-offers-frontend' => WOO_OFFERS_PLUGIN_URL . 'assets/js/frontend.js',
                'woo-offers-admin' => WOO_OFFERS_PLUGIN_URL . 'assets/js/admin.js',
                'woo-offers-admin-settings' => WOO_OFFERS_PLUGIN_URL . 'assets/js/admin-settings.js',
                'woo-offers-analytics-tracker' => WOO_OFFERS_PLUGIN_URL . 'assets/js/analytics-tracker.js',
            ],
        ];
    }

    /**
     * Get analytics date range for JavaScript
     */
    private function get_analytics_date_range() {
        $start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
        $end_date = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];
    }

    /**
     * Get all registered assets
     */
    public static function get_all_assets() {
        return [
            'styles' => [
                'frontend' => [
                    'handle' => 'woo-offers-frontend',
                    'path' => 'assets/css/frontend.css',
                    'dependencies' => [],
                ],
                'admin' => [
                    'handle' => 'woo-offers-admin',
                    'path' => 'assets/css/admin.css',
                    'dependencies' => [ 'wp-admin', 'colors', 'common', 'forms' ],
                ],
            ],
            'scripts' => [
                'frontend' => [
                    'handle' => 'woo-offers-frontend',
                    'path' => 'assets/js/frontend.js',
                    'dependencies' => [ 'jquery', 'wc-add-to-cart' ],
                ],
                'admin' => [
                    'handle' => 'woo-offers-admin',
                    'path' => 'assets/js/admin.js',
                    'dependencies' => [ 'jquery', 'common', 'wp-lists', 'postbox', 'jquery-ui-sortable' ],
                ],
                'admin-settings' => [
                    'handle' => 'woo-offers-admin-settings',
                    'path' => 'assets/js/admin-settings.js',
                    'dependencies' => [ 'jquery', 'woo-offers-admin' ],
                ],
                'analytics-tracker' => [
                    'handle' => 'woo-offers-analytics-tracker',
                    'path' => 'assets/js/analytics-tracker.js',
                    'dependencies' => [ 'jquery', 'woo-offers-admin' ],
                ],
            ],
        ];
    }

    /**
     * Check if asset file exists
     */
    public static function asset_exists( $asset_path ) {
        $full_path = WOO_OFFERS_PLUGIN_DIR . $asset_path;
        return file_exists( $full_path ) && is_readable( $full_path );
    }

    /**
     * Get asset size in bytes
     */
    public static function get_asset_size( $asset_path ) {
        $full_path = WOO_OFFERS_PLUGIN_DIR . $asset_path;
        if ( file_exists( $full_path ) ) {
            return filesize( $full_path );
        }
        return 0;
    }

    /**
     * Preload critical assets
     */
    public function preload_assets() {
        if ( ! $this->should_load_frontend_assets() ) {
            return;
        }

        echo '<link rel="preload" href="' . WOO_OFFERS_PLUGIN_URL . 'assets/css/frontend.css" as="style">';
        echo '<link rel="preload" href="' . WOO_OFFERS_PLUGIN_URL . 'assets/js/frontend.js" as="script">';
    }
}
