<?php

namespace WooOffers\Offers\Types;

use WooOffers\Offers\AbstractEngine;

/**
 * Fixed Discount Engine
 * 
 * Handles fixed amount discounts on cart or eligible items
 * 
 * @package WooOffers
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class FixedDiscountEngine extends AbstractEngine {
    
    /**
     * Get offer type identifier
     * 
     * @return string
     */
    public function get_type() {
        return 'fixed_discount';
    }
    
    /**
     * Calculate fixed discount
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
        $discount_amount = $conditions['discount_value'] ?? 0;
        
        if ($discount_amount <= 0) {
            return [
                'success' => false,
                'discount' => 0,
                'error' => 'Invalid discount amount'
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
        
        // Don't exceed the eligible total
        $final_discount = min($discount_amount, $eligible_total);
        
        return $this->format_result($final_discount, [
            'eligible_total' => $eligible_total,
            'requested_discount' => $discount_amount,
            'eligible_items' => count($eligible_items)
        ]);
    }
} 