<?php

namespace WooOffers\Admin;

/**
 * Dashboard management for admin interface
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Dashboard class for managing admin dashboard widgets and overview
 */
class Dashboard {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widgets' ] );
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
     * Render the main dashboard page for the plugin.
     * Este é o método que estava faltando.
     */
    public function render_dashboard_page() {
        ?>
        <div class="woo-offers-dashboard-page">
            <div class="woo-offers-dashboard-main-content">
                <h2><?php _e('Offers Overview', 'woo-offers'); ?></h2>
                <?php $this->render_overview_widget(); ?>
                
                <h2 style="margin-top: 40px;"><?php _e('Recent Activity', 'woo-offers'); ?></h2>
                <?php $this->render_recent_activity_widget(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render overview widget for WordPress dashboard
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
     * Render recent activity widget for WordPress dashboard
     */
    public function render_recent_activity_widget() {
        $recent_activity = $this->get_recent_activity();
        ?>
        <div class="woo-offers-activity-widget">
            <?php if ( ! empty( $recent_activity ) ): ?>
                <ul class="activity-list">
                    <?php foreach ( $recent_activity as $activity ): ?>
                        <li class="activity-item">
                            <div class="activity-icon activity-<?php echo esc_attr( $activity['type'] ); ?>">
                                <span class="dashicons <?php echo esc_attr( $activity['icon'] ); ?>"></span>
                            </div>
                            <div class="activity-content">
                                <div class="activity-message"><?php echo wp_kses_post( $activity['message'] ); ?></div>
                                <div class="activity-time"><?php echo esc_html( $activity['time_ago'] ); ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="widget-actions">
                    <a href="<?php echo admin_url( 'admin.php?page=woo-offers-analytics' ); ?>">
                        <?php _e( 'View All Activity', 'woo-offers' ); ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="no-activity">
                    <p><?php _e( 'No recent activity. Create your first offer to get started!', 'woo-offers' ); ?></p>
                    <a href="<?php echo admin_url( 'admin.php?page=woo-offers-create' ); ?>" class="button button-primary">
                        <?php _e( 'Create Your First Offer', 'woo-offers' ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Get overview statistics for dashboard
     */
    public function get_overview_stats() {
        global $wpdb;

        $offers_table = $wpdb->prefix . 'woo_offers';
        $analytics_table = $wpdb->prefix . 'woo_offers_analytics';

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
        $offers_table = $wpdb->prefix . 'woo_offers';
        $activities = [];

        $recent_offers = $wpdb->get_results( $wpdb->prepare( "SELECT name, created_at FROM {$offers_table} ORDER BY created_at DESC LIMIT %d", $limit ) );

        foreach ( $recent_offers as $offer ) {
            $activities[] = [
                'type' => 'offer_created',
                'icon' => 'dashicons-plus-alt',
                'message' => sprintf( __( 'Offer "%s" was created', 'woo-offers' ), '<strong>' . esc_html( $offer->name ) . '</strong>' ),
                'time_ago' => $this->time_ago( $offer->created_at ),
                'timestamp' => strtotime( $offer->created_at )
            ];
        }
        
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