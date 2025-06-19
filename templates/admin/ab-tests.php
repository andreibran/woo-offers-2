<?php
/**
 * A/B Tests admin page template
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="woo-offers-ab-tests-page">
    <div class="woo-offers-page-header">
        <div class="woo-offers-page-title">
            <h2><?php _e( 'A/B Tests', 'woo-offers' ); ?></h2>
        </div>
        <div class="woo-offers-page-actions">
            <button class="button button-primary" disabled><?php _e( 'Create New Test', 'woo-offers' ); ?></button>
        </div>
    </div>

    <div class="woo-offers-ab-tests-content">
        <div class="woo-offers-tests-filters">
            <select disabled>
                <option><?php _e( 'All Tests', 'woo-offers' ); ?></option>
                <option><?php _e( 'Running', 'woo-offers' ); ?></option>
                <option><?php _e( 'Completed', 'woo-offers' ); ?></option>
                <option><?php _e( 'Draft', 'woo-offers' ); ?></option>
            </select>
        </div>

        <div class="woo-offers-tests-list">
            <div class="postbox">
                <h2 class="hndle"><?php _e( 'Active Tests', 'woo-offers' ); ?></h2>
                <div class="inside">
                    <div class="woo-offers-empty-state">
                        <h3><?php _e( 'No A/B tests found', 'woo-offers' ); ?></h3>
                        <p><?php _e( 'A/B testing allows you to compare different versions of your offers to see which performs better.', 'woo-offers' ); ?></p>
                        <button class="button button-primary" disabled>
                            <?php _e( 'Create Your First Test', 'woo-offers' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="woo-offers-ab-info">
            <div class="postbox">
                <h2 class="hndle"><?php _e( 'About A/B Testing', 'woo-offers' ); ?></h2>
                <div class="inside">
                    <p><?php _e( 'A/B testing helps you optimize your offers by comparing different versions:', 'woo-offers' ); ?></p>
                    <ul>
                        <li><?php _e( 'Test different discount amounts', 'woo-offers' ); ?></li>
                        <li><?php _e( 'Compare offer designs and layouts', 'woo-offers' ); ?></li>
                        <li><?php _e( 'Optimize call-to-action text', 'woo-offers' ); ?></li>
                        <li><?php _e( 'Experiment with targeting rules', 'woo-offers' ); ?></li>
                    </ul>
                    <p><strong><?php _e( 'Note:', 'woo-offers' ); ?></strong> <?php _e( 'A/B testing functionality will be implemented in upcoming tasks.', 'woo-offers' ); ?></p>
                </div>
            </div>
        </div>
    </div>
</div> 