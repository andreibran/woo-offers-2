---
description: 
globs: 
alwaysApply: false
---
# WooCommerce Integration Guidelines

## **Plugin Dependencies**
- **Minimum WooCommerce:** 4.0
- **Tested up to:** 8.9
- **Required Check:** Verify WooCommerce is active before plugin initialization
- **Version Check:** Compare against `WC()->version`

## **WooCommerce Integration Patterns**

### **✅ DO: Proper WooCommerce Checks**
```php
// Check if WooCommerce is active
if ( ! class_exists( 'WooCommerce' ) ) {
    add_action( 'admin_notices', function() {
        $message = __( 'Woo Offers requires WooCommerce to be installed and activated.', 'woo-offers' );
        printf( '<div class="notice notice-error"><p>%s</p></div>', $message );
    });
    return;
}

// Check WooCommerce version
if ( version_compare( WC()->version, WOO_OFFERS_MIN_WC, '<' ) ) {
    // Show version error
}
```

### **✅ DO: Use WooCommerce Hooks and Filters**
```php
// Product page hooks
add_action( 'woocommerce_single_product_summary', [$this, 'display_offer'], 25 );
add_action( 'woocommerce_before_add_to_cart_form', [$this, 'display_offer'] );
add_action( 'woocommerce_after_add_to_cart_form', [$this, 'display_offer'] );

// Cart hooks
add_action( 'woocommerce_before_cart_table', [$this, 'display_cart_offer'] );
add_action( 'woocommerce_cart_calculate_fees', [$this, 'apply_cart_discount'] );

// Checkout hooks
add_action( 'woocommerce_review_order_before_payment', [$this, 'display_checkout_offer'] );
```

### **✅ DO: Proper Cart Integration**
```php
namespace WooOffers\Offers;

class CartIntegration {
    
    public static function init() {
        add_action( 'woocommerce_cart_calculate_fees', [__CLASS__, 'apply_offers'] );
        add_action( 'woocommerce_before_cart', [__CLASS__, 'display_cart_offers'] );
    }
    
    public static function apply_offers() {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }
        
        $cart = WC()->cart;
        if ( ! $cart || $cart->is_empty() ) {
            return;
        }
        
        // Apply offer logic
        $offers = self::get_applicable_offers();
        foreach ( $offers as $offer ) {
            self::apply_offer_to_cart( $offer, $cart );
        }
    }
}
```

### **✅ DO: Product Data Integration**
```php
// Get product safely
$product = wc_get_product( $product_id );
if ( ! $product || ! $product->exists() ) {
    return false;
}

// Check product type
if ( $product->is_type( 'variable' ) ) {
    // Handle variable product
} elseif ( $product->is_type( 'grouped' ) ) {
    // Handle grouped product
}

// Get product price
$price = $product->get_price();
$regular_price = $product->get_regular_price();
$sale_price = $product->get_sale_price();
```

## **Admin Integration**

### **✅ DO: Proper Capability Checks**
```php
// Use WooCommerce capabilities
if ( ! current_user_can( 'manage_woocommerce' ) ) {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
}

// Or for shop managers
if ( ! current_user_can( 'manage_product_terms' ) ) {
    return;
}
```

### **✅ DO: WooCommerce Admin Styling**
```php
public function enqueue_admin_scripts( $hook ) {
    // Only on WooCommerce pages and our pages
    if ( strpos( $hook, 'woocommerce' ) === false && strpos( $hook, 'woo-offers' ) === false ) {
        return;
    }
    
    // Enqueue WooCommerce admin styles
    wp_enqueue_style( 'woocommerce_admin_styles' );
    
    // Our custom styles
    wp_enqueue_style( 'woo-offers-admin', /* ... */ );
}
```

### **✅ DO: Product Search Integration**
```php
public function search_products_ajax() {
    check_ajax_referer( 'woo_offers_nonce', 'nonce' );
    
    $term = sanitize_text_field( $_POST['term'] ?? '' );
    $results = [];
    
    if ( $term ) {
        $products = wc_get_products([
            'status' => 'publish',
            'limit' => 20,
            's' => $term,
            'return' => 'ids'
        ]);
        
        foreach ( $products as $product_id ) {
            $product = wc_get_product( $product_id );
            if ( $product ) {
                $results[] = [
                    'id' => $product_id,
                    'text' => $product->get_formatted_name(),
                    'price' => $product->get_price_html()
                ];
            }
        }
    }
    
    wp_send_json_success( $results );
}
```

## **Frontend Integration**

### **✅ DO: Conditional Loading**
```php
private function should_load_frontend_assets() {
    return is_woocommerce() || 
           is_cart() || 
           is_checkout() || 
           is_account_page() ||
           is_shop() ||
           is_product_category() ||
           is_product_tag() ||
           is_product();
}
```

### **✅ DO: Template Override System**
```php
// Allow theme to override templates
$template_name = 'woo-offers/offer-box.php';
$template_path = 'woocommerce/';
$default_path = WOO_OFFERS_PLUGIN_PATH . 'templates/';

$template = wc_locate_template( $template_name, $template_path, $default_path );

if ( $template ) {
    include $template;
}
```

## **Database Integration**

### **✅ DO: Use WooCommerce Tables When Appropriate**
```php
global $wpdb;

// Use WooCommerce order item meta
$wpdb->insert(
    $wpdb->prefix . 'woocommerce_order_itemmeta',
    [
        'order_item_id' => $item_id,
        'meta_key' => '_woo_offers_discount',
        'meta_value' => $discount_amount
    ]
);

// Or use WooCommerce meta functions
wc_add_order_item_meta( $item_id, '_woo_offers_discount', $discount_amount );
```

### **✅ DO: Order Meta Integration**
```php
// Add offer data to order
add_action( 'woocommerce_checkout_create_order_line_item', function( $item, $cart_item_key, $values, $order ) {
    if ( isset( $values['woo_offers_data'] ) ) {
        $item->add_meta_data( '_woo_offers_data', $values['woo_offers_data'] );
    }
}, 10, 4 );

// Display in admin order
add_action( 'woocommerce_after_order_itemmeta', function( $item_id, $item, $product ) {
    $offer_data = wc_get_order_item_meta( $item_id, '_woo_offers_data' );
    if ( $offer_data ) {
        echo '<p><strong>' . __( 'Offer Applied:', 'woo-offers' ) . '</strong> ' . esc_html( $offer_data['title'] ) . '</p>';
    }
}, 10, 3 );
```

## **Security and Validation**

### **✅ DO: Validate WooCommerce Context**
```php
// Always check if in WooCommerce context
if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
    return;
}

// Check if cart is available
if ( ! WC()->cart || WC()->cart->is_empty() ) {
    return;
}

// Validate product exists
$product = wc_get_product( $product_id );
if ( ! $product || ! $product->exists() ) {
    return new WP_Error( 'invalid_product', __( 'Product not found', 'woo-offers' ) );
}
```

### **❌ DON'T: Direct Database Access**
```php
// Don't access WooCommerce tables directly without checks
$results = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE post_type = 'product'" );

// Use WooCommerce functions instead
$products = wc_get_products(['status' => 'publish']);
```

## **Testing Integration**

### **✅ DO: Test with WooCommerce States**
```php
// Test with different product types
// Test with different cart states (empty, multiple items)
// Test with different user states (logged in, guest)
// Test with different WooCommerce settings (tax inclusive/exclusive, etc.)
```

## **File References**
- [Cart Integration](mdc:src/Offers/CartIntegration.php)
- [Main Plugin File](mdc:woo-offers.php)
- [Admin Class](mdc:src/Admin/Admin.php)
- [Frontend Assets](mdc:src/Core/Assets.php)

