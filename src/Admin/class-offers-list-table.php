<?php

namespace WooOffers\Admin;

/**
 * Offers List Table for WordPress admin
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load WP_List_Table class if not already loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Offers List Table class for managing offers in WordPress admin
 */
class Offers_List_Table extends \WP_List_Table {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct([
            'singular' => 'offer',
            'plural'   => 'offers',
            'ajax'     => false
        ]);
    }

    /**
     * Get table columns
     *
     * @return array
     */
    public function get_columns() {
        return [
            'cb'          => '<input type="checkbox" />',
            'title'       => __( 'Offer Title', 'woo-offers' ),
            'type'        => __( 'Type', 'woo-offers' ),
            'status'      => __( 'Status', 'woo-offers' ),
            'conversions' => __( 'Conversions', 'woo-offers' ),
            'revenue'     => __( 'Revenue', 'woo-offers' ),
            'date'        => __( 'Created', 'woo-offers' )
        ];
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function get_sortable_columns() {
        return [
            'title'       => [ 'title', false ],
            'type'        => [ 'type', false ],
            'status'      => [ 'status', false ],
            'conversions' => [ 'conversions', true ],
            'revenue'     => [ 'revenue', true ],
            'date'        => [ 'date', false ]
        ];
    }

    /**
     * Get bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        return [
            'enable'  => __( 'Enable', 'woo-offers' ),
            'disable' => __( 'Disable', 'woo-offers' ),
            'delete'  => __( 'Delete', 'woo-offers' )
        ];
    }

    /**
     * Render the checkbox column
     *
     * @param array $item
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="offer[]" value="%s" />',
            esc_attr( $item['id'] )
        );
    }

    /**
     * Render the title column with row actions
     *
     * @param array $item
     * @return string
     */
    public function column_title( $item ) {
        $edit_url = add_query_arg([
            'page'   => 'woo-offers-create',
            'action' => 'edit',
            'id'     => $item['id']
        ], admin_url( 'admin.php' ));

        $delete_url = add_query_arg([
            'page'   => 'woo-offers-offers',
            'action' => 'delete',
            'id'     => $item['id'],
            'nonce'  => wp_create_nonce( 'delete_offer_' . $item['id'] )
        ], admin_url( 'admin.php' ));

        $duplicate_url = add_query_arg([
            'page'   => 'woo-offers-offers',
            'action' => 'duplicate',
            'id'     => $item['id'],
            'nonce'  => wp_create_nonce( 'duplicate_offer_' . $item['id'] )
        ], admin_url( 'admin.php' ));

        $actions = [
            'edit'      => sprintf( '<a href="%s">%s</a>', esc_url( $edit_url ), __( 'Edit', 'woo-offers' ) ),
            'duplicate' => sprintf( '<a href="%s">%s</a>', esc_url( $duplicate_url ), __( 'Duplicate', 'woo-offers' ) ),
            'delete'    => sprintf( 
                '<a href="%s" onclick="return confirm(\'%s\')">%s</a>', 
                esc_url( $delete_url ), 
                esc_js( __( 'Are you sure you want to delete this offer?', 'woo-offers' ) ),
                __( 'Delete', 'woo-offers' ) 
            )
        ];

        return sprintf(
            '<strong><a href="%s">%s</a></strong>%s',
            esc_url( $edit_url ),
            esc_html( $item['title'] ),
            $this->row_actions( $actions )
        );
    }

    /**
     * Render the type column
     *
     * @param array $item
     * @return string
     */
    public function column_type( $item ) {
        $types = [
            'upsell'     => __( 'Upsell', 'woo-offers' ),
            'cross_sell' => __( 'Cross-sell', 'woo-offers' ),
            'downsell'   => __( 'Downsell', 'woo-offers' ),
            'bundle'     => __( 'Bundle', 'woo-offers' )
        ];

        return isset( $types[ $item['type'] ] ) ? $types[ $item['type'] ] : $item['type'];
    }

    /**
     * Render the status column
     *
     * @param array $item
     * @return string
     */
    public function column_status( $item ) {
        $status_classes = [
            'active'   => 'status-active',
            'inactive' => 'status-inactive',
            'draft'    => 'status-draft'
        ];

        $status_labels = [
            'active'   => __( 'Active', 'woo-offers' ),
            'inactive' => __( 'Inactive', 'woo-offers' ),
            'draft'    => __( 'Draft', 'woo-offers' )
        ];

        $status = $item['status'];
        $class = isset( $status_classes[ $status ] ) ? $status_classes[ $status ] : '';
        $label = isset( $status_labels[ $status ] ) ? $status_labels[ $status ] : $status;

        return sprintf(
            '<span class="offer-status %s">%s</span>',
            esc_attr( $class ),
            esc_html( $label )
        );
    }

    /**
     * Render the conversions column
     *
     * @param array $item
     * @return string
     */
    public function column_conversions( $item ) {
        $conversions = isset( $item['conversions'] ) ? intval( $item['conversions'] ) : 0;
        $views = isset( $item['views'] ) ? intval( $item['views'] ) : 0;
        
        if ( $views > 0 ) {
            $rate = round( ( $conversions / $views ) * 100, 2 );
            return sprintf(
                '%d <small class="conversion-rate">(%s%% conversion)</small>',
                $conversions,
                $rate
            );
        }

        if ( $conversions > 0 ) {
            return sprintf(
                '%d <small class="no-views">(no view data)</small>',
                $conversions
            );
        }

        return '<span class="no-data">—</span>';
    }

    /**
     * Render the revenue column
     *
     * @param array $item
     * @return string
     */
    public function column_revenue( $item ) {
        $revenue = isset( $item['revenue'] ) ? floatval( $item['revenue'] ) : 0;
        
        // Use WooCommerce formatting if available, otherwise fall back to basic formatting
        if ( function_exists( 'wc_price' ) ) {
            return wc_price( $revenue );
        }
        
        return '$' . number_format( $revenue, 2 );
    }

    /**
     * Render the date column
     *
     * @param array $item
     * @return string
     */
    public function column_date( $item ) {
        $date = $item['date'];
        $timestamp = strtotime( $date );
        
        if ( ! $timestamp ) {
            return '—';
        }

        $time_diff = time() - $timestamp;
        
        if ( $time_diff < DAY_IN_SECONDS ) {
            return sprintf(
                '<abbr title="%s">%s</abbr>',
                esc_attr( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) ),
                human_time_diff( $timestamp )
            );
        }

        return date_i18n( get_option( 'date_format' ), $timestamp );
    }

    /**
     * Default column renderer
     *
     * @param array $item
     * @param string $column_name
     * @return string
     */
    public function column_default( $item, $column_name ) {
        return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '—';
    }

    /**
     * Prepare table items for display
     */
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [ $columns, $hidden, $sortable ];

        // Handle bulk actions
        $this->process_bulk_action();

        // Get current page and items per page
        $per_page = $this->get_items_per_page( 'offers_per_page', 20 );
        $current_page = $this->get_pagenum();
        $offset = ( $current_page - 1 ) * $per_page;

        // Get sorting parameters
        $orderby = sanitize_text_field( $_GET['orderby'] ?? 'date' );
        $order = sanitize_text_field( $_GET['order'] ?? 'desc' );

        // Get search term
        $search = sanitize_text_field( $_GET['s'] ?? '' );

        // Get filter parameters
        $status_filter = sanitize_text_field( $_GET['status'] ?? '' );
        $type_filter = sanitize_text_field( $_GET['type'] ?? '' );

        // Get offers data
        $data = $this->get_offers( $offset, $per_page, $orderby, $order, $search, $status_filter, $type_filter );
        $total_items = $this->get_offers_count( $search, $status_filter, $type_filter );

        $this->items = $data;

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page )
        ]);
    }

    /**
     * Get offers data from database
     *
     * @param int $offset
     * @param int $limit
     * @param string $orderby
     * @param string $order
     * @param string $search
     * @param string $status_filter
     * @param string $type_filter
     * @return array
     */
    private function get_offers( $offset = 0, $limit = 20, $orderby = 'date', $order = 'desc', $search = '', $status_filter = '', $type_filter = '' ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'woo_offers';
        $analytics_table = $wpdb->prefix . 'woo_offers_analytics';

        // Build WHERE clauses
        $where_clauses = [ '1=1' ];
        $where_values = [];

        // Search functionality
        if ( ! empty( $search ) ) {
            $where_clauses[] = '(o.name LIKE %s OR o.description LIKE %s)';
            $search_term = '%' . $wpdb->esc_like( $search ) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }

        // Status filter
        if ( ! empty( $status_filter ) ) {
            $where_clauses[] = 'o.status = %s';
            $where_values[] = $status_filter;
        }

        // Type filter
        if ( ! empty( $type_filter ) ) {
            $where_clauses[] = 'o.type = %s';
            $where_values[] = $type_filter;
        }

        // Build ORDER BY clause
        $allowed_orderby = [ 'name', 'type', 'status', 'created_at', 'usage_count', 'revenue' ];
        if ( 'date' === $orderby ) {
            $orderby = 'created_at';
        } elseif ( 'title' === $orderby ) {
            $orderby = 'name';
        } elseif ( 'conversions' === $orderby ) {
            $orderby = 'usage_count';
        } elseif ( 'revenue' === $orderby ) {
            $orderby = 'revenue';
        }

        if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
            $orderby = 'created_at';
        }

        $order = strtoupper( $order );
        if ( ! in_array( $order, [ 'ASC', 'DESC' ], true ) ) {
            $order = 'DESC';
        }

        $where_sql = implode( ' AND ', $where_clauses );

        // Main query with analytics data
        $sql = "
            SELECT 
                o.id,
                o.name as title,
                o.type,
                o.status,
                o.usage_count as conversions,
                o.created_at as date,
                COALESCE(SUM(CASE WHEN a.event_type = 'conversion' THEN a.revenue ELSE 0 END), 0) as revenue,
                COUNT(CASE WHEN a.event_type = 'view' THEN 1 END) as views
            FROM {$table_name} o
            LEFT JOIN {$analytics_table} a ON o.id = a.offer_id
            WHERE {$where_sql}
            GROUP BY o.id
            ORDER BY {$orderby} {$order}
            LIMIT %d OFFSET %d
        ";

        $query_values = array_merge( $where_values, [ $limit, $offset ] );
        $results = $wpdb->get_results( $wpdb->prepare( $sql, $query_values ), ARRAY_A );

        // If no results from database, return sample data for testing
        if ( empty( $results ) ) {
            return $this->get_sample_data();
        }

        return $results;
    }

    /**
     * Get total offers count
     *
     * @param string $search
     * @param string $status_filter
     * @param string $type_filter
     * @return int
     */
    private function get_offers_count( $search = '', $status_filter = '', $type_filter = '' ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'woo_offers';

        // Build WHERE clauses
        $where_clauses = [ '1=1' ];
        $where_values = [];

        // Search functionality
        if ( ! empty( $search ) ) {
            $where_clauses[] = '(name LIKE %s OR description LIKE %s)';
            $search_term = '%' . $wpdb->esc_like( $search ) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }

        // Status filter
        if ( ! empty( $status_filter ) ) {
            $where_clauses[] = 'status = %s';
            $where_values[] = $status_filter;
        }

        // Type filter
        if ( ! empty( $type_filter ) ) {
            $where_clauses[] = 'type = %s';
            $where_values[] = $type_filter;
        }

        $where_sql = implode( ' AND ', $where_clauses );

        $sql = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}";

        if ( empty( $where_values ) ) {
            $count = $wpdb->get_var( $sql );
        } else {
            $count = $wpdb->get_var( $wpdb->prepare( $sql, $where_values ) );
        }

        // If no records in database, return sample count for testing
        return $count ? intval( $count ) : 4;
    }

    /**
     * Display extra table navigation (filters)
     *
     * @param string $which
     */
    public function extra_tablenav( $which ) {
        if ( 'top' !== $which ) {
            return;
        }

        $status_filter = sanitize_text_field( $_GET['status'] ?? '' );
        $type_filter = sanitize_text_field( $_GET['type'] ?? '' );
        ?>
        <div class="alignleft actions">
            <?php $this->display_status_filter( $status_filter ); ?>
            <?php $this->display_type_filter( $type_filter ); ?>
            <?php submit_button( __( 'Filter', 'woo-offers' ), '', 'filter_action', false, [ 'id' => 'post-query-submit' ] ); ?>
        </div>
        <?php
    }

    /**
     * Display status filter dropdown
     *
     * @param string $selected
     */
    private function display_status_filter( $selected = '' ) {
        $statuses = [
            ''         => __( 'All statuses', 'woo-offers' ),
            'active'   => __( 'Active', 'woo-offers' ),
            'inactive' => __( 'Inactive', 'woo-offers' ),
            'draft'    => __( 'Draft', 'woo-offers' )
        ];

        echo '<label for="filter-by-status" class="screen-reader-text">' . __( 'Filter by status', 'woo-offers' ) . '</label>';
        echo '<select name="status" id="filter-by-status">';
        
        foreach ( $statuses as $value => $label ) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr( $value ),
                selected( $selected, $value, false ),
                esc_html( $label )
            );
        }
        
        echo '</select>';
    }

    /**
     * Display type filter dropdown
     *
     * @param string $selected
     */
    private function display_type_filter( $selected = '' ) {
        $types = [
            ''           => __( 'All types', 'woo-offers' ),
            'upsell'     => __( 'Upsell', 'woo-offers' ),
            'cross_sell' => __( 'Cross-sell', 'woo-offers' ),
            'downsell'   => __( 'Downsell', 'woo-offers' ),
            'bundle'     => __( 'Bundle', 'woo-offers' ),
            'quantity_break' => __( 'Quantity Break', 'woo-offers' ),
            'bogo'       => __( 'Buy One Get One', 'woo-offers' )
        ];

        echo '<label for="filter-by-type" class="screen-reader-text">' . __( 'Filter by type', 'woo-offers' ) . '</label>';
        echo '<select name="type" id="filter-by-type">';
        
        foreach ( $types as $value => $label ) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr( $value ),
                selected( $selected, $value, false ),
                esc_html( $label )
            );
        }
        
        echo '</select>';
    }

    /**
     * Display search box
     *
     * @param string $text
     * @param string $input_id
     */
    public function search_box( $text = '', $input_id = 'offer' ) {
        if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
            return;
        }

        $input_id = $input_id . '-search-input';
        
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
        }
        if ( ! empty( $_REQUEST['order'] ) ) {
            echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
        }
        if ( ! empty( $_REQUEST['status'] ) ) {
            echo '<input type="hidden" name="status" value="' . esc_attr( $_REQUEST['status'] ) . '" />';
        }
        if ( ! empty( $_REQUEST['type'] ) ) {
            echo '<input type="hidden" name="type" value="' . esc_attr( $_REQUEST['type'] ) . '" />';
        }
        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>">
                <?php echo $text ? esc_html( $text ) : __( 'Search Offers:', 'woo-offers' ); ?>
            </label>
            <input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_attr_e( 'Search offers...', 'woo-offers' ); ?>" />
            <?php submit_button( $text ? $text : __( 'Search Offers', 'woo-offers' ), '', '', false, [ 'id' => 'search-submit' ] ); ?>
        </p>
        <?php
    }

    /**
     * Process bulk actions
     */
    public function process_bulk_action() {
        $action = $this->current_action();

        if ( ! $action ) {
            return;
        }

        $offer_ids = isset( $_GET['offer'] ) ? array_map( 'intval', $_GET['offer'] ) : [];

        if ( empty( $offer_ids ) ) {
            return;
        }

        // Verify nonce
        if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'bulk-offers' ) ) {
            wp_die( __( 'Security check failed.', 'woo-offers' ) );
        }

        switch ( $action ) {
            case 'enable':
                $this->bulk_enable( $offer_ids );
                break;
            case 'disable':
                $this->bulk_disable( $offer_ids );
                break;
            case 'delete':
                $this->bulk_delete( $offer_ids );
                break;
        }

        // Redirect to remove query parameters
        wp_redirect( remove_query_arg( [ 'action', 'offer', '_wpnonce', 'action2' ] ) );
        exit;
    }

    /**
     * Bulk enable offers
     *
     * @param array $offer_ids
     */
    private function bulk_enable( $offer_ids ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'woo_offers';
        $updated_count = 0;

        foreach ( $offer_ids as $offer_id ) {
            $result = $wpdb->update(
                $table_name,
                [ 
                    'status' => 'active',
                    'updated_at' => current_time( 'mysql' )
                ],
                [ 'id' => $offer_id ],
                [ '%s', '%s' ],
                [ '%d' ]
            );

            if ( $result !== false ) {
                $updated_count++;
                do_action( 'woo_offers_status_changed', $offer_id, 'active' );
            }
        }

        if ( $updated_count > 0 ) {
            $admin = new Admin();
            $admin->add_success_notice(
                sprintf( 
                    _n( 'Enabled %d offer.', 'Enabled %d offers.', $updated_count, 'woo-offers' ),
                    $updated_count
                ),
                true,
                true
            );
        }
    }

    /**
     * Bulk disable offers
     *
     * @param array $offer_ids
     */
    private function bulk_disable( $offer_ids ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'woo_offers';
        $updated_count = 0;

        foreach ( $offer_ids as $offer_id ) {
            $result = $wpdb->update(
                $table_name,
                [ 
                    'status' => 'inactive',
                    'updated_at' => current_time( 'mysql' )
                ],
                [ 'id' => $offer_id ],
                [ '%s', '%s' ],
                [ '%d' ]
            );

            if ( $result !== false ) {
                $updated_count++;
                do_action( 'woo_offers_status_changed', $offer_id, 'inactive' );
            }
        }

        if ( $updated_count > 0 ) {
            $admin = new Admin();
            $admin->add_success_notice(
                sprintf( 
                    _n( 'Disabled %d offer.', 'Disabled %d offers.', $updated_count, 'woo-offers' ),
                    $updated_count
                ),
                true,
                true
            );
        }
    }

    /**
     * Bulk delete offers
     *
     * @param array $offer_ids
     */
    private function bulk_delete( $offer_ids ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'woo_offers';
        $deleted_count = 0;

        foreach ( $offer_ids as $offer_id ) {
            // First fire the before delete action
            do_action( 'woo_offers_before_delete', $offer_id );

            $result = $wpdb->delete(
                $table_name,
                [ 'id' => $offer_id ],
                [ '%d' ]
            );

            if ( $result !== false ) {
                $deleted_count++;
                do_action( 'woo_offers_after_delete', $offer_id );
            }
        }

        if ( $deleted_count > 0 ) {
            $admin = new Admin();
            $admin->add_success_notice(
                sprintf( 
                    _n( 'Deleted %d offer.', 'Deleted %d offers.', $deleted_count, 'woo-offers' ),
                    $deleted_count
                ),
                true,
                true
            );
        }
    }

    /**
     * Get sample data for testing
     *
     * @return array
     */
    private function get_sample_data() {
        return [
            [
                'id'          => 1,
                'title'       => 'Premium Package Upgrade',
                'type'        => 'upsell',
                'status'      => 'active',
                'conversions' => 45,
                'views'       => 320,
                'revenue'     => 2250.00,
                'date'        => '2024-01-15 10:30:00'
            ],
            [
                'id'          => 2,
                'title'       => 'Extended Warranty Offer',
                'type'        => 'cross_sell',
                'status'      => 'active',
                'conversions' => 23,
                'views'       => 180,
                'revenue'     => 1150.00,
                'date'        => '2024-01-10 14:20:00'
            ],
            [
                'id'          => 3,
                'title'       => 'Basic Plan Alternative',
                'type'        => 'downsell',
                'status'      => 'inactive',
                'conversions' => 12,
                'views'       => 95,
                'revenue'     => 480.00,
                'date'        => '2024-01-08 09:15:00'
            ],
            [
                'id'          => 4,
                'title'       => 'Accessories Bundle',
                'type'        => 'bundle',
                'status'      => 'draft',
                'conversions' => 0,
                'views'       => 0,
                'revenue'     => 0.00,
                'date'        => '2024-01-05 16:45:00'
            ]
        ];
    }

    /**
     * Get summary statistics for all offers
     *
     * @return array
     */
    public function get_summary_stats() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'woo_offers';
        $analytics_table = $wpdb->prefix . 'woo_offers_analytics';

        $sql = "
            SELECT 
                COUNT(o.id) as total_offers,
                SUM(CASE WHEN o.status = 'active' THEN 1 ELSE 0 END) as active_offers,
                SUM(CASE WHEN o.status = 'inactive' THEN 1 ELSE 0 END) as inactive_offers,
                SUM(CASE WHEN o.status = 'draft' THEN 1 ELSE 0 END) as draft_offers,
                COALESCE(SUM(CASE WHEN a.event_type = 'conversion' THEN a.revenue ELSE 0 END), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN a.event_type = 'conversion' THEN 1 ELSE 0 END), 0) as total_conversions,
                COALESCE(SUM(CASE WHEN a.event_type = 'view' THEN 1 ELSE 0 END), 0) as total_views
            FROM {$table_name} o
            LEFT JOIN {$analytics_table} a ON o.id = a.offer_id
        ";

        $stats = $wpdb->get_row( $sql, ARRAY_A );

        if ( ! $stats ) {
            return [
                'total_offers' => 0,
                'active_offers' => 0,
                'inactive_offers' => 0,
                'draft_offers' => 0,
                'total_revenue' => 0,
                'total_conversions' => 0,
                'total_views' => 0,
                'conversion_rate' => 0
            ];
        }

        // Calculate conversion rate
        $conversion_rate = 0;
        if ( $stats['total_views'] > 0 ) {
            $conversion_rate = round( ( $stats['total_conversions'] / $stats['total_views'] ) * 100, 2 );
        }

        $stats['conversion_rate'] = $conversion_rate;

        return $stats;
    }
} 