<?php
/**
 * Plugin Name:       Woo Offers - Advanced Upsell Plugin
 * Plugin URI:        https://wooofers.com/
 * Description:       Advanced upsell and cross-sell plugin for WooCommerce with modern UI, multiple offer types, and comprehensive analytics.
 * Version:           2.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            Woo Offers Team
 * Author URI:        https://wooofers.com/
 * License:           GPLv3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       woo-offers
 * Domain Path:       /languages
 * WC requires at least: 4.0
 * WC tested up to:   8.9
 * Network:           false
 *
 * @package WooOffers
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'WOO_OFFERS_VERSION', '2.0.0' );
define( 'WOO_OFFERS_PLUGIN_FILE', __FILE__ );
define( 'WOO_OFFERS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOO_OFFERS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); // Alias for template compatibility
define( 'WOO_OFFERS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WOO_OFFERS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Minimum requirements
define( 'WOO_OFFERS_MIN_PHP', '7.4' );
define( 'WOO_OFFERS_MIN_WP', '5.6' );
define( 'WOO_OFFERS_MIN_WC', '4.0' );

/**
 * Main plugin class
 */
final class WooOffers {

    /**
     * Plugin instance
     * * @var WooOffers
     */
    private static $instance = null;

    /**
     * Get plugin instance
     * * @return WooOffers
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
        register_activation_hook( WOO_OFFERS_PLUGIN_FILE, [ $this, 'activate' ] );
        register_deactivation_hook( WOO_OFFERS_PLUGIN_FILE, [ $this, 'deactivate' ] );
    }

    /**
     * Initialize the plugin
     */
    public function init_plugin() {
        // Check system requirements
        if ( ! $this->check_requirements() ) {
            return;
        }

        // Load plugin
        $this->load_textdomain();
        $this->includes();
        $this->init_hooks();
        
        // Initialize plugin
        do_action( 'woo_offers_loaded' );
    }

    /**
     * Check system requirements
     * * @return bool
     */
    private function check_requirements() {
        $requirements_met = true;

        // Check PHP version
        if ( version_compare( PHP_VERSION, WOO_OFFERS_MIN_PHP, '<' ) ) {
            add_action( 'admin_notices', function() {
                $message = sprintf(
                    __( 'Woo Offers requires PHP version %s or higher. You are running version %s.', 'woo-offers' ),
                    WOO_OFFERS_MIN_PHP,
                    PHP_VERSION
                );
                printf( '<div class="notice notice-error"><p>%s</p></div>', $message );
            });
            $requirements_met = false;
        }

        // Check WordPress version
        if ( version_compare( get_bloginfo( 'version' ), WOO_OFFERS_MIN_WP, '<' ) ) {
            add_action( 'admin_notices', function() {
                $message = sprintf(
                    __( 'Woo Offers requires WordPress version %s or higher. You are running version %s.', 'woo-offers' ),
                    WOO_OFFERS_MIN_WP,
                    get_bloginfo( 'version' )
                );
                printf( '<div class="notice notice-error"><p>%s</p></div>', $message );
            });
            $requirements_met = false;
        }

        // Check WooCommerce
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', function() {
                $message = __( 'Woo Offers requires WooCommerce to be installed and activated.', 'woo-offers' );
                printf( '<div class="notice notice-error"><p>%s</p></div>', $message );
            });
            $requirements_met = false;
        } elseif ( version_compare( WC()->version, WOO_OFFERS_MIN_WC, '<' ) ) {
            add_action( 'admin_notices', function() {
                $message = sprintf(
                    __( 'Woo Offers requires WooCommerce version %s or higher. You are running version %s.', 'woo-offers' ),
                    WOO_OFFERS_MIN_WC,
                    WC()->version
                );
                printf( '<div class="notice notice-error"><p>%s</p></div>', $message );
            });
            $requirements_met = false;
        }

        return $requirements_met;
    }

    /**
     * Load plugin textdomain
     */
    private function load_textdomain() {
        load_plugin_textdomain( 'woo-offers', false, dirname( WOO_OFFERS_PLUGIN_BASENAME ) . '/languages' );
    }

    /**
     * Include required files
     */
    private function includes() {
        // Core classes
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Core/Installer.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Core/Assets.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Core/Permissions.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Core/SecurityManager.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Core/DatabaseSchema.php';
        
        // Admin classes
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Admin/Admin.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Admin/Dashboard.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Admin/Settings.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Admin/Analytics.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Admin/SetupWizard.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Admin/class-offers-list-table.php';
        
        // Frontend classes
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Frontend/Display.php';
        
        // API classes
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/API/RestAPI.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/API/AjaxHandlers.php';
        
        // Discount Engine classes
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Offers/EngineInterface.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Offers/AbstractEngine.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Offers/DiscountEngine.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Offers/CartIntegration.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Offers/OfferScheduler.php';
        
        // Specific engine types
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Offers/Types/QuantityDiscountEngine.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Offers/Types/BogoEngine.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Offers/Types/BundleEngine.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Offers/Types/PercentageDiscountEngine.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Offers/Types/FixedDiscountEngine.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Offers/Types/FreeShippingEngine.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'init', [ $this, 'init' ] );
    }

    /**
     * Initialize plugin components
     */
    public function init() {
        // Initialize core components
        new WooOffers\Core\Assets();
        new WooOffers\Core\Permissions();
        
        // Initialize security and database
        WooOffers\Core\DatabaseSchema::init();
        
        // Initialize admin components
        new WooOffers\Admin\Admin();
        new WooOffers\Admin\Dashboard();
        new WooOffers\Admin\Settings();
        new WooOffers\Admin\Analytics();
        new WooOffers\Admin\SetupWizard();
        
        // Initialize frontend components
        WooOffers\Frontend\Display::init();
        
        // Initialize API components
        new WooOffers\API\RestAPI();
        new WooOffers\API\AjaxHandlers();
        
        // Initialize discount engine cart integration
        WooOffers\Offers\CartIntegration::init();
        
        // Initialize offer scheduler
        WooOffers\Offers\OfferScheduler::init();
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Check requirements during activation
        if ( ! $this->check_requirements() ) {
            deactivate_plugins( WOO_OFFERS_PLUGIN_BASENAME );
            wp_die( __( 'Woo Offers could not be activated due to system requirements not being met.', 'woo-offers' ) );
        }

        // Run installer
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Core/Installer.php';
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Core/Permissions.php';
        WooOffers\Core\Installer::activate();
        
        // Set plugin version
        update_option( 'woo_offers_version', WOO_OFFERS_VERSION );
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Run cleanup
        require_once WOO_OFFERS_PLUGIN_DIR . 'src/Core/Installer.php';
        WooOffers\Core\Installer::deactivate();
    }
}

/**
 * Initialize plugin
 */
function woo_offers() {
    return WooOffers::instance();
}

// Start the plugin
woo_offers();