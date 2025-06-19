<?php

namespace WooOffers\Offers;

/**
 * Offer Scheduler
 * 
 * Handles automatic scheduling and status management of offers
 * 
 * @package WooOffers
 * @since 2.0.0
 */

defined('ABSPATH') || exit;

class OfferScheduler {
    
    /**
     * WP-Cron hook name for offer status updates
     */
    const CRON_HOOK = 'woo_offers_update_status';
    
    /**
     * Initialize scheduler
     */
    public static function init() {
        // Register WP-Cron hook
        add_action(self::CRON_HOOK, [__CLASS__, 'update_offer_statuses']);
        
        // Schedule recurring event if not already scheduled
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), 'hourly', self::CRON_HOOK);
        }
        
        // Hook into offer save to schedule status changes
        add_action('woo_offers_offer_saved', [__CLASS__, 'schedule_offer_status_changes']);
        
        // Admin actions
        add_action('wp_ajax_woo_offers_manual_status_update', [__CLASS__, 'ajax_manual_status_update']);
    }
    
    /**
     * Update offer statuses based on scheduled dates
     */
    public static function update_offer_statuses() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'woo_offers';
        $current_time = current_time('mysql');
        
        // Activate scheduled offers that should start now
        $wpdb->query($wpdb->prepare(
            "UPDATE {$table} 
             SET status = 'active', updated_at = %s 
             WHERE status = 'scheduled' 
             AND start_date <= %s 
             AND (end_date IS NULL OR end_date > %s)",
            $current_time,
            $current_time,
            $current_time
        ));
        
        // Expire active offers that have ended
        $wpdb->query($wpdb->prepare(
            "UPDATE {$table} 
             SET status = 'expired', updated_at = %s 
             WHERE status = 'active' 
             AND end_date IS NOT NULL 
             AND end_date <= %s",
            $current_time,
            $current_time
        ));
        
        // Log status changes
        self::log_status_changes();
        
        // Clear any relevant caches
        self::clear_offer_caches();
    }
    
    /**
     * Schedule offer status changes for specific offer
     * 
     * @param int $offer_id
     */
    public static function schedule_offer_status_changes($offer_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'woo_offers';
        $offer = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $offer_id
        ), ARRAY_A);
        
        if (!$offer) {
            return;
        }
        
        $current_time = current_time('timestamp');
        $start_time = !empty($offer['start_date']) ? strtotime($offer['start_date']) : null;
        $end_time = !empty($offer['end_date']) ? strtotime($offer['end_date']) : null;
        
        // Determine correct status based on dates
        $new_status = self::calculate_offer_status($offer, $current_time);
        
        if ($new_status !== $offer['status']) {
            self::update_offer_status($offer_id, $new_status);
        }
        
        // Schedule future status changes
        self::schedule_future_status_changes($offer_id, $start_time, $end_time);
    }
    
    /**
     * Calculate what status an offer should have based on current time
     * 
     * @param array $offer
     * @param int $current_time
     * @return string
     */
    private static function calculate_offer_status($offer, $current_time) {
        $start_time = !empty($offer['start_date']) ? strtotime($offer['start_date']) : null;
        $end_time = !empty($offer['end_date']) ? strtotime($offer['end_date']) : null;
        
        // If manually set to inactive, respect that
        if ($offer['status'] === 'inactive') {
            return 'inactive';
        }
        
        // Check if expired
        if ($end_time && $current_time > $end_time) {
            return 'expired';
        }
        
        // Check if not yet started
        if ($start_time && $current_time < $start_time) {
            return 'scheduled';
        }
        
        // Should be active
        return 'active';
    }
    
    /**
     * Update offer status in database
     * 
     * @param int $offer_id
     * @param string $status
     */
    public static function update_offer_status($offer_id, $status) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'woo_offers';
        
        $result = $wpdb->update(
            $table,
            [
                'status' => $status,
                'updated_at' => current_time('mysql')
            ],
            ['id' => $offer_id],
            ['%s', '%s'],
            ['%d']
        );
        
        if ($result !== false) {
            // Fire action for other components
            do_action('woo_offers_status_changed', $offer_id, $status);
            
            // Clear caches
            self::clear_offer_caches();
        }
        
        return $result !== false;
    }
    
    /**
     * Schedule future status changes using WP-Cron
     * 
     * @param int $offer_id
     * @param int|null $start_time
     * @param int|null $end_time
     */
    private static function schedule_future_status_changes($offer_id, $start_time, $end_time) {
        $hook_activate = "woo_offers_activate_{$offer_id}";
        $hook_expire = "woo_offers_expire_{$offer_id}";
        
        // Clear existing scheduled events
        wp_clear_scheduled_hook($hook_activate);
        wp_clear_scheduled_hook($hook_expire);
        
        // Schedule activation
        if ($start_time && $start_time > time()) {
            wp_schedule_single_event($start_time, $hook_activate, [$offer_id]);
            add_action($hook_activate, function($id) {
                self::update_offer_status($id, 'active');
            });
        }
        
        // Schedule expiration
        if ($end_time && $end_time > time()) {
            wp_schedule_single_event($end_time, $hook_expire, [$offer_id]);
            add_action($hook_expire, function($id) {
                self::update_offer_status($id, 'expired');
            });
        }
    }
    
    /**
     * Get offers by status
     * 
     * @param string|array $status
     * @return array
     */
    public static function get_offers_by_status($status) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'woo_offers';
        
        if (is_array($status)) {
            $placeholders = implode(',', array_fill(0, count($status), '%s'));
            $query = $wpdb->prepare(
                "SELECT * FROM {$table} WHERE status IN ({$placeholders}) ORDER BY created_at DESC",
                $status
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT * FROM {$table} WHERE status = %s ORDER BY created_at DESC",
                $status
            );
        }
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Get upcoming scheduled offers
     * 
     * @param int $limit
     * @return array
     */
    public static function get_upcoming_offers($limit = 10) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'woo_offers';
        $current_time = current_time('mysql');
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE status = 'scheduled' 
             AND start_date > %s 
             ORDER BY start_date ASC 
             LIMIT %d",
            $current_time,
            $limit
        ), ARRAY_A);
    }
    
    /**
     * Get expiring offers
     * 
     * @param int $hours_ahead
     * @param int $limit
     * @return array
     */
    public static function get_expiring_offers($hours_ahead = 24, $limit = 10) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'woo_offers';
        $current_time = current_time('mysql');
        $expire_time = date('Y-m-d H:i:s', strtotime("+{$hours_ahead} hours"));
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE status = 'active' 
             AND end_date IS NOT NULL 
             AND end_date BETWEEN %s AND %s 
             ORDER BY end_date ASC 
             LIMIT %d",
            $current_time,
            $expire_time,
            $limit
        ), ARRAY_A);
    }
    
    /**
     * AJAX handler for manual status updates
     */
    public static function ajax_manual_status_update() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'woo_offers_admin') || 
            !current_user_can('manage_woo_offers')) {
            wp_die(__('Security check failed', 'woo-offers'));
        }
        
        $offer_id = intval($_POST['offer_id'] ?? 0);
        $new_status = sanitize_text_field($_POST['status'] ?? '');
        
        if (!$offer_id || !in_array($new_status, ['active', 'inactive', 'scheduled', 'expired'])) {
            wp_send_json_error(__('Invalid parameters', 'woo-offers'));
        }
        
        $success = self::update_offer_status($offer_id, $new_status);
        
        if ($success) {
            wp_send_json_success([
                'message' => __('Status updated successfully', 'woo-offers'),
                'new_status' => $new_status
            ]);
        } else {
            wp_send_json_error(__('Failed to update status', 'woo-offers'));
        }
    }
    
    /**
     * Log status changes for analytics
     */
    private static function log_status_changes() {
        // This would integrate with the analytics system
        // For now, we'll just fire an action
        do_action('woo_offers_status_batch_updated');
    }
    
    /**
     * Clear offer-related caches
     */
    private static function clear_offer_caches() {
        // Clear any object caches or transients
        delete_transient('woo_offers_active_offers');
        delete_transient('woo_offers_scheduled_offers');
        
        // Clear WooCommerce caches if needed
        if (function_exists('wc_delete_shop_order_transients')) {
            wc_delete_shop_order_transients();
        }
    }
    
    /**
     * Get status display information
     * 
     * @param string $status
     * @return array
     */
    public static function get_status_info($status) {
        $statuses = [
            'active' => [
                'label' => __('Active', 'woo-offers'),
                'color' => '#46b450',
                'icon' => 'yes-alt',
                'description' => __('Offer is currently active and available', 'woo-offers')
            ],
            'inactive' => [
                'label' => __('Inactive', 'woo-offers'),
                'color' => '#999',
                'icon' => 'minus',
                'description' => __('Offer is manually disabled', 'woo-offers')
            ],
            'scheduled' => [
                'label' => __('Scheduled', 'woo-offers'),
                'color' => '#00a0d2',
                'icon' => 'clock',
                'description' => __('Offer will activate at scheduled time', 'woo-offers')
            ],
            'expired' => [
                'label' => __('Expired', 'woo-offers'),
                'color' => '#dc3232',
                'icon' => 'no-alt',
                'description' => __('Offer has ended and is no longer available', 'woo-offers')
            ]
        ];
        
        return $statuses[$status] ?? $statuses['inactive'];
    }
} 