<?php

namespace WooOffers\API;

defined('ABSPATH') || exit;

/**
 * AJAX handlers for Woo Offers (fallback for REST API)
 */
class AjaxHandlers {
    
    public function __construct() {
        // Admin AJAX hooks
        add_action('wp_ajax_woo_offers_get_offers', [$this, 'ajax_get_offers']);
        add_action('wp_ajax_woo_offers_get_offer', [$this, 'ajax_get_offer']);
        add_action('wp_ajax_woo_offers_create_offer', [$this, 'ajax_create_offer']);
        add_action('wp_ajax_woo_offers_update_offer', [$this, 'ajax_update_offer']);
        add_action('wp_ajax_woo_offers_delete_offer', [$this, 'ajax_delete_offer']);
        add_action('wp_ajax_woo_offers_get_templates', [$this, 'ajax_get_templates']);
        add_action('wp_ajax_woo_offers_get_analytics', [$this, 'ajax_get_analytics']);
        add_action('wp_ajax_woo_offers_get_settings', [$this, 'ajax_get_settings']);
        add_action('wp_ajax_woo_offers_update_settings', [$this, 'ajax_update_settings']);
        add_action('wp_ajax_woo_offers_get_ab_tests', [$this, 'ajax_get_ab_tests']);
        add_action('wp_ajax_woo_offers_create_ab_test', [$this, 'ajax_create_ab_test']);
        
        // Offer actions
        add_action('wp_ajax_woo_offers_save_offer', [$this, 'ajax_save_offer']);
        add_action('wp_ajax_woo_offers_delete_offer', [$this, 'ajax_delete_offer']);
        add_action('wp_ajax_woo_offers_duplicate_offer', [$this, 'ajax_duplicate_offer']);
        
        // New scheduling endpoints
        add_action('wp_ajax_woo_offers_get_scheduled_data', [$this, 'ajax_get_scheduled_data']);
        add_action('wp_ajax_woo_offers_manual_status_update', [$this, 'ajax_manual_status_update']);
    }
    
    /**
     * Verify nonce and permissions
     */
    private function verify_request() {
        if (!check_ajax_referer('woo_offers_nonce', 'nonce', false)) {
            wp_die(json_encode(['success' => false, 'data' => 'Invalid nonce']));
        }
        
        if (!current_user_can('manage_woo_offers')) {
            wp_die(json_encode(['success' => false, 'data' => 'Insufficient permissions']));
        }
    }
    
    /**
     * Send JSON response
     */
    private function send_response($data, $success = true) {
        wp_send_json([
            'success' => $success,
            'data' => $data
        ]);
    }
    
    /**
     * AJAX: Get offers
     */
    public function ajax_get_offers() {
        $this->verify_request();
        
        // Use RestAPI class for the actual logic
        $rest_api = new RestAPI();
        $request = new \WP_REST_Request('GET', '/woo-offers/v1/offers');
        $request->set_query_params($_POST);
        
        $response = $rest_api->get_offers($request);
        
        if (is_wp_error($response)) {
            $this->send_response($response->get_error_message(), false);
        } else {
            $this->send_response($response->get_data());
        }
    }
    
    /**
     * AJAX: Get single offer
     */
    public function ajax_get_offer() {
        $this->verify_request();
        
        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            $this->send_response('Invalid offer ID', false);
            return;
        }
        
        $rest_api = new RestAPI();
        $request = new \WP_REST_Request('GET', '/woo-offers/v1/offers/' . $id);
        $request->set_url_params(['id' => $id]);
        
        $response = $rest_api->get_offer($request);
        
        if (is_wp_error($response)) {
            $this->send_response($response->get_error_message(), false);
        } else {
            $this->send_response($response->get_data());
        }
    }
    
    /**
     * AJAX: Create offer
     */
    public function ajax_create_offer() {
        $this->verify_request();
        
        $rest_api = new RestAPI();
        $request = new \WP_REST_Request('POST', '/woo-offers/v1/offers');
        $request->set_body_params($_POST);
        
        $response = $rest_api->create_offer($request);
        
        if (is_wp_error($response)) {
            $this->send_response($response->get_error_message(), false);
        } else {
            $this->send_response($response->get_data());
        }
    }
    
    /**
     * AJAX: Update offer
     */
    public function ajax_update_offer() {
        $this->verify_request();
        
        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            $this->send_response('Invalid offer ID', false);
            return;
        }
        
        $rest_api = new RestAPI();
        $request = new \WP_REST_Request('PUT', '/woo-offers/v1/offers/' . $id);
        $request->set_url_params(['id' => $id]);
        $request->set_body_params($_POST);
        
        $response = $rest_api->update_offer($request);
        
        if (is_wp_error($response)) {
            $this->send_response($response->get_error_message(), false);
        } else {
            $this->send_response($response->get_data());
        }
    }
    
    /**
     * AJAX: Delete offer
     */
    public function ajax_delete_offer() {
        $this->verify_request();
        
        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            $this->send_response('Invalid offer ID', false);
            return;
        }
        
        $rest_api = new RestAPI();
        $request = new \WP_REST_Request('DELETE', '/woo-offers/v1/offers/' . $id);
        $request->set_url_params(['id' => $id]);
        
        $response = $rest_api->delete_offer($request);
        
        if (is_wp_error($response)) {
            $this->send_response($response->get_error_message(), false);
        } else {
            $this->send_response($response->get_data());
        }
    }
    
    /**
     * AJAX: Get templates
     */
    public function ajax_get_templates() {
        $this->verify_request();
        
        $rest_api = new RestAPI();
        $request = new \WP_REST_Request('GET', '/woo-offers/v1/templates');
        
        $response = $rest_api->get_templates($request);
        $this->send_response($response->get_data());
    }
    
    /**
     * AJAX: Get analytics
     */
    public function ajax_get_analytics() {
        $this->verify_request();
        
        $rest_api = new RestAPI();
        $request = new \WP_REST_Request('GET', '/woo-offers/v1/analytics');
        $request->set_query_params($_POST);
        
        $response = $rest_api->get_analytics($request);
        $this->send_response($response->get_data());
    }
    
    /**
     * AJAX: Get settings
     */
    public function ajax_get_settings() {
        $this->verify_request();
        
        $rest_api = new RestAPI();
        $request = new \WP_REST_Request('GET', '/woo-offers/v1/settings');
        
        $response = $rest_api->get_settings($request);
        $this->send_response($response->get_data());
    }
    
    /**
     * AJAX: Update settings
     */
    public function ajax_update_settings() {
        $this->verify_request();
        
        $rest_api = new RestAPI();
        $request = new \WP_REST_Request('PUT', '/woo-offers/v1/settings');
        $request->set_body_params($_POST);
        
        $response = $rest_api->update_settings($request);
        $this->send_response($response->get_data());
    }
    
    /**
     * AJAX: Get A/B tests
     */
    public function ajax_get_ab_tests() {
        $this->verify_request();
        
        $rest_api = new RestAPI();
        $request = new \WP_REST_Request('GET', '/woo-offers/v1/ab-tests');
        
        $response = $rest_api->get_ab_tests($request);
        $this->send_response($response->get_data());
    }
    
    /**
     * AJAX: Create A/B test
     */
    public function ajax_create_ab_test() {
        $this->verify_request();
        
        $rest_api = new RestAPI();
        $request = new \WP_REST_Request('POST', '/woo-offers/v1/ab-tests');
        $request->set_body_params($_POST);
        
        $response = $rest_api->create_ab_test($request);
        
        if (is_wp_error($response)) {
            $this->send_response($response->get_error_message(), false);
        } else {
            $this->send_response($response->get_data());
        }
    }
    
    /**
     * AJAX: Get scheduled offers data
     */
    public function ajax_get_scheduled_data() {
        $this->verify_request();
        
        $max_items = intval($_POST['max_items'] ?? 10);
        
        try {
            $upcoming = \WooOffers\Offers\OfferScheduler::get_upcoming_offers($max_items);
            $expiring = \WooOffers\Offers\OfferScheduler::get_expiring_offers(24, $max_items);
            
            $this->send_response([
                'upcoming' => $upcoming,
                'expiring' => $expiring
            ]);
        } catch (Exception $e) {
            $this->send_response($e->getMessage(), false);
        }
    }
    
    /**
     * AJAX: Manual status update (already handled by OfferScheduler)
     */
    public function ajax_manual_status_update() {
        // This is handled directly by OfferScheduler::ajax_manual_status_update()
        // We just need to ensure the action is registered
        \WooOffers\Offers\OfferScheduler::ajax_manual_status_update();
    }
}
