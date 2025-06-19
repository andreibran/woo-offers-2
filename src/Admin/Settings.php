<?php

namespace WooOffers\Admin;

/**
 * Settings management for plugin configuration
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings class for managing plugin configuration
 */
class Settings {

    /**
     * Settings option name
     */
    const OPTION_NAME = 'woo_offers_settings';
    const ADVANCED_OPTION_NAME = 'woo_offers_remove_data';

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_init', [ $this, 'init_settings' ] );
    }

    /**
     * Initialize settings API
     */
    public function init_settings() {
        register_setting(
            'woo_offers_settings_group',
            self::OPTION_NAME,
            [ $this, 'sanitize_settings' ]
        );
        
        // Register a separate option for the dangerous setting
        register_setting(
            'woo_offers_settings_group',
            self::ADVANCED_OPTION_NAME,
            'boolval'
        );

        // General settings section
        add_settings_section(
            'woo_offers_general_section',
            null,
            null,
            'woo_offers_general'
        );

        // Display settings section
        add_settings_section(
            'woo_offers_display_section',
            null,
            null,
            'woo_offers_display'
        );

        // Performance settings section
        add_settings_section(
            'woo_offers_performance_section',
            null,
            null,
            'woo_offers_performance'
        );

        // Advanced settings section
        add_settings_section(
            'woo_offers_advanced_section',
            null,
            null,
            'woo_offers_advanced'
        );

        $this->add_settings_fields();
    }

    /**
     * Add settings fields
     */
    private function add_settings_fields() {
        $settings = self::get_settings();

        // General settings fields
        add_settings_field(
            'enable_analytics',
            __( 'Enable Analytics', 'woo-offers' ),
            [ $this, 'checkbox_field_callback' ],
            'woo_offers_general',
            'woo_offers_general_section',
            [
                'name' => self::OPTION_NAME . '[enable_analytics]',
                'value' => $settings['enable_analytics'],
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
                'name' => self::OPTION_NAME . '[enable_ab_testing]',
                'value' => $settings['enable_ab_testing'],
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
                'name' => self::OPTION_NAME . '[primary_color]',
                'value' => $settings['primary_color'],
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
                'name' => self::OPTION_NAME . '[default_position]',
                'value' => $settings['default_position'],
                'options' => [
                    'before_add_to_cart' => __( 'Before Add to Cart Button', 'woo-offers' ),
                    'after_add_to_cart' => __( 'After Add to Cart Button', 'woo-offers' ),
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
                'name' => self::OPTION_NAME . '[cache_offers]',
                'value' => $settings['cache_offers'],
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
                'name' => self::OPTION_NAME . '[load_scripts_everywhere]',
                'value' => $settings['load_scripts_everywhere'],
                'description' => __( 'Load offer scripts on all pages (may impact performance)', 'woo-offers' )
            ]
        );
        
        // Advanced settings fields
        add_settings_field(
            'debug_mode',
            __( 'Debug Mode', 'woo-offers' ),
            [ $this, 'checkbox_field_callback' ],
            'woo_offers_advanced',
            'woo_offers_advanced_section',
            [
                'name' => self::OPTION_NAME . '[debug_mode]',
                'value' => $settings['debug_mode'],
                'description' => __( 'Enable debug mode for development purposes.', 'woo-offers' )
            ]
        );

        add_settings_field(
            'remove_data_on_uninstall',
            __( 'Remove Data on Uninstall', 'woo-offers' ),
            [ $this, 'checkbox_field_callback' ],
            'woo_offers_advanced',
            'woo_offers_advanced_section',
            [
                'name' => self::ADVANCED_OPTION_NAME,
                'value' => get_option(self::ADVANCED_OPTION_NAME, false),
                'description' => '<strong style="color:red;">' . __( 'Warning:', 'woo-offers' ) . '</strong> ' . __( 'Check this box to permanently delete all plugin data (offers, analytics, settings) when the plugin is uninstalled.', 'woo-offers' )
            ]
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap woo-offers-settings-page">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <div class="woo-offers-settings-tabs">
                <h2 class="nav-tab-wrapper">
                    <a href="#general" class="nav-tab nav-tab-active"><?php _e( 'General', 'woo-offers' ); ?></a>
                    <a href="#display" class="nav-tab"><?php _e( 'Display', 'woo-offers' ); ?></a>
                    <a href="#performance" class="nav-tab"><?php _e( 'Performance', 'woo-offers' ); ?></a>
                    <a href="#advanced" class="nav-tab"><?php _e( 'Advanced', 'woo-offers' ); ?></a>
                </h2>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields( 'woo_offers_settings_group' ); ?>
                
                <div class="tab-content">
                    <div id="general" class="tab-pane active">
                        <table class="form-table">
                            <?php do_settings_sections( 'woo_offers_general' ); ?>
                        </table>
                    </div>

                    <div id="display" class="tab-pane">
                        <table class="form-table">
                            <?php do_settings_sections( 'woo_offers_display' ); ?>
                        </table>
                    </div>

                    <div id="performance" class="tab-pane">
                        <table class="form-table">
                            <?php do_settings_sections( 'woo_offers_performance' ); ?>
                        </table>
                    </div>

                    <div id="advanced" class="tab-pane">
                        <table class="form-table">
                            <?php do_settings_sections( 'woo_offers_advanced' ); ?>
                        </table>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Get all settings or a default value
     */
    public static function get_settings() {
        $defaults = [
            'enable_analytics' => true,
            'enable_ab_testing' => true,
            'primary_color' => '#e92d3b',
            'default_position' => 'before_add_to_cart',
            'cache_offers' => true,
            'load_scripts_everywhere' => false,
            'debug_mode' => false,
        ];

        $settings = get_option( self::OPTION_NAME, [] );
        return wp_parse_args( $settings, $defaults );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings( $input ) {
        $new_input = [];
        $settings = self::get_settings();

        $new_input['enable_analytics'] = isset( $input['enable_analytics'] ) ? boolval( $input['enable_analytics'] ) : false;
        $new_input['enable_ab_testing'] = isset( $input['enable_ab_testing'] ) ? boolval( $input['enable_ab_testing'] ) : false;
        $new_input['primary_color'] = isset( $input['primary_color'] ) ? sanitize_hex_color( $input['primary_color'] ) : $settings['primary_color'];
        $new_input['default_position'] = isset( $input['default_position'] ) ? sanitize_text_field( $input['default_position'] ) : $settings['default_position'];
        $new_input['cache_offers'] = isset( $input['cache_offers'] ) ? boolval( $input['cache_offers'] ) : false;
        $new_input['load_scripts_everywhere'] = isset( $input['load_scripts_everywhere'] ) ? boolval( $input['load_scripts_everywhere'] ) : false;
        $new_input['debug_mode'] = isset( $input['debug_mode'] ) ? boolval( $input['debug_mode'] ) : false;
        
        return $new_input;
    }

    /**
     * Checkbox field callback
     */
    public function checkbox_field_callback( $args ) {
        $name = esc_attr( $args['name'] );
        $value = esc_attr( $args['value'] );
        $description = esc_html( $args['description'] );
        
        echo "<input type='checkbox' name='{$name}' value='1' " . checked( 1, $value, false ) . " />";
        echo "<p class='description'>{$description}</p>";
    }
    
    /**
     * Color field callback
     */
    public function color_field_callback( $args ) {
        $name = esc_attr( $args['name'] );
        $value = esc_attr( $args['value'] );
        $description = esc_html( $args['description'] );

        echo "<input type='text' name='{$name}' value='{$value}' class='woo-offers-color-picker' />";
        echo "<p class='description'>{$description}</p>";
    }

    /**
     * Select field callback
     */
    public function select_field_callback( $args ) {
        $name = esc_attr( $args['name'] );
        $value = esc_attr( $args['value'] );
        $options = $args['options'];
        $description = esc_html( $args['description'] );

        echo "<select name='{$name}'>";
        foreach( $options as $option_value => $option_label ) {
            echo "<option value='{$option_value}' " . selected( $value, $option_value, false ) . ">{$option_label}</option>";
        }
        echo "</select>";
        echo "<p class='description'>{$description}</p>";
    }
}