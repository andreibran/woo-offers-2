<?php
/**
 * Modern Analytics Dashboard Template
 * Professional, responsive analytics page with advanced visualizations
 *
 * @package WooOffers
 * @since 3.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get instances
use WooOffers\Analytics\AnalyticsManager;
use WooOffers\Campaigns\CampaignManager;

$analytics = new \WooOffers\Admin\Analytics();
$date_range = isset($_GET['days']) ? ['days' => intval($_GET['days'])] : ['days' => 30];
$overview_stats = $analytics->get_overview_stats( $date_range );
$chart_data = $analytics->get_chart_data( $date_range );
$top_offers = $analytics->get_top_offers( 10, $date_range );
$recent_activity = $analytics->get_recent_activity( 15 );
?>

<div class="woo-offers-modern wo-reset">
    <!-- Skip Links for Accessibility -->
    <a href="#main-analytics-content" class="wo-skip-link">
        <?php _e( 'Skip to analytics content', 'woo-offers' ); ?>
    </a>

    <!-- Modern Analytics Header -->
    <header class="wo-admin-header" style="background: var(--wo-bg-secondary); border-bottom: var(--wo-border-width) solid var(--wo-border-primary); padding: var(--wo-space-6) 0;">
        <div class="wo-container">
            <div class="wo-flex wo-items-center wo-justify-between wo-gap-6">
                <div class="header-content">
                    <!-- Breadcrumbs -->
                    <nav aria-label="<?php _e( 'Breadcrumb', 'woo-offers' ); ?>" style="margin-bottom: var(--wo-space-2);">
                        <ol class="wo-flex wo-gap-2" style="list-style: none; margin: 0; padding: 0; font-size: var(--wo-text-sm); color: var(--wo-text-secondary);">
                            <li><a href="<?php echo admin_url( 'admin.php?page=woo-offers' ); ?>" style="color: var(--wo-text-secondary); text-decoration: none;"><?php _e( 'Woo Offers', 'woo-offers' ); ?></a></li>
                            <li style="color: var(--wo-text-muted);">/</li>
                            <li style="color: var(--wo-text-primary); font-weight: var(--wo-font-medium);"><?php _e( 'Analytics', 'woo-offers' ); ?></li>
                        </ol>
                    </nav>

                    <h1 style="margin: 0; font-size: var(--wo-text-3xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary);">
                        <span class="dashicons dashicons-chart-line" style="font-size: var(--wo-text-2xl); margin-right: var(--wo-space-3); color: var(--wo-primary-500);"></span>
                        <?php _e( 'Analytics Dashboard', 'woo-offers' ); ?>
                    </h1>
                    <p style="margin: var(--wo-space-2) 0 0; font-size: var(--wo-text-base); color: var(--wo-text-secondary);">
                        <?php _e( 'Comprehensive insights and performance analytics for your offer campaigns', 'woo-offers' ); ?>
                    </p>
                </div>

                <div class="header-actions wo-flex wo-gap-3">
                    <!-- Date Range Selector -->
                    <div class="date-range-selector" style="position: relative;">
                        <label for="analytics-date-range" class="wo-sr-only"><?php _e( 'Select date range', 'woo-offers' ); ?></label>
                        <select id="analytics-date-range" name="date_range" class="wo-select" 
                                style="padding: var(--wo-space-2) var(--wo-space-4); border: var(--wo-border-width) solid var(--wo-border-primary); border-radius: var(--wo-border-radius); background: var(--wo-bg-secondary); color: var(--wo-text-primary); font-size: var(--wo-text-sm);">
                            <option value="7" <?php selected( ($date_range['days'] ?? 30), 7 ); ?>><?php _e( 'Last 7 days', 'woo-offers' ); ?></option>
                            <option value="30" <?php selected( ($date_range['days'] ?? 30), 30 ); ?>><?php _e( 'Last 30 days', 'woo-offers' ); ?></option>
                            <option value="90" <?php selected( ($date_range['days'] ?? 30), 90 ); ?>><?php _e( 'Last 90 days', 'woo-offers' ); ?></option>
                            <option value="365" <?php selected( ($date_range['days'] ?? 30), 365 ); ?>><?php _e( 'Last year', 'woo-offers' ); ?></option>
                            <option value="custom"><?php _e( 'Custom range', 'woo-offers' ); ?></option>
                        </select>
                    </div>

                    <!-- Export Button -->
                    <button type="button" id="export-analytics-data" class="wo-btn wo-btn-outline" 
                            data-tooltip="<?php _e( 'Export analytics data to CSV', 'woo-offers' ); ?>">
                        <span class="dashicons dashicons-download" style="font-size: 16px;"></span>
                        <?php _e( 'Export', 'woo-offers' ); ?>
                    </button>

                    <!-- Refresh Button -->
                    <button type="button" id="refresh-analytics" class="wo-btn wo-btn-secondary"
                            data-tooltip="<?php _e( 'Refresh analytics data', 'woo-offers' ); ?>">
                        <span class="dashicons dashicons-update" style="font-size: 16px;"></span>
                        <span class="wo-sr-only"><?php _e( 'Refresh data', 'woo-offers' ); ?></span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main id="main-analytics-content" class="wo-analytics-content" style="padding: var(--wo-space-8) 0;">
        <div class="wo-container">
            
            <!-- Key Metrics Overview -->
            <section class="analytics-overview" aria-labelledby="overview-heading" style="margin-bottom: var(--wo-space-10);">
                <h2 id="overview-heading" class="wo-sr-only"><?php _e( 'Key Metrics Overview', 'woo-offers' ); ?></h2>
                
                <div class="wo-grid wo-grid-cols-1 wo-md:grid-cols-2 wo-lg:grid-cols-4 wo-gap-6">
                    <!-- Total Views Card -->
                    <div class="wo-card metric-card" style="position: relative; overflow: hidden;" 
                         data-metric="views" tabindex="0" role="button" 
                         aria-label="<?php _e( 'Total Views metric details', 'woo-offers' ); ?>">
                        <div class="wo-card-body wo-flex wo-items-center wo-gap-4">
                            <div class="metric-icon" style="width: 48px; height: 48px; background: var(--wo-primary-100); border-radius: var(--wo-border-radius-lg); display: flex; align-items: center; justify-content: center;">
                                <span class="dashicons dashicons-visibility" style="font-size: 24px; color: var(--wo-primary-600);"></span>
                            </div>
                            <div class="metric-content wo-flex wo-flex-col">
                                <div class="metric-number" style="font-size: var(--wo-text-2xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary); line-height: var(--wo-leading-tight);">
                                    <?php echo number_format( $overview_stats['total_views'] ); ?>
                                </div>
                                <div class="metric-label" style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary); margin-bottom: var(--wo-space-1);">
                                    <?php _e( 'Total Views', 'woo-offers' ); ?>
                                </div>
                                <div class="metric-change positive" style="font-size: var(--wo-text-xs); color: var(--wo-success-600); font-weight: var(--wo-font-medium);">
                                    <span class="dashicons dashicons-arrow-up-alt" style="font-size: 12px;"></span>
                                    +<?php echo number_format( $overview_stats['views_change'], 1 ); ?>% <?php _e( 'vs last period', 'woo-offers' ); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversions Card -->
                    <div class="wo-card metric-card" style="position: relative; overflow: hidden;" 
                         data-metric="conversions" tabindex="0" role="button"
                         aria-label="<?php _e( 'Conversions metric details', 'woo-offers' ); ?>">
                        <div class="wo-card-body wo-flex wo-items-center wo-gap-4">
                            <div class="metric-icon" style="width: 48px; height: 48px; background: var(--wo-success-100); border-radius: var(--wo-border-radius-lg); display: flex; align-items: center; justify-content: center;">
                                <span class="dashicons dashicons-yes-alt" style="font-size: 24px; color: var(--wo-success-600);"></span>
                            </div>
                            <div class="metric-content wo-flex wo-flex-col">
                                <div class="metric-number" style="font-size: var(--wo-text-2xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary); line-height: var(--wo-leading-tight);">
                                    <?php echo number_format( $overview_stats['total_conversions'] ); ?>
                                </div>
                                <div class="metric-label" style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary); margin-bottom: var(--wo-space-1);">
                                    <?php _e( 'Conversions', 'woo-offers' ); ?>
                                </div>
                                <div class="metric-change positive" style="font-size: var(--wo-text-xs); color: var(--wo-success-600); font-weight: var(--wo-font-medium);">
                                    <span class="dashicons dashicons-arrow-up-alt" style="font-size: 12px;"></span>
                                    +<?php echo number_format( $overview_stats['conversions_change'], 1 ); ?>% <?php _e( 'vs last period', 'woo-offers' ); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversion Rate Card -->
                    <div class="wo-card metric-card" style="position: relative; overflow: hidden;" 
                         data-metric="conversion_rate" tabindex="0" role="button"
                         aria-label="<?php _e( 'Conversion Rate metric details', 'woo-offers' ); ?>">
                        <div class="wo-card-body wo-flex wo-items-center wo-gap-4">
                            <div class="metric-icon" style="width: 48px; height: 48px; background: var(--wo-warning-100); border-radius: var(--wo-border-radius-lg); display: flex; align-items: center; justify-content: center;">
                                <span class="dashicons dashicons-chart-line" style="font-size: 24px; color: var(--wo-warning-600);"></span>
                            </div>
                            <div class="metric-content wo-flex wo-flex-col">
                                <div class="metric-number" style="font-size: var(--wo-text-2xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary); line-height: var(--wo-leading-tight);">
                                    <?php echo number_format( $overview_stats['conversion_rate'], 2 ); ?>%
                                </div>
                                <div class="metric-label" style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary); margin-bottom: var(--wo-space-1);">
                                    <?php _e( 'Conversion Rate', 'woo-offers' ); ?>
                                </div>
                                <div class="metric-change <?php echo $overview_stats['rate_change'] >= 0 ? 'positive' : 'negative'; ?>" style="font-size: var(--wo-text-xs); color: <?php echo $overview_stats['rate_change'] >= 0 ? 'var(--wo-success-600)' : 'var(--wo-error-600)'; ?>; font-weight: var(--wo-font-medium);">
                                    <span class="dashicons dashicons-arrow-<?php echo $overview_stats['rate_change'] >= 0 ? 'up' : 'down'; ?>-alt" style="font-size: 12px;"></span>
                                    <?php echo ( $overview_stats['rate_change'] >= 0 ? '+' : '' ) . number_format( $overview_stats['rate_change'], 1 ); ?>% <?php _e( 'vs last period', 'woo-offers' ); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Card -->
                    <div class="wo-card metric-card" style="position: relative; overflow: hidden;" 
                         data-metric="revenue" tabindex="0" role="button"
                         aria-label="<?php _e( 'Revenue metric details', 'woo-offers' ); ?>">
                        <div class="wo-card-body wo-flex wo-items-center wo-gap-4">
                            <div class="metric-icon" style="width: 48px; height: 48px; background: var(--wo-primary-100); border-radius: var(--wo-border-radius-lg); display: flex; align-items: center; justify-content: center;">
                                <span class="dashicons dashicons-money-alt" style="font-size: 24px; color: var(--wo-primary-600);"></span>
                            </div>
                            <div class="metric-content wo-flex wo-flex-col">
                                <div class="metric-number" style="font-size: var(--wo-text-2xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary); line-height: var(--wo-leading-tight);">
                                    <?php echo wc_price( $overview_stats['total_revenue'] ); ?>
                                </div>
                                <div class="metric-label" style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary); margin-bottom: var(--wo-space-1);">
                                    <?php _e( 'Total Revenue', 'woo-offers' ); ?>
                                </div>
                                <div class="metric-change positive" style="font-size: var(--wo-text-xs); color: var(--wo-success-600); font-weight: var(--wo-font-medium);">
                                    <span class="dashicons dashicons-arrow-up-alt" style="font-size: 12px;"></span>
                                    +<?php echo number_format( $overview_stats['revenue_change'], 1 ); ?>% <?php _e( 'vs last period', 'woo-offers' ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Charts Section -->
            <section class="analytics-charts" aria-labelledby="charts-heading" style="margin-bottom: var(--wo-space-10);">
                <h2 id="charts-heading" style="font-size: var(--wo-text-xl); font-weight: var(--wo-font-semibold); color: var(--wo-text-primary); margin-bottom: var(--wo-space-6);">
                    <?php _e( 'Performance Analytics', 'woo-offers' ); ?>
                </h2>

                <div class="wo-grid wo-grid-cols-1 wo-lg:grid-cols-3 wo-gap-6">
                    <!-- Performance Over Time Chart (Large) -->
                    <div class="wo-lg:col-span-2">
                        <div class="wo-card">
                            <div class="wo-card-header wo-flex wo-items-center wo-justify-between">
                                <h3 style="font-size: var(--wo-text-lg); font-weight: var(--wo-font-semibold); color: var(--wo-text-primary); margin: 0;">
                                    <?php _e( 'Performance Over Time', 'woo-offers' ); ?>
                                </h3>
                                <div class="chart-controls wo-flex wo-gap-2">
                                    <button type="button" class="chart-toggle active" data-chart="line" style="padding: var(--wo-space-1) var(--wo-space-2); border: var(--wo-border-width) solid var(--wo-border-primary); border-radius: var(--wo-border-radius); background: var(--wo-primary-500); color: white; font-size: var(--wo-text-xs);">
                                        <?php _e( 'Line', 'woo-offers' ); ?>
                                    </button>
                                    <button type="button" class="chart-toggle" data-chart="bar" style="padding: var(--wo-space-1) var(--wo-space-2); border: var(--wo-border-width) solid var(--wo-border-primary); border-radius: var(--wo-border-radius); background: var(--wo-bg-secondary); color: var(--wo-text-primary); font-size: var(--wo-text-xs);">
                                        <?php _e( 'Bar', 'woo-offers' ); ?>
                                    </button>
                                </div>
                            </div>
                            <div class="wo-card-body">
                                <div class="chart-container" style="position: relative; height: 350px;">
                                    <canvas id="performance-chart" role="img" 
                                            aria-label="<?php _e( 'Performance over time chart showing views, conversions, and revenue trends', 'woo-offers' ); ?>">
                                        <p><?php _e( 'Performance chart data not available. Please enable JavaScript.', 'woo-offers' ); ?></p>
                                    </canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversion Funnel (Medium) -->
                    <div class="wo-card">
                        <div class="wo-card-header">
                            <h3 style="font-size: var(--wo-text-lg); font-weight: var(--wo-font-semibold); color: var(--wo-text-primary); margin: 0;">
                                <?php _e( 'Conversion Funnel', 'woo-offers' ); ?>
                            </h3>
                        </div>
                        <div class="wo-card-body">
                            <div class="chart-container" style="position: relative; height: 350px;">
                                <canvas id="funnel-chart" role="img"
                                        aria-label="<?php _e( 'Conversion funnel showing the flow from views to conversions', 'woo-offers' ); ?>">
                                    <p><?php _e( 'Conversion funnel chart not available. Please enable JavaScript.', 'woo-offers' ); ?></p>
                                </canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Data Tables Section -->
            <section class="analytics-tables" aria-labelledby="tables-heading">
                <h2 id="tables-heading" style="font-size: var(--wo-text-xl); font-weight: var(--wo-font-semibold); color: var(--wo-text-primary); margin-bottom: var(--wo-space-6);">
                    <?php _e( 'Detailed Analytics', 'woo-offers' ); ?>
                </h2>

                <div class="wo-grid wo-grid-cols-1 wo-lg:grid-cols-2 wo-gap-6">
                    <!-- Top Performing Offers -->
                    <div class="wo-card">
                        <div class="wo-card-header wo-flex wo-items-center wo-justify-between">
                            <h3 style="font-size: var(--wo-text-lg); font-weight: var(--wo-font-semibold); color: var(--wo-text-primary); margin: 0;">
                                <?php _e( 'Top Performing Offers', 'woo-offers' ); ?>
                            </h3>
                            <a href="<?php echo admin_url( 'admin.php?page=woo-offers' ); ?>" class="wo-btn wo-btn-sm wo-btn-outline">
                                <?php _e( 'View All', 'woo-offers' ); ?>
                            </a>
                        </div>
                        <div class="wo-card-body" style="padding: 0;">
                            <?php if ( ! empty( $top_offers ) ) : ?>
                                <!-- Top offers table would go here -->
                                <p style="padding: var(--wo-space-4); color: var(--wo-text-secondary);"><?php _e( 'Top offers data will be displayed here.', 'woo-offers' ); ?></p>
                            <?php else : ?>
                                <div class="wo-empty-state">
                                    <div class="wo-empty-icon">
                                        <span class="dashicons dashicons-chart-bar" style="font-size: 48px;"></span>
                                    </div>
                                    <h3 class="wo-empty-title"><?php _e( 'No Performance Data', 'woo-offers' ); ?></h3>
                                    <p class="wo-empty-description"><?php _e( 'No offer performance data available for the selected period.', 'woo-offers' ); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="wo-card">
                        <div class="wo-card-header">
                            <h3 style="font-size: var(--wo-text-lg); font-weight: var(--wo-font-semibold); color: var(--wo-text-primary); margin: 0;">
                                <?php _e( 'Recent Activity', 'woo-offers' ); ?>
                            </h3>
                        </div>
                        <div class="wo-card-body" style="padding: 0;">
                            <?php if ( ! empty( $recent_activity ) ) : ?>
                                <!-- Recent activity list would go here -->
                                <p style="padding: var(--wo-space-4); color: var(--wo-text-secondary);"><?php _e( 'Recent activity data will be displayed here.', 'woo-offers' ); ?></p>
                            <?php else : ?>
                                <div class="wo-empty-state">
                                    <div class="wo-empty-icon">
                                        <span class="dashicons dashicons-clock" style="font-size: 48px;"></span>
                                    </div>
                                    <h3 class="wo-empty-title"><?php _e( 'No Recent Activity', 'woo-offers' ); ?></h3>
                                    <p class="wo-empty-description"><?php _e( 'No recent activity to display.', 'woo-offers' ); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>

<!-- Chart Data for JavaScript -->
<script type="application/json" id="analytics-chart-data">
<?php
echo json_encode([
    'performance' => $chart_data,
    'overview' => $overview_stats,
    'dateRange' => $date_range,
    'strings' => [
        'loading' => __( 'Loading...', 'woo-offers' ),
        'error' => __( 'Error loading data', 'woo-offers' ),
        'noData' => __( 'No data available', 'woo-offers' ),
        'views' => __( 'Views', 'woo-offers' ),
        'conversions' => __( 'Conversions', 'woo-offers' ),
        'revenue' => __( 'Revenue', 'woo-offers' ),
        'conversionRate' => __( 'Conversion Rate', 'woo-offers' ),
    ]
]);
?>
</script>
