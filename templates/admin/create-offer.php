<?php
/**
 * Create offer admin page template
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Since we have a comprehensive edit-offer template with metaboxes,
// we'll redirect create requests to the edit-offer template for new offers.
// The Admin class handles the differentiation between create and edit modes.

// Load the edit-offer template for creating new offers
include WOO_OFFERS_PLUGIN_PATH . 'templates/admin/edit-offer.php';
?> 