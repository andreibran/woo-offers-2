<?php
/**
 * Template Renderer Class
 * 
 * Enhanced template rendering system with security and caching
 * 
 * @package WooOffers\Admin
 * @since 3.0.0
 */

namespace WooOffers\Admin;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template Renderer Management Class
 */
class TemplateRenderer {

    /**
     * Template base paths
     */
    const TEMPLATE_PATHS = [
        'admin' => 'templates/admin/',
        'campaigns' => 'templates/campaigns/',
        'frontend' => 'templates/frontend/',
        'partials' => 'templates/partials/',
        'pages' => 'templates/pages/'
    ];

    /**
     * Initialize template renderer
     */
    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'register_template_hooks' ] );
    }

    /**
     * Register template hooks
     */
    public static function register_template_hooks() {
        // Template rendering filters
        add_filter( 'woo_offers_render_admin_page', [ __CLASS__, 'render_admin_page' ], 10, 3 );
        add_filter( 'woo_offers_render_partial', [ __CLASS__, 'render_partial' ], 10, 3 );
    }

    /**
     * Render admin page using new template structure
     */
    public static function render_admin_page( $page, $data = [], $return = false ) {
        // Sanitize page name
        $page = sanitize_file_name( $page );
        
        // Security check
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have permission to access this page.', 'woo-offers' ) );
            return;
        }

        $content_template_path = self::get_template_path( 'admin', $page );

        if ( ! $content_template_path || ! file_exists( $content_template_path ) ) {
            if ( $return ) {
                return '<div class="notice notice-error"><p>' . __( 'Template not found.', 'woo-offers' ) . '</p></div>';
            } else {
                wp_die( __( 'Template not found.', 'woo-offers' ) );
                return;
            }
        }

        // Extract template data
        extract( $data );

        if ( $return ) {
            ob_start();
            include $content_template_path;
            return ob_get_clean();
        } else {
            include $content_template_path;
        }
    }

    /**
     * Render partial template
     */
    public static function render_partial( $partial, $data = [], $return = false ) {
        $partial_path = self::get_template_path( 'partials', $partial );
        
        if ( ! $partial_path || ! file_exists( $partial_path ) ) {
            if ( $return ) {
                return '';
            }
            return;
        }

        // Extract data for template
        extract( $data );

        if ( $return ) {
            ob_start();
            include $partial_path;
            return ob_get_clean();
        } else {
            include $partial_path;
        }
    }

    /**
     * Get template path with enhanced security
     */
    public static function get_template_path( $type, $template ) {
        // Validate template type
        if ( ! isset( self::TEMPLATE_PATHS[ $type ] ) ) {
            return false;
        }

        // Sanitize template name
        $template = sanitize_file_name( $template );
        
        // Add .php extension if not present
        if ( ! str_ends_with( $template, '.php' ) ) {
            $template .= '.php';
        }

        $template_path = WOO_OFFERS_PLUGIN_PATH . self::TEMPLATE_PATHS[ $type ] . $template;

        return file_exists( $template_path ) ? $template_path : false;
    }

    /**
     * Check if template file exists
     */
    public static function template_exists( $type, $template ) {
        $path = self::get_template_path( $type, $template );
        return $path && is_readable( $path );
    }
} 