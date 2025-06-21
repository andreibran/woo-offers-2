<?php
/**
 * Campaigns List Template
 * 
 * Template for displaying a list of campaigns
 * 
 * @package WooOffers
 * @since 3.0.0
 * 
 * Variables available in this template:
 * @var array $campaigns Array of campaign objects
 * @var string $shortcode_class CSS class from shortcode
 * @var bool $is_shortcode Whether this is rendered via shortcode
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Ensure we have campaigns data
if ( ! isset( $campaigns ) || ! is_array( $campaigns ) || empty( $campaigns ) ) {
    return;
}

// Set CSS classes
$css_classes = [
    'woo-offers-campaigns-list'
];

if ( isset( $shortcode_class ) && $shortcode_class ) {
    $css_classes[] = sanitize_html_class( $shortcode_class );
}

if ( isset( $is_shortcode ) && $is_shortcode ) {
    $css_classes[] = 'woo-offers-campaigns-list--shortcode';
}

$css_class_string = implode( ' ', array_filter( $css_classes ) );
?>

<div class="<?php echo esc_attr( $css_class_string ); ?>">
    
    <div class="woo-offers-campaigns-list__header">
        <h3 class="woo-offers-campaigns-list__title">
            <?php esc_html_e( 'Special Offers', 'woo-offers' ); ?>
        </h3>
    </div>
    
    <div class="woo-offers-campaigns-list__grid">
        <?php foreach ( $campaigns as $campaign ): ?>
            <?php if ( ! isset( $campaign->id ) || ! $campaign->id ) continue; ?>
            
            <div class="woo-offers-campaigns-list__item">
                <?php
                // Render individual campaign using our campaign template function
                echo woo_offers_render_campaign_template( $campaign->id, 'display', [
                    'context' => 'campaigns_list',
                    'is_in_list' => true,
                    'shortcode_class' => ''
                ] );
                ?>
            </div>
            
        <?php endforeach; ?>
    </div>
    
    <?php if ( count( $campaigns ) === 0 ): ?>
        <div class="woo-offers-campaigns-list__empty">
            <p><?php esc_html_e( 'No offers available at this time.', 'woo-offers' ); ?></p>
        </div>
    <?php endif; ?>
    
    <?php
    // Hook for additional content after campaigns list
    do_action( 'woo_offers_campaigns_list_after', $campaigns );
    ?>
    
</div> 