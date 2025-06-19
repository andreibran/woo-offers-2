<?php
/**
 * Help & Documentation Page Template
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap woo-offers-help">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <p class="description">
        <?php _e( 'Complete guide to using Woo Offers effectively in your WooCommerce store.', 'woo-offers' ); ?>
    </p>

    <div class="help-navigation">
        <nav class="nav-tab-wrapper woo-offers-help-nav">
            <a href="#getting-started" class="nav-tab nav-tab-active" data-tab="getting-started"><?php _e( 'Getting Started', 'woo-offers' ); ?></a>
            <a href="#quick-start-guide" class="nav-tab" data-tab="quick-start-guide"><?php _e( 'Quick Start Guide', 'woo-offers' ); ?></a>
            <a href="#creating-offers" class="nav-tab" data-tab="creating-offers"><?php _e( 'Creating Offers', 'woo-offers' ); ?></a>
            <a href="#managing-products" class="nav-tab" data-tab="managing-products"><?php _e( 'Product Management', 'woo-offers' ); ?></a>
            <a href="#customization" class="nav-tab" data-tab="customization"><?php _e( 'Customization', 'woo-offers' ); ?></a>
            <a href="#analytics" class="nav-tab" data-tab="analytics"><?php _e( 'Analytics & Testing', 'woo-offers' ); ?></a>
            <a href="#troubleshooting" class="nav-tab" data-tab="troubleshooting"><?php _e( 'Troubleshooting', 'woo-offers' ); ?></a>
            <a href="#faq" class="nav-tab" data-tab="faq"><?php _e( 'FAQ', 'woo-offers' ); ?></a>
        </nav>
    </div>

    <div class="help-content">
        <!-- Getting Started Section -->
        <div id="getting-started" class="help-section active">
            <h2><?php _e( 'Getting Started with Woo Offers', 'woo-offers' ); ?></h2>
            
            <div class="help-cards">
                <div class="help-card">
                    <h3><?php _e( '1. Plugin Setup', 'woo-offers' ); ?></h3>
                    <p><?php _e( 'Welcome to Woo Offers! Follow these steps to get started:', 'woo-offers' ); ?></p>
                    <ul>
                        <li><?php _e( 'Ensure WooCommerce is installed and activated', 'woo-offers' ); ?></li>
                        <li><?php _e( 'Activate the Woo Offers plugin', 'woo-offers' ); ?></li>
                        <li><?php _e( 'Configure basic settings in the Settings page', 'woo-offers' ); ?></li>
                        <li><?php _e( 'Create your first offer to test functionality', 'woo-offers' ); ?></li>
                    </ul>
                </div>

                <div class="help-card">
                    <h3><?php _e( '2. Quick Start Checklist', 'woo-offers' ); ?></h3>
                    <ul class="checklist">
                        <li><input type="checkbox" disabled> <?php _e( 'Install and activate plugin', 'woo-offers' ); ?></li>
                        <li><input type="checkbox"> <?php _e( 'Configure general settings', 'woo-offers' ); ?></li>
                        <li><input type="checkbox"> <?php _e( 'Set default colors and position', 'woo-offers' ); ?></li>
                        <li><input type="checkbox"> <?php _e( 'Create your first percentage discount', 'woo-offers' ); ?></li>
                        <li><input type="checkbox"> <?php _e( 'Test offer on frontend', 'woo-offers' ); ?></li>
                        <li><input type="checkbox"> <?php _e( 'Review analytics after first sales', 'woo-offers' ); ?></li>
                    </ul>
                </div>

                <div class="help-card">
                    <h3><?php _e( '3. Understanding Offer Types', 'woo-offers' ); ?></h3>
                    <div class="offer-types">
                        <div class="offer-type">
                            <strong><?php _e( 'Percentage Discount', 'woo-offers' ); ?></strong>
                            <p><?php _e( 'Most common type. Offers X% off product price. Great for promotions and sales.', 'woo-offers' ); ?></p>
                        </div>
                        <div class="offer-type">
                            <strong><?php _e( 'Fixed Amount', 'woo-offers' ); ?></strong>
                            <p><?php _e( 'Offers specific dollar amount off. Works well for higher-priced items.', 'woo-offers' ); ?></p>
                        </div>
                        <div class="offer-type">
                            <strong><?php _e( 'BOGO (Buy One Get One)', 'woo-offers' ); ?></strong>
                            <p><?php _e( 'Encourages bulk purchases. Great for inventory clearance.', 'woo-offers' ); ?></p>
                        </div>
                        <div class="offer-type">
                            <strong><?php _e( 'Free Shipping', 'woo-offers' ); ?></strong>
                            <p><?php _e( 'Removes shipping costs. Effective for increasing conversions.', 'woo-offers' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Start Guide Section -->
        <div id="quick-start-guide" class="help-section">
            <h2><?php _e( 'Quick Start Guide - Create Your First Offer', 'woo-offers' ); ?></h2>
            <p class="guide-intro">
                <?php _e( 'Follow this step-by-step guide to create your first offer in under 5 minutes. Each step includes detailed instructions and helpful tips.', 'woo-offers' ); ?>
            </p>

            <div class="quick-start-progress">
                <div class="progress-bar">
                    <div class="progress-fill" id="quick-start-progress-fill"></div>
                </div>
                <span class="progress-text" id="quick-start-progress-text"><?php _e( 'Step 0 of 5 complete', 'woo-offers' ); ?></span>
            </div>

            <div class="quick-start-steps">
                <!-- Step 1: Access Create Offer -->
                <div class="quick-start-step" data-step="1">
                    <div class="step-header">
                        <div class="step-number">1</div>
                        <h3><?php _e( 'Access the Create Offer Page', 'woo-offers' ); ?></h3>
                        <button class="step-toggle" type="button">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                    </div>
                    <div class="step-content">
                        <div class="step-video">
                            <div class="video-placeholder">
                                <div class="video-icon">
                                    <span class="dashicons dashicons-video-alt3"></span>
                                </div>
                                <p><?php _e( 'Video: Navigating to Create Offer', 'woo-offers' ); ?></p>
                                <button class="play-video-btn" data-video="navigation"><?php _e( 'Play Video', 'woo-offers' ); ?></button>
                            </div>
                        </div>
                        <div class="step-instructions">
                            <ol>
                                <li><?php _e( 'In your WordPress admin dashboard, navigate to the left sidebar', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Click on "Woo Offers" to expand the menu', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Select "Create Offer" from the dropdown menu', 'woo-offers' ); ?></li>
                            </ol>
                            <div class="step-tip">
                                <span class="dashicons dashicons-lightbulb"></span>
                                <strong><?php _e( 'Tip:', 'woo-offers' ); ?></strong>
                                <?php _e( 'You can also access this page directly by clicking "Create New Offer" from the dashboard.', 'woo-offers' ); ?>
                            </div>
                            <button class="complete-step-btn" data-step="1"><?php _e( 'Mark as Complete', 'woo-offers' ); ?></button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Choose Offer Type -->
                <div class="quick-start-step" data-step="2">
                    <div class="step-header">
                        <div class="step-number">2</div>
                        <h3><?php _e( 'Choose Your Offer Type', 'woo-offers' ); ?></h3>
                        <button class="step-toggle" type="button">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                    </div>
                    <div class="step-content">
                        <div class="step-video">
                            <div class="video-placeholder">
                                <div class="video-icon">
                                    <span class="dashicons dashicons-video-alt3"></span>
                                </div>
                                <p><?php _e( 'Video: Selecting Offer Types', 'woo-offers' ); ?></p>
                                <button class="play-video-btn" data-video="offer-types"><?php _e( 'Play Video', 'woo-offers' ); ?></button>
                            </div>
                        </div>
                        <div class="step-instructions">
                            <p><?php _e( 'For your first offer, we recommend starting with a percentage discount:', 'woo-offers' ); ?></p>
                            <ol>
                                <li><?php _e( 'In the General Settings metabox, select "Percentage Discount"', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Enter a value between 10-25% for your first offer', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Leave usage limit blank for unlimited usage', 'woo-offers' ); ?></li>
                            </ol>
                            <div class="step-recommendation">
                                <span class="dashicons dashicons-star-filled"></span>
                                <strong><?php _e( 'Recommended for beginners:', 'woo-offers' ); ?></strong>
                                <?php _e( '15% percentage discount with no minimum order amount.', 'woo-offers' ); ?>
                            </div>
                            <button class="complete-step-btn" data-step="2"><?php _e( 'Mark as Complete', 'woo-offers' ); ?></button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Select Products -->
                <div class="quick-start-step" data-step="3">
                    <div class="step-header">
                        <div class="step-number">3</div>
                        <h3><?php _e( 'Select Products for Your Offer', 'woo-offers' ); ?></h3>
                        <button class="step-toggle" type="button">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                    </div>
                    <div class="step-content">
                        <div class="step-video">
                            <div class="video-placeholder">
                                <div class="video-icon">
                                    <span class="dashicons dashicons-video-alt3"></span>
                                </div>
                                <p><?php _e( 'Video: Product Selection Process', 'woo-offers' ); ?></p>
                                <button class="play-video-btn" data-video="product-selection"><?php _e( 'Play Video', 'woo-offers' ); ?></button>
                            </div>
                        </div>
                        <div class="step-instructions">
                            <ol>
                                <li><?php _e( 'Go to the "Products" metabox', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Use the search box to find products by name or SKU', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Click "Add Product" for each product you want to include', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Set quantity requirements if needed', 'woo-offers' ); ?></li>
                            </ol>
                            <div class="step-tip">
                                <span class="dashicons dashicons-lightbulb"></span>
                                <strong><?php _e( 'Tip:', 'woo-offers' ); ?></strong>
                                <?php _e( 'Start with 1-3 related products for your first offer to keep it simple.', 'woo-offers' ); ?>
                            </div>
                            <button class="complete-step-btn" data-step="3"><?php _e( 'Mark as Complete', 'woo-offers' ); ?></button>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Customize Appearance -->
                <div class="quick-start-step" data-step="4">
                    <div class="step-header">
                        <div class="step-number">4</div>
                        <h3><?php _e( 'Customize Offer Appearance', 'woo-offers' ); ?></h3>
                        <button class="step-toggle" type="button">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                    </div>
                    <div class="step-content">
                        <div class="step-video">
                            <div class="video-placeholder">
                                <div class="video-icon">
                                    <span class="dashicons dashicons-video-alt3"></span>
                                </div>
                                <p><?php _e( 'Video: Customizing Offer Design', 'woo-offers' ); ?></p>
                                <button class="play-video-btn" data-video="customization"><?php _e( 'Play Video', 'woo-offers' ); ?></button>
                            </div>
                        </div>
                        <div class="step-instructions">
                            <ol>
                                <li><?php _e( 'Navigate to the "Appearance" metabox', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Choose colors that match your brand', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Select "Before Add to Cart Button" for position', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Choose "Card" layout for best visibility', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Use the live preview to see your changes', 'woo-offers' ); ?></li>
                            </ol>
                            <div class="step-recommendation">
                                <span class="dashicons dashicons-star-filled"></span>
                                <strong><?php _e( 'Recommended settings:', 'woo-offers' ); ?></strong>
                                <?php _e( 'Green background (#46b450), white text, card layout, before add to cart position.', 'woo-offers' ); ?>
                            </div>
                            <button class="complete-step-btn" data-step="4"><?php _e( 'Mark as Complete', 'woo-offers' ); ?></button>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Publish and Test -->
                <div class="quick-start-step" data-step="5">
                    <div class="step-header">
                        <div class="step-number">5</div>
                        <h3><?php _e( 'Publish and Test Your Offer', 'woo-offers' ); ?></h3>
                        <button class="step-toggle" type="button">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                    </div>
                    <div class="step-content">
                        <div class="step-video">
                            <div class="video-placeholder">
                                <div class="video-icon">
                                    <span class="dashicons dashicons-video-alt3"></span>
                                </div>
                                <p><?php _e( 'Video: Publishing and Testing', 'woo-offers' ); ?></p>
                                <button class="play-video-btn" data-video="publish-test"><?php _e( 'Play Video', 'woo-offers' ); ?></button>
                            </div>
                        </div>
                        <div class="step-instructions">
                            <ol>
                                <li><?php _e( 'Give your offer a descriptive title', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Use the "Preview in Modal" button to test your offer', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Click "Publish" to make your offer live', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Visit a product page to see your offer in action', 'woo-offers' ); ?></li>
                                <li><?php _e( 'Test the offer by adding products to cart', 'woo-offers' ); ?></li>
                            </ol>
                            <div class="step-success">
                                <span class="dashicons dashicons-yes"></span>
                                <strong><?php _e( 'Congratulations!', 'woo-offers' ); ?></strong>
                                <?php _e( 'You\'ve created your first offer. Monitor its performance in the Analytics section.', 'woo-offers' ); ?>
                            </div>
                            <button class="complete-step-btn" data-step="5"><?php _e( 'Mark as Complete', 'woo-offers' ); ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="guide-completion" id="guide-completion" style="display: none;">
                <div class="completion-card">
                    <div class="completion-icon">
                        <span class="dashicons dashicons-awards"></span>
                    </div>
                    <h3><?php _e( 'Quick Start Guide Complete!', 'woo-offers' ); ?></h3>
                    <p><?php _e( 'You\'ve successfully completed the quick start guide. Your first offer is now live and ready to start generating sales.', 'woo-offers' ); ?></p>
                    <div class="next-steps">
                        <h4><?php _e( 'What\'s Next?', 'woo-offers' ); ?></h4>
                        <ul>
                            <li><a href="<?php echo admin_url('admin.php?page=woo-offers-analytics'); ?>"><?php _e( 'Monitor offer performance in Analytics', 'woo-offers' ); ?></a></li>
                            <li><a href="#creating-offers" class="help-nav-link" data-tab="creating-offers"><?php _e( 'Learn advanced offer creation techniques', 'woo-offers' ); ?></a></li>
                            <li><a href="#customization" class="help-nav-link" data-tab="customization"><?php _e( 'Explore design customization options', 'woo-offers' ); ?></a></li>
                            <li><a href="<?php echo admin_url('admin.php?page=woo-offers-create'); ?>"><?php _e( 'Create another offer', 'woo-offers' ); ?></a></li>
                        </ul>
                    </div>
                    <button class="reset-guide-btn" type="button"><?php _e( 'Reset Guide', 'woo-offers' ); ?></button>
                </div>
            </div>
        </div>

        <!-- Creating Offers Section -->
        <div id="creating-offers" class="help-section">
            <h2><?php _e( 'Creating Effective Offers', 'woo-offers' ); ?></h2>
            
            <div class="help-subsection">
                <h3><?php _e( 'Step-by-Step Offer Creation', 'woo-offers' ); ?></h3>
                <div class="step-guide">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4><?php _e( 'Choose Offer Type', 'woo-offers' ); ?></h4>
                            <p><?php _e( 'Select the discount type that best fits your marketing goal. Consider your profit margins and customer behavior.', 'woo-offers' ); ?></p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4><?php _e( 'Set Discount Value', 'woo-offers' ); ?></h4>
                            <p><?php _e( 'Enter the discount amount. For percentages, use values between 5-50%. For fixed amounts, consider your average order value.', 'woo-offers' ); ?></p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4><?php _e( 'Configure Conditions', 'woo-offers' ); ?></h4>
                            <p><?php _e( 'Set minimum/maximum order amounts and usage limits to control offer impact on revenue.', 'woo-offers' ); ?></p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4><?php _e( 'Select Products', 'woo-offers' ); ?></h4>
                            <p><?php _e( 'Choose which products the offer applies to. You can select specific products or entire categories.', 'woo-offers' ); ?></p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">5</div>
                        <div class="step-content">
                            <h4><?php _e( 'Customize Appearance', 'woo-offers' ); ?></h4>
                            <p><?php _e( 'Design your offer to match your brand. Use high contrast colors and clear, compelling text.', 'woo-offers' ); ?></p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">6</div>
                        <div class="step-content">
                            <h4><?php _e( 'Preview & Test', 'woo-offers' ); ?></h4>
                            <p><?php _e( 'Always preview your offer before publishing. Test on different devices and screen sizes.', 'woo-offers' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="help-subsection">
                <h3><?php _e( 'Best Practices for Offer Creation', 'woo-offers' ); ?></h3>
                <div class="best-practices">
                    <div class="practice-item">
                        <span class="dashicons dashicons-yes"></span>
                        <div>
                            <strong><?php _e( 'Keep It Simple', 'woo-offers' ); ?></strong>
                            <p><?php _e( 'Clear, straightforward offers perform better than complex conditions.', 'woo-offers' ); ?></p>
                        </div>
                    </div>
                    <div class="practice-item">
                        <span class="dashicons dashicons-yes"></span>
                        <div>
                            <strong><?php _e( 'Create Urgency', 'woo-offers' ); ?></strong>
                            <p><?php _e( 'Use time limits and usage limits to encourage immediate action.', 'woo-offers' ); ?></p>
                        </div>
                    </div>
                    <div class="practice-item">
                        <span class="dashicons dashicons-yes"></span>
                        <div>
                            <strong><?php _e( 'Test Different Positions', 'woo-offers' ); ?></strong>
                            <p><?php _e( 'Try offers before/after add to cart button to find what converts best.', 'woo-offers' ); ?></p>
                        </div>
                    </div>
                    <div class="practice-item">
                        <span class="dashicons dashicons-yes"></span>
                        <div>
                            <strong><?php _e( 'Monitor Performance', 'woo-offers' ); ?></strong>
                            <p><?php _e( 'Use analytics to track which offers generate the most revenue.', 'woo-offers' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Managing Products Section -->
        <div id="managing-products" class="help-section">
            <h2><?php _e( 'Product Management Guide', 'woo-offers' ); ?></h2>
            
            <div class="help-subsection">
                <h3><?php _e( 'Product Selection Strategies', 'woo-offers' ); ?></h3>
                <div class="strategy-grid">
                    <div class="strategy-card">
                        <h4><?php _e( 'High-Margin Products', 'woo-offers' ); ?></h4>
                        <p><?php _e( 'Apply offers to products with higher profit margins to maintain profitability while increasing sales volume.', 'woo-offers' ); ?></p>
                    </div>
                    <div class="strategy-card">
                        <h4><?php _e( 'Slow-Moving Inventory', 'woo-offers' ); ?></h4>
                        <p><?php _e( 'Use offers to clear inventory that is not selling well. Consider BOGO offers for overstocked items.', 'woo-offers' ); ?></p>
                    </div>
                    <div class="strategy-card">
                        <h4><?php _e( 'Complementary Products', 'woo-offers' ); ?></h4>
                        <p><?php _e( 'Bundle related products together to increase average order value and improve customer experience.', 'woo-offers' ); ?></p>
                    </div>
                    <div class="strategy-card">
                        <h4><?php _e( 'New Product Launches', 'woo-offers' ); ?></h4>
                        <p><?php _e( 'Use introductory offers to generate initial sales and reviews for new products.', 'woo-offers' ); ?></p>
                    </div>
                </div>
            </div>

            <div class="help-subsection">
                <h3><?php _e( 'Product Search & Organization', 'woo-offers' ); ?></h3>
                <div class="feature-explanation">
                    <h4><?php _e( 'Search Functionality', 'woo-offers' ); ?></h4>
                    <ul>
                        <li><?php _e( 'Search by product name, SKU, or product ID', 'woo-offers' ); ?></li>
                        <li><?php _e( 'Type at least 2 characters to see results', 'woo-offers' ); ?></li>
                        <li><?php _e( 'Search results show product image, price, and type', 'woo-offers' ); ?></li>
                        <li><?php _e( 'Click "Add Product" to include in the offer', 'woo-offers' ); ?></li>
                    </ul>

                    <h4><?php _e( 'Managing Selected Products', 'woo-offers' ); ?></h4>
                    <ul>
                        <li><?php _e( 'Adjust quantity requirements for each product', 'woo-offers' ); ?></li>
                        <li><?php _e( 'Remove products that no longer fit the offer', 'woo-offers' ); ?></li>
                        <li><?php _e( 'Use "Clear All" to start over with product selection', 'woo-offers' ); ?></li>
                        <li><?php _e( 'Products show current price and availability status', 'woo-offers' ); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Customization Section -->
        <div id="customization" class="help-section">
            <h2><?php _e( 'Customization & Appearance', 'woo-offers' ); ?></h2>
            
            <div class="help-subsection">
                <h3><?php _e( 'Design Best Practices', 'woo-offers' ); ?></h3>
                <div class="design-tips">
                    <div class="tip-item">
                        <h4><?php _e( 'Color Psychology', 'woo-offers' ); ?></h4>
                        <ul>
                            <li><?php _e( 'Red: Creates urgency and excitement (great for sales)', 'woo-offers' ); ?></li>
                            <li><?php _e( 'Green: Suggests savings and money (effective for discounts)', 'woo-offers' ); ?></li>
                            <li><?php _e( 'Blue: Professional and trustworthy (good for premium offers)', 'woo-offers' ); ?></li>
                            <li><?php _e( 'Orange: Friendly and approachable (works for clearance)', 'woo-offers' ); ?></li>
                        </ul>
                    </div>
                    
                    <div class="tip-item">
                        <h4><?php _e( 'Layout Considerations', 'woo-offers' ); ?></h4>
                        <ul>
                            <li><?php _e( 'Card layout: Best for most offers, provides clear boundaries', 'woo-offers' ); ?></li>
                            <li><?php _e( 'Banner layout: Good for store-wide promotions', 'woo-offers' ); ?></li>
                            <li><?php _e( 'Modal popup: High visibility but can be intrusive', 'woo-offers' ); ?></li>
                            <li><?php _e( 'Inline layout: Subtle integration with page content', 'woo-offers' ); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics & Testing Section -->
        <div id="analytics" class="help-section">
            <h2><?php _e( 'Analytics & A/B Testing', 'woo-offers' ); ?></h2>
            
            <div class="help-subsection">
                <h3><?php _e( 'Understanding Analytics', 'woo-offers' ); ?></h3>
                <div class="metrics-explanation">
                    <div class="metric-item">
                        <h4><?php _e( 'Impressions', 'woo-offers' ); ?></h4>
                        <p><?php _e( 'Number of times your offer was displayed to customers. High impressions with low conversions may indicate poor offer positioning or targeting.', 'woo-offers' ); ?></p>
                    </div>
                    <div class="metric-item">
                        <h4><?php _e( 'Conversion Rate', 'woo-offers' ); ?></h4>
                        <p><?php _e( 'Percentage of people who saw the offer and used it. Industry average is 2-5%. Higher rates indicate compelling offers.', 'woo-offers' ); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Troubleshooting Section -->
        <div id="troubleshooting" class="help-section">
            <h2><?php _e( 'Troubleshooting Common Issues', 'woo-offers' ); ?></h2>
            
            <div class="troubleshooting-items">
                <div class="trouble-item">
                    <h3><?php _e( 'Offers Not Displaying', 'woo-offers' ); ?></h3>
                    <div class="solutions">
                        <h4><?php _e( 'Possible Causes & Solutions:', 'woo-offers' ); ?></h4>
                        <ul>
                            <li><?php _e( 'Check if offer is published and active', 'woo-offers' ); ?></li>
                            <li><?php _e( 'Verify product is selected in offer settings', 'woo-offers' ); ?></li>
                            <li><?php _e( 'Ensure minimum order requirements are met', 'woo-offers' ); ?></li>
                            <li><?php _e( 'Check theme compatibility - some themes override WooCommerce hooks', 'woo-offers' ); ?></li>
                            <li><?php _e( 'Clear any caching plugins and browser cache', 'woo-offers' ); ?></li>
                        </ul>
                    </div>
                </div>

                <div class="trouble-item">
                    <h3><?php _e( 'Styling Issues', 'woo-offers' ); ?></h3>
                    <div class="solutions">
                        <h4><?php _e( 'Common Fixes:', 'woo-offers' ); ?></h4>
                        <ul>
                            <li><?php _e( 'Check for CSS conflicts with theme styles', 'woo-offers' ); ?></li>
                            <li><?php _e( 'Adjust colors for better contrast and visibility', 'woo-offers' ); ?></li>
                            <li><?php _e( 'Test responsive design on mobile devices', 'woo-offers' ); ?></li>
                            <li><?php _e( 'Use browser developer tools to inspect elements', 'woo-offers' ); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div id="faq" class="help-section">
            <h2><?php _e( 'Frequently Asked Questions', 'woo-offers' ); ?></h2>
            
            <div class="faq-items">
                <div class="faq-item">
                    <h3><?php _e( 'Can I create multiple offers for the same product?', 'woo-offers' ); ?></h3>
                    <p><?php _e( 'Yes, but only one offer will display at a time based on priority and conditions. The system automatically selects the best offer for each customer.', 'woo-offers' ); ?></p>
                </div>

                <div class="faq-item">
                    <h3><?php _e( 'Do offers work with variable products?', 'woo-offers' ); ?></h3>
                    <p><?php _e( 'Yes, offers are compatible with variable products. The discount applies to the selected variation price.', 'woo-offers' ); ?></p>
                </div>

                <div class="faq-item">
                    <h3><?php _e( 'Can I schedule offers to start and end automatically?', 'woo-offers' ); ?></h3>
                    <p><?php _e( 'This feature is planned for future updates. Currently, offers need to be manually activated and deactivated.', 'woo-offers' ); ?></p>
                </div>

                <div class="faq-item">
                    <h3><?php _e( 'How do offers interact with WooCommerce coupons?', 'woo-offers' ); ?></h3>
                    <p><?php _e( 'Offers are applied at display time and do not conflict with coupon codes. Both can be used together for maximum savings.', 'woo-offers' ); ?></p>
                </div>

                <div class="faq-item">
                    <h3><?php _e( 'Can I create offers for specific user roles?', 'woo-offers' ); ?></h3>
                    <p><?php _e( 'This advanced feature is planned for the premium version. Currently, offers apply to all customers who meet the conditions.', 'woo-offers' ); ?></p>
                </div>

                <div class="faq-item">
                    <h3><?php _e( 'Are offers mobile-friendly?', 'woo-offers' ); ?></h3>
                    <p><?php _e( 'Yes, all offer layouts are responsive and optimized for mobile devices. Test your offers on various screen sizes for best results.', 'woo-offers' ); ?></p>
                </div>

                <div class="faq-item">
                    <h3><?php _e( 'Can I track which offers generate the most revenue?', 'woo-offers' ); ?></h3>
                    <p><?php _e( 'Yes, use the Analytics section to view performance metrics including conversion rates, revenue impact, and customer engagement.', 'woo-offers' ); ?></p>
                </div>
            </div>

            <div class="support-section">
                <h3><?php _e( 'Still Need Help?', 'woo-offers' ); ?></h3>
                <div class="support-options">
                    <div class="support-option">
                        <h4><?php _e( 'Documentation', 'woo-offers' ); ?></h4>
                        <p><?php _e( 'Visit our comprehensive online documentation for detailed guides and tutorials.', 'woo-offers' ); ?></p>
                        <a href="https://woooffers.com/docs" target="_blank" class="button button-secondary">
                            <?php _e( 'View Documentation', 'woo-offers' ); ?>
                        </a>
                    </div>
                    <div class="support-option">
                        <h4><?php _e( 'Support Forum', 'woo-offers' ); ?></h4>
                        <p><?php _e( 'Connect with other users and get help from our support team.', 'woo-offers' ); ?></p>
                        <a href="https://woooffers.com/support" target="_blank" class="button button-secondary">
                            <?php _e( 'Visit Forum', 'woo-offers' ); ?>
                        </a>
                    </div>
                    <div class="support-option">
                        <h4><?php _e( 'Video Tutorials', 'woo-offers' ); ?></h4>
                        <p><?php _e( 'Watch step-by-step video guides for common tasks and advanced features.', 'woo-offers' ); ?></p>
                        <a href="https://woooffers.com/tutorials" target="_blank" class="button button-secondary">
                            <?php _e( 'Watch Videos', 'woo-offers' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.woo-offers-help {
    max-width: 1200px;
}

.help-navigation {
    margin: 20px 0;
}

.woo-offers-help-nav {
    border-bottom: 1px solid #ccc;
    margin-bottom: 20px;
}

.help-section {
    display: none;
}

.help-section.active {
    display: block;
}

.help-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.help-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.help-card h3 {
    margin-top: 0;
    color: #23282d;
}

.checklist {
    list-style: none;
    padding: 0;
}

.checklist li {
    margin: 8px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.offer-types {
    display: grid;
    gap: 15px;
}

.offer-type {
    padding: 10px;
    background: #f9f9f9;
    border-left: 4px solid #0073aa;
}

.step-guide {
    display: grid;
    gap: 20px;
}

.step {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.step-number {
    background: #0073aa;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.step-content h4 {
    margin: 0 0 8px 0;
    color: #23282d;
}

.best-practices {
    display: grid;
    gap: 15px;
}

.practice-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.practice-item .dashicons {
    color: #46b450;
    margin-top: 2px;
}

.strategy-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.strategy-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #e9ecef;
}

.strategy-card h4 {
    margin-top: 0;
    color: #495057;
}

.feature-explanation h4 {
    color: #23282d;
    margin-top: 20px;
}

.feature-explanation ul {
    margin-bottom: 20px;
}

.design-tips {
    display: grid;
    gap: 20px;
}

.tip-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #0073aa;
}

.tip-item h4 {
    margin-top: 0;
    color: #23282d;
}

.animation-guide {
    display: grid;
    gap: 15px;
}

.animation-item {
    padding: 10px;
    background: #f9f9f9;
    border-radius: 3px;
}

.metrics-explanation {
    display: grid;
    gap: 20px;
}

.metric-item {
    background: #fff;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 5px;
}

.metric-item h4 {
    margin-top: 0;
    color: #0073aa;
}

.testing-guide h4 {
    color: #23282d;
    margin-top: 20px;
}

.troubleshooting-items {
    display: grid;
    gap: 25px;
}

.trouble-item {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    overflow: hidden;
}

.trouble-item h3 {
    background: #f1f1f1;
    margin: 0;
    padding: 15px;
    border-bottom: 1px solid #ddd;
}

.trouble-item .solutions {
    padding: 15px;
}

.trouble-item .solutions h4 {
    margin-top: 0;
    color: #d63638;
}

.faq-items {
    display: grid;
    gap: 20px;
}

.faq-item {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.faq-item h3 {
    margin-top: 0;
    color: #0073aa;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.support-section {
    margin-top: 40px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 5px;
}

.support-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.support-option {
    background: #fff;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
    text-align: center;
}

.support-option h4 {
    margin-top: 0;
    color: #23282d;
}

.support-option .button {
    margin-top: 10px;
}

@media (max-width: 768px) {
    .help-cards,
    .strategy-grid,
    .design-tips,
    .support-options {
        grid-template-columns: 1fr;
    }
    
    .step {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .woo-offers-help-nav {
        flex-wrap: wrap;
    }
    
    .woo-offers-help-nav .nav-tab {
        margin-bottom: 5px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab navigation
    $('.woo-offers-help-nav a').on('click', function(e) {
        e.preventDefault();
        
        var targetTab = $(this).data('tab');
        
        // Update active tab
        $('.woo-offers-help-nav a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show target section
        $('.help-section').removeClass('active');
        $('#' + targetTab).addClass('active');
        
        // Update URL hash
        window.location.hash = targetTab;
    });
    
    // Handle initial hash on page load
    if (window.location.hash) {
        var hash = window.location.hash.substring(1);
        var targetLink = $('.woo-offers-help-nav a[data-tab="' + hash + '"]');
        if (targetLink.length) {
            targetLink.click();
        }
    }
});
</script> 