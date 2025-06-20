<?php

namespace WooOffers\Admin;

/**
 * Analytics and reporting functionality
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Analytics class for managing reporting and data analysis
 */
class Analytics {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'init', [ $this, 'init' ] );
        add_action( 'wp_ajax_woo_offers_get_analytics_data', [ $this, 'get_analytics_data_ajax' ] );
        add_action( 'wp_ajax_woo_offers_export_analytics', [ $this, 'export_analytics_ajax' ] );
        
        // Frontend event tracking AJAX handlers
        add_action( 'wp_ajax_woo_offers_track_event', [ $this, 'track_event_ajax' ] );
        add_action( 'wp_ajax_nopriv_woo_offers_track_event', [ $this, 'track_event_ajax' ] );
        add_action( 'wp_ajax_woo_offers_track_events_batch', [ $this, 'track_events_batch_ajax' ] );
        add_action( 'wp_ajax_nopriv_woo_offers_track_events_batch', [ $this, 'track_events_batch_ajax' ] );
        add_action( 'wp_ajax_woo_offers_get_applied_offers', [ $this, 'get_applied_offers_ajax' ] );
        add_action( 'wp_ajax_nopriv_woo_offers_get_applied_offers', [ $this, 'get_applied_offers_ajax' ] );
        
        // Database setup
        register_activation_hook( WOO_OFFERS_PLUGIN_FILE, [ $this, 'create_analytics_table' ] );
    }

    /**
     * Initialize analytics functionality
     */
    public function init() {
        // Ensure analytics table exists
        $this->create_analytics_table();
        
        // Clean up old analytics data (older than 1 year)
        add_action( 'woo_offers_daily_cleanup', [ $this, 'cleanup_old_analytics_data' ] );
        
        // Schedule cleanup if not already scheduled
        if ( ! wp_next_scheduled( 'woo_offers_daily_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'woo_offers_daily_cleanup' );
        }
    }

    /**
     * Render analytics page
     */
    public function render_analytics_page() {
        $date_range = $this->get_current_date_range();
        $overview_stats = $this->get_overview_stats( $date_range );
        ?>
        <div class="wrap woo-offers-analytics">
            <h1><?php _e( 'Offer Analytics', 'woo-offers' ); ?></h1>
            
            <!-- Date Range Filter -->
            <div class="analytics-filters">
                <div class="date-range-filter">
                    <label for="date-range"><?php _e( 'Date Range:', 'woo-offers' ); ?></label>
                    <select id="date-range" name="date_range">
                        <option value="7"><?php _e( 'Last 7 days', 'woo-offers' ); ?></option>
                        <option value="30" selected><?php _e( 'Last 30 days', 'woo-offers' ); ?></option>
                        <option value="90"><?php _e( 'Last 90 days', 'woo-offers' ); ?></option>
                        <option value="365"><?php _e( 'Last year', 'woo-offers' ); ?></option>
                        <option value="custom"><?php _e( 'Custom range', 'woo-offers' ); ?></option>
                    </select>
                    <div id="custom-date-range" style="display: none;">
                        <input type="date" id="start-date" name="start_date" />
                        <input type="date" id="end-date" name="end_date" />
                    </div>
                    <button type="button" class="button" id="apply-filter">
                        <?php _e( 'Apply', 'woo-offers' ); ?>
                    </button>
                    <button type="button" class="button" id="export-data">
                        <?php _e( 'Export Data', 'woo-offers' ); ?>
                    </button>
                </div>
            </div>

            <!-- Overview Stats -->
            <div class="analytics-overview">
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <span class="dashicons dashicons-visibility"></span>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo number_format( $overview_stats['total_views'] ); ?></div>
                            <div class="stat-label"><?php _e( 'Total Views', 'woo-offers' ); ?></div>
                            <div class="stat-change positive">
                                +<?php echo number_format( $overview_stats['views_change'], 1 ); ?>%
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo number_format( $overview_stats['total_conversions'] ); ?></div>
                            <div class="stat-label"><?php _e( 'Conversions', 'woo-offers' ); ?></div>
                            <div class="stat-change positive">
                                +<?php echo number_format( $overview_stats['conversions_change'], 1 ); ?>%
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <span class="dashicons dashicons-chart-line"></span>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo number_format( $overview_stats['conversion_rate'], 2 ); ?>%</div>
                            <div class="stat-label"><?php _e( 'Conversion Rate', 'woo-offers' ); ?></div>
                            <div class="stat-change <?php echo $overview_stats['rate_change'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo ( $overview_stats['rate_change'] >= 0 ? '+' : '' ) . number_format( $overview_stats['rate_change'], 1 ); ?>%
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <span class="dashicons dashicons-money-alt"></span>
                        </div>
                        <div class="stat-info">
                            <div class="stat-number"><?php echo wc_price( $overview_stats['total_revenue'] ); ?></div>
                            <div class="stat-label"><?php _e( 'Revenue', 'woo-offers' ); ?></div>
                            <div class="stat-change positive">
                                +<?php echo number_format( $overview_stats['revenue_change'], 1 ); ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="analytics-charts">
                <div class="chart-container">
                    <h3><?php _e( 'Performance Over Time', 'woo-offers' ); ?></h3>
                    <canvas id="performance-chart"></canvas>
                </div>
            </div>

            <!-- Top Performing Offers -->
            <div class="analytics-tables">
                <div class="top-offers">
                    <h3><?php _e( 'Top Performing Offers', 'woo-offers' ); ?></h3>
                    <?php $this->render_top_offers_table(); ?>
                </div>
                
                <div class="recent-activity">
                    <h3><?php _e( 'Recent Activity', 'woo-offers' ); ?></h3>
                    <?php $this->render_recent_activity_table(); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get overview statistics for analytics
     * 
     * @param array $date_range Date range parameters
     * @return array Overview statistics
     */
    public function get_overview_stats( $date_range = null ) {
        global $wpdb;

        if ( ! $date_range ) {
            $date_range = $this->get_current_date_range();
        }

        $analytics_table = $wpdb->prefix . 'woo_offers_analytics';
        $offers_table = $wpdb->prefix . 'woo_offers';

        // Current period stats
        $current_stats = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                COUNT(*) as total_views,
                SUM(CASE WHEN conversion = 1 THEN 1 ELSE 0 END) as total_conversions,
                SUM(CASE WHEN conversion = 1 THEN revenue ELSE 0 END) as total_revenue
             FROM {$analytics_table} 
             WHERE created_at >= %s AND created_at <= %s",
            $date_range['start'],
            $date_range['end']
        ) );

        // Previous period stats for comparison
        $period_length = strtotime( $date_range['end'] ) - strtotime( $date_range['start'] );
        $previous_start = date( 'Y-m-d', strtotime( $date_range['start'] ) - $period_length );
        $previous_end = date( 'Y-m-d', strtotime( $date_range['start'] ) - 1 );

        $previous_stats = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                COUNT(*) as total_views,
                SUM(CASE WHEN conversion = 1 THEN 1 ELSE 0 END) as total_conversions,
                SUM(CASE WHEN conversion = 1 THEN revenue ELSE 0 END) as total_revenue
             FROM {$analytics_table} 
             WHERE created_at >= %s AND created_at <= %s",
            $previous_start,
            $previous_end
        ) );

        // Calculate conversion rate and changes
        $conversion_rate = $current_stats->total_views > 0 
            ? ( $current_stats->total_conversions / $current_stats->total_views ) * 100 
            : 0;

        $previous_conversion_rate = $previous_stats->total_views > 0 
            ? ( $previous_stats->total_conversions / $previous_stats->total_views ) * 100 
            : 0;

        return [
            'total_views' => intval( $current_stats->total_views ),
            'total_conversions' => intval( $current_stats->total_conversions ),
            'total_revenue' => floatval( $current_stats->total_revenue ),
            'conversion_rate' => $conversion_rate,
            'views_change' => $this->calculate_percentage_change( $current_stats->total_views, $previous_stats->total_views ),
            'conversions_change' => $this->calculate_percentage_change( $current_stats->total_conversions, $previous_stats->total_conversions ),
            'revenue_change' => $this->calculate_percentage_change( $current_stats->total_revenue, $previous_stats->total_revenue ),
            'rate_change' => $conversion_rate - $previous_conversion_rate
        ];
    }

    /**
     * Get performance data for charts
     * 
     * @param array $date_range Date range parameters
     * @return array Chart data
     */
    public function get_chart_data( $date_range = null ) {
        global $wpdb;

        if ( ! $date_range ) {
            $date_range = $this->get_current_date_range();
        }

        $analytics_table = $wpdb->prefix . 'woo_offers_analytics';

        $chart_data = $wpdb->get_results( $wpdb->prepare(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as views,
                SUM(CASE WHEN conversion = 1 THEN 1 ELSE 0 END) as conversions,
                SUM(CASE WHEN conversion = 1 THEN revenue ELSE 0 END) as revenue
             FROM {$analytics_table} 
             WHERE created_at >= %s AND created_at <= %s
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            $date_range['start'],
            $date_range['end']
        ) );

        return $chart_data;
    }

    /**
     * Get top performing offers
     * 
     * @param int $limit Number of offers to return
     * @param array $date_range Date range parameters
     * @return array Top offers
     */
    public function get_top_offers( $limit = 10, $date_range = null ) {
        global $wpdb;

        if ( ! $date_range ) {
            $date_range = $this->get_current_date_range();
        }

        $analytics_table = $wpdb->prefix . 'woo_offers_analytics';
        $offers_table = $wpdb->prefix . 'woo_offers';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT 
                o.id,
                o.name,
                o.offer_type,
                COUNT(a.id) as total_views,
                SUM(CASE WHEN a.conversion = 1 THEN 1 ELSE 0 END) as conversions,
                SUM(CASE WHEN a.conversion = 1 THEN a.revenue ELSE 0 END) as revenue,
                CASE 
                    WHEN COUNT(a.id) > 0 THEN (SUM(CASE WHEN a.conversion = 1 THEN 1 ELSE 0 END) / COUNT(a.id)) * 100 
                    ELSE 0 
                END as conversion_rate
             FROM {$offers_table} o
             LEFT JOIN {$analytics_table} a ON o.id = a.offer_id 
                AND a.created_at >= %s AND a.created_at <= %s
             GROUP BY o.id
             HAVING total_views > 0
             ORDER BY conversions DESC, conversion_rate DESC
             LIMIT %d",
            $date_range['start'],
            $date_range['end'],
            $limit
        ) );
    }

    /**
     * Render top offers table
     */
    private function render_top_offers_table() {
        $top_offers = $this->get_top_offers( 10 );
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e( 'Offer Name', 'woo-offers' ); ?></th>
                    <th><?php _e( 'Type', 'woo-offers' ); ?></th>
                    <th><?php _e( 'Views', 'woo-offers' ); ?></th>
                    <th><?php _e( 'Conversions', 'woo-offers' ); ?></th>
                    <th><?php _e( 'Rate', 'woo-offers' ); ?></th>
                    <th><?php _e( 'Revenue', 'woo-offers' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $top_offers ) ): ?>
                    <?php foreach ( $top_offers as $offer ): ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo admin_url( 'admin.php?page=woo-offers-create&action=edit&id=' . $offer->id ); ?>">
                                        <?php echo esc_html( $offer->name ); ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $offer->offer_type ) ) ); ?></td>
                            <td><?php echo number_format( $offer->total_views ); ?></td>
                            <td><?php echo number_format( $offer->conversions ); ?></td>
                            <td><?php echo number_format( $offer->conversion_rate, 2 ); ?>%</td>
                            <td><?php echo wc_price( $offer->revenue ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">
                            <?php _e( 'No offer data available for the selected period.', 'woo-offers' ); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render recent activity table
     */
    private function render_recent_activity_table() {
        $recent_activity = $this->get_recent_activity( 10 );
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e( 'Time', 'woo-offers' ); ?></th>
                    <th><?php _e( 'Event', 'woo-offers' ); ?></th>
                    <th><?php _e( 'Offer', 'woo-offers' ); ?></th>
                    <th><?php _e( 'Revenue', 'woo-offers' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $recent_activity ) ): ?>
                    <?php foreach ( $recent_activity as $activity ): ?>
                        <tr>
                            <td><?php echo esc_html( $activity->time_ago ); ?></td>
                            <td>
                                <span class="activity-badge activity-<?php echo esc_attr( $activity->event_type ); ?>">
                                    <?php echo esc_html( $activity->event_label ); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html( $activity->offer_name ); ?></td>
                            <td><?php echo $activity->revenue > 0 ? wc_price( $activity->revenue ) : '—'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="no-data">
                            <?php _e( 'No recent activity to display.', 'woo-offers' ); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Get recent activity
     * 
     * @param int $limit Number of activities to return
     * @return array Recent activities
     */
    public function get_recent_activity( $limit = 10 ) {
        global $wpdb;

        $analytics_table = $wpdb->prefix . 'woo_offers_analytics';
        $offers_table = $wpdb->prefix . 'woo_offers';

        $activities = $wpdb->get_results( $wpdb->prepare(
            "SELECT 
                a.*,
                o.name as offer_name,
                CASE 
                    WHEN a.conversion = 1 THEN 'conversion'
                    ELSE 'view'
                END as event_type,
                CASE 
                    WHEN a.conversion = 1 THEN 'Conversion'
                    ELSE 'View'
                END as event_label
             FROM {$analytics_table} a
             LEFT JOIN {$offers_table} o ON a.offer_id = o.id
             ORDER BY a.created_at DESC
             LIMIT %d",
            $limit
        ) );

        // Add time ago to each activity
        foreach ( $activities as &$activity ) {
            $activity->time_ago = $this->time_ago( $activity->created_at );
        }

        return $activities;
    }

    /**
     * Get current date range for analytics
     * 
     * @return array Date range with start and end dates
     */
    private function get_current_date_range() {
        $days = intval( $_GET['days'] ?? 30 );
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';

        if ( $start_date && $end_date ) {
            return [
                'start' => sanitize_text_field( $start_date ),
                'end' => sanitize_text_field( $end_date )
            ];
        }

        return [
            'start' => date( 'Y-m-d', strtotime( "-{$days} days" ) ),
            'end' => date( 'Y-m-d' )
        ];
    }

    /**
     * Calculate percentage change between two values
     * 
     * @param float $current Current value
     * @param float $previous Previous value
     * @return float Percentage change
     */
    private function calculate_percentage_change( $current, $previous ) {
        if ( $previous == 0 ) {
            return $current > 0 ? 100 : 0;
        }
        
        return ( ( $current - $previous ) / $previous ) * 100;
    }

    /**
     * Get time ago string for activity
     * 
     * @param string $datetime MySQL datetime string
     * @return string Human readable time ago
     */
    private function time_ago( $datetime ) {
        $time = time() - strtotime( $datetime );

        if ( $time < 60 ) {
            return __( 'Just now', 'woo-offers' );
        } elseif ( $time < 3600 ) {
            $minutes = floor( $time / 60 );
            return sprintf( 
                _n( '%d minute ago', '%d minutes ago', $minutes, 'woo-offers' ), 
                $minutes 
            );
        } elseif ( $time < 86400 ) {
            $hours = floor( $time / 3600 );
            return sprintf( 
                _n( '%d hour ago', '%d hours ago', $hours, 'woo-offers' ), 
                $hours 
            );
        } else {
            $days = floor( $time / 86400 );
            return sprintf( 
                _n( '%d day ago', '%d days ago', $days, 'woo-offers' ), 
                $days 
            );
        }
    }

    /**
     * AJAX handler for getting analytics data
     */
    public function get_analytics_data_ajax() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'woo_offers_nonce' ) ) {
            wp_die( __( 'Security check failed', 'woo-offers' ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have permission to perform this action', 'woo-offers' ) );
        }

        $date_range = [
            'start' => sanitize_text_field( $_POST['start_date'] ?? '' ),
            'end' => sanitize_text_field( $_POST['end_date'] ?? '' )
        ];

        $data = [
            'overview' => $this->get_overview_stats( $date_range ),
            'chart_data' => $this->get_chart_data( $date_range ),
            'top_offers' => $this->get_top_offers( 10, $date_range )
        ];

        wp_send_json_success( $data );
    }

    /**
     * AJAX handler for exporting analytics data
     */
    public function export_analytics_ajax() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'woo_offers_nonce' ) ) {
            wp_die( __( 'Security check failed', 'woo-offers' ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have permission to perform this action', 'woo-offers' ) );
        }

        $date_range = [
            'start' => sanitize_text_field( $_POST['start_date'] ?? '' ),
            'end' => sanitize_text_field( $_POST['end_date'] ?? '' )
        ];

        $export_data = $this->prepare_export_data( $date_range );

        // Set headers for CSV download
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename="woo-offers-analytics-' . date( 'Y-m-d' ) . '.csv"' );

        // Output CSV
        $output = fopen( 'php://output', 'w' );
        
        // Add CSV headers
        fputcsv( $output, [ 'Date', 'Offer Name', 'Type', 'Event', 'Revenue' ] );
        
        // Add data rows
        foreach ( $export_data as $row ) {
            fputcsv( $output, $row );
        }
        
        fclose( $output );
        exit;
    }

    /**
     * Prepare data for export
     * 
     * @param array $date_range Date range parameters
     * @return array Export data
     */
    private function prepare_export_data( $date_range ) {
        global $wpdb;

        $analytics_table = $wpdb->prefix . 'woo_offers_analytics';
        $offers_table = $wpdb->prefix . 'woo_offers';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT 
                DATE(a.created_at) as date,
                o.name as offer_name,
                o.offer_type as type,
                CASE WHEN a.conversion = 1 THEN 'Conversion' ELSE 'View' END as event,
                a.revenue
             FROM {$analytics_table} a
             LEFT JOIN {$offers_table} o ON a.offer_id = o.id
             WHERE a.created_at >= %s AND a.created_at <= %s
             ORDER BY a.created_at DESC",
            $date_range['start'],
            $date_range['end']
        ), ARRAY_A );
    }

    /**
     * Create analytics table
     */
    public function create_analytics_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'woo_offers_analytics';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            offer_id bigint(20) NOT NULL,
            product_id bigint(20) DEFAULT NULL,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(100) NOT NULL,
            url varchar(255) DEFAULT NULL,
            referrer varchar(255) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            screen_resolution varchar(20) DEFAULT NULL,
            viewport_size varchar(20) DEFAULT NULL,
            discount_amount decimal(10,2) DEFAULT 0.00,
            conversion tinyint(1) DEFAULT 0,
            revenue decimal(10,2) DEFAULT 0.00,
            event_data longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY offer_id (offer_id),
            KEY product_id (product_id),
            KEY user_id (user_id),
            KEY session_id (session_id),
            KEY event_type (event_type),
            KEY created_at (created_at),
            KEY conversion (conversion)
        ) {$charset_collate};";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Track a single event
     * 
     * @param array $event_data Event data
     * @return bool Success status
     */
    public function track_event( $event_data ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'woo_offers_analytics';

        // Determine if this is a conversion event
        $conversion_events = [ 'offer_conversion', 'offer_apply_success', 'purchase' ];
        $is_conversion = in_array( $event_data['event_type'] ?? '', $conversion_events );

        $data = [
            'event_type' => sanitize_text_field( $event_data['event_type'] ?? '' ),
            'offer_id' => intval( $event_data['offer_id'] ?? 0 ),
            'product_id' => intval( $event_data['product_id'] ?? 0 ),
            'user_id' => intval( $event_data['user_id'] ?? get_current_user_id() ),
            'session_id' => sanitize_text_field( $event_data['session_id'] ?? '' ),
            'url' => esc_url_raw( $event_data['url'] ?? '' ),
            'referrer' => esc_url_raw( $event_data['referrer'] ?? '' ),
            'user_agent' => sanitize_text_field( $event_data['user_agent'] ?? '' ),
            'screen_resolution' => sanitize_text_field( $event_data['screen_resolution'] ?? '' ),
            'viewport_size' => sanitize_text_field( $event_data['viewport_size'] ?? '' ),
            'discount_amount' => floatval( $event_data['discount_amount'] ?? 0 ),
            'conversion' => $is_conversion ? 1 : 0,
            'revenue' => floatval( $event_data['conversion_value'] ?? $event_data['revenue'] ?? 0 ),
            'event_data' => wp_json_encode( $event_data ),
            'created_at' => current_time( 'mysql' )
        ];

        return $wpdb->insert( $table_name, $data ) !== false;
    }

    /**
     * AJAX handler for tracking single event
     */
    public function track_event_ajax() {
        // Basic security check
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'woo_offers_nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Security check failed', 'woo-offers' ) ] );
        }

        $event_data_json = stripslashes( $_POST['event_data'] ?? '' );
        $event_data = json_decode( $event_data_json, true );

        if ( ! $event_data || ! isset( $event_data['event_type'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid event data', 'woo-offers' ) ] );
        }

        $success = $this->track_event( $event_data );

        if ( $success ) {
            wp_send_json_success( [ 'message' => __( 'Event tracked successfully', 'woo-offers' ) ] );
        } else {
            wp_send_json_error( [ 'message' => __( 'Failed to track event', 'woo-offers' ) ] );
        }
    }

    /**
     * AJAX handler for tracking batch events
     */
    public function track_events_batch_ajax() {
        // Basic security check
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'woo_offers_nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Security check failed', 'woo-offers' ) ] );
        }

        $events_data_json = stripslashes( $_POST['events_data'] ?? '' );
        $events_data = json_decode( $events_data_json, true );

        if ( ! $events_data || ! is_array( $events_data ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid events data', 'woo-offers' ) ] );
        }

        $tracked_count = 0;
        foreach ( $events_data as $event_data ) {
            if ( isset( $event_data['event_type'] ) && $this->track_event( $event_data ) ) {
                $tracked_count++;
            }
        }

        wp_send_json_success( [ 
            'message' => sprintf( __( '%d events tracked successfully', 'woo-offers' ), $tracked_count ),
            'tracked_count' => $tracked_count,
            'total_events' => count( $events_data )
        ] );
    }

    /**
     * AJAX handler for getting applied offers
     */
    public function get_applied_offers_ajax() {
        // Basic security check
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'woo_offers_nonce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Security check failed', 'woo-offers' ) ] );
        }

        $product_id = intval( $_POST['product_id'] ?? 0 );
        
        // Get current cart
        if ( ! WC()->cart ) {
            wp_send_json_success( [ 'applied_offers' => [], 'cart_total' => 0 ] );
            return;
        }

        $applied_offers = [];
        $cart_coupons = WC()->cart->get_applied_coupons();
        
        foreach ( $cart_coupons as $coupon_code ) {
            // Check if this is a WooOffers generated coupon
            if ( strpos( $coupon_code, 'woo_offers_' ) === 0 ) {
                // Extract offer ID from coupon code
                preg_match( '/woo_offers_(\d+)_/', $coupon_code, $matches );
                if ( isset( $matches[1] ) ) {
                    $offer_id = intval( $matches[1] );
                    $coupon = new WC_Coupon( $coupon_code );
                    
                    $applied_offers[] = [
                        'offer_id' => $offer_id,
                        'coupon_code' => $coupon_code,
                        'discount_amount' => $coupon->get_amount(),
                        'offer_type' => $this->get_offer_type_by_id( $offer_id )
                    ];
                }
            }
        }

        wp_send_json_success( [ 
            'applied_offers' => $applied_offers,
            'cart_total' => WC()->cart->get_cart_contents_total()
        ] );
    }

    /**
     * Get offer type by offer ID
     * 
     * @param int $offer_id Offer ID
     * @return string Offer type
     */
    private function get_offer_type_by_id( $offer_id ) {
        global $wpdb;
        
        $offers_table = $wpdb->prefix . 'woo_offers';
        $offer_type = $wpdb->get_var( $wpdb->prepare(
            "SELECT offer_type FROM {$offers_table} WHERE id = %d",
            $offer_id
        ) );
        
        return $offer_type ?: 'unknown';
    }

    /**
     * Clean up old analytics data (older than 1 year)
     */
    public function cleanup_old_analytics_data() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'woo_offers_analytics';
        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( '-1 year' ) );

        $deleted = $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$table_name} WHERE created_at < %s",
            $cutoff_date
        ) );

        if ( $deleted !== false ) {
            error_log( sprintf( 'WooOffers: Cleaned up %d old analytics records', $deleted ) );
        }
    }

    /**
     * Get analytics insights for dashboard
     * 
     * @return array Analytics insights
     */
    public function get_analytics_insights() {
        $date_range = $this->get_current_date_range();
        $overview_stats = $this->get_overview_stats( $date_range );
        $top_offers = $this->get_top_offers( 5, $date_range );

        return [
            'total_views' => $overview_stats['total_views'],
            'total_conversions' => $overview_stats['total_conversions'],
            'conversion_rate' => $overview_stats['conversion_rate'],
            'total_revenue' => $overview_stats['total_revenue'],
            'top_performing_offer' => ! empty( $top_offers ) ? $top_offers[0] : null,
            'performance_trend' => $overview_stats['conversions_change'] >= 0 ? 'up' : 'down'
        ];
    }
}
