<?php
/**
 * Quantity Discount Offer Template
 * 
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Quantity discount styling
$bg_color = ! empty( $appearance['background_color'] ) ? $appearance['background_color'] : '#f39c12';
$min_qty = ! empty( $offer->value ) ? intval( $offer->value ) : 2;
?>

<div class="woo-offer woo-offer-quantity" 
     data-offer-id="<?php echo esc_attr( $offer->id ); ?>" 
     data-offer-type="quantity"
     data-min-qty="<?php echo esc_attr( $min_qty ); ?>"
     style="background: linear-gradient(135deg, <?php echo esc_attr( $bg_color ); ?> 0%, #e67e22 100%); color: white; padding: 18px; border-radius: 10px; margin: 15px 0; box-shadow: 0 6px 20px rgba(0,0,0,0.15);">
    
    <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 16px; align-items: center;">
        
        <!-- Quantity Badge -->
        <div style="background: rgba(255,255,255,0.25); padding: 16px; border-radius: 12px; text-align: center; min-width: 100px;">
            <div style="font-size: 28px; margin-bottom: 5px;">📊</div>
            <div style="font-size: 18px; font-weight: 900; line-height: 1;"><?php echo $min_qty; ?>+</div>
            <div style="font-size: 10px; font-weight: 600; letter-spacing: 1px; opacity: 0.9; margin-top: 2px;"><?php _e( 'ITEMS', 'woo-offers' ); ?></div>
        </div>

        <!-- Offer Details -->
        <div>
            <h4 style="margin: 0 0 8px 0; font-size: 17px; font-weight: 600;"><?php echo esc_html( $offer->name ); ?></h4>
            
            <?php if ( ! empty( $offer->description ) ): ?>
            <p style="margin: 0 0 12px 0; font-size: 14px; opacity: 0.9; line-height: 1.4;"><?php echo wp_kses_post( $offer->description ); ?></p>
            <?php endif; ?>

            <div style="background: rgba(255,255,255,0.1); padding: 12px; border-radius: 6px; font-size: 14px;">
                <div style="margin-bottom: 6px;">
                    <span style="opacity: 0.9;"><?php _e( 'Buy', 'woo-offers' ); ?></span>
                    <strong style="margin: 0 5px;"><?php echo $min_qty; ?>+</strong>
                    <span style="opacity: 0.9;"><?php _e( 'items and get:', 'woo-offers' ); ?></span>
                </div>
                
                <?php if ( $product ): ?>
                <div style="font-weight: 600; color: #FFD700;">
                    <?php 
                    $discount_percent = 15; // Default discount percentage for bulk
                    $current_price = $product->get_price();
                    $bulk_price = $current_price * (1 - $discount_percent / 100);
                    ?>
                    <?php echo wc_price( $bulk_price ); ?> <?php _e( 'each', 'woo-offers' ); ?>
                    <span style="font-size: 12px; opacity: 0.8; margin-left: 8px;">
                        (<?php _e( 'was', 'woo-offers' ); ?> <?php echo wc_price( $current_price ); ?>)
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Button -->
        <div>
            <button type="button" 
                    class="woo-offer-button apply-quantity-discount" 
                    data-offer-id="<?php echo esc_attr( $offer->id ); ?>"
                    data-min-qty="<?php echo esc_attr( $min_qty ); ?>"
                    style="background: #e67e22; color: white; border: none; padding: 14px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; min-width: 140px;">
                <span style="font-size: 18px;">🔢</span>
                <span>
                    <div style="font-size: 14px; line-height: 1;"><?php _e( 'Buy in Bulk', 'woo-offers' ); ?></div>
                    <div style="font-size: 11px; opacity: 0.9; margin-top: 2px;"><?php echo $min_qty; ?>+ <?php _e( 'items', 'woo-offers' ); ?></div>
                </span>
            </button>
        </div>
    </div>

    <?php if ( ! empty( $conditions['maximum_quantity'] ) ): ?>
    <div style="margin-top: 10px; padding: 6px 10px; background: rgba(255,255,255,0.15); border-radius: 5px; font-size: 12px; text-align: center;">
        <span>⚠️</span>
        <?php printf( __( 'Maximum %d items per order', 'woo-offers' ), intval( $conditions['maximum_quantity'] ) ); ?>
    </div>
    <?php endif; ?>
</div> 