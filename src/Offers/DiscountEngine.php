<?php

namespace WooOffers\Offers;

/**
 * Core Discount Calculation Engine
 * 
 * @package WooOffers
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class DiscountEngine {
    
    /**
     * Available offer types and their engines
     */
    private static $engines = [
        'quantity_discount' => 'WooOffers\\Offers\\Types\\QuantityDiscountEngine',
        'bogo' => 'WooOffers\\Offers\\Types\\BogoEngine', 
        'product_bundle' => 'WooOffers\\Offers\\Types\\BundleEngine',
        'free_shipping' => 'WooOffers\\Offers\\Types\\FreeShippingEngine',
        'percentage_discount' => 'WooOffers\\Offers\\Types\\PercentageDiscountEngine',
        'fixed_discount' => 'WooOffers\\Offers\\Types\\FixedDiscountEngine'
    ];
    
    /**
     * Calculate discount for cart
     * 
     * @param array $cart_items WooCommerce cart items
     * @param array $offer Offer configuration
     * @return array Discount calculation result
     */
    public static function calculate_discount($cart_items, $offer) {
        $offer_type = $offer['type'] ?? null;
        
        if (!$offer_type || !isset(self::$engines[$offer_type])) {
            return [
                'success' => false,
                'error' => 'Invalid offer type',
                'discount' => 0
            ];
        }
        
        $engine_class = self::$engines[$offer_type];
        
        if (!class_exists($engine_class)) {
            return [
                'success' => false,
                'error' => 'Engine class not found',
                'discount' => 0
            ];
        }
        
        $engine = new $engine_class();
        return $engine->calculate($cart_items, $offer);
    }
    
    /**
     * Validate offer can be applied
     * 
     * @param array $cart_items
     * @param array $offer
     * @return bool
     */
    public static function can_apply_offer($cart_items, $offer) {
        // Check if offer is active
        if ($offer['status'] !== 'active') {
            return false;
        }
        
        // Check date range
        $now = current_time('timestamp');
        if (!empty($offer['start_date']) && strtotime($offer['start_date']) > $now) {
            return false;
        }
        
        if (!empty($offer['end_date']) && strtotime($offer['end_date']) < $now) {
            return false;
        }
        
        // Check targeting rules
        if (!self::check_targeting_rules($cart_items, $offer)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check targeting rules
     * 
     * @param array $cart_items
     * @param array $offer
     * @return bool
     */
    private static function check_targeting_rules($cart_items, $offer) {
        $targeting = $offer['targeting_rules'] ?? ['type' => 'all'];
        
        if ($targeting['type'] === 'all') {
            return true;
        }
        
        $include = $targeting['include'] ?? [];
        $exclude = $targeting['exclude'] ?? [];
        
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'];
            $variation_id = $item['variation_id'] ?? 0;
            $item_id = $variation_id > 0 ? $variation_id : $product_id;
            
            // Check exclusions first
            if (!empty($exclude) && in_array($item_id, $exclude)) {
                continue; // Skip excluded items
            }
            
            // Check inclusions
            if (!empty($include)) {
                if (in_array($item_id, $include)) {
                    return true; // Found included item
                }
            } else {
                return true; // No specific inclusions, apply to all
            }
        }
        
        return empty($include); // Return true if no inclusions specified
    }
    
    /**
     * Get applicable offers for cart
     * 
     * @param array $cart_items
     * @return array
     */
    public static function get_applicable_offers($cart_items) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'woo_offers';
        $offers = $wpdb->get_results(
            "SELECT * FROM {$table} WHERE status = 'active' ORDER BY priority ASC, id ASC",
            ARRAY_A
        );
        
        $applicable_offers = [];
        
        foreach ($offers as $offer) {
            // Parse JSON fields
            $offer['conditions'] = json_decode($offer['conditions'] ?? '{}', true);
            $offer['targeting_rules'] = json_decode($offer['targeting_rules'] ?? '{}', true);
            
            if (self::can_apply_offer($cart_items, $offer)) {
                $calculation = self::calculate_discount($cart_items, $offer);
                if ($calculation['success'] && $calculation['discount'] > 0) {
                    $offer['calculated_discount'] = $calculation;
                    $applicable_offers[] = $offer;
                }
            }
        }
        
        return $applicable_offers;
    }
    
    /**
     * Format discount amount for display
     * 
     * @param float $amount
     * @return string
     */
    public static function format_discount($amount) {
        return wc_price($amount);
    }
    
    /**
     * Log offer usage for analytics
     * 
     * @param int $offer_id
     * @param array $calculation_result
     * @param string $event_type
     */
    public static function log_offer_usage($offer_id, $calculation_result, $event_type = 'applied') {
        global $wpdb;
        
        $analytics_table = $wpdb->prefix . 'woo_offers_analytics';
        
        $wpdb->insert(
            $analytics_table,
            [
                'offer_id' => $offer_id,
                'event_type' => $event_type,
                'user_id' => get_current_user_id(),
                'session_id' => WC()->session->get_customer_id(),
                'revenue' => $calculation_result['discount'] ?? 0,
                'metadata' => json_encode($calculation_result),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => current_time('mysql')
            ]
        );
    }
}
