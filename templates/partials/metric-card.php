<?php
/**
 * Reusable Metric Card Partial
 * Displays a metric card with icon, value, label, and optional change indicator
 *
 * @package WooOffers
 * @since 3.0.0
 * 
 * Variables to pass:
 * $metric_value - The main metric value (e.g., '1,234' or '12.5%')
 * $metric_label - The label for the metric (e.g., 'Total Views')
 * $metric_icon - Dashicons class (e.g., 'dashicons-visibility')
 * $metric_icon_color - Color for the icon (e.g., 'var(--wo-primary-500)')
 * $metric_change - Optional change percentage (e.g., '+12.5')
 * $metric_change_label - Optional change label (e.g., 'vs last period')
 * $metric_breakdown - Optional additional info (e.g., '10 active, 5 paused')
 * $metric_data_attr - Optional data attribute for the card (e.g., 'views')
 * $metric_aria_label - Optional aria-label for accessibility
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Set defaults
$metric_value = $metric_value ?? '0';
$metric_label = $metric_label ?? __( 'Metric', 'woo-offers' );
$metric_icon = $metric_icon ?? 'dashicons-chart-bar';
$metric_icon_color = $metric_icon_color ?? 'var(--wo-primary-500)';
$metric_change = $metric_change ?? null;
$metric_change_label = $metric_change_label ?? __( 'vs last period', 'woo-offers' );
$metric_breakdown = $metric_breakdown ?? '';
$metric_data_attr = $metric_data_attr ?? '';
$metric_aria_label = $metric_aria_label ?? $metric_label . ' metric details';

// Determine change direction and color
$change_direction = '';
$change_color = '';
if ( $metric_change !== null ) {
    if ( is_numeric( str_replace( ['+', '-', '%'], '', $metric_change ) ) ) {
        $change_value = floatval( str_replace( ['+', '%'], '', $metric_change ) );
        if ( $change_value >= 0 ) {
            $change_direction = 'up';
            $change_color = 'var(--wo-success-600)';
        } else {
            $change_direction = 'down';
            $change_color = 'var(--wo-error-600)';
        }
    }
}
?>

<div class="wo-card metric-card" 
     style="position: relative; overflow: hidden;" 
     <?php if ( $metric_data_attr ) : ?>data-metric="<?php echo esc_attr( $metric_data_attr ); ?>"<?php endif; ?>
     tabindex="0" 
     role="button" 
     aria-label="<?php echo esc_attr( $metric_aria_label ); ?>">
    <div class="wo-card-body wo-flex wo-items-center wo-gap-4">
        <div class="metric-icon" 
             style="width: 48px; height: 48px; background: <?php echo esc_attr( str_replace( '500', '100', $metric_icon_color ) ); ?>; border-radius: var(--wo-border-radius-lg); display: flex; align-items: center; justify-content: center;">
            <span class="dashicons <?php echo esc_attr( $metric_icon ); ?>" 
                  style="font-size: 24px; color: <?php echo esc_attr( $metric_icon_color ); ?>;"></span>
        </div>
        <div class="metric-content wo-flex wo-flex-col">
            <div class="metric-number" 
                 style="font-size: var(--wo-text-2xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary); line-height: var(--wo-leading-tight);">
                <?php echo wp_kses_post( $metric_value ); ?>
            </div>
            <div class="metric-label" 
                 style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary); margin-bottom: var(--wo-space-1);">
                <?php echo esc_html( $metric_label ); ?>
            </div>
            
            <?php if ( $metric_change !== null ) : ?>
                <div class="metric-change <?php echo $change_direction ? $change_direction : 'neutral'; ?>" 
                     style="font-size: var(--wo-text-xs); color: <?php echo $change_color ?: 'var(--wo-text-muted)'; ?>; font-weight: var(--wo-font-medium);">
                    <?php if ( $change_direction ) : ?>
                        <span class="dashicons dashicons-arrow-<?php echo $change_direction; ?>-alt" style="font-size: 12px;"></span>
                    <?php endif; ?>
                    <?php echo esc_html( $metric_change ); ?>% <?php echo esc_html( $metric_change_label ); ?>
                </div>
            <?php elseif ( $metric_breakdown ) : ?>
                <div class="metric-breakdown" 
                     style="font-size: var(--wo-text-xs); color: var(--wo-text-muted); margin-top: var(--wo-space-1);">
                    <?php echo wp_kses_post( $metric_breakdown ); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 