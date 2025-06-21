<?php
/**
 * Analytics Manager
 *
 * Comprehensive analytics system for tracking campaign performance
 *
 * @package WooOffers
 * @since 3.0.0
 */

namespace WooOffers\Analytics;

use WooOffers\Campaigns\CampaignManager;
use WooOffers\Core\SecurityManager;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AnalyticsManager class for campaign performance tracking
 */
class AnalyticsManager {
    
    /**
     * Singleton instance
     *
     * @var AnalyticsManager|null
     */
    private static $instance = null;
    
    /**
     * Campaign Manager instance
     *
     * @var CampaignManager
     */
    private $campaign_manager;
    
    /**
     * Security Manager instance
     *
     * @var SecurityManager
     */
    private $security_manager;
    
    /**
     * Analytics table name
     *
     * @var string
     */
    private $analytics_table;
    
    /**
     * Event types constants
     */
    const EVENT_VIEW = 'view';
    const EVENT_CLICK = 'click';
    const EVENT_CONVERSION = 'conversion';
    const EVENT_ADD_TO_CART = 'add_to_cart';
    const EVENT_PURCHASE = 'purchase';
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        global $wpdb;
        
        $this->analytics_table = $wpdb->prefix . 'woo_campaign_analytics';
        $this->campaign_manager = CampaignManager::getInstance();
        $this->security_manager = SecurityManager::getInstance();
        
        $this->init_hooks();
    }
    
    /**
     * Get singleton instance
     *
     * @return AnalyticsManager
     */
    public static function getInstance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // WordPress hooks
        add_action( 'init', [ $this, 'init' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_tracking_scripts' ] );
        
        // WooCommerce hooks for conversion tracking
        add_action( 'woocommerce_add_to_cart', [ $this, 'track_add_to_cart' ], 10, 6 );
        add_action( 'woocommerce_thankyou', [ $this, 'track_purchase' ], 10, 1 );
        add_action( 'woocommerce_order_status_completed', [ $this, 'track_completed_order' ], 10, 1 );
        
        // AJAX hooks for frontend tracking
        add_action( 'wp_ajax_woo_offers_track_event', [ $this, 'handle_track_event_ajax' ] );
        add_action( 'wp_ajax_nopriv_woo_offers_track_event', [ $this, 'handle_track_event_ajax' ] );
        
        // ✅ NEW: AJAX hook for campaign attribution
        add_action( 'wp_ajax_woo_offers_set_campaign_attribution', [ $this, 'handle_set_attribution_ajax' ] );
        add_action( 'wp_ajax_nopriv_woo_offers_set_campaign_attribution', [ $this, 'handle_set_attribution_ajax' ] );
    }
    
    /**
     * Initialize the analytics manager
     */
    public function init() {
        // Initialize any necessary components
        $this->maybe_create_session();
        
        // Schedule aggregation jobs
        $this->schedule_aggregation_jobs();
    }
    
    /**
     * Enqueue tracking scripts on frontend
     */
    public function enqueue_tracking_scripts() {
        // Only enqueue on pages where campaigns might be displayed
        if ( ! is_admin() ) {
            wp_enqueue_script(
                'woo-offers-analytics-tracker',
                WOO_OFFERS_URL . 'assets/js/analytics-tracker.js',
                [ 'jquery' ],
                WOO_OFFERS_VERSION,
                true
            );
            
            // Localize script with AJAX URL and nonce
            wp_localize_script( 'woo-offers-analytics-tracker', 'wooOffersAnalytics', [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'woo_offers_track_event' ),
                'sessionId' => $this->get_session_id(),
                'userId' => get_current_user_id(),
                'debug' => defined( 'WP_DEBUG' ) && WP_DEBUG
            ] );
        }
    }
    
    /**
     * Track a generic analytics event
     *
     * @param array $event_data Event data including type, campaign_id, etc.
     * @return bool Success status
     */
    public function track_event( $event_data ) {
        global $wpdb;
        
        // Validate required fields
        if ( ! isset( $event_data['event_type'] ) || ! isset( $event_data['campaign_id'] ) ) {
            return false;
        }
        
        // Sanitize and validate data
        $event_data = $this->sanitize_event_data( $event_data );
        
        if ( ! $this->validate_event_data( $event_data ) ) {
            return false;
        }
        
        // Prepare data for insertion
        $insert_data = [
            'campaign_id' => intval( $event_data['campaign_id'] ),
            'event_type' => sanitize_text_field( $event_data['event_type'] ),
            'session_id' => sanitize_text_field( $event_data['session_id'] ?? $this->get_session_id() ),
            'user_id' => intval( $event_data['user_id'] ?? get_current_user_id() ),
            'ip_address' => sanitize_text_field( $this->get_client_ip() ),
            'user_agent' => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
            'page_url' => esc_url_raw( $event_data['page_url'] ?? $this->get_current_url() ),
            'referrer_url' => esc_url_raw( $event_data['referrer_url'] ?? wp_get_referer() ),
            'device_type' => sanitize_text_field( $this->detect_device_type() ),
            'created_at' => current_time( 'mysql' )
        ];
        
        // Add optional fields if provided
        if ( isset( $event_data['order_id'] ) ) {
            $insert_data['order_id'] = intval( $event_data['order_id'] );
        }
        
        if ( isset( $event_data['product_id'] ) ) {
            $insert_data['product_id'] = intval( $event_data['product_id'] );
        }
        
        if ( isset( $event_data['value'] ) ) {
            $insert_data['value'] = floatval( $event_data['value'] );
        }
        
        if ( isset( $event_data['metadata'] ) ) {
            $insert_data['metadata'] = wp_json_encode( $event_data['metadata'] );
        }
        
        // Insert into database
        $result = $wpdb->insert(
            $this->analytics_table,
            $insert_data,
            [
                '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s'
            ]
        );
        
        if ( $result === false ) {
            error_log( 'WooOffers Analytics: Failed to insert event - ' . $wpdb->last_error );
            return false;
        }
        
        return true;
    }
    
    /**
     * Handle AJAX event tracking from frontend
     */
    public function handle_track_event_ajax() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'woo_offers_track_event' ) ) {
            wp_send_json_error( [
                'message' => __( 'Security check failed', 'woo-offers' )
            ] );
        }
        
        // Rate limiting check
        if ( ! $this->security_manager->check_rate_limit( 'analytics_tracking', 60, 120 ) ) {
            wp_send_json_error( [
                'message' => __( 'Too many requests', 'woo-offers' )
            ] );
        }
        
        // Get and sanitize event data
        $event_data = [
            'event_type' => sanitize_text_field( $_POST['event_type'] ?? '' ),
            'campaign_id' => intval( $_POST['campaign_id'] ?? 0 ),
            'page_url' => esc_url_raw( $_POST['page_url'] ?? '' ),
            'referrer_url' => esc_url_raw( $_POST['referrer_url'] ?? '' ),
            'session_id' => sanitize_text_field( $_POST['session_id'] ?? '' ),
            'user_id' => intval( $_POST['user_id'] ?? 0 ),
            'metadata' => json_decode( stripslashes( $_POST['metadata'] ?? '{}' ), true )
        ];
        
        // Track the event
        $success = $this->track_event( $event_data );
        
        if ( $success ) {
            wp_send_json_success( [
                'message' => __( 'Event tracked successfully', 'woo-offers' )
            ] );
        } else {
            wp_send_json_error( [
                'message' => __( 'Failed to track event', 'woo-offers' )
            ] );
        }
    }
    
    /**
     * ✅ NEW: Handle AJAX campaign attribution from frontend
     */
    public function handle_set_attribution_ajax() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'woo_offers_track_event' ) ) {
            wp_send_json_error( [
                'message' => __( 'Security check failed', 'woo-offers' )
            ] );
        }
        
        $campaign_id = intval( $_POST['campaign_id'] ?? 0 );
        
        if ( $campaign_id <= 0 ) {
            wp_send_json_error( [
                'message' => __( 'Invalid campaign ID', 'woo-offers' )
            ] );
        }
        
        // Set campaign attribution
        $this->set_campaign_attribution( $campaign_id );
        
        wp_send_json_success( [
            'message' => __( 'Campaign attribution set successfully', 'woo-offers' ),
            'campaign_id' => $campaign_id
        ] );
    }
    
    /**
     * Track add to cart event
     */
    public function track_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
        $campaign_id = $this->get_attributed_campaign_id();
        
        if ( $campaign_id ) {
            $this->track_event( [
                'event_type' => self::EVENT_ADD_TO_CART,
                'campaign_id' => $campaign_id,
                'product_id' => $product_id,
                'value' => $quantity,
                'metadata' => [
                    'quantity' => $quantity,
                    'variation_id' => $variation_id
                ]
            ] );
        }
    }
    
    /**
     * Track purchase completion
     */
    public function track_purchase( $order_id ) {
        if ( ! $order_id ) {
            return;
        }
        
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        
        $campaign_id = $this->get_attributed_campaign_id();
        
        if ( $campaign_id ) {
            // Store attribution in order meta
            update_post_meta( $order_id, '_woo_offers_campaign_id', $campaign_id );
            
            $this->track_event( [
                'event_type' => self::EVENT_PURCHASE,
                'campaign_id' => $campaign_id,
                'order_id' => $order_id,
                'value' => $order->get_total(),
                'metadata' => [
                    'order_total' => $order->get_total(),
                    'order_status' => $order->get_status()
                ]
            ] );
        }
    }
    
    /**
     * Track completed order
     */
    public function track_completed_order( $order_id ) {
        $campaign_id = get_post_meta( $order_id, '_woo_offers_campaign_id', true );
        
        if ( $campaign_id ) {
            $order = wc_get_order( $order_id );
            
            $this->track_event( [
                'event_type' => self::EVENT_CONVERSION,
                'campaign_id' => intval( $campaign_id ),
                'order_id' => $order_id,
                'value' => $order->get_total(),
                'metadata' => [
                    'completed_at' => current_time( 'mysql' )
                ]
            ] );
        }
    }
    
    /**
     * Get campaign performance metrics
     */
    public function get_campaign_metrics( $campaign_id, $start_date = null, $end_date = null ) {
        global $wpdb;
        
        if ( ! $start_date ) {
            $start_date = date( 'Y-m-d', strtotime( '-30 days' ) );
        }
        
        if ( ! $end_date ) {
            $end_date = date( 'Y-m-d' );
        }
        
        $where_clause = $wpdb->prepare(
            "WHERE campaign_id = %d AND DATE(created_at) BETWEEN %s AND %s",
            $campaign_id,
            $start_date,
            $end_date
        );
        
        $events_query = "
            SELECT event_type, COUNT(*) as count, SUM(value) as total_value
            FROM {$this->analytics_table}
            {$where_clause}
            GROUP BY event_type
        ";
        
        $events = $wpdb->get_results( $events_query, ARRAY_A );
        
        $metrics = [
            'views' => 0,
            'clicks' => 0,
            'conversions' => 0,
            'add_to_cart' => 0,
            'purchases' => 0,
            'total_revenue' => 0.00,
            'ctr' => 0.00,
            'conversion_rate' => 0.00,
            'average_order_value' => 0.00
        ];
        
        foreach ( $events as $event ) {
            switch ( $event['event_type'] ) {
                case self::EVENT_VIEW:
                    $metrics['views'] = intval( $event['count'] );
                    break;
                case self::EVENT_CLICK:
                    $metrics['clicks'] = intval( $event['count'] );
                    break;
                case self::EVENT_CONVERSION:
                    $metrics['conversions'] = intval( $event['count'] );
                    $metrics['total_revenue'] = floatval( $event['total_value'] );
                    break;
                case self::EVENT_ADD_TO_CART:
                    $metrics['add_to_cart'] = intval( $event['count'] );
                    break;
                case self::EVENT_PURCHASE:
                    $metrics['purchases'] = intval( $event['count'] );
                    break;
            }
        }
        
        // Calculate derived metrics
        if ( $metrics['views'] > 0 ) {
            $metrics['ctr'] = ( $metrics['clicks'] / $metrics['views'] ) * 100;
        }
        
        if ( $metrics['clicks'] > 0 ) {
            $metrics['conversion_rate'] = ( $metrics['conversions'] / $metrics['clicks'] ) * 100;
        }
        
        if ( $metrics['conversions'] > 0 ) {
            $metrics['average_order_value'] = $metrics['total_revenue'] / $metrics['conversions'];
        }
        
        return $metrics;
    }
    
    /**
     * Set campaign attribution
     */
    public function set_campaign_attribution( $campaign_id ) {
        if ( ! session_id() ) {
            session_start();
        }
        
        $_SESSION['woo_offers_attributed_campaign'] = intval( $campaign_id );
        
        setcookie(
            'woo_offers_campaign_attribution',
            $campaign_id,
            time() + ( 30 * DAY_IN_SECONDS ),
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
    }
    
    /**
     * Get attributed campaign ID
     */
    public function get_attributed_campaign_id() {
        if ( isset( $_SESSION['woo_offers_attributed_campaign'] ) ) {
            return intval( $_SESSION['woo_offers_attributed_campaign'] );
        }
        
        if ( isset( $_COOKIE['woo_offers_campaign_attribution'] ) ) {
            return intval( $_COOKIE['woo_offers_campaign_attribution'] );
        }
        
        return null;
    }
    
    /**
     * Sanitize event data
     */
    private function sanitize_event_data( $data ) {
        $sanitized = [];
        
        $sanitized['campaign_id'] = intval( $data['campaign_id'] ?? 0 );
        $sanitized['event_type'] = sanitize_text_field( $data['event_type'] ?? '' );
        
        if ( isset( $data['session_id'] ) ) {
            $sanitized['session_id'] = sanitize_text_field( $data['session_id'] );
        }
        
        if ( isset( $data['user_id'] ) ) {
            $sanitized['user_id'] = intval( $data['user_id'] );
        }
        
        if ( isset( $data['page_url'] ) ) {
            $sanitized['page_url'] = esc_url_raw( $data['page_url'] );
        }
        
        if ( isset( $data['referrer_url'] ) ) {
            $sanitized['referrer_url'] = esc_url_raw( $data['referrer_url'] );
        }
        
        if ( isset( $data['order_id'] ) ) {
            $sanitized['order_id'] = intval( $data['order_id'] );
        }
        
        if ( isset( $data['product_id'] ) ) {
            $sanitized['product_id'] = intval( $data['product_id'] );
        }
        
        if ( isset( $data['value'] ) ) {
            $sanitized['value'] = floatval( $data['value'] );
        }
        
        if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
            $sanitized['metadata'] = $data['metadata'];
        }
        
        return $sanitized;
    }
    
    /**
     * Validate event data
     */
    private function validate_event_data( $data ) {
        if ( empty( $data['campaign_id'] ) || empty( $data['event_type'] ) ) {
            return false;
        }
        
        $valid_types = [
            self::EVENT_VIEW,
            self::EVENT_CLICK,
            self::EVENT_CONVERSION,
            self::EVENT_ADD_TO_CART,
            self::EVENT_PURCHASE
        ];
        
        if ( ! in_array( $data['event_type'], $valid_types ) ) {
            return false;
        }
        
        $campaign = $this->campaign_manager->get_campaign( $data['campaign_id'] );
        if ( ! $campaign ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get session ID
     */
    private function get_session_id() {
        if ( ! session_id() ) {
            session_start();
        }
        
        if ( ! isset( $_SESSION['woo_offers_session_id'] ) ) {
            $_SESSION['woo_offers_session_id'] = wp_generate_uuid4();
        }
        
        return $_SESSION['woo_offers_session_id'];
    }
    
    /**
     * Maybe create session
     */
    private function maybe_create_session() {
        if ( ! session_id() && ! headers_sent() ) {
            session_start();
        }
    }
    
    /**
     * Get client IP
     */
    private function get_client_ip() {
        $ip_keys = [ 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ];
        
        foreach ( $ip_keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = trim( $_SERVER[ $key ] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                    return $ip;
                }
            }
        }
        
        return '127.0.0.1';
    }
    
    /**
     * Get current URL
     */
    private function get_current_url() {
        if ( isset( $_SERVER['REQUEST_URI'] ) ) {
            return home_url( $_SERVER['REQUEST_URI'] );
        }
        
        return home_url();
    }
    
    /**
     * Detect device type
     */
    private function detect_device_type() {
        if ( wp_is_mobile() ) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            if ( preg_match( '/tablet|ipad/i', $user_agent ) ) {
                return 'tablet';
            }
            
            return 'mobile';
        }
        
        return 'desktop';
    }
    
    // ===================================================================
    // DATA AGGREGATION AND CACHING SYSTEM
    // Performance optimization through background processing and caching
    // ===================================================================
    
    /**
     * Schedule data aggregation cron jobs
     */
    public function schedule_aggregation_jobs() {
        // Schedule daily aggregation at 2 AM
        if ( ! wp_next_scheduled( 'woo_offers_daily_aggregation' ) ) {
            wp_schedule_event( strtotime( '2:00 AM' ), 'daily', 'woo_offers_daily_aggregation' );
        }
        
        // Schedule weekly aggregation on Sundays at 3 AM
        if ( ! wp_next_scheduled( 'woo_offers_weekly_aggregation' ) ) {
            wp_schedule_event( strtotime( 'next Sunday 3:00 AM' ), 'weekly', 'woo_offers_weekly_aggregation' );
        }
        
        // Schedule monthly aggregation on 1st of month at 4 AM
        if ( ! wp_next_scheduled( 'woo_offers_monthly_aggregation' ) ) {
            $next_month = strtotime( 'first day of next month 4:00 AM' );
            wp_schedule_event( $next_month, 'monthly', 'woo_offers_monthly_aggregation' );
        }
        
        // Hook the aggregation functions
        add_action( 'woo_offers_daily_aggregation', [ $this, 'run_daily_aggregation' ] );
        add_action( 'woo_offers_weekly_aggregation', [ $this, 'run_weekly_aggregation' ] );
        add_action( 'woo_offers_monthly_aggregation', [ $this, 'run_monthly_aggregation' ] );
    }
    
    /**
     * Run daily aggregation
     */
    public function run_daily_aggregation() {
        $yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );
        $this->aggregate_data_for_period( $yesterday, $yesterday, 'daily' );
        
        // Clear related caches
        $this->clear_metrics_cache();
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'WooOffers Analytics: Daily aggregation completed for ' . $yesterday );
        }
    }
    
    /**
     * Run weekly aggregation
     */
    public function run_weekly_aggregation() {
        $last_week_start = date( 'Y-m-d', strtotime( 'last Monday' ) );
        $last_week_end = date( 'Y-m-d', strtotime( 'last Sunday' ) );
        
        $this->aggregate_data_for_period( $last_week_start, $last_week_end, 'weekly' );
        
        // Clear related caches
        $this->clear_metrics_cache();
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'WooOffers Analytics: Weekly aggregation completed for ' . $last_week_start . ' to ' . $last_week_end );
        }
    }
    
    /**
     * Run monthly aggregation
     */
    public function run_monthly_aggregation() {
        $last_month_start = date( 'Y-m-01', strtotime( 'last month' ) );
        $last_month_end = date( 'Y-m-t', strtotime( 'last month' ) );
        
        $this->aggregate_data_for_period( $last_month_start, $last_month_end, 'monthly' );
        
        // Clear related caches
        $this->clear_metrics_cache();
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'WooOffers Analytics: Monthly aggregation completed for ' . $last_month_start . ' to ' . $last_month_end );
        }
    }
    
    /**
     * Aggregate data for a specific period
     *
     * @param string $start_date Start date (Y-m-d)
     * @param string $end_date End date (Y-m-d)
     * @param string $period_type Period type (daily|weekly|monthly)
     */
    private function aggregate_data_for_period( $start_date, $end_date, $period_type ) {
        global $wpdb;
        
        // Get all campaigns that had activity in this period
        $campaigns_query = $wpdb->prepare(
            "SELECT DISTINCT campaign_id FROM {$this->analytics_table} 
             WHERE DATE(created_at) BETWEEN %s AND %s",
            $start_date,
            $end_date
        );
        
        $campaign_ids = $wpdb->get_col( $campaigns_query );
        
        if ( empty( $campaign_ids ) ) {
            return;
        }
        
        foreach ( $campaign_ids as $campaign_id ) {
            $this->aggregate_campaign_data( $campaign_id, $start_date, $end_date, $period_type );
        }
    }
    
    /**
     * Aggregate data for a specific campaign and period
     *
     * @param int $campaign_id Campaign ID
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param string $period_type Period type
     */
    private function aggregate_campaign_data( $campaign_id, $start_date, $end_date, $period_type ) {
        global $wpdb;
        
        // Create aggregated table name
        $aggregated_table = $wpdb->prefix . 'woo_campaign_analytics_aggregated';
        
        // Ensure aggregated table exists
        $this->maybe_create_aggregated_table();
        
        // Check if aggregation already exists
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$aggregated_table} 
             WHERE campaign_id = %d AND period_start = %s AND period_end = %s AND period_type = %s",
            $campaign_id,
            $start_date,
            $end_date,
            $period_type
        ) );
        
        if ( $existing ) {
            // Update existing aggregation
            $aggregated_data = $this->calculate_aggregated_metrics( $campaign_id, $start_date, $end_date );
            
            $wpdb->update(
                $aggregated_table,
                array_merge( $aggregated_data, [
                    'updated_at' => current_time( 'mysql' )
                ] ),
                [
                    'id' => $existing
                ],
                [ '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%s' ],
                [ '%d' ]
            );
        } else {
            // Create new aggregation
            $aggregated_data = $this->calculate_aggregated_metrics( $campaign_id, $start_date, $end_date );
            
            $wpdb->insert(
                $aggregated_table,
                array_merge( $aggregated_data, [
                    'campaign_id' => $campaign_id,
                    'period_start' => $start_date,
                    'period_end' => $end_date,
                    'period_type' => $period_type,
                    'created_at' => current_time( 'mysql' ),
                    'updated_at' => current_time( 'mysql' )
                ] ),
                [ '%d', '%d', '%d', '%d', '%d', '%f', '%f', '%f', '%s', '%s', '%s', '%s', '%s' ]
            );
        }
    }
    
    /**
     * Calculate aggregated metrics for a campaign and period
     *
     * @param int $campaign_id Campaign ID
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Aggregated metrics
     */
    private function calculate_aggregated_metrics( $campaign_id, $start_date, $end_date ) {
        global $wpdb;
        
        $where_clause = $wpdb->prepare(
            "WHERE campaign_id = %d AND DATE(created_at) BETWEEN %s AND %s",
            $campaign_id,
            $start_date,
            $end_date
        );
        
        $events_query = "
            SELECT event_type, COUNT(*) as count, SUM(COALESCE(value, 0)) as total_value
            FROM {$this->analytics_table}
            {$where_clause}
            GROUP BY event_type
        ";
        
        $events = $wpdb->get_results( $events_query, ARRAY_A );
        
        $metrics = [
            'views' => 0,
            'clicks' => 0,
            'conversions' => 0,
            'add_to_cart' => 0,
            'purchases' => 0,
            'total_revenue' => 0.00,
            'ctr' => 0.00,
            'conversion_rate' => 0.00
        ];
        
        foreach ( $events as $event ) {
            switch ( $event['event_type'] ) {
                case self::EVENT_VIEW:
                    $metrics['views'] = intval( $event['count'] );
                    break;
                case self::EVENT_CLICK:
                    $metrics['clicks'] = intval( $event['count'] );
                    break;
                case self::EVENT_CONVERSION:
                    $metrics['conversions'] = intval( $event['count'] );
                    $metrics['total_revenue'] = floatval( $event['total_value'] );
                    break;
                case self::EVENT_ADD_TO_CART:
                    $metrics['add_to_cart'] = intval( $event['count'] );
                    break;
                case self::EVENT_PURCHASE:
                    $metrics['purchases'] = intval( $event['count'] );
                    break;
            }
        }
        
        // Calculate derived metrics
        if ( $metrics['views'] > 0 ) {
            $metrics['ctr'] = ( $metrics['clicks'] / $metrics['views'] ) * 100;
        }
        
        if ( $metrics['clicks'] > 0 ) {
            $metrics['conversion_rate'] = ( $metrics['conversions'] / $metrics['clicks'] ) * 100;
        }
        
        return $metrics;
    }
    
    /**
     * Maybe create aggregated table
     */
    private function maybe_create_aggregated_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woo_campaign_analytics_aggregated';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            campaign_id bigint(20) unsigned NOT NULL,
            period_start date NOT NULL,
            period_end date NOT NULL,
            period_type varchar(20) NOT NULL,
            views int(11) NOT NULL DEFAULT 0,
            clicks int(11) NOT NULL DEFAULT 0,
            conversions int(11) NOT NULL DEFAULT 0,
            add_to_cart int(11) NOT NULL DEFAULT 0,
            purchases int(11) NOT NULL DEFAULT 0,
            total_revenue decimal(10,2) NOT NULL DEFAULT 0.00,
            ctr decimal(5,2) NOT NULL DEFAULT 0.00,
            conversion_rate decimal(5,2) NOT NULL DEFAULT 0.00,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_aggregation (campaign_id, period_start, period_end, period_type),
            KEY idx_campaign_period (campaign_id, period_type),
            KEY idx_period_dates (period_start, period_end)
        ) {$charset_collate};";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    
    /**
     * Get cached campaign metrics
     *
     * @param int $campaign_id Campaign ID
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array|false Cached metrics or false
     */
    public function get_cached_campaign_metrics( $campaign_id, $start_date = null, $end_date = null ) {
        if ( ! $start_date ) {
            $start_date = date( 'Y-m-d', strtotime( '-30 days' ) );
        }
        
        if ( ! $end_date ) {
            $end_date = date( 'Y-m-d' );
        }
        
        $cache_key = "woo_offers_metrics_{$campaign_id}_{$start_date}_{$end_date}";
        $cached_data = get_transient( $cache_key );
        
        if ( $cached_data !== false ) {
            return $cached_data;
        }
        
        // Try to get from aggregated data first
        $aggregated_data = $this->get_aggregated_metrics( $campaign_id, $start_date, $end_date );
        
        if ( $aggregated_data ) {
            // Cache for 1 hour
            set_transient( $cache_key, $aggregated_data, HOUR_IN_SECONDS );
            return $aggregated_data;
        }
        
        // Fallback to real-time calculation
        $real_time_data = $this->get_campaign_metrics( $campaign_id, $start_date, $end_date );
        
        // Cache for 15 minutes (shorter time for real-time data)
        set_transient( $cache_key, $real_time_data, 15 * MINUTE_IN_SECONDS );
        
        return $real_time_data;
    }
    
    /**
     * Get aggregated metrics from aggregated table
     *
     * @param int $campaign_id Campaign ID
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array|null Aggregated metrics or null
     */
    private function get_aggregated_metrics( $campaign_id, $start_date, $end_date ) {
        global $wpdb;
        
        $aggregated_table = $wpdb->prefix . 'woo_campaign_analytics_aggregated';
        
        // Check if table exists
        $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$aggregated_table}'" );
        if ( ! $table_exists ) {
            return null;
        }
        
        $query = $wpdb->prepare(
            "SELECT 
                SUM(views) as views,
                SUM(clicks) as clicks,
                SUM(conversions) as conversions,
                SUM(add_to_cart) as add_to_cart,
                SUM(purchases) as purchases,
                SUM(total_revenue) as total_revenue,
                AVG(ctr) as ctr,
                AVG(conversion_rate) as conversion_rate
            FROM {$aggregated_table}
            WHERE campaign_id = %d 
            AND period_start >= %s 
            AND period_end <= %s",
            $campaign_id,
            $start_date,
            $end_date
        );
        
        $result = $wpdb->get_row( $query, ARRAY_A );
        
        if ( ! $result || $result['views'] === null ) {
            return null;
        }
        
        // Format results
        foreach ( $result as $key => $value ) {
            if ( in_array( $key, [ 'views', 'clicks', 'conversions', 'add_to_cart', 'purchases' ] ) ) {
                $result[ $key ] = intval( $value );
            } else {
                $result[ $key ] = floatval( $value );
            }
        }
        
        // Recalculate average order value
        if ( $result['conversions'] > 0 ) {
            $result['average_order_value'] = $result['total_revenue'] / $result['conversions'];
        } else {
            $result['average_order_value'] = 0.00;
        }
        
        return $result;
    }
    
    /**
     * Clear metrics cache
     *
     * @param int $campaign_id Optional campaign ID to clear specific cache
     */
    public function clear_metrics_cache( $campaign_id = null ) {
        global $wpdb;
        
        if ( $campaign_id ) {
            // Clear cache for specific campaign
            $cache_pattern = "woo_offers_metrics_{$campaign_id}_";
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . $cache_pattern . '%'
            ) );
        } else {
            // Clear all metrics cache
            $wpdb->query(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_woo_offers_metrics_%'"
            );
        }
    }
    
    /**
     * Force refresh metrics for a campaign
     *
     * @param int $campaign_id Campaign ID
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Fresh metrics
     */
    public function refresh_campaign_metrics( $campaign_id, $start_date = null, $end_date = null ) {
        if ( ! $start_date ) {
            $start_date = date( 'Y-m-d', strtotime( '-30 days' ) );
        }
        
        if ( ! $end_date ) {
            $end_date = date( 'Y-m-d' );
        }
        
        // Clear existing cache
        $cache_key = "woo_offers_metrics_{$campaign_id}_{$start_date}_{$end_date}";
        delete_transient( $cache_key );
        
        // Get fresh data
        return $this->get_cached_campaign_metrics( $campaign_id, $start_date, $end_date );
    }
    
    /**
     * Get dashboard summary metrics (cached)
     *
     * @param string $period Period (today|week|month|quarter|year)
     * @return array Summary metrics
     */
    public function get_dashboard_summary( $period = 'month' ) {
        $cache_key = "woo_offers_dashboard_summary_{$period}";
        $cached_data = get_transient( $cache_key );
        
        if ( $cached_data !== false ) {
            return $cached_data;
        }
        
        // Calculate date range based on period
        switch ( $period ) {
            case 'today':
                $start_date = date( 'Y-m-d' );
                $end_date = date( 'Y-m-d' );
                break;
            case 'week':
                $start_date = date( 'Y-m-d', strtotime( 'monday this week' ) );
                $end_date = date( 'Y-m-d', strtotime( 'sunday this week' ) );
                break;
            case 'quarter':
                $quarter_start = date( 'Y-m-d', strtotime( 'first day of this quarter' ) );
                $quarter_end = date( 'Y-m-d', strtotime( 'last day of this quarter' ) );
                $start_date = $quarter_start;
                $end_date = $quarter_end;
                break;
            case 'year':
                $start_date = date( 'Y-01-01' );
                $end_date = date( 'Y-12-31' );
                break;
            default: // month
                $start_date = date( 'Y-m-01' );
                $end_date = date( 'Y-m-t' );
                break;
        }
        
        global $wpdb;
        
        // Get summary from aggregated table if available
        $aggregated_table = $wpdb->prefix . 'woo_campaign_analytics_aggregated';
        $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$aggregated_table}'" );
        
        if ( $table_exists ) {
            $summary_query = $wpdb->prepare(
                "SELECT 
                    SUM(views) as total_views,
                    SUM(clicks) as total_clicks,
                    SUM(conversions) as total_conversions,
                    SUM(total_revenue) as total_revenue,
                    COUNT(DISTINCT campaign_id) as active_campaigns
                FROM {$aggregated_table}
                WHERE period_start >= %s AND period_end <= %s",
                $start_date,
                $end_date
            );
        } else {
            // Fallback to raw analytics table
            $summary_query = $wpdb->prepare(
                "SELECT 
                    SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as total_views,
                    SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as total_clicks,
                    SUM(CASE WHEN event_type = 'conversion' THEN 1 ELSE 0 END) as total_conversions,
                    SUM(CASE WHEN event_type = 'conversion' THEN value ELSE 0 END) as total_revenue,
                    COUNT(DISTINCT campaign_id) as active_campaigns
                FROM {$this->analytics_table}
                WHERE DATE(created_at) BETWEEN %s AND %s",
                $start_date,
                $end_date
            );
        }
        
        $summary = $wpdb->get_row( $summary_query, ARRAY_A );
        
        if ( ! $summary ) {
            $summary = [
                'total_views' => 0,
                'total_clicks' => 0,
                'total_conversions' => 0,
                'total_revenue' => 0.00,
                'active_campaigns' => 0
            ];
        }
        
        // Format and calculate derived metrics
        $summary['total_views'] = intval( $summary['total_views'] );
        $summary['total_clicks'] = intval( $summary['total_clicks'] );
        $summary['total_conversions'] = intval( $summary['total_conversions'] );
        $summary['total_revenue'] = floatval( $summary['total_revenue'] );
        $summary['active_campaigns'] = intval( $summary['active_campaigns'] );
        
        $summary['overall_ctr'] = $summary['total_views'] > 0 ? 
            ( $summary['total_clicks'] / $summary['total_views'] ) * 100 : 0;
        
        $summary['overall_conversion_rate'] = $summary['total_clicks'] > 0 ? 
            ( $summary['total_conversions'] / $summary['total_clicks'] ) * 100 : 0;
        
        $summary['average_order_value'] = $summary['total_conversions'] > 0 ? 
            $summary['total_revenue'] / $summary['total_conversions'] : 0;
        
        // Cache for different periods
        $cache_time = $period === 'today' ? 15 * MINUTE_IN_SECONDS : HOUR_IN_SECONDS;
        set_transient( $cache_key, $summary, $cache_time );
        
        return $summary;
    }
} 