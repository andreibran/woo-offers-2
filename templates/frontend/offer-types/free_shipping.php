<?php
/**
 * Free Shipping Offer Template
 * 
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Free shipping styling
$bg_color = ! empty( $appearance['background_color'] ) ? $appearance['background_color'] : '#16a085';
$min_amount = ! empty( $offer->value ) ? floatval( $offer->value ) : 0;
?>

<div class="woo-offer woo-offer-free-shipping" 
     data-offer-id="<?php echo esc_attr( $offer->id ); ?>" 
     data-offer-type="free_shipping"
     data-min-amount="<?php echo esc_attr( $min_amount ); ?>"
     style="background: linear-gradient(135deg, <?php echo esc_attr( $bg_color ); ?> 0%, #1abc9c 100%); color: white; padding: 16px; border-radius: 10px; margin: 15px 0; border-left: 4px solid rgba(255,255,255,0.3);">
    
    <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 16px; align-items: center;">
        
        <!-- Shipping Icon -->
        <div style="background: rgba(255,255,255,0.2); padding: 14px; border-radius: 8px; text-align: center; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
            <span style="font-size: 28px;">🚚</span>
        </div>

        <!-- Offer Details -->
        <div>
            <h4 style="margin: 0 0 6px 0; font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <span>🆓</span>
                <?php echo esc_html( $offer->name ); ?>
            </h4>
            
            <?php if ( ! empty( $offer->description ) ): ?>
            <p style="margin: 0 0 10px 0; font-size: 13px; opacity: 0.95; line-height: 1.4;"><?php echo wp_kses_post( $offer->description ); ?></p>
            <?php endif; ?>

            <div style="font-size: 14px;">
                <?php if ( $min_amount > 0 ): ?>
                    <div style="margin-bottom: 4px;">
                        <span style="opacity: 0.9;"><?php _e( 'Free shipping on orders over', 'woo-offers' ); ?></span>
                        <strong style="color: #FFD700; margin-left: 5px;"><?php echo wc_price( $min_amount ); ?></strong>
                    </div>
                    
                    <?php if ( WC()->cart && WC()->cart->get_total( 'raw' ) > 0 ): ?>
                        <?php 
                        $cart_total = WC()->cart->get_total( 'raw' );
                        $remaining = max( 0, $min_amount - $cart_total );
                        ?>
                        
                        <?php if ( $remaining > 0 ): ?>
                            <div style="background: rgba(255,255,255,0.15); padding: 8px 10px; border-radius: 5px; font-size: 12px; margin-top: 6px;">
                                <span>📏</span>
                                <?php printf( __( 'Add %s more to qualify!', 'woo-offers' ), wc_price( $remaining ) ); ?>
                            </div>
                        <?php else: ?>
                            <div style="background: rgba(255,255,255,0.2); padding: 8px 10px; border-radius: 5px; font-size: 12px; margin-top: 6px; color: #FFD700; font-weight: 600;">
                                <span>✅</span>
                                <?php _e( 'You qualify for free shipping!', 'woo-offers' ); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div style="font-weight: 600; color: #FFD700;">
                        <span>🎉</span>
                        <?php _e( 'Free shipping on all orders!', 'woo-offers' ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Button -->
        <div>
            <button type="button" 
                    class="woo-offer-button apply-free-shipping" 
                    data-offer-id="<?php echo esc_attr( $offer->id ); ?>"
                    data-min-amount="<?php echo esc_attr( $min_amount ); ?>"
                    style="background: #1abc9c; color: white; border: none; padding: 12px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; min-width: 130px;">
                <span style="font-size: 16px;">📦</span>
                <span>
                    <div style="font-size: 13px; line-height: 1;"><?php _e( 'Free Ship', 'woo-offers' ); ?></div>
                    <div style="font-size: 10px; opacity: 0.9; margin-top: 2px;">
                        <?php if ( $min_amount > 0 ): ?>
                            <?php printf( __( 'over %s', 'woo-offers' ), wc_price( $min_amount ) ); ?>
                        <?php else: ?>
                            <?php _e( 'always free', 'woo-offers' ); ?>
                        <?php endif; ?>
                    </div>
                </span>
            </button>
        </div>
    </div>

    <!-- Additional Info -->
    <?php if ( ! empty( $offer->end_date ) || ! empty( $conditions['shipping_regions'] ) ): ?>
    <div style="margin-top: 10px; padding: 8px 12px; background: rgba(255,255,255,0.1); border-radius: 6px; font-size: 11px; display: flex; gap: 15px;">
        
        <?php if ( ! empty( $offer->end_date ) ): ?>
        <div style="display: flex; align-items: center; gap: 4px;">
            <span>⏰</span>
            <span><?php printf( __( 'Until %s', 'woo-offers' ), date_i18n( get_option( 'date_format' ), strtotime( $offer->end_date ) ) ); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ( ! empty( $conditions['shipping_regions'] ) ): ?>
        <div style="display: flex; align-items: center; gap: 4px;">
            <span>🌍</span>
            <span><?php _e( 'Selected regions only', 'woo-offers' ); ?></span>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div> 