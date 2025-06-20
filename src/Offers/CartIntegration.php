<?php

namespace WooOffers\Offers;

/**
 * Cart Integration for Discount Engine
 * 
 * Handles integration with WooCommerce cart and checkout
 * Enhanced with programmatic coupons and improved validation
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
     * Generated programmatic coupons
     * 
     * @var array
     */
    private static $programmatic_coupons = [];
    
    /**
     * Prefix for programmatic coupon codes
     * 
     * @var string
     */
    private static $coupon_prefix = 'woo_offers_';
    
    /**
     * Initialize cart integration
     */
    public static function init() {
        // Hook into cart calculation (higher priority to run early)
        add_action('woocommerce_cart_calculate_fees', [__CLASS__, 'apply_offer_discounts'], 5);
        
        // Hook for programmatic coupon data
        add_filter('woocommerce_get_shop_coupon_data', [__CLASS__, 'get_programmatic_coupon_data'], 10, 2);
        
        // Validate coupons before application
        add_filter('woocommerce_coupon_is_valid', [__CLASS__, 'validate_programmatic_coupon'], 10, 2);
        
        // Hook into checkout to log analytics
        add_action('woocommerce_checkout_order_processed', [__CLASS__, 'log_checkout_analytics'], 10, 2);
        
        // Clear applied offers when cart is updated
        add_action('woocommerce_cart_updated', [__CLASS__, 'clear_applied_offers']);
        
        // Clear applied offers when cart is emptied
        add_action('woocommerce_cart_emptied', [__CLASS__, 'clear_applied_offers']);
        
        // Remove programmatic coupons when they shouldn't apply
        add_action('woocommerce_applied_coupon', [__CLASS__, 'validate_applied_coupon']);
        
        // Add AJAX handlers for offer preview
        add_action('wp_ajax_woo_offers_preview', [__CLASS__, 'ajax_preview_offers']);
        add_action('wp_ajax_nopriv_woo_offers_preview', [__CLASS__, 'ajax_preview_offers']);
        
        // Auto-apply eligible offers
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'auto_apply_eligible_offers'], 10);
    }
    
    /**
     * Apply offer discounts to cart with enhanced validation
     */
    public static function apply_offer_discounts() {
        if (!self::is_cart_available()) {
            return;
        }
        
        // Prevent recursive calls
        if (doing_action('woocommerce_cart_calculate_fees')) {
            static $calculating = false;
            if ($calculating) {
                return;
            }
            $calculating = true;
        }
        
        // Get cart items in the format expected by engines
        $cart_items = self::get_cart_items_for_engine();
        
        if (empty($cart_items)) {
            return;
        }
        
        // Get applicable offers with enhanced validation
        $applicable_offers = DiscountEngine::get_applicable_offers($cart_items);
        
        if (empty($applicable_offers)) {
            return;
        }
        
        // Apply offers (highest priority first)
        foreach ($applicable_offers as $offer) {
            if (!self::can_apply_offer($offer)) {
                continue;
            }
            
            $calculation = $offer['calculated_discount'];
            
            if ($calculation['success'] && $calculation['discount'] > 0) {
                self::apply_offer_to_cart($offer, $calculation);
            }
        }
        
        if (isset($calculating)) {
            $calculating = false;
        }
    }
    
    /**
     * Enhanced validation before applying offers
     * 
     * @param array $offer
     * @return bool
     */
    private static function can_apply_offer($offer) {
        // Check if offer is already applied (prevent duplicates)
        if (self::is_offer_applied($offer['id'])) {
            return false;
        }
        
        // Check usage limits
        if (!self::validate_offer_usage_limits($offer)) {
            return false;
        }
        
        // Check user eligibility
        if (!self::validate_user_eligibility($offer)) {
            return false;
        }
        
        // Check cart conditions
        if (!self::validate_cart_conditions($offer)) {
            return false;
        }
        
        // Check compatibility with other coupons
        if (!self::validate_coupon_compatibility($offer)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Apply single offer to cart using programmatic coupons
     * 
     * @param array $offer
     * @param array $calculation
     */
    private static function apply_offer_to_cart($offer, $calculation) {
        $offer_type = $calculation['type'];
        $discount_amount = $calculation['discount'];
        
        // Generate unique coupon code
        $coupon_code = self::generate_coupon_code($offer['id']);
        
        // Create programmatic coupon data
        $coupon_data = self::create_coupon_data($offer, $calculation);
        
        // Store coupon data for programmatic retrieval
        self::$programmatic_coupons[$coupon_code] = $coupon_data;
        
        // Apply coupon to cart if not already applied
        if (!WC()->cart->has_discount($coupon_code)) {
            WC()->cart->apply_coupon($coupon_code);
        }
        
        // Store applied offer for analytics and duplicate prevention
        self::$applied_offers[] = [
            'offer_id' => $offer['id'],
            'coupon_code' => $coupon_code,
            'discount_amount' => $discount_amount,
            'calculation' => $calculation,
            'applied_at' => current_time('timestamp')
        ];
        
        // Log offer application
        DiscountEngine::log_offer_usage($offer['id'], $calculation, 'applied');
    }
    
    /**
     * Generate unique coupon code for offer
     * 
     * @param int $offer_id
     * @return string
     */
    private static function generate_coupon_code($offer_id) {
        return self::$coupon_prefix . $offer_id . '_' . wp_hash($offer_id . WC()->session->get_customer_id() . time());
    }
    
    /**
     * Create coupon data array for programmatic coupon
     * 
     * @param array $offer
     * @param array $calculation
     * @return array
     */
    private static function create_coupon_data($offer, $calculation) {
        $discount_type = 'fixed_cart';
        $amount = $calculation['discount'];
        
        // Determine discount type based on offer type
        switch ($calculation['type']) {
            case 'percentage':
                $discount_type = 'percent';
                $amount = $calculation['percentage'] ?? ($calculation['discount'] / WC()->cart->get_subtotal() * 100);
                break;
            case 'fixed':
                $discount_type = 'fixed_cart';
                break;
            case 'fixed_product':
                $discount_type = 'fixed_product';
                break;
            case 'free_shipping':
                $discount_type = 'fixed_cart';
                break;
        }
        
        $coupon_data = [
            'discount_type' => $discount_type,
            'amount' => $amount,
            'description' => $offer['description'] ?? '',
            'date_expires' => !empty($offer['end_date']) ? strtotime($offer['end_date']) : null,
            'individual_use' => false, // Allow stacking unless specified
            'product_ids' => $calculation['applicable_products'] ?? [],
            'exclude_product_ids' => [],
            'usage_limit' => $offer['usage_limit'] ?? null,
            'usage_limit_per_user' => $offer['usage_limit_per_user'] ?? null,
            'limit_usage_to_x_items' => $calculation['max_items'] ?? null,
            'free_shipping' => ($calculation['type'] === 'free_shipping'),
            'product_categories' => $calculation['categories'] ?? [],
            'exclude_product_categories' => [],
            'exclude_sale_items' => false,
            'minimum_amount' => $calculation['minimum_amount'] ?? '',
            'maximum_amount' => $calculation['maximum_amount'] ?? '',
            'email_restrictions' => [],
            'used_by' => []
        ];
        
        return apply_filters('woo_offers_programmatic_coupon_data', $coupon_data, $offer, $calculation);
    }
    
    /**
     * Filter hook to provide programmatic coupon data
     * 
     * @param array|false $coupon_data
     * @param string $coupon_code
     * @return array|false
     */
    public static function get_programmatic_coupon_data($coupon_data, $coupon_code) {
        // Check if this is one of our programmatic coupons
        if (strpos($coupon_code, self::$coupon_prefix) !== 0) {
            return $coupon_data;
        }
        
        // Return stored coupon data if available
        if (isset(self::$programmatic_coupons[$coupon_code])) {
            return self::$programmatic_coupons[$coupon_code];
        }
        
        return $coupon_data;
    }
    
    /**
     * Validate programmatic coupon before application
     * 
     * @param bool $is_valid
     * @param WC_Coupon $coupon
     * @return bool
     */
    public static function validate_programmatic_coupon($is_valid, $coupon) {
        $coupon_code = $coupon->get_code();
        
        // Only validate our programmatic coupons
        if (strpos($coupon_code, self::$coupon_prefix) !== 0) {
            return $is_valid;
        }
        
        // Additional validation for programmatic coupons
        if (!isset(self::$programmatic_coupons[$coupon_code])) {
            return false;
        }
        
        // Check if cart still meets conditions
        $cart_items = self::get_cart_items_for_engine();
        $applicable_offers = DiscountEngine::get_applicable_offers($cart_items);
        
        // Extract offer ID from coupon code
        $offer_id = self::extract_offer_id_from_coupon($coupon_code);
        
        // Check if this offer is still applicable
        foreach ($applicable_offers as $offer) {
            if ($offer['id'] == $offer_id) {
                return $is_valid;
            }
        }
        
        return false;
    }
    
    /**
     * Validate applied coupon and remove if no longer valid
     * 
     * @param string $coupon_code
     */
    public static function validate_applied_coupon($coupon_code) {
        // Only handle our programmatic coupons
        if (strpos($coupon_code, self::$coupon_prefix) !== 0) {
            return;
        }
        
        // Validate in next tick to avoid conflicts
        wp_schedule_single_event(time() + 1, 'woo_offers_validate_coupon', [$coupon_code]);
    }
    
    /**
     * Auto-apply eligible offers that should be automatically applied
     */
    public static function auto_apply_eligible_offers() {
        if (!self::is_cart_available()) {
            return;
        }
        
        // Only auto-apply on frontend
        if (is_admin() && !wp_doing_ajax()) {
            return;
        }
        
        // Get cart items
        $cart_items = self::get_cart_items_for_engine();
        
        if (empty($cart_items)) {
            return;
        }
        
        // Get offers that should auto-apply
        $auto_apply_offers = self::get_auto_apply_offers($cart_items);
        
        foreach ($auto_apply_offers as $offer) {
            if (!self::is_offer_applied($offer['id'])) {
                $calculation = $offer['calculated_discount'];
                if ($calculation['success'] && $calculation['discount'] > 0) {
                    self::apply_offer_to_cart($offer, $calculation);
                }
            }
        }
    }
    
    /**
     * Get offers that should be automatically applied
     * 
     * @param array $cart_items
     * @return array
     */
    private static function get_auto_apply_offers($cart_items) {
        $all_offers = DiscountEngine::get_applicable_offers($cart_items);
        $auto_apply_offers = [];
        
        foreach ($all_offers as $offer) {
            // Check if offer is configured for auto-application
            $auto_apply = $offer['auto_apply'] ?? true; // Default to auto-apply
            
            if ($auto_apply && self::can_apply_offer($offer)) {
                $auto_apply_offers[] = $offer;
            }
        }
        
        return $auto_apply_offers;
    }
    
    /**
     * Enhanced validation for usage limits
     * 
     * @param array $offer
     * @return bool
     */
    private static function validate_offer_usage_limits($offer) {
        global $wpdb;
        
        // Check global usage limit
        if (!empty($offer['usage_limit'])) {
            $table_name = $wpdb->prefix . 'woo_offers_usage';
            $usage_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE offer_id = %d AND status = 'converted'",
                $offer['id']
            ));
            
            if ($usage_count >= $offer['usage_limit']) {
                return false;
            }
        }
        
        // Check per-user usage limit
        if (!empty($offer['usage_limit_per_user'])) {
            $user_id = get_current_user_id();
            if ($user_id > 0) {
                $table_name = $wpdb->prefix . 'woo_offers_usage';
                $user_usage_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table_name} WHERE offer_id = %d AND user_id = %d AND status = 'converted'",
                    $offer['id'],
                    $user_id
                ));
                
                if ($user_usage_count >= $offer['usage_limit_per_user']) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Validate user eligibility for offer
     * 
     * @param array $offer
     * @return bool
     */
    private static function validate_user_eligibility($offer) {
        // Check if user restrictions exist
        if (empty($offer['user_restrictions'])) {
            return true;
        }
        
        $restrictions = $offer['user_restrictions'];
        $user = wp_get_current_user();
        
        // Check email restrictions
        if (!empty($restrictions['allowed_emails'])) {
            if (!in_array($user->user_email, $restrictions['allowed_emails'])) {
                return false;
            }
        }
        
        // Check role restrictions
        if (!empty($restrictions['allowed_roles'])) {
            $user_roles = $user->roles ?? [];
            if (empty(array_intersect($user_roles, $restrictions['allowed_roles']))) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Enhanced cart conditions validation
     * 
     * @param array $offer
     * @return bool
     */
    private static function validate_cart_conditions($offer) {
        $cart = WC()->cart;
        
        // Check minimum amount
        if (!empty($offer['minimum_amount'])) {
            if ($cart->get_subtotal() < floatval($offer['minimum_amount'])) {
                return false;
            }
        }
        
        // Check maximum amount
        if (!empty($offer['maximum_amount'])) {
            if ($cart->get_subtotal() > floatval($offer['maximum_amount'])) {
                return false;
            }
        }
        
        // Check minimum quantity
        if (!empty($offer['minimum_quantity'])) {
            if ($cart->get_cart_contents_count() < intval($offer['minimum_quantity'])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate compatibility with other applied coupons
     * 
     * @param array $offer
     * @return bool
     */
    private static function validate_coupon_compatibility($offer) {
        $applied_coupons = WC()->cart->get_applied_coupons();
        
        // Check if offer should be individual use
        if (!empty($offer['individual_use']) && !empty($applied_coupons)) {
            return false;
        }
        
        // Check for conflicts with existing coupons
        foreach ($applied_coupons as $coupon_code) {
            $coupon = new \WC_Coupon($coupon_code);
            
            // If existing coupon is individual use, don't allow stacking
            if ($coupon->get_individual_use()) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if cart is available and valid
     * 
     * @return bool
     */
    private static function is_cart_available() {
        return WC()->cart && !WC()->cart->is_empty() && !is_admin();
    }
    
    /**
     * Extract offer ID from programmatic coupon code
     * 
     * @param string $coupon_code
     * @return int|false
     */
    private static function extract_offer_id_from_coupon($coupon_code) {
        if (strpos($coupon_code, self::$coupon_prefix) !== 0) {
            return false;
        }
        
        $parts = explode('_', str_replace(self::$coupon_prefix, '', $coupon_code));
        return !empty($parts[0]) ? intval($parts[0]) : false;
    }
    
    /**
     * Get cart items formatted for discount engines
     * 
     * @return array
     */
    private static function get_cart_items_for_engine() {
        if (!self::is_cart_available()) {
            return [];
        }
        
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
        // Remove programmatic coupons from cart
        foreach (self::$applied_offers as $applied_offer) {
            if (!empty($applied_offer['coupon_code']) && WC()->cart) {
                WC()->cart->remove_coupon($applied_offer['coupon_code']);
            }
        }
        
        self::$applied_offers = [];
        self::$programmatic_coupons = [];
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
                    'final_discount' => $applied_offer['discount_amount'],
                    'coupon_code' => $applied_offer['coupon_code']
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
        
        if (!self::is_cart_available()) {
            wp_send_json_error(__('Cart is empty', 'woo-offers'));
        }
        
        $cart_items = self::get_cart_items_for_engine();
        $applicable_offers = DiscountEngine::get_applicable_offers($cart_items);
        
        $preview_data = [];
        
        foreach ($applicable_offers as $offer) {
            if (!self::can_apply_offer($offer)) {
                continue;
            }
            
            $calculation = $offer['calculated_discount'];
            
            $preview_data[] = [
                'id' => $offer['id'],
                'title' => $offer['title'],
                'description' => $offer['description'],
                'discount_amount' => $calculation['discount'],
                'discount_formatted' => DiscountEngine::format_discount($calculation['discount']),
                'type' => $calculation['type'],
                'metadata' => $calculation['metadata'],
                'can_apply' => true
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
    
    /**
     * Manually apply an offer (for AJAX/frontend interactions)
     * 
     * @param int $offer_id
     * @return bool|WP_Error
     */
    public static function manually_apply_offer($offer_id) {
        if (!self::is_cart_available()) {
            return new \WP_Error('cart_unavailable', __('Cart is not available', 'woo-offers'));
        }
        
        if (self::is_offer_applied($offer_id)) {
            return new \WP_Error('already_applied', __('Offer is already applied', 'woo-offers'));
        }
        
        $cart_items = self::get_cart_items_for_engine();
        $applicable_offers = DiscountEngine::get_applicable_offers($cart_items);
        
        foreach ($applicable_offers as $offer) {
            if ($offer['id'] == $offer_id && self::can_apply_offer($offer)) {
                $calculation = $offer['calculated_discount'];
                if ($calculation['success'] && $calculation['discount'] > 0) {
                    self::apply_offer_to_cart($offer, $calculation);
                    return true;
                }
            }
        }
        
        return new \WP_Error('not_applicable', __('Offer is not applicable to current cart', 'woo-offers'));
    }
    
    /**
     * Remove a specific applied offer
     * 
     * @param int $offer_id
     * @return bool
     */
    public static function remove_applied_offer($offer_id) {
        foreach (self::$applied_offers as $key => $applied_offer) {
            if ($applied_offer['offer_id'] == $offer_id) {
                // Remove coupon from cart
                if (!empty($applied_offer['coupon_code']) && WC()->cart) {
                    WC()->cart->remove_coupon($applied_offer['coupon_code']);
                }
                
                // Remove from applied offers
                unset(self::$applied_offers[$key]);
                
                // Remove from programmatic coupons
                if (!empty($applied_offer['coupon_code'])) {
                    unset(self::$programmatic_coupons[$applied_offer['coupon_code']]);
                }
                
                return true;
            }
        }
        
        return false;
    }
} 