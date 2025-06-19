<?php
/**
 * Admin Settings Page Template
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get settings instance and render the settings page
$settings = new \WooOffers\Admin\Settings();
$settings->render_settings_page();
?> 