<?php
/**
 * Modern Campaign Management Page Template
 * Enhanced with responsive design system and visual cards
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load the Offers List Table class for data processing
require_once WOO_OFFERS_PLUGIN_PATH . 'src/Admin/class-offers-list-table.php';

// Create and prepare the list table for data fetching
$offers_table = new \WooOffers\Admin\Offers_List_Table();
$offers_table->prepare_items();

// Get summary statistics
$stats = $offers_table->get_summary_stats();

// Get current filters
$current_search = sanitize_text_field( $_GET['s'] ?? '' );
$current_status = sanitize_text_field( $_GET['status'] ?? '' );
$current_type = sanitize_text_field( $_GET['type'] ?? '' );
$current_view = sanitize_text_field( $_GET['view'] ?? 'cards' ); // New view toggle

// Get pagination info
$current_page = $offers_table->get_pagenum();
$total_items = count( $offers_table->items );
?>

<div class="woo-offers-modern wo-container">
    <!-- Skip Link for Accessibility -->
    <a href="#main-content" class="wo-skip-link"><?php _e( 'Skip to main content', 'woo-offers' ); ?></a>

    <!-- Page Header -->
    <header class="campaigns-header wo-flex wo-justify-between wo-items-center" style="margin-bottom: var(--wo-space-8);">
        <div>
            <h1 style="margin: 0; font-size: var(--wo-text-3xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary);">
                <?php _e( 'Campaign Management', 'woo-offers' ); ?>
            </h1>
            <p style="margin: var(--wo-space-2) 0 0; color: var(--wo-text-secondary); font-size: var(--wo-text-base);">
                <?php 
                if ( $current_search ) {
                    printf( __( 'Search results for: %s', 'woo-offers' ), '<strong>' . esc_html( $current_search ) . '</strong>' );
                } else {
                    _e( 'Manage and monitor all your marketing campaigns', 'woo-offers' ); 
                }
                ?>
            </p>
        </div>
        <div class="campaigns-actions wo-flex wo-gap-4">
            <button type="button" 
                    id="export-campaigns-csv"
                    class="wo-btn wo-btn-outline"
                    data-tooltip="<?php _e( 'Export campaigns data to CSV', 'woo-offers' ); ?>">
                <span class="dashicons dashicons-download" style="margin-right: var(--wo-space-2);"></span>
                <?php _e( 'Export CSV', 'woo-offers' ); ?>
            </button>
            <a href="<?php echo admin_url( 'admin.php?page=woo-offers-analytics' ); ?>" 
               class="wo-btn wo-btn-outline">
                <span class="dashicons dashicons-chart-area" style="margin-right: var(--wo-space-2);"></span>
                <?php _e( 'Analytics', 'woo-offers' ); ?>
            </a>
            <a href="<?php echo admin_url( 'admin.php?page=woo-offers-create-campaign' ); ?>" 
               class="wo-btn wo-btn-primary wo-btn-lg">
                <span class="dashicons dashicons-plus-alt2" style="margin-right: var(--wo-space-2);"></span>
                <?php _e( 'Create Campaign', 'woo-offers' ); ?>
            </a>
        </div>
    </header>

    <main id="main-content">
        <!-- Key Metrics Overview -->
        <section class="metrics-overview" style="margin-bottom: var(--wo-space-8);">
            <h2 class="wo-sr-only"><?php _e( 'Campaign Metrics Overview', 'woo-offers' ); ?></h2>
            <div class="wo-grid wo-grid-cols-1 wo-md:grid-cols-2 wo-lg:grid-cols-4 wo-gap-6">
                <!-- Total Campaigns -->
                <div class="wo-card metric-card">
                    <div class="wo-card-body wo-flex wo-items-center">
                        <div class="metric-icon" style="margin-right: var(--wo-space-4); color: var(--wo-primary-500);">
                            <span class="dashicons dashicons-megaphone" style="font-size: var(--wo-text-3xl);"></span>
                        </div>
                        <div class="metric-content">
                            <div class="metric-value" style="font-size: var(--wo-text-2xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary);">
                                <?php echo number_format( $stats['total_offers'] ); ?>
                            </div>
                            <div class="metric-label" style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary);">
                                <?php _e( 'Total Campaigns', 'woo-offers' ); ?>
                            </div>
                            <div class="metric-breakdown" style="font-size: var(--wo-text-xs); color: var(--wo-text-muted); margin-top: var(--wo-space-1);">
                                <span style="color: var(--wo-success-600);"><?php echo intval( $stats['active_offers'] ); ?> active</span> • 
                                <span style="color: var(--wo-error-600);"><?php echo intval( $stats['inactive_offers'] ); ?> inactive</span> • 
                                <span style="color: var(--wo-warning-600);"><?php echo intval( $stats['draft_offers'] ); ?> draft</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="wo-card metric-card">
                    <div class="wo-card-body wo-flex wo-items-center">
                        <div class="metric-icon" style="margin-right: var(--wo-space-4); color: var(--wo-success-500);">
                            <span class="dashicons dashicons-money-alt" style="font-size: var(--wo-text-3xl);"></span>
                        </div>
                        <div class="metric-content">
                            <div class="metric-value" style="font-size: var(--wo-text-2xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary);">
                                <?php 
                                if ( function_exists( 'wc_price' ) ) {
                                    echo wc_price( $stats['total_revenue'] );
                                } else {
                                    echo '$' . number_format( $stats['total_revenue'], 2 );
                                }
                                ?>
                            </div>
                            <div class="metric-label" style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary);">
                                <?php _e( 'Total Revenue', 'woo-offers' ); ?>
                            </div>
                            <div class="metric-breakdown" style="font-size: var(--wo-text-xs); color: var(--wo-text-muted); margin-top: var(--wo-space-1);">
                                <?php echo number_format( $stats['total_conversions'] ); ?> conversions
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conversion Rate -->
                <div class="wo-card metric-card">
                    <div class="wo-card-body wo-flex wo-items-center">
                        <div class="metric-icon" style="margin-right: var(--wo-space-4); color: var(--wo-warning-500);">
                            <span class="dashicons dashicons-chart-line" style="font-size: var(--wo-text-3xl);"></span>
                        </div>
                        <div class="metric-content">
                            <div class="metric-value" style="font-size: var(--wo-text-2xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary);">
                                <?php echo $stats['conversion_rate']; ?>%
                            </div>
                            <div class="metric-label" style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary);">
                                <?php _e( 'Conversion Rate', 'woo-offers' ); ?>
                            </div>
                            <div class="metric-breakdown" style="font-size: var(--wo-text-xs); color: var(--wo-text-muted); margin-top: var(--wo-space-1);">
                                <?php echo number_format( $stats['total_views'] ); ?> total views
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Campaigns -->
                <div class="wo-card metric-card">
                    <div class="wo-card-body wo-flex wo-items-center">
                        <div class="metric-icon" style="margin-right: var(--wo-space-4); color: var(--wo-error-500);">
                            <span class="dashicons dashicons-controls-play" style="font-size: var(--wo-text-3xl);"></span>
                        </div>
                        <div class="metric-content">
                            <div class="metric-value" style="font-size: var(--wo-text-2xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary);">
                                <?php echo intval( $stats['active_offers'] ); ?>
                            </div>
                            <div class="metric-label" style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary);">
                                <?php _e( 'Active Campaigns', 'woo-offers' ); ?>
                            </div>
                            <div class="metric-breakdown" style="font-size: var(--wo-text-xs); color: var(--wo-text-muted); margin-top: var(--wo-space-1);">
                                <?php 
                                $active_percentage = $stats['total_offers'] > 0 ? round( ( $stats['active_offers'] / $stats['total_offers'] ) * 100 ) : 0;
                                echo $active_percentage . '% of total';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filters and Controls -->
        <section class="campaigns-filters wo-card" style="margin-bottom: var(--wo-space-6);">
            <div class="wo-card-body">
                <form id="campaigns-filter" method="get" class="wo-flex wo-flex-col wo-gap-4">
                    <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ?? 'woo-offers-offers' ); ?>" />
                    
                    <div class="wo-flex wo-justify-between wo-items-center">
                        <h3 style="margin: 0; font-size: var(--wo-text-lg); font-weight: var(--wo-font-semibold);">
                            <?php _e( 'Filter & Search Campaigns', 'woo-offers' ); ?>
                        </h3>
                        <div class="view-toggle wo-flex wo-gap-2">
                            <button type="button" 
                                    class="wo-btn wo-btn-sm <?php echo $current_view === 'cards' ? 'wo-btn-primary' : 'wo-btn-secondary'; ?>" 
                                    data-view="cards">
                                <span class="dashicons dashicons-grid-view"></span>
                                <?php _e( 'Cards', 'woo-offers' ); ?>
                            </button>
                            <button type="button" 
                                    class="wo-btn wo-btn-sm <?php echo $current_view === 'table' ? 'wo-btn-primary' : 'wo-btn-secondary'; ?>" 
                                    data-view="table">
                                <span class="dashicons dashicons-list-view"></span>
                                <?php _e( 'Table', 'woo-offers' ); ?>
                            </button>
                        </div>
                    </div>

                    <div class="wo-grid wo-grid-cols-1 wo-md:grid-cols-4 wo-gap-4">
                        <!-- Search Input -->
                        <div class="search-field">
                            <label for="campaign-search" class="wo-sr-only"><?php _e( 'Search Campaigns', 'woo-offers' ); ?></label>
                            <input type="search" 
                                   id="campaign-search" 
                                   name="s" 
                                   value="<?php echo esc_attr( $current_search ); ?>"
                                   placeholder="<?php _e( 'Search campaigns...', 'woo-offers' ); ?>"
                                   style="width: 100%; padding: var(--wo-space-3); border: var(--wo-border-width) solid var(--wo-border-secondary); border-radius: var(--wo-border-radius); font-size: var(--wo-text-sm);">
                        </div>

                        <!-- Status Filter -->
                        <div class="status-filter">
                            <label for="status-filter" class="wo-sr-only"><?php _e( 'Filter by Status', 'woo-offers' ); ?></label>
                            <select id="status-filter" 
                                    name="status" 
                                    style="width: 100%; padding: var(--wo-space-3); border: var(--wo-border-width) solid var(--wo-border-secondary); border-radius: var(--wo-border-radius); font-size: var(--wo-text-sm);">
                                <option value=""><?php _e( 'All Statuses', 'woo-offers' ); ?></option>
                                <option value="active" <?php selected( $current_status, 'active' ); ?>><?php _e( 'Active', 'woo-offers' ); ?></option>
                                <option value="inactive" <?php selected( $current_status, 'inactive' ); ?>><?php _e( 'Inactive', 'woo-offers' ); ?></option>
                                <option value="draft" <?php selected( $current_status, 'draft' ); ?>><?php _e( 'Draft', 'woo-offers' ); ?></option>
                            </select>
                        </div>

                        <!-- Type Filter -->
                        <div class="type-filter">
                            <label for="type-filter" class="wo-sr-only"><?php _e( 'Filter by Type', 'woo-offers' ); ?></label>
                            <select id="type-filter" 
                                    name="type" 
                                    style="width: 100%; padding: var(--wo-space-3); border: var(--wo-border-width) solid var(--wo-border-secondary); border-radius: var(--wo-border-radius); font-size: var(--wo-text-sm);">
                                <option value=""><?php _e( 'All Types', 'woo-offers' ); ?></option>
                                <option value="upsell" <?php selected( $current_type, 'upsell' ); ?>><?php _e( 'Upsell', 'woo-offers' ); ?></option>
                                <option value="cross_sell" <?php selected( $current_type, 'cross_sell' ); ?>><?php _e( 'Cross-sell', 'woo-offers' ); ?></option>
                                <option value="downsell" <?php selected( $current_type, 'downsell' ); ?>><?php _e( 'Downsell', 'woo-offers' ); ?></option>
                                <option value="bundle" <?php selected( $current_type, 'bundle' ); ?>><?php _e( 'Bundle', 'woo-offers' ); ?></option>
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="filter-actions wo-flex wo-gap-2">
                            <button type="submit" class="wo-btn wo-btn-primary wo-btn-sm">
                                <span class="dashicons dashicons-search" style="margin-right: var(--wo-space-1);"></span>
                                <?php _e( 'Filter', 'woo-offers' ); ?>
                            </button>
                            <a href="<?php echo admin_url( 'admin.php?page=woo-offers-offers' ); ?>" 
                               class="wo-btn wo-btn-secondary wo-btn-sm">
                                <?php _e( 'Clear', 'woo-offers' ); ?>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <!-- Campaigns Content -->
        <section class="campaigns-content">
            <?php if ( $stats['total_offers'] === 0 && empty( $current_search ) ): ?>
                <!-- Empty State -->
                <div class="wo-card" style="text-align: center; padding: var(--wo-space-16);">
                    <div style="color: var(--wo-text-muted); margin-bottom: var(--wo-space-6);">
                        <span class="dashicons dashicons-megaphone" style="font-size: 4rem; opacity: 0.3;"></span>
                    </div>
                    <h3 style="margin: 0 0 var(--wo-space-4); font-size: var(--wo-text-2xl); color: var(--wo-text-primary);">
                        <?php _e( 'No campaigns yet', 'woo-offers' ); ?>
                    </h3>
                    <p style="margin: 0 0 var(--wo-space-6); color: var(--wo-text-secondary); max-width: 32rem; margin-left: auto; margin-right: auto;">
                        <?php _e( 'Start boosting your sales by creating your first marketing campaign. Choose from upsells, cross-sells, bundles, and more.', 'woo-offers' ); ?>
                    </p>
                    <a href="<?php echo admin_url( 'admin.php?page=woo-offers-create-campaign' ); ?>" 
                       class="wo-btn wo-btn-primary wo-btn-lg">
                        <span class="dashicons dashicons-plus-alt2" style="margin-right: var(--wo-space-2);"></span>
                        <?php _e( 'Create Your First Campaign', 'woo-offers' ); ?>
                    </a>
                </div>

            <?php elseif ( empty( $offers_table->items ) ): ?>
                <!-- No Results State -->
                <div class="wo-card" style="text-align: center; padding: var(--wo-space-12);">
                    <div style="color: var(--wo-text-muted); margin-bottom: var(--wo-space-4);">
                        <span class="dashicons dashicons-search" style="font-size: 3rem; opacity: 0.3;"></span>
                    </div>
                    <h3 style="margin: 0 0 var(--wo-space-2); font-size: var(--wo-text-xl); color: var(--wo-text-primary);">
                        <?php _e( 'No campaigns found', 'woo-offers' ); ?>
                    </h3>
                    <p style="margin: 0 0 var(--wo-space-4); color: var(--wo-text-secondary);">
                        <?php _e( 'Try adjusting your filters or search terms.', 'woo-offers' ); ?>
                    </p>
                    <a href="<?php echo admin_url( 'admin.php?page=woo-offers-offers' ); ?>" 
                       class="wo-btn wo-btn-secondary">
                        <?php _e( 'Clear Filters', 'woo-offers' ); ?>
                    </a>
                </div>

            <?php else: ?>
                <!-- Campaigns List -->
                <div id="campaigns-view-cards" class="campaigns-cards-view <?php echo $current_view === 'cards' ? '' : 'hidden'; ?>">
                    <div class="wo-grid wo-grid-cols-1 wo-md:grid-cols-2 wo-lg:grid-cols-3 wo-gap-6">
                        <?php foreach ( $offers_table->items as $campaign ): ?>
                            <?php 
                            $edit_url = add_query_arg([
                                'page'   => 'woo-offers-create',
                                'action' => 'edit',
                                'id'     => $campaign['id']
                            ], admin_url( 'admin.php' ));

                            $status_colors = [
                                'active'   => 'var(--wo-success-500)',
                                'inactive' => 'var(--wo-error-500)',
                                'draft'    => 'var(--wo-warning-500)'
                            ];
                            $status_color = $status_colors[$campaign['status']] ?? 'var(--wo-gray-500)';

                            $conversion_rate = 0;
                            if ( isset( $campaign['views'] ) && $campaign['views'] > 0 ) {
                                $conversion_rate = round( ( $campaign['conversions'] / $campaign['views'] ) * 100, 2 );
                            }
                            ?>
                            <article class="wo-card campaign-card">
                                <!-- Campaign Header -->
                                <div class="wo-card-header wo-flex wo-justify-between wo-items-start">
                                    <div style="flex: 1;">
                                        <h4 style="margin: 0 0 var(--wo-space-2); font-size: var(--wo-text-lg); font-weight: var(--wo-font-semibold);">
                                            <a href="<?php echo esc_url( $edit_url ); ?>" 
                                               style="color: var(--wo-text-primary); text-decoration: none;">
                                                <?php echo esc_html( $campaign['title'] ); ?>
                                            </a>
                                        </h4>
                                        <div style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary);">
                                            <?php echo esc_html( ucfirst( str_replace( '_', ' ', $campaign['type'] ) ) ); ?>
                                        </div>
                                    </div>
                                    <div class="campaign-status" 
                                         style="padding: var(--wo-space-1) var(--wo-space-3); background: <?php echo $status_color; ?>; color: white; border-radius: var(--wo-border-radius-full); font-size: var(--wo-text-xs); font-weight: var(--wo-font-medium);">
                                        <?php echo esc_html( ucfirst( $campaign['status'] ) ); ?>
                                    </div>
                                </div>

                                <!-- Campaign Metrics -->
                                <div class="wo-card-body">
                                    <div class="wo-grid wo-grid-cols-2 wo-gap-4">
                                        <div class="metric">
                                            <div style="font-size: var(--wo-text-2xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary);">
                                                <?php echo number_format( $campaign['conversions'] ); ?>
                                            </div>
                                            <div style="font-size: var(--wo-text-xs); color: var(--wo-text-muted);">
                                                <?php _e( 'Conversions', 'woo-offers' ); ?>
                                            </div>
                                        </div>
                                        <div class="metric">
                                            <div style="font-size: var(--wo-text-2xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary);">
                                                <?php echo $conversion_rate; ?>%
                                            </div>
                                            <div style="font-size: var(--wo-text-xs); color: var(--wo-text-muted);">
                                                <?php _e( 'Conv. Rate', 'woo-offers' ); ?>
                                            </div>
                                        </div>
                                        <div class="metric wo-flex wo-items-center">
                                            <div>
                                                <div style="font-size: var(--wo-text-base); font-weight: var(--wo-font-medium); color: var(--wo-text-primary);">
                                                    <?php 
                                                    if ( function_exists( 'wc_price' ) ) {
                                                        echo wc_price( $campaign['revenue'] );
                                                    } else {
                                                        echo '$' . number_format( $campaign['revenue'], 2 );
                                                    }
                                                    ?>
                                                </div>
                                                <div style="font-size: var(--wo-text-xs); color: var(--wo-text-muted);">
                                                    <?php _e( 'Revenue', 'woo-offers' ); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="metric">
                                            <div style="font-size: var(--wo-text-base); color: var(--wo-text-secondary);">
                                                <?php 
                                                $date = new DateTime( $campaign['date'] );
                                                echo $date->format( 'M d, Y' );
                                                ?>
                                            </div>
                                            <div style="font-size: var(--wo-text-xs); color: var(--wo-text-muted);">
                                                <?php _e( 'Created', 'woo-offers' ); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Campaign Actions -->
                                <div class="wo-card-footer wo-flex wo-justify-between wo-items-center">
                                    <div class="wo-flex wo-gap-2">
                                        <a href="<?php echo esc_url( $edit_url ); ?>" 
                                           class="wo-btn wo-btn-sm wo-btn-primary">
                                            <?php _e( 'Edit', 'woo-offers' ); ?>
                                        </a>
                                        <button type="button" 
                                                class="wo-btn wo-btn-sm wo-btn-secondary"
                                                data-campaign-id="<?php echo esc_attr( $campaign['id'] ); ?>"
                                                data-action="duplicate">
                                            <?php _e( 'Duplicate', 'woo-offers' ); ?>
                                        </button>
                                    </div>
                                    <div class="campaign-menu">
                                        <button type="button" 
                                                class="wo-btn wo-btn-sm wo-btn-secondary"
                                                data-campaign-id="<?php echo esc_attr( $campaign['id'] ); ?>"
                                                data-action="menu">
                                            <span class="dashicons dashicons-ellipsis"></span>
                                            <span class="wo-sr-only"><?php _e( 'More actions', 'woo-offers' ); ?></span>
                                        </button>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Table View (Legacy) -->
                <div id="campaigns-view-table" class="campaigns-table-view <?php echo $current_view === 'table' ? '' : 'hidden'; ?>">
                    <div class="wo-card">
                        <div class="wo-card-body" style="padding: 0;">
                            <form id="offers-table-form" method="get">
                                <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ?? 'woo-offers-offers' ); ?>" />
                                <?php $offers_table->display(); ?>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ( $offers_table->get_pagination_arg( 'total_pages' ) > 1 ): ?>
                    <div class="pagination-wrapper wo-flex wo-justify-center" style="margin-top: var(--wo-space-8);">
                        <?php $offers_table->pagination( 'bottom' ); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
</div>

<style>
/* Campaign Management Specific Styles */
.campaigns-header {
    padding: var(--wo-space-6) 0;
    border-bottom: var(--wo-border-width) solid var(--wo-border-primary);
}

.metric-card {
    transition: transform var(--wo-transition-fast), box-shadow var(--wo-transition-fast);
    cursor: pointer;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--wo-shadow-lg);
}

.campaign-card {
    transition: transform var(--wo-transition-fast), box-shadow var(--wo-transition-fast);
}

.campaign-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--wo-shadow-lg);
}

.hidden {
    display: none !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .campaigns-header {
        flex-direction: column;
        gap: var(--wo-space-4);
        align-items: flex-start;
    }
    
    .campaigns-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .wo-grid {
        grid-template-columns: 1fr;
    }
    
    .campaigns-filters .wo-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        justify-content: stretch;
    }
    
    .filter-actions .wo-btn {
        flex: 1;
    }
}

/* Animation for view switching */
.campaigns-cards-view,
.campaigns-table-view {
    transition: opacity var(--wo-transition), transform var(--wo-transition);
}

.campaigns-cards-view.hidden,
.campaigns-table-view.hidden {
    opacity: 0;
    transform: translateY(10px);
}
</style>

<?php
// Enqueue necessary scripts for enhanced functionality
add_action( 'admin_footer', function() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // View toggle functionality
        $('.view-toggle button').on('click', function() {
            var view = $(this).data('view');
            
            // Update button states
            $('.view-toggle button').removeClass('wo-btn-primary').addClass('wo-btn-secondary');
            $(this).removeClass('wo-btn-secondary').addClass('wo-btn-primary');
            
            // Toggle views
            if (view === 'cards') {
                $('#campaigns-view-table').addClass('hidden');
                $('#campaigns-view-cards').removeClass('hidden');
            } else {
                $('#campaigns-view-cards').addClass('hidden');
                $('#campaigns-view-table').removeClass('hidden');
            }
            
            // Store preference
            localStorage.setItem('woo_offers_campaigns_view', view);
        });
        
        // Restore view preference
        var savedView = localStorage.getItem('woo_offers_campaigns_view');
        if (savedView && savedView !== 'cards') {
            $('.view-toggle button[data-view="' + savedView + '"]').click();
        }
        
        // Auto-submit form on filter changes
        $('#status-filter, #type-filter').on('change', function() {
            $('#campaigns-filter').submit();
        });
        
        // Search input with debounce
        var searchTimeout;
        $('#campaign-search').on('input', function() {
            clearTimeout(searchTimeout);
            var value = $(this).val();
            
            searchTimeout = setTimeout(function() {
                if (value.length >= 3 || value.length === 0) {
                    $('#campaigns-filter').submit();
                }
            }, 500);
        });
        
        // Campaign actions
        $('[data-action="duplicate"]').on('click', function() {
            var campaignId = $(this).data('campaign-id');
            if (confirm('<?php _e( "Are you sure you want to duplicate this campaign?", "woo-offers" ); ?>')) {
                // Implement duplicate functionality
                window.location.href = '<?php echo admin_url( "admin.php?page=woo-offers-offers&action=duplicate&id=" ); ?>' + campaignId;
            }
        });
        
        // Metric card click to navigate
        $('.metric-card').on('click', function() {
            window.location.href = '<?php echo admin_url( "admin.php?page=woo-offers-analytics" ); ?>';
        });
    });
    </script>
    <?php
});
?> 