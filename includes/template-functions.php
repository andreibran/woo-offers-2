<?php
/**
 * Template Rendering Functions
 * 
 * Secure template loading functions for Woo Offers
 * 
 * @package WooOffers
 * @since 3.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load a template part with enhanced security
 * 
 * @param string $slug The template slug
 * @param string|null $name Optional template variation name
 * @param array $data Optional data to pass to template
 * @param string $type Template type: 'admin', 'campaigns', 'frontend', 'partials', 'pages'
 * @return bool True if template was loaded, false otherwise
 */
function woo_offers_load_template_part( $slug, $name = null, $data = [], $type = 'partials' ) {
    // Security: Sanitize inputs
    $slug = sanitize_file_name( $slug );
    $name = $name ? sanitize_file_name( $name ) : null;
    $type = sanitize_key( $type );
    
    // Validate template type
    $allowed_types = [ 'admin', 'campaigns', 'frontend', 'partials', 'pages' ];
    if ( ! in_array( $type, $allowed_types, true ) ) {
        $type = 'partials';
    }
    
    // Build template path
    $template_name = $slug;
    if ( $name ) {
        $template_name .= '-' . $name;
    }
    $template_name .= '.php';
    
    // Locate template
    $template_path = woo_offers_locate_template( $template_name, $type );
    
    if ( ! $template_path ) {
        return false;
    }
    
    // Sanitize template data
    $sanitized_data = woo_offers_sanitize_template_data( $data, $slug );
    
    // Extract data for template use
    extract( $sanitized_data );
    
    // Include template
    include $template_path;
    return true;
}

/**
 * Render a campaign template with campaign data
 * 
 * @param int $campaign_id Campaign ID to render
 * @param string $template_type Template type
 * @param array $additional_data Additional data
 * @return string|false Rendered template HTML or false on failure
 */
function woo_offers_render_campaign_template( $campaign_id, $template_type = 'display', $additional_data = [] ) {
    // Security: Validate campaign ID
    $campaign_id = absint( $campaign_id );
    if ( ! $campaign_id ) {
        return false;
    }
    
    // Fetch campaign data
    if ( ! class_exists( 'WooOffers\Campaigns\CampaignManager' ) ) {
        return false;
    }
    
    $campaign = \WooOffers\Campaigns\CampaignManager::get_campaign( $campaign_id );
    if ( ! $campaign ) {
        return false;
    }
    
    // Prepare template data
    $template_data = array_merge( [
        'campaign' => $campaign,
        'campaign_id' => $campaign_id,
        'template_type' => $template_type
    ], $additional_data );
    
    // Determine template path
    $template_slug = "campaign-{$template_type}";
    $template_path = woo_offers_locate_template( $template_slug . '.php', 'campaigns' );
    
    // Fallback to default
    if ( ! $template_path ) {
        $template_path = woo_offers_locate_template( 'offer-default.php', 'frontend' );
    }
    
    if ( ! $template_path ) {
        return false;
    }
    
    // Sanitize template data
    $sanitized_data = woo_offers_sanitize_template_data( $template_data, 'campaign-' . $template_type );
    
    // Capture template output
    ob_start();
    extract( $sanitized_data );
    include $template_path;
    return ob_get_clean();
}

/**
 * Locate template file with secure path resolution
 * 
 * @param string $template_name Template filename
 * @param string $type Template type
 * @return string|false Full path to template or false
 */
function woo_offers_locate_template( $template_name, $type = 'partials' ) {
    // Security: Sanitize inputs
    $template_name = sanitize_file_name( $template_name );
    $type = sanitize_key( $type );
    
    // Define template directories
    $template_directories = [
        'admin' => 'templates/admin/',
        'campaigns' => 'templates/campaigns/',
        'frontend' => 'templates/frontend/',
        'partials' => 'templates/partials/',
        'pages' => 'templates/pages/'
    ];
    
    // Validate template type
    if ( ! isset( $template_directories[ $type ] ) ) {
        return false;
    }
    
    // Build template path
    $template_path = WOO_OFFERS_PLUGIN_PATH . $template_directories[ $type ] . $template_name;
    
    // Check if template exists
    if ( file_exists( $template_path ) && is_readable( $template_path ) ) {
        return $template_path;
    }
    
    return false;
}

/**
 * Sanitize template data
 * 
 * @param array $data Data to sanitize
 * @param string $context Template context
 * @return array Sanitized data
 */
function woo_offers_sanitize_template_data( $data, $context = '' ) {
    if ( ! is_array( $data ) ) {
        return [];
    }
    
    $sanitized = [];
    
    foreach ( $data as $key => $value ) {
        $key = sanitize_key( $key );
        
        if ( is_array( $value ) ) {
            $sanitized[ $key ] = woo_offers_sanitize_template_data( $value, $context );
        } elseif ( is_string( $value ) ) {
            if ( str_ends_with( $key, '_url' ) || $key === 'url' ) {
                $sanitized[ $key ] = esc_url( $value );
            } elseif ( str_ends_with( $key, '_html' ) || $key === 'html' ) {
                $sanitized[ $key ] = wp_kses_post( $value );
            } else {
                $sanitized[ $key ] = sanitize_text_field( $value );
            }
        } elseif ( is_numeric( $value ) ) {
            $sanitized[ $key ] = is_float( $value ) ? floatval( $value ) : intval( $value );
        } else {
            $sanitized[ $key ] = $value;
        }
    }
    
    return $sanitized;
}

/**
 * Campaign shortcode
 */
function woo_offers_campaign_shortcode( $atts ) {
    $atts = shortcode_atts( [
        'id' => 0,
        'type' => 'display'
    ], $atts );
    
    $campaign_id = absint( $atts['id'] );
    if ( ! $campaign_id ) {
        return '';
    }
    
    return woo_offers_render_campaign_template( $campaign_id, $atts['type'] );
}

/**
 * Get template with data and return as string
 * 
 * @param string $slug Template slug
 * @param string|null $name Template variation name
 * @param array $data Data to pass to template
 * @param string $type Template type
 * @return string Template output
 */
function woo_offers_get_template( $slug, $name = null, $data = [], $type = 'partials' ) {
    ob_start();
    woo_offers_load_template_part( $slug, $name, $data, $type );
    return ob_get_clean();
}

/**
 * Display offers list shortcode
 * 
 * @param array $atts Shortcode attributes
 * @return string Offers list template output
 */
function woo_offers_list_shortcode( $atts ) {
    $atts = shortcode_atts( [
        'limit' => 5,
        'type' => '',
        'status' => 'active',
        'class' => ''
    ], $atts, 'woo_offers_list' );
    
    // Get campaigns using CampaignManager
    if ( ! class_exists( 'WooOffers\Campaigns\CampaignManager' ) ) {
        return '';
    }
    
    $campaigns_data = \WooOffers\Campaigns\CampaignManager::get_campaigns( [
        'status' => sanitize_text_field( $atts['status'] ),
        'type' => sanitize_text_field( $atts['type'] ),
        'per_page' => absint( $atts['limit'] )
    ] );
    
    $template_data = [
        'campaigns' => $campaigns_data['campaigns'] ?? [],
        'shortcode_class' => sanitize_html_class( $atts['class'] ),
        'is_shortcode' => true
    ];
    
    return woo_offers_get_template( 'campaigns-list', null, $template_data, 'frontend' );
}

/**
 * Initialize template functions with comprehensive hooks
 */
function woo_offers_init_template_functions() {
    // Register shortcodes
    add_shortcode( 'woo_offers_campaign', 'woo_offers_campaign_shortcode' );
    add_shortcode( 'woo_offers_list', 'woo_offers_list_shortcode' );
    add_shortcode( 'woo_campaign', 'woo_offers_campaign_shortcode' ); // Alias
    add_shortcode( 'woo_offers', 'woo_offers_list_shortcode' ); // Alias
    
    // Register template hooks for frontend integration
    add_action( 'woocommerce_single_product_summary', 'woo_offers_maybe_display_product_campaigns', 25 );
    add_action( 'woocommerce_before_cart', 'woo_offers_maybe_display_cart_campaigns', 10 );
    add_action( 'woocommerce_review_order_before_payment', 'woo_offers_maybe_display_checkout_campaigns', 10 );
    
    // Admin template hooks
    add_action( 'woo_offers_admin_page_header', 'woo_offers_render_admin_header', 10, 1 );
    add_action( 'woo_offers_admin_metric_card', 'woo_offers_render_metric_card', 10, 1 );
    add_action( 'woo_offers_admin_empty_state', 'woo_offers_render_empty_state', 10, 1 );
}

/**
 * Maybe display product page campaigns
 */
function woo_offers_maybe_display_product_campaigns() {
    global $product;
    
    if ( ! $product || ! class_exists( 'WooOffers\Campaigns\CampaignManager' ) ) {
        return;
    }
    
    // Get active product campaigns
    $campaigns = \WooOffers\Campaigns\CampaignManager::get_campaigns( [
        'status' => 'active',
        'type' => 'product_upsell',
        'per_page' => 5
    ] );
    
    if ( empty( $campaigns['campaigns'] ) ) {
        return;
    }
    
    foreach ( $campaigns['campaigns'] as $campaign ) {
        echo woo_offers_render_campaign_template( $campaign->id, 'display', [
            'product' => $product,
            'context' => 'product_page'
        ] );
    }
}

/**
 * Maybe display cart page campaigns
 */
function woo_offers_maybe_display_cart_campaigns() {
    if ( ! class_exists( 'WooOffers\Campaigns\CampaignManager' ) ) {
        return;
    }
    
    // Get active cart campaigns
    $campaigns = \WooOffers\Campaigns\CampaignManager::get_campaigns( [
        'status' => 'active',
        'type' => 'cart_upsell',
        'per_page' => 3
    ] );
    
    if ( empty( $campaigns['campaigns'] ) ) {
        return;
    }
    
    foreach ( $campaigns['campaigns'] as $campaign ) {
        echo woo_offers_render_campaign_template( $campaign->id, 'display', [
            'cart' => WC()->cart,
            'context' => 'cart_page'
        ] );
    }
}

/**
 * Maybe display checkout page campaigns
 */
function woo_offers_maybe_display_checkout_campaigns() {
    if ( ! class_exists( 'WooOffers\Campaigns\CampaignManager' ) ) {
        return;
    }
    
    // Get active checkout campaigns
    $campaigns = \WooOffers\Campaigns\CampaignManager::get_campaigns( [
        'status' => 'active',
        'type' => 'checkout_upsell',
        'per_page' => 2
    ] );
    
    if ( empty( $campaigns['campaigns'] ) ) {
        return;
    }
    
    foreach ( $campaigns['campaigns'] as $campaign ) {
        echo woo_offers_render_campaign_template( $campaign->id, 'display', [
            'checkout' => WC()->checkout(),
            'context' => 'checkout_page'
        ] );
    }
}

/**
 * Render admin header hook
 * 
 * @param array $data Header data
 */
function woo_offers_render_admin_header( $data = [] ) {
    if ( class_exists( 'WooOffers\Admin\TemplateRenderer' ) ) {
        \WooOffers\Admin\TemplateRenderer::render_partial( 'admin-header', $data );
    } else {
        woo_offers_load_template_part( 'admin-header', null, $data, 'partials' );
    }
}

/**
 * Render metric card hook
 * 
 * @param array $data Metric data
 */
function woo_offers_render_metric_card( $data = [] ) {
    if ( class_exists( 'WooOffers\Admin\TemplateRenderer' ) ) {
        \WooOffers\Admin\TemplateRenderer::render_partial( 'metric-card', $data );
    } else {
        woo_offers_load_template_part( 'metric-card', null, $data, 'partials' );
    }
}

/**
 * Render empty state hook
 * 
 * @param array $data Empty state data
 */
function woo_offers_render_empty_state( $data = [] ) {
    if ( class_exists( 'WooOffers\Admin\TemplateRenderer' ) ) {
        \WooOffers\Admin\TemplateRenderer::render_partial( 'empty-state', $data );
    } else {
        woo_offers_load_template_part( 'empty-state', null, $data, 'partials' );
    }
}

/**
 * Check if template exists
 * 
 * @param string $template_name Template name
 * @param string $type Template type
 * @return bool True if template exists
 */
function woo_offers_template_exists( $template_name, $type = 'partials' ) {
    return woo_offers_locate_template( $template_name, $type ) !== false;
}

/**
 * Get available templates by type
 * 
 * @param string $type Template type
 * @return array Available templates
 */
function woo_offers_get_available_templates( $type = 'partials' ) {
    $template_directories = [
        'admin' => 'templates/admin/',
        'campaigns' => 'templates/campaigns/',
        'frontend' => 'templates/frontend/',
        'partials' => 'templates/partials/',
        'pages' => 'templates/pages/'
    ];
    
    if ( ! isset( $template_directories[ $type ] ) ) {
        return [];
    }
    
    $template_dir = WOO_OFFERS_PLUGIN_PATH . $template_directories[ $type ];
    
    if ( ! is_dir( $template_dir ) ) {
        return [];
    }
    
    $templates = [];
    $files = glob( $template_dir . '*.php' );
    
    foreach ( $files as $file ) {
        $template_name = basename( $file, '.php' );
        $templates[] = $template_name;
    }
    
    return $templates;
}

// Initialize on WordPress init
add_action( 'init', 'woo_offers_init_template_functions' ); 