<?php

namespace WooOffers\Offers\Types;

use WooOffers\Offers\AbstractEngine;

/**
 * Percentage Discount Engine
 * 
 * Handles simple percentage discounts on cart or eligible items
 * 
 * @package WooOffers
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class PercentageDiscountEngine extends AbstractEngine {
    
    /**
     * Get offer type identifier
     * 
     * @return string
     */
    public function get_type() {
        return 'percentage_discount';
    }
    
    /**
     * Calculate percentage discount
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
        $discount_percentage = $conditions['discount_value'] ?? 0;
        $max_discount = $conditions['max_discount'] ?? 0;
        
        if ($discount_percentage <= 0 || $discount_percentage > 100) {
            return [
                'success' => false,
                'discount' => 0,
                'error' => 'Invalid discount percentage'
            ];
        }
        
        $eligible_items = $this->get_eligible_items($cart_items, $offer);
        
        if (empty($eligible_items)) {
            return [
                'success' => false,
                'discount' => 0,
                'error' => 'No eligible items found'
            ];
        }
        
        // Calculate eligible total
        $eligible_total = 0;
        foreach ($eligible_items as $item) {
            $eligible_total += $item['line_total'];
        }
        
        // Calculate discount amount
        $discount_amount = ($eligible_total * $discount_percentage) / 100;
        
        // Apply maximum discount limit
        if ($max_discount > 0 && $discount_amount > $max_discount) {
            $discount_amount = $max_discount;
        }
        
        // Don't exceed the eligible total
        $discount_amount = min($discount_amount, $eligible_total);
        
        return $this->format_result($discount_amount, [
            'eligible_total' => $eligible_total,
            'discount_percentage' => $discount_percentage,
            'max_discount' => $max_discount,
            'eligible_items' => count($eligible_items)
        ]);
    }
} 