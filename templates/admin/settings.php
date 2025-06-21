<?php
/**
 * Settings Page Template
 * 
 * Provides a modern tabbed interface for plugin settings including
 * general settings, campaign type configurations, and advanced options.
 * 
 * @package WooOffers\Templates\Admin
 * @since 3.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use WooOffers\Admin\Settings;

// Get current settings
$general_settings = Settings::get_settings( 'general' );
$campaign_settings = Settings::get_settings( 'campaigns' );
$advanced_settings = Settings::get_settings( 'advanced' );

// Handle settings updates
if ( isset( $_GET['settings-updated'] ) ) {
    add_settings_error(
        'woo_offers_messages',
        'woo_offers_message',
        __( 'Settings saved successfully.', 'woo-offers' ),
        'updated'
    );
}

settings_errors( 'woo_offers_messages' );
?>

<div class="wrap woo-offers-settings">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <div class="woo-offers-settings-header">
        <p class="description">
            <?php esc_html_e( 'Configure your Woo Offers plugin settings to customize behavior, enable campaign types, and optimize performance.', 'woo-offers' ); ?>
        </p>
    </div>
    
    <div class="woo-offers-settings-container">
        <nav class="nav-tab-wrapper woo-offers-nav-tabs">
            <a href="#general" class="nav-tab nav-tab-active" data-tab="general">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php esc_html_e( 'General', 'woo-offers' ); ?>
            </a>
            <a href="#campaigns" class="nav-tab" data-tab="campaigns">
                <span class="dashicons dashicons-megaphone"></span>
                <?php esc_html_e( 'Campaign Types', 'woo-offers' ); ?>
            </a>
            <a href="#advanced" class="nav-tab" data-tab="advanced">
                <span class="dashicons dashicons-admin-tools"></span>
                <?php esc_html_e( 'Advanced', 'woo-offers' ); ?>
            </a>
        </nav>
        
        <form action="options.php" method="post" class="woo-offers-settings-form">
            <?php
            // Output security fields for all setting groups
            settings_fields( Settings::OPTION_GROUP_GENERAL );
            settings_fields( Settings::OPTION_GROUP_CAMPAIGNS );
            settings_fields( Settings::OPTION_GROUP_ADVANCED );
            ?>
            
            <!-- General Settings Tab -->
            <div id="general" class="tab-content active">
                <div class="settings-section">
                    <h2><?php esc_html_e( 'General Settings', 'woo-offers' ); ?></h2>
                    <p class="description">
                        <?php esc_html_e( 'Configure general plugin behavior and basic functionality.', 'woo-offers' ); ?>
                    </p>
                    
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="woo_offers_general_settings_enable_plugin">
                                        <?php esc_html_e( 'Enable Plugin', 'woo-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text">
                                            <span><?php esc_html_e( 'Enable Plugin', 'woo-offers' ); ?></span>
                                        </legend>
                                        <label for="woo_offers_general_settings_enable_plugin">
                                            <input type="checkbox" 
                                                   id="woo_offers_general_settings_enable_plugin" 
                                                   name="woo_offers_general_settings[enable_plugin]" 
                                                   value="1" 
                                                   <?php checked( 1, $general_settings['enable_plugin'] ?? true ); ?> />
                                            <?php esc_html_e( 'Enable or disable the Woo Offers plugin functionality.', 'woo-offers' ); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="woo_offers_general_settings_debug_mode">
                                        <?php esc_html_e( 'Debug Mode', 'woo-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text">
                                            <span><?php esc_html_e( 'Debug Mode', 'woo-offers' ); ?></span>
                                        </legend>
                                        <label for="woo_offers_general_settings_debug_mode">
                                            <input type="checkbox" 
                                                   id="woo_offers_general_settings_debug_mode" 
                                                   name="woo_offers_general_settings[debug_mode]" 
                                                   value="1" 
                                                   <?php checked( 1, $general_settings['debug_mode'] ?? false ); ?> />
                                            <?php esc_html_e( 'Enable debug mode to log detailed information for troubleshooting.', 'woo-offers' ); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="woo_offers_general_settings_cache_enabled">
                                        <?php esc_html_e( 'Enable Caching', 'woo-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text">
                                            <span><?php esc_html_e( 'Enable Caching', 'woo-offers' ); ?></span>
                                        </legend>
                                        <label for="woo_offers_general_settings_cache_enabled">
                                            <input type="checkbox" 
                                                   id="woo_offers_general_settings_cache_enabled" 
                                                   name="woo_offers_general_settings[cache_enabled]" 
                                                   value="1" 
                                                   <?php checked( 1, $general_settings['cache_enabled'] ?? true ); ?> />
                                            <?php esc_html_e( 'Enable caching to improve performance.', 'woo-offers' ); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="woo_offers_general_settings_cache_duration">
                                        <?php esc_html_e( 'Cache Duration (seconds)', 'woo-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="woo_offers_general_settings_cache_duration" 
                                           name="woo_offers_general_settings[cache_duration]" 
                                           value="<?php echo esc_attr( $general_settings['cache_duration'] ?? 3600 ); ?>" 
                                           min="60" 
                                           max="86400" 
                                           step="60" 
                                           class="small-text" />
                                    <p class="description">
                                        <?php esc_html_e( 'How long to cache data in seconds (default: 3600).', 'woo-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="woo_offers_general_settings_load_assets_globally">
                                        <?php esc_html_e( 'Load Assets Globally', 'woo-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text">
                                            <span><?php esc_html_e( 'Load Assets Globally', 'woo-offers' ); ?></span>
                                        </legend>
                                        <label for="woo_offers_general_settings_load_assets_globally">
                                            <input type="checkbox" 
                                                   id="woo_offers_general_settings_load_assets_globally" 
                                                   name="woo_offers_general_settings[load_assets_globally]" 
                                                   value="1" 
                                                   <?php checked( 1, $general_settings['load_assets_globally'] ?? false ); ?> />
                                            <?php esc_html_e( 'Load plugin CSS and JavaScript on all pages (not recommended for performance).', 'woo-offers' ); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Campaign Settings Tab -->
            <div id="campaigns" class="tab-content">
                <div class="settings-section">
                    <h2><?php esc_html_e( 'Campaign Type Settings', 'woo-offers' ); ?></h2>
                    <p class="description">
                        <?php esc_html_e( 'Configure which campaign types are enabled and their behavior settings.', 'woo-offers' ); ?>
                    </p>
                    
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Enabled Campaign Types', 'woo-offers' ); ?></th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text">
                                            <span><?php esc_html_e( 'Enabled Campaign Types', 'woo-offers' ); ?></span>
                                        </legend>
                                        
                                        <label for="woo_offers_campaign_settings_checkout_enabled">
                                            <input type="checkbox" 
                                                   id="woo_offers_campaign_settings_checkout_enabled" 
                                                   name="woo_offers_campaign_settings[checkout_enabled]" 
                                                   value="1" 
                                                   <?php checked( 1, $campaign_settings['checkout_enabled'] ?? true ); ?> />
                                            <?php esc_html_e( 'Checkout Campaigns', 'woo-offers' ); ?>
                                        </label><br>
                                        
                                        <label for="woo_offers_campaign_settings_cart_enabled">
                                            <input type="checkbox" 
                                                   id="woo_offers_campaign_settings_cart_enabled" 
                                                   name="woo_offers_campaign_settings[cart_enabled]" 
                                                   value="1" 
                                                   <?php checked( 1, $campaign_settings['cart_enabled'] ?? true ); ?> />
                                            <?php esc_html_e( 'Cart Campaigns', 'woo-offers' ); ?>
                                        </label><br>
                                        
                                        <label for="woo_offers_campaign_settings_product_page_enabled">
                                            <input type="checkbox" 
                                                   id="woo_offers_campaign_settings_product_page_enabled" 
                                                   name="woo_offers_campaign_settings[product_page_enabled]" 
                                                   value="1" 
                                                   <?php checked( 1, $campaign_settings['product_page_enabled'] ?? true ); ?> />
                                            <?php esc_html_e( 'Product Page Campaigns', 'woo-offers' ); ?>
                                        </label><br>
                                        
                                        <label for="woo_offers_campaign_settings_exit_intent_enabled">
                                            <input type="checkbox" 
                                                   id="woo_offers_campaign_settings_exit_intent_enabled" 
                                                   name="woo_offers_campaign_settings[exit_intent_enabled]" 
                                                   value="1" 
                                                   <?php checked( 1, $campaign_settings['exit_intent_enabled'] ?? true ); ?> />
                                            <?php esc_html_e( 'Exit Intent Campaigns', 'woo-offers' ); ?>
                                        </label><br>
                                        
                                        <label for="woo_offers_campaign_settings_post_purchase_enabled">
                                            <input type="checkbox" 
                                                   id="woo_offers_campaign_settings_post_purchase_enabled" 
                                                   name="woo_offers_campaign_settings[post_purchase_enabled]" 
                                                   value="1" 
                                                   <?php checked( 1, $campaign_settings['post_purchase_enabled'] ?? true ); ?> />
                                            <?php esc_html_e( 'Post-Purchase Campaigns', 'woo-offers' ); ?>
                                        </label>
                                        
                                        <p class="description">
                                            <?php esc_html_e( 'Select which campaign types should be available for creation.', 'woo-offers' ); ?>
                                        </p>
                                    </fieldset>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="woo_offers_campaign_settings_max_campaigns_per_page">
                                        <?php esc_html_e( 'Max Campaigns Per Page', 'woo-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="woo_offers_campaign_settings_max_campaigns_per_page" 
                                           name="woo_offers_campaign_settings[max_campaigns_per_page]" 
                                           value="<?php echo esc_attr( $campaign_settings['max_campaigns_per_page'] ?? 3 ); ?>" 
                                           min="1" 
                                           max="10" 
                                           step="1" 
                                           class="small-text" />
                                    <p class="description">
                                        <?php esc_html_e( 'Maximum number of campaigns to display on a single page.', 'woo-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="woo_offers_campaign_settings_campaign_timeout">
                                        <?php esc_html_e( 'Campaign Timeout (seconds)', 'woo-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="woo_offers_campaign_settings_campaign_timeout" 
                                           name="woo_offers_campaign_settings[campaign_timeout]" 
                                           value="<?php echo esc_attr( $campaign_settings['campaign_timeout'] ?? 300 ); ?>" 
                                           min="30" 
                                           max="3600" 
                                           step="30" 
                                           class="small-text" />
                                    <p class="description">
                                        <?php esc_html_e( 'How long to wait before showing another campaign to the same user.', 'woo-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="woo_offers_campaign_settings_analytics_enabled">
                                        <?php esc_html_e( 'Enable Analytics', 'woo-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text">
                                            <span><?php esc_html_e( 'Enable Analytics', 'woo-offers' ); ?></span>
                                        </legend>
                                        <label for="woo_offers_campaign_settings_analytics_enabled">
                                            <input type="checkbox" 
                                                   id="woo_offers_campaign_settings_analytics_enabled" 
                                                   name="woo_offers_campaign_settings[analytics_enabled]" 
                                                   value="1" 
                                                   <?php checked( 1, $campaign_settings['analytics_enabled'] ?? true ); ?> />
                                            <?php esc_html_e( 'Track campaign performance and user interactions.', 'woo-offers' ); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Advanced Settings Tab -->
            <div id="advanced" class="tab-content">
                <div class="settings-section">
                    <h2><?php esc_html_e( 'Advanced Settings', 'woo-offers' ); ?></h2>
                    <p class="description">
                        <?php esc_html_e( 'Advanced settings for developers and power users. Use with caution.', 'woo-offers' ); ?>
                    </p>
                    
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="woo_offers_advanced_settings_custom_css">
                                        <?php esc_html_e( 'Custom CSS', 'woo-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <textarea id="woo_offers_advanced_settings_custom_css" 
                                              name="woo_offers_advanced_settings[custom_css]" 
                                              rows="10" 
                                              class="large-text code"><?php echo esc_textarea( $advanced_settings['custom_css'] ?? '' ); ?></textarea>
                                    <p class="description">
                                        <?php esc_html_e( 'Add custom CSS to style your campaigns.', 'woo-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="woo_offers_advanced_settings_custom_js">
                                        <?php esc_html_e( 'Custom JavaScript', 'woo-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <textarea id="woo_offers_advanced_settings_custom_js" 
                                              name="woo_offers_advanced_settings[custom_js]" 
                                              rows="10" 
                                              class="large-text code"><?php echo esc_textarea( $advanced_settings['custom_js'] ?? '' ); ?></textarea>
                                    <p class="description">
                                        <?php esc_html_e( 'Add custom JavaScript for advanced campaign functionality.', 'woo-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="woo_offers_advanced_settings_rest_api_enabled">
                                        <?php esc_html_e( 'Enable REST API', 'woo-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <fieldset>
                                        <legend class="screen-reader-text">
                                            <span><?php esc_html_e( 'Enable REST API', 'woo-offers' ); ?></span>
                                        </legend>
                                        <label for="woo_offers_advanced_settings_rest_api_enabled">
                                            <input type="checkbox" 
                                                   id="woo_offers_advanced_settings_rest_api_enabled" 
                                                   name="woo_offers_advanced_settings[rest_api_enabled]" 
                                                   value="1" 
                                                   <?php checked( 1, $advanced_settings['rest_api_enabled'] ?? true ); ?> />
                                            <?php esc_html_e( 'Enable REST API endpoints for external integrations.', 'woo-offers' ); ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="woo_offers_advanced_settings_performance_mode">
                                        <?php esc_html_e( 'Performance Mode', 'woo-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="woo_offers_advanced_settings_performance_mode" 
                                            name="woo_offers_advanced_settings[performance_mode]">
                                        <option value="conservative" <?php selected( $advanced_settings['performance_mode'] ?? 'balanced', 'conservative' ); ?>>
                                            <?php esc_html_e( 'Conservative - Maximum compatibility', 'woo-offers' ); ?>
                                        </option>
                                        <option value="balanced" <?php selected( $advanced_settings['performance_mode'] ?? 'balanced', 'balanced' ); ?>>
                                            <?php esc_html_e( 'Balanced - Good performance and compatibility', 'woo-offers' ); ?>
                                        </option>
                                        <option value="aggressive" <?php selected( $advanced_settings['performance_mode'] ?? 'balanced', 'aggressive' ); ?>>
                                            <?php esc_html_e( 'Aggressive - Maximum performance', 'woo-offers' ); ?>
                                        </option>
                                    </select>
                                    <p class="description">
                                        <?php esc_html_e( 'Choose the performance optimization level.', 'woo-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="woo_offers_advanced_settings_security_level">
                                        <?php esc_html_e( 'Security Level', 'woo-offers' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="woo_offers_advanced_settings_security_level" 
                                            name="woo_offers_advanced_settings[security_level]">
                                        <option value="basic" <?php selected( $advanced_settings['security_level'] ?? 'standard', 'basic' ); ?>>
                                            <?php esc_html_e( 'Basic - Standard security checks', 'woo-offers' ); ?>
                                        </option>
                                        <option value="standard" <?php selected( $advanced_settings['security_level'] ?? 'standard', 'standard' ); ?>>
                                            <?php esc_html_e( 'Standard - Enhanced security validation', 'woo-offers' ); ?>
                                        </option>
                                        <option value="strict" <?php selected( $advanced_settings['security_level'] ?? 'standard', 'strict' ); ?>>
                                            <?php esc_html_e( 'Strict - Maximum security (may affect performance)', 'woo-offers' ); ?>
                                        </option>
                                    </select>
                                    <p class="description">
                                        <?php esc_html_e( 'Choose the security validation level.', 'woo-offers' ); ?>
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Import/Export Section -->
                <div class="settings-section">
                    <h2><?php esc_html_e( 'Backup & Restore', 'woo-offers' ); ?></h2>
                    <p class="description"><?php esc_html_e( 'Export your settings for backup or import settings from another site.', 'woo-offers' ); ?></p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                        <!-- Export Section -->
                        <div style="padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                            <h3><?php esc_html_e( 'Export Settings', 'woo-offers' ); ?></h3>
                            <p><?php esc_html_e( 'Download your current settings as a JSON file for backup or transfer.', 'woo-offers' ); ?></p>
                            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                                <input type="hidden" name="action" value="woo_offers_export_settings">
                                <?php wp_nonce_field( 'woo_offers_export_settings' ); ?>
                                <button type="submit" class="button button-secondary">
                                    <span class="dashicons dashicons-download" style="margin-top: 3px;"></span>
                                    <?php esc_html_e( 'Export Settings', 'woo-offers' ); ?>
                                </button>
                            </form>
                        </div>
                        
                        <!-- Import Section -->
                        <div style="padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                            <h3><?php esc_html_e( 'Import Settings', 'woo-offers' ); ?></h3>
                            <p><?php esc_html_e( 'Upload a JSON settings file to restore or transfer settings.', 'woo-offers' ); ?></p>
                            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="woo_offers_import_settings">
                                <?php wp_nonce_field( 'woo_offers_import_settings' ); ?>
                                <p>
                                    <input type="file" name="settings_file" accept=".json" style="margin-bottom: 10px;">
                                </p>
                                <p style="text-align: center; margin: 10px 0;">
                                    <em><?php esc_html_e( 'or', 'woo-offers' ); ?></em>
                                </p>
                                <p>
                                    <textarea name="settings_json" placeholder="<?php esc_attr_e( 'Paste JSON settings here...', 'woo-offers' ); ?>" rows="4" style="width: 100%; font-family: monospace; font-size: 12px;"></textarea>
                                </p>
                                <button type="submit" class="button button-secondary" onclick="return confirm('<?php esc_attr_e( 'This will overwrite your current settings. Are you sure?', 'woo-offers' ); ?>');">
                                    <span class="dashicons dashicons-upload" style="margin-top: 3px;"></span>
                                    <?php esc_html_e( 'Import Settings', 'woo-offers' ); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Reset Section -->
                    <div style="margin-top: 20px; padding: 20px; border: 1px solid #dc3232; border-radius: 4px; background: #fef7f7;">
                        <h3 style="color: #dc3232; margin-top: 0;"><?php esc_html_e( 'Reset to Defaults', 'woo-offers' ); ?></h3>
                        <p><?php esc_html_e( 'Reset all settings to their default values. This action cannot be undone.', 'woo-offers' ); ?></p>
                        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                            <input type="hidden" name="action" value="woo_offers_reset_settings">
                            <?php wp_nonce_field( 'woo_offers_reset_settings' ); ?>
                            <button type="submit" class="button button-secondary" style="color: #dc3232; border-color: #dc3232;" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reset all settings to defaults? This cannot be undone.', 'woo-offers' ); ?>');">
                                <span class="dashicons dashicons-backup" style="margin-top: 3px;"></span>
                                <?php esc_html_e( 'Reset to Defaults', 'woo-offers' ); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php submit_button( __( 'Save Settings', 'woo-offers' ) ); ?>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab switching functionality
    $('.woo-offers-nav-tabs .nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var tabId = $(this).data('tab');
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show/hide content
        $('.tab-content').removeClass('active');
        $('#' + tabId).addClass('active');
        
        // Update URL hash without triggering scroll
        if (history.pushState) {
            history.pushState(null, null, '#' + tabId);
        }
    });
    
    // Handle direct hash navigation
    function switchToHashTab() {
        var hash = window.location.hash.substring(1);
        if (hash && $('#' + hash).length) {
            $('.nav-tab[data-tab="' + hash + '"]').trigger('click');
        }
    }
    
    // Switch to hash tab on load
    switchToHashTab();
    
    // Switch to hash tab on hash change
    $(window).on('hashchange', switchToHashTab);
});
</script> 