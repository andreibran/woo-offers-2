<?php

namespace WooOffers\Offers;

/**
 * Abstract base class for discount calculation engines
 * 
 * @package WooOffers
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

abstract class AbstractEngine implements EngineInterface {
    
    /**
     * Calculate discount for cart items
     * 
     * @param array $cart_items WooCommerce cart items
     * @param array $offer Offer configuration
     * @return array Calculation result
     */
    abstract public function calculate($cart_items, $offer);
    
    /**
     * Get offer type identifier
     * 
     * @return string
     */
    abstract public function get_type();
    
    /**
     * Validate if offer can be applied to cart items
     * 
     * @param array $cart_items
     * @param array $offer
     * @return bool
     */
    public function can_apply($cart_items, $offer) {
        // Check minimum/maximum cart value requirements
        if (!$this->check_cart_value_requirements($cart_items, $offer)) {
            return false;
        }
        
        // Check product quantity requirements
        if (!$this->check_quantity_requirements($cart_items, $offer)) {
            return false;
        }
        
        // Check user restrictions
        if (!$this->check_user_restrictions($offer)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if cart meets minimum/maximum value requirements
     * 
     * @param array $cart_items
     * @param array $offer
     * @return bool
     */
    protected function check_cart_value_requirements($cart_items, $offer) {
        $conditions = $offer['conditions'] ?? [];
        $cart_total = $this->calculate_cart_total($cart_items);
        
        // Check minimum order value
        if (isset($conditions['min_order_value']) && $conditions['min_order_value'] > 0) {
            if ($cart_total < $conditions['min_order_value']) {
                return false;
            }
        }
        
        // Check maximum order value
        if (isset($conditions['max_order_value']) && $conditions['max_order_value'] > 0) {
            if ($cart_total > $conditions['max_order_value']) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if cart meets quantity requirements
     * 
     * @param array $cart_items
     * @param array $offer
     * @return bool
     */
    protected function check_quantity_requirements($cart_items, $offer) {
        $conditions = $offer['conditions'] ?? [];
        $total_quantity = $this->calculate_total_quantity($cart_items);
        
        // Check minimum quantity
        if (isset($conditions['min_quantity']) && $conditions['min_quantity'] > 0) {
            if ($total_quantity < $conditions['min_quantity']) {
                return false;
            }
        }
        
        // Check maximum quantity
        if (isset($conditions['max_quantity']) && $conditions['max_quantity'] > 0) {
            if ($total_quantity > $conditions['max_quantity']) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check user restrictions
     * 
     * @param array $offer
     * @return bool
     */
    protected function check_user_restrictions($offer) {
        $conditions = $offer['conditions'] ?? [];
        
        // Check if user is logged in (if required)
        if (isset($conditions['logged_in_required']) && $conditions['logged_in_required']) {
            if (!is_user_logged_in()) {
                return false;
            }
        }
        
        // Check user roles
        if (isset($conditions['allowed_user_roles']) && !empty($conditions['allowed_user_roles'])) {
            if (!is_user_logged_in()) {
                return false;
            }
            
            $user = wp_get_current_user();
            $user_roles = $user->roles ?? [];
            
            if (empty(array_intersect($user_roles, $conditions['allowed_user_roles']))) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Calculate total cart value
     * 
     * @param array $cart_items
     * @return float
     */
    protected function calculate_cart_total($cart_items) {
        $total = 0;
        
        foreach ($cart_items as $item) {
            $total += ($item['line_total'] ?? 0) + ($item['line_tax'] ?? 0);
        }
        
        return $total;
    }
    
    /**
     * Calculate total cart quantity
     * 
     * @param array $cart_items
     * @return int
     */
    protected function calculate_total_quantity($cart_items) {
        $total = 0;
        
        foreach ($cart_items as $item) {
            $total += $item['quantity'] ?? 0;
        }
        
        return $total;
    }
    
    /**
     * Get eligible cart items based on targeting rules
     * 
     * @param array $cart_items
     * @param array $offer
     * @return array
     */
    protected function get_eligible_items($cart_items, $offer) {
        $targeting = $offer['targeting_rules'] ?? ['type' => 'all'];
        
        if ($targeting['type'] === 'all') {
            return $cart_items;
        }
        
        $include = $targeting['include'] ?? [];
        $exclude = $targeting['exclude'] ?? [];
        $eligible_items = [];
        
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'];
            $variation_id = $item['variation_id'] ?? 0;
            $item_id = $variation_id > 0 ? $variation_id : $product_id;
            
            // Skip excluded items
            if (!empty($exclude) && in_array($item_id, $exclude)) {
                continue;
            }
            
            // Include if specifically included or no specific inclusions
            if (empty($include) || in_array($item_id, $include)) {
                $eligible_items[] = $item;
            }
        }
        
        return $eligible_items;
    }
    
    /**
     * Format calculation result
     * 
     * @param float $discount_amount
     * @param array $metadata
     * @return array
     */
    protected function format_result($discount_amount, $metadata = []) {
        return [
            'success' => true,
            'discount' => max(0, $discount_amount),
            'type' => $this->get_type(),
            'metadata' => $metadata
        ];
    }
    
    /**
     * Get product data by ID
     * 
     * @param int $product_id
     * @return \WC_Product|null
     */
    protected function get_product($product_id) {
        return \wc_get_product($product_id);
    }
} 