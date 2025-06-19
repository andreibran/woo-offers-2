<?php

namespace WooOffers\Offers;

/**
 * Interface for all discount calculation engines
 * 
 * @package WooOffers
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

interface EngineInterface {
    
    /**
     * Calculate discount for cart items
     * 
     * @param array $cart_items WooCommerce cart items
     * @param array $offer Offer configuration
     * @return array Calculation result with success, discount amount, and metadata
     */
    public function calculate($cart_items, $offer);
    
    /**
     * Validate if offer can be applied to given cart items
     * 
     * @param array $cart_items WooCommerce cart items
     * @param array $offer Offer configuration
     * @return bool
     */
    public function can_apply($cart_items, $offer);
    
    /**
     * Get offer type identifier
     * 
     * @return string
     */
    public function get_type();
} 