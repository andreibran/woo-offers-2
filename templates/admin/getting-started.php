<?php
/**
 * Getting Started Guide Template
 * 
 * @package WooOffers
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap woo-offers-getting-started">
    <div class="getting-started-header">
        <div class="getting-started-logo">
            <h1><?php esc_html_e('Welcome to WooCommerce Offers', 'woo-offers'); ?></h1>
            <p class="subtitle"><?php esc_html_e('Let\'s get you started with creating amazing offers for your store!', 'woo-offers'); ?></p>
        </div>
        <div class="getting-started-close">
            <button type="button" class="button-link" id="dismiss-getting-started">
                <span class="dashicons dashicons-dismiss"></span>
                <?php esc_html_e('Skip for now', 'woo-offers'); ?>
            </button>
        </div>
    </div>

    <div class="getting-started-progress">
        <div class="progress-bar">
            <div class="progress-fill" style="width: 0%"></div>
        </div>
        <div class="progress-steps">
            <span class="step active" data-step="1"><?php esc_html_e('Overview', 'woo-offers'); ?></span>
            <span class="step" data-step="2"><?php esc_html_e('Setup', 'woo-offers'); ?></span>
            <span class="step" data-step="3"><?php esc_html_e('First Offer', 'woo-offers'); ?></span>
            <span class="step" data-step="4"><?php esc_html_e('Analytics', 'woo-offers'); ?></span>
            <span class="step" data-step="5"><?php esc_html_e('Complete', 'woo-offers'); ?></span>
        </div>
    </div>

    <!-- Step 1: Overview -->
    <div class="getting-started-step active" data-step="1">
        <div class="step-content">
            <div class="step-video">
                <div class="video-placeholder">
                    <div class="video-icon">
                        <span class="dashicons dashicons-video-alt3"></span>
                    </div>
                    <h3><?php esc_html_e('Plugin Overview', 'woo-offers'); ?></h3>
                    <p><?php esc_html_e('Watch this 2-minute overview to understand what WooCommerce Offers can do for your store.', 'woo-offers'); ?></p>
                    <div class="video-embed-placeholder" data-video="overview">
                        <!-- Video will be embedded here -->
                        <div class="placeholder-content">
                            <span class="play-button">▶</span>
                            <span class="duration">2:15</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="step-info">
                <h2><?php esc_html_e('What You Can Do', 'woo-offers'); ?></h2>
                <ul class="feature-list">
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <strong><?php esc_html_e('Percentage Discounts', 'woo-offers'); ?></strong>
                        <p><?php esc_html_e('Create percentage-based discounts for any product or category', 'woo-offers'); ?></p>
                    </li>
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <strong><?php esc_html_e('Fixed Amount Discounts', 'woo-offers'); ?></strong>
                        <p><?php esc_html_e('Set fixed dollar amount discounts with flexible conditions', 'woo-offers'); ?></p>
                    </li>
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <strong><?php esc_html_e('BOGO & Bundle Offers', 'woo-offers'); ?></strong>
                        <p><?php esc_html_e('Create buy-one-get-one and product bundle promotions', 'woo-offers'); ?></p>
                    </li>
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <strong><?php esc_html_e('Free Shipping', 'woo-offers'); ?></strong>
                        <p><?php esc_html_e('Offer free shipping based on cart value or product selection', 'woo-offers'); ?></p>
                    </li>
                    <li>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <strong><?php esc_html_e('Advanced Analytics', 'woo-offers'); ?></strong>
                        <p><?php esc_html_e('Track performance and optimize your offers with detailed reports', 'woo-offers'); ?></p>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Step 2: Setup -->
    <div class="getting-started-step" data-step="2">
        <div class="step-content">
            <div class="step-video">
                <div class="video-placeholder">
                    <div class="video-icon">
                        <span class="dashicons dashicons-admin-settings"></span>
                    </div>
                    <h3><?php esc_html_e('Plugin Configuration', 'woo-offers'); ?></h3>
                    <p><?php esc_html_e('Learn how to configure the basic settings for optimal performance.', 'woo-offers'); ?></p>
                    <div class="video-embed-placeholder" data-video="setup">
                        <div class="placeholder-content">
                            <span class="play-button">▶</span>
                            <span class="duration">3:45</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="step-info">
                <h2><?php esc_html_e('Essential Settings', 'woo-offers'); ?></h2>
                <div class="setup-checklist">
                    <div class="checklist-item">
                        <input type="checkbox" id="check-general" class="setup-checkbox">
                        <label for="check-general">
                            <strong><?php esc_html_e('General Settings', 'woo-offers'); ?></strong>
                            <p><?php esc_html_e('Configure display preferences and default behavior', 'woo-offers'); ?></p>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=woo-offers-settings')); ?>" class="button button-secondary">
                                <?php esc_html_e('Open Settings', 'woo-offers'); ?>
                            </a>
                        </label>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" id="check-styling" class="setup-checkbox">
                        <label for="check-styling">
                            <strong><?php esc_html_e('Styling Options', 'woo-offers'); ?></strong>
                            <p><?php esc_html_e('Customize colors and appearance to match your theme', 'woo-offers'); ?></p>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=woo-offers-settings&tab=styling')); ?>" class="button button-secondary">
                                <?php esc_html_e('Customize Styling', 'woo-offers'); ?>
                            </a>
                        </label>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" id="check-notifications" class="setup-checkbox">
                        <label for="check-notifications">
                            <strong><?php esc_html_e('Email Notifications', 'woo-offers'); ?></strong>
                            <p><?php esc_html_e('Set up automatic notifications for offer events', 'woo-offers'); ?></p>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=woo-offers-settings&tab=notifications')); ?>" class="button button-secondary">
                                <?php esc_html_e('Configure Emails', 'woo-offers'); ?>
                            </a>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: First Offer -->
    <div class="getting-started-step" data-step="3">
        <div class="step-content">
            <div class="step-video">
                <div class="video-placeholder">
                    <div class="video-icon">
                        <span class="dashicons dashicons-tag"></span>
                    </div>
                    <h3><?php esc_html_e('Creating Your First Offer', 'woo-offers'); ?></h3>
                    <p><?php esc_html_e('Follow along as we create a simple percentage discount offer step by step.', 'woo-offers'); ?></p>
                    <div class="video-embed-placeholder" data-video="first-offer">
                        <div class="placeholder-content">
                            <span class="play-button">▶</span>
                            <span class="duration">4:30</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="step-info">
                <h2><?php esc_html_e('Quick Start Offer', 'woo-offers'); ?></h2>
                <p><?php esc_html_e('Let\'s create a simple 10% discount offer to get you started:', 'woo-offers'); ?></p>
                
                <div class="offer-template">
                    <h3><?php esc_html_e('Suggested First Offer', 'woo-offers'); ?></h3>
                    <div class="template-preview">
                        <div class="template-icon">
                            <span class="dashicons dashicons-tickets-alt"></span>
                        </div>
                        <div class="template-info">
                            <h4><?php esc_html_e('Welcome Discount', 'woo-offers'); ?></h4>
                            <p><?php esc_html_e('10% off for new customers', 'woo-offers'); ?></p>
                            <ul class="template-details">
                                <li><?php esc_html_e('Type: Percentage Discount', 'woo-offers'); ?></li>
                                <li><?php esc_html_e('Discount: 10%', 'woo-offers'); ?></li>
                                <li><?php esc_html_e('Usage: Once per customer', 'woo-offers'); ?></li>
                                <li><?php esc_html_e('Duration: 30 days', 'woo-offers'); ?></li>
                            </ul>
                        </div>
                    </div>
                    <div class="template-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=woo-offers&action=create&template=welcome')); ?>" class="button button-primary">
                            <?php esc_html_e('Create This Offer', 'woo-offers'); ?>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=woo-offers&action=create')); ?>" class="button button-secondary">
                            <?php esc_html_e('Start From Scratch', 'woo-offers'); ?>
                        </a>
                    </div>
                </div>

                <div class="quick-tips">
                    <h4><?php esc_html_e('Pro Tips', 'woo-offers'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Start with simple offers and gradually add complexity', 'woo-offers'); ?></li>
                        <li><?php esc_html_e('Test your offers before launching to ensure they work as expected', 'woo-offers'); ?></li>
                        <li><?php esc_html_e('Monitor analytics to see which offers perform best', 'woo-offers'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 4: Analytics -->
    <div class="getting-started-step" data-step="4">
        <div class="step-content">
            <div class="step-video">
                <div class="video-placeholder">
                    <div class="video-icon">
                        <span class="dashicons dashicons-chart-line"></span>
                    </div>
                    <h3><?php esc_html_e('Understanding Your Analytics', 'woo-offers'); ?></h3>
                    <p><?php esc_html_e('Learn how to read and interpret your offer performance data.', 'woo-offers'); ?></p>
                    <div class="video-embed-placeholder" data-video="analytics">
                        <div class="placeholder-content">
                            <span class="play-button">▶</span>
                            <span class="duration">3:20</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="step-info">
                <h2><?php esc_html_e('Tracking Your Success', 'woo-offers'); ?></h2>
                <p><?php esc_html_e('Once your offers are live, you can track their performance with detailed analytics:', 'woo-offers'); ?></p>
                
                <div class="analytics-overview">
                    <div class="metric-card">
                        <div class="metric-icon">
                            <span class="dashicons dashicons-visibility"></span>
                        </div>
                        <div class="metric-info">
                            <h4><?php esc_html_e('Views & Impressions', 'woo-offers'); ?></h4>
                            <p><?php esc_html_e('See how many people viewed your offers', 'woo-offers'); ?></p>
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon">
                            <span class="dashicons dashicons-cart"></span>
                        </div>
                        <div class="metric-info">
                            <h4><?php esc_html_e('Conversions', 'woo-offers'); ?></h4>
                            <p><?php esc_html_e('Track how many offers resulted in purchases', 'woo-offers'); ?></p>
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon">
                            <span class="dashicons dashicons-money-alt"></span>
                        </div>
                        <div class="metric-info">
                            <h4><?php esc_html_e('Revenue Impact', 'woo-offers'); ?></h4>
                            <p><?php esc_html_e('Measure the financial impact of your offers', 'woo-offers'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="analytics-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=woo-offers-analytics')); ?>" class="button button-primary">
                        <?php esc_html_e('View Analytics Dashboard', 'woo-offers'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=woo-offers-help&tab=analytics')); ?>" class="button button-secondary">
                        <?php esc_html_e('Learn More', 'woo-offers'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 5: Complete -->
    <div class="getting-started-step" data-step="5">
        <div class="step-content completion-content">
            <div class="completion-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <h2><?php esc_html_e('You\'re All Set!', 'woo-offers'); ?></h2>
            <p class="completion-message">
                <?php esc_html_e('Congratulations! You now have everything you need to create amazing offers for your WooCommerce store.', 'woo-offers'); ?>
            </p>

            <div class="next-steps">
                <h3><?php esc_html_e('What\'s Next?', 'woo-offers'); ?></h3>
                <div class="next-step-cards">
                    <div class="next-step-card">
                        <div class="card-icon">
                            <span class="dashicons dashicons-plus-alt"></span>
                        </div>
                        <h4><?php esc_html_e('Create More Offers', 'woo-offers'); ?></h4>
                        <p><?php esc_html_e('Experiment with different offer types to find what works best for your customers.', 'woo-offers'); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=woo-offers&action=create')); ?>" class="button">
                            <?php esc_html_e('Create Offer', 'woo-offers'); ?>
                        </a>
                    </div>
                    <div class="next-step-card">
                        <div class="card-icon">
                            <span class="dashicons dashicons-admin-customizer"></span>
                        </div>
                        <h4><?php esc_html_e('Customize Appearance', 'woo-offers'); ?></h4>
                        <p><?php esc_html_e('Make your offers match your brand with custom colors and styling.', 'woo-offers'); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=woo-offers-settings&tab=styling')); ?>" class="button">
                            <?php esc_html_e('Customize', 'woo-offers'); ?>
                        </a>
                    </div>
                    <div class="next-step-card">
                        <div class="card-icon">
                            <span class="dashicons dashicons-sos"></span>
                        </div>
                        <h4><?php esc_html_e('Get Support', 'woo-offers'); ?></h4>
                        <p><?php esc_html_e('Need help? Access our comprehensive documentation and support resources.', 'woo-offers'); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=woo-offers-help')); ?>" class="button">
                            <?php esc_html_e('Get Help', 'woo-offers'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="completion-actions">
                <button type="button" class="button button-primary" id="complete-getting-started">
                    <?php esc_html_e('Complete Setup', 'woo-offers'); ?>
                </button>
                <button type="button" class="button button-secondary" id="restart-guide">
                    <?php esc_html_e('Restart Guide', 'woo-offers'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="getting-started-navigation">
        <button type="button" class="button button-secondary" id="prev-step" disabled>
            <span class="dashicons dashicons-arrow-left-alt2"></span>
            <?php esc_html_e('Previous', 'woo-offers'); ?>
        </button>
        <button type="button" class="button button-primary" id="next-step">
            <?php esc_html_e('Next', 'woo-offers'); ?>
            <span class="dashicons dashicons-arrow-right-alt2"></span>
        </button>
    </div>
</div>

<style>
.woo-offers-getting-started {
    max-width: 1200px;
    margin: 20px auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.getting-started-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 30px 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.getting-started-header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 600;
}

.getting-started-header .subtitle {
    margin: 5px 0 0 0;
    font-size: 16px;
    opacity: 0.9;
}

.getting-started-close button {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 14px;
}

.getting-started-close button:hover {
    color: white;
}

.getting-started-progress {
    padding: 30px 40px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.progress-bar {
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    margin-bottom: 20px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.3s ease;
    border-radius: 2px;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
}

.progress-steps .step {
    font-size: 14px;
    color: #6c757d;
    transition: color 0.3s ease;
}

.progress-steps .step.active {
    color: #667eea;
    font-weight: 600;
}

.progress-steps .step.completed {
    color: #28a745;
}

.getting-started-step {
    display: none;
    padding: 40px;
}

.getting-started-step.active {
    display: block;
}

.step-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: start;
}

.completion-content {
    display: block !important;
    text-align: center;
}

.step-video {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
}

.video-placeholder {
    max-width: 100%;
}

.video-icon {
    font-size: 48px;
    color: #667eea;
    margin-bottom: 20px;
}

.video-embed-placeholder {
    position: relative;
    background: #000;
    border-radius: 8px;
    aspect-ratio: 16/9;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 20px;
    cursor: pointer;
    overflow: hidden;
}

.placeholder-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: white;
}

.play-button {
    font-size: 60px;
    margin-bottom: 10px;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.video-embed-placeholder:hover .play-button {
    opacity: 1;
}

.duration {
    font-size: 14px;
    background: rgba(0,0,0,0.7);
    padding: 4px 8px;
    border-radius: 4px;
}

.step-info h2 {
    margin-top: 0;
    color: #2c3e50;
    font-size: 24px;
}

.feature-list {
    list-style: none;
    padding: 0;
}

.feature-list li {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.feature-list .dashicons {
    color: #28a745;
    margin-right: 15px;
    margin-top: 2px;
    font-size: 20px;
}

.feature-list strong {
    display: block;
    margin-bottom: 5px;
    color: #2c3e50;
}

.feature-list p {
    margin: 0;
    color: #6c757d;
    font-size: 14px;
}

.setup-checklist {
    space-y: 20px;
}

.checklist-item {
    display: flex;
    align-items: flex-start;
    padding: 20px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 15px;
    transition: border-color 0.3s ease;
}

.checklist-item:has(.setup-checkbox:checked) {
    border-color: #28a745;
    background: #f8fff9;
}

.setup-checkbox {
    margin-right: 15px;
    margin-top: 5px;
}

.offer-template {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 25px;
    margin: 20px 0;
}

.template-preview {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.template-icon {
    font-size: 40px;
    color: #667eea;
    margin-right: 20px;
}

.template-details {
    list-style: none;
    padding: 0;
    margin: 10px 0;
}

.template-details li {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 5px;
}

.template-actions {
    display: flex;
    gap: 10px;
}

.quick-tips {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 6px;
    padding: 20px;
    margin-top: 25px;
}

.quick-tips h4 {
    margin-top: 0;
    color: #856404;
}

.quick-tips ul {
    margin-bottom: 0;
}

.quick-tips li {
    color: #856404;
    margin-bottom: 8px;
}

.analytics-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 25px 0;
}

.metric-card {
    display: flex;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.metric-icon {
    font-size: 32px;
    color: #667eea;
    margin-right: 15px;
}

.metric-info h4 {
    margin: 0 0 5px 0;
    color: #2c3e50;
}

.metric-info p {
    margin: 0;
    font-size: 14px;
    color: #6c757d;
}

.analytics-actions {
    margin-top: 25px;
}

.analytics-actions .button {
    margin-right: 10px;
}

.completion-icon {
    font-size: 80px;
    color: #28a745;
    margin-bottom: 20px;
}

.completion-message {
    font-size: 18px;
    color: #6c757d;
    margin-bottom: 40px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.next-steps {
    margin-bottom: 40px;
}

.next-step-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-top: 25px;
}

.next-step-card {
    text-align: center;
    padding: 30px 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.card-icon {
    font-size: 40px;
    color: #667eea;
    margin-bottom: 15px;
}

.next-step-card h4 {
    margin-bottom: 10px;
    color: #2c3e50;
}

.next-step-card p {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 20px;
}

.completion-actions {
    margin-top: 30px;
}

.completion-actions .button {
    margin: 0 10px;
}

.getting-started-navigation {
    display: flex;
    justify-content: space-between;
    padding: 20px 40px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

@media (max-width: 768px) {
    .step-content {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .getting-started-header {
        padding: 20px;
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .progress-steps {
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }
    
    .next-step-cards {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let currentStep = 1;
    const totalSteps = 5;
    
    function updateProgress() {
        const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
        $('.progress-fill').css('width', progress + '%');
        
        $('.progress-steps .step').removeClass('active completed');
        $('.progress-steps .step').each(function() {
            const stepNum = parseInt($(this).data('step'));
            if (stepNum === currentStep) {
                $(this).addClass('active');
            } else if (stepNum < currentStep) {
                $(this).addClass('completed');
            }
        });
    }
    
    function showStep(step) {
        $('.getting-started-step').removeClass('active');
        $(`.getting-started-step[data-step="${step}"]`).addClass('active');
        
        $('#prev-step').prop('disabled', step === 1);
        
        if (step === totalSteps) {
            $('#next-step').text('<?php esc_html_e('Complete', 'woo-offers'); ?>').removeClass('button-primary').addClass('button-success');
        } else {
            $('#next-step').html('<?php esc_html_e('Next', 'woo-offers'); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>').removeClass('button-success').addClass('button-primary');
        }
        
        updateProgress();
    }
    
    $('#next-step').on('click', function() {
        if (currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
        } else {
            // Complete the guide
            completeGettingStarted();
        }
    });
    
    $('#prev-step').on('click', function() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });
    
    $('.progress-steps .step').on('click', function() {
        const step = parseInt($(this).data('step'));
        if (step <= currentStep || step === currentStep + 1) {
            currentStep = step;
            showStep(currentStep);
        }
    });
    
    $('#dismiss-getting-started, #complete-getting-started').on('click', function() {
        completeGettingStarted();
    });
    
    $('#restart-guide').on('click', function() {
        currentStep = 1;
        showStep(currentStep);
    });
    
    function completeGettingStarted() {
        $.post(ajaxurl, {
            action: 'woo_offers_dismiss_getting_started',
            nonce: '<?php echo wp_create_nonce('woo_offers_getting_started'); ?>'
        }, function(response) {
            if (response.success) {
                window.location.href = '<?php echo esc_url(admin_url('admin.php?page=woo-offers')); ?>';
            }
        });
    }
    
    // Auto-check setup items when visited
    $('a[href*="woo-offers-settings"]').on('click', function() {
        setTimeout(function() {
            $('#check-general').prop('checked', true);
            localStorage.setItem('woo_offers_setup_general', 'true');
        }, 1000);
    });
    
    // Load saved progress
    if (localStorage.getItem('woo_offers_setup_general')) {
        $('#check-general').prop('checked', true);
    }
    
    // Video placeholder interactions
    $('.video-embed-placeholder').on('click', function() {
        const videoType = $(this).data('video');
        // This would integrate with your video hosting solution
        console.log('Would play video:', videoType);
        // For now, just show a placeholder message
        $(this).html('<p style="color: white; padding: 20px;">Video player would load here<br><small>Integration with your video hosting service required</small></p>');
    });
    
    // Initialize
    showStep(currentStep);
});
</script> 