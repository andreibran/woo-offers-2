<?php
/**
 * Percentage Discount Offer Template
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
$css_classes = ['woo-offer', 'woo-offer-percentage', 'woo-offer-highlight'];

// Background color with default for percentage offers
$bg_color = ! empty( $appearance['background_color'] ) ? $appearance['background_color'] : '#ff6b35';
$styles['background'] = "linear-gradient(135deg, {$bg_color} 0%, " . adjustBrightness($bg_color, -20) . " 100%)";

// Text color
$text_color = ! empty( $appearance['text_color'] ) ? $appearance['text_color'] : '#ffffff';
$styles['color'] = $text_color;

// Accent color for button
$accent_color = ! empty( $appearance['accent_color'] ) ? $appearance['accent_color'] : '#ff4500';

// Border radius for modern look
$border_radius = ! empty( $appearance['border_radius'] ) ? intval( $appearance['border_radius'] ) : 12;
$styles['border-radius'] = $border_radius . 'px';

// Box shadow for depth
$styles['box-shadow'] = '0 8px 25px rgba(0,0,0,0.15)';

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
     data-offer-type="percentage"
     <?php echo $style_string; ?>>
    
    <div class="woo-offer-content">
        
        <!-- Percentage Badge -->
        <div class="percentage-badge">
            <div class="percentage-value">
                <span class="percentage-number"><?php echo esc_html( $offer->value ); ?></span>
                <span class="percentage-symbol">%</span>
            </div>
            <div class="percentage-text"><?php _e( 'OFF', 'woo-offers' ); ?></div>
        </div>

        <!-- Offer Details -->
        <div class="offer-details">
            <h4 class="offer-title"><?php echo esc_html( $offer->name ); ?></h4>
            
            <?php if ( ! empty( $offer->description ) ): ?>
            <p class="offer-description"><?php echo wp_kses_post( $offer->description ); ?></p>
            <?php endif; ?>

            <!-- Savings Calculator -->
            <div class="savings-calculator">
                <?php 
                $current_price = $product->get_price();
                $savings_amount = $current_price * ($offer->value / 100);
                $new_price = $current_price - $savings_amount;
                ?>
                <div class="price-comparison">
                    <span class="original-price"><?php echo wc_price( $current_price ); ?></span>
                    <span class="arrow">→</span>
                    <span class="sale-price"><?php echo wc_price( $new_price ); ?></span>
                </div>
                <div class="savings-amount">
                    <?php printf( __( 'You save: %s', 'woo-offers' ), wc_price( $savings_amount ) ); ?>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <div class="offer-action">
            <button type="button" 
                    class="woo-offer-button apply-percentage-discount" 
                    data-offer-id="<?php echo esc_attr( $offer->id ); ?>"
                    data-percentage="<?php echo esc_attr( $offer->value ); ?>"
                    style="background-color: <?php echo esc_attr( $accent_color ); ?>; border-color: <?php echo esc_attr( $accent_color ); ?>;">
                <span class="button-text"><?php _e( 'Apply Discount', 'woo-offers' ); ?></span>
                <span class="button-savings"><?php printf( __( 'Save %s%%', 'woo-offers' ), esc_html( $offer->value ) ); ?></span>
            </button>
        </div>

        <!-- Offer Conditions -->
        <?php if ( ! empty( $conditions['minimum_amount'] ) || ! empty( $offer->usage_limit ) ): ?>
        <div class="offer-conditions">
            <?php if ( ! empty( $conditions['minimum_amount'] ) ): ?>
                <div class="condition-item">
                    <span class="condition-icon">💰</span>
                    <span class="condition-text">
                        <?php printf( __( 'Min. order: %s', 'woo-offers' ), wc_price( $conditions['minimum_amount'] ) ); ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if ( ! empty( $offer->usage_limit ) ): ?>
                <div class="condition-item">
                    <span class="condition-icon">⏰</span>
                    <span class="condition-text">
                        <?php printf( __( 'Limited offer: %d uses left', 'woo-offers' ), intval( $offer->usage_limit ) ); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Urgency Element -->
        <?php if ( ! empty( $offer->end_date ) ): ?>
        <div class="offer-urgency">
            <span class="urgency-icon">⚡</span>
            <span class="urgency-text">
                <?php 
                $end_date = strtotime( $offer->end_date );
                $days_left = ceil( ( $end_date - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
                
                if ( $days_left <= 1 ) {
                    _e( 'Ending today!', 'woo-offers' );
                } elseif ( $days_left <= 3 ) {
                    printf( __( 'Only %d days left!', 'woo-offers' ), $days_left );
                } else {
                    printf( __( 'Ends %s', 'woo-offers' ), date_i18n( get_option( 'date_format' ), $end_date ) );
                }
                ?>
            </span>
        </div>
        <?php endif; ?>

    </div>
</div>

<style>
.woo-offer-percentage {
    position: relative;
    padding: 20px;
    margin: 15px 0;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.woo-offer-percentage:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.2) !important;
}

.woo-offer-percentage .woo-offer-content {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 20px;
    align-items: center;
}

.percentage-badge {
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    width: 80px;
    height: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.3);
}

.percentage-value {
    display: flex;
    align-items: baseline;
}

.percentage-number {
    font-size: 24px;
    font-weight: 900;
    line-height: 1;
}

.percentage-symbol {
    font-size: 16px;
    font-weight: 700;
    margin-left: 2px;
}

.percentage-text {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1px;
    opacity: 0.9;
}

.offer-details {
    min-width: 0;
}

.offer-title {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: inherit;
}

.offer-description {
    margin: 0 0 12px 0;
    font-size: 14px;
    opacity: 0.9;
    line-height: 1.4;
}

.savings-calculator {
    background: rgba(255,255,255,0.15);
    padding: 10px 12px;
    border-radius: 8px;
    margin-top: 8px;
}

.price-comparison {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}

.original-price {
    text-decoration: line-through;
    opacity: 0.7;
    font-size: 14px;
}

.arrow {
    font-weight: bold;
}

.sale-price {
    font-weight: 700;
    font-size: 16px;
}

.savings-amount {
    font-size: 12px;
    font-weight: 600;
    opacity: 0.9;
}

.woo-offer-button {
    background: #ff4500;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    min-width: 140px;
}

.woo-offer-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.button-text {
    display: block;
    font-size: 14px;
}

.button-savings {
    display: block;
    font-size: 11px;
    opacity: 0.9;
    margin-top: 2px;
}

.offer-conditions {
    grid-column: 1 / -1;
    margin-top: 12px;
    display: flex;
    gap: 15px;
    font-size: 12px;
}

.condition-item {
    display: flex;
    align-items: center;
    gap: 4px;
    opacity: 0.9;
}

.offer-urgency {
    grid-column: 1 / -1;
    margin-top: 8px;
    padding: 6px 10px;
    background: rgba(255,255,255,0.2);
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

@media (max-width: 768px) {
    .woo-offer-percentage .woo-offer-content {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 15px;
    }
    
    .offer-conditions {
        flex-direction: column;
        gap: 8px;
    }
}
</style> 