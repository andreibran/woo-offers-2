<?php
/**
 * Offers list admin page template
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load the Offers List Table class
require_once WOO_OFFERS_PLUGIN_PATH . 'src/Admin/class-offers-list-table.php';

// Create and prepare the list table
$offers_table = new \WooOffers\Admin\Offers_List_Table();
$offers_table->prepare_items();

// Get summary statistics
$stats = $offers_table->get_summary_stats();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e( 'All Offers', 'woo-offers' ); ?>
        <a href="<?php echo admin_url( 'admin.php?page=woo-offers-create' ); ?>" class="page-title-action">
            <?php _e( 'Add New Offer', 'woo-offers' ); ?>
        </a>
    </h1>
    
    <?php
    // Display any admin notices
    if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) {
        printf(
            '<span class="subtitle">' . __( 'Search results for: %s', 'woo-offers' ) . '</span>',
            '<strong>' . esc_html( $_REQUEST['s'] ) . '</strong>'
        );
    }
    ?>
    
    <hr class="wp-header-end">

    <!-- Summary Statistics -->
    <div class="woo-offers-stats-summary">
        <div class="woo-offers-stats-cards">
            <div class="woo-offers-stat-card">
                <div class="stat-number"><?php echo number_format( $stats['total_offers'] ); ?></div>
                <div class="stat-label"><?php _e( 'Total Offers', 'woo-offers' ); ?></div>
                <div class="stat-breakdown">
                    <span class="active"><?php echo intval( $stats['active_offers'] ); ?> <?php _e( 'active', 'woo-offers' ); ?></span>
                    <span class="inactive"><?php echo intval( $stats['inactive_offers'] ); ?> <?php _e( 'inactive', 'woo-offers' ); ?></span>
                    <span class="draft"><?php echo intval( $stats['draft_offers'] ); ?> <?php _e( 'draft', 'woo-offers' ); ?></span>
                </div>
            </div>
            
            <div class="woo-offers-stat-card">
                <div class="stat-number">
                    <?php 
                    if ( function_exists( 'wc_price' ) ) {
                        echo wc_price( $stats['total_revenue'] );
                    } else {
                        echo '$' . number_format( $stats['total_revenue'], 2 );
                    }
                    ?>
                </div>
                <div class="stat-label"><?php _e( 'Total Revenue', 'woo-offers' ); ?></div>
                <div class="stat-breakdown">
                    <span><?php echo number_format( $stats['total_conversions'] ); ?> <?php _e( 'conversions', 'woo-offers' ); ?></span>
                </div>
            </div>
            
            <div class="woo-offers-stat-card">
                <div class="stat-number"><?php echo $stats['conversion_rate']; ?>%</div>
                <div class="stat-label"><?php _e( 'Conversion Rate', 'woo-offers' ); ?></div>
                <div class="stat-breakdown">
                    <span><?php echo number_format( $stats['total_views'] ); ?> <?php _e( 'total views', 'woo-offers' ); ?></span>
                </div>
            </div>
        </div>
    </div>

    <form id="offers-filter" method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ?? 'woo-offers-offers' ); ?>" />
        
        <?php $offers_table->search_box( __( 'Search Offers', 'woo-offers' ), 'offer' ); ?>
        
        <?php if ( $stats['total_offers'] === 0 && empty( $_REQUEST['s'] ) ): ?>
            <!-- Empty state when no offers exist -->
            <div class="woo-offers-empty-state">
                <div class="empty-state-icon">
                    <span class="dashicons dashicons-tag" style="font-size: 64px; color: #ccd0d4;"></span>
                </div>
                <h2><?php _e( 'No offers yet', 'woo-offers' ); ?></h2>
                <p class="empty-state-description">
                    <?php _e( 'You haven\'t created any offers yet. Start by creating your first offer to boost your sales with targeted promotions.', 'woo-offers' ); ?>
                </p>
                <a href="<?php echo admin_url( 'admin.php?page=woo-offers-create' ); ?>" class="button button-primary button-hero">
                    <?php _e( 'Create Your First Offer', 'woo-offers' ); ?>
                </a>
            </div>
            
            <style>
            .woo-offers-empty-state {
                text-align: center;
                padding: 60px 20px;
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                margin-top: 20px;
            }
            .woo-offers-empty-state h2 {
                margin: 20px 0 10px;
                color: #50575e;
                font-size: 24px;
                font-weight: 400;
            }
            .empty-state-description {
                color: #646970;
                font-size: 16px;
                margin-bottom: 30px;
                max-width: 500px;
                margin-left: auto;
                margin-right: auto;
            }
            .button-hero {
                padding: 12px 24px;
                font-size: 16px;
                height: auto;
            }
            </style>
        <?php else: ?>
            <?php $offers_table->display(); ?>
        <?php endif; ?>
        
    </form>
</div> 