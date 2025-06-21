<?php
/**
 * Reusable Empty State Partial
 * Displays an empty state with icon, title, description, and optional action button
 *
 * @package WooOffers
 * @since 3.0.0
 * 
 * Variables to pass:
 * $empty_icon - Dashicons class (e.g., 'dashicons-megaphone')
 * $empty_title - Main title for empty state
 * $empty_description - Description text
 * $empty_action_label - Optional action button label
 * $empty_action_url - Optional action button URL
 * $empty_action_class - Optional action button CSS class
 * $empty_size - Size variant: 'small', 'medium', 'large' (default: 'medium')
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Set defaults
$empty_icon = $empty_icon ?? 'dashicons-admin-generic';
$empty_title = $empty_title ?? __( 'No data available', 'woo-offers' );
$empty_description = $empty_description ?? __( 'There is currently no data to display.', 'woo-offers' );
$empty_action_label = $empty_action_label ?? '';
$empty_action_url = $empty_action_url ?? '';
$empty_action_class = $empty_action_class ?? 'wo-btn-primary';
$empty_size = $empty_size ?? 'medium';

// Size configurations
$size_configs = [
    'small' => [
        'padding' => 'var(--wo-space-8)',
        'icon_size' => '3rem',
        'title_size' => 'var(--wo-text-lg)',
        'desc_width' => '24rem'
    ],
    'medium' => [
        'padding' => 'var(--wo-space-12)',
        'icon_size' => '4rem',
        'title_size' => 'var(--wo-text-xl)',
        'desc_width' => '32rem'
    ],
    'large' => [
        'padding' => 'var(--wo-space-16)',
        'icon_size' => '5rem',
        'title_size' => 'var(--wo-text-2xl)',
        'desc_width' => '40rem'
    ]
];

$config = $size_configs[$empty_size] ?? $size_configs['medium'];
?>

<div class="wo-card wo-empty-state" style="text-align: center; padding: <?php echo esc_attr( $config['padding'] ); ?>;">
    <!-- Empty State Icon -->
    <div class="wo-empty-icon" style="color: var(--wo-text-muted); margin-bottom: var(--wo-space-6);">
        <span class="dashicons <?php echo esc_attr( $empty_icon ); ?>" 
              style="font-size: <?php echo esc_attr( $config['icon_size'] ); ?>; opacity: 0.3;"></span>
    </div>
    
    <!-- Empty State Title -->
    <h3 class="wo-empty-title" 
        style="margin: 0 0 var(--wo-space-4); font-size: <?php echo esc_attr( $config['title_size'] ); ?>; color: var(--wo-text-primary); font-weight: var(--wo-font-semibold);">
        <?php echo esc_html( $empty_title ); ?>
    </h3>
    
    <!-- Empty State Description -->
    <p class="wo-empty-description" 
       style="margin: 0 0 <?php echo ! empty( $empty_action_label ) ? 'var(--wo-space-6)' : '0'; ?>; color: var(--wo-text-secondary); max-width: <?php echo esc_attr( $config['desc_width'] ); ?>; margin-left: auto; margin-right: auto; line-height: var(--wo-leading-relaxed);">
        <?php echo wp_kses_post( $empty_description ); ?>
    </p>
    
    <!-- Optional Action Button -->
    <?php if ( ! empty( $empty_action_label ) && ! empty( $empty_action_url ) ) : ?>
        <a href="<?php echo esc_url( $empty_action_url ); ?>" 
           class="wo-btn <?php echo esc_attr( $empty_action_class ); ?> wo-btn-lg">
            <?php echo esc_html( $empty_action_label ); ?>
        </a>
    <?php endif; ?>
</div> 