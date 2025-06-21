<?php
/**
 * Modern Campaign Creation Wizard Template
 * Refactored for responsive design system and enhanced UX
 *
 * @package WooOffers
 * @since 3.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="woo-offers-modern">
    <div id="woo-offers-campaign-wizard" class="wizard-container">
        
        <!-- Skip Link for Accessibility -->
        <a href="#wizard-content" class="wo-skip-link">
            <?php esc_html_e( 'Skip to main content', 'woo-offers' ); ?>
        </a>

        <!-- Wizard Header -->
        <header class="wizard-header">
            <div class="wo-container">
                <div class="wizard-header-content">
                    <div class="wizard-branding">
                        <h1 class="wizard-title wo-text-2xl wo-font-bold wo-text-gray-900">
                            <?php esc_html_e( 'Create New Campaign', 'woo-offers' ); ?>
                        </h1>
                        <p class="wizard-subtitle wo-text-base wo-text-gray-600 wo-mt-1">
                            <?php esc_html_e( 'Follow these steps to create a powerful marketing campaign', 'woo-offers' ); ?>
                        </p>
                    </div>
                    <div class="wizard-actions">
                        <button type="button" class="wo-btn wo-btn-secondary wo-btn-sm" id="wizard-save-exit">
                            <svg class="wo-icon wo-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <?php esc_html_e( 'Save & Exit', 'woo-offers' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Step Progress Indicator -->
        <div class="wizard-progress-section" role="navigation" aria-label="<?php esc_attr_e( 'Wizard Progress', 'woo-offers' ); ?>">
            <div class="wo-container">
                <div class="wizard-progress">
                    <!-- Progress Bar -->
                    <div class="progress-track" aria-hidden="true">
                        <div class="progress-fill" id="wizard-progress-fill" style="width: 33.33%;"></div>
                    </div>
                    
                    <!-- Step Indicators -->
                    <div class="step-indicators">
                        <div class="step-indicator active" data-step="1" role="tab" aria-selected="true" tabindex="0">
                            <div class="step-circle">
                                <span class="step-number">1</span>
                                <svg class="step-check wo-icon wo-icon-sm" style="display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="step-label">
                                <span class="step-title"><?php esc_html_e( 'Type & Info', 'woo-offers' ); ?></span>
                                <span class="step-desc"><?php esc_html_e( 'Choose campaign type', 'woo-offers' ); ?></span>
                            </div>
                        </div>
                        
                        <div class="step-indicator" data-step="2" role="tab" aria-selected="false" tabindex="-1">
                            <div class="step-circle">
                                <span class="step-number">2</span>
                                <svg class="step-check wo-icon wo-icon-sm" style="display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="step-label">
                                <span class="step-title"><?php esc_html_e( 'Configuration', 'woo-offers' ); ?></span>
                                <span class="step-desc"><?php esc_html_e( 'Set up campaign details', 'woo-offers' ); ?></span>
                            </div>
                        </div>
                        
                        <div class="step-indicator" data-step="3" role="tab" aria-selected="false" tabindex="-1">
                            <div class="step-circle">
                                <span class="step-number">3</span>
                                <svg class="step-check wo-icon wo-icon-sm" style="display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="step-label">
                                <span class="step-title"><?php esc_html_e( 'Preview & Launch', 'woo-offers' ); ?></span>
                                <span class="step-desc"><?php esc_html_e( 'Review and activate', 'woo-offers' ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wizard Content Area -->
        <main id="wizard-content" class="wizard-content">
            <div class="wo-container">
                
                <!-- Step 1: Campaign Type & Basic Info -->
                <div class="wizard-step active" id="step-1" role="tabpanel" aria-labelledby="step-1-tab">
                    <div class="step-content">
                        <div class="step-header">
                            <h2 class="wo-text-xl wo-font-semibold wo-text-gray-900 wo-mb-2">
                                <?php esc_html_e( 'Choose Campaign Type & Basic Information', 'woo-offers' ); ?>
                            </h2>
                            <p class="wo-text-sm wo-text-gray-600">
                                <?php esc_html_e( 'Start by selecting the type of campaign you want to create and providing basic details.', 'woo-offers' ); ?>
                            </p>
                        </div>
                        
                        <!-- Campaign Type Selection -->
                        <div class="wo-form-section">
                            <h3 class="wo-form-section-title">
                                <?php esc_html_e( 'Campaign Type', 'woo-offers' ); ?>
                                <span class="required-indicator" aria-label="<?php esc_attr_e( 'Required', 'woo-offers' ); ?>">*</span>
                            </h3>
                            <div class="campaign-types-grid">
                                <div class="campaign-type-card" data-type="checkout" role="button" tabindex="0" 
                                     aria-describedby="checkout-desc">
                                    <div class="campaign-type-icon">
                                        <svg class="wo-icon wo-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.68 4.72a1 1 0 00.95 1.28h9.46M16 16a2 2 0 11-4 0 2 2 0 014 0zM9 20a2 2 0 100-4 2 2 0 000 4z"></path>
                                        </svg>
                                    </div>
                                    <div class="campaign-type-content">
                                        <h4 class="campaign-type-title"><?php esc_html_e( 'Checkout Upsell', 'woo-offers' ); ?></h4>
                                        <p class="campaign-type-desc" id="checkout-desc"><?php esc_html_e( 'Show offers during the checkout process to increase order value', 'woo-offers' ); ?></p>
                                    </div>
                                    <div class="campaign-type-check">
                                        <svg class="wo-icon wo-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                
                                <div class="campaign-type-card" data-type="cart" role="button" tabindex="0" 
                                     aria-describedby="cart-desc">
                                    <div class="campaign-type-icon">
                                        <svg class="wo-icon wo-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                    </div>
                                    <div class="campaign-type-content">
                                        <h4 class="campaign-type-title"><?php esc_html_e( 'Cart Upsell', 'woo-offers' ); ?></h4>
                                        <p class="campaign-type-desc" id="cart-desc"><?php esc_html_e( 'Display complementary offers on the cart page', 'woo-offers' ); ?></p>
                                    </div>
                                    <div class="campaign-type-check">
                                        <svg class="wo-icon wo-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                
                                <div class="campaign-type-card" data-type="product" role="button" tabindex="0" 
                                     aria-describedby="product-desc">
                                    <div class="campaign-type-icon">
                                        <svg class="wo-icon wo-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <div class="campaign-type-content">
                                        <h4 class="campaign-type-title"><?php esc_html_e( 'Product Page', 'woo-offers' ); ?></h4>
                                        <p class="campaign-type-desc" id="product-desc"><?php esc_html_e( 'Show related offers directly on product pages', 'woo-offers' ); ?></p>
                                    </div>
                                    <div class="campaign-type-check">
                                        <svg class="wo-icon wo-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                
                                <div class="campaign-type-card" data-type="exit-intent" role="button" tabindex="0" 
                                     aria-describedby="exit-desc">
                                    <div class="campaign-type-icon">
                                        <svg class="wo-icon wo-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                    </div>
                                    <div class="campaign-type-content">
                                        <h4 class="campaign-type-title"><?php esc_html_e( 'Exit-Intent', 'woo-offers' ); ?></h4>
                                        <p class="campaign-type-desc" id="exit-desc"><?php esc_html_e( 'Capture visitors with last-chance offers before they leave', 'woo-offers' ); ?></p>
                                    </div>
                                    <div class="campaign-type-check">
                                        <svg class="wo-icon wo-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                
                                <div class="campaign-type-card" data-type="post-purchase" role="button" tabindex="0" 
                                     aria-describedby="post-purchase-desc">
                                    <div class="campaign-type-icon">
                                        <svg class="wo-icon wo-icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="campaign-type-content">
                                        <h4 class="campaign-type-title"><?php esc_html_e( 'Post-Purchase', 'woo-offers' ); ?></h4>
                                        <p class="campaign-type-desc" id="post-purchase-desc"><?php esc_html_e( 'Show complementary offers after successful purchase', 'woo-offers' ); ?></p>
                                    </div>
                                    <div class="campaign-type-check">
                                        <svg class="wo-icon wo-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="campaign-type" name="campaign_type" value="" required>
                            <div class="wo-form-error" id="campaign-type-error" role="alert" aria-live="polite"></div>
                        </div>

                        <!-- Basic Information -->
                        <div class="wo-form-section">
                            <h3 class="wo-form-section-title">
                                <?php esc_html_e( 'Basic Information', 'woo-offers' ); ?>
                            </h3>
                            <div class="wo-form-grid wo-grid-cols-2">
                                <div class="wo-form-group">
                                    <label for="campaign-name" class="wo-form-label">
                                        <?php esc_html_e( 'Campaign Name', 'woo-offers' ); ?>
                                        <span class="required-indicator" aria-label="<?php esc_attr_e( 'Required', 'woo-offers' ); ?>">*</span>
                                    </label>
                                    <input type="text" id="campaign-name" name="campaign_name" 
                                           class="wo-form-input" required 
                                           placeholder="<?php esc_attr_e( 'Enter a descriptive campaign name...', 'woo-offers' ); ?>"
                                           aria-describedby="campaign-name-help">
                                    <div class="wo-form-help" id="campaign-name-help">
                                        <?php esc_html_e( 'Choose a name that helps you identify this campaign later.', 'woo-offers' ); ?>
                                    </div>
                                    <div class="wo-form-error" id="campaign-name-error" role="alert" aria-live="polite"></div>
                                </div>
                                
                                <div class="wo-form-group">
                                    <label for="campaign-priority" class="wo-form-label">
                                        <?php esc_html_e( 'Priority', 'woo-offers' ); ?>
                                    </label>
                                    <select id="campaign-priority" name="campaign_priority" class="wo-form-select">
                                        <option value="low"><?php esc_html_e( 'Low', 'woo-offers' ); ?></option>
                                        <option value="medium" selected><?php esc_html_e( 'Medium', 'woo-offers' ); ?></option>
                                        <option value="high"><?php esc_html_e( 'High', 'woo-offers' ); ?></option>
                                    </select>
                                    <div class="wo-form-help">
                                        <?php esc_html_e( 'Higher priority campaigns will be shown first when multiple campaigns apply.', 'woo-offers' ); ?>
                                    </div>
                                </div>
                                
                                <div class="wo-form-group wo-col-span-2">
                                    <label for="campaign-description" class="wo-form-label">
                                        <?php esc_html_e( 'Description', 'woo-offers' ); ?>
                                    </label>
                                    <textarea id="campaign-description" name="campaign_description" 
                                              class="wo-form-textarea" rows="3" 
                                              placeholder="<?php esc_attr_e( 'Describe the purpose and goals of this campaign...', 'woo-offers' ); ?>"></textarea>
                                    <div class="wo-form-help">
                                        <?php esc_html_e( 'Optional description to help you remember the campaign\'s purpose.', 'woo-offers' ); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Campaign Configuration -->
                <div class="wizard-step" id="step-2" role="tabpanel" aria-labelledby="step-2-tab" style="display: none;">
                    <div class="step-content">
                        <div class="step-header">
                            <h2 class="wo-text-xl wo-font-semibold wo-text-gray-900 wo-mb-2">
                                <?php esc_html_e( 'Campaign Configuration', 'woo-offers' ); ?>
                            </h2>
                            <p class="wo-text-sm wo-text-gray-600">
                                <?php esc_html_e( 'Configure the specific settings for your campaign type.', 'woo-offers' ); ?>
                            </p>
                        </div>
                        
                        <div id="step-2-content">
                            <!-- Dynamic content will be loaded here based on campaign type -->
                            <div class="loading-placeholder">
                                <div class="wo-empty-state">
                                    <div class="wo-empty-icon">
                                        <svg class="wo-icon wo-icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="wo-empty-title"><?php esc_html_e( 'Waiting for Step 1', 'woo-offers' ); ?></h3>
                                    <p class="wo-empty-description">
                                        <?php esc_html_e( 'Please complete the previous step to continue with campaign configuration.', 'woo-offers' ); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Preview & Launch -->
                <div class="wizard-step" id="step-3" role="tabpanel" aria-labelledby="step-3-tab" style="display: none;">
                    <div class="step-content">
                        <div class="step-header">
                            <h2 class="wo-text-xl wo-font-semibold wo-text-gray-900 wo-mb-2">
                                <?php esc_html_e( 'Preview & Launch', 'woo-offers' ); ?>
                            </h2>
                            <p class="wo-text-sm wo-text-gray-600">
                                <?php esc_html_e( 'Review your campaign settings and launch when ready.', 'woo-offers' ); ?>
                            </p>
                        </div>
                        
                        <div id="step-3-content">
                            <!-- Preview content will be loaded here -->
                            <div class="loading-placeholder">
                                <div class="wo-empty-state">
                                    <div class="wo-empty-icon">
                                        <svg class="wo-icon wo-icon-xl" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="wo-empty-title"><?php esc_html_e( 'Almost There!', 'woo-offers' ); ?></h3>
                                    <p class="wo-empty-description">
                                        <?php esc_html_e( 'Complete the previous steps to see your campaign preview.', 'woo-offers' ); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Wizard Navigation -->
        <footer class="wizard-navigation">
            <div class="wo-container">
                <div class="nav-content">
                    <div class="nav-info">
                        <span class="step-counter">
                            <?php esc_html_e( 'Step', 'woo-offers' ); ?> 
                            <span id="current-step-number">1</span> 
                            <?php esc_html_e( 'of', 'woo-offers' ); ?> 3
                        </span>
                    </div>
                    
                    <div class="nav-buttons">
                        <button type="button" class="wo-btn wo-btn-secondary" id="wizard-prev" disabled>
                            <svg class="wo-icon wo-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            <?php esc_html_e( 'Previous', 'woo-offers' ); ?>
                        </button>
                        
                        <div class="nav-primary-buttons">
                            <button type="button" class="wo-btn wo-btn-secondary" id="wizard-save-draft" style="display: none;">
                                <svg class="wo-icon wo-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <?php esc_html_e( 'Save as Draft', 'woo-offers' ); ?>
                            </button>
                            
                            <button type="button" class="wo-btn wo-btn-primary" id="wizard-next">
                                <?php esc_html_e( 'Continue', 'woo-offers' ); ?>
                                <svg class="wo-icon wo-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                            
                            <button type="button" class="wo-btn wo-btn-secondary" id="wizard-open-builder" style="display: none;">
                                <svg class="wo-icon wo-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                <?php esc_html_e( 'Open in Builder', 'woo-offers' ); ?>
                            </button>
                            
                            <button type="button" class="wo-btn wo-btn-success" id="wizard-launch" style="display: none;">
                                <svg class="wo-icon wo-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <?php esc_html_e( 'Launch Campaign', 'woo-offers' ); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Loading Overlay -->
        <div class="wizard-loading-overlay" id="wizard-loading" style="display: none;" 
             role="dialog" aria-modal="true" aria-labelledby="loading-text">
            <div class="loading-content">
                <div class="wo-spinner wo-spinner-lg"></div>
                <p id="loading-text" class="wo-text-base wo-text-gray-600 wo-mt-4">
                    <?php esc_html_e( 'Processing your campaign...', 'woo-offers' ); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for data submission -->
<form id="campaign-wizard-form" style="display: none;">
    <?php wp_nonce_field( 'woo_offers_campaign_wizard', 'wizard_nonce' ); ?>
    <input type="hidden" name="action" value="save_campaign">
    <input type="hidden" name="wizard_data" id="wizard-data-input">
</form>

<style>
/* ========================================
   CAMPAIGN WIZARD SPECIFIC STYLES
   ======================================== */

/* Wizard Container */
.wizard-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: var(--wo-color-gray-50);
}

/* Header */
.wizard-header {
    background: var(--wo-color-white);
    border-bottom: var(--wo-border-width) solid var(--wo-border-primary);
    padding: var(--wo-space-6) 0;
}

.wizard-header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--wo-space-4);
}

.wizard-branding .wizard-title {
    margin: 0;
    line-height: 1.2;
}

.wizard-branding .wizard-subtitle {
    margin: var(--wo-space-1) 0 0;
}

@media (max-width: 768px) {
    .wizard-header-content {
        flex-direction: column;
        gap: var(--wo-space-3);
    }
    
    .wizard-actions {
        align-self: flex-end;
    }
}

/* Progress Section */
.wizard-progress-section {
    background: var(--wo-color-white);
    border-bottom: var(--wo-border-width) solid var(--wo-border-primary);
    padding: var(--wo-space-6) 0;
    position: sticky;
    top: 32px; /* Account for WP admin bar */
    z-index: 10;
}

.wizard-progress {
    position: relative;
    max-width: 800px;
    margin: 0 auto;
}

/* Progress Track */
.progress-track {
    height: 4px;
    background: var(--wo-color-gray-200);
    border-radius: var(--wo-border-radius-full);
    position: absolute;
    top: 20px;
    left: 24px;
    right: 24px;
    z-index: 1;
}

.progress-fill {
    height: 100%;
    background: var(--wo-color-primary);
    border-radius: var(--wo-border-radius-full);
    transition: width var(--wo-transition-normal);
}

/* Step Indicators */
.step-indicators {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 2;
}

.step-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--wo-space-2);
    max-width: 140px;
    text-align: center;
    cursor: pointer;
    outline: none;
}

.step-indicator:focus-visible {
    outline: 2px solid var(--wo-color-primary);
    outline-offset: 4px;
    border-radius: var(--wo-border-radius);
}

.step-circle {
    width: 48px;
    height: 48px;
    border-radius: var(--wo-border-radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--wo-color-white);
    border: 2px solid var(--wo-color-gray-300);
    color: var(--wo-color-gray-500);
    font-weight: 600;
    position: relative;
    transition: all var(--wo-transition-fast);
}

.step-indicator.active .step-circle,
.step-indicator.completed .step-circle {
    background: var(--wo-color-primary);
    border-color: var(--wo-color-primary);
    color: var(--wo-color-white);
}

.step-indicator.completed .step-number {
    display: none;
}

.step-indicator.completed .step-check {
    display: block;
}

.step-label {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.step-title {
    font-size: var(--wo-text-sm);
    font-weight: 600;
    color: var(--wo-color-gray-900);
}

.step-indicator.active .step-title {
    color: var(--wo-color-primary);
}

.step-desc {
    font-size: var(--wo-text-xs);
    color: var(--wo-color-gray-500);
}

@media (max-width: 640px) {
    .step-indicator {
        max-width: 80px;
    }
    
    .step-circle {
        width: 36px;
        height: 36px;
        font-size: var(--wo-text-sm);
    }
    
    .step-title {
        font-size: var(--wo-text-xs);
    }
    
    .step-desc {
        display: none;
    }
}

/* Content Area */
.wizard-content {
    flex: 1;
    padding: var(--wo-space-8) 0;
}

.wizard-step {
    animation: slideIn var(--wo-transition-normal) ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.step-content {
    max-width: 800px;
    margin: 0 auto;
}

.step-header {
    margin-bottom: var(--wo-space-8);
    text-align: center;
}

/* Campaign Type Cards */
.campaign-types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--wo-space-4);
    margin-top: var(--wo-space-4);
}

.campaign-type-card {
    background: var(--wo-color-white);
    border: 2px solid var(--wo-border-primary);
    border-radius: var(--wo-border-radius-lg);
    padding: var(--wo-space-6);
    cursor: pointer;
    transition: all var(--wo-transition-fast);
    position: relative;
    display: flex;
    flex-direction: column;
    gap: var(--wo-space-4);
    min-height: 140px;
}

.campaign-type-card:hover {
    border-color: var(--wo-color-primary);
    transform: translateY(-2px);
    box-shadow: var(--wo-shadow-lg);
}

.campaign-type-card.selected {
    border-color: var(--wo-color-primary);
    background: var(--wo-color-primary-50);
}

.campaign-type-card:focus-visible {
    outline: 2px solid var(--wo-color-primary);
    outline-offset: 2px;
}

.campaign-type-icon {
    color: var(--wo-color-gray-400);
    transition: color var(--wo-transition-fast);
}

.campaign-type-card:hover .campaign-type-icon,
.campaign-type-card.selected .campaign-type-icon {
    color: var(--wo-color-primary);
}

.campaign-type-content {
    flex: 1;
}

.campaign-type-title {
    font-size: var(--wo-text-base);
    font-weight: 600;
    color: var(--wo-color-gray-900);
    margin: 0 0 var(--wo-space-2);
}

.campaign-type-desc {
    font-size: var(--wo-text-sm);
    color: var(--wo-color-gray-600);
    margin: 0;
    line-height: 1.5;
}

.campaign-type-check {
    position: absolute;
    top: var(--wo-space-3);
    right: var(--wo-space-3);
    color: var(--wo-color-primary);
    opacity: 0;
    transform: scale(0.8);
    transition: all var(--wo-transition-fast);
}

.campaign-type-card.selected .campaign-type-check {
    opacity: 1;
    transform: scale(1);
}

@media (max-width: 640px) {
    .campaign-types-grid {
        grid-template-columns: 1fr;
    }
    
    .campaign-type-card {
        padding: var(--wo-space-4);
        min-height: 120px;
    }
}

/* Navigation */
.wizard-navigation {
    background: var(--wo-color-white);
    border-top: var(--wo-border-width) solid var(--wo-border-primary);
    padding: var(--wo-space-6) 0;
    position: sticky;
    bottom: 0;
    z-index: 10;
}

.nav-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--wo-space-4);
}

.nav-info {
    color: var(--wo-color-gray-600);
    font-size: var(--wo-text-sm);
}

.nav-buttons {
    display: flex;
    align-items: center;
    gap: var(--wo-space-3);
}

.nav-primary-buttons {
    display: flex;
    align-items: center;
    gap: var(--wo-space-3);
}

@media (max-width: 640px) {
    .nav-content {
        flex-direction: column;
        gap: var(--wo-space-3);
    }
    
    .nav-buttons {
        width: 100%;
        justify-content: space-between;
    }
    
    .nav-primary-buttons {
        flex: 1;
        justify-content: flex-end;
    }
}

/* Loading Overlay */
.wizard-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-content {
    text-align: center;
    color: var(--wo-color-white);
}

/* Responsive Enhancements */
@media (max-width: 480px) {
    .wizard-header {
        padding: var(--wo-space-4) 0;
    }
    
    .wizard-progress-section {
        padding: var(--wo-space-4) 0;
    }
    
    .wizard-content {
        padding: var(--wo-space-6) 0;
    }
    
    .step-header {
        margin-bottom: var(--wo-space-6);
    }
    
    .wizard-navigation {
        padding: var(--wo-space-4) 0;
    }
}

/* Form Enhancements for Touch */
.wo-form-input,
.wo-form-select,
.wo-form-textarea {
    min-height: 44px; /* Touch-friendly minimum */
    font-size: 16px; /* Prevent zoom on iOS */
}

@media (max-width: 640px) {
    .wo-form-grid.wo-grid-cols-2 {
        grid-template-columns: 1fr;
    }
}

/* Required Indicator */
.required-indicator {
    color: var(--wo-color-red-500);
    margin-left: 2px;
}

/* Skip Link */
.wo-skip-link {
    position: absolute;
    top: -40px;
    left: 6px;
    background: var(--wo-color-primary);
    color: var(--wo-color-white);
    padding: 8px 16px;
    text-decoration: none;
    border-radius: var(--wo-border-radius);
    z-index: 10000;
    transition: top var(--wo-transition-fast);
}

.wo-skip-link:focus {
    top: 6px;
}

/* Print Styles */
@media print {
    .wizard-navigation,
    .wizard-loading-overlay {
        display: none !important;
    }
}
</style> 