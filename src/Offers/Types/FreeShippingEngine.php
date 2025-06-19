<?php

namespace WooOffers\Offers\Types;

use WooOffers\Offers\AbstractEngine;

/**
 * Free Shipping Engine
 * 
 * Handles free shipping offers based on cart conditions
 * 
 * @package WooOffers
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class FreeShippingEngine extends AbstractEngine {
    
    /**
     * Get offer type identifier
     * 
     * @return string
     */
    public function get_type() {
        return 'free_shipping';
    }
    
    /**
     * Calculate free shipping discount
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
        
        // For free shipping, we need to get the current shipping cost
        $shipping_cost = $this->get_current_shipping_cost();
        
        if ($shipping_cost <= 0) {
            return [
                'success' => false,
                'discount' => 0,
                'error' => 'No shipping cost to discount'
            ];
        }
        
        $conditions = $offer['conditions'] ?? [];
        $discount_type = $conditions['shipping_discount_type'] ?? 'free'; // 'free' or 'percentage'
        $discount_value = $conditions['discount_value'] ?? 100;
        
        $discount_amount = 0;
        
        if ($discount_type === 'free') {
            // Full free shipping
            $discount_amount = $shipping_cost;
        } else {
            // Percentage discount on shipping
            $discount_amount = ($shipping_cost * $discount_value) / 100;
        }
        
        return $this->format_result($discount_amount, [
            'shipping_cost' => $shipping_cost,
            'discount_type' => $discount_type,
            'discount_value' => $discount_value,
            'is_free_shipping' => $discount_type === 'free'
        ]);
    }
    
    /**
     * Additional validation for free shipping offers
     * 
     * @param array $cart_items
     * @param array $offer
     * @return bool
     */
    public function can_apply($cart_items, $offer) {
        if (!parent::can_apply($cart_items, $offer)) {
            return false;
        }
        
        // Check if shipping is needed
        if (!WC()->cart || !WC()->cart->needs_shipping()) {
            return false;
        }
        
        // Check if there are shippable products in eligible items
        $eligible_items = $this->get_eligible_items($cart_items, $offer);
        
        foreach ($eligible_items as $item) {
            $product = $this->get_product($item['product_id']);
            if ($product && $product->needs_shipping()) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get current shipping cost from WooCommerce
     * 
     * @return float
     */
    private function get_current_shipping_cost() {
        if (!WC()->cart || !WC()->cart->needs_shipping()) {
            return 0;
        }
        
        // Get shipping packages
        $packages = WC()->shipping->get_packages();
        $shipping_total = 0;
        
        foreach ($packages as $package) {
            if (isset($package['rates'])) {
                foreach ($package['rates'] as $rate) {
                    if (WC()->session->get('chosen_shipping_methods')[0] === $rate->get_id()) {
                        $shipping_total += $rate->get_cost();
                        break;
                    }
                }
            }
        }
        
        return $shipping_total;
    }
} 