<?php
/**
 * Campaign Manager - Core Campaign System Engine
 */

namespace WooOffers\Campaigns;

use WooOffers\Core\SecurityManager;
use WooOffers\Core\DatabaseSchema;

defined( 'ABSPATH' ) || exit;

class CampaignManager {
    
    /**
     * Singleton instance
     *
     * @var CampaignManager|null
     */
    private static $instance = null;
    
    /**
     * Campaign cache for performance optimization
     *
     * @var array
     */
    private static $campaign_cache = [];
    
    /**
     * Query cache TTL in seconds
     *
     * @var int
     */
    const CACHE_TTL = 300; // 5 minutes
    
    const CAMPAIGN_TYPES = [
        'product_upsell'    => 'Product Upsell',
        'cart_upsell'       => 'Cart Upsell', 
        'checkout_upsell'   => 'Checkout Upsell',
        'cross_sell'        => 'Cross-sell',
        'exit_intent'       => 'Exit Intent',
        'post_purchase'     => 'Post Purchase'
    ];
    
    const CAMPAIGN_STATUSES = [
        'draft'     => 'Draft',
        'active'    => 'Active',
        'paused'    => 'Paused',
        'scheduled' => 'Scheduled',
        'completed' => 'Completed',
        'expired'   => 'Expired'
    ];
    
    private static $tables = null;
    
    /**
     * ✅ ARCHITECTURE: Get singleton instance for better resource management
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * ✅ PERFORMANCE: Clear campaign cache
     */
    public static function clear_cache($campaign_id = null) {
        if ($campaign_id) {
            unset(self::$campaign_cache[$campaign_id]);
            unset(self::$campaign_cache['stats_' . $campaign_id]);
            wp_cache_delete("woo_campaign_{$campaign_id}", 'woo_offers');
        } else {
            self::$campaign_cache = [];
            wp_cache_flush_group('woo_offers');
        }
    }
    
    /**
     * ✅ PERFORMANCE: Get cached campaign data
     */
    private static function get_cached_campaign($campaign_id) {
        // Check memory cache first
        if (isset(self::$campaign_cache[$campaign_id])) {
            return self::$campaign_cache[$campaign_id];
        }
        
        // Check WordPress object cache
        $cached = wp_cache_get("woo_campaign_{$campaign_id}", 'woo_offers');
        if ($cached !== false) {
            self::$campaign_cache[$campaign_id] = $cached;
            return $cached;
        }
        
        return false;
    }
    
    /**
     * ✅ PERFORMANCE: Set cached campaign data
     */
    private static function set_cached_campaign($campaign_id, $campaign_data) {
        self::$campaign_cache[$campaign_id] = $campaign_data;
        wp_cache_set("woo_campaign_{$campaign_id}", $campaign_data, 'woo_offers', self::CACHE_TTL);
    }
    
    public static function init() {
        add_action( 'init', [ __CLASS__, 'setup_hooks' ] );
        add_action( 'wp_ajax_woo_offers_save_campaign', [ __CLASS__, 'ajax_save_campaign' ] );
        add_action( 'wp_ajax_woo_offers_delete_campaign', [ __CLASS__, 'ajax_delete_campaign' ] );
        
        // Campaign Builder AJAX endpoints
        add_action( 'wp_ajax_woo_offers_save_builder_data', [ __CLASS__, 'ajax_save_builder_data' ] );
        add_action( 'wp_ajax_woo_offers_load_builder_data', [ __CLASS__, 'ajax_load_builder_data' ] );
        add_action( 'wp_ajax_woo_offers_create_campaign_from_builder', [ __CLASS__, 'ajax_create_campaign_from_builder' ] );
    }
    
    public static function setup_hooks() {
        add_action( 'woo_offers_campaign_activated', [ __CLASS__, 'on_campaign_activated' ] );
        add_action( 'woo_offers_campaign_paused', [ __CLASS__, 'on_campaign_paused' ] );
        
        if ( ! wp_next_scheduled( 'woo_offers_check_campaign_schedules' ) ) {
            wp_schedule_event( time(), 'hourly', 'woo_offers_check_campaign_schedules' );
        }
    }
    
    private static function get_tables() {
        if ( null === self::$tables ) {
            self::$tables = DatabaseSchema::get_table_names();
        }
        return self::$tables;
    }
    
    public static function create_campaign( $data ) {
        global $wpdb;
        
        try {
            $validation = self::validate_campaign_data( $data );
            if ( is_wp_error( $validation ) ) {
                return $validation;
            }
            
            $campaign_data = self::prepare_campaign_data( $data );
            $campaign_data['created_by'] = get_current_user_id();
            $campaign_data['created_at'] = current_time( 'mysql' );
            $campaign_data['updated_at'] = current_time( 'mysql' );
            
            $tables = self::get_tables();
            
            $result = $wpdb->insert( $tables['campaigns'], $campaign_data );
            
            if ( false === $result ) {
                error_log( 'WooOffers: Failed to create campaign - DB Error: ' . $wpdb->last_error );
                return new \WP_Error( 'db_error', __( 'Failed to create campaign.', 'woo-offers' ) );
            }
            
            $campaign_id = $wpdb->insert_id;
            
            self::log_campaign_event( $campaign_id, 'campaign_created', [
                'user_id' => get_current_user_id(),
                'campaign_data' => $campaign_data
            ] );
            
            // ✅ PERFORMANCE: Clear list caches after creation
            wp_cache_delete('woo_campaigns_list', 'woo_offers');
            
            do_action( 'woo_offers_campaign_created', $campaign_id, $campaign_data );
            
            return $campaign_id;
            
        } catch ( \Exception $e ) {
            error_log( 'WooOffers: Campaign creation failed - ' . $e->getMessage() );
            return new \WP_Error( 'creation_failed', __( 'Campaign creation failed.', 'woo-offers' ) );
        }
    }
    
    public static function get_campaign( $campaign_id ) {
        // ✅ PERFORMANCE: Check cache first
        $cached_campaign = self::get_cached_campaign($campaign_id);
        if ($cached_campaign !== false) {
            return $cached_campaign;
        }
        
        global $wpdb;
        $tables = self::get_tables();
        
        $campaign = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$tables['campaigns']} WHERE id = %d",
            $campaign_id
        ) );
        
        if ( $campaign ) {
            $campaign = self::decode_campaign_json_fields( $campaign );
            
            // ✅ PERFORMANCE: Cache the result
            self::set_cached_campaign($campaign_id, $campaign);
        }
        
        return $campaign;
    }
    
    public static function get_campaigns( $args = [] ) {
        global $wpdb;
        
        $defaults = [
            'status'      => '',
            'type'        => '',
            'search'      => '',
            'orderby'     => 'created_at',
            'order'       => 'DESC',
            'per_page'    => 20,
            'page'        => 1
        ];
        
        $args = wp_parse_args( $args, $defaults );
        $tables = self::get_tables();
        
        $where_clauses = [ '1=1' ];
        $prepare_values = [];
        
        if ( ! empty( $args['status'] ) ) {
            $where_clauses[] = 'status = %s';
            $prepare_values[] = $args['status'];
        }
        
        if ( ! empty( $args['type'] ) ) {
            $where_clauses[] = 'type = %s';
            $prepare_values[] = $args['type'];
        }
        
        if ( ! empty( $args['search'] ) ) {
            $where_clauses[] = '(name LIKE %s OR description LIKE %s)';
            $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $prepare_values[] = $search_term;
            $prepare_values[] = $search_term;
        }
        
        $where_sql = implode( ' AND ', $where_clauses );
        
        $allowed_orderby = [ 'id', 'name', 'type', 'status', 'created_at', 'updated_at', 'priority' ];
        $orderby = in_array( $args['orderby'], $allowed_orderby ) ? $args['orderby'] : 'created_at';
        $order = in_array( strtoupper( $args['order'] ), [ 'ASC', 'DESC' ] ) ? strtoupper( $args['order'] ) : 'DESC';
        
        $offset = ( $args['page'] - 1 ) * $args['per_page'];
        $limit_sql = $wpdb->prepare( 'LIMIT %d OFFSET %d', $args['per_page'], $offset );
        
        $count_query = "SELECT COUNT(*) FROM {$tables['campaigns']} WHERE $where_sql";
        $total_items = 0;
        
        if ( ! empty( $prepare_values ) ) {
            $total_items = $wpdb->get_var( $wpdb->prepare( $count_query, $prepare_values ) );
        } else {
            $total_items = $wpdb->get_var( $count_query );
        }
        
        $query = "SELECT * FROM {$tables['campaigns']} WHERE $where_sql ORDER BY $orderby $order $limit_sql";
        $campaigns = [];
        
        if ( ! empty( $prepare_values ) ) {
            $results = $wpdb->get_results( $wpdb->prepare( $query, $prepare_values ) );
        } else {
            $results = $wpdb->get_results( $query );
        }
        
        foreach ( $results as $campaign ) {
            $campaigns[] = self::decode_campaign_json_fields( $campaign );
        }
        
        return [
            'campaigns'   => $campaigns,
            'total_items' => (int) $total_items,
            'total_pages' => ceil( $total_items / $args['per_page'] ),
            'current_page' => $args['page'],
            'per_page'    => $args['per_page']
        ];
    }
    
    private static function validate_campaign_data( $data, $campaign_id = null ) {
        $errors = [];
        
        if ( empty( $data['name'] ) ) {
            $errors[] = __( 'Campaign name is required.', 'woo-offers' );
        }
        
        if ( ! empty( $data['type'] ) && ! array_key_exists( $data['type'], self::CAMPAIGN_TYPES ) ) {
            $errors[] = __( 'Invalid campaign type.', 'woo-offers' );
        }
        
        if ( ! empty( $data['status'] ) && ! array_key_exists( $data['status'], self::CAMPAIGN_STATUSES ) ) {
            $errors[] = __( 'Invalid campaign status.', 'woo-offers' );
        }
        
        if ( ! empty( $errors ) ) {
            return new \WP_Error( 'validation_failed', implode( ' ', $errors ) );
        }
        
        return true;
    }
    
    private static function prepare_campaign_data( $data ) {
        $prepared = [];
        
        $string_fields = [ 'name', 'description', 'type', 'status' ];
        foreach ( $string_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $prepared[ $field ] = sanitize_text_field( $data[ $field ] );
            }
        }
        
        $json_fields = [ 'settings', 'targeting_rules', 'schedule_config', 'design_config' ];
        foreach ( $json_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $prepared[ $field ] = is_string( $data[ $field ] ) ? $data[ $field ] : wp_json_encode( $data[ $field ] );
            }
        }
        
        $int_fields = [ 'priority', 'usage_limit' ];
        foreach ( $int_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $prepared[ $field ] = (int) $data[ $field ];
            }
        }
        
        $date_fields = [ 'start_date', 'end_date' ];
        foreach ( $date_fields as $field ) {
            if ( isset( $data[ $field ] ) && ! empty( $data[ $field ] ) ) {
                $prepared[ $field ] = gmdate( 'Y-m-d H:i:s', strtotime( $data[ $field ] ) );
            }
        }
        
        return $prepared;
    }
    
    private static function decode_campaign_json_fields( $campaign ) {
        $json_fields = [ 'settings', 'targeting_rules', 'schedule_config', 'design_config' ];
        
        foreach ( $json_fields as $field ) {
            if ( isset( $campaign->$field ) && ! empty( $campaign->$field ) ) {
                $decoded = json_decode( $campaign->$field, true );
                if ( json_last_error() === JSON_ERROR_NONE ) {
                    $campaign->$field = $decoded;
                }
            }
        }
        
        return $campaign;
    }
    
    private static function log_campaign_event( $campaign_id, $event_type, $event_data = [] ) {
        global $wpdb;
        
        $tables = self::get_tables();
        
        $analytics_data = [
            'campaign_id' => $campaign_id,
            'user_id' => get_current_user_id(),
            'session_id' => session_id() ?: '',
            'event_type' => $event_type,
            'event_data' => wp_json_encode( $event_data ),
            'page_type' => 'admin',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => SecurityManager::get_client_ip(),
            'created_at' => current_time( 'mysql' )
        ];
        
        $wpdb->insert( $tables['analytics'], $analytics_data );
    }
    
    /**
     * Update an existing campaign
     */
    public static function update_campaign( $campaign_id, $data ) {
        global $wpdb;
        
        try {
            $existing = self::get_campaign( $campaign_id );
            if ( ! $existing ) {
                return new \WP_Error( 'not_found', __( 'Campaign not found.', 'woo-offers' ) );
            }
            
            $validation = self::validate_campaign_data( $data, $campaign_id );
            if ( is_wp_error( $validation ) ) {
                return $validation;
            }
            
            $update_data = self::prepare_campaign_data( $data );
            $update_data['updated_at'] = current_time( 'mysql' );
            
            $status_changed = isset( $data['status'] ) && $existing->status !== $data['status'];
            $old_status = $existing->status;
            
            $tables = self::get_tables();
            
            $result = $wpdb->update( 
                $tables['campaigns'], 
                $update_data, 
                [ 'id' => $campaign_id ],
                null,
                [ '%d' ]
            );
            
            if ( false === $result ) {
                error_log( 'WooOffers: Failed to update campaign - DB Error: ' . $wpdb->last_error );
                return new \WP_Error( 'db_error', __( 'Failed to update campaign.', 'woo-offers' ) );
            }
            
            if ( $status_changed ) {
                self::handle_status_change( $campaign_id, $old_status, $data['status'] );
            }
            
            // ✅ PERFORMANCE: Clear cache after update
            self::clear_cache($campaign_id);
            
            self::log_campaign_event( $campaign_id, 'campaign_updated', [
                'user_id' => get_current_user_id(),
                'changes' => $update_data,
                'status_changed' => $status_changed
            ] );
            
            do_action( 'woo_offers_campaign_updated', $campaign_id, $update_data, $existing );
            
            return true;
            
        } catch ( \Exception $e ) {
            error_log( 'WooOffers: Campaign update failed - ' . $e->getMessage() );
            return new \WP_Error( 'update_failed', __( 'Campaign update failed.', 'woo-offers' ) );
        }
    }
    
    /**
     * Delete a campaign
     */
    public static function delete_campaign( $campaign_id ) {
        global $wpdb;
        
        try {
            $campaign = self::get_campaign( $campaign_id );
            if ( ! $campaign ) {
                return new \WP_Error( 'not_found', __( 'Campaign not found.', 'woo-offers' ) );
            }
            
            $tables = self::get_tables();
            
            $result = $wpdb->delete( 
                $tables['campaigns'], 
                [ 'id' => $campaign_id ],
                [ '%d' ]
            );
            
            if ( false === $result ) {
                error_log( 'WooOffers: Failed to delete campaign - DB Error: ' . $wpdb->last_error );
                return new \WP_Error( 'db_error', __( 'Failed to delete campaign.', 'woo-offers' ) );
            }
            
            error_log( "WooOffers: Campaign deleted - ID: $campaign_id, Name: {$campaign->name}, User: " . get_current_user_id() );
            
            // ✅ PERFORMANCE: Clear cache after deletion
            self::clear_cache($campaign_id);
            wp_cache_delete('woo_campaigns_list', 'woo_offers');
            
            do_action( 'woo_offers_campaign_deleted', $campaign_id, $campaign );
            
            return true;
            
        } catch ( \Exception $e ) {
            error_log( 'WooOffers: Campaign deletion failed - ' . $e->getMessage() );
            return new \WP_Error( 'deletion_failed', __( 'Campaign deletion failed.', 'woo-offers' ) );
        }
    }
    
    /**
     * Duplicate a campaign
     */
    public static function duplicate_campaign( $campaign_id, $overrides = [] ) {
        $original = self::get_campaign( $campaign_id );
        if ( ! $original ) {
            return new \WP_Error( 'not_found', __( 'Original campaign not found.', 'woo-offers' ) );
        }
        
        $duplicate_data = [
            'name'            => $overrides['name'] ?? $original->name . ' (Copy)',
            'description'     => $original->description,
            'type'            => $original->type,
            'status'          => 'draft',
            'settings'        => $original->settings,
            'targeting_rules' => $original->targeting_rules,
            'schedule_config' => $original->schedule_config,
            'design_config'   => $original->design_config,
            'priority'        => $original->priority,
            'usage_limit'     => $original->usage_limit
        ];
        
        $duplicate_data = array_merge( $duplicate_data, $overrides );
        
        $new_campaign_id = self::create_campaign( $duplicate_data );
        
        if ( ! is_wp_error( $new_campaign_id ) ) {
            self::log_campaign_event( $new_campaign_id, 'campaign_duplicated', [
                'original_campaign_id' => $campaign_id,
                'user_id' => get_current_user_id()
            ] );
            
            do_action( 'woo_offers_campaign_duplicated', $new_campaign_id, $campaign_id );
        }
        
        return $new_campaign_id;
    }
    
    /**
     * Toggle campaign status
     */
    public static function toggle_campaign_status( $campaign_id ) {
        $campaign = self::get_campaign( $campaign_id );
        if ( ! $campaign ) {
            return new \WP_Error( 'not_found', __( 'Campaign not found.', 'woo-offers' ) );
        }
        
        $new_status = ( $campaign->status === 'active' ) ? 'paused' : 'active';
        
        return self::update_campaign( $campaign_id, [ 'status' => $new_status ] );
    }
    
    /**
     * Handle status changes
     */
    private static function handle_status_change( $campaign_id, $old_status, $new_status ) {
        switch ( $new_status ) {
            case 'active':
                do_action( 'woo_offers_campaign_activated', $campaign_id, $old_status );
                break;
            case 'paused':
                do_action( 'woo_offers_campaign_paused', $campaign_id, $old_status );
                break;
            case 'completed':
                do_action( 'woo_offers_campaign_completed', $campaign_id, $old_status );
                break;
        }
    }
    
    /**
     * AJAX: Save campaign
     */
    public static function ajax_save_campaign() {
        try {
            SecurityManager::verify_ajax_nonce( 'woo_offers_save_campaign' );
            SecurityManager::verify_capability( 'manage_woocommerce' );
            
            $campaign_data = SecurityManager::sanitize_campaign_data( $_POST );
            
            if ( empty( $campaign_data['id'] ) ) {
                $result = self::create_campaign( $campaign_data );
                $action = 'created';
            } else {
                $campaign_id = (int) $campaign_data['id'];
                unset( $campaign_data['id'] );
                $result = self::update_campaign( $campaign_id, $campaign_data );
                $action = 'updated';
            }
            
            if ( is_wp_error( $result ) ) {
                wp_send_json_error( [
                    'message' => $result->get_error_message(),
                    'code' => $result->get_error_code()
                ] );
            }
            
            wp_send_json_success( [
                'message' => sprintf( __( 'Campaign %s successfully.', 'woo-offers' ), $action ),
                'campaign_id' => $action === 'created' ? $result : $campaign_id,
                'action' => $action
            ] );
            
        } catch ( \Exception $e ) {
            error_log( 'WooOffers: Save campaign AJAX failed - ' . $e->getMessage() );
            wp_send_json_error( [
                'message' => __( 'Failed to save campaign.', 'woo-offers' ),
                'code' => 'SAVE_FAILED'
            ] );
        }
    }
    
    /**
     * AJAX: Delete campaign
     */
    public static function ajax_delete_campaign() {
        try {
            SecurityManager::verify_ajax_nonce( 'woo_offers_delete_campaign' );
            SecurityManager::verify_capability( 'manage_woocommerce' );
            
            $campaign_id = (int) ($_POST['campaign_id'] ?? 0);
            
            if ( empty( $campaign_id ) ) {
                wp_send_json_error( [
                    'message' => __( 'Invalid campaign ID.', 'woo-offers' ),
                    'code' => 'INVALID_ID'
                ] );
            }
            
            $result = self::delete_campaign( $campaign_id );
            
            if ( is_wp_error( $result ) ) {
                wp_send_json_error( [
                    'message' => $result->get_error_message(),
                    'code' => $result->get_error_code()
                ] );
            }
            
            wp_send_json_success( [
                'message' => __( 'Campaign deleted successfully.', 'woo-offers' ),
                'campaign_id' => $campaign_id
            ] );
            
        } catch ( \Exception $e ) {
            error_log( 'WooOffers: Delete campaign AJAX failed - ' . $e->getMessage() );
            wp_send_json_error( [
                'message' => __( 'Failed to delete campaign.', 'woo-offers' ),
                'code' => 'DELETE_FAILED'
            ] );
        }
    }
    
    /**
     * Campaign lifecycle handlers
     */
    public static function on_campaign_activated( $campaign_id, $old_status ) {
        error_log( "WooOffers: Campaign activated - ID: $campaign_id, Previous status: $old_status" );
        
        // Clear any existing schedules
        wp_clear_scheduled_hook( 'woo_offers_campaign_schedule_check', [ $campaign_id ] );
        
        // Update metrics
        self::update_campaign_metrics( $campaign_id );
    }
    
    public static function on_campaign_paused( $campaign_id, $old_status ) {
        error_log( "WooOffers: Campaign paused - ID: $campaign_id, Previous status: $old_status" );
        
        // Clear schedules
        wp_clear_scheduled_hook( 'woo_offers_campaign_schedule_check', [ $campaign_id ] );
    }
    
    /**
     * Update campaign metrics
     */
    private static function update_campaign_metrics( $campaign_id ) {
        global $wpdb;
        
        $tables = self::get_tables();
        
        // Get metrics from analytics
        $metrics = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                COUNT(*) as total_events,
                SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views,
                SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as clicks,
                SUM(CASE WHEN event_type = 'conversion' THEN 1 ELSE 0 END) as conversions,
                SUM(revenue_impact) as total_revenue
            FROM {$tables['analytics']} 
            WHERE campaign_id = %d",
            $campaign_id
        ) );
        
        if ( $metrics ) {
            $wpdb->update(
                $tables['campaigns'],
                [
                    'views_count' => (int) $metrics->views,
                    'clicks_count' => (int) $metrics->clicks,
                    'conversions_count' => (int) $metrics->conversions,
                    'revenue_generated' => (float) $metrics->total_revenue
                ],
                [ 'id' => $campaign_id ],
                [ '%d', '%d', '%d', '%f' ],
                [ '%d' ]
            );
        }
    }
    
    /**
     * Get campaign statistics
     */
    public static function get_campaign_stats( $campaign_id, $date_range = 30 ) {
        global $wpdb;
        
        $tables = self::get_tables();
        
        $stats = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                COUNT(*) as total_events,
                SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views,
                SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as clicks,
                SUM(CASE WHEN event_type = 'conversion' THEN 1 ELSE 0 END) as conversions,
                SUM(revenue_impact) as revenue,
                COUNT(DISTINCT visitor_id) as unique_visitors
            FROM {$tables['analytics']} 
            WHERE campaign_id = %d 
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
            $campaign_id,
            $date_range
        ) );
        
        $conversion_rate = 0;
        if ( $stats && $stats->views > 0 ) {
            $conversion_rate = ( $stats->conversions / $stats->views ) * 100;
        }
        
        return [
            'views' => (int) ($stats->views ?? 0),
            'clicks' => (int) ($stats->clicks ?? 0),
            'conversions' => (int) ($stats->conversions ?? 0),
            'revenue' => (float) ($stats->revenue ?? 0),
            'unique_visitors' => (int) ($stats->unique_visitors ?? 0),
            'conversion_rate' => round( $conversion_rate, 2 ),
            'click_through_rate' => $stats && $stats->views > 0 ? round( ( $stats->clicks / $stats->views ) * 100, 2 ) : 0
        ];
    }
    
    /**
     * ========================================
     * CAMPAIGN BUILDER INTEGRATION METHODS
     * ========================================
     */
    
    /**
     * Save Campaign Builder data
     */
    public static function save_builder_campaign( $campaign_id, $builder_data ) {
        global $wpdb;
        
        try {
            // Validate builder data structure
            $validation = self::validate_builder_data( $builder_data );
            if ( is_wp_error( $validation ) ) {
                return $validation;
            }
            
            // Get existing campaign
            $campaign = self::get_campaign( $campaign_id );
            if ( ! $campaign ) {
                return new \WP_Error( 'not_found', __( 'Campaign not found.', 'woo-offers' ) );
            }
            
            // Prepare builder data for storage
            $update_data = [
                'design_config' => wp_json_encode( $builder_data ),
                'updated_at' => current_time( 'mysql' )
            ];
            
            $tables = self::get_tables();
            
            $result = $wpdb->update( 
                $tables['campaigns'], 
                $update_data, 
                [ 'id' => $campaign_id ],
                null,
                [ '%d' ]
            );
            
            if ( false === $result ) {
                error_log( 'WooOffers: Failed to save builder data - DB Error: ' . $wpdb->last_error );
                return new \WP_Error( 'db_error', __( 'Failed to save campaign builder data.', 'woo-offers' ) );
            }
            
            // Log the save event
            self::log_campaign_event( $campaign_id, 'builder_saved', [
                'user_id' => get_current_user_id(),
                'builder_data_size' => strlen( wp_json_encode( $builder_data ) ),
                'components_count' => count( $builder_data['content']['components'] ?? [] ),
                'workflow_nodes_count' => count( $builder_data['workflow']['nodes'] ?? [] )
            ] );
            
            do_action( 'woo_offers_builder_campaign_saved', $campaign_id, $builder_data );
            
            return true;
            
        } catch ( \Exception $e ) {
            error_log( 'WooOffers: Builder save failed - ' . $e->getMessage() );
            return new \WP_Error( 'save_failed', __( 'Failed to save campaign builder data.', 'woo-offers' ) );
        }
    }
    
    /**
     * Load Campaign Builder data
     */
    public static function load_builder_campaign( $campaign_id ) {
        $campaign = self::get_campaign( $campaign_id );
        if ( ! $campaign ) {
            return new \WP_Error( 'not_found', __( 'Campaign not found.', 'woo-offers' ) );
        }
        
        // Extract builder data from design_config
        $builder_data = [];
        if ( ! empty( $campaign->design_config ) ) {
            $builder_data = is_array( $campaign->design_config ) ? $campaign->design_config : [];
        }
        
        // Ensure builder data has required structure
        $builder_data = self::ensure_builder_data_structure( $builder_data );
        
        // Add campaign metadata
        $builder_data['metadata'] = [
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->name,
            'campaign_type' => $campaign->type,
            'campaign_status' => $campaign->status,
            'last_modified' => $campaign->updated_at,
            'created_by' => $campaign->created_by
        ];
        
        return $builder_data;
    }
    
    /**
     * Validate Campaign Builder data structure
     */
    private static function validate_builder_data( $data ) {
        $errors = [];
        
        // Check required top-level structure
        $required_keys = [ 'content', 'workflow', 'settings' ];
        foreach ( $required_keys as $key ) {
            if ( ! isset( $data[ $key ] ) ) {
                $errors[] = sprintf( __( 'Missing required field: %s', 'woo-offers' ), $key );
            }
        }
        
        // Validate content structure
        if ( isset( $data['content'] ) ) {
            if ( ! isset( $data['content']['components'] ) || ! is_array( $data['content']['components'] ) ) {
                $errors[] = __( 'Content components must be an array.', 'woo-offers' );
            }
        }
        
        // Validate workflow structure
        if ( isset( $data['workflow'] ) ) {
            if ( ! isset( $data['workflow']['nodes'] ) || ! is_array( $data['workflow']['nodes'] ) ) {
                $errors[] = __( 'Workflow nodes must be an array.', 'woo-offers' );
            }
            
            if ( ! isset( $data['workflow']['connections'] ) || ! is_array( $data['workflow']['connections'] ) ) {
                $errors[] = __( 'Workflow connections must be an array.', 'woo-offers' );
            }
        }
        
        // Validate settings structure
        if ( isset( $data['settings'] ) ) {
            if ( ! is_array( $data['settings'] ) ) {
                $errors[] = __( 'Settings must be an object.', 'woo-offers' );
            }
        }
        
        if ( ! empty( $errors ) ) {
            return new \WP_Error( 'validation_failed', implode( ' ', $errors ) );
        }
        
        return true;
    }
    
    /**
     * Ensure builder data has the complete required structure
     */
    private static function ensure_builder_data_structure( $data ) {
        $default_structure = [
            'content' => [
                'components' => [],
                'layout' => [
                    'type' => 'single-column',
                    'spacing' => 'default',
                    'alignment' => 'center'
                ],
                'theme' => [
                    'colors' => [
                        'primary' => '#007cba',
                        'secondary' => '#50575e', 
                        'background' => '#ffffff',
                        'text' => '#1e1e1e'
                    ],
                    'typography' => [
                        'heading_font' => 'inherit',
                        'body_font' => 'inherit',
                        'font_sizes' => [
                            'small' => '14px',
                            'medium' => '16px',
                            'large' => '20px',
                            'xlarge' => '24px'
                        ]
                    ]
                ]
            ],
            'workflow' => [
                'nodes' => [],
                'connections' => [],
                'settings' => [
                    'auto_start' => false,
                    'trigger_events' => [],
                    'execution_limit' => null
                ]
            ],
            'settings' => [
                'auto_save' => true,
                'history_enabled' => true,
                'version' => '3.0.0',
                'last_auto_save' => null
            ],
            'history' => [
                'current_index' => 0,
                'max_entries' => 50,
                'entries' => []
            ]
        ];
        
        return array_merge( $default_structure, $data );
    }
    
    /**
     * Create a new campaign from Campaign Builder
     */
    public static function create_campaign_from_builder( $builder_data, $campaign_meta ) {
        try {
            // Validate input data
            $validation = self::validate_builder_data( $builder_data );
            if ( is_wp_error( $validation ) ) {
                return $validation;
            }
            
            // Prepare campaign data
            $campaign_data = [
                'name' => $campaign_meta['name'] ?? __( 'Untitled Campaign', 'woo-offers' ),
                'description' => $campaign_meta['description'] ?? '',
                'type' => $campaign_meta['type'] ?? 'product_upsell',
                'status' => $campaign_meta['status'] ?? 'draft',
                'design_config' => $builder_data,
                'priority' => $campaign_meta['priority'] ?? 10
            ];
            
            // Add scheduling and targeting if provided
            if ( ! empty( $campaign_meta['schedule_config'] ) ) {
                $campaign_data['schedule_config'] = $campaign_meta['schedule_config'];
                $campaign_data['start_date'] = $campaign_meta['start_date'] ?? null;
                $campaign_data['end_date'] = $campaign_meta['end_date'] ?? null;
            }
            
            if ( ! empty( $campaign_meta['targeting_rules'] ) ) {
                $campaign_data['targeting_rules'] = $campaign_meta['targeting_rules'];
            }
            
            if ( ! empty( $campaign_meta['settings'] ) ) {
                $campaign_data['settings'] = $campaign_meta['settings'];
            }
            
            // Create the campaign
            $campaign_id = self::create_campaign( $campaign_data );
            
            if ( is_wp_error( $campaign_id ) ) {
                return $campaign_id;
            }
            
            // Log builder creation
            self::log_campaign_event( $campaign_id, 'created_from_builder', [
                'user_id' => get_current_user_id(),
                'components_count' => count( $builder_data['content']['components'] ?? [] ),
                'workflow_nodes_count' => count( $builder_data['workflow']['nodes'] ?? [] ),
                'campaign_meta' => $campaign_meta
            ] );
            
            do_action( 'woo_offers_campaign_created_from_builder', $campaign_id, $builder_data, $campaign_meta );
            
            return $campaign_id;
            
        } catch ( \Exception $e ) {
            error_log( 'WooOffers: Create campaign from builder failed - ' . $e->getMessage() );
            return new \WP_Error( 'creation_failed', __( 'Failed to create campaign from builder.', 'woo-offers' ) );
        }
    }
    
    /**
     * Get campaign preview data for the builder
     */
    public static function get_campaign_preview_data( $campaign_id ) {
        $campaign = self::get_campaign( $campaign_id );
        if ( ! $campaign ) {
            return new \WP_Error( 'not_found', __( 'Campaign not found.', 'woo-offers' ) );
        }
        
        $preview_data = [
            'campaign' => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'type' => $campaign->type,
                'status' => $campaign->status
            ],
            'design_config' => $campaign->design_config ?? [],
            'targeting_rules' => $campaign->targeting_rules ?? [],
            'schedule_config' => $campaign->schedule_config ?? [],
            'settings' => $campaign->settings ?? []
        ];
        
        return $preview_data;
    }
    
    /**
     * AJAX: Save Campaign Builder data
     */
    public static function ajax_save_builder_data() {
        try {
            SecurityManager::verify_ajax_nonce( 'woo_offers_builder_save' );
            SecurityManager::verify_capability( 'manage_woocommerce' );
            
            $campaign_id = (int) ($_POST['campaign_id'] ?? 0);
            $builder_data = json_decode( stripslashes( $_POST['builder_data'] ?? '{}' ), true );
            
            if ( empty( $campaign_id ) ) {
                wp_send_json_error( [
                    'message' => __( 'Invalid campaign ID.', 'woo-offers' ),
                    'code' => 'INVALID_ID'
                ] );
            }
            
            if ( empty( $builder_data ) ) {
                wp_send_json_error( [
                    'message' => __( 'No builder data provided.', 'woo-offers' ),
                    'code' => 'NO_DATA'
                ] );
            }
            
            $result = self::save_builder_campaign( $campaign_id, $builder_data );
            
            if ( is_wp_error( $result ) ) {
                wp_send_json_error( [
                    'message' => $result->get_error_message(),
                    'code' => $result->get_error_code()
                ] );
            }
            
            wp_send_json_success( [
                'message' => __( 'Campaign builder data saved successfully.', 'woo-offers' ),
                'campaign_id' => $campaign_id,
                'timestamp' => current_time( 'mysql' )
            ] );
            
        } catch ( \Exception $e ) {
            error_log( 'WooOffers: Save builder data AJAX failed - ' . $e->getMessage() );
            wp_send_json_error( [
                'message' => __( 'Failed to save builder data.', 'woo-offers' ),
                'code' => 'SAVE_FAILED'
            ] );
        }
    }
    
    /**
     * AJAX: Load Campaign Builder data
     */
    public static function ajax_load_builder_data() {
        try {
            SecurityManager::verify_ajax_nonce( 'woo_offers_builder_load' );
            SecurityManager::verify_capability( 'manage_woocommerce' );
            
            $campaign_id = (int) ($_POST['campaign_id'] ?? 0);
            
            if ( empty( $campaign_id ) ) {
                wp_send_json_error( [
                    'message' => __( 'Invalid campaign ID.', 'woo-offers' ),
                    'code' => 'INVALID_ID'
                ] );
            }
            
            $builder_data = self::load_builder_campaign( $campaign_id );
            
            if ( is_wp_error( $builder_data ) ) {
                wp_send_json_error( [
                    'message' => $builder_data->get_error_message(),
                    'code' => $builder_data->get_error_code()
                ] );
            }
            
            wp_send_json_success( [
                'message' => __( 'Campaign builder data loaded successfully.', 'woo-offers' ),
                'data' => $builder_data
            ] );
            
        } catch ( \Exception $e ) {
            error_log( 'WooOffers: Load builder data AJAX failed - ' . $e->getMessage() );
            wp_send_json_error( [
                'message' => __( 'Failed to load builder data.', 'woo-offers' ),
                'code' => 'LOAD_FAILED'
            ] );
        }
    }
    
    /**
     * AJAX: Create campaign from builder
     */
    public static function ajax_create_campaign_from_builder() {
        try {
            SecurityManager::verify_ajax_nonce( 'woo_offers_builder_create' );
            SecurityManager::verify_capability( 'manage_woocommerce' );
            
            $builder_data = json_decode( stripslashes( $_POST['builder_data'] ?? '{}' ), true );
            $campaign_meta = json_decode( stripslashes( $_POST['campaign_meta'] ?? '{}' ), true );
            
            if ( empty( $builder_data ) ) {
                wp_send_json_error( [
                    'message' => __( 'No builder data provided.', 'woo-offers' ),
                    'code' => 'NO_DATA'
                ] );
            }
            
            $campaign_id = self::create_campaign_from_builder( $builder_data, $campaign_meta );
            
            if ( is_wp_error( $campaign_id ) ) {
                wp_send_json_error( [
                    'message' => $campaign_id->get_error_message(),
                    'code' => $campaign_id->get_error_code()
                ] );
            }
            
            wp_send_json_success( [
                'message' => __( 'Campaign created successfully from builder.', 'woo-offers' ),
                'campaign_id' => $campaign_id,
                'redirect_url' => admin_url( 'admin.php?page=woo-offers&view=campaigns&campaign_id=' . $campaign_id )
            ] );
            
        } catch ( \Exception $e ) {
            error_log( 'WooOffers: Create from builder AJAX failed - ' . $e->getMessage() );
            wp_send_json_error( [
                'message' => __( 'Failed to create campaign from builder.', 'woo-offers' ),
                'code' => 'CREATE_FAILED'
            ] );
        }
    }
    
    /**
     * Get the Campaign Builder JSON schema for validation
     */
    public static function get_builder_schema() {
        return [
            'type' => 'object',
            'required' => [ 'content', 'workflow', 'settings' ],
            'properties' => [
                'content' => [
                    'type' => 'object',
                    'required' => [ 'components' ],
                    'properties' => [
                        'components' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'required' => [ 'id', 'type', 'position' ],
                                'properties' => [
                                    'id' => [ 'type' => 'string' ],
                                    'type' => [ 
                                        'type' => 'string',
                                        'enum' => [ 'heading', 'text', 'button', 'image', 'video', 'spacer', 'divider', 'countdown', 'social' ]
                                    ],
                                    'position' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'x' => [ 'type' => 'number' ],
                                            'y' => [ 'type' => 'number' ],
                                            'width' => [ 'type' => 'number' ],
                                            'height' => [ 'type' => 'number' ]
                                        ]
                                    ],
                                    'properties' => [ 'type' => 'object' ],
                                    'styles' => [ 'type' => 'object' ]
                                ]
                            ]
                        ],
                        'layout' => [
                            'type' => 'object',
                            'properties' => [
                                'type' => [ 'type' => 'string' ],
                                'spacing' => [ 'type' => 'string' ],
                                'alignment' => [ 'type' => 'string' ]
                            ]
                        ],
                        'theme' => [
                            'type' => 'object',
                            'properties' => [
                                'colors' => [ 'type' => 'object' ],
                                'typography' => [ 'type' => 'object' ]
                            ]
                        ]
                    ]
                ],
                'workflow' => [
                    'type' => 'object',
                    'required' => [ 'nodes', 'connections' ],
                    'properties' => [
                        'nodes' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'required' => [ 'id', 'type', 'position' ],
                                'properties' => [
                                    'id' => [ 'type' => 'string' ],
                                    'type' => [ 
                                        'type' => 'string',
                                        'enum' => [ 'start', 'action', 'condition', 'delay', 'end' ]
                                    ],
                                    'position' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'x' => [ 'type' => 'number' ],
                                            'y' => [ 'type' => 'number' ]
                                        ]
                                    ],
                                    'properties' => [ 'type' => 'object' ]
                                ]
                            ]
                        ],
                        'connections' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'required' => [ 'from', 'to' ],
                                'properties' => [
                                    'from' => [ 'type' => 'string' ],
                                    'to' => [ 'type' => 'string' ],
                                    'conditions' => [ 'type' => 'object' ]
                                ]
                            ]
                        ],
                        'settings' => [
                            'type' => 'object',
                            'properties' => [
                                'auto_start' => [ 'type' => 'boolean' ],
                                'trigger_events' => [ 'type' => 'array' ],
                                'execution_limit' => [ 'type' => [ 'number', 'null' ] ]
                            ]
                        ]
                    ]
                ],
                'settings' => [
                    'type' => 'object',
                    'properties' => [
                        'auto_save' => [ 'type' => 'boolean' ],
                        'history_enabled' => [ 'type' => 'boolean' ],
                        'version' => [ 'type' => 'string' ]
                    ]
                ]
            ]
        ];
    }
} 