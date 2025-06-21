<?php
/**
 * Full Page Template Structure
 * Provides a complete page layout with header, main content, and footer areas
 *
 * @package WooOffers
 * @since 3.0.0
 * 
 * Variables to pass:
 * $page_title - Page title
 * $page_description - Page description
 * $breadcrumbs - Breadcrumbs array
 * $header_actions - Header actions array
 * $content_template - Path to content template to include
 * $skip_header - Skip header rendering (default: false)
 * $page_class - Additional CSS class for page wrapper
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Set defaults
$page_title = $page_title ?? get_admin_page_title();
$page_description = $page_description ?? '';
$breadcrumbs = $breadcrumbs ?? [];
$header_actions = $header_actions ?? [];
$content_template = $content_template ?? '';
$skip_header = $skip_header ?? false;
$page_class = $page_class ?? '';
?>

<div class="wrap woo-offers-admin <?php echo esc_attr( $page_class ); ?>">
    <!-- Skip Links for Accessibility -->
    <a href="#main-content" class="wo-skip-link">
        <?php _e( 'Skip to main content', 'woo-offers' ); ?>
    </a>

    <?php if ( ! $skip_header ) : ?>
        <?php
        // Set header variables for partial
        $header_title = $page_title;
        $header_description = $page_description;
        
        // Include header partial
        include WOO_OFFERS_PLUGIN_PATH . 'templates/partials/admin-header.php';
        ?>
    <?php endif; ?>

    <!-- Main Content Area -->
    <main id="main-content" class="woo-offers-main-content">
        <?php
        // Show admin notices
        settings_errors();
        ?>
        
        <div class="woo-offers-content-wrapper">
            <?php
            // Include content template if specified
            if ( ! empty( $content_template ) && file_exists( $content_template ) ) {
                include $content_template;
            } else {
                echo '<p>' . __( 'Content template not found.', 'woo-offers' ) . '</p>';
            }
            ?>
        </div>
    </main>

    <!-- Page Footer (if needed) -->
    <?php if ( apply_filters( 'woo_offers_show_admin_footer', false ) ) : ?>
        <footer class="woo-offers-admin-footer" style="margin-top: var(--wo-space-8); padding-top: var(--wo-space-4); border-top: var(--wo-border-width) solid var(--wo-border-secondary);">
            <div class="wo-container">
                <p style="text-align: center; color: var(--wo-text-muted); font-size: var(--wo-text-sm);">
                    <?php 
                    printf( 
                        __( 'Woo Offers v%s - Built with ❤️ for WooCommerce', 'woo-offers' ), 
                        esc_html( WOO_OFFERS_VERSION ?? '3.0.0' )
                    ); 
                    ?>
                </p>
            </div>
        </footer>
    <?php endif; ?>
</div> 