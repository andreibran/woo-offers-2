<?php
/**
 * Campaign Display Template
 * 
 * Default template for displaying campaigns
 * 
 * @package WooOffers
 * @since 3.0.0
 * 
 * Variables available in this template:
 * @var object $campaign Campaign data object
 * @var int $campaign_id Campaign ID
 * @var string $template_type Template type being rendered
 * @var string $context Context where template is being displayed
 * @var mixed $product Product object (if available)
 * @var mixed $cart Cart object (if available)
 * @var mixed $checkout Checkout object (if available)
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Ensure we have campaign data
if ( ! isset( $campaign ) || ! $campaign ) {
    return;
}

// Sanitize variables
$campaign_title = isset( $campaign->title ) ? sanitize_text_field( $campaign->title ) : '';
$campaign_description = isset( $campaign->description ) ? wp_kses_post( $campaign->description ) : '';
$campaign_type = isset( $campaign->type ) ? sanitize_text_field( $campaign->type ) : '';
$context = isset( $context ) ? sanitize_text_field( $context ) : 'default';
$template_type = isset( $template_type ) ? sanitize_text_field( $template_type ) : 'display';

// Set CSS classes
$css_classes = [
    'woo-offers-campaign',
    'woo-offers-campaign--' . $campaign_type,
    'woo-offers-campaign--' . $template_type,
    'woo-offers-campaign--' . $context
];

if ( isset( $shortcode_class ) && $shortcode_class ) {
    $css_classes[] = sanitize_html_class( $shortcode_class );
}

$css_class_string = implode( ' ', array_filter( $css_classes ) );
?>

<div class="<?php echo esc_attr( $css_class_string ); ?>" data-campaign-id="<?php echo esc_attr( $campaign_id ); ?>">
    
    <div class="woo-offers-campaign__header">
        <?php if ( $campaign_title ): ?>
            <h3 class="woo-offers-campaign__title"><?php echo esc_html( $campaign_title ); ?></h3>
        <?php endif; ?>
        
        <?php if ( $campaign_description ): ?>
            <div class="woo-offers-campaign__description">
                <?php echo $campaign_description; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="woo-offers-campaign__content">
        <?php
        // Display campaign content based on type
        switch ( $campaign_type ) {
            case 'product_upsell':
                if ( isset( $product ) && $product ) {
                    echo '<p class="woo-offers-campaign__context">' . 
                         sprintf( esc_html__( 'Special offer for %s', 'woo-offers' ), $product->get_name() ) . 
                         '</p>';
                }
                break;
                
            case 'cart_upsell':
                echo '<p class="woo-offers-campaign__context">' . 
                     esc_html__( 'Add this to your cart for extra savings!', 'woo-offers' ) . 
                     '</p>';
                break;
                
            case 'checkout_upsell':
                echo '<p class="woo-offers-campaign__context">' . 
                     esc_html__( 'Last chance to save - add this before checkout!', 'woo-offers' ) . 
                     '</p>';
                break;
                
            default:
                echo '<p class="woo-offers-campaign__context">' . 
                     esc_html__( 'Special offer available now!', 'woo-offers' ) . 
                     '</p>';
                break;
        }
        ?>
        
        <?php if ( isset( $campaign->offer_details ) && $campaign->offer_details ): ?>
            <div class="woo-offers-campaign__offer-details">
                <?php echo wp_kses_post( $campaign->offer_details ); ?>
            </div>
        <?php endif; ?>
        
        <?php if ( isset( $campaign->discount_amount ) && $campaign->discount_amount ): ?>
            <div class="woo-offers-campaign__discount">
                <span class="woo-offers-campaign__discount-label">
                    <?php esc_html_e( 'Save:', 'woo-offers' ); ?>
                </span>
                <span class="woo-offers-campaign__discount-amount">
                    <?php
                    if ( isset( $campaign->discount_type ) && $campaign->discount_type === 'percentage' ) {
                        echo esc_html( $campaign->discount_amount . '%' );
                    } else {
                        echo wp_kses_post( wc_price( $campaign->discount_amount ) );
                    }
                    ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="woo-offers-campaign__actions">
        <?php
        // Generate action button based on campaign type and context
        $button_text = isset( $campaign->button_text ) ? 
                      sanitize_text_field( $campaign->button_text ) : 
                      esc_html__( 'Get This Offer', 'woo-offers' );
        
        $button_url = isset( $campaign->button_url ) ? 
                     esc_url( $campaign->button_url ) : 
                     '#';
        
        // Apply filters to button data
        $button_data = apply_filters( 'woo_offers_campaign_button_data', [
            'text' => $button_text,
            'url' => $button_url,
            'class' => 'woo-offers-campaign__button btn btn-primary',
            'attributes' => [
                'data-campaign-id' => $campaign_id,
                'data-campaign-type' => $campaign_type,
                'data-context' => $context
            ]
        ], $campaign, $context );
        ?>
        
        <a href="<?php echo esc_url( $button_data['url'] ); ?>" 
           class="<?php echo esc_attr( $button_data['class'] ); ?>"
           <?php foreach ( $button_data['attributes'] as $attr => $value ): ?>
               <?php echo esc_attr( $attr ); ?>="<?php echo esc_attr( $value ); ?>"
           <?php endforeach; ?>>
            <?php echo esc_html( $button_data['text'] ); ?>
        </a>
    </div>
    
    <?php if ( isset( $campaign->terms_conditions ) && $campaign->terms_conditions ): ?>
        <div class="woo-offers-campaign__terms">
            <small><?php echo wp_kses_post( $campaign->terms_conditions ); ?></small>
        </div>
    <?php endif; ?>
    
    <?php
    // Hook for additional campaign content
    do_action( 'woo_offers_campaign_template_after_content', $campaign, $context, $template_type );
    ?>
    
</div>

<?php
// Hook for after campaign template
do_action( 'woo_offers_campaign_template_after', $campaign, $context, $template_type );
?> 