<?php

namespace WooOffers\Offers;

/**
 * Cart Integration for Discount Engine
 * 
 * Handles integration with WooCommerce cart and checkout
 * 
 * @package WooOffers
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class CartIntegration {
    
    /**
     * Applied offers in current session
     * 
     * @var array
     */
    private static $applied_offers = [];
    
    /**
     * Initialize cart integration
     */
    public static function init() {
        // Hook into cart calculation
        add_action('woocommerce_cart_calculate_fees', [__CLASS__, 'apply_offer_discounts']);
        
        // Hook into checkout to log analytics
        add_action('woocommerce_checkout_order_processed', [__CLASS__, 'log_checkout_analytics'], 10, 2);
        
        // Clear applied offers when cart is updated
        add_action('woocommerce_cart_updated', [__CLASS__, 'clear_applied_offers']);
        
        // Add AJAX handlers for offer preview
        add_action('wp_ajax_woo_offers_preview', [__CLASS__, 'ajax_preview_offers']);
        add_action('wp_ajax_nopriv_woo_offers_preview', [__CLASS__, 'ajax_preview_offers']);
    }
    
    /**
     * Apply offer discounts to cart
     */
    public static function apply_offer_discounts() {
        if (!WC()->cart || WC()->cart->is_empty()) {
            return;
        }
        
        // Get cart items in the format expected by engines
        $cart_items = self::get_cart_items_for_engine();
        
        // Get applicable offers
        $applicable_offers = DiscountEngine::get_applicable_offers($cart_items);
        
        if (empty($applicable_offers)) {
            return;
        }
        
        // Apply offers (highest priority first)
        foreach ($applicable_offers as $offer) {
            $calculation = $offer['calculated_discount'];
            
            if ($calculation['success'] && $calculation['discount'] > 0) {
                self::apply_offer_to_cart($offer, $calculation);
            }
        }
    }
    
    /**
     * Apply single offer to cart
     * 
     * @param array $offer
     * @param array $calculation
     */
    private static function apply_offer_to_cart($offer, $calculation) {
        $discount_amount = $calculation['discount'];
        $offer_type = $calculation['type'];
        
        // Create fee label
        $fee_label = sprintf(
            __('Discount: %s', 'woo-offers'),
            $offer['title'] ?? __('Special Offer', 'woo-offers')
        );
        
        // Handle different types of discounts
        if ($offer_type === 'free_shipping') {
            // For free shipping, we'll add a negative shipping fee
            WC()->cart->add_fee($fee_label, -$discount_amount);
        } else {
            // Regular discount
            WC()->cart->add_fee($fee_label, -$discount_amount);
        }
        
        // Store applied offer for analytics
        self::$applied_offers[] = [
            'offer_id' => $offer['id'],
            'discount_amount' => $discount_amount,
            'calculation' => $calculation
        ];
        
        // Log offer application
        DiscountEngine::log_offer_usage($offer['id'], $calculation, 'applied');
    }
    
    /**
     * Get cart items formatted for discount engines
     * 
     * @return array
     */
    private static function get_cart_items_for_engine() {
        $cart_items = [];
        
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $cart_items[] = [
                'cart_item_key' => $cart_item_key,
                'product_id' => $cart_item['product_id'],
                'variation_id' => $cart_item['variation_id'] ?? 0,
                'quantity' => $cart_item['quantity'],
                'line_total' => $cart_item['line_total'],
                'line_tax' => $cart_item['line_tax'],
                'data' => $cart_item['data'] // WC_Product object
            ];
        }
        
        return $cart_items;
    }
    
    /**
     * Clear applied offers when cart is updated
     */
    public static function clear_applied_offers() {
        self::$applied_offers = [];
    }
    
    /**
     * Log analytics when checkout is processed
     * 
     * @param int $order_id
     * @param array $posted_data
     */
    public static function log_checkout_analytics($order_id, $posted_data) {
        if (empty(self::$applied_offers)) {
            return;
        }
        
        foreach (self::$applied_offers as $applied_offer) {
            DiscountEngine::log_offer_usage(
                $applied_offer['offer_id'],
                array_merge($applied_offer['calculation'], [
                    'order_id' => $order_id,
                    'final_discount' => $applied_offer['discount_amount']
                ]),
                'converted'
            );
        }
    }
    
    /**
     * AJAX handler for offer preview
     */
    public static function ajax_preview_offers() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'woo_offers_preview')) {
            wp_die(__('Security check failed', 'woo-offers'));
        }
        
        if (!WC()->cart || WC()->cart->is_empty()) {
            wp_send_json_error(__('Cart is empty', 'woo-offers'));
        }
        
        $cart_items = self::get_cart_items_for_engine();
        $applicable_offers = DiscountEngine::get_applicable_offers($cart_items);
        
        $preview_data = [];
        
        foreach ($applicable_offers as $offer) {
            $calculation = $offer['calculated_discount'];
            
            $preview_data[] = [
                'id' => $offer['id'],
                'title' => $offer['title'],
                'description' => $offer['description'],
                'discount_amount' => $calculation['discount'],
                'discount_formatted' => DiscountEngine::format_discount($calculation['discount']),
                'type' => $calculation['type'],
                'metadata' => $calculation['metadata']
            ];
        }
        
        wp_send_json_success($preview_data);
    }
    
    /**
     * Get total discount amount for display
     * 
     * @return float
     */
    public static function get_total_discount() {
        $total = 0;
        
        foreach (self::$applied_offers as $applied_offer) {
            $total += $applied_offer['discount_amount'];
        }
        
        return $total;
    }
    
    /**
     * Get applied offers for current session
     * 
     * @return array
     */
    public static function get_applied_offers() {
        return self::$applied_offers;
    }
    
    /**
     * Check if specific offer is already applied
     * 
     * @param int $offer_id
     * @return bool
     */
    public static function is_offer_applied($offer_id) {
        foreach (self::$applied_offers as $applied_offer) {
            if ($applied_offer['offer_id'] == $offer_id) {
                return true;
            }
        }
        
        return false;
    }
} 