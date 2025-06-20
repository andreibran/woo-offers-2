<?php
/**
 * BOGO (Buy One Get One) Offer Template
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
$css_classes = ['woo-offer', 'woo-offer-bogo', 'woo-offer-special'];

// Background color with default for BOGO offers
$bg_color = ! empty( $appearance['background_color'] ) ? $appearance['background_color'] : '#e74c3c';
$styles['background'] = "linear-gradient(135deg, {$bg_color} 0%, " . adjustBrightness($bg_color, -20) . " 100%)";

// Text color
$text_color = ! empty( $appearance['text_color'] ) ? $appearance['text_color'] : '#ffffff';
$styles['color'] = $text_color;

// Accent color for button
$accent_color = ! empty( $appearance['accent_color'] ) ? $appearance['accent_color'] : '#c0392b';

// Border radius
$border_radius = ! empty( $appearance['border_radius'] ) ? intval( $appearance['border_radius'] ) : 15;
$styles['border-radius'] = $border_radius . 'px';

// Special BOGO styling
$styles['box-shadow'] = '0 10px 30px rgba(0,0,0,0.2)';
$styles['border'] = '2px solid rgba(255,255,255,0.2)';

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

// Parse BOGO parameters
$bogo_buy_qty = ! empty( $offer->value ) ? intval( $offer->value ) : 1;
$bogo_get_qty = 1; // Default: Buy X, Get 1 Free
$bogo_discount = 100; // Default: 100% off (free)

// Check if custom BOGO settings exist in offer data
if ( ! empty( $offer->data ) ) {
    $offer_data = is_string( $offer->data ) ? json_decode( $offer->data, true ) : $offer->data;
    $bogo_get_qty = ! empty( $offer_data['get_quantity'] ) ? intval( $offer_data['get_quantity'] ) : 1;
    $bogo_discount = ! empty( $offer_data['discount_percent'] ) ? intval( $offer_data['discount_percent'] ) : 100;
}
?>

<div class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>" 
     data-offer-id="<?php echo esc_attr( $offer->id ); ?>" 
     data-offer-type="bogo"
     data-buy-qty="<?php echo esc_attr( $bogo_buy_qty ); ?>"
     data-get-qty="<?php echo esc_attr( $bogo_get_qty ); ?>"
     data-discount="<?php echo esc_attr( $bogo_discount ); ?>"
     <?php echo $style_string; ?>>
    
    <!-- Decorative Elements -->
    <div class="bogo-decorations">
        <div class="decoration-star">⭐</div>
        <div class="decoration-flash">⚡</div>
        <div class="decoration-gift">🎁</div>
    </div>
    
    <div class="woo-offer-content">
        
        <!-- BOGO Badge -->
        <div class="bogo-badge">
            <div class="bogo-main">
                <span class="bogo-buy">
                    <?php _e( 'BUY', 'woo-offers' ); ?>
                    <strong><?php echo $bogo_buy_qty; ?></strong>
                </span>
                <span class="bogo-separator">+</span>
                <span class="bogo-get">
                    <?php _e( 'GET', 'woo-offers' ); ?>
                    <strong><?php echo $bogo_get_qty; ?></strong>
                </span>
            </div>
            <div class="bogo-benefit">
                <?php 
                if ( $bogo_discount >= 100 ) {
                    _e( 'FREE!', 'woo-offers' );
                } else {
                    printf( __( '%d%% OFF!', 'woo-offers' ), $bogo_discount );
                }
                ?>
            </div>
        </div>

        <!-- Offer Details -->
        <div class="offer-details">
            <h4 class="offer-title"><?php echo esc_html( $offer->name ); ?></h4>
            
            <?php if ( ! empty( $offer->description ) ): ?>
            <p class="offer-description"><?php echo wp_kses_post( $offer->description ); ?></p>
            <?php endif; ?>

            <!-- BOGO Explanation -->
            <div class="bogo-explanation">
                <div class="explanation-header">
                    <span class="explanation-icon">🛍️</span>
                    <span class="explanation-title"><?php _e( 'How it works:', 'woo-offers' ); ?></span>
                </div>
                <div class="explanation-steps">
                    <div class="step">
                        <span class="step-number">1</span>
                        <span class="step-text">
                            <?php printf( __( 'Add %d of this item to cart', 'woo-offers' ), $bogo_buy_qty ); ?>
                        </span>
                    </div>
                    <div class="step">
                        <span class="step-number">2</span>
                        <span class="step-text">
                            <?php 
                            if ( $bogo_discount >= 100 ) {
                                printf( __( 'Get %d more FREE!', 'woo-offers' ), $bogo_get_qty );
                            } else {
                                printf( __( 'Get %d more at %d%% off!', 'woo-offers' ), $bogo_get_qty, $bogo_discount );
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Savings Display -->
            <div class="savings-display">
                <?php 
                $current_price = $product->get_price();
                $total_savings = $current_price * $bogo_get_qty * ($bogo_discount / 100);
                $total_normal_price = $current_price * ($bogo_buy_qty + $bogo_get_qty);
                $total_offer_price = $current_price * $bogo_buy_qty + ($current_price * $bogo_get_qty * (1 - $bogo_discount / 100));
                ?>
                <div class="savings-row">
                    <span class="savings-label"><?php printf( __( 'Normal price for %d items:', 'woo-offers' ), $bogo_buy_qty + $bogo_get_qty ); ?></span>
                    <span class="normal-total"><?php echo wc_price( $total_normal_price ); ?></span>
                </div>
                <div class="savings-row highlight">
                    <span class="savings-label"><?php _e( 'Your price:', 'woo-offers' ); ?></span>
                    <span class="offer-total"><?php echo wc_price( $total_offer_price ); ?></span>
                </div>
                <div class="total-savings">
                    <span class="savings-amount"><?php echo wc_price( $total_savings ); ?></span>
                    <span class="savings-text"><?php _e( 'saved!', 'woo-offers' ); ?></span>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <div class="offer-action">
            <button type="button" 
                    class="woo-offer-button apply-bogo-offer" 
                    data-offer-id="<?php echo esc_attr( $offer->id ); ?>"
                    data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
                    data-buy-qty="<?php echo esc_attr( $bogo_buy_qty ); ?>"
                    data-get-qty="<?php echo esc_attr( $bogo_get_qty ); ?>"
                    style="background-color: <?php echo esc_attr( $accent_color ); ?>; border-color: <?php echo esc_attr( $accent_color ); ?>;">
                <span class="button-icon">🎯</span>
                <span class="button-content">
                    <span class="button-text"><?php _e( 'Add to Cart', 'woo-offers' ); ?></span>
                    <span class="button-detail">
                        <?php printf( __( 'Buy %d + Get %d', 'woo-offers' ), $bogo_buy_qty, $bogo_get_qty ); ?>
                    </span>
                </span>
            </button>
        </div>

        <!-- Offer Conditions -->
        <?php if ( ! empty( $conditions['minimum_amount'] ) || ! empty( $offer->usage_limit ) ): ?>
        <div class="offer-conditions">
            <?php if ( ! empty( $conditions['minimum_amount'] ) ): ?>
                <div class="condition-item">
                    <span class="condition-icon">💳</span>
                    <span class="condition-text">
                        <?php printf( __( 'Min. cart value: %s', 'woo-offers' ), wc_price( $conditions['minimum_amount'] ) ); ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if ( ! empty( $offer->usage_limit ) ): ?>
                <div class="condition-item">
                    <span class="condition-icon">🔥</span>
                    <span class="condition-text">
                        <?php printf( __( 'Hot deal: %d left!', 'woo-offers' ), intval( $offer->usage_limit ) ); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Urgency Timer -->
        <?php if ( ! empty( $offer->end_date ) ): ?>
        <div class="offer-urgency bogo-urgency">
            <span class="urgency-icon">⚡</span>
            <span class="urgency-content">
                <span class="urgency-label"><?php _e( 'BOGO ends in:', 'woo-offers' ); ?></span>
                <span class="urgency-time">
                    <?php 
                    $end_date = strtotime( $offer->end_date );
                    $time_left = $end_date - current_time( 'timestamp' );
                    $days_left = floor( $time_left / DAY_IN_SECONDS );
                    $hours_left = floor( ( $time_left % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
                    
                    if ( $days_left > 0 ) {
                        printf( __( '%dd %dh', 'woo-offers' ), $days_left, $hours_left );
                    } elseif ( $hours_left > 0 ) {
                        printf( __( '%d hours', 'woo-offers' ), $hours_left );
                    } else {
                        _e( 'Less than 1 hour!', 'woo-offers' );
                    }
                    ?>
                </span>
            </span>
        </div>
        <?php endif; ?>

    </div>
</div>

<style>
.woo-offer-bogo {
    position: relative;
    padding: 25px;
    margin: 20px 0;
    overflow: hidden;
    animation: bogoGlow 3s ease-in-out infinite alternate;
}

@keyframes bogoGlow {
    0% { box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    100% { box-shadow: 0 15px 40px rgba(0,0,0,0.3), 0 0 20px rgba(255,255,255,0.1); }
}

.bogo-decorations {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    overflow: hidden;
}

.decoration-star {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 18px;
    animation: twinkle 2s ease-in-out infinite;
}

.decoration-flash {
    position: absolute;
    top: 15px;
    left: 20px;
    font-size: 16px;
    animation: flash 1.5s ease-in-out infinite;
}

.decoration-gift {
    position: absolute;
    bottom: 15px;
    right: 25px;
    font-size: 16px;
    animation: bounce 2s ease-in-out infinite;
}

@keyframes twinkle {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

@keyframes flash {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

.woo-offer-bogo .woo-offer-content {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 20px;
    align-items: center;
    position: relative;
    z-index: 1;
}

.bogo-badge {
    background: rgba(255,255,255,0.25);
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    min-width: 120px;
    border: 2px solid rgba(255,255,255,0.3);
    backdrop-filter: blur(10px);
}

.bogo-main {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 8px;
}

.bogo-buy,
.bogo-get {
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.bogo-buy strong,
.bogo-get strong {
    font-size: 18px;
    font-weight: 900;
}

.bogo-separator {
    font-size: 20px;
    font-weight: 900;
    color: rgba(255,255,255,0.8);
    margin: 0 5px;
}

.bogo-benefit {
    font-size: 16px;
    font-weight: 900;
    color: #FFD700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    animation: pulse 2s ease-in-out infinite;
}

.offer-details {
    min-width: 0;
}

.offer-title {
    margin: 0 0 10px 0;
    font-size: 18px;
    font-weight: 700;
    color: inherit;
}

.offer-description {
    margin: 0 0 15px 0;
    font-size: 14px;
    opacity: 0.95;
    line-height: 1.4;
}

.bogo-explanation {
    background: rgba(255,255,255,0.1);
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
}

.explanation-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    font-weight: 600;
}

.explanation-steps {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.step {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
}

.step-number {
    background: rgba(255,255,255,0.3);
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 11px;
    flex-shrink: 0;
}

.savings-display {
    background: rgba(255,255,255,0.15);
    padding: 12px;
    border-radius: 8px;
    font-size: 13px;
}

.savings-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.savings-row.highlight {
    font-weight: 700;
    font-size: 14px;
    border-top: 1px solid rgba(255,255,255,0.3);
    padding-top: 8px;
    margin-top: 8px;
}

.normal-total {
    text-decoration: line-through;
    opacity: 0.8;
}

.total-savings {
    text-align: center;
    margin-top: 10px;
    padding: 5px;
    background: rgba(255,255,255,0.2);
    border-radius: 5px;
}

.savings-amount {
    font-weight: 900;
    font-size: 16px;
    color: #FFD700;
}

.savings-text {
    margin-left: 5px;
    font-weight: 600;
}

.woo-offer-button {
    background: #c0392b;
    color: white;
    border: none;
    padding: 16px 20px;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 180px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.woo-offer-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

.button-icon {
    font-size: 20px;
}

.button-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.button-text {
    font-size: 15px;
    line-height: 1;
}

.button-detail {
    font-size: 11px;
    opacity: 0.9;
    margin-top: 3px;
    font-weight: 500;
}

.offer-conditions {
    grid-column: 1 / -1;
    margin-top: 15px;
    display: flex;
    gap: 20px;
    font-size: 12px;
}

.condition-item {
    display: flex;
    align-items: center;
    gap: 6px;
    opacity: 0.95;
}

.bogo-urgency {
    grid-column: 1 / -1;
    margin-top: 12px;
    padding: 12px 15px;
    background: rgba(255,255,255,0.2);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid rgba(255,255,255,0.3);
    animation: urgencyPulse 2s ease-in-out infinite;
}

@keyframes urgencyPulse {
    0%, 100% { background: rgba(255,255,255,0.2); }
    50% { background: rgba(255,255,255,0.3); }
}

.urgency-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.urgency-label {
    font-size: 11px;
    opacity: 0.9;
}

.urgency-time {
    font-size: 14px;
    font-weight: 700;
    color: #FFD700;
}

@media (max-width: 768px) {
    .woo-offer-bogo .woo-offer-content {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 20px;
    }
    
    .bogo-badge {
        margin: 0 auto;
    }
    
    .offer-conditions {
        flex-direction: column;
        gap: 10px;
        align-items: center;
    }
    
    .woo-offer-button {
        justify-content: center;
        margin: 0 auto;
    }
    
    .savings-display {
        font-size: 12px;
    }
}
</style> 