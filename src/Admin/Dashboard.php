<?php

namespace WooOffers\Admin;

use WooOffers\Analytics\AnalyticsManager;
use WooOffers\Campaigns\CampaignManager;

/**
 * Modern Dashboard management for admin interface
 * Integrates with AnalyticsManager for real-time metrics
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Dashboard class for managing modern admin dashboard
 */
class Dashboard {

    /**
     * AnalyticsManager instance
     */
    private $analytics_manager;

    /**
     * CampaignManager instance
     */
    private $campaign_manager;

    /**
     * Constructor
     */
    public function __construct() {
        $this->analytics_manager = AnalyticsManager::get_instance();
        $this->campaign_manager = new CampaignManager();
        
        add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widgets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_dashboard_assets' ] );
    }

    /**
     * Enqueue dashboard-specific assets
     */
    public function enqueue_dashboard_assets( $hook ) {
        // Only load on dashboard page
        if ( 'toplevel_page_woo-offers' !== $hook ) {
            return;
        }

        wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js', [], '4.4.0', true );
        
        wp_localize_script( 'woo-offers-admin', 'wooOffersAnalytics', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'woo_offers_analytics' ),
            'dashboardData' => $this->get_dashboard_data_for_js(),
        ] );
    }

    /**
     * Add dashboard widgets to WordPress dashboard
     */
    public function add_dashboard_widgets() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        wp_add_dashboard_widget(
            'woo_offers_overview',
            __( 'Woo Offers Overview', 'woo-offers' ),
            [ $this, 'render_overview_widget' ]
        );
    }

    /**
     * Render the modern dashboard page
     */
    public function render_dashboard_page() {
        $dashboard_data = $this->get_comprehensive_dashboard_data();
        ?>
        <div class="woo-offers-modern wo-container">
            <!-- Skip Link for Accessibility -->
            <a href="#main-content" class="wo-skip-link"><?php _e( 'Skip to main content', 'woo-offers' ); ?></a>

            <!-- Dashboard Header -->
            <header class="dashboard-header wo-flex wo-justify-between wo-items-center" style="margin-bottom: var(--wo-space-8);">
                <div>
                    <h1 style="margin: 0; font-size: var(--wo-text-3xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary);">
                        <?php _e( 'Campaign Dashboard', 'woo-offers' ); ?>
                    </h1>
                    <p style="margin: var(--wo-space-2) 0 0; color: var(--wo-text-secondary); font-size: var(--wo-text-base);">
                        <?php _e( 'Monitor your campaigns performance and metrics', 'woo-offers' ); ?>
                    </p>
                </div>
                <div class="dashboard-actions wo-flex wo-gap-4">
                    <a href="<?php echo admin_url( 'admin.php?page=woo-offers-create-campaign' ); ?>" 
                       class="wo-btn wo-btn-primary wo-btn-lg">
                        <span class="dashicons dashicons-plus-alt2" style="margin-right: var(--wo-space-2);"></span>
                        <?php _e( 'Create Campaign', 'woo-offers' ); ?>
                    </a>
                    <a href="<?php echo admin_url( 'admin.php?page=woo-offers-analytics' ); ?>" 
                       class="wo-btn wo-btn-outline">
                        <span class="dashicons dashicons-chart-area" style="margin-right: var(--wo-space-2);"></span>
                        <?php _e( 'View Analytics', 'woo-offers' ); ?>
                    </a>
                </div>
            </header>

            <main id="main-content">
                <!-- Key Metrics Cards -->
                <section class="metrics-overview" style="margin-bottom: var(--wo-space-8);">
                    <h2 class="wo-sr-only"><?php _e( 'Key Performance Metrics', 'woo-offers' ); ?></h2>
                    <div class="wo-grid wo-grid-cols-1 wo-md:grid-cols-2 wo-lg:grid-cols-4 wo-gap-6">
                        <?php $this->render_metric_card( __( 'Total Campaigns', 'woo-offers' ), $dashboard_data['total_campaigns'], 'dashicons-megaphone', 'primary' ); ?>
                        <?php $this->render_metric_card( __( 'Active Campaigns', 'woo-offers' ), $dashboard_data['active_campaigns'], 'dashicons-controls-play', 'success' ); ?>
                        <?php $this->render_metric_card( __( 'Total Conversions', 'woo-offers' ), number_format( $dashboard_data['total_conversions'] ), 'dashicons-chart-line', 'warning' ); ?>
                        <?php $this->render_metric_card( __( 'Revenue Generated', 'woo-offers' ), wc_price( $dashboard_data['total_revenue'] ), 'dashicons-money-alt', 'error' ); ?>
                    </div>
                </section>

                <!-- Dashboard Grid Layout -->
                <div class="wo-grid wo-grid-cols-1 wo-lg:grid-cols-3 wo-gap-8">
                    <!-- Main Content Area (2/3 width) -->
                    <div class="wo-lg:col-span-2">
                        <!-- Performance Chart -->
                        <section class="wo-card" style="margin-bottom: var(--wo-space-6);">
                            <div class="wo-card-header">
                                <h3 style="margin: 0; font-size: var(--wo-text-xl); font-weight: var(--wo-font-semibold);">
                                    <?php _e( 'Performance Overview', 'woo-offers' ); ?>
                                </h3>
                                <p style="margin: var(--wo-space-1) 0 0; color: var(--wo-text-secondary); font-size: var(--wo-text-sm);">
                                    <?php _e( 'Campaign performance over the last 30 days', 'woo-offers' ); ?>
                                </p>
                            </div>
                            <div class="wo-card-body">
                                <canvas id="performanceChart" width="400" height="200" aria-label="<?php _e( 'Performance chart showing campaign metrics over time', 'woo-offers' ); ?>"></canvas>
                            </div>
                        </section>

                        <!-- Recent Campaigns -->
                        <section class="wo-card">
                            <div class="wo-card-header">
                                <div class="wo-flex wo-justify-between wo-items-center">
                                    <h3 style="margin: 0; font-size: var(--wo-text-xl); font-weight: var(--wo-font-semibold);">
                                        <?php _e( 'Recent Campaigns', 'woo-offers' ); ?>
                                    </h3>
                                    <a href="<?php echo admin_url( 'admin.php?page=woo-offers-offers' ); ?>" 
                                       class="wo-btn wo-btn-sm wo-btn-secondary">
                                        <?php _e( 'View All', 'woo-offers' ); ?>
                                    </a>
                                </div>
                            </div>
                            <div class="wo-card-body" style="padding: 0;">
                                <?php $this->render_recent_campaigns_table( $dashboard_data['recent_campaigns'] ); ?>
                            </div>
                        </section>
                    </div>

                    <!-- Sidebar (1/3 width) -->
                    <div class="dashboard-sidebar">
                        <!-- Quick Stats -->
                        <section class="wo-card" style="margin-bottom: var(--wo-space-6);">
                            <div class="wo-card-header">
                                <h3 style="margin: 0; font-size: var(--wo-text-lg); font-weight: var(--wo-font-semibold);">
                                    <?php _e( 'Quick Stats', 'woo-offers' ); ?>
                                </h3>
                            </div>
                            <div class="wo-card-body">
                                <?php $this->render_quick_stats( $dashboard_data ); ?>
                            </div>
                        </section>

                        <!-- Recent Activity -->
                        <section class="wo-card">
                            <div class="wo-card-header">
                                <h3 style="margin: 0; font-size: var(--wo-text-lg); font-weight: var(--wo-font-semibold);">
                                    <?php _e( 'Recent Activity', 'woo-offers' ); ?>
                                </h3>
                            </div>
                            <div class="wo-card-body" style="padding: 0;">
                                <?php $this->render_recent_activity_modern( $dashboard_data['recent_activity'] ); ?>
                            </div>
                        </section>
                    </div>
                </div>
            </main>
        </div>

        <style>
        /* Dashboard-specific styles using design tokens */
        .dashboard-header {
            padding: var(--wo-space-6) 0;
            border-bottom: var(--wo-border-width) solid var(--wo-border-primary);
        }

        .metrics-overview .metric-card {
            transition: transform var(--wo-transition-fast);
        }

        .metrics-overview .metric-card:hover {
            transform: translateY(-2px);
        }

        .dashboard-sidebar .wo-card {
            position: sticky;
            top: var(--wo-space-4);
        }

        @media (max-width: 1024px) {
            .dashboard-sidebar .wo-card {
                position: static;
            }
        }
        </style>
        <?php
    }

    /**
     * Render metric card component
     */
    private function render_metric_card( $title, $value, $icon, $variant = 'primary' ) {
        $color_map = [
            'primary' => '--wo-primary-500',
            'success' => '--wo-success-500',
            'warning' => '--wo-warning-500',
            'error' => '--wo-error-500',
        ];
        $color = $color_map[$variant] ?? '--wo-primary-500';
        ?>
        <div class="wo-card metric-card">
            <div class="wo-card-body wo-flex wo-items-center">
                <div class="metric-icon" style="margin-right: var(--wo-space-4); color: var(<?php echo $color; ?>);">
                    <span class="dashicons <?php echo esc_attr( $icon ); ?>" style="font-size: var(--wo-text-3xl);"></span>
                </div>
                <div class="metric-content">
                    <div class="metric-value" style="font-size: var(--wo-text-2xl); font-weight: var(--wo-font-bold); color: var(--wo-text-primary); line-height: var(--wo-leading-tight);">
                        <?php echo wp_kses_post( $value ); ?>
                    </div>
                    <div class="metric-label" style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary); margin-top: var(--wo-space-1);">
                        <?php echo esc_html( $title ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render recent campaigns table
     */
    private function render_recent_campaigns_table( $campaigns ) {
        if ( empty( $campaigns ) ) {
            ?>
            <div style="padding: var(--wo-space-8); text-align: center;">
                <div style="color: var(--wo-text-muted); margin-bottom: var(--wo-space-4);">
                    <span class="dashicons dashicons-info" style="font-size: var(--wo-text-2xl);"></span>
                </div>
                <p style="color: var(--wo-text-secondary); margin-bottom: var(--wo-space-4);">
                    <?php _e( 'No campaigns found. Create your first campaign to get started!', 'woo-offers' ); ?>
                </p>
                <a href="<?php echo admin_url( 'admin.php?page=woo-offers-create-campaign' ); ?>" 
                   class="wo-btn wo-btn-primary">
                    <?php _e( 'Create Campaign', 'woo-offers' ); ?>
                </a>
            </div>
            <?php
            return;
        }
        ?>
        <div class="campaigns-table">
            <?php foreach ( $campaigns as $campaign ): ?>
                <div class="campaign-row wo-flex wo-justify-between wo-items-center" 
                     style="padding: var(--wo-space-4) var(--wo-space-6); border-bottom: var(--wo-border-width) solid var(--wo-border-primary);">
                    <div class="campaign-info">
                        <div class="campaign-name" style="font-weight: var(--wo-font-medium); color: var(--wo-text-primary);">
                            <?php echo esc_html( $campaign['name'] ); ?>
                        </div>
                        <div class="campaign-meta" style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary); margin-top: var(--wo-space-1);">
                            <?php echo esc_html( $campaign['type'] ); ?> • <?php echo esc_html( $campaign['status'] ); ?>
                        </div>
                    </div>
                    <div class="campaign-metrics" style="text-align: right;">
                        <div class="conversion-rate" style="font-weight: var(--wo-font-medium); color: var(--wo-text-primary);">
                            <?php echo esc_html( $campaign['conversion_rate'] ); ?>%
                        </div>
                        <div class="revenue" style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary);">
                            <?php echo wc_price( $campaign['revenue'] ); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render quick stats sidebar
     */
    private function render_quick_stats( $data ) {
        $stats = [
            [
                'label' => __( 'Avg. Conversion Rate', 'woo-offers' ),
                'value' => number_format( $data['avg_conversion_rate'], 2 ) . '%',
                'change' => $data['conversion_rate_change'],
                'icon' => 'dashicons-chart-line'
            ],
            [
                'label' => __( 'Avg. Click-Through Rate', 'woo-offers' ),
                'value' => number_format( $data['avg_ctr'], 2 ) . '%',
                'change' => $data['ctr_change'],
                'icon' => 'dashicons-admin-links'
            ],
            [
                'label' => __( 'Total Views', 'woo-offers' ),
                'value' => number_format( $data['total_views'] ),
                'change' => $data['views_change'],
                'icon' => 'dashicons-visibility'
            ],
        ];

        foreach ( $stats as $stat ):
            $change_class = $stat['change'] >= 0 ? 'positive' : 'negative';
            $change_color = $stat['change'] >= 0 ? 'var(--wo-success-600)' : 'var(--wo-error-600)';
            $change_icon = $stat['change'] >= 0 ? '↗' : '↘';
            ?>
            <div class="stat-row wo-flex wo-justify-between wo-items-center" 
                 style="padding: var(--wo-space-3) 0; border-bottom: var(--wo-border-width) solid var(--wo-border-primary);">
                <div class="wo-flex wo-items-center">
                    <span class="<?php echo esc_attr( $stat['icon'] ); ?>" 
                          style="color: var(--wo-text-muted); margin-right: var(--wo-space-3);"></span>
                    <div>
                        <div style="font-size: var(--wo-text-sm); color: var(--wo-text-secondary);">
                            <?php echo esc_html( $stat['label'] ); ?>
                        </div>
                        <div style="font-weight: var(--wo-font-medium); color: var(--wo-text-primary);">
                            <?php echo esc_html( $stat['value'] ); ?>
                        </div>
                    </div>
                </div>
                <div style="color: <?php echo $change_color; ?>; font-size: var(--wo-text-sm); font-weight: var(--wo-font-medium);">
                    <?php echo $change_icon . abs( $stat['change'] ) . '%'; ?>
                </div>
            </div>
            <?php
        endforeach;
    }

    /**
     * Render modern recent activity
     */
    private function render_recent_activity_modern( $activities ) {
        if ( empty( $activities ) ) {
            ?>
            <div style="padding: var(--wo-space-6); text-align: center;">
                <p style="color: var(--wo-text-secondary);">
                    <?php _e( 'No recent activity', 'woo-offers' ); ?>
                </p>
            </div>
            <?php
            return;
        }
        ?>
        <div class="activity-feed">
            <?php foreach ( $activities as $activity ): ?>
                <div class="activity-item wo-flex" 
                     style="padding: var(--wo-space-4) var(--wo-space-6); border-bottom: var(--wo-border-width) solid var(--wo-border-primary);">
                    <div class="activity-icon" style="margin-right: var(--wo-space-3); color: var(--wo-text-muted);">
                        <span class="dashicons <?php echo esc_attr( $activity['icon'] ); ?>"></span>
                    </div>
                    <div class="activity-content">
                        <div style="font-size: var(--wo-text-sm); color: var(--wo-text-primary);">
                            <?php echo wp_kses_post( $activity['message'] ); ?>
                        </div>
                        <div style="font-size: var(--wo-text-xs); color: var(--wo-text-muted); margin-top: var(--wo-space-1);">
                            <?php echo esc_html( $activity['time_ago'] ); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Get comprehensive dashboard data
     */
    public function get_comprehensive_dashboard_data() {
        $basic_stats = $this->get_overview_stats();
        
        // Get analytics data
        $analytics_data = $this->analytics_manager->get_dashboard_summary();
        
        // Get recent campaigns
        $recent_campaigns = $this->get_recent_campaigns();
        
        // Get recent activity
        $recent_activity = $this->get_recent_activity();

        return array_merge( $basic_stats, [
            'total_campaigns' => $basic_stats['total_offers'], // Alias for consistency
            'active_campaigns' => $basic_stats['active_offers'], // Alias for consistency
            'avg_conversion_rate' => $analytics_data['avg_conversion_rate'] ?? 0,
            'avg_ctr' => $analytics_data['avg_ctr'] ?? 0,
            'total_views' => $analytics_data['total_views'] ?? 0,
            'conversion_rate_change' => $analytics_data['conversion_rate_change'] ?? 0,
            'ctr_change' => $analytics_data['ctr_change'] ?? 0,
            'views_change' => $analytics_data['views_change'] ?? 0,
            'recent_campaigns' => $recent_campaigns,
            'recent_activity' => $recent_activity,
        ] );
    }

    /**
     * Get recent campaigns data
     */
    private function get_recent_campaigns( $limit = 5 ) {
        global $wpdb;
        $campaigns_table = $wpdb->prefix . 'woo_campaigns';
        
        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT c.*, 
                   COALESCE(SUM(CASE WHEN a.event_type = 'conversion' THEN 1 ELSE 0 END), 0) as conversions,
                   COALESCE(SUM(CASE WHEN a.event_type = 'view' THEN 1 ELSE 0 END), 0) as views,
                   COALESCE(SUM(a.revenue), 0) as revenue
            FROM {$campaigns_table} c 
            LEFT JOIN {$wpdb->prefix}woo_campaign_analytics a ON c.id = a.campaign_id 
            GROUP BY c.id 
            ORDER BY c.created_at DESC 
            LIMIT %d
        ", $limit ) );

        $campaigns = [];
        foreach ( $results as $campaign ) {
            $conversion_rate = $campaign->views > 0 ? ( $campaign->conversions / $campaign->views ) * 100 : 0;
            
            $campaigns[] = [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'type' => ucfirst( $campaign->campaign_type ),
                'status' => ucfirst( $campaign->status ),
                'conversion_rate' => number_format( $conversion_rate, 2 ),
                'revenue' => $campaign->revenue,
                'created_at' => $campaign->created_at,
            ];
        }

        return $campaigns;
    }

    /**
     * Get dashboard data formatted for JavaScript
     */
    private function get_dashboard_data_for_js() {
        // Get 30 days of analytics data for chart
        $chart_data = $this->analytics_manager->get_chart_data( 30 );
        
        return [
            'chartData' => $chart_data,
            'refreshInterval' => 300000, // 5 minutes
        ];
    }

    /**
     * Render overview widget for WordPress dashboard (legacy support)
     */
    public function render_overview_widget() {
        $stats = $this->get_overview_stats();
        ?>
        <div class="woo-offers-dashboard-widget">
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="dashicons dashicons-megaphone"></span>
                    <div class="stat-number"><?php echo esc_html( $stats['total_offers'] ); ?></div>
                    <div class="stat-label"><?php _e( 'Total Offers', 'woo-offers' ); ?></div>
                </div>
                <div class="stat-item">
                    <span class="dashicons dashicons-controls-play"></span>
                    <div class="stat-number"><?php echo esc_html( $stats['active_offers'] ); ?></div>
                    <div class="stat-label"><?php _e( 'Active Offers', 'woo-offers' ); ?></div>
                </div>
                <div class="stat-item">
                    <span class="dashicons dashicons-chart-line"></span>
                    <div class="stat-number"><?php echo esc_html( $stats['total_conversions'] ); ?></div>
                    <div class="stat-label"><?php _e( 'Conversions', 'woo-offers' ); ?></div>
                </div>
                <div class="stat-item">
                    <span class="dashicons dashicons-money-alt"></span>
                    <div class="stat-number"><?php echo wc_price( $stats['total_revenue'] ); ?></div>
                    <div class="stat-label"><?php _e( 'Revenue', 'woo-offers' ); ?></div>
                </div>
            </div>
            <div class="widget-actions">
                <a href="<?php echo admin_url( 'admin.php?page=woo-offers-offers' ); ?>" class="button button-primary">
                    <?php _e( 'Manage Offers', 'woo-offers' ); ?>
                </a>
                <a href="<?php echo admin_url( 'admin.php?page=woo-offers-create' ); ?>" class="button">
                    <?php _e( 'Create New Offer', 'woo-offers' ); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Get overview statistics for dashboard
     */
    public function get_overview_stats() {
        global $wpdb;

        $offers_table = $wpdb->prefix . 'woo_offers';
        $analytics_table = $wpdb->prefix . 'woo_campaign_analytics';

        $total_offers = $wpdb->get_var( "SELECT COUNT(*) FROM {$offers_table}" );
        $active_offers = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$offers_table} WHERE status = %s", 'active' ) );

        $total_conversions = 0;
        $total_revenue = 0;

        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$analytics_table}'" ) ) {
            $total_conversions = $wpdb->get_var( "SELECT COUNT(*) FROM {$analytics_table} WHERE event_type = 'conversion'" );
            $total_revenue = $wpdb->get_var( "SELECT SUM(revenue) FROM {$analytics_table} WHERE event_type = 'conversion'" ) ?: 0;
        }

        return [
            'total_offers' => intval( $total_offers ),
            'active_offers' => intval( $active_offers ),
            'total_conversions' => intval( $total_conversions ),
            'total_revenue' => floatval( $total_revenue ),
        ];
    }

    /**
     * Get recent activity for dashboard
     */
    public function get_recent_activity( $limit = 5 ) {
        global $wpdb;
        $campaigns_table = $wpdb->prefix . 'woo_campaigns';
        $analytics_table = $wpdb->prefix . 'woo_campaign_analytics';
        $activities = [];

        // Get recent campaign creations
        $recent_campaigns = $wpdb->get_results( $wpdb->prepare( 
            "SELECT name, created_at FROM {$campaigns_table} ORDER BY created_at DESC LIMIT %d", 
            $limit 
        ) );

        foreach ( $recent_campaigns as $campaign ) {
            $activities[] = [
                'type' => 'campaign_created',
                'icon' => 'dashicons-plus-alt',
                'message' => sprintf( __( 'Campaign "%s" was created', 'woo-offers' ), '<strong>' . esc_html( $campaign->name ) . '</strong>' ),
                'time_ago' => $this->time_ago( $campaign->created_at ),
                'timestamp' => strtotime( $campaign->created_at )
            ];
        }

        // Get recent conversions
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$analytics_table}'" ) ) {
            $recent_conversions = $wpdb->get_results( $wpdb->prepare( "
                SELECT a.*, c.name as campaign_name 
                FROM {$analytics_table} a 
                LEFT JOIN {$campaigns_table} c ON a.campaign_id = c.id 
                WHERE a.event_type = 'conversion' 
                ORDER BY a.created_at DESC 
                LIMIT %d
            ", $limit ) );

            foreach ( $recent_conversions as $conversion ) {
                $activities[] = [
                    'type' => 'conversion',
                    'icon' => 'dashicons-chart-line',
                    'message' => sprintf( 
                        __( 'Conversion from campaign "%s" - %s', 'woo-offers' ), 
                        '<strong>' . esc_html( $conversion->campaign_name ) . '</strong>',
                        wc_price( $conversion->revenue )
                    ),
                    'time_ago' => $this->time_ago( $conversion->created_at ),
                    'timestamp' => strtotime( $conversion->created_at )
                ];
            }
        }
        
        // Sort by timestamp and limit
        usort( $activities, function( $a, $b ) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return array_slice( $activities, 0, $limit );
    }

    /**
     * Get time ago string for activity
     */
    private function time_ago( $datetime ) {
        return human_time_diff( strtotime( $datetime ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'woo-offers' );
    }
}