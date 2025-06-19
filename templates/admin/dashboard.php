<?php
/**
 * Dashboard admin page template
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get dashboard instance and render the dashboard
$dashboard = new \WooOffers\Admin\Dashboard();
$dashboard->render_dashboard_page();
?> 