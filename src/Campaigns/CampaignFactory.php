<?php
/**
 * Campaign Factory - Modular Campaign System Architecture
 * 
 * Provides factory pattern for creating different campaign types with
 * type-specific behaviors and optimized performance.
 *
 * @package WooOffers
 * @since 3.0.0
 */

namespace WooOffers\Campaigns;

use WooOffers\Core\SecurityManager;

defined( 'ABSPATH' ) || exit;

/**
 * ✅ ARCHITECTURE: Campaign Factory for improved modularity
 */
class CampaignFactory {
    
    /**
     * Campaign type handlers registry
     *
     * @var array
     */
    private static $type_handlers = [];
    
    /**
     * Campaign instance cache
     *
     * @var array
     */
    private static $instances = [];
    
    /**
     * Register campaign type handler
     *
     * @param string $type Campaign type
     * @param callable $handler Handler function/class
     */
    public static function register_type_handler($type, $handler) {
        self::$type_handlers[$type] = $handler;
    }
    
    /**
     * Create campaign instance based on type
     *
     * @param string $type Campaign type
     * @param array $data Campaign data
     * @return CampaignInterface|WP_Error
     */
    public static function create_campaign($type, $data = []) {
        try {
            // ✅ SECURITY: Validate campaign type
            if (!self::is_valid_campaign_type($type)) {
                return new \WP_Error('invalid_type', __('Invalid campaign type', 'woo-offers'));
            }
            
            // ✅ PERFORMANCE: Check instance cache
            $cache_key = $type . '_' . md5(serialize($data));
            if (isset(self::$instances[$cache_key])) {
                return self::$instances[$cache_key];
            }
            
            // Get type-specific handler
            $handler = self::get_type_handler($type);
            
            if (!$handler) {
                // Fallback to default campaign creation
                $campaign = self::create_default_campaign($type, $data);
            } else {
                $campaign = call_user_func($handler, $data);
            }
            
            // ✅ PERFORMANCE: Cache instance
            self::$instances[$cache_key] = $campaign;
            
            return $campaign;
            
        } catch (\Exception $e) {
            error_log('WooOffers CampaignFactory Error: ' . $e->getMessage());
            return new \WP_Error('creation_failed', $e->getMessage());
        }
    }
    
    /**
     * Get type-specific configuration
     *
     * @param string $type Campaign type
     * @return array
     */
    public static function get_type_config($type) {
        $configs = [
            'checkout' => [
                'display_positions' => ['before_payment', 'after_payment', 'sidebar'],
                'triggers' => ['cart_total', 'item_count', 'user_role'],
                'compatibility' => ['woocommerce_blocks', 'classic_checkout'],
                'performance' => ['lazy_load' => true, 'cache_ttl' => 300]
            ],
            'cart' => [
                'display_positions' => ['cart_table', 'cart_totals', 'below_cart'],
                'triggers' => ['cart_value', 'product_categories', 'user_history'],
                'compatibility' => ['cart_blocks', 'classic_cart'],
                'performance' => ['lazy_load' => true, 'cache_ttl' => 600]
            ],
            'product' => [
                'display_positions' => ['product_summary', 'product_tabs', 'related_products'],
                'triggers' => ['product_price', 'stock_level', 'user_behavior'],
                'compatibility' => ['product_blocks', 'classic_product'],
                'performance' => ['lazy_load' => false, 'cache_ttl' => 1800]
            ],
            'exit_intent' => [
                'display_positions' => ['modal', 'notification_bar', 'slide_in'],
                'triggers' => ['mouse_leave', 'scroll_position', 'time_on_page'],
                'compatibility' => ['all_pages'],
                'performance' => ['lazy_load' => true, 'cache_ttl' => 3600]
            ],
            'post_purchase' => [
                'display_positions' => ['order_confirmation', 'email', 'account_page'],
                'triggers' => ['order_status', 'order_value', 'product_purchased'],
                'compatibility' => ['order_emails', 'thank_you_page'],
                'performance' => ['lazy_load' => false, 'cache_ttl' => 86400]
            ]
        ];
        
        return $configs[$type] ?? [];
    }
    
    /**
     * Validate campaign data for specific type
     *
     * @param string $type Campaign type
     * @param array $data Campaign data
     * @return true|WP_Error
     */
    public static function validate_campaign_data($type, $data) {
        $config = self::get_type_config($type);
        $errors = [];
        
        // Type-specific validation
        switch ($type) {
            case 'checkout':
                if (empty($data['display_position']) || 
                    !in_array($data['display_position'], $config['display_positions'])) {
                    $errors[] = __('Invalid display position for checkout campaign', 'woo-offers');
                }
                break;
                
            case 'cart':
                if (isset($data['minimum_cart_amount']) && !is_numeric($data['minimum_cart_amount'])) {
                    $errors[] = __('Minimum cart amount must be numeric', 'woo-offers');
                }
                break;
                
            case 'exit_intent':
                if (empty($data['trigger_sensitivity']) || 
                    !in_array($data['trigger_sensitivity'], ['low', 'medium', 'high'])) {
                    $errors[] = __('Invalid trigger sensitivity for exit intent campaign', 'woo-offers');
                }
                break;
        }
        
        // ✅ SECURITY: Sanitize all input data
        $sanitized_data = SecurityManager::sanitize_campaign_data($data);
        
        if (!empty($errors)) {
            return new \WP_Error('validation_failed', implode(', ', $errors));
        }
        
        return true;
    }
    
    /**
     * Get optimization recommendations for campaign type
     *
     * @param string $type Campaign type
     * @param array $performance_data Performance metrics
     * @return array
     */
    public static function get_optimization_recommendations($type, $performance_data = []) {
        $recommendations = [];
        $config = self::get_type_config($type);
        
        // Performance-based recommendations
        if (isset($performance_data['page_load_time']) && $performance_data['page_load_time'] > 3) {
            if ($config['performance']['lazy_load']) {
                $recommendations[] = [
                    'type' => 'performance',
                    'message' => __('Consider enabling lazy loading for better page performance', 'woo-offers'),
                    'action' => 'enable_lazy_load'
                ];
            }
        }
        
        // Type-specific recommendations
        switch ($type) {
            case 'checkout':
                if (isset($performance_data['conversion_rate']) && $performance_data['conversion_rate'] < 0.02) {
                    $recommendations[] = [
                        'type' => 'positioning',
                        'message' => __('Try moving the offer before payment fields for better visibility', 'woo-offers'),
                        'action' => 'change_position'
                    ];
                }
                break;
                
            case 'exit_intent':
                if (isset($performance_data['trigger_rate']) && $performance_data['trigger_rate'] > 0.8) {
                    $recommendations[] = [
                        'type' => 'trigger',
                        'message' => __('Trigger sensitivity might be too high, consider reducing it', 'woo-offers'),
                        'action' => 'adjust_sensitivity'
                    ];
                }
                break;
        }
        
        return $recommendations;
    }
    
    /**
     * Create default campaign instance
     *
     * @param string $type Campaign type
     * @param array $data Campaign data
     * @return object
     */
    private static function create_default_campaign($type, $data) {
        $campaign = new \stdClass();
        $campaign->type = $type;
        $campaign->config = self::get_type_config($type);
        $campaign->data = $data;
        $campaign->created_at = current_time('mysql');
        
        return $campaign;
    }
    
    /**
     * Get registered type handler
     *
     * @param string $type Campaign type
     * @return callable|null
     */
    private static function get_type_handler($type) {
        return self::$type_handlers[$type] ?? null;
    }
    
    /**
     * Validate campaign type
     *
     * @param string $type Campaign type
     * @return bool
     */
    private static function is_valid_campaign_type($type) {
        $valid_types = [
            'checkout', 'cart', 'product', 'exit_intent', 'post_purchase'
        ];
        
        return in_array($type, $valid_types);
    }
    
    /**
     * Clear factory caches
     */
    public static function clear_cache() {
        self::$instances = [];
    }
    
    /**
     * Get campaign type metrics
     *
     * @param string $type Campaign type
     * @return array
     */
    public static function get_type_metrics($type) {
        global $wpdb;
        
        $tables = \WooOffers\Core\DatabaseSchema::get_table_names();
        
        $metrics = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_campaigns,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_campaigns,
                AVG(priority) as avg_priority
            FROM {$tables['campaigns']} 
            WHERE type = %s",
            $type
        ), ARRAY_A);
        
        return $metrics ?: [];
    }
} 