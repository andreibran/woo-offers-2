<?php

namespace WooOffers\Offers\Types;

use WooOffers\Offers\AbstractEngine;

/**
 * BOGO (Buy One Get One) Engine
 * 
 * Handles Buy X Get Y free/discounted offers
 * 
 * @package WooOffers
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class BogoEngine extends AbstractEngine {
    
    /**
     * Get offer type identifier
     * 
     * @return string
     */
    public function get_type() {
        return 'bogo';
    }
    
    /**
     * Calculate BOGO discount
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
        $buy_quantity = $conditions['buy_quantity'] ?? 1;
        $get_quantity = $conditions['get_quantity'] ?? 1;
        $discount_value = $conditions['discount_value'] ?? 100; // Default 100% (free)
        $discount_type = $conditions['discount_type'] ?? 'percentage';
        $max_applications = $conditions['max_applications'] ?? 0; // 0 = unlimited
        
        $eligible_items = $this->get_eligible_items($cart_items, $offer);
        
        if (empty($eligible_items)) {
            return [
                'success' => false,
                'discount' => 0,
                'error' => 'No eligible items found'
            ];
        }
        
        // Sort items by price (descending) to give discount on most expensive
        usort($eligible_items, function($a, $b) {
            $price_a = ($a['line_total'] / $a['quantity']);
            $price_b = ($b['line_total'] / $b['quantity']);
            return $price_b <=> $price_a;
        });
        
        $total_eligible_quantity = 0;
        foreach ($eligible_items as $item) {
            $total_eligible_quantity += $item['quantity'];
        }
        
        // Check if minimum buy quantity is met
        if ($total_eligible_quantity < $buy_quantity) {
            return [
                'success' => false,
                'discount' => 0,
                'error' => 'Minimum buy quantity not met'
            ];
        }
        
        // Calculate how many BOGO sets can be applied
        $total_sets = floor($total_eligible_quantity / ($buy_quantity + $get_quantity));
        
        if ($max_applications > 0) {
            $total_sets = min($total_sets, $max_applications);
        }
        
        if ($total_sets <= 0) {
            return [
                'success' => false,
                'discount' => 0,
                'error' => 'Not enough items for BOGO set'
            ];
        }
        
        // Calculate discount on the "get" items
        $discount_amount = 0;
        $discounted_quantity = $total_sets * $get_quantity;
        $processed_quantity = 0;
        
        foreach ($eligible_items as $item) {
            if ($processed_quantity >= $discounted_quantity) {
                break;
            }
            
            $item_price = $item['line_total'] / $item['quantity'];
            $quantity_to_discount = min(
                $item['quantity'],
                $discounted_quantity - $processed_quantity
            );
            
            if ($discount_type === 'percentage') {
                $item_discount = ($item_price * $quantity_to_discount * $discount_value) / 100;
            } else {
                // Fixed discount per item
                $item_discount = $discount_value * $quantity_to_discount;
            }
            
            $discount_amount += $item_discount;
            $processed_quantity += $quantity_to_discount;
        }
        
        return $this->format_result($discount_amount, [
            'buy_quantity' => $buy_quantity,
            'get_quantity' => $get_quantity,
            'total_sets' => $total_sets,
            'discounted_quantity' => $discounted_quantity,
            'eligible_quantity' => $total_eligible_quantity,
            'discount_type' => $discount_type,
            'discount_value' => $discount_value,
            'eligible_items' => count($eligible_items)
        ]);
    }
    
    /**
     * Additional validation for BOGO offers
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
        $buy_quantity = $conditions['buy_quantity'] ?? 1;
        $get_quantity = $conditions['get_quantity'] ?? 1;
        
        if ($buy_quantity <= 0 || $get_quantity <= 0) {
            return false;
        }
        
        $eligible_items = $this->get_eligible_items($cart_items, $offer);
        $total_eligible_quantity = 0;
        
        foreach ($eligible_items as $item) {
            $total_eligible_quantity += $item['quantity'];
        }
        
        // Need at least buy_quantity + get_quantity items for one BOGO set
        return $total_eligible_quantity >= ($buy_quantity + $get_quantity);
    }
} 