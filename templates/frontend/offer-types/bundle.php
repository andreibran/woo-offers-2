<?php
/**
 * Bundle Offer Template
 * 
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Bundle styling
$bg_color = ! empty( $appearance['background_color'] ) ? $appearance['background_color'] : '#9b59b6';
?>

<div class="woo-offer woo-offer-bundle" 
     data-offer-id="<?php echo esc_attr( $offer->id ); ?>" 
     data-offer-type="bundle"
     style="background: linear-gradient(135deg, <?php echo esc_attr( $bg_color ); ?> 0%, #8e44ad 100%); color: white; padding: 20px; border-radius: 12px; margin: 15px 0; border: 2px solid rgba(255,255,255,0.2);">
    
    <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 18px; align-items: center;">
        
        <!-- Bundle Icon -->
        <div style="background: rgba(255,255,255,0.2); padding: 18px; border-radius: 50%; text-align: center; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
            <span style="font-size: 24px;">📦</span>
        </div>

        <!-- Bundle Details -->
        <div>
            <h4 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700;"><?php echo esc_html( $offer->name ); ?></h4>
            
            <?php if ( ! empty( $offer->description ) ): ?>
            <p style="margin: 0 0 12px 0; font-size: 14px; opacity: 0.9; line-height: 1.4;"><?php echo wp_kses_post( $offer->description ); ?></p>
            <?php endif; ?>

            <div style="background: rgba(255,255,255,0.1); padding: 12px; border-radius: 8px; font-size: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span><?php _e( 'Bundle includes:', 'woo-offers' ); ?></span>
                    <span style="font-weight: 700;"><?php _e( 'Multiple products', 'woo-offers' ); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 15px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 8px; margin-top: 8px;">
                    <span><?php _e( 'Bundle discount:', 'woo-offers' ); ?></span>
                    <span style="color: #FFD700;"><?php echo esc_html( $offer->value ); ?>% <?php _e( 'OFF', 'woo-offers' ); ?></span>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <div>
            <button type="button" 
                    class="woo-offer-button apply-bundle-offer" 
                    data-offer-id="<?php echo esc_attr( $offer->id ); ?>"
                    style="background: #8e44ad; color: white; border: none; padding: 14px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; min-width: 150px;">
                <span style="font-size: 18px;">🛍️</span>
                <span>
                    <div style="font-size: 14px; line-height: 1;"><?php _e( 'View Bundle', 'woo-offers' ); ?></div>
                    <div style="font-size: 11px; opacity: 0.9; margin-top: 2px;"><?php echo esc_html( $offer->value ); ?>% <?php _e( 'discount', 'woo-offers' ); ?></div>
                </span>
            </button>
        </div>
    </div>

    <?php if ( ! empty( $offer->end_date ) ): ?>
    <div style="margin-top: 12px; padding: 8px 12px; background: rgba(255,255,255,0.15); border-radius: 6px; font-size: 12px; text-align: center;">
        <span>⏰</span>
        <?php printf( __( 'Bundle offer ends %s', 'woo-offers' ), date_i18n( get_option( 'date_format' ), strtotime( $offer->end_date ) ) ); ?>
    </div>
    <?php endif; ?>
</div> 