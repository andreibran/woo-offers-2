<?php
/**
 * Reusable Admin Header Partial
 * Displays page header with breadcrumbs, title, description, and action buttons
 *
 * @package WooOffers
 * @since 3.0.0
 * 
 * Variables to pass:
 * $header_title - Main page title
 * $header_description - Page description
 * $breadcrumbs - Array of breadcrumb items with 'label' and 'url'
 * $header_actions - Array of action buttons with 'label', 'url', 'class', 'icon'
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Set defaults
$header_title = $header_title ?? get_admin_page_title();
$header_description = $header_description ?? '';
$breadcrumbs = $breadcrumbs ?? [];
$header_actions = $header_actions ?? [];
?>

<header class="wo-admin-header" style="background: var(--wo-bg-secondary); border-bottom: var(--wo-border-width) solid var(--wo-border-primary); padding: var(--wo-space-6) 0; margin-bottom: var(--wo-space-8);">
    <div class="wo-container">
        <div class="wo-flex wo-items-center wo-justify-between wo-gap-6">
            <div class="header-content">
                <?php if ( ! empty( $breadcrumbs ) ) : ?>
                    <!-- Breadcrumbs -->
                    <nav aria-label="<?php _e( 'Breadcrumb', 'woo-offers' ); ?>" style="margin-bottom: var(--wo-space-2);">
                        <ol class="wo-flex wo-gap-2" style="list-style: none; margin: 0; padding: 0; font-size: var(--wo-text-sm); color: var(--wo-text-secondary);">
                            <?php foreach ( $breadcrumbs as $index => $crumb ) : ?>
                                <li>
                                    <?php if ( ! empty( $crumb['url'] ) ) : ?>
                                        <a href="<?php echo esc_url( $crumb['url'] ); ?>" style="color: var(--wo-text-secondary); text-decoration: none;">
                                            <?php echo esc_html( $crumb['label'] ); ?>
                                        </a>
                                    <?php else : ?>
                                        <span style="color: var(--wo-text-primary); font-weight: var(--wo-font-medium);">
                                            <?php echo esc_html( $crumb['label'] ); ?>
                                        </span>
                                    <?php endif; ?>
                                </li>
                                <?php if ( $index < count( $breadcrumbs ) - 1 ) : ?>
                                    <li style="color: var(--wo-text-muted);">/</li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                <?php endif; ?>

                <h1 style="margin: 0; font-size: var(--wo-text-3xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary);">
                    <?php echo esc_html( $header_title ); ?>
                </h1>
                
                <?php if ( ! empty( $header_description ) ) : ?>
                    <p style="margin: var(--wo-space-2) 0 0; font-size: var(--wo-text-base); color: var(--wo-text-secondary);">
                        <?php echo wp_kses_post( $header_description ); ?>
                    </p>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $header_actions ) ) : ?>
                <div class="header-actions wo-flex wo-gap-3">
                    <?php foreach ( $header_actions as $action ) : ?>
                        <a href="<?php echo esc_url( $action['url'] ); ?>" 
                           class="wo-btn <?php echo esc_attr( $action['class'] ?? 'wo-btn-primary' ); ?>"
                           <?php if ( ! empty( $action['tooltip'] ) ) : ?>
                               data-tooltip="<?php echo esc_attr( $action['tooltip'] ); ?>"
                           <?php endif; ?>>
                            <?php if ( ! empty( $action['icon'] ) ) : ?>
                                <span class="dashicons <?php echo esc_attr( $action['icon'] ); ?>" style="margin-right: var(--wo-space-2);"></span>
                            <?php endif; ?>
                            <?php echo esc_html( $action['label'] ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header> 