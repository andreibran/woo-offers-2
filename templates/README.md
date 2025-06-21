# Woo Offers v3.0 - Template Directory Structure

This document outlines the WordPress-compliant template directory structure for Woo Offers v3.0, designed for maintainability, reusability, and extensibility.

## 📂 Directory Structure

```
/templates/
├── admin/                     # Admin-specific templates
│   ├── metaboxes/            # Metabox templates for edit forms
│   ├── analytics.php         # Analytics dashboard
│   ├── offers.php           # Campaign management page
│   ├── offers-modern.php    # Modern campaign list view
│   ├── settings.php         # Plugin settings page
│   ├── edit-offer.php       # Campaign editor
│   ├── create-offer.php     # Campaign creation
│   ├── getting-started.php  # Onboarding guide
│   ├── help.php             # Help documentation
│   ├── dashboard.php        # Main dashboard
│   ├── admin-wrapper.php    # Admin page wrapper
│   ├── ab-tests.php         # A/B testing interface
│   └── import-export.php    # Data import/export
├── campaigns/                # Campaign-specific templates
│   ├── campaign-builder.php # Visual campaign builder
│   └── campaign-wizard.php  # Campaign creation wizard
├── frontend/                 # Frontend display templates
│   ├── offer-types/         # Offer type-specific templates
│   │   ├── bogo.php         # Buy-one-get-one offers
│   │   ├── bundle.php       # Bundle offers
│   │   ├── fixed.php        # Fixed discount offers
│   │   ├── free_shipping.php # Free shipping offers
│   │   ├── percentage.php   # Percentage discount offers
│   │   └── quantity.php     # Quantity-based offers
│   └── offer-default.php    # Default offer display template
├── partials/                 # Reusable template components
│   ├── admin-header.php     # Reusable admin page header
│   ├── metric-card.php      # Metric display card component
│   ├── empty-state.php      # Empty state component
│   └── offer-box.php        # Offer display box
└── pages/                    # Full page templates
    └── full-page-template.php # Complete page layout structure
```

## 🧩 Reusable Partials

### admin-header.php
Displays a consistent admin page header with breadcrumbs, title, description, and action buttons.

**Usage:**
```php
// Set variables before including
$header_title = 'Analytics Dashboard';
$header_description = 'Comprehensive insights and performance analytics';
$breadcrumbs = [
    ['label' => 'Woo Offers', 'url' => admin_url('admin.php?page=woo-offers')],
    ['label' => 'Analytics', 'url' => '']
];
$header_actions = [
    [
        'label' => 'Export Data',
        'url' => '#',
        'class' => 'wo-btn-outline',
        'icon' => 'dashicons-download'
    ]
];

include WOO_OFFERS_PLUGIN_PATH . 'templates/partials/admin-header.php';
```

### metric-card.php
Displays a metric card with icon, value, label, and optional change indicator.

**Usage:**
```php
// Set variables before including
$metric_value = '1,234';
$metric_label = 'Total Views';
$metric_icon = 'dashicons-visibility';
$metric_icon_color = 'var(--wo-primary-500)';
$metric_change = '+12.5';
$metric_data_attr = 'views';

include WOO_OFFERS_PLUGIN_PATH . 'templates/partials/metric-card.php';
```

### empty-state.php
Displays an empty state with icon, title, description, and optional action button.

**Usage:**
```php
// Set variables before including
$empty_icon = 'dashicons-megaphone';
$empty_title = 'No campaigns yet';
$empty_description = 'Start boosting your sales by creating your first marketing campaign.';
$empty_action_label = 'Create Campaign';
$empty_action_url = admin_url('admin.php?page=woo-offers-create');
$empty_size = 'large';

include WOO_OFFERS_PLUGIN_PATH . 'templates/partials/empty-state.php';
```

## 📄 Page Templates

### full-page-template.php
Provides a complete page layout structure with header, main content, and footer areas.

**Usage:**
```php
// Set variables before including
$page_title = 'Campaign Management';
$page_description = 'Manage and monitor all your marketing campaigns';
$content_template = WOO_OFFERS_PLUGIN_PATH . 'templates/admin/offers-content.php';
$page_class = 'campaigns-page';

include WOO_OFFERS_PLUGIN_PATH . 'templates/pages/full-page-template.php';
```

## 🎨 Assets Organization

```
/assets/
├── css/                      # Stylesheets
│   ├── admin.css            # Admin interface styles
│   └── frontend.css         # Frontend offer display styles
├── js/                       # JavaScript files
│   ├── admin.js             # Admin functionality
│   ├── frontend.js          # Frontend offer interactions
│   ├── analytics-tracker.js # Analytics tracking
│   └── admin-settings.js    # Settings page functionality
└── img/                      # Image assets (created, ready for use)
    └── (placeholder for future image assets)
```

## 📋 Template Loading Patterns

### Current Loading Methods
```php
// Admin templates
$template_file = WOO_OFFERS_PLUGIN_PATH . 'templates/admin/' . $page . '.php';

// Metaboxes
include WOO_OFFERS_PLUGIN_PATH . 'templates/admin/metaboxes/general.php';

// Frontend templates
return WOO_OFFERS_PLUGIN_PATH . 'templates/frontend/offer-default.php';

// Campaign templates
include WOO_OFFERS_PLUGIN_PATH . 'templates/campaigns/campaign-builder.php';

// Partials
include WOO_OFFERS_PLUGIN_PATH . 'templates/partials/admin-header.php';
```

## 🔄 Migration Notes

### Moved Files
- `templates/offer-box.php` → `templates/partials/offer-box.php`
- `templates/admin/campaign-builder.php` → `templates/campaigns/campaign-builder.php`
- `templates/admin/campaign-wizard.php` → `templates/campaigns/campaign-wizard.php`

### New Directories Created
- `templates/partials/` - Reusable template components
- `templates/pages/` - Full page templates
- `templates/campaigns/` - Campaign-specific templates
- `assets/img/` - Image assets directory

## 🚀 Benefits

1. **WordPress Compliance** - Follows WordPress template hierarchy and naming conventions
2. **Maintainability** - Clear separation of concerns and organized file structure
3. **Reusability** - Partials can be reused across multiple templates
4. **Extensibility** - Easy to add new templates and components
5. **Performance** - Optimized loading patterns and asset organization
6. **Accessibility** - Built-in accessibility features in all partials
7. **Consistency** - Standardized header, empty states, and metric displays

## 🔧 Next Steps

1. Update PHP classes to use new template paths
2. Create additional partials as needed
3. Implement template rendering functions
4. Update autoloader for new structure
5. Add more page templates for different admin pages

---

*This structure supports the campaign system architecture and security improvements implemented in Woo Offers v3.0.* 