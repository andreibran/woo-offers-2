---
description: 
globs: 
alwaysApply: false
---
# Woo Offers - Plugin Structure and Conventions

## **Plugin Overview**
- **Plugin Name:** Woo Offers - Advanced Upsell Plugin
- **Version:** 2.0.0
- **Type:** WooCommerce Plugin
- **Main File:** `woo-offers.php`
- **Text Domain:** `woo-offers`
- **PHP Namespace:** `WooOffers`

## **Directory Structure**

```
woo-offers-2/
├── src/                          # Core plugin classes (PSR-4)
│   ├── Admin/                    # Admin interface classes
│   ├── API/                      # REST API and AJAX handlers
│   ├── Core/                     # Core functionality
│   ├── Offers/                   # Discount engines and offer logic
│   │   └── Types/                # Specific discount engine types
│   └── Frontend/                 # Frontend functionality (to be created)
├── assets/                       # Static assets
│   ├── css/                      # Stylesheets
│   └── js/                       # JavaScript files
├── templates/                    # PHP template files
│   ├── admin/                    # Admin page templates
│   │   └── metaboxes/           # Metabox templates
│   └── offer-box.php            # Frontend offer display
├── languages/                    # Translation files
├── composer.json                 # Composer configuration
├── woo-offers.php               # Main plugin file
└── uninstall.php                # Cleanup on uninstall
```

## **Naming Conventions**

### **Files and Classes**
- **PHP Classes:** PascalCase (`Admin.php`, `DiscountEngine.php`)
- **Template Files:** kebab-case (`create-offer.php`, `edit-offer.php`)
- **CSS/JS Files:** kebab-case (`admin.css`, `frontend.js`)
- **Constants:** SCREAMING_SNAKE_CASE (`WOO_OFFERS_VERSION`)

### **PHP Coding Standards**
- **Namespace:** `WooOffers\SubNamespace`
- **Class Names:** PascalCase
- **Method Names:** snake_case (WordPress standard)
- **Variable Names:** snake_case
- **Hook Names:** `woo_offers_hook_name`

## **Key Plugin Constants**
```php
WOO_OFFERS_VERSION           // Plugin version
WOO_OFFERS_PLUGIN_FILE       // Main plugin file path
WOO_OFFERS_PLUGIN_DIR        // Plugin directory path
WOO_OFFERS_PLUGIN_URL        // Plugin URL
WOO_OFFERS_PLUGIN_BASENAME   // Plugin basename
WOO_OFFERS_MIN_PHP           // Minimum PHP version
WOO_OFFERS_MIN_WP            // Minimum WordPress version
WOO_OFFERS_MIN_WC            // Minimum WooCommerce version
```

## **Asset Management**
- **Admin CSS:** `assets/css/admin.css`
- **Frontend CSS:** `assets/css/frontend.css`
- **Admin JS:** `assets/js/admin.js`
- **Frontend JS:** `assets/js/frontend.js`
- **Localization:** Use `wooOffersAdmin` and `wooOffers` objects

## **Template System**
- **Admin Templates:** `templates/admin/`
- **Frontend Templates:** `templates/`
- **Template Loading:** Use `WOO_OFFERS_PLUGIN_PATH` constant
- **Template Wrapper:** `admin-wrapper.php` for admin pages

## **Database and Options**
- **Settings Option:** `woo_offers_settings`
- **Version Option:** `woo_offers_version`
- **Wizard Completion:** `woo_offers_wizard_completed`
- **Admin Notices:** `woo_offers_admin_notices` (transient)

## **Development Guidelines**

### **Creating New Classes**
1. Place in appropriate `src/` subdirectory
2. Use proper namespace (`WooOffers\SubNamespace`)
3. Follow WordPress coding standards
4. Include security check: `defined('ABSPATH') || exit;`
5. Add proper PHPDoc blocks

### **Adding Admin Pages**
1. Create template in `templates/admin/`
2. Add menu item in `Admin.php`
3. Enqueue specific assets if needed
4. Use admin wrapper template

### **Creating Discount Engines**
1. Extend `AbstractEngine` class
2. Implement `EngineInterface`
3. Place in `src/Offers/Types/`
4. Register in main plugin file

### **Asset Enqueueing**
- **Admin:** Only on plugin pages (`strpos($hook, 'woo-offers')`)
- **Frontend:** Only where needed (WooCommerce pages)
- **Dependencies:** Properly declare WordPress dependencies
- **Versioning:** Use `WOO_OFFERS_VERSION` for cache busting

## **Security Practices**
- **Nonce Verification:** All AJAX requests
- **Capability Checks:** `manage_woocommerce` for admin features
- **Data Sanitization:** All user inputs
- **Direct Access Prevention:** `defined('ABSPATH') || exit;`
- **SQL Injection Prevention:** Use WordPress database methods

## **WordPress Integration**
- **Action Hooks:** `woo_offers_*` prefix
- **Filter Hooks:** `woo_offers_*` prefix
- **Option Names:** `woo_offers_*` prefix
- **Transient Names:** `woo_offers_*` prefix
- **User Meta:** `woo_offers_*` prefix

## **File References**
- [Main Plugin File](mdc:woo-offers.php)
- [Admin Class](mdc:src/Admin/Admin.php)
- [Abstract Engine](mdc:src/Offers/AbstractEngine.php)
- [Composer Config](mdc:composer.json)
- [Admin CSS](mdc:assets/css/admin.css)
- [Admin JS](mdc:assets/js/admin.js)

