<?php
/**
 * Analytics admin page template
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get analytics instance and render the analytics page
$analytics = new \WooOffers\Admin\Analytics();
$analytics->render_analytics_page();
?> 