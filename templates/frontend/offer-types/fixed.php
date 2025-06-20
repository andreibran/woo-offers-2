<?php
/**
 * Fixed Discount Offer Template
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

// Build styles from appearance settings
$styles = [];
$css_classes = ['woo-offer', 'woo-offer-fixed', 'woo-offer-value'];

// Background color with default for fixed offers
$bg_color = ! empty( $appearance['background_color'] ) ? $appearance['background_color'] : '#2ecc71';
$styles['background'] = "linear-gradient(135deg, {$bg_color} 0%, " . adjustBrightness($bg_color, -15) . " 100%)";

// Text color
$text_color = ! empty( $appearance['text_color'] ) ? $appearance['text_color'] : '#ffffff';
$styles['color'] = $text_color;

// Accent color for button
$accent_color = ! empty( $appearance['accent_color'] ) ? $appearance['accent_color'] : '#27ae60';

// Border radius
$border_radius = ! empty( $appearance['border_radius'] ) ? intval( $appearance['border_radius'] ) : 10;
$styles['border-radius'] = $border_radius . 'px';

// Box shadow for modern look
$styles['box-shadow'] = '0 6px 20px rgba(0,0,0,0.12)';

// Convert styles to CSS string
$style_string = '';
if ( ! empty( $styles ) ) {
    $style_parts = [];
    foreach ( $styles as $property => $value ) {
        $style_parts[] = $property . ': ' . $value;
    }
    $style_string = 'style="' . implode( '; ', $style_parts ) . '"';
}

/**
 * Helper function to adjust color brightness
 */
function adjustBrightness($hex, $percent) {
    $hex = ltrim($hex, '#');
    
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    
    $hex = array_map('hexdec', str_split($hex, 2));
    
    foreach ($hex as & $color) {
        $adjustableLimit = $percent < 0 ? $color : 255 - $color;
        $adjustAmount = ceil($adjustableLimit * $percent / 100);
        $color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
    }
    
    return '#' . implode($hex);
}
?>

<div class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>" 
     data-offer-id="<?php echo esc_attr( $offer->id ); ?>" 
     data-offer-type="fixed"
     <?php echo $style_string; ?>>
    
    <div class="woo-offer-content">
        
        <!-- Fixed Discount Badge -->
        <div class="fixed-discount-badge">
            <div class="currency-symbol"><?php echo get_woocommerce_currency_symbol(); ?></div>
            <div class="discount-amount"><?php echo number_format( $offer->value, 2 ); ?></div>
            <div class="discount-text"><?php _e( 'OFF', 'woo-offers' ); ?></div>
        </div>

        <!-- Offer Details -->
        <div class="offer-details">
            <h4 class="offer-title"><?php echo esc_html( $offer->name ); ?></h4>
            
            <?php if ( ! empty( $offer->description ) ): ?>
            <p class="offer-description"><?php echo wp_kses_post( $offer->description ); ?></p>
            <?php endif; ?>

            <!-- Price Display -->
            <div class="price-display">
                <?php 
                $current_price = $product->get_price();
                $new_price = max( 0, $current_price - $offer->value );
                ?>
                <div class="price-row">
                    <span class="price-label"><?php _e( 'Regular Price:', 'woo-offers' ); ?></span>
                    <span class="regular-price"><?php echo wc_price( $current_price ); ?></span>
                </div>
                <div class="price-row highlight">
                    <span class="price-label"><?php _e( 'Your Price:', 'woo-offers' ); ?></span>
                    <span class="sale-price"><?php echo wc_price( $new_price ); ?></span>
                </div>
                <div class="savings-badge">
                    <span class="savings-text"><?php _e( 'You Save', 'woo-offers' ); ?></span>
                    <span class="savings-value"><?php echo wc_price( $offer->value ); ?></span>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <div class="offer-action">
            <button type="button" 
                    class="woo-offer-button apply-fixed-discount" 
                    data-offer-id="<?php echo esc_attr( $offer->id ); ?>"
                    data-discount-amount="<?php echo esc_attr( $offer->value ); ?>"
                    style="background-color: <?php echo esc_attr( $accent_color ); ?>; border-color: <?php echo esc_attr( $accent_color ); ?>;">
                <span class="button-icon">💰</span>
                <span class="button-content">
                    <span class="button-text"><?php _e( 'Get Discount', 'woo-offers' ); ?></span>
                    <span class="button-amount"><?php echo wc_price( $offer->value ); ?> <?php _e( 'off', 'woo-offers' ); ?></span>
                </span>
            </button>
        </div>

        <!-- Offer Conditions -->
        <?php if ( ! empty( $conditions['minimum_amount'] ) || ! empty( $offer->usage_limit ) ): ?>
        <div class="offer-conditions">
            <?php if ( ! empty( $conditions['minimum_amount'] ) ): ?>
                <div class="condition-item">
                    <span class="condition-icon">🛒</span>
                    <span class="condition-text">
                        <?php printf( __( 'Minimum purchase: %s', 'woo-offers' ), wc_price( $conditions['minimum_amount'] ) ); ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if ( ! empty( $offer->usage_limit ) ): ?>
                <div class="condition-item">
                    <span class="condition-icon">🏷️</span>
                    <span class="condition-text">
                        <?php printf( __( 'Limited time: %d remaining', 'woo-offers' ), intval( $offer->usage_limit ) ); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Validity Period -->
        <?php if ( ! empty( $offer->end_date ) ): ?>
        <div class="offer-validity">
            <span class="validity-icon">⏳</span>
            <span class="validity-text">
                <?php 
                $end_date = strtotime( $offer->end_date );
                $hours_left = ceil( ( $end_date - current_time( 'timestamp' ) ) / HOUR_IN_SECONDS );
                
                if ( $hours_left <= 12 ) {
                    printf( __( 'Expires in %d hours', 'woo-offers' ), $hours_left );
                } else {
                    printf( __( 'Valid until %s', 'woo-offers' ), date_i18n( get_option( 'date_format' ), $end_date ) );
                }
                ?>
            </span>
        </div>
        <?php endif; ?>

    </div>
</div>

<style>
.woo-offer-fixed {
    position: relative;
    padding: 18px;
    margin: 15px 0;
    border-left: 4px solid rgba(255,255,255,0.3);
    transition: all 0.3s ease;
}

.woo-offer-fixed:hover {
    transform: translateX(4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.woo-offer-fixed .woo-offer-content {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 18px;
    align-items: center;
}

.fixed-discount-badge {
    background: rgba(255,255,255,0.25);
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    min-width: 90px;
    border: 1px solid rgba(255,255,255,0.3);
    backdrop-filter: blur(5px);
}

.currency-symbol {
    font-size: 14px;
    font-weight: 600;
    opacity: 0.8;
    margin-bottom: 2px;
}

.discount-amount {
    font-size: 22px;
    font-weight: 900;
    line-height: 1;
    margin-bottom: 2px;
}

.discount-text {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1px;
    opacity: 0.9;
}

.offer-details {
    min-width: 0;
}

.offer-title {
    margin: 0 0 8px 0;
    font-size: 17px;
    font-weight: 600;
    color: inherit;
}

.offer-description {
    margin: 0 0 12px 0;
    font-size: 14px;
    opacity: 0.9;
    line-height: 1.4;
}

.price-display {
    background: rgba(255,255,255,0.1);
    padding: 12px;
    border-radius: 6px;
    font-size: 14px;
}

.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.price-row.highlight {
    font-weight: 600;
    font-size: 15px;
    border-top: 1px solid rgba(255,255,255,0.2);
    padding-top: 8px;
    margin-top: 8px;
    margin-bottom: 8px;
}

.price-label {
    opacity: 0.9;
}

.regular-price {
    text-decoration: line-through;
    opacity: 0.7;
}

.sale-price {
    font-weight: 700;
}

.savings-badge {
    background: rgba(255,255,255,0.2);
    padding: 4px 8px;
    border-radius: 4px;
    text-align: center;
    font-size: 12px;
    margin-top: 8px;
}

.savings-text {
    opacity: 0.9;
}

.savings-value {
    font-weight: 700;
    margin-left: 4px;
}

.woo-offer-button {
    background: #27ae60;
    color: white;
    border: none;
    padding: 14px 18px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 160px;
}

.woo-offer-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.button-icon {
    font-size: 18px;
}

.button-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.button-text {
    font-size: 14px;
    line-height: 1;
}

.button-amount {
    font-size: 11px;
    opacity: 0.9;
    margin-top: 2px;
}

.offer-conditions {
    grid-column: 1 / -1;
    margin-top: 12px;
    display: flex;
    gap: 20px;
    font-size: 12px;
}

.condition-item {
    display: flex;
    align-items: center;
    gap: 5px;
    opacity: 0.9;
}

.offer-validity {
    grid-column: 1 / -1;
    margin-top: 8px;
    padding: 8px 12px;
    background: rgba(255,255,255,0.15);
    border-radius: 5px;
    font-size: 12px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
    border: 1px solid rgba(255,255,255,0.2);
}

@media (max-width: 768px) {
    .woo-offer-fixed .woo-offer-content {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 15px;
    }
    
    .offer-conditions {
        flex-direction: column;
        gap: 8px;
        align-items: center;
    }
    
    .woo-offer-button {
        justify-content: center;
    }
}
</style> 