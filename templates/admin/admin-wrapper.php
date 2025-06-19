<?php
/**
 * Admin page wrapper template
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap woo-offers-admin">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <?php
    // Show admin notices
    settings_errors();
    ?>
    
    <div class="woo-offers-admin-content">
        <?php
        // Content will be included here
        if ( isset( $template_file ) && file_exists( $template_file ) ) {
            include $template_file;
        } else {
            echo '<p>' . __( 'Template not found.', 'woo-offers' ) . '</p>';
        }
        ?>
    </div>
</div> 