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
            WOO_OFFERS_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            WOO_OFFERS_VERSION
        );

        wp_enqueue_script(
            'woo-offers-frontend',
            WOO_OFFERS_PLUGIN_URL . 'assets/js/frontend.js',
            [ 'jquery', 'wc-add-to-cart' ],
            WOO_OFFERS_VERSION,
            true
        );

        // Localize frontend script
        wp_localize_script( 'woo-offers-frontend', 'wooOffers', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'restUrl' => rest_url( 'woo-offers/v1/' ),
            'nonce' => wp_create_nonce( 'woo_offers_nonce' ),
            'settings' => $this->get_frontend_settings(),
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
        
        // Enqueue our custom admin styles
        wp_enqueue_style(
            'woo-offers-admin',
            WOO_OFFERS_PLUGIN_URL . 'assets/css/admin.css',
            [ 'wp-admin', 'colors', 'common', 'forms' ],
            WOO_OFFERS_VERSION
        );

        // Enqueue WordPress standard admin scripts
        wp_enqueue_script( 'common' );
        wp_enqueue_script( 'wp-lists' );
        wp_enqueue_script( 'postbox' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_media(); // For media uploader
        
        // Enqueue our custom admin script
        wp_enqueue_script(
            'woo-offers-admin',
            WOO_OFFERS_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery', 'common', 'wp-lists', 'postbox', 'jquery-ui-sortable' ],
            WOO_OFFERS_VERSION,
            true
        );

        // Localize admin script for WordPress-native interface
        wp_localize_script( 'woo-offers-admin', 'wooOffersAdmin', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'woo_offers_nonce' ),
            'pluginUrl' => WOO_OFFERS_PLUGIN_URL,
            'currentPage' => $_GET['page'] ?? '',
            'settings' => get_option( 'woo_offers_settings', [] ),
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
