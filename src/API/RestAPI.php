<?php

namespace WooOffers\API;

defined('ABSPATH') || exit;

/**
 * REST API endpoints for Woo Offers
 */
class RestAPI {
    
    private $namespace = 'woo-offers/v1';
    
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Register all REST API routes
     */
    public function register_routes() {
        // Offers endpoints
        register_rest_route($this->namespace, '/offers', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_offers'],
                'permission_callback' => [$this, 'check_permissions']
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'create_offer'],
                'permission_callback' => [$this, 'check_permissions']
            ]
        ]);
        
        register_rest_route($this->namespace, '/offers/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_offer'],
                'permission_callback' => [$this, 'check_permissions']
            ],
            [
                'methods' => 'PUT',
                'callback' => [$this, 'update_offer'],
                'permission_callback' => [$this, 'check_permissions']
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'delete_offer'],
                'permission_callback' => [$this, 'check_permissions']
            ]
        ]);
        
        // Templates endpoints
        register_rest_route($this->namespace, '/templates', [
            'methods' => 'GET',
            'callback' => [$this, 'get_templates'],
            'permission_callback' => [$this, 'check_permissions']
        ]);
        
        // Analytics endpoints
        register_rest_route($this->namespace, '/analytics', [
            'methods' => 'GET',
            'callback' => [$this, 'get_analytics'],
            'permission_callback' => [$this, 'check_permissions']
        ]);
        
        // Settings endpoints
        register_rest_route($this->namespace, '/settings', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_settings'],
                'permission_callback' => [$this, 'check_permissions']
            ],
            [
                'methods' => 'PUT',
                'callback' => [$this, 'update_settings'],
                'permission_callback' => [$this, 'check_permissions']
            ]
        ]);
        
        // A/B Tests endpoints
        register_rest_route($this->namespace, '/ab-tests', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_ab_tests'],
                'permission_callback' => [$this, 'check_permissions']
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'create_ab_test'],
                'permission_callback' => [$this, 'check_permissions']
            ]
        ]);
        
        // Initial data endpoint for app bootstrap
        register_rest_route($this->namespace, '/initial-data', [
            'methods' => 'GET',
            'callback' => [$this, 'get_initial_data'],
            'permission_callback' => [$this, 'check_permissions']
        ]);
    }
    
    /**
     * Check permissions for API requests
     */
    public function check_permissions() {
        return current_user_can('manage_woo_offers');
    }
    
    /**
     * Get all offers
     */
    public function get_offers($request) {
        global $wpdb;
        
        $params = $request->get_params();
        $page = isset($params['page']) ? max(1, intval($params['page'])) : 1;
        $per_page = isset($params['per_page']) ? max(1, min(100, intval($params['per_page']))) : 10;
        $status = isset($params['status']) ? sanitize_text_field($params['status']) : '';
        $search = isset($params['search']) ? sanitize_text_field($params['search']) : '';
        
        $table_name = $wpdb->prefix . 'woo_offers';
        $where_clauses = ['1=1'];
        $where_values = [];
        
        if (!empty($status)) {
            $where_clauses[] = 'status = %s';
            $where_values[] = $status;
        }
        
        if (!empty($search)) {
            $where_clauses[] = '(title LIKE %s OR description LIKE %s)';
            $where_values[] = '%' . $wpdb->esc_like($search) . '%';
            $where_values[] = '%' . $wpdb->esc_like($search) . '%';
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        $offset = ($page - 1) * $per_page;
        
        // Get total count
        $count_sql = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}";
        $total = $wpdb->get_var($wpdb->prepare($count_sql, $where_values));
        
        // Get offers
        $sql = "SELECT * FROM {$table_name} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $offers = $wpdb->get_results($wpdb->prepare($sql, array_merge($where_values, [$per_page, $offset])), ARRAY_A);
        
        // Parse JSON fields
        foreach ($offers as &$offer) {
            $offer['conditions'] = json_decode($offer['conditions'], true);
            $offer['actions'] = json_decode($offer['actions'], true);
            $offer['display_options'] = json_decode($offer['display_options'], true);
        }
        
        return rest_ensure_response([
            'offers' => $offers,
            'total' => intval($total),
            'pages' => ceil($total / $per_page),
            'current_page' => $page,
            'per_page' => $per_page
        ]);
    }
    
    /**
     * Get single offer
     */
    public function get_offer($request) {
        global $wpdb;
        
        $id = intval($request['id']);
        $table_name = $wpdb->prefix . 'woo_offers';
        
        $offer = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id), ARRAY_A);
        
        if (!$offer) {
            return new \WP_Error('offer_not_found', 'Offer not found', ['status' => 404]);
        }
        
        // Parse JSON fields
        $offer['conditions'] = json_decode($offer['conditions'], true);
        $offer['actions'] = json_decode($offer['actions'], true);
        $offer['display_options'] = json_decode($offer['display_options'], true);
        
        return rest_ensure_response($offer);
    }
    
    /**
     * Create new offer
     */
    public function create_offer($request) {
        global $wpdb;
        
        $params = $request->get_params();
        $table_name = $wpdb->prefix . 'woo_offers';
        
        $data = [
            'title' => sanitize_text_field($params['title']),
            'description' => sanitize_textarea_field($params['description']),
            'type' => sanitize_text_field($params['type']),
            'status' => sanitize_text_field($params['status'] ?? 'draft'),
            'conditions' => wp_json_encode($params['conditions'] ?? []),
            'actions' => wp_json_encode($params['actions'] ?? []),
            'display_options' => wp_json_encode($params['display_options'] ?? []),
            'priority' => intval($params['priority'] ?? 5),
            'start_date' => $params['start_date'] ?? null,
            'end_date' => $params['end_date'] ?? null,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert($table_name, $data);
        
        if ($result === false) {
            return new \WP_Error('create_failed', 'Failed to create offer', ['status' => 500]);
        }
        
        $offer_id = $wpdb->insert_id;
        return $this->get_offer(new \WP_REST_Request('GET', '', ['id' => $offer_id]));
    }
    
    /**
     * Update offer
     */
    public function update_offer($request) {
        global $wpdb;
        
        $id = intval($request['id']);
        $params = $request->get_params();
        $table_name = $wpdb->prefix . 'woo_offers';
        
        // Check if offer exists
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table_name} WHERE id = %d", $id));
        if (!$exists) {
            return new \WP_Error('offer_not_found', 'Offer not found', ['status' => 404]);
        }
        
        $data = [
            'title' => sanitize_text_field($params['title']),
            'description' => sanitize_textarea_field($params['description']),
            'type' => sanitize_text_field($params['type']),
            'status' => sanitize_text_field($params['status']),
            'conditions' => wp_json_encode($params['conditions'] ?? []),
            'actions' => wp_json_encode($params['actions'] ?? []),
            'display_options' => wp_json_encode($params['display_options'] ?? []),
            'priority' => intval($params['priority'] ?? 5),
            'start_date' => $params['start_date'] ?? null,
            'end_date' => $params['end_date'] ?? null,
            'updated_at' => current_time('mysql')
        ];
        
        $result = $wpdb->update($table_name, $data, ['id' => $id]);
        
        if ($result === false) {
            return new \WP_Error('update_failed', 'Failed to update offer', ['status' => 500]);
        }
        
        return $this->get_offer(new \WP_REST_Request('GET', '', ['id' => $id]));
    }
    
    /**
     * Delete offer
     */
    public function delete_offer($request) {
        global $wpdb;
        
        $id = intval($request['id']);
        $table_name = $wpdb->prefix . 'woo_offers';
        
        $result = $wpdb->delete($table_name, ['id' => $id]);
        
        if ($result === false) {
            return new \WP_Error('delete_failed', 'Failed to delete offer', ['status' => 500]);
        }
        
        return rest_ensure_response(['success' => true, 'id' => $id]);
    }
    
    /**
     * Get templates
     */
    public function get_templates($request) {
        // Return predefined templates for now
        $templates = [
            [
                'id' => 1,
                'name' => 'Quantity Discount',
                'description' => 'Offer discount for buying multiple items',
                'type' => 'quantity_discount',
                'preview' => '🔢 Buy 3, Get 20% Off!',
                'config' => [
                    'min_quantity' => 3,
                    'discount_type' => 'percentage',
                    'discount_value' => 20
                ]
            ],
            [
                'id' => 2,
                'name' => 'BOGO Offer',
                'description' => 'Buy one get one free promotion',
                'type' => 'bogo',
                'preview' => '🎁 Buy 1 Get 1 FREE!',
                'config' => [
                    'buy_quantity' => 1,
                    'get_quantity' => 1,
                    'discount_type' => 'percentage',
                    'discount_value' => 100
                ]
            ],
            [
                'id' => 3,
                'name' => 'Free Shipping',
                'description' => 'Free shipping threshold offer',
                'type' => 'free_shipping',
                'preview' => '🚚 Free Shipping on Orders $50+',
                'config' => [
                    'min_amount' => 50,
                    'shipping_discount' => 'free'
                ]
            ]
        ];
        
        return rest_ensure_response($templates);
    }
    
    /**
     * Get analytics data
     */
    public function get_analytics($request) {
        global $wpdb;
        
        $offers_table = $wpdb->prefix . 'woo_offers';
        $analytics_table = $wpdb->prefix . 'woo_offers_analytics';
        
        // Get basic counts
        $total_offers = $wpdb->get_var("SELECT COUNT(*) FROM {$offers_table}");
        $active_offers = $wpdb->get_var("SELECT COUNT(*) FROM {$offers_table} WHERE status = 'active'");
        
        // Get analytics summary (last 30 days)
        $thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
        $analytics = $wpdb->get_row($wpdb->prepare("
            SELECT 
                SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as total_views,
                SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as total_clicks,
                SUM(CASE WHEN event_type = 'conversion' THEN 1 ELSE 0 END) as total_conversions,
                SUM(COALESCE(JSON_EXTRACT(metadata, '$.revenue'), 0)) as total_revenue
            FROM {$analytics_table} 
            WHERE created_at >= %s
        ", $thirty_days_ago), ARRAY_A);
        
        $conversion_rate = 0;
        if ($analytics['total_views'] > 0) {
            $conversion_rate = round(($analytics['total_conversions'] / $analytics['total_views']) * 100, 2);
        }
        
        return rest_ensure_response([
            'totalOffers' => intval($total_offers),
            'activeOffers' => intval($active_offers),
            'totalViews' => intval($analytics['total_views'] ?? 0),
            'totalClicks' => intval($analytics['total_clicks'] ?? 0),
            'totalConversions' => intval($analytics['total_conversions'] ?? 0),
            'revenue' => floatval($analytics['total_revenue'] ?? 0),
            'conversionRate' => $conversion_rate
        ]);
    }
    
    /**
     * Get settings
     */
    public function get_settings($request) {
        $settings = [
            'general' => get_option('woo_offers_general_settings', []),
            'display' => get_option('woo_offers_display_settings', []),
            'performance' => get_option('woo_offers_performance_settings', [])
        ];
        
        return rest_ensure_response($settings);
    }
    
    /**
     * Update settings
     */
    public function update_settings($request) {
        $params = $request->get_params();
        
        if (isset($params['general'])) {
            update_option('woo_offers_general_settings', $params['general']);
        }
        
        if (isset($params['display'])) {
            update_option('woo_offers_display_settings', $params['display']);
        }
        
        if (isset($params['performance'])) {
            update_option('woo_offers_performance_settings', $params['performance']);
        }
        
        return $this->get_settings($request);
    }
    
    /**
     * Get A/B tests
     */
    public function get_ab_tests($request) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'woo_offers_ab_tests';
        $tests = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY created_at DESC", ARRAY_A);
        
        // Parse JSON fields
        foreach ($tests as &$test) {
            $test['variants'] = json_decode($test['variants'], true);
            $test['conversion_goals'] = json_decode($test['conversion_goals'], true);
        }
        
        return rest_ensure_response($tests);
    }
    
    /**
     * Create A/B test
     */
    public function create_ab_test($request) {
        global $wpdb;
        
        $params = $request->get_params();
        $table_name = $wpdb->prefix . 'woo_offers_ab_tests';
        
        $data = [
            'name' => sanitize_text_field($params['name']),
            'description' => sanitize_textarea_field($params['description']),
            'offer_id' => intval($params['offer_id']),
            'variants' => wp_json_encode($params['variants'] ?? []),
            'traffic_allocation' => floatval($params['traffic_allocation'] ?? 50),
            'status' => sanitize_text_field($params['status'] ?? 'draft'),
            'conversion_goals' => wp_json_encode($params['conversion_goals'] ?? []),
            'start_date' => $params['start_date'] ?? null,
            'end_date' => $params['end_date'] ?? null,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert($table_name, $data);
        
        if ($result === false) {
            return new \WP_Error('create_failed', 'Failed to create A/B test', ['status' => 500]);
        }
        
        return rest_ensure_response(['success' => true, 'id' => $wpdb->insert_id]);
    }
    
    /**
     * Get initial data for app bootstrap
     */
    public function get_initial_data($request) {
        // Get basic data for app initialization
        $offers_response = $this->get_offers(new \WP_REST_Request('GET', '', ['per_page' => 5]));
        $templates_response = $this->get_templates($request);
        $analytics_response = $this->get_analytics($request);
        
        return rest_ensure_response([
            'offers' => $offers_response->get_data()['offers'],
            'templates' => $templates_response->get_data(),
            'analytics' => $analytics_response->get_data()
        ]);
    }
}
