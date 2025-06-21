<?php
/**
 * Default Offer Template
 * 
 * @package WooOffers
 * @since 2.0.0
 * 
 * Variables available:
 * @var object $offer Offer object from database
 * @var WC_Product $product Current WooCommerce product
 * @var array $conditions Parsed offer conditions
 * @var array $appearance Appearance settings for the offer
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Build inline styles from appearance settings
$styles = [];
$css_classes = ['woo-offer', 'woo-offer-' . esc_attr( $offer->type )];

// Background color
if ( ! empty( $appearance['background_color'] ) ) {
    $styles['background-color'] = esc_attr( $appearance['background_color'] );
}

// Text color
if ( ! empty( $appearance['text_color'] ) ) {
    $styles['color'] = esc_attr( $appearance['text_color'] );
}

// Border styles
if ( ! empty( $appearance['border_style'] ) && $appearance['border_style'] !== 'none' ) {
    $border_width = ! empty( $appearance['border_width'] ) ? intval( $appearance['border_width'] ) : 1;
    $border_color = ! empty( $appearance['border_color'] ) ? esc_attr( $appearance['border_color'] ) : '#dddddd';
    $styles['border'] = sprintf( '%dpx %s %s', $border_width, esc_attr( $appearance['border_style'] ), $border_color );
}

// Border radius
if ( ! empty( $appearance['border_radius'] ) ) {
    $styles['border-radius'] = intval( $appearance['border_radius'] ) . 'px';
}

// Layout specific classes
if ( ! empty( $appearance['layout'] ) ) {
    $css_classes[] = 'woo-offer-layout-' . esc_attr( $appearance['layout'] );
}

// Shadow effects
if ( ! empty( $appearance['shadow'] ) && $appearance['shadow'] !== 'none' ) {
    $css_classes[] = 'woo-offer-shadow-' . esc_attr( $appearance['shadow'] );
}

// Convert styles array to CSS string
$style_string = '';
if ( ! empty( $styles ) ) {
    $style_parts = [];
    foreach ( $styles as $property => $value ) {
        $style_parts[] = $property . ': ' . $value;
    }
    $style_string = 'style="' . implode( '; ', $style_parts ) . '"';
}
?>

<div class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>" 
     data-offer-id="<?php echo esc_attr( $offer->id ); ?>" 
     data-offer-type="<?php echo esc_attr( $offer->type ); ?>"
     <?php echo $style_string; ?>>
    
    <div class="woo-offer-content">
        
        <!-- Offer Title -->
        <div class="woo-offer-header">
            <h4 class="woo-offer-title"><?php echo esc_html( $offer->name ); ?></h4>
            
            <!-- Offer Value Display -->
            <div class="woo-offer-value">
                <?php 
                switch ( $offer->type ) {
                    case 'percentage':
                        echo '<span class="offer-discount">' . esc_html( $offer->value ) . '%</span> ';
                        echo '<span class="offer-text">' . __( 'OFF', 'woo-offers' ) . '</span>';
                        break;
                        
                    case 'fixed':
                        echo '<span class="offer-discount">' . wc_price( $offer->value ) . '</span> ';
                        echo '<span class="offer-text">' . __( 'OFF', 'woo-offers' ) . '</span>';
                        break;
                        
                    case 'bogo':
                        echo '<span class="offer-text">' . __( 'Buy One, Get One FREE!', 'woo-offers' ) . '</span>';
                        break;
                        
                    case 'free_shipping':
                        echo '<span class="offer-text">' . __( 'FREE SHIPPING', 'woo-offers' ) . '</span>';
                        break;
                        
                    case 'bundle':
                        echo '<span class="offer-text">' . __( 'Bundle Deal', 'woo-offers' ) . '</span>';
                        break;
                        
                    case 'quantity':
                        echo '<span class="offer-text">' . sprintf( 
                            __( 'Buy %s+ and save!', 'woo-offers' ), 
                            esc_html( $offer->value )
                        ) . '</span>';
                        break;
                        
                    default:
                        if ( $offer->value > 0 ) {
                            echo '<span class="offer-discount">' . esc_html( $offer->value ) . '</span>';
                        }
                        break;
                }
                ?>
            </div>
        </div>

        <!-- Offer Description -->
        <?php if ( ! empty( $offer->description ) ): ?>
        <div class="woo-offer-description">
            <?php echo wp_kses_post( $offer->description ); ?>
        </div>
        <?php endif; ?>

        <!-- Offer Products (for bundle/BOGO offers) -->
        <?php if ( ! empty( $conditions['products'] ) && in_array( $offer->type, ['bundle', 'bogo'] ) ): ?>
        <div class="woo-offer-products">
            <div class="offer-products-label">
                <?php _e( 'Included Products:', 'woo-offers' ); ?>
            </div>
            <ul class="offer-products-list">
                <?php foreach ( $conditions['products'] as $offer_product ): ?>
                    <?php 
                    $prod = \wc_get_product( $offer_product['id'] );
                    if ( $prod ): 
                    ?>
                    <li class="offer-product-item">
                        <span class="product-name"><?php echo esc_html( $prod->get_name() ?: __( 'Product Name Not Available', 'woo-offers' ) ); ?></span>
                        <?php if ( ! empty( $offer_product['quantity'] ) && $offer_product['quantity'] > 1 ): ?>
                            <span class="product-quantity">(<?php echo intval( $offer_product['quantity'] ); ?>x)</span>
                        <?php endif; ?>
                    </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Call to Action -->
        <div class="woo-offer-actions">
            <?php 
            $accent_color = ! empty( $appearance['accent_color'] ) ? esc_attr( $appearance['accent_color'] ) : '#e92d3b';
            $button_style = 'style="background-color: ' . $accent_color . '; border-color: ' . $accent_color . ';"';
            ?>
            
            <?php if ( $offer->type === 'bundle' && ! empty( $conditions['products'] ) ): ?>
                <!-- Bundle: Add all products to cart -->
                <button type="button" 
                        class="woo-offer-button woo-offer-add-bundle" 
                        data-offer-id="<?php echo esc_attr( $offer->id ); ?>"
                        <?php echo $button_style; ?>>
                    <?php _e( 'Add Bundle to Cart', 'woo-offers' ); ?>
                </button>
            <?php elseif ( $offer->type === 'bogo' ): ?>
                <!-- BOGO: Add current product (will trigger BOGO logic) -->
                <button type="button" 
                        class="woo-offer-button woo-offer-activate" 
                        data-offer-id="<?php echo esc_attr( $offer->id ); ?>"
                        <?php echo $button_style; ?>>
                    <?php _e( 'Activate Offer', 'woo-offers' ); ?>
                </button>
            <?php else: ?>
                <!-- Generic: Apply offer/discount -->
                <button type="button" 
                        class="woo-offer-button woo-offer-apply" 
                        data-offer-id="<?php echo esc_attr( $offer->id ); ?>"
                        <?php echo $button_style; ?>>
                    <?php _e( 'Apply Offer', 'woo-offers' ); ?>
                </button>
            <?php endif; ?>
        </div>

        <!-- Offer Conditions/Limits -->
        <?php if ( ! empty( $conditions['minimum_amount'] ) || ! empty( $conditions['maximum_amount'] ) || ! empty( $offer->usage_limit ) ): ?>
        <div class="woo-offer-conditions">
            <div class="offer-conditions-label"><?php _e( 'Conditions:', 'woo-offers' ); ?></div>
            <ul class="offer-conditions-list">
                <?php if ( ! empty( $conditions['minimum_amount'] ) ): ?>
                    <li><?php printf( __( 'Minimum order: %s', 'woo-offers' ), wc_price( $conditions['minimum_amount'] ) ); ?></li>
                <?php endif; ?>
                
                <?php if ( ! empty( $conditions['maximum_amount'] ) ): ?>
                    <li><?php printf( __( 'Maximum order: %s', 'woo-offers' ), wc_price( $conditions['maximum_amount'] ) ); ?></li>
                <?php endif; ?>
                
                <?php if ( ! empty( $offer->usage_limit ) ): ?>
                    <li><?php printf( __( 'Limited time: %d uses remaining', 'woo-offers' ), intval( $offer->usage_limit ) ); ?></li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Offer Expiry -->
        <?php if ( ! empty( $offer->end_date ) ): ?>
        <div class="woo-offer-expiry">
            <span class="offer-expiry-label"><?php _e( 'Offer expires:', 'woo-offers' ); ?></span>
            <span class="offer-expiry-date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $offer->end_date ) ) ); ?></span>
        </div>
        <?php endif; ?>

    </div>
</div> 