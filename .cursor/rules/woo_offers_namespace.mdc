---
description: 
globs: 
alwaysApply: false
---
# WooOffers PHP Namespace Structure

## **Base Namespace**
- **Root:** `WooOffers`
- **PSR-4 Mapping:** `"WooOffers\\": "src/"`
- **Autoloader:** Composer PSR-4

## **Namespace Structure**

### **Core Namespaces**
```php
WooOffers\Admin          // Admin interface classes
WooOffers\API            // REST API and AJAX handlers  
WooOffers\Core           // Core plugin functionality
WooOffers\Frontend       // Frontend functionality
WooOffers\Offers         // Discount engines and offer logic
WooOffers\Offers\Types   // Specific discount engine implementations
```

## **Class Naming Conventions**

### **✅ DO: Proper Namespace Usage**
```php
<?php
namespace WooOffers\Admin;

defined('ABSPATH') || exit;

class Dashboard {
    // Class implementation
}
```

### **✅ DO: Proper Imports**
```php
<?php
namespace WooOffers\Offers\Types;

use WooOffers\Offers\AbstractEngine;
use WooOffers\Offers\EngineInterface;

class PercentageDiscountEngine extends AbstractEngine implements EngineInterface {
    // Implementation
}
```

### **❌ DON'T: Global Namespace**
```php
<?php
// Don't put classes in global namespace
class WooOffersAdmin {
    // Wrong approach
}
```

## **Interface and Abstract Class Standards**

### **Interfaces**
- **Location:** Same namespace as implementing classes
- **Naming:** End with `Interface` (e.g., `EngineInterface`)
- **File Naming:** Match class name (`EngineInterface.php`)

### **Abstract Classes**
- **Location:** Parent namespace of extending classes
- **Naming:** Start with `Abstract` (e.g., `AbstractEngine`)
- **File Naming:** Match class name (`AbstractEngine.php`)

## **Autoloading Requirements**

### **File Structure Must Match Namespace**
```
src/
├── Admin/
│   ├── Admin.php              // WooOffers\Admin\Admin
│   ├── Dashboard.php          // WooOffers\Admin\Dashboard
│   └── Settings.php           // WooOffers\Admin\Settings
├── Offers/
│   ├── AbstractEngine.php     // WooOffers\Offers\AbstractEngine
│   ├── EngineInterface.php    // WooOffers\Offers\EngineInterface
│   └── Types/
│       ├── BogoEngine.php     // WooOffers\Offers\Types\BogoEngine
│       └── BundleEngine.php   // WooOffers\Offers\Types\BundleEngine
```

## **Security Headers**
Every namespaced class file must include:
```php
<?php
namespace WooOffers\Appropriate\Namespace;

defined('ABSPATH') || exit;
```

## **Documentation Standards**
```php
/**
 * Class description
 * 
 * @package WooOffers
 * @subpackage Admin
 * @since 2.0.0
 */
class ExampleClass {
    // Implementation
}
```

## **Cross-Namespace References**
When referencing classes from other namespaces:

### **✅ DO: Use Full Qualified Names or Imports**
```php
<?php
namespace WooOffers\Admin;

use WooOffers\Core\Assets;
use WooOffers\Offers\DiscountEngine;

class Admin {
    public function init() {
        new Assets();
        new DiscountEngine();
    }
}
```

### **❌ DON'T: Assume Auto-Resolution**
```php
<?php
namespace WooOffers\Admin;

class Admin {
    public function init() {
        // Wrong - will look for WooOffers\Admin\Assets
        new Assets();
    }
}
```

## **Integration with WordPress**
- **Hook Names:** Use plugin prefix `woo_offers_` 
- **Global Access:** Use global functions when needed
- **WordPress Classes:** Don't namespace WordPress core classes

```php
<?php
namespace WooOffers\Admin;

class Admin {
    public function __construct() {
        // WordPress hooks - no namespace needed
        add_action('admin_menu', [$this, 'add_menu']);
        
        // Plugin-specific hook
        do_action('woo_offers_admin_init', $this);
    }
}
```

## **File References**
- [Composer Autoload Config](mdc:composer.json)
- [Abstract Engine](mdc:src/Offers/AbstractEngine.php)
- [Engine Interface](mdc:src/Offers/EngineInterface.php)
- [Admin Class](mdc:src/Admin/Admin.php)

