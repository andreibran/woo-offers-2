<?php
namespace WooOffers\Admin;

/**
 * Setup Wizard for initial plugin configuration
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * SetupWizard class for guiding users through initial plugin setup
 */
class SetupWizard {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_wizard_page' ] );
        add_action( 'wp_ajax_woo_offers_setup_step', [ $this, 'process_setup_step' ] );
    }

    /**
     * Add setup wizard page to admin menu
     */
    public function add_wizard_page() {
        // Only show setup wizard if not completed
        if ( get_option( 'woo_offers_wizard_completed', false ) ) {
            return;
        }

        add_submenu_page(
            null, // No parent menu (hidden)
            __( 'Woo Offers Setup', 'woo-offers' ),
            __( 'Setup', 'woo-offers' ),
            'manage_woocommerce',
            'woo-offers-setup',
            [ $this, 'render_wizard_page' ]
        );
    }

    /**
     * Render setup wizard page
     */
    public function render_wizard_page() {
        $current_step = intval( $_GET['step'] ?? 1 );
        ?>
        <div class="wrap woo-offers-setup-wizard">
            <h1><?php _e( 'Woo Offers Setup Wizard', 'woo-offers' ); ?></h1>
            
            <div class="setup-progress">
                <?php $this->render_progress_bar( $current_step ); ?>
            </div>

            <div class="setup-content">
                <?php $this->render_step_content( $current_step ); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render progress bar
     * 
     * @param int $current_step Current step number
     */
    private function render_progress_bar( $current_step ) {
        $steps = [
            1 => __( 'Welcome', 'woo-offers' ),
            2 => __( 'Basic Settings', 'woo-offers' ),
            3 => __( 'Display Options', 'woo-offers' ),
            4 => __( 'Complete', 'woo-offers' )
        ];

        echo '<div class="progress-bar">';
        foreach ( $steps as $step_num => $step_name ) {
            $class = $step_num <= $current_step ? 'active' : '';
            echo '<div class="step ' . esc_attr( $class ) . '">';
            echo '<span class="step-number">' . esc_html( $step_num ) . '</span>';
            echo '<span class="step-name">' . esc_html( $step_name ) . '</span>';
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * Render step content
     * 
     * @param int $step Step number
     */
    private function render_step_content( $step ) {
        switch ( $step ) {
            case 1:
                $this->render_welcome_step();
                break;
            case 2:
                $this->render_basic_settings_step();
                break;
            case 3:
                $this->render_display_options_step();
                break;
            case 4:
                $this->render_complete_step();
                break;
            default:
                $this->render_welcome_step();
        }
    }

    /**
     * Render welcome step
     */
    private function render_welcome_step() {
        ?>
        <div class="setup-step welcome-step">
            <h2><?php _e( 'Welcome to Woo Offers!', 'woo-offers' ); ?></h2>
            <p><?php _e( 'Thank you for choosing Woo Offers to boost your sales with powerful upsell and cross-sell offers.', 'woo-offers' ); ?></p>
            <p><?php _e( 'This wizard will help you configure the basic settings to get started quickly.', 'woo-offers' ); ?></p>
            
            <div class="features-grid">
                <div class="feature-item">
                    <span class="dashicons dashicons-tag"></span>
                    <h3><?php _e( 'Multiple Offer Types', 'woo-offers' ); ?></h3>
                    <p><?php _e( 'Create percentage discounts, fixed amounts, BOGO offers, and more.', 'woo-offers' ); ?></p>
                </div>
                <div class="feature-item">
                    <span class="dashicons dashicons-chart-line"></span>
                    <h3><?php _e( 'Analytics & Insights', 'woo-offers' ); ?></h3>
                    <p><?php _e( 'Track performance and optimize your offers with detailed analytics.', 'woo-offers' ); ?></p>
                </div>
                <div class="feature-item">
                    <span class="dashicons dashicons-admin-appearance"></span>
                    <h3><?php _e( 'Customizable Appearance', 'woo-offers' ); ?></h3>
                    <p><?php _e( 'Match your brand with flexible styling and positioning options.', 'woo-offers' ); ?></p>
                </div>
            </div>

            <div class="step-actions">
                <a href="<?php echo admin_url( 'admin.php?page=woo-offers-setup&step=2' ); ?>" 
                   class="button button-primary button-large">
                    <?php _e( 'Get Started', 'woo-offers' ); ?>
                </a>
                <a href="<?php echo admin_url( 'admin.php?page=woo-offers' ); ?>" 
                   class="button button-secondary">
                    <?php _e( 'Skip Setup', 'woo-offers' ); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render basic settings step
     */
    private function render_basic_settings_step() {
        $settings = get_option( 'woo_offers_settings', [] );
        ?>
        <div class="setup-step basic-settings-step">
            <h2><?php _e( 'Basic Settings', 'woo-offers' ); ?></h2>
            <p><?php _e( 'Configure the essential settings for your offers.', 'woo-offers' ); ?></p>

            <form method="post" class="setup-form" id="basic-settings-form">
                <?php wp_nonce_field( 'woo_offers_setup', 'setup_nonce' ); ?>
                <input type="hidden" name="step" value="2" />

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enable_analytics"><?php _e( 'Enable Analytics', 'woo-offers' ); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_analytics" id="enable_analytics" 
                                       value="1" <?php checked( $settings['enable_analytics'] ?? true ); ?> />
                                <?php _e( 'Track offer performance and user interactions', 'woo-offers' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="default_expiration"><?php _e( 'Default Offer Expiration', 'woo-offers' ); ?></label>
                        </th>
                        <td>
                            <select name="default_expiration" id="default_expiration">
                                <option value="7" <?php selected( $settings['offer_expiration'] ?? '30', '7' ); ?>>
                                    <?php _e( '7 Days', 'woo-offers' ); ?>
                                </option>
                                <option value="14" <?php selected( $settings['offer_expiration'] ?? '30', '14' ); ?>>
                                    <?php _e( '14 Days', 'woo-offers' ); ?>
                                </option>
                                <option value="30" <?php selected( $settings['offer_expiration'] ?? '30', '30' ); ?>>
                                    <?php _e( '30 Days', 'woo-offers' ); ?>
                                </option>
                                <option value="60" <?php selected( $settings['offer_expiration'] ?? '30', '60' ); ?>>
                                    <?php _e( '60 Days', 'woo-offers' ); ?>
                                </option>
                                <option value="0" <?php selected( $settings['offer_expiration'] ?? '30', '0' ); ?>>
                                    <?php _e( 'Never Expire', 'woo-offers' ); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                </table>

                <div class="step-actions">
                    <a href="<?php echo admin_url( 'admin.php?page=woo-offers-setup&step=1' ); ?>" 
                       class="button button-secondary">
                        <?php _e( 'Previous', 'woo-offers' ); ?>
                    </a>
                    <button type="submit" class="button button-primary button-large">
                        <?php _e( 'Continue', 'woo-offers' ); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Render display options step
     */
    private function render_display_options_step() {
        $settings = get_option( 'woo_offers_settings', [] );
        ?>
        <div class="setup-step display-options-step">
            <h2><?php _e( 'Display Options', 'woo-offers' ); ?></h2>
            <p><?php _e( 'Customize how your offers appear to customers.', 'woo-offers' ); ?></p>

            <form method="post" class="setup-form" id="display-options-form">
                <?php wp_nonce_field( 'woo_offers_setup', 'setup_nonce' ); ?>
                <input type="hidden" name="step" value="3" />

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="primary_color"><?php _e( 'Primary Color', 'woo-offers' ); ?></label>
                        </th>
                        <td>
                            <input type="color" name="primary_color" id="primary_color" 
                                   value="<?php echo esc_attr( $settings['primary_color'] ?? '#e92d3b' ); ?>" />
                            <p class="description">
                                <?php _e( 'This color will be used for buttons and highlights in your offers.', 'woo-offers' ); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="default_position"><?php _e( 'Default Position', 'woo-offers' ); ?></label>
                        </th>
                        <td>
                            <select name="default_position" id="default_position">
                                <option value="before_add_to_cart" <?php selected( $settings['default_position'] ?? 'before_add_to_cart', 'before_add_to_cart' ); ?>>
                                    <?php _e( 'Before Add to Cart Button', 'woo-offers' ); ?>
                                </option>
                                <option value="after_add_to_cart" <?php selected( $settings['default_position'] ?? 'before_add_to_cart', 'after_add_to_cart' ); ?>>
                                    <?php _e( 'After Add to Cart Button', 'woo-offers' ); ?>
                                </option>
                                <option value="before_product_summary" <?php selected( $settings['default_position'] ?? 'before_add_to_cart', 'before_product_summary' ); ?>>
                                    <?php _e( 'Before Product Summary', 'woo-offers' ); ?>
                                </option>
                                <option value="after_product_summary" <?php selected( $settings['default_position'] ?? 'before_add_to_cart', 'after_product_summary' ); ?>>
                                    <?php _e( 'After Product Summary', 'woo-offers' ); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                </table>

                <div class="step-actions">
                    <a href="<?php echo admin_url( 'admin.php?page=woo-offers-setup&step=2' ); ?>" 
                       class="button button-secondary">
                        <?php _e( 'Previous', 'woo-offers' ); ?>
                    </a>
                    <button type="submit" class="button button-primary button-large">
                        <?php _e( 'Complete Setup', 'woo-offers' ); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Render complete step
     */
    private function render_complete_step() {
        // Mark setup as completed
        update_option( 'woo_offers_wizard_completed', true );
        ?>
        <div class="setup-step complete-step">
            <div class="success-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <h2><?php _e( 'Setup Complete!', 'woo-offers' ); ?></h2>
            <p><?php _e( 'Congratulations! Woo Offers has been successfully configured.', 'woo-offers' ); ?></p>

            <div class="next-steps">
                <h3><?php _e( 'What\'s Next?', 'woo-offers' ); ?></h3>
                <div class="steps-grid">
                    <div class="next-step">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <h4><?php _e( 'Create Your First Offer', 'woo-offers' ); ?></h4>
                        <p><?php _e( 'Start boosting your sales by creating your first upsell offer.', 'woo-offers' ); ?></p>
                        <a href="<?php echo admin_url( 'admin.php?page=woo-offers-create' ); ?>" 
                           class="button button-primary">
                            <?php _e( 'Create Offer', 'woo-offers' ); ?>
                        </a>
                    </div>
                    <div class="next-step">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <h4><?php _e( 'View Dashboard', 'woo-offers' ); ?></h4>
                        <p><?php _e( 'Monitor your offer performance and analytics.', 'woo-offers' ); ?></p>
                        <a href="<?php echo admin_url( 'admin.php?page=woo-offers' ); ?>" 
                           class="button button-secondary">
                            <?php _e( 'Go to Dashboard', 'woo-offers' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Process setup step via AJAX
     */
    public function process_setup_step() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['setup_nonce'] ?? '', 'woo_offers_setup' ) ) {
            wp_die( __( 'Security check failed', 'woo-offers' ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( __( 'You do not have permission to perform this action', 'woo-offers' ) );
        }

        $step = intval( $_POST['step'] ?? 0 );
        $settings = get_option( 'woo_offers_settings', [] );

        switch ( $step ) {
            case 2:
                // Save basic settings
                $settings['enable_analytics'] = isset( $_POST['enable_analytics'] );
                $settings['offer_expiration'] = sanitize_text_field( $_POST['default_expiration'] ?? '30' );
                break;

            case 3:
                // Save display options
                $settings['primary_color'] = sanitize_hex_color( $_POST['primary_color'] ?? '#e92d3b' );
                $settings['default_position'] = sanitize_text_field( $_POST['default_position'] ?? 'before_add_to_cart' );
                
                // Mark setup as completed
                update_option( 'woo_offers_wizard_completed', true );
                break;
        }

        // Save settings
        update_option( 'woo_offers_settings', $settings );

        // Redirect to next step
        $next_step = $step + 1;
        $redirect_url = admin_url( 'admin.php?page=woo-offers-setup&step=' . $next_step );
        
        wp_send_json_success( [
            'redirect' => $redirect_url,
            'message' => __( 'Settings saved successfully!', 'woo-offers' )
        ] );
    }
}
