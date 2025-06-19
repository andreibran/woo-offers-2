<?php

namespace WooOffers\Offers\Types;

use WooOffers\Offers\AbstractEngine;

/**
 * Quantity Discount Engine
 * 
 * Handles quantity-based discounts (buy X get Y% off)
 * 
 * @package WooOffers
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class QuantityDiscountEngine extends AbstractEngine {
    
    /**
     * Get offer type identifier
     * 
     * @return string
     */
    public function get_type() {
        return 'quantity_discount';
    }
    
    /**
     * Calculate quantity-based discount
     * 
     * @param array $cart_items
     * @param array $offer
     * @return array
     */
    public function calculate($cart_items, $offer) {
        if (!$this->can_apply($cart_items, $offer)) {
            return [
                'success' => false,
                'discount' => 0,
                'error' => 'Offer cannot be applied'
            ];
        }
        
        $conditions = $offer['conditions'] ?? [];
        $required_quantity = $conditions['quantity'] ?? 1;
        $discount_value = $conditions['discount_value'] ?? 0;
        $discount_type = $conditions['discount_type'] ?? 'percentage'; // percentage or fixed
        $max_discount = $conditions['max_discount'] ?? 0;
        
        $eligible_items = $this->get_eligible_items($cart_items, $offer);
        
        if (empty($eligible_items)) {
            return [
                'success' => false,
                'discount' => 0,
                'error' => 'No eligible items found'
            ];
        }
        
        $total_eligible_quantity = 0;
        $eligible_total = 0;
        
        foreach ($eligible_items as $item) {
            $total_eligible_quantity += $item['quantity'];
            $eligible_total += $item['line_total'];
        }
        
        // Check if minimum quantity is met
        if ($total_eligible_quantity < $required_quantity) {
            return [
                'success' => false,
                'discount' => 0,
                'error' => 'Minimum quantity not met'
            ];
        }
        
        // Calculate discount amount
        $discount_amount = 0;
        
        if ($discount_type === 'percentage') {
            $discount_amount = ($eligible_total * $discount_value) / 100;
        } else {
            // Fixed discount
            $discount_amount = $discount_value;
        }
        
        // Apply maximum discount limit
        if ($max_discount > 0 && $discount_amount > $max_discount) {
            $discount_amount = $max_discount;
        }
        
        // Don't exceed the total eligible amount
        $discount_amount = min($discount_amount, $eligible_total);
        
        return $this->format_result($discount_amount, [
            'required_quantity' => $required_quantity,
            'eligible_quantity' => $total_eligible_quantity,
            'eligible_total' => $eligible_total,
            'discount_type' => $discount_type,
            'discount_value' => $discount_value,
            'eligible_items' => count($eligible_items)
        ]);
    }
    
    /**
     * Additional validation for quantity discounts
     * 
     * @param array $cart_items
     * @param array $offer
     * @return bool
     */
    public function can_apply($cart_items, $offer) {
        if (!parent::can_apply($cart_items, $offer)) {
            return false;
        }
        
        $conditions = $offer['conditions'] ?? [];
        $required_quantity = $conditions['quantity'] ?? 1;
        
        if ($required_quantity <= 0) {
            return false;
        }
        
        $eligible_items = $this->get_eligible_items($cart_items, $offer);
        $total_eligible_quantity = 0;
        
        foreach ($eligible_items as $item) {
            $total_eligible_quantity += $item['quantity'];
        }
        
        return $total_eligible_quantity >= $required_quantity;
    }
} 