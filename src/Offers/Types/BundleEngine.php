<?php

namespace WooOffers\Offers\Types;

use WooOffers\Offers\AbstractEngine;

/**
 * Product Bundle Engine
 * 
 * Handles product bundle discounts (buy specific products together for discount)
 * 
 * @package WooOffers
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class BundleEngine extends AbstractEngine {
    
    /**
     * Get offer type identifier
     * 
     * @return string
     */
    public function get_type() {
        return 'product_bundle';
    }
    
    /**
     * Calculate bundle discount
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
        $bundle_products = $conditions['bundle_products'] ?? [];
        $discount_value = $conditions['discount_value'] ?? 0;
        $discount_type = $conditions['discount_type'] ?? 'percentage';
        $max_applications = $conditions['max_applications'] ?? 1;
        
        if (empty($bundle_products)) {
            return [
                'success' => false,
                'discount' => 0,
                'error' => 'No bundle products defined'
            ];
        }
        
        // Find how many complete bundle sets are in the cart
        $bundle_sets = $this->find_bundle_sets($cart_items, $bundle_products, $max_applications);
        
        if ($bundle_sets <= 0) {
            return [
                'success' => false,
                'discount' => 0,
                'error' => 'Bundle requirements not met'
            ];
        }
        
        // Calculate bundle total value
        $bundle_total = 0;
        $bundled_items = [];
        
        foreach ($bundle_products as $bundle_product) {
            $product_id = $bundle_product['product_id'];
            $required_quantity = $bundle_product['quantity'] ?? 1;
            
            foreach ($cart_items as $item) {
                $item_id = $item['variation_id'] > 0 ? $item['variation_id'] : $item['product_id'];
                
                if ($item_id == $product_id) {
                    $item_price = $item['line_total'] / $item['quantity'];
                    $quantity_for_bundles = min($item['quantity'], $required_quantity * $bundle_sets);
                    
                    $bundle_total += $item_price * $quantity_for_bundles;
                    $bundled_items[] = [
                        'product_id' => $product_id,
                        'quantity' => $quantity_for_bundles,
                        'item_total' => $item_price * $quantity_for_bundles
                    ];
                    break;
                }
            }
        }
        
        // Calculate discount amount
        $discount_amount = 0;
        
        if ($discount_type === 'percentage') {
            $discount_amount = ($bundle_total * $discount_value) / 100;
        } else {
            // Fixed discount per bundle set
            $discount_amount = $discount_value * $bundle_sets;
        }
        
        // Don't exceed the bundle total
        $discount_amount = min($discount_amount, $bundle_total);
        
        return $this->format_result($discount_amount, [
            'bundle_sets' => $bundle_sets,
            'bundle_total' => $bundle_total,
            'bundled_items' => $bundled_items,
            'discount_type' => $discount_type,
            'discount_value' => $discount_value,
            'required_products' => count($bundle_products)
        ]);
    }
    
    /**
     * Additional validation for bundle offers
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
        $bundle_products = $conditions['bundle_products'] ?? [];
        
        if (empty($bundle_products)) {
            return false;
        }
        
        return $this->find_bundle_sets($cart_items, $bundle_products, 1) >= 1;
    }
    
    /**
     * Find how many complete bundle sets are available in the cart
     * 
     * @param array $cart_items
     * @param array $bundle_products
     * @param int $max_applications
     * @return int
     */
    private function find_bundle_sets($cart_items, $bundle_products, $max_applications) {
        $available_quantities = [];
        
        // Check availability of each required product
        foreach ($bundle_products as $bundle_product) {
            $product_id = $bundle_product['product_id'];
            $required_quantity = $bundle_product['quantity'] ?? 1;
            $found_quantity = 0;
            
            foreach ($cart_items as $item) {
                $item_id = $item['variation_id'] > 0 ? $item['variation_id'] : $item['product_id'];
                
                if ($item_id == $product_id) {
                    $found_quantity = $item['quantity'];
                    break;
                }
            }
            
            if ($found_quantity < $required_quantity) {
                return 0; // Missing required product
            }
            
            // How many bundle sets this product allows
            $available_quantities[] = floor($found_quantity / $required_quantity);
        }
        
        // The number of complete bundles is limited by the least available product
        $bundle_sets = min($available_quantities);
        
        // Apply maximum applications limit
        if ($max_applications > 0) {
            $bundle_sets = min($bundle_sets, $max_applications);
        }
        
        return $bundle_sets;
    }
} 