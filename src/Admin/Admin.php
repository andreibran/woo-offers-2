<?php

namespace WooOffers\Admin;

use WooOffers\Core\SecurityManager;

/**
 * Admin interface management
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin class for managing backend interface
 */
class Admin {

    /**
     * Menu slug
     */
    const MENU_SLUG = 'woo-offers';

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_init', [ $this, 'init_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
        add_action( 'admin_notices', [ $this, 'admin_notices' ] );
        add_action( 'current_screen', [ $this, 'add_contextual_help' ] );
        add_filter( 'plugin_action_links_' . WOO_OFFERS_PLUGIN_BASENAME, [ $this, 'plugin_action_links' ] );
        
        // Metabox registration - Now handled directly in templates
        // add_action( 'admin_init', [ $this, 'register_offer_metaboxes' ] );
        
        // AJAX hooks
        add_action( 'wp_ajax_woo_offers_save_settings', [ $this, 'save_settings_ajax' ] );
        add_action( 'wp_ajax_woo_offers_get_offers', [ $this, 'get_offers_ajax' ] );
        add_action( 'wp_ajax_woo_offers_delete_offer', [ $this, 'delete_offer_ajax' ] );
        add_action( 'wp_ajax_woo_offers_search_products', [ $this, 'search_products_ajax' ] );
        add_action( 'wp_ajax_woo_offers_preview_offer', [ $this, 'preview_offer_ajax' ] );
        add_action( 'wp_ajax_woo_offers_preview_offer_modal', [ $this, 'preview_offer_modal_ajax' ] );
        add_action( 'wp_ajax_woo_offers_dismiss_getting_started', [ $this, 'dismiss_getting_started_ajax' ] );
    }

    /**
     * Add admin menu and submenus
     */
    public function add_admin_menu() {
        // Check if user has permission
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        // Main menu page
        add_menu_page(
            __( 'Woo Offers', 'woo-offers' ),
            __( 'Woo Offers', 'woo-offers' ),
            'manage_woocommerce',
            self::MENU_SLUG,
            [ $this, 'dashboard_page' ],
            $this->get_menu_icon(),
            57 // Position after WooCommerce
        );

        // Dashboard submenu
        add_submenu_page(
            self::MENU_SLUG,
            __( 'Dashboard', 'woo-offers' ),
            __( 'Dashboard', 'woo-offers' ),
            'manage_woocommerce',
            self::MENU_SLUG,
            [ $this, 'dashboard_page' ]
        );

        // Offers submenu
        add_submenu_page(
            self::MENU_SLUG,
            __( 'All Offers', 'woo-offers' ),
            __( 'All Offers', 'woo-offers' ),
            'manage_woocommerce',
            self::MENU_SLUG . '-offers',
            [ $this, 'offers_page' ]
        );

        // Create Offer submenu
        add_submenu_page(
            self::MENU_SLUG,
            __( 'Create Offer', 'woo-offers' ),
            __( 'Create Offer', 'woo-offers' ),
            'manage_woocommerce',
            self::MENU_SLUG . '-create',
            [ $this, 'create_offer_page' ]
        );

        // Analytics submenu
        add_submenu_page(
            self::MENU_SLUG,
            __( 'Analytics', 'woo-offers' ),
            __( 'Analytics', 'woo-offers' ),
            'manage_woocommerce',
            self::MENU_SLUG . '-analytics',
            [ $this, 'analytics_page' ]
        );

        // A/B Tests submenu
        add_submenu_page(
            self::MENU_SLUG,
            __( 'A/B Tests', 'woo-offers' ),
            __( 'A/B Tests', 'woo-offers' ),
            'manage_woocommerce',
            self::MENU_SLUG . '-ab-tests',
            [ $this, 'ab_tests_page' ]
        );

        // Settings submenu
        add_submenu_page(
            self::MENU_SLUG,
            __( 'Settings', 'woo-offers' ),
            __( 'Settings', 'woo-offers' ),
            'manage_woocommerce',
            self::MENU_SLUG . '-settings',
            [ $this, 'settings_page' ]
        );

        // Import/Export submenu
        add_submenu_page(
            self::MENU_SLUG,
            __( 'Import/Export', 'woo-offers' ),
            __( 'Import/Export', 'woo-offers' ),
            'manage_woocommerce',
            self::MENU_SLUG . '-import-export',
            [ $this, 'import_export_page' ]
        );

        // Help Documentation submenu
        add_submenu_page(
            self::MENU_SLUG,
            __( 'Help & Documentation', 'woo-offers' ),
            __( 'Help', 'woo-offers' ),
            'manage_woocommerce',
            self::MENU_SLUG . '-help',
            [ $this, 'help_page' ]
        );

        // Getting Started submenu
        add_submenu_page(
            self::MENU_SLUG,
            __( 'Getting Started Guide', 'woo-offers' ),
            __( 'Getting Started', 'woo-offers' ),
            'manage_woocommerce',
            self::MENU_SLUG . '-getting-started',
            [ $this, 'getting_started_page' ]
        );
    }

    /**
     * Initialize settings API
     */
    public function init_settings() {
        register_setting(
            'woo_offers_settings_group',
            'woo_offers_settings',
            [ $this, 'sanitize_settings' ]
        );

        // General settings section
        add_settings_section(
            'woo_offers_general_section',
            __( 'General Settings', 'woo-offers' ),
            [ $this, 'general_section_callback' ],
            'woo_offers_general'
        );

        // Display settings section
        add_settings_section(
            'woo_offers_display_section',
            __( 'Display Settings', 'woo-offers' ),
            [ $this, 'display_section_callback' ],
            'woo_offers_display'
        );

        // Performance settings section
        add_settings_section(
            'woo_offers_performance_section',
            __( 'Performance Settings', 'woo-offers' ),
            [ $this, 'performance_section_callback' ],
            'woo_offers_performance'
        );

        $this->add_settings_fields();
    }

    /**
     * Add settings fields
     */
    private function add_settings_fields() {
        $settings = get_option( 'woo_offers_settings', [] );

        // General settings fields
        add_settings_field(
            'enable_analytics',
            __( 'Enable Analytics', 'woo-offers' ),
            [ $this, 'checkbox_field_callback' ],
            'woo_offers_general',
            'woo_offers_general_section',
            [
                'name' => 'woo_offers_settings[enable_analytics]',
                'value' => $settings['enable_analytics'] ?? true,
                'description' => __( 'Track offer performance and user interactions', 'woo-offers' )
            ]
        );

        add_settings_field(
            'enable_ab_testing',
            __( 'Enable A/B Testing', 'woo-offers' ),
            [ $this, 'checkbox_field_callback' ],
            'woo_offers_general',
            'woo_offers_general_section',
            [
                'name' => 'woo_offers_settings[enable_ab_testing]',
                'value' => $settings['enable_ab_testing'] ?? true,
                'description' => __( 'Allow creating and running A/B tests for offers', 'woo-offers' )
            ]
        );

        // Display settings fields
        add_settings_field(
            'primary_color',
            __( 'Primary Color', 'woo-offers' ),
            [ $this, 'color_field_callback' ],
            'woo_offers_display',
            'woo_offers_display_section',
            [
                'name' => 'woo_offers_settings[primary_color]',
                'value' => $settings['primary_color'] ?? '#e92d3b',
                'description' => __( 'Main color for offer displays', 'woo-offers' )
            ]
        );

        add_settings_field(
            'default_position',
            __( 'Default Position', 'woo-offers' ),
            [ $this, 'select_field_callback' ],
            'woo_offers_display',
            'woo_offers_display_section',
            [
                'name' => 'woo_offers_settings[default_position]',
                'value' => $settings['default_position'] ?? 'before_add_to_cart',
                'options' => [
                    'before_add_to_cart' => __( 'Before Add to Cart Button', 'woo-offers' ),
                    'after_add_to_cart' => __( 'After Add to Cart Button', 'woo-offers' ),
                    'before_product_summary' => __( 'Before Product Summary', 'woo-offers' ),
                    'after_product_summary' => __( 'After Product Summary', 'woo-offers' ),
                ],
                'description' => __( 'Default position for offer displays on product pages', 'woo-offers' )
            ]
        );

        // Performance settings fields
        add_settings_field(
            'cache_offers',
            __( 'Cache Offers', 'woo-offers' ),
            [ $this, 'checkbox_field_callback' ],
            'woo_offers_performance',
            'woo_offers_performance_section',
            [
                'name' => 'woo_offers_settings[cache_offers]',
                'value' => $settings['cache_offers'] ?? true,
                'description' => __( 'Cache offer data for better performance', 'woo-offers' )
            ]
        );

        add_settings_field(
            'load_scripts_everywhere',
            __( 'Load Scripts Everywhere', 'woo-offers' ),
            [ $this, 'checkbox_field_callback' ],
            'woo_offers_performance',
            'woo_offers_performance_section',
            [
                'name' => 'woo_offers_settings[load_scripts_everywhere]',
                'value' => $settings['load_scripts_everywhere'] ?? false,
                'description' => __( 'Load offer scripts on all pages (may impact performance)', 'woo-offers' )
            ]
        );
    }

    /**
     * Dashboard page content
     */
    public function dashboard_page() {
        $this->render_admin_page( 'dashboard' );
    }

    /**
     * Offers page content
     */
    public function offers_page() {
        $this->render_admin_page( 'offers' );
    }

    /**
     * Create/Edit offer page content
     */
    public function create_offer_page() {
        // Handle form submission
        if ( $_POST && isset( $_POST['action'] ) && $_POST['action'] === 'save_offer' ) {
            $this->save_offer();
        }
        
        // Use edit template for both create and edit
        $this->render_admin_page( 'edit-offer' );
    }

    /**
     * Analytics page content
     */
    public function analytics_page() {
        $this->render_admin_page( 'analytics' );
    }

    /**
     * A/B Tests page content
     */
    public function ab_tests_page() {
        $this->render_admin_page( 'ab-tests' );
    }

    /**
     * Settings page content
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'woo_offers_settings_group' );
                ?>
                <div class="woo-offers-settings-tabs">
                    <nav class="nav-tab-wrapper">
                        <a href="#general" class="nav-tab nav-tab-active"><?php _e( 'General', 'woo-offers' ); ?></a>
                        <a href="#display" class="nav-tab"><?php _e( 'Display', 'woo-offers' ); ?></a>
                        <a href="#performance" class="nav-tab"><?php _e( 'Performance', 'woo-offers' ); ?></a>
                    </nav>
                    <div class="tab-content">
                        <div id="general" class="tab-pane active">
                            <?php do_settings_sections( 'woo_offers_general' ); ?>
                        </div>
                        <div id="display" class="tab-pane">
                            <?php do_settings_sections( 'woo_offers_display' ); ?>
                        </div>
                        <div id="performance" class="tab-pane">
                            <?php do_settings_sections( 'woo_offers_performance' ); ?>
                        </div>
                    </div>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Import/Export page content
     */
    public function import_export_page() {
        // Handle form submissions
        if ( $_POST ) {
            if ( isset( $_POST['action'] ) && $_POST['action'] === 'export_csv' ) {
                $this->export_offers_csv();
            } elseif ( isset( $_POST['action'] ) && $_POST['action'] === 'export_json' ) {
                $this->export_offers_json();
            } elseif ( isset( $_POST['action'] ) && $_POST['action'] === 'import_csv' ) {
                $this->handle_import_csv();
            }
        }
        
        $this->render_admin_page( 'import-export' );
    }

    /**
     * Help & Documentation page content
     */
    public function help_page() {
        $this->render_admin_page( 'help' );
    }

    /**
     * Render admin page using PHP templates
     */
    private function render_admin_page( $page ) {
        $template_file = WOO_OFFERS_PLUGIN_PATH . 'templates/admin/' . $page . '.php';
        $wrapper_file = WOO_OFFERS_PLUGIN_PATH . 'templates/admin/admin-wrapper.php';
        
        if ( file_exists( $wrapper_file ) ) {
            include $wrapper_file;
        } else {
            // Fallback if wrapper doesn't exist
            ?>
            <div class="wrap">
                <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
                <?php
                if ( file_exists( $template_file ) ) {
                    include $template_file;
                } else {
                    echo '<p>' . __( 'Template not found.', 'woo-offers' ) . '</p>';
                }
                ?>
            </div>
            <?php
        }
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts( $hook ) {
        // Only load on our admin pages
        if ( strpos( $hook, 'woo-offers' ) === false ) {
            return;
        }

        // Enqueue WordPress standard admin styles
        wp_enqueue_style( 'wp-admin' );
        wp_enqueue_style( 'colors' );
        wp_enqueue_style( 'common' );
        wp_enqueue_style( 'forms' );
        wp_enqueue_style( 'dashboard' );
        
        // Enqueue our custom admin styles
        wp_enqueue_style(
            'woo-offers-admin',
            WOO_OFFERS_PLUGIN_URL . 'assets/css/admin.css',
            [ 'wp-admin', 'colors', 'common', 'forms' ],
            WOO_OFFERS_VERSION
        );

        // Enqueue WordPress standard admin scripts
        wp_enqueue_script( 'common' );
        wp_enqueue_script( 'wp-lists' );
        wp_enqueue_script( 'postbox' );
        
        // Enqueue our custom admin script (if needed)
        wp_enqueue_script(
            'woo-offers-admin',
            WOO_OFFERS_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery', 'common', 'wp-lists', 'postbox' ],
            WOO_OFFERS_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script( 'woo-offers-admin', 'wooOffersAdmin', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'woo_offers_nonce' ),
            // ✅ SECURITY: Specific nonces for different actions
            'nonces' => [
                'searchProducts' => wp_create_nonce( 'woo_offers_search_products' ),
                'saveOffer' => wp_create_nonce( 'woo_offers_save_offer' ),
                'previewOffer' => wp_create_nonce( 'woo_offers_preview_offer' ),
                'deleteOffer' => wp_create_nonce( 'woo_offers_delete_offer' ),
                'saveSettings' => wp_create_nonce( 'woo_offers_save_settings' ),
                'analytics' => wp_create_nonce( 'woo_offers_analytics' ),
            ],
            'pluginUrl' => WOO_OFFERS_PLUGIN_URL,
            'currentPage' => $_GET['page'] ?? '',
            'strings' => [
                'confirmDelete' => __( 'Are you sure you want to delete this offer?', 'woo-offers' ),
                'confirmBulkDelete' => __( 'Are you sure you want to delete the selected offers?', 'woo-offers' ),
                'offerSaved' => __( 'Offer saved successfully!', 'woo-offers' ),
                'offerDeleted' => __( 'Offer deleted successfully!', 'woo-offers' ),
                'error' => __( 'An error occurred. Please try again.', 'woo-offers' ),
                'selectItems' => __( 'Please select at least one item.', 'woo-offers' ),
                'filtering' => __( 'Filtering...', 'woo-offers' ),
                'filter' => __( 'Filter', 'woo-offers' ),
                'searching' => __( 'Searching products...', 'woo-offers' ),
                'noResults' => __( 'No products found.', 'woo-offers' ),
                'productAdded' => __( 'Product added successfully!', 'woo-offers' ),
                'productRemoved' => __( 'Product removed.', 'woo-offers' ),
                'rateLimitExceeded' => __( 'Too many requests. Please wait.', 'woo-offers' ),
                'accessDenied' => __( 'Access denied. Please refresh.', 'woo-offers' ),
            ]
        ]);
    }

    /**
     * Show admin notices
     */
    public function admin_notices() {
        // Show setup wizard notice if not completed
        if ( ! get_option( 'woo_offers_wizard_completed', false ) ) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <?php _e( 'Welcome to Woo Offers! Complete the setup wizard to get started.', 'woo-offers' ); ?>
                    <a href="<?php echo admin_url( 'admin.php?page=' . self::MENU_SLUG . '-setup' ); ?>" class="button button-primary">
                        <?php _e( 'Start Setup', 'woo-offers' ); ?>
                    </a>
                </p>
            </div>
            <?php
        }

        // Display transient notices
        $this->display_transient_notices();
    }

    /**
     * Add a success notice
     * 
     * @param string $message The message to display
     * @param bool $dismissible Whether the notice can be dismissed
     * @param bool $persistent Whether to store as transient (survives page reload)
     */
    public function add_success_notice( $message, $dismissible = true, $persistent = false ) {
        $this->add_admin_notice( $message, 'success', $dismissible, $persistent );
    }

    /**
     * Add an error notice
     * 
     * @param string $message The message to display
     * @param bool $dismissible Whether the notice can be dismissed
     * @param bool $persistent Whether to store as transient (survives page reload)
     */
    public function add_error_notice( $message, $dismissible = true, $persistent = false ) {
        $this->add_admin_notice( $message, 'error', $dismissible, $persistent );
    }

    /**
     * Add a warning notice
     * 
     * @param string $message The message to display
     * @param bool $dismissible Whether the notice can be dismissed
     * @param bool $persistent Whether to store as transient (survives page reload)
     */
    public function add_warning_notice( $message, $dismissible = true, $persistent = false ) {
        $this->add_admin_notice( $message, 'warning', $dismissible, $persistent );
    }

    /**
     * Add an info notice
     * 
     * @param string $message The message to display
     * @param bool $dismissible Whether the notice can be dismissed
     * @param bool $persistent Whether to store as transient (survives page reload)
     */
    public function add_info_notice( $message, $dismissible = true, $persistent = false ) {
        $this->add_admin_notice( $message, 'info', $dismissible, $persistent );
    }

    /**
     * Add an admin notice
     * 
     * @param string $message The message to display
     * @param string $type The notice type (success, error, warning, info)
     * @param bool $dismissible Whether the notice can be dismissed
     * @param bool $persistent Whether to store as transient (survives page reload)
     */
    public function add_admin_notice( $message, $type = 'info', $dismissible = true, $persistent = false ) {
        if ( $persistent ) {
            $notices = get_transient( 'woo_offers_admin_notices' ) ?: [];
            $notices[] = [
                'message' => $message,
                'type' => $type,
                'dismissible' => $dismissible,
                'timestamp' => time()
            ];
            set_transient( 'woo_offers_admin_notices', $notices, 300 ); // 5 minutes
        } else {
            // Display immediately if not persistent
            echo $this->generate_notice_html( $message, $type, $dismissible );
        }
    }

    /**
     * Display transient notices and clear them
     */
    private function display_transient_notices() {
        $notices = get_transient( 'woo_offers_admin_notices' );
        
        if ( ! empty( $notices ) && is_array( $notices ) ) {
            foreach ( $notices as $notice ) {
                echo $this->generate_notice_html( 
                    $notice['message'], 
                    $notice['type'], 
                    $notice['dismissible'] 
                );
            }
            
            // Clear the notices after displaying
            delete_transient( 'woo_offers_admin_notices' );
        }
    }

    /**
     * Generate HTML for admin notice
     * 
     * @param string $message The message to display
     * @param string $type The notice type (success, error, warning, info)
     * @param bool $dismissible Whether the notice can be dismissed
     * @return string The HTML for the notice
     */
    private function generate_notice_html( $message, $type = 'info', $dismissible = true ) {
        $classes = ['notice', 'notice-' . $type];
        
        if ( $dismissible ) {
            $classes[] = 'is-dismissible';
        }
        
        $class_string = implode( ' ', $classes );
        $dismiss_button = '';
        
        if ( $dismissible ) {
            $dismiss_button = '<button type="button" class="notice-dismiss">
                <span class="screen-reader-text">' . __( 'Dismiss this notice.', 'woo-offers' ) . '</span>
            </button>';
        }
        
        return sprintf(
            '<div class="%s"><p>%s</p>%s</div>',
            esc_attr( $class_string ),
            wp_kses_post( $message ),
            $dismiss_button
        );
    }

    /**
     * Clear all pending admin notices
     */
    public function clear_admin_notices() {
        delete_transient( 'woo_offers_admin_notices' );
    }

    /**
     * Check if there are pending notices
     * 
     * @return bool Whether there are pending notices
     */
    public function has_pending_notices() {
        $notices = get_transient( 'woo_offers_admin_notices' );
        return ! empty( $notices );
    }

    /**
     * Add plugin action links
     */
    public function plugin_action_links( $links ) {
        $plugin_links = [
            '<a href="' . admin_url( 'admin.php?page=' . self::MENU_SLUG ) . '">' . __( 'Dashboard', 'woo-offers' ) . '</a>',
            '<a href="' . admin_url( 'admin.php?page=' . self::MENU_SLUG . '-settings' ) . '">' . __( 'Settings', 'woo-offers' ) . '</a>',
        ];

        return array_merge( $plugin_links, $links );
    }

    /**
     * Get menu icon SVG
     */
    private function get_menu_icon() {
        return 'data:image/svg+xml;base64,' . base64_encode(
            '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2 4h16v2H2V4zm0 5h16v2H2V9zm0 5h16v2H2v-2z" fill="#a0a5aa"/>
            </svg>'
        );
    }

    /**
     * Settings section callbacks
     */
    public function general_section_callback() {
        echo '<p>' . __( 'Configure general plugin settings.', 'woo-offers' ) . '</p>';
    }

    public function display_section_callback() {
        echo '<p>' . __( 'Configure how offers are displayed to customers.', 'woo-offers' ) . '</p>';
    }

    public function performance_section_callback() {
        echo '<p>' . __( 'Configure performance and optimization settings.', 'woo-offers' ) . '</p>';
    }

    /**
     * Settings field callbacks
     */
    public function checkbox_field_callback( $args ) {
        $checked = $args['value'] ? 'checked' : '';
        echo "<input type='checkbox' name='{$args['name']}' value='1' {$checked} />";
        if ( ! empty( $args['description'] ) ) {
            echo "<p class='description'>{$args['description']}</p>";
        }
    }

    public function color_field_callback( $args ) {
        echo "<input type='color' name='{$args['name']}' value='{$args['value']}' />";
        if ( ! empty( $args['description'] ) ) {
            echo "<p class='description'>{$args['description']}</p>";
        }
    }

    public function select_field_callback( $args ) {
        echo "<select name='{$args['name']}'>";
        foreach ( $args['options'] as $value => $label ) {
            $selected = selected( $args['value'], $value, false );
            echo "<option value='{$value}' {$selected}>{$label}</option>";
        }
        echo "</select>";
        if ( ! empty( $args['description'] ) ) {
            echo "<p class='description'>{$args['description']}</p>";
        }
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings( $input ) {
        $sanitized = [];

        if ( isset( $input['enable_analytics'] ) ) {
            $sanitized['enable_analytics'] = (bool) $input['enable_analytics'];
        }

        if ( isset( $input['enable_ab_testing'] ) ) {
            $sanitized['enable_ab_testing'] = (bool) $input['enable_ab_testing'];
        }

        if ( isset( $input['primary_color'] ) ) {
            $sanitized['primary_color'] = sanitize_hex_color( $input['primary_color'] );
        }

        if ( isset( $input['default_position'] ) ) {
            $sanitized['default_position'] = sanitize_text_field( $input['default_position'] );
        }

        if ( isset( $input['cache_offers'] ) ) {
            $sanitized['cache_offers'] = (bool) $input['cache_offers'];
        }

        if ( isset( $input['load_scripts_everywhere'] ) ) {
            $sanitized['load_scripts_everywhere'] = (bool) $input['load_scripts_everywhere'];
        }

        return $sanitized;
    }

    /**
     * AJAX: Save settings
     */
    public function save_settings_ajax() {
        check_ajax_referer( 'woo_offers_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'Permission denied.', 'woo-offers' ) );
        }

        $settings = $this->sanitize_settings( $_POST['settings'] ?? [] );
        update_option( 'woo_offers_settings', $settings );

        wp_send_json_success( [
            'message' => __( 'Settings saved successfully!', 'woo-offers' )
        ] );
    }

    /**
     * AJAX: Get offers
     */
    public function get_offers_ajax() {
        check_ajax_referer( 'woo_offers_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'Permission denied.', 'woo-offers' ) );
        }

        // TODO: Implement offer retrieval logic
        wp_send_json_success( [] );
    }

    /**
     * AJAX: Delete offer
     */
    public function delete_offer_ajax() {
        check_ajax_referer( 'woo_offers_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'Permission denied.', 'woo-offers' ) );
        }

        $offer_id = intval( $_POST['offer_id'] ?? 0 );
        
        // TODO: Implement offer deletion logic
        
        wp_send_json_success( [
            'message' => __( 'Offer deleted successfully!', 'woo-offers' )
        ] );
    }

    /**
     * AJAX: Search products (SECURE VERSION v3.0)
     * 
     * ✅ SECURITY FIXES IMPLEMENTED:
     * - Nonce verification
     * - Capability checks  
     * - Rate limiting
     * - WooCommerce Data Store usage
     * - Comprehensive error handling
     * - Input validation
     */
    public function search_products_ajax() {
        try {
            // ✅ SECURITY: Verify nonce
            SecurityManager::verify_ajax_nonce( 'woo_offers_search_products' );
            
            // ✅ SECURITY: Verify user capabilities
            SecurityManager::verify_capability( 'edit_products' );
            
            // ✅ SECURITY: Check rate limits (30 requests per minute)
            SecurityManager::check_rate_limit( 'product_search', 30, 60 );
            
            // ✅ SECURITY: Validate and sanitize query
            $query = SecurityManager::sanitize_product_search_query( $_POST['query'] ?? '' );
            
            $products = [];
            
            // ✅ PERFORMANCE: Use WooCommerce Data Store (instead of manual WP_Query)
            if ( class_exists( 'WC_Data_Store' ) ) {
                $data_store = \WC_Data_Store::load( 'product' );
                
                // Search using WooCommerce native search
                $product_ids = $data_store->search_products(
                    $query,           // search term
                    '',               // status (empty for all published)
                    true,             // include variations
                    false,            // return ids only
                    20,               // limit
                    [],               // product_ids (empty for all)
                    []                // exclude_ids
                );
                
                foreach ( $product_ids as $product_id ) {
                    $product = \wc_get_product( $product_id );
                    
                    if ( ! $product || ! $product->is_purchasable() ) {
                        continue;
                    }
                    
                    $formatted_product = $this->format_product_data( $product );
                    if ( $formatted_product ) {
                        $products[] = $formatted_product;
                    }
                }
                
            } else {
                // Fallback to manual search if WC Data Store not available
                $this->fallback_product_search( $query, $products );
            }
            
            // Apply final limit and send response
            $products = array_slice( $products, 0, 20 );
            
            // ✅ SECURITY: Log successful search for monitoring
            error_log( sprintf( 
                'WooOffers: Product search successful - Query: %s, Results: %d, User: %d', 
                $query, 
                count( $products ), 
                get_current_user_id() 
            ) );
            
            wp_send_json_success( $products );
            
        } catch ( \Exception $e ) {
            // ✅ SECURITY: Log error with context for monitoring
            error_log( sprintf( 
                'WooOffers: Product search failed - Error: %s, Query: %s, User: %d, IP: %s', 
                $e->getMessage(),
                $_POST['query'] ?? 'N/A',
                get_current_user_id(),
                SecurityManager::get_client_ip()
            ) );
            
            wp_send_json_error( [
                'message' => __( 'Search failed. Please try again.', 'woo-offers' ),
                'code' => 'SEARCH_FAILED'
            ] );
        }
    }
    
    /**
     * Fallback product search method (when WC Data Store not available)
     * 
     * @param string $query Search query
     * @param array $products Products array (passed by reference)
     */
    private function fallback_product_search( $query, &$products ) {
        // Search by name and content
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            's' => $query,
            'orderby' => 'relevance',
            'order' => 'DESC'
        ];

        $name_query = new \WP_Query( $args );
        
        if ( $name_query->have_posts() ) {
            while ( $name_query->have_posts() ) {
                $name_query->the_post();
                $product = \wc_get_product( get_the_ID() );
                
                if ( ! $product || ! $product->is_purchasable() ) {
                    continue;
                }

                // Check for duplicates
                $already_added = false;
                foreach ( $products as $existing_product ) {
                    if ( $existing_product['id'] === $product->get_id() ) {
                        $already_added = true;
                        break;
                    }
                }
                
                if ( ! $already_added ) {
                    $formatted_product = $this->format_product_data( $product );
                    if ( $formatted_product ) {
                        $products[] = $formatted_product;
                    }
                }
            }
            wp_reset_postdata();
        }

        // Search by SKU if not enough results
        if ( count( $products ) < 10 ) {
            $sku_args = [
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => 20,
                'meta_query' => [
                    [
                        'key' => '_sku',
                        'value' => $query,
                        'compare' => 'LIKE'
                    ]
                ]
            ];

            $sku_query = new \WP_Query( $sku_args );
            
            if ( $sku_query->have_posts() ) {
                while ( $sku_query->have_posts() ) {
                    $sku_query->the_post();
                    $product = \wc_get_product( get_the_ID() );
                    
                    if ( ! $product || ! $product->is_purchasable() ) {
                        continue;
                    }

                    // Check for duplicates
                    $already_added = false;
                    foreach ( $products as $existing_product ) {
                        if ( $existing_product['id'] === $product->get_id() ) {
                            $already_added = true;
                            break;
                        }
                    }
                    
                    if ( ! $already_added ) {
                        $formatted_product = $this->format_product_data( $product );
                        if ( $formatted_product ) {
                            $products[] = $formatted_product;
                        }
                    }
                }
                wp_reset_postdata();
            }
        }
    }

    /**
     * AJAX: Preview offer in new tab
     */
    public function preview_offer_ajax() {
        check_ajax_referer( 'woo_offers_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'Permission denied.', 'woo-offers' ) );
        }

        // Generate preview URL with form data
        $preview_id = 'preview_' . time() . '_' . wp_rand( 1000, 9999 );
        $preview_data = $this->sanitize_preview_data( $_POST );
        
        // Store preview data temporarily
        set_transient( 'woo_offers_preview_' . $preview_id, $preview_data, 300 ); // 5 minutes
        
        $preview_url = add_query_arg( [
            'woo_offers_preview' => $preview_id,
            'nonce' => wp_create_nonce( 'woo_offers_preview_' . $preview_id )
        ], home_url() );

        wp_send_json_success( [
            'preview_url' => $preview_url
        ] );
    }

    /**
     * AJAX: Preview offer in modal
     */
    public function preview_offer_modal_ajax() {
        check_ajax_referer( 'woo_offers_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'Permission denied.', 'woo-offers' ) );
        }

        $preview_data = $this->sanitize_preview_data( $_POST );
        $html = $this->generate_offer_preview_html( $preview_data );

        wp_send_json_success( [
            'html' => $html
        ] );
    }

    /**
     * Sanitize preview data from form submission
     */
    private function sanitize_preview_data( $post_data ) {
        $sanitized = [
            'offer_name' => sanitize_text_field( $post_data['offer_name'] ?? '' ),
            'offer_description' => wp_kses_post( $post_data['offer_description'] ?? '' ),
            'offer_type' => sanitize_text_field( $post_data['offer_type'] ?? '' ),
            'offer_value' => floatval( $post_data['offer_value'] ?? 0 ),
            'minimum_amount' => floatval( $post_data['minimum_amount'] ?? 0 ),
            'maximum_amount' => floatval( $post_data['maximum_amount'] ?? 0 ),
            'featured_image_id' => intval( $post_data['featured_image_id'] ?? 0 ),
            'gallery_images' => array_map( 'intval', $post_data['gallery_images'] ?? [] ),
            'selected_products' => [],
            'appearance' => []
        ];

        // Handle selected products
        if ( ! empty( $post_data['selected_products'] ) && is_array( $post_data['selected_products'] ) ) {
            foreach ( $post_data['selected_products'] as $product_id => $product_data ) {
                $sanitized['selected_products'][] = [
                    'id' => intval( $product_id ),
                    'name' => sanitize_text_field( $product_data['name'] ?? '' ),
                    'quantity' => intval( $product_data['quantity'] ?? 1 )
                ];
            }
        }

        // Handle appearance settings
        if ( ! empty( $post_data['appearance'] ) && is_array( $post_data['appearance'] ) ) {
            $appearance = $post_data['appearance'];
            $sanitized['appearance'] = [
                'background_color' => sanitize_hex_color( $appearance['background_color'] ?? '#ffffff' ),
                'text_color' => sanitize_hex_color( $appearance['text_color'] ?? '#333333' ),
                'accent_color' => sanitize_hex_color( $appearance['accent_color'] ?? '#e92d3b' ),
                'border_style' => sanitize_text_field( $appearance['border_style'] ?? 'solid' ),
                'border_width' => intval( $appearance['border_width'] ?? 1 ),
                'border_color' => sanitize_hex_color( $appearance['border_color'] ?? '#dddddd' ),
                'border_radius' => intval( $appearance['border_radius'] ?? 4 ),
                'layout' => sanitize_text_field( $appearance['layout'] ?? 'card' ),
                'position' => sanitize_text_field( $appearance['position'] ?? 'before_add_to_cart' ),
                'animation' => sanitize_text_field( $appearance['animation'] ?? 'none' ),
                'shadow' => sanitize_text_field( $appearance['shadow'] ?? 'light' )
            ];
        }

        return $sanitized;
    }

    /**
     * Generate HTML for offer preview
     */
    private function generate_offer_preview_html( $preview_data ) {
        $appearance = $preview_data['appearance'];
        
        // Get featured image
        $featured_image = '';
        if ( ! empty( $preview_data['featured_image_id'] ) ) {
            $featured_image = wp_get_attachment_image( $preview_data['featured_image_id'], 'medium' );
        }

        // Generate offer HTML based on layout
        ob_start();
        ?>
        <div class="woo-offers-preview-container">
            <div class="offer-preview-wrapper offer-layout-<?php echo esc_attr( $appearance['layout'] ?? 'card' ); ?>" 
                 style="
                     background-color: <?php echo esc_attr( $appearance['background_color'] ?? '#ffffff' ); ?>;
                     color: <?php echo esc_attr( $appearance['text_color'] ?? '#333333' ); ?>;
                     border: <?php echo esc_attr( $appearance['border_width'] ?? 1 ); ?>px <?php echo esc_attr( $appearance['border_style'] ?? 'solid' ); ?> <?php echo esc_attr( $appearance['border_color'] ?? '#dddddd' ); ?>;
                     border-radius: <?php echo esc_attr( $appearance['border_radius'] ?? 4 ); ?>px;
                     <?php echo $this->generate_shadow_style( $appearance['shadow'] ?? 'light' ); ?>
                 ">
                
                <?php if ( $featured_image ): ?>
                    <div class="offer-image">
                        <?php echo $featured_image; ?>
                    </div>
                <?php endif; ?>
                
                <div class="offer-content">
                    <h3 class="offer-title" style="color: <?php echo esc_attr( $appearance['accent_color'] ?? '#e92d3b' ); ?>;">
                        <?php echo esc_html( $preview_data['offer_name'] ?: __( 'Your Offer Title', 'woo-offers' ) ); ?>
                    </h3>
                    
                    <?php if ( ! empty( $preview_data['offer_description'] ) ): ?>
                        <div class="offer-description">
                            <?php echo wp_kses_post( $preview_data['offer_description'] ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="offer-details">
                        <div class="offer-type">
                            <strong><?php _e( 'Type:', 'woo-offers' ); ?></strong>
                            <?php echo esc_html( ucfirst( str_replace( '_', ' ', $preview_data['offer_type'] ) ) ); ?>
                        </div>
                        
                        <?php if ( in_array( $preview_data['offer_type'], [ 'percentage', 'fixed', 'quantity' ] ) ): ?>
                            <div class="offer-value">
                                <strong><?php _e( 'Value:', 'woo-offers' ); ?></strong>
                                <?php if ( $preview_data['offer_type'] === 'percentage' ): ?>
                                    <?php echo esc_html( $preview_data['offer_value'] ); ?>%
                                <?php elseif ( $preview_data['offer_type'] === 'fixed' ): ?>
                                    <?php echo wc_price( $preview_data['offer_value'] ); ?>
                                <?php else: ?>
                                    <?php echo esc_html( $preview_data['offer_value'] ); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ( ! empty( $preview_data['selected_products'] ) ): ?>
                            <div class="offer-products">
                                <strong><?php _e( 'Products:', 'woo-offers' ); ?></strong>
                                <ul>
                                    <?php foreach ( $preview_data['selected_products'] as $product ): ?>
                                        <li><?php echo esc_html( $product['name'] ); ?> (<?php echo esc_html( $product['quantity'] ); ?>)</li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="offer-cta">
                        <button class="offer-button" 
                                style="
                                    background-color: <?php echo esc_attr( $appearance['accent_color'] ?? '#e92d3b' ); ?>;
                                    color: #ffffff;
                                    border: none;
                                    padding: 12px 24px;
                                    border-radius: <?php echo esc_attr( $appearance['border_radius'] ?? 4 ); ?>px;
                                ">
                            <?php _e( 'Claim This Offer', 'woo-offers' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .woo-offers-preview-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .offer-preview-wrapper {
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .offer-preview-wrapper.offer-layout-card {
            border-radius: 8px;
        }
        
        .offer-preview-wrapper.offer-layout-banner {
            border-radius: 0;
            padding: 15px 20px;
        }
        
        .offer-image {
            margin-bottom: 15px;
            text-align: center;
        }
        
        .offer-image img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        
        .offer-title {
            margin: 0 0 10px 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .offer-description {
            margin: 0 0 15px 0;
            line-height: 1.5;
        }
        
        .offer-details {
            margin: 15px 0;
            font-size: 14px;
        }
        
        .offer-details > div {
            margin: 8px 0;
        }
        
        .offer-products ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }
        
        .offer-cta {
            margin-top: 20px;
            text-align: center;
        }
        
        .offer-button {
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .offer-button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Generate CSS shadow style
     */
    private function generate_shadow_style( $shadow_type ) {
        $shadows = [
            'none' => 'box-shadow: none;',
            'light' => 'box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);',
            'medium' => 'box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);',
            'heavy' => 'box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);'
        ];

        return $shadows[ $shadow_type ] ?? $shadows['light'];
    }

    /**
     * Save offer from form submission
     */
    private function save_offer() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['woo_offers_nonce'] ?? '', 'woo_offers_save_offer' ) ) {
            $this->add_error_notice( __( 'Security check failed. Please try again.', 'woo-offers' ), true, true );
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            $this->add_error_notice( __( 'You do not have permission to save offers.', 'woo-offers' ), true, true );
            return;
        }

        // Get and sanitize form data
        $offer_id = intval( $_POST['offer_id'] ?? 0 );
        $offer_name = sanitize_text_field( $_POST['offer_name'] ?? '' );
        $offer_description = wp_kses_post( $_POST['offer_description'] ?? '' );
        $offer_type = sanitize_text_field( $_POST['offer_type'] ?? '' );
        $offer_value = floatval( $_POST['offer_value'] ?? 0 );
        $offer_status = sanitize_text_field( $_POST['offer_status'] ?? 'draft' );
        $usage_limit = intval( $_POST['usage_limit'] ?? 0 );
        $start_date = sanitize_text_field( $_POST['start_date'] ?? '' );
        $end_date = sanitize_text_field( $_POST['end_date'] ?? '' );
        $minimum_amount = floatval( $_POST['minimum_amount'] ?? 0 );
        $maximum_amount = floatval( $_POST['maximum_amount'] ?? 0 );
        
        // Handle appearance settings
        $appearance = $_POST['appearance'] ?? [];
        $appearance_data = [
            'background_color' => sanitize_hex_color( $appearance['background_color'] ?? '#ffffff' ),
            'text_color' => sanitize_hex_color( $appearance['text_color'] ?? '#333333' ),
            'accent_color' => sanitize_hex_color( $appearance['accent_color'] ?? '#e92d3b' ),
            'border_style' => sanitize_text_field( $appearance['border_style'] ?? 'solid' ),
            'border_width' => intval( $appearance['border_width'] ?? 1 ),
            'border_color' => sanitize_hex_color( $appearance['border_color'] ?? '#dddddd' ),
            'border_radius' => intval( $appearance['border_radius'] ?? 4 ),
            'layout' => sanitize_text_field( $appearance['layout'] ?? 'card' ),
            'position' => sanitize_text_field( $appearance['position'] ?? 'before_add_to_cart' ),
            'animation' => sanitize_text_field( $appearance['animation'] ?? 'none' ),
            'shadow' => sanitize_text_field( $appearance['shadow'] ?? 'light' )
        ];

        // Handle selected products
        $selected_products_raw = $_POST['selected_products'] ?? [];
        $selected_products = [];

        if ( ! empty( $selected_products_raw ) && is_array( $selected_products_raw ) ) {
            foreach ( $selected_products_raw as $product_id => $product_data ) {
                $product_id = intval( $product_id );
                $quantity = intval( $product_data['quantity'] ?? 1 );
                $name = sanitize_text_field( $product_data['name'] ?? '' );

                // Validate product exists
                $product = \wc_get_product( $product_id );
                if ( $product && $quantity > 0 ) {
                    $selected_products[] = [
                        'id' => $product_id,
                        'name' => $name ?: ( $product->get_name() ?: __( 'Product Name Not Available', 'woo-offers' ) ),
                        'quantity' => $quantity
                    ];
                }
            }
        }

        // Handle media data
        $featured_image_id = intval( $_POST['featured_image_id'] ?? 0 );
        $gallery_images_raw = $_POST['gallery_images'] ?? [];
        $gallery_images = [];

        if ( ! empty( $gallery_images_raw ) && is_array( $gallery_images_raw ) ) {
            foreach ( $gallery_images_raw as $image_id ) {
                $image_id = intval( $image_id );
                // Validate attachment exists
                if ( $image_id > 0 && wp_attachment_is_image( $image_id ) ) {
                    $gallery_images[] = $image_id;
                }
            }
        }

        // Validate required fields
        if ( empty( $offer_name ) ) {
            $this->add_error_notice( __( 'Offer name is required.', 'woo-offers' ), true, true );
            return;
        }

        if ( empty( $offer_type ) ) {
            $this->add_error_notice( __( 'Offer type is required.', 'woo-offers' ), true, true );
            return;
        }

        // Validate offer types that require values
        if ( in_array( $offer_type, [ 'percentage', 'fixed', 'quantity' ] ) && $offer_value <= 0 ) {
            $this->add_error_notice( __( 'Offer value is required for this offer type.', 'woo-offers' ), true, true );
            return;
        }

        // Validate percentage limits
        if ( $offer_type === 'percentage' && $offer_value > 100 ) {
            $this->add_error_notice( __( 'Percentage discount cannot exceed 100%.', 'woo-offers' ), true, true );
            return;
        }

        // Convert dates to MySQL format
        $formatted_start_date = null;
        $formatted_end_date = null;
        
        if ( ! empty( $start_date ) ) {
            $formatted_start_date = date( 'Y-m-d H:i:s', strtotime( $start_date ) );
        }
        
        if ( ! empty( $end_date ) ) {
            $formatted_end_date = date( 'Y-m-d H:i:s', strtotime( $end_date ) );
        }

        // Validate date logic
        if ( $formatted_start_date && $formatted_end_date && $formatted_start_date >= $formatted_end_date ) {
            $this->add_error_notice( __( 'End date must be after start date.', 'woo-offers' ), true, true );
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'woo_offers';

        // Prepare conditions with appearance data, products, and media
        $conditions = [
            'appearance' => $appearance_data,
            'products' => $selected_products,
            'featured_image_id' => $featured_image_id ?: null,
            'gallery_images' => $gallery_images,
            'minimum_amount' => $minimum_amount ?: null,
            'maximum_amount' => $maximum_amount ?: null
        ];

        // Prepare data for database
        $offer_data = [
            'name' => $offer_name,
            'description' => $offer_description,
            'type' => $offer_type,
            'value' => $offer_value,
            'status' => $offer_status,
            'usage_limit' => $usage_limit ?: null,
            'start_date' => $formatted_start_date,
            'end_date' => $formatted_end_date,
            'conditions' => json_encode( $conditions ),
            'updated_at' => current_time( 'mysql' )
        ];

        $offer_data_format = [
            '%s', // name
            '%s', // description
            '%s', // type
            '%f', // value
            '%s', // status
            '%d', // usage_limit
            '%s', // start_date
            '%s', // end_date
            '%s', // conditions
            '%s'  // updated_at
        ];

        // Insert or update offer
        if ( $offer_id > 0 ) {
            // Update existing offer
            $result = $wpdb->update(
                $table_name,
                $offer_data,
                [ 'id' => $offer_id ],
                $offer_data_format,
                [ '%d' ]
            );

            if ( $result !== false ) {
                $this->add_success_notice( 
                    __( 'Offer updated successfully!', 'woo-offers' ), 
                    true, 
                    true 
                );
                
                // Redirect to edit page to prevent resubmission
                wp_redirect( admin_url( 'admin.php?page=woo-offers-create&id=' . $offer_id ) );
                exit;
            } else {
                $this->add_error_notice( 
                    __( 'Failed to update offer. Please try again.', 'woo-offers' ), 
                    true, 
                    true 
                );
            }
        } else {
            // Insert new offer
            $offer_data['created_at'] = current_time( 'mysql' );
            $offer_data_format[] = '%s'; // created_at format

            $result = $wpdb->insert(
                $table_name,
                $offer_data,
                $offer_data_format
            );

            if ( $result !== false ) {
                $new_offer_id = $wpdb->insert_id;
                
                $this->add_success_notice( 
                    __( 'Offer created successfully!', 'woo-offers' ), 
                    true, 
                    true 
                );

                // Redirect to edit page
                wp_redirect( admin_url( 'admin.php?page=woo-offers-create&id=' . $new_offer_id ) );
                exit;
            } else {
                $this->add_error_notice( 
                    __( 'Failed to create offer. Please try again.', 'woo-offers' ), 
                    true, 
                    true 
                );
            }
        }
    }

    /**
     * Register metaboxes for offer edit screen
     */
    public function register_offer_metaboxes() {
        // Only register metaboxes on the create/edit offer page
        $current_screen = get_current_screen();
        $current_page = $_GET['page'] ?? '';
        
        // Check if we're on the create offer page (used for both create and edit)
        if ( ! $current_screen || $current_page !== 'woo-offers-create' ) {
            return;
        }
        
        add_meta_box(
            'woo_offers_general',
            __( 'General Settings', 'woo-offers' ),
            [ $this, 'render_general_metabox' ],
            'woo_offers_edit',
            'normal',
            'default'
        );

        add_meta_box(
            'woo_offers_products',
            __( 'Products', 'woo-offers' ),
            [ $this, 'render_products_metabox' ],
            'woo_offers_edit',
            'normal',
            'default'
        );

        add_meta_box(
            'woo_offers_media',
            __( 'Media & Preview', 'woo-offers' ),
            [ $this, 'render_media_metabox' ],
            'woo_offers_edit',
            'normal',
            'default'
        );

        add_meta_box(
            'woo_offers_appearance',
            __( 'Appearance', 'woo-offers' ),
            [ $this, 'render_appearance_metabox' ],
            'woo_offers_edit',
            'side',
            'default'
        );
    }

    /**
     * Render General Settings metabox
     */
    public function render_general_metabox( $post, $metabox ) {
        // Get offer data
        $offer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $offer_data = [];

        if ( $offer_id > 0 ) {
            global $wpdb;
            $offer = $wpdb->get_row( 
                $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woo_offers WHERE id = %d", $offer_id ) 
            );
            
            if ( $offer ) {
                $offer_data = [
                    'type' => $offer->type,
                    'value' => $offer->value,
                    'usage_limit' => $offer->usage_limit,
                    'minimum_amount' => $offer->minimum_amount ?? '',
                    'maximum_amount' => $offer->maximum_amount ?? ''
                ];
            }
        }

        // Include the general metabox template
        include WOO_OFFERS_PLUGIN_PATH . 'templates/admin/metaboxes/general.php';
    }

    /**
     * Render Appearance metabox
     */
    public function render_appearance_metabox( $post, $metabox ) {
        // Get offer data
        $offer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $offer_data = [];

        if ( $offer_id > 0 ) {
            global $wpdb;
            $offer = $wpdb->get_row( 
                $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woo_offers WHERE id = %d", $offer_id ) 
            );
            
            if ( $offer ) {
                $conditions = json_decode( $offer->conditions, true ) ?? [];
                $offer_data = [
                    'appearance' => $conditions['appearance'] ?? []
                ];
            }
        }

        // Include the appearance metabox template
        include WOO_OFFERS_PLUGIN_PATH . 'templates/admin/metaboxes/appearance.php';
    }

    /**
     * Render Products metabox
     */
    public function render_products_metabox( $post, $metabox ) {
        // Get offer data
        $offer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $offer_data = [];

        if ( $offer_id > 0 ) {
            global $wpdb;
            $offer = $wpdb->get_row( 
                $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woo_offers WHERE id = %d", $offer_id ) 
            );
            
            if ( $offer ) {
                $conditions = json_decode( $offer->conditions, true ) ?? [];
                $offer_data = [
                    'products' => $conditions['products'] ?? []
                ];
            }
        }

        // Include the products metabox template
        include WOO_OFFERS_PLUGIN_PATH . 'templates/admin/metaboxes/products.php';
    }

    /**
     * Render Media metabox
     */
    public function render_media_metabox( $post, $metabox ) {
        // Get offer data
        $offer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $offer_data = [];

        if ( $offer_id > 0 ) {
            global $wpdb;
            $offer = $wpdb->get_row( 
                $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woo_offers WHERE id = %d", $offer_id ) 
            );
            
            if ( $offer ) {
                $conditions = json_decode( $offer->conditions, true ) ?? [];
                $offer_data = [
                    'featured_image_id' => $conditions['featured_image_id'] ?? 0,
                    'gallery_images' => $conditions['gallery_images'] ?? []
                ];
            }
        }

        // Include the media metabox template
        include WOO_OFFERS_PLUGIN_PATH . 'templates/admin/metaboxes/media.php';
    }

    /**
     * Export offers as CSV
     */
    public function export_offers_csv() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['export_csv_nonce'] ?? '', 'woo_offers_export_csv' ) ) {
            $this->add_error_notice( __( 'Security check failed. Please try again.', 'woo-offers' ), true, true );
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have sufficient permissions to export offers.', 'woo-offers' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'woo_offers';

        // Get all offers
        $offers = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY created_at DESC" );

        if ( empty( $offers ) ) {
            $this->add_error_notice( __( 'No offers found to export.', 'woo-offers' ), true, true );
            wp_redirect( admin_url( 'admin.php?page=woo-offers-import-export' ) );
            exit;
        }

        // Set headers for CSV download
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=woo-offers-export-' . date( 'Y-m-d' ) . '.csv' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        // Create output stream
        $output = fopen( 'php://output', 'w' );

        // Add CSV headers
        fputcsv( $output, [
            'ID',
            'Title',
            'Description',
            'Type',
            'Value',
            'Status',
            'Usage Limit',
            'Start Date',
            'End Date',
            'Products',
            'Minimum Amount',
            'Maximum Amount',
            'Created Date',
            'Updated Date'
        ] );

        // Add offer data
        foreach ( $offers as $offer ) {
            $conditions = json_decode( $offer->conditions, true ) ?? [];
            $products = $conditions['products'] ?? [];
            $product_names = [];

            // Get product names
            foreach ( $products as $product ) {
                $product_names[] = $product['name'] . ' (ID: ' . $product['id'] . ', Qty: ' . $product['quantity'] . ')';
            }

            fputcsv( $output, [
                $offer->id,
                $offer->name,
                $offer->description,
                $offer->type,
                $offer->value,
                $offer->status,
                $offer->usage_limit ?: '',
                $offer->start_date ?: '',
                $offer->end_date ?: '',
                implode( '; ', $product_names ),
                $conditions['minimum_amount'] ?? '',
                $conditions['maximum_amount'] ?? '',
                $offer->created_at,
                $offer->updated_at
            ] );
        }

        fclose( $output );
        exit;
    }

    /**
     * Export offers as JSON
     */
    public function export_offers_json() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['export_json_nonce'] ?? '', 'woo_offers_export_json' ) ) {
            $this->add_error_notice( __( 'Security check failed. Please try again.', 'woo-offers' ), true, true );
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have sufficient permissions to export offers.', 'woo-offers' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'woo_offers';

        // Get all offers
        $offers = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY created_at DESC" );

        if ( empty( $offers ) ) {
            $this->add_error_notice( __( 'No offers found to export.', 'woo-offers' ), true, true );
            wp_redirect( admin_url( 'admin.php?page=woo-offers-import-export' ) );
            exit;
        }

        // Prepare export data
        $export_data = [
            'version' => WOO_OFFERS_VERSION,
            'export_date' => current_time( 'Y-m-d H:i:s' ),
            'site_url' => get_site_url(),
            'offers' => []
        ];

        foreach ( $offers as $offer ) {
            $conditions = json_decode( $offer->conditions, true ) ?? [];
            
            $export_data['offers'][] = [
                'id' => $offer->id,
                'name' => $offer->name,
                'description' => $offer->description,
                'type' => $offer->type,
                'value' => $offer->value,
                'status' => $offer->status,
                'usage_limit' => $offer->usage_limit,
                'start_date' => $offer->start_date,
                'end_date' => $offer->end_date,
                'conditions' => $conditions,
                'created_at' => $offer->created_at,
                'updated_at' => $offer->updated_at
            ];
        }

        // Set headers for JSON download
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=woo-offers-export-' . date( 'Y-m-d' ) . '.json' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        echo json_encode( $export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
        exit;
    }

    /**
     * Handle CSV import
     */
    public function handle_import_csv() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['import_csv_nonce'] ?? '', 'woo_offers_import_csv' ) ) {
            $this->add_error_notice( __( 'Security check failed. Please try again.', 'woo-offers' ), true, true );
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have sufficient permissions to import offers.', 'woo-offers' ) );
        }

        // Check if file was uploaded
        if ( ! isset( $_FILES['import_file'] ) || empty( $_FILES['import_file']['tmp_name'] ) ) {
            $this->add_error_notice( __( 'No file uploaded. Please select a file to import.', 'woo-offers' ), true, true );
            return;
        }

        $file = $_FILES['import_file'];
        $import_mode = sanitize_text_field( $_POST['import_mode'] ?? 'create' );

        // Validate file
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            $this->add_error_notice( __( 'File upload error. Please try again.', 'woo-offers' ), true, true );
            return;
        }

        // Check file size (2MB limit)
        if ( $file['size'] > 2 * 1024 * 1024 ) {
            $this->add_error_notice( __( 'File is too large. Maximum size is 2MB.', 'woo-offers' ), true, true );
            return;
        }

        // Check file type
        $file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        if ( ! in_array( $file_extension, [ 'csv', 'json' ] ) ) {
            $this->add_error_notice( __( 'Invalid file type. Please upload a CSV or JSON file.', 'woo-offers' ), true, true );
            return;
        }

        // Process the file based on extension
        if ( $file_extension === 'csv' ) {
            $result = $this->process_csv_import( $file['tmp_name'], $import_mode );
        } else {
            $result = $this->process_json_import( $file['tmp_name'], $import_mode );
        }

        // Handle results
        if ( $result['success'] ) {
            if ( ! empty( $result['errors'] ) ) {
                $this->add_warning_notice( 
                    sprintf( 
                        __( '%d offers imported successfully, but %d had errors.', 'woo-offers' ), 
                        $result['imported'], 
                        count( $result['errors'] ) 
                    ), 
                    true, 
                    true 
                );
            } else {
                $this->add_success_notice( 
                    sprintf( 
                        _n( '%d offer imported successfully!', '%d offers imported successfully!', $result['imported'], 'woo-offers' ), 
                        $result['imported'] 
                    ), 
                    true, 
                    true 
                );
            }
        } else {
            $this->add_error_notice( 
                __( 'Import failed: ', 'woo-offers' ) . $result['message'], 
                true, 
                true 
            );
        }
    }

    /**
     * Process CSV import
     */
    private function process_csv_import( $file_path, $import_mode ) {
        $handle = fopen( $file_path, 'r' );
        
        if ( $handle === false ) {
            return [
                'success' => false,
                'message' => __( 'Could not open file for reading.', 'woo-offers' )
            ];
        }

        // Get headers
        $headers = fgetcsv( $handle );
        if ( empty( $headers ) ) {
            fclose( $handle );
            return [
                'success' => false,
                'message' => __( 'Invalid CSV format. No headers found.', 'woo-offers' )
            ];
        }

        // Map headers to array indices
        $header_map = array_flip( array_map( 'strtolower', $headers ) );
        $required_headers = [ 'title', 'type' ];
        
        // Check for required headers
        foreach ( $required_headers as $required ) {
            if ( ! isset( $header_map[ $required ] ) ) {
                fclose( $handle );
                return [
                    'success' => false,
                    'message' => sprintf( __( 'Missing required column: %s', 'woo-offers' ), $required )
                ];
            }
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'woo_offers';
        
        $imported = 0;
        $errors = [];

        // Process rows
        while ( ( $data = fgetcsv( $handle ) ) !== false ) {
            try {
                // Skip empty rows
                if ( empty( array_filter( $data ) ) ) {
                    continue;
                }

                // Extract data
                $offer_name = $data[ $header_map['title'] ] ?? '';
                $offer_type = $data[ $header_map['type'] ] ?? '';
                $offer_value = floatval( $data[ $header_map['value'] ] ?? 0 );
                $offer_status = $data[ $header_map['status'] ] ?? 'active';
                $offer_description = $data[ $header_map['description'] ] ?? '';

                // Validate required fields
                if ( empty( $offer_name ) || empty( $offer_type ) ) {
                    $errors[] = sprintf( __( 'Row %d: Missing required data (title or type)', 'woo-offers' ), $imported + 1 );
                    continue;
                }

                // Prepare offer data
                $offer_data = [
                    'name' => sanitize_text_field( $offer_name ),
                    'description' => wp_kses_post( $offer_description ),
                    'type' => sanitize_text_field( $offer_type ),
                    'value' => $offer_value,
                    'status' => sanitize_text_field( $offer_status ),
                    'usage_limit' => null,
                    'start_date' => null,
                    'end_date' => null,
                    'conditions' => json_encode( [] ),
                    'created_at' => current_time( 'mysql' ),
                    'updated_at' => current_time( 'mysql' )
                ];

                // Insert offer
                $result = $wpdb->insert(
                    $table_name,
                    $offer_data,
                    [ '%s', '%s', '%s', '%f', '%s', '%d', '%s', '%s', '%s', '%s', '%s' ]
                );

                if ( $result !== false ) {
                    $imported++;
                } else {
                    $errors[] = sprintf( __( 'Row %d: Database error', 'woo-offers' ), $imported + 1 );
                }

            } catch ( Exception $e ) {
                $errors[] = sprintf( __( 'Row %d: %s', 'woo-offers' ), $imported + 1, $e->getMessage() );
            }
        }

        fclose( $handle );

        return [
            'success' => true,
            'imported' => $imported,
            'errors' => $errors
        ];
    }

    /**
     * Process JSON import
     */
    private function process_json_import( $file_path, $import_mode ) {
        $json_content = file_get_contents( $file_path );
        $data = json_decode( $json_content, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return [
                'success' => false,
                'message' => __( 'Invalid JSON format.', 'woo-offers' )
            ];
        }

        if ( ! isset( $data['offers'] ) || ! is_array( $data['offers'] ) ) {
            return [
                'success' => false,
                'message' => __( 'Invalid JSON structure. No offers found.', 'woo-offers' )
            ];
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'woo_offers';
        
        $imported = 0;
        $errors = [];

        foreach ( $data['offers'] as $index => $offer_data ) {
            try {
                // Validate required fields
                if ( empty( $offer_data['name'] ) || empty( $offer_data['type'] ) ) {
                    $errors[] = sprintf( __( 'Offer %d: Missing required data (name or type)', 'woo-offers' ), $index + 1 );
                    continue;
                }

                // Prepare data for database
                $db_data = [
                    'name' => sanitize_text_field( $offer_data['name'] ),
                    'description' => wp_kses_post( $offer_data['description'] ?? '' ),
                    'type' => sanitize_text_field( $offer_data['type'] ),
                    'value' => floatval( $offer_data['value'] ?? 0 ),
                    'status' => sanitize_text_field( $offer_data['status'] ?? 'active' ),
                    'usage_limit' => ! empty( $offer_data['usage_limit'] ) ? intval( $offer_data['usage_limit'] ) : null,
                    'start_date' => ! empty( $offer_data['start_date'] ) ? $offer_data['start_date'] : null,
                    'end_date' => ! empty( $offer_data['end_date'] ) ? $offer_data['end_date'] : null,
                    'conditions' => json_encode( $offer_data['conditions'] ?? [] ),
                    'created_at' => current_time( 'mysql' ),
                    'updated_at' => current_time( 'mysql' )
                ];

                // Insert offer
                $result = $wpdb->insert(
                    $table_name,
                    $db_data,
                    [ '%s', '%s', '%s', '%f', '%s', '%d', '%s', '%s', '%s', '%s', '%s' ]
                );

                if ( $result !== false ) {
                    $imported++;
                } else {
                    $errors[] = sprintf( __( 'Offer %d: Database error', 'woo-offers' ), $index + 1 );
                }

            } catch ( Exception $e ) {
                $errors[] = sprintf( __( 'Offer %d: %s', 'woo-offers' ), $index + 1, $e->getMessage() );
            }
        }

        return [
            'success' => true,
            'imported' => $imported,
            'errors' => $errors
        ];
    }

    /**
     * Add contextual help tabs to admin pages
     */
    public function add_contextual_help() {
        $screen = get_current_screen();
        
        // Only add to plugin pages
        if ( ! $screen || strpos( $screen->id, 'woo-offers' ) === false ) {
            return;
        }

        // Add help based on current page
        switch ( $screen->id ) {
            case 'toplevel_page_woo-offers':
                $this->add_dashboard_help( $screen );
                break;
            case 'woo-offers_page_woo-offers-offers':
                $this->add_offers_help( $screen );
                break;
            case 'woo-offers_page_woo-offers-create':
                $this->add_create_offer_help( $screen );
                break;
            case 'woo-offers_page_woo-offers-analytics':
                $this->add_analytics_help( $screen );
                break;
            case 'woo-offers_page_woo-offers-settings':
                $this->add_settings_help( $screen );
                break;
            case 'woo-offers_page_woo-offers-import-export':
                $this->add_import_export_help( $screen );
                break;
            case 'woo-offers_page_woo-offers-ab-tests':
                $this->add_ab_tests_help( $screen );
                break;
            case 'woo-offers_page_woo-offers-help':
                $this->add_help_documentation_help( $screen );
                break;
        }

        // Add common help sidebar to all plugin pages
        $this->add_help_sidebar( $screen );
    }

    /**
     * Add dashboard help tabs
     */
    private function add_dashboard_help( $screen ) {
        $screen->add_help_tab( [
            'id' => 'woo-offers-dashboard-overview',
            'title' => __( 'Overview', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Dashboard Overview', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'The dashboard provides a comprehensive overview of your offers performance and quick access to common actions.', 'woo-offers' ) . '</p>' .
                '<p>' . __( 'Use this page to monitor your offers effectiveness and make data-driven decisions about your marketing strategies.', 'woo-offers' ) . '</p>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-dashboard-widgets',
            'title' => __( 'Dashboard Widgets', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Available Widgets', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'The dashboard contains the following informational widgets:', 'woo-offers' ) . '</p>' .
                '<ul>' .
                    '<li><strong>' . __( 'Performance Summary', 'woo-offers' ) . '</strong>: ' . __( 'Shows overall conversion rates, revenue generated, and key metrics.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Recent Offers', 'woo-offers' ) . '</strong>: ' . __( 'Displays your most recently created and modified offers.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Quick Stats', 'woo-offers' ) . '</strong>: ' . __( 'Shows key performance indicators and trends.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Active Campaigns', 'woo-offers' ) . '</strong>: ' . __( 'Overview of currently running promotional campaigns.', 'woo-offers' ) . '</li>' .
                '</ul>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-dashboard-actions',
            'title' => __( 'Quick Actions', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Getting Started', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'Use these quick actions to manage your offers efficiently:', 'woo-offers' ) . '</p>' .
                '<ul>' .
                    '<li><strong>' . __( 'Create New Offer', 'woo-offers' ) . '</strong>: ' . __( 'Start building a new promotional offer.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'View All Offers', 'woo-offers' ) . '</strong>: ' . __( 'Browse and manage your existing offers.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Analytics', 'woo-offers' ) . '</strong>: ' . __( 'Dive deep into performance metrics and insights.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Settings', 'woo-offers' ) . '</strong>: ' . __( 'Configure plugin behavior and appearance.', 'woo-offers' ) . '</li>' .
                '</ul>'
        ] );
    }

    /**
     * Add offers management help tabs
     */
    private function add_offers_help( $screen ) {
        $screen->add_help_tab( [
            'id' => 'woo-offers-offers-overview',
            'title' => __( 'Managing Offers', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Offers Management', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'This page displays all your promotional offers in a sortable and filterable table format.', 'woo-offers' ) . '</p>' .
                '<p>' . __( 'You can perform bulk actions, search for specific offers, and quickly edit or delete existing offers.', 'woo-offers' ) . '</p>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-offers-actions',
            'title' => __( 'Available Actions', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Offer Actions', 'woo-offers' ) . '</h3>' .
                '<ul>' .
                    '<li><strong>' . __( 'Edit', 'woo-offers' ) . '</strong>: ' . __( 'Modify offer settings, appearance, and conditions.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Duplicate', 'woo-offers' ) . '</strong>: ' . __( 'Create a copy of an existing offer as a starting point.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Enable/Disable', 'woo-offers' ) . '</strong>: ' . __( 'Quickly activate or deactivate offers.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Delete', 'woo-offers' ) . '</strong>: ' . __( 'Permanently remove offers (cannot be undone).', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'View Analytics', 'woo-offers' ) . '</strong>: ' . __( 'See performance metrics for individual offers.', 'woo-offers' ) . '</li>' .
                '</ul>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-offers-filtering',
            'title' => __( 'Filtering & Search', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Finding Offers', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'Use the following tools to find specific offers:', 'woo-offers' ) . '</p>' .
                '<ul>' .
                    '<li><strong>' . __( 'Status Filter', 'woo-offers' ) . '</strong>: ' . __( 'Show only active, inactive, or scheduled offers.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Type Filter', 'woo-offers' ) . '</strong>: ' . __( 'Filter by offer type (percentage, fixed amount, BOGO, etc.).', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Date Range', 'woo-offers' ) . '</strong>: ' . __( 'Show offers created within a specific time period.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Search', 'woo-offers' ) . '</strong>: ' . __( 'Search by offer name, description, or product names.', 'woo-offers' ) . '</li>' .
                '</ul>'
        ] );
    }

    /**
     * Add create/edit offer help tabs
     */
    private function add_create_offer_help( $screen ) {
        $screen->add_help_tab( [
            'id' => 'woo-offers-create-overview',
            'title' => __( 'Creating Offers', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Offer Creation', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'Create compelling promotional offers using our intuitive form builder.', 'woo-offers' ) . '</p>' .
                '<p>' . __( 'Each offer consists of general settings, product selection, appearance customization, and media elements.', 'woo-offers' ) . '</p>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-create-settings',
            'title' => __( 'Offer Settings', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'General Settings', 'woo-offers' ) . '</h3>' .
                '<ul>' .
                    '<li><strong>' . __( 'Offer Name', 'woo-offers' ) . '</strong>: ' . __( 'Internal name for your reference (not shown to customers).', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Offer Type', 'woo-offers' ) . '</strong>: ' . __( 'Choose discount type: percentage, fixed amount, BOGO, free shipping, etc.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Discount Value', 'woo-offers' ) . '</strong>: ' . __( 'The discount amount (percentage or fixed value).', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Usage Limits', 'woo-offers' ) . '</strong>: ' . __( 'Restrict how many times this offer can be used.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Schedule', 'woo-offers' ) . '</strong>: ' . __( 'Set start and end dates for automatic activation.', 'woo-offers' ) . '</li>' .
                '</ul>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-create-products',
            'title' => __( 'Product Selection', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Selecting Products', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'Choose which products this offer applies to using our product selector.', 'woo-offers' ) . '</p>' .
                '<ul>' .
                    '<li><strong>' . __( 'Search Products', 'woo-offers' ) . '</strong>: ' . __( 'Type to search and select individual products.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Product Categories', 'woo-offers' ) . '</strong>: ' . __( 'Apply to entire product categories.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'All Products', 'woo-offers' ) . '</strong>: ' . __( 'Apply the offer to your entire catalog.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Quantities', 'woo-offers' ) . '</strong>: ' . __( 'Set minimum quantities required for the offer.', 'woo-offers' ) . '</li>' .
                '</ul>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-create-appearance',
            'title' => __( 'Appearance & Display', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Customizing Appearance', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'Control how your offer appears to customers on the frontend.', 'woo-offers' ) . '</p>' .
                '<ul>' .
                    '<li><strong>' . __( 'Colors', 'woo-offers' ) . '</strong>: ' . __( 'Customize background, text, and accent colors.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Layout', 'woo-offers' ) . '</strong>: ' . __( 'Choose between card, banner, or popup display styles.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Position', 'woo-offers' ) . '</strong>: ' . __( 'Select where the offer appears on product pages.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Animation', 'woo-offers' ) . '</strong>: ' . __( 'Add subtle animations to catch customer attention.', 'woo-offers' ) . '</li>' .
                '</ul>'
        ] );
    }

    /**
     * Add analytics help tabs
     */
    private function add_analytics_help( $screen ) {
        $screen->add_help_tab( [
            'id' => 'woo-offers-analytics-overview',
            'title' => __( 'Analytics Overview', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Performance Analytics', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'Track the performance of your offers with comprehensive analytics and reporting.', 'woo-offers' ) . '</p>' .
                '<p>' . __( 'Monitor conversion rates, revenue impact, and customer engagement to optimize your promotional strategies.', 'woo-offers' ) . '</p>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-analytics-metrics',
            'title' => __( 'Key Metrics', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Understanding Metrics', 'woo-offers' ) . '</h3>' .
                '<ul>' .
                    '<li><strong>' . __( 'Impressions', 'woo-offers' ) . '</strong>: ' . __( 'How many times your offers were displayed to customers.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Conversion Rate', 'woo-offers' ) . '</strong>: ' . __( 'Percentage of visitors who used an offer.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Revenue Impact', 'woo-offers' ) . '</strong>: ' . __( 'Total revenue generated through offers.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Average Order Value', 'woo-offers' ) . '</strong>: ' . __( 'How offers affect customer purchase amounts.', 'woo-offers' ) . '</li>' .
                '</ul>'
        ] );
    }

    /**
     * Add settings help tabs
     */
    private function add_settings_help( $screen ) {
        $screen->add_help_tab( [
            'id' => 'woo-offers-settings-overview',
            'title' => __( 'Settings Overview', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Plugin Configuration', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'Configure global plugin settings to control behavior, appearance, and performance.', 'woo-offers' ) . '</p>' .
                '<p>' . __( 'Settings are organized into logical groups: General, Display, and Performance.', 'woo-offers' ) . '</p>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-settings-general',
            'title' => __( 'General Settings', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'General Configuration', 'woo-offers' ) . '</h3>' .
                '<ul>' .
                    '<li><strong>' . __( 'Enable Analytics', 'woo-offers' ) . '</strong>: ' . __( 'Turn on/off performance tracking and reporting.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Enable A/B Testing', 'woo-offers' ) . '</strong>: ' . __( 'Allow creation and management of A/B tests.', 'woo-offers' ) . '</li>' .
                '</ul>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-settings-display',
            'title' => __( 'Display Settings', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Display Configuration', 'woo-offers' ) . '</h3>' .
                '<ul>' .
                    '<li><strong>' . __( 'Primary Color', 'woo-offers' ) . '</strong>: ' . __( 'Default color scheme for new offers.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Default Position', 'woo-offers' ) . '</strong>: ' . __( 'Where offers appear on product pages by default.', 'woo-offers' ) . '</li>' .
                '</ul>'
        ] );
    }

    /**
     * Add import/export help tabs
     */
    private function add_import_export_help( $screen ) {
        $screen->add_help_tab( [
            'id' => 'woo-offers-import-export-overview',
            'title' => __( 'Import/Export Overview', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Data Management', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'Backup your offers data or transfer it between different websites using our import/export tools.', 'woo-offers' ) . '</p>' .
                '<p>' . __( 'Choose between CSV format for spreadsheet compatibility or JSON for complete data preservation.', 'woo-offers' ) . '</p>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-export-help',
            'title' => __( 'Exporting Data', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Export Formats', 'woo-offers' ) . '</h3>' .
                '<ul>' .
                    '<li><strong>' . __( 'CSV Export', 'woo-offers' ) . '</strong>: ' . __( 'Basic offer data suitable for spreadsheet applications.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'JSON Export', 'woo-offers' ) . '</strong>: ' . __( 'Complete offer data including all settings and configurations.', 'woo-offers' ) . '</li>' .
                '</ul>' .
                '<p>' . __( 'Exported files are automatically downloaded to your computer.', 'woo-offers' ) . '</p>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-import-help',
            'title' => __( 'Importing Data', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Import Guidelines', 'woo-offers' ) . '</h3>' .
                '<ul>' .
                    '<li><strong>' . __( 'File Size Limit', 'woo-offers' ) . '</strong>: ' . __( 'Maximum 2MB per file.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Supported Formats', 'woo-offers' ) . '</strong>: ' . __( 'CSV and JSON files.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Required Fields', 'woo-offers' ) . '</strong>: ' . __( 'Title and Type columns are mandatory for CSV imports.', 'woo-offers' ) . '</li>' .
                '</ul>' .
                '<p>' . __( 'Download the sample CSV file to see the expected format before importing your data.', 'woo-offers' ) . '</p>'
        ] );
    }

    /**
     * Add A/B tests help tabs
     */
    private function add_ab_tests_help( $screen ) {
        $screen->add_help_tab( [
            'id' => 'woo-offers-ab-tests-overview',
            'title' => __( 'A/B Testing Overview', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'A/B Testing', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'Test different versions of your offers to determine which performs better.', 'woo-offers' ) . '</p>' .
                '<p>' . __( 'Create controlled experiments to optimize your conversion rates and revenue.', 'woo-offers' ) . '</p>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-ab-tests-setup',
            'title' => __( 'Setting Up Tests', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Test Configuration', 'woo-offers' ) . '</h3>' .
                '<ul>' .
                    '<li><strong>' . __( 'Control Group', 'woo-offers' ) . '</strong>: ' . __( 'The original version of your offer.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Variation', 'woo-offers' ) . '</strong>: ' . __( 'The alternative version you want to test.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Traffic Split', 'woo-offers' ) . '</strong>: ' . __( 'How to divide visitors between versions.', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Success Metrics', 'woo-offers' ) . '</strong>: ' . __( 'What you want to measure (clicks, conversions, revenue).', 'woo-offers' ) . '</li>' .
                '</ul>'
        ] );
    }

    /**
     * Add help documentation page help tabs
     */
    private function add_help_documentation_help( $screen ) {
        $screen->add_help_tab( [
            'id' => 'woo-offers-help-overview',
            'title' => __( 'Help Overview', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Help & Documentation', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'This comprehensive help section contains everything you need to use Woo Offers effectively.', 'woo-offers' ) . '</p>' .
                '<p>' . __( 'Navigate through different sections using the tabs to find specific information about features and functionality.', 'woo-offers' ) . '</p>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-help-navigation',
            'title' => __( 'Using This Help System', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'How to Navigate', 'woo-offers' ) . '</h3>' .
                '<p>' . __( 'The help documentation is organized into logical sections:', 'woo-offers' ) . '</p>' .
                '<ul>' .
                    '<li><strong>' . __( 'Getting Started', 'woo-offers' ) . '</strong>: ' . __( 'Initial setup and basic concepts', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Creating Offers', 'woo-offers' ) . '</strong>: ' . __( 'Step-by-step guide to building effective offers', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Product Management', 'woo-offers' ) . '</strong>: ' . __( 'Selecting and organizing products for offers', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Customization', 'woo-offers' ) . '</strong>: ' . __( 'Design and appearance optimization', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Analytics & Testing', 'woo-offers' ) . '</strong>: ' . __( 'Performance tracking and A/B testing', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'Troubleshooting', 'woo-offers' ) . '</strong>: ' . __( 'Solutions for common issues', 'woo-offers' ) . '</li>' .
                    '<li><strong>' . __( 'FAQ', 'woo-offers' ) . '</strong>: ' . __( 'Frequently asked questions and answers', 'woo-offers' ) . '</li>' .
                '</ul>'
        ] );

        $screen->add_help_tab( [
            'id' => 'woo-offers-help-tips',
            'title' => __( 'Quick Tips', 'woo-offers' ),
            'content' => 
                '<h3>' . __( 'Getting the Most from Help', 'woo-offers' ) . '</h3>' .
                '<ul>' .
                    '<li>' . __( 'Use Ctrl+F (Cmd+F on Mac) to search for specific terms on any help page', 'woo-offers' ) . '</li>' .
                    '<li>' . __( 'Bookmark frequently referenced sections for quick access', 'woo-offers' ) . '</li>' .
                    '<li>' . __( 'Check the troubleshooting section first if you encounter issues', 'woo-offers' ) . '</li>' .
                    '<li>' . __( 'Review the FAQ section for quick answers to common questions', 'woo-offers' ) . '</li>' .
                    '<li>' . __( 'Use the contextual help tabs on other admin pages for page-specific guidance', 'woo-offers' ) . '</li>' .
                '</ul>'
        ] );
    }

    /**
     * Add common help sidebar to all plugin pages
     */
    private function add_help_sidebar( $screen ) {
        $screen->set_help_sidebar(
            '<p><strong>' . __( 'For more information:', 'woo-offers' ) . '</strong></p>' .
            '<p><a href="https://woooffers.com/docs" target="_blank">' . __( 'Documentation', 'woo-offers' ) . ' <span class="dashicons dashicons-external"></span></a></p>' .
            '<p><a href="https://woooffers.com/support" target="_blank">' . __( 'Support Forums', 'woo-offers' ) . ' <span class="dashicons dashicons-external"></span></a></p>' .
            '<p><a href="https://woooffers.com/tutorials" target="_blank">' . __( 'Video Tutorials', 'woo-offers' ) . ' <span class="dashicons dashicons-external"></span></a></p>' .
            '<p><a href="https://woooffers.com/contact" target="_blank">' . __( 'Contact Support', 'woo-offers' ) . ' <span class="dashicons dashicons-external"></span></a></p>' .
            '<hr>' .
            '<p><strong>' . __( 'Quick Tips:', 'woo-offers' ) . '</strong></p>' .
            '<p>' . __( '• Start with simple percentage discounts', 'woo-offers' ) . '</p>' .
            '<p>' . __( '• Test different positions and colors', 'woo-offers' ) . '</p>' .
            '<p>' . __( '• Monitor analytics regularly', 'woo-offers' ) . '</p>' .
            '<p>' . __( '• Use A/B tests for optimization', 'woo-offers' ) . '</p>'
        );
    }

    /**
     * Getting Started page content
     */
    public function getting_started_page() {
        $this->render_admin_page( 'getting-started' );
    }

    /**
     * AJAX handler for dismissing getting started guide
     */
    public function dismiss_getting_started_ajax() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'woo_offers_getting_started' ) ) {
            wp_die( __( 'Security check failed', 'woo-offers' ) );
        }

        // Check user capability
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'Insufficient permissions', 'woo-offers' ) );
        }

        // Mark getting started as completed
        update_option( 'woo_offers_getting_started_completed', true );

        wp_send_json_success( [
            'message' => __( 'Getting started guide completed!', 'woo-offers' ),
            'redirect' => admin_url( 'admin.php?page=' . self::MENU_SLUG )
        ] );
    }

    /**
     * Format product data safely to prevent null values
     */
    private function format_product_data( $product ) {
        if ( ! $product || ! is_object( $product ) ) {
            return null;
        }

        // Get data with null safety
        $name = $product->get_name();
        $sku = $product->get_sku();
        $price_html = $product->get_price_html();
        $type = $product->get_type();
        $image = $product->get_image( 'thumbnail' );
        $status = $product->get_status();
        $stock_status = $product->get_stock_status();

        // Ensure all values are strings to prevent null errors
        return [
            'id' => intval( $product->get_id() ),
            'name' => ! empty( $name ) ? (string) $name : '',
            'sku' => ! empty( $sku ) ? (string) $sku : '',
            'price' => ! empty( $price_html ) ? (string) $price_html : (string) __( 'Price not available', 'woo-offers' ),
            'type' => ! empty( $type ) ? (string) \wc_get_product_type_name( $type ) : (string) __( 'Unknown', 'woo-offers' ),
            'image' => ! empty( $image ) ? (string) $image : '',
            'status' => ! empty( $status ) ? (string) $status : 'publish',
            'stock_status' => ! empty( $stock_status ) ? (string) $stock_status : 'instock'
        ];
    }
}
