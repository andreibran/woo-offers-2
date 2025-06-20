<?php
/**
 * Frontend Display System
 *
 * @package WooOffers
 * @since 2.0.0
 */

namespace WooOffers\Frontend;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Frontend Display Class
 * 
 * Handles the display of offers on WooCommerce product pages
 */
class Display {

    /**
     * Initialize the display system
     */
    public static function init() {
        $instance = new self();
        $instance->init_hooks();
    }

    /**
     * Initialize hooks for frontend display
     */
    private function init_hooks() {
        // Hook into WooCommerce product pages
        add_action( 'woocommerce_before_add_to_cart_form', [ $this, 'display_product_offers' ], 15 );
        
        // Add filter for customizing offer display position
        add_filter( 'woo_offers_display_position', [ $this, 'get_display_position' ], 10, 2 );
        
        // Add filter for customizing offer template
        add_filter( 'woo_offers_offer_template', [ $this, 'locate_offer_template' ], 10, 2 );
    }

    /**
     * Display offers on product pages
     */
    public function display_product_offers() {
        global $product;

        // Ensure we have a valid product
        if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
            return;
        }

        // Get applicable offers for this product
        $offers = $this->get_applicable_offers_for_product( $product->get_id() );

        if ( empty( $offers ) ) {
            return;
        }

        // Get display position from settings or offer configuration
        $position = apply_filters( 'woo_offers_display_position', 'before_add_to_cart', $product );

        echo '<div class="woo-offers-container" data-product-id="' . esc_attr( $product->get_id() ) . '">';
        
        foreach ( $offers as $offer ) {
            $this->render_offer( $offer, $product );
        }
        
        echo '</div>';
    }

    /**
     * Get offers applicable to a specific product
     *
     * @param int $product_id Product ID
     * @return array Array of offer objects
     */
    public function get_applicable_offers_for_product( $product_id ) {
        global $wpdb;

        // Get all active offers
        $table_name = $wpdb->prefix . 'woo_offers';
        $offers = $wpdb->get_results( 
            $wpdb->prepare(
                "SELECT * FROM {$table_name} 
                 WHERE status = 'active' 
                 AND (start_date IS NULL OR start_date <= %s)
                 AND (end_date IS NULL OR end_date >= %s)
                 ORDER BY created_at DESC",
                current_time( 'mysql' ),
                current_time( 'mysql' )
            )
        );

        if ( empty( $offers ) ) {
            return [];
        }

        $applicable_offers = [];

        foreach ( $offers as $offer ) {
            // Parse conditions from JSON
            $conditions = json_decode( $offer->conditions, true );
            if ( ! is_array( $conditions ) ) {
                $conditions = [];
            }

            // Check if offer applies to this product
            if ( $this->offer_applies_to_product( $offer, $conditions, $product_id ) ) {
                $offer->parsed_conditions = $conditions;
                $applicable_offers[] = $offer;
            }
        }

        return apply_filters( 'woo_offers_applicable_offers', $applicable_offers, $product_id );
    }

    /**
     * Check if an offer applies to a specific product
     *
     * @param object $offer Offer object from database
     * @param array $conditions Parsed conditions array
     * @param int $product_id Product ID to check
     * @return bool Whether offer applies to product
     */
    private function offer_applies_to_product( $offer, $conditions, $product_id ) {
        // If no specific products are defined, offer applies to all products
        if ( empty( $conditions['products'] ) ) {
            return true;
        }

        // Check if product is in the offer's product list
        foreach ( $conditions['products'] as $offer_product ) {
            if ( intval( $offer_product['id'] ) === intval( $product_id ) ) {
                return true;
            }
        }

        // Check cart conditions (minimum/maximum amounts)
        if ( ! $this->check_cart_conditions( $conditions ) ) {
            return false;
        }

        return false;
    }

    /**
     * Check cart conditions (minimum/maximum amounts)
     *
     * @param array $conditions Offer conditions
     * @return bool Whether cart meets conditions
     */
    private function check_cart_conditions( $conditions ) {
        // If WooCommerce cart is not available, assume conditions are met
        if ( ! WC()->cart ) {
            return true;
        }

        $cart_total = WC()->cart->get_subtotal();

        // Check minimum amount
        if ( ! empty( $conditions['minimum_amount'] ) && $cart_total < floatval( $conditions['minimum_amount'] ) ) {
            return false;
        }

        // Check maximum amount
        if ( ! empty( $conditions['maximum_amount'] ) && $cart_total > floatval( $conditions['maximum_amount'] ) ) {
            return false;
        }

        return true;
    }

    /**
     * Render an individual offer
     *
     * @param object $offer Offer object
     * @param WC_Product $product Current product
     */
    private function render_offer( $offer, $product ) {
        // Get template based on offer type
        $template = $this->locate_offer_template( $offer->type, $offer );
        
        if ( ! $template ) {
            // Fallback to default template
            $template = $this->get_default_template_path();
        }

        // Prepare data for template
        $offer_data = [
            'offer' => $offer,
            'product' => $product,
            'conditions' => $offer->parsed_conditions ?? [],
            'appearance' => $offer->parsed_conditions['appearance'] ?? []
        ];

        // Extract variables for template
        extract( $offer_data );

        // Start output buffering
        ob_start();

        // Include template
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            $this->render_default_offer( $offer_data );
        }

        $output = ob_get_clean();

        // Apply filters to allow customization
        $output = apply_filters( 'woo_offers_render_offer_output', $output, $offer, $product );
        
        echo $output;
    }

    /**
     * Locate template file for specific offer type
     *
     * @param string $offer_type Type of offer
     * @param object $offer Offer object
     * @return string|false Template file path or false if not found
     */
    public function locate_offer_template( $offer_type, $offer ) {
        // Use the new template mapping system
        $template_path = $this->get_offer_template_path( $offer_type );
        
        if ( $template_path && file_exists( $template_path ) ) {
            return $template_path;
        }
        
        // Check theme for override (legacy support)
        $template_name = "offer-{$offer_type}.php";
        $theme_template = locate_template( [
            "woo-offers/{$template_name}",
            "woocommerce/woo-offers/{$template_name}",
            "woo-offers/offer-types/{$offer_type}.php"
        ] );

        if ( $theme_template ) {
            return $theme_template;
        }

        return false;
    }

    /**
     * Get default template path
     *
     * @return string Default template path
     */
    private function get_default_template_path() {
        return WOO_OFFERS_PLUGIN_PATH . 'templates/frontend/offer-default.php';
    }

    /**
     * Render default offer when no specific template is found
     *
     * @param array $offer_data Offer data array
     */
    private function render_default_offer( $offer_data ) {
        $offer = $offer_data['offer'];
        $product = $offer_data['product'];
        $appearance = $offer_data['appearance'];

        // Basic styling from appearance settings
        $styles = [];
        if ( ! empty( $appearance['background_color'] ) ) {
            $styles[] = 'background-color: ' . esc_attr( $appearance['background_color'] );
        }
        if ( ! empty( $appearance['text_color'] ) ) {
            $styles[] = 'color: ' . esc_attr( $appearance['text_color'] );
        }
        if ( ! empty( $appearance['border_color'] ) && ! empty( $appearance['border_style'] ) ) {
            $border_width = ! empty( $appearance['border_width'] ) ? intval( $appearance['border_width'] ) : 1;
            $styles[] = sprintf( 
                'border: %dpx %s %s', 
                $border_width,
                esc_attr( $appearance['border_style'] ),
                esc_attr( $appearance['border_color'] )
            );
        }
        if ( ! empty( $appearance['border_radius'] ) ) {
            $styles[] = 'border-radius: ' . intval( $appearance['border_radius'] ) . 'px';
        }

        $style_attr = ! empty( $styles ) ? 'style="' . implode( '; ', $styles ) . '"' : '';

        echo '<div class="woo-offer woo-offer-' . esc_attr( $offer->type ) . '" ' . $style_attr . '>';
        echo '<div class="woo-offer-content">';
        echo '<h4 class="woo-offer-title">' . esc_html( $offer->name ) . '</h4>';
        
        if ( ! empty( $offer->description ) ) {
            echo '<div class="woo-offer-description">' . wp_kses_post( $offer->description ) . '</div>';
        }

        // Display offer value based on type
        $this->display_offer_value( $offer );

        echo '</div>';
        echo '</div>';
    }

    /**
     * Display offer value based on offer type
     *
     * @param object $offer Offer object
     */
    private function display_offer_value( $offer ) {
        switch ( $offer->type ) {
            case 'percentage':
                echo '<div class="woo-offer-value">';
                echo sprintf( __( '%s%% OFF', 'woo-offers' ), esc_html( $offer->value ) );
                echo '</div>';
                break;
                
            case 'fixed':
                echo '<div class="woo-offer-value">';
                echo sprintf( __( '%s OFF', 'woo-offers' ), wc_price( $offer->value ) );
                echo '</div>';
                break;
                
            case 'bogo':
                echo '<div class="woo-offer-value">';
                echo __( 'Buy One, Get One FREE!', 'woo-offers' );
                echo '</div>';
                break;
                
            case 'free_shipping':
                echo '<div class="woo-offer-value">';
                echo __( 'FREE SHIPPING', 'woo-offers' );
                echo '</div>';
                break;
                
            case 'bundle':
                echo '<div class="woo-offer-value">';
                echo __( 'Bundle Deal Available', 'woo-offers' );
                echo '</div>';
                break;
                
            case 'quantity':
                echo '<div class="woo-offer-value">';
                echo sprintf( 
                    __( 'Buy %s or more and save!', 'woo-offers' ), 
                    esc_html( $offer->value )
                );
                echo '</div>';
                break;
                
            default:
                if ( $offer->value > 0 ) {
                    echo '<div class="woo-offer-value">';
                    echo esc_html( $offer->value );
                    echo '</div>';
                }
                break;
        }
    }

    /**
     * Get display position from settings
     *
     * @param string $default Default position
     * @param WC_Product $product Current product
     * @return string Display position
     */
    public function get_display_position( $default, $product ) {
        // Get from plugin settings or offer-specific settings
        $settings = get_option( 'woo_offers_settings', [] );
        return $settings['default_position'] ?? $default;
    }

    /**
     * Get the appropriate template for an offer type
     */
    private function get_offer_template_path( $offer_type ) {
        // Define template mapping
        $template_map = [
            'percentage' => 'percentage.php',
            'fixed' => 'fixed.php', 
            'bogo' => 'bogo.php',
            'bundle' => 'bundle.php',
            'quantity' => 'quantity.php',
            'free_shipping' => 'free_shipping.php'
        ];
        
        // Get template filename
        $template_file = isset( $template_map[$offer_type] ) ? $template_map[$offer_type] : 'offer-default.php';
        
        // Build full path
        $template_path = WOO_OFFERS_PLUGIN_DIR . 'templates/frontend/offer-types/' . $template_file;
        
        // Check if specific template exists, fallback to default
        if ( ! file_exists( $template_path ) ) {
            $template_path = WOO_OFFERS_PLUGIN_DIR . 'templates/frontend/offer-default.php';
        }
        
        return $template_path;
    }
} 