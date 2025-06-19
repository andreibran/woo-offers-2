<?php

namespace WooOffers\Core;

/**
 * Database installer and schema management
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Installer class for managing database schema
 */
class Installer {

    /**
     * Database version
     */
    const DB_VERSION = '1.0.0';

    /**
     * Run installation process
     */
    public static function activate() {
        self::create_tables();
        self::create_default_options();
        self::schedule_events();
        self::set_database_version();
        
        // Add capabilities to roles
        Permissions::add_capabilities();
        
        // Clear any cached data
        wp_cache_flush();
    }

    /**
     * Run deactivation process
     */
    public static function deactivate() {
        self::clear_scheduled_events();
        // Note: We don't drop tables on deactivation, only on uninstall
    }

    /**
     * Run uninstall process
     */
    public static function uninstall() {
        if ( get_option( 'woo_offers_remove_data', false ) ) {
            self::drop_tables();
            self::remove_options();
        }
        self::clear_scheduled_events();
    }

    /**
     * Create database tables
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Offers table
        $offers_table = $wpdb->prefix . 'woo_offers';
        $offers_sql = "CREATE TABLE {$offers_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            type varchar(50) NOT NULL DEFAULT 'quantity_break',
            status varchar(20) NOT NULL DEFAULT 'active',
            conditions longtext,
            actions longtext,
            display_options longtext,
            targeting_rules longtext,
            start_date datetime DEFAULT NULL,
            end_date datetime DEFAULT NULL,
            priority int(11) NOT NULL DEFAULT 10,
            usage_count bigint(20) unsigned DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY type (type),
            KEY start_date (start_date),
            KEY end_date (end_date),
            KEY priority (priority)
        ) {$charset_collate};";

        // Analytics table
        $analytics_table = $wpdb->prefix . 'woo_offers_analytics';
        $analytics_sql = "CREATE TABLE {$analytics_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            offer_id bigint(20) unsigned NOT NULL,
            event_type varchar(50) NOT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            session_id varchar(100),
            product_id bigint(20) unsigned DEFAULT NULL,
            order_id bigint(20) unsigned DEFAULT NULL,
            revenue decimal(10,2) DEFAULT NULL,
            quantity int(11) DEFAULT NULL,
            metadata longtext,
            ip_address varchar(45),
            user_agent text,
            referrer varchar(255),
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY offer_id (offer_id),
            KEY event_type (event_type),
            KEY user_id (user_id),
            KEY session_id (session_id),
            KEY product_id (product_id),
            KEY order_id (order_id),
            KEY created_at (created_at)
        ) {$charset_collate};";

        // A/B Testing table
        $ab_tests_table = $wpdb->prefix . 'woo_offers_ab_tests';
        $ab_tests_sql = "CREATE TABLE {$ab_tests_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            status varchar(20) NOT NULL DEFAULT 'draft',
            original_offer_id bigint(20) unsigned NOT NULL,
            variant_offer_ids longtext,
            traffic_allocation longtext,
            conversion_goals longtext,
            winner_offer_id bigint(20) unsigned DEFAULT NULL,
            confidence_level decimal(5,2) DEFAULT 95.00,
            start_date datetime DEFAULT NULL,
            end_date datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY original_offer_id (original_offer_id),
            KEY winner_offer_id (winner_offer_id),
            KEY start_date (start_date),
            KEY end_date (end_date)
        ) {$charset_collate};";

        // User assignments for A/B testing
        $user_assignments_table = $wpdb->prefix . 'woo_offers_user_assignments';
        $user_assignments_sql = "CREATE TABLE {$user_assignments_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            test_id bigint(20) unsigned NOT NULL,
            user_identifier varchar(100) NOT NULL,
            offer_id bigint(20) unsigned NOT NULL,
            assigned_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_assignment (test_id, user_identifier),
            KEY test_id (test_id),
            KEY user_identifier (user_identifier),
            KEY offer_id (offer_id)
        ) {$charset_collate};";

        // Sessions table for tracking user behavior
        $sessions_table = $wpdb->prefix . 'woo_offers_sessions';
        $sessions_sql = "CREATE TABLE {$sessions_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            session_id varchar(100) NOT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            ip_address varchar(45),
            user_agent text,
            first_seen datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_seen datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            page_views int(11) DEFAULT 1,
            offers_viewed longtext,
            offers_clicked longtext,
            conversions longtext,
            PRIMARY KEY (id),
            UNIQUE KEY session_id (session_id),
            KEY user_id (user_id),
            KEY first_seen (first_seen),
            KEY last_seen (last_seen)
        ) {$charset_collate};";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        
        dbDelta( $offers_sql );
        dbDelta( $analytics_sql );
        dbDelta( $ab_tests_sql );
        dbDelta( $user_assignments_sql );
        dbDelta( $sessions_sql );
    }

    /**
     * Create default plugin options
     */
    private static function create_default_options() {
        $default_options = [
            'woo_offers_version' => WOO_OFFERS_VERSION,
            'woo_offers_db_version' => self::DB_VERSION,
            'woo_offers_settings' => [
                'enable_analytics' => true,
                'enable_ab_testing' => true,
                'session_timeout' => 30, // minutes
                'analytics_retention' => 365, // days
                'primary_color' => '#e92d3b',
                'default_position' => 'before_add_to_cart',
                'enable_mobile_optimization' => true,
                'cache_offers' => true,
                'load_scripts_everywhere' => false,
                'debug_mode' => false
            ],
            'woo_offers_wizard_completed' => false,
            'woo_offers_remove_data' => false
        ];

        foreach ( $default_options as $option_name => $option_value ) {
            add_option( $option_name, $option_value );
        }
    }

    /**
     * Schedule WordPress events
     */
    private static function schedule_events() {
        // Schedule analytics cleanup
        if ( ! wp_next_scheduled( 'woo_offers_analytics_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'woo_offers_analytics_cleanup' );
        }

        // Schedule A/B test monitoring
        if ( ! wp_next_scheduled( 'woo_offers_ab_test_monitor' ) ) {
            wp_schedule_event( time(), 'hourly', 'woo_offers_ab_test_monitor' );
        }
    }

    /**
     * Clear scheduled events
     */
    private static function clear_scheduled_events() {
        wp_clear_scheduled_hook( 'woo_offers_analytics_cleanup' );
        wp_clear_scheduled_hook( 'woo_offers_ab_test_monitor' );
    }

    /**
     * Set database version
     */
    private static function set_database_version() {
        update_option( 'woo_offers_db_version', self::DB_VERSION );
    }

    /**
     * Drop all plugin tables
     */
    private static function drop_tables() {
        global $wpdb;

        $tables = [
            $wpdb->prefix . 'woo_offers_user_assignments',
            $wpdb->prefix . 'woo_offers_sessions',
            $wpdb->prefix . 'woo_offers_analytics',
            $wpdb->prefix . 'woo_offers_ab_tests',
            $wpdb->prefix . 'woo_offers'
        ];

        foreach ( $tables as $table ) {
            $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
        }
    }

    /**
     * Remove all plugin options
     */
    private static function remove_options() {
        $options = [
            'woo_offers_version',
            'woo_offers_db_version', 
            'woo_offers_settings',
            'woo_offers_wizard_completed',
            'woo_offers_remove_data'
        ];

        foreach ( $options as $option ) {
            delete_option( $option );
        }
    }

    /**
     * Check if database needs updating
     */
    public static function needs_database_update() {
        $current_db_version = get_option( 'woo_offers_db_version', '0.0.0' );
        return version_compare( $current_db_version, self::DB_VERSION, '<' );
    }

    /**
     * Update database if needed
     */
    public static function maybe_update_database() {
        if ( self::needs_database_update() ) {
            self::create_tables();
            self::set_database_version();
        }
    }
}