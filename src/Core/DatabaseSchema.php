<?php
/**
 * Database Schema Manager for Woo Offers v3.0
 */

namespace WooOffers\Core;

defined( 'ABSPATH' ) || exit;

class DatabaseSchema {
    
    const DB_VERSION = '3.0.0';
    const VERSION_OPTION = 'woo_offers_db_version';
    
    const CAMPAIGNS_TABLE = 'woo_campaigns';
    const ANALYTICS_TABLE = 'woo_campaign_analytics';
    const TESTS_TABLE = 'woo_campaign_tests';
    
    public static function init() {
        add_action( 'plugins_loaded', [ __CLASS__, 'check_database_version' ] );
        register_activation_hook( WOO_OFFERS_PLUGIN_FILE, [ __CLASS__, 'create_tables' ] );
    }
    
    public static function check_database_version() {
        $current_version = get_option( self::VERSION_OPTION, '0.0.0' );
        
        if ( version_compare( $current_version, self::DB_VERSION, '<' ) ) {
            self::create_tables();
            update_option( self::VERSION_OPTION, self::DB_VERSION );
        }
    }
    
    public static function create_tables() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        
        self::create_campaigns_table();
        self::create_analytics_table();
        self::create_tests_table();
        
        error_log( 'WooOffers: Database tables created/updated to version ' . self::DB_VERSION );
    }
    
    private static function create_campaigns_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::CAMPAIGNS_TABLE;
        
        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            type varchar(50) NOT NULL DEFAULT 'product_upsell',
            status varchar(20) NOT NULL DEFAULT 'draft',
            
            settings longtext,
            targeting_rules longtext,
            schedule_config longtext,
            design_config longtext,
            
            views_count bigint(20) unsigned DEFAULT 0,
            clicks_count bigint(20) unsigned DEFAULT 0,
            conversions_count bigint(20) unsigned DEFAULT 0,
            revenue_generated decimal(10,2) DEFAULT 0.00,
            
            priority int(11) NOT NULL DEFAULT 10,
            usage_limit int(11) DEFAULT NULL,
            usage_count bigint(20) unsigned DEFAULT 0,
            
            created_by bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            start_date datetime DEFAULT NULL,
            end_date datetime DEFAULT NULL,
            
            PRIMARY KEY (id),
            KEY idx_type_status (type, status),
            KEY idx_priority (priority),
            KEY idx_created_by (created_by),
            KEY idx_schedule (start_date, end_date),
            KEY idx_performance (views_count, conversions_count)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        dbDelta( $sql );
    }
    
    private static function create_analytics_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::ANALYTICS_TABLE;
        
        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            campaign_id bigint(20) unsigned NOT NULL,
            
            user_id bigint(20) unsigned DEFAULT NULL,
            session_id varchar(100),
            visitor_id varchar(100),
            
            event_type varchar(50) NOT NULL,
            event_data longtext,
            
            page_url varchar(500),
            page_type varchar(50),
            product_id bigint(20) unsigned DEFAULT NULL,
            order_id bigint(20) unsigned DEFAULT NULL,
            
            revenue_impact decimal(10,2) DEFAULT NULL,
            discount_amount decimal(10,2) DEFAULT NULL,
            
            user_agent text,
            ip_address varchar(45),
            device_type varchar(20),
            referrer varchar(500),
            
            country varchar(2),
            region varchar(100),
            city varchar(100),
            
            test_id bigint(20) unsigned DEFAULT NULL,
            variation varchar(50),
            
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            
            PRIMARY KEY (id),
            KEY idx_campaign_event (campaign_id, event_type),
            KEY idx_user_session (user_id, session_id),
            KEY idx_visitor (visitor_id),
            KEY idx_temporal (created_at),
            KEY idx_revenue (revenue_impact),
            KEY idx_page_context (page_type, product_id),
            KEY idx_test (test_id, variation)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        dbDelta( $sql );
    }
    
    private static function create_tests_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TESTS_TABLE;
        
        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            hypothesis text,
            
            original_campaign_id bigint(20) unsigned NOT NULL,
            variant_campaigns longtext,
            traffic_allocation longtext,
            
            conversion_goal varchar(50) NOT NULL DEFAULT 'conversions',
            min_confidence_level decimal(5,2) DEFAULT 95.00,
            min_sample_size int(11) DEFAULT 100,
            max_duration_days int(11) DEFAULT 30,
            
            status varchar(20) NOT NULL DEFAULT 'draft',
            winner_campaign_id bigint(20) unsigned DEFAULT NULL,
            confidence_level decimal(5,2) DEFAULT NULL,
            significance_reached boolean DEFAULT FALSE,
            
            start_date datetime DEFAULT NULL,
            end_date datetime DEFAULT NULL,
            actual_end_date datetime DEFAULT NULL,
            
            results_data longtext,
            statistical_data longtext,
            
            created_by bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            PRIMARY KEY (id),
            KEY idx_original_campaign (original_campaign_id),
            KEY idx_status_dates (status, start_date, end_date),
            KEY idx_winner (winner_campaign_id),
            KEY idx_created_by (created_by),
            KEY idx_confidence (confidence_level, significance_reached)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        dbDelta( $sql );
    }
    
    public static function get_table_names() {
        global $wpdb;
        
        return [
            'campaigns' => $wpdb->prefix . self::CAMPAIGNS_TABLE,
            'analytics' => $wpdb->prefix . self::ANALYTICS_TABLE,
            'tests' => $wpdb->prefix . self::TESTS_TABLE
        ];
    }
    
    public static function get_schema_info() {
        global $wpdb;
        
        $tables = self::get_table_names();
        $info = [
            'version' => self::DB_VERSION,
            'current_version' => get_option( self::VERSION_OPTION, '0.0.0' ),
            'tables' => []
        ];
        
        foreach ( $tables as $name => $table ) {
            $exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" );
            $info['tables'][ $name ] = [
                'name' => $table,
                'exists' => ! empty( $exists ),
                'rows' => $exists ? $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) : 0
            ];
        }
        
        return $info;
    }
    
    public static function drop_tables() {
        global $wpdb;
        
        $tables = self::get_table_names();
        
        $wpdb->query( "DROP TABLE IF EXISTS {$tables['analytics']}" );
        $wpdb->query( "DROP TABLE IF EXISTS {$tables['tests']}" );
        $wpdb->query( "DROP TABLE IF EXISTS {$tables['campaigns']}" );
        
        delete_option( self::VERSION_OPTION );
        
        error_log( 'WooOffers: Database tables dropped' );
    }
} 