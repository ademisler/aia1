# AI Inventory Agent - Developer Guide

## 📋 Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Getting Started](#getting-started)
3. [Module Development](#module-development)
4. [API Integration](#api-integration)
5. [Database Schema](#database-schema)
6. [Hooks & Filters](#hooks--filters)
7. [JavaScript Components](#javascript-components)
8. [Testing](#testing)
9. [Contributing](#contributing)

## Architecture Overview

### Plugin Structure

```
ai-inventory-agent/
├── includes/
│   ├── Core/           # Core functionality
│   ├── Modules/        # Feature modules
│   ├── API/            # External API integrations
│   ├── Admin/          # Admin interface
│   └── Utils/          # Utility classes
├── assets/
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   └── icons/         # SVG icons
├── templates/         # PHP templates
├── languages/         # Translation files
└── docs/             # Documentation
```

### Core Classes

#### Plugin Class
The main plugin class that initializes all components:

```php
namespace AIA\Core;

class Plugin {
    private static $instance = null;
    private $modules = [];
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function init() {
        $this->load_dependencies();
        $this->init_modules();
        $this->setup_hooks();
    }
}
```

#### ModuleManager Class
Manages all plugin modules with dependency resolution:

```php
$module_manager = new ModuleManager($plugin);
$module_manager->register('inventory_analysis', InventoryAnalysis::class);
$module_manager->init_modules();
```

## Getting Started

### Requirements

- WordPress 5.0+
- PHP 7.4+
- WooCommerce 5.0+
- MySQL 5.7+ or MariaDB 10.2+

### Installation for Development

1. Clone the repository:
```bash
git clone https://github.com/yourusername/ai-inventory-agent.git
cd ai-inventory-agent
```

2. Install dependencies:
```bash
composer install --dev
npm install
```

3. Build assets:
```bash
npm run build
```

4. Set up local environment:
```bash
wp plugin activate ai-inventory-agent
```

### Development Workflow

1. Create a new branch for your feature:
```bash
git checkout -b feature/your-feature-name
```

2. Make your changes following our coding standards

3. Run tests:
```bash
composer test
npm test
```

4. Submit a pull request

## Module Development

### Creating a New Module

1. Create a new class in `includes/Modules/`:

```php
namespace AIA\Modules;

use AIA\Core\Module;

class YourModule extends Module {
    
    public function init() {
        // Initialize your module
        add_action('init', [$this, 'register_hooks']);
    }
    
    public function get_dependencies() {
        return ['inventory_analysis']; // Optional dependencies
    }
    
    public function register_hooks() {
        // Register your hooks
    }
}
```

2. Register the module in `Plugin::init_modules()`:

```php
$this->module_manager->register('your_module', YourModule::class);
```

### Module Interface

All modules must implement the `ModuleInterface`:

```php
interface ModuleInterface {
    public function init();
    public function get_id();
    public function get_dependencies();
    public function is_active();
}
```

## API Integration

### Adding a New AI Provider

1. Create a provider class implementing `AIProviderInterface`:

```php
namespace AIA\API;

class YourProvider implements AIProviderInterface {
    
    public function generate_response($conversation, $options = []) {
        // Implement API call
    }
    
    public function validate_api_key($api_key) {
        // Validate the API key
    }
    
    public function get_models() {
        // Return available models
    }
}
```

2. Register the provider:

```php
add_filter('aia_ai_providers', function($providers) {
    $providers['your_provider'] = YourProvider::class;
    return $providers;
});
```

### Making API Requests

Use the built-in API client:

```php
$ai_chat = $plugin->get_module('ai_chat');
$response = $ai_chat->process_message('Your message here');
```

## Database Schema

### Custom Tables

The plugin creates the following custom tables:

#### inventory_logs
```sql
CREATE TABLE {prefix}_aia_inventory_logs (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    product_id bigint(20) unsigned NOT NULL,
    action varchar(50) NOT NULL,
    old_stock int(11),
    new_stock int(11),
    change_reason text,
    user_id bigint(20) unsigned,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY product_id (product_id),
    KEY action (action),
    KEY created_at (created_at)
);
```

#### ai_conversations
```sql
CREATE TABLE {prefix}_aia_ai_conversations (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    session_id varchar(64) NOT NULL,
    role varchar(20) NOT NULL,
    message longtext NOT NULL,
    metadata longtext,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY session_id (session_id),
    KEY created_at (created_at)
);
```

### Database Operations

Use the Database class for all operations:

```php
$database = $plugin->get_database();

// Log inventory change
$database->log_inventory_change(
    $product_id,
    'manual_update',
    $old_stock,
    $new_stock,
    'Manual stock adjustment'
);

// Get logs
$logs = $database->get_inventory_logs($product_id, 30);
```

## Hooks & Filters

### Action Hooks

#### aia_before_analysis
Fired before inventory analysis runs:
```php
do_action('aia_before_analysis', $products);
```

#### aia_after_analysis
Fired after inventory analysis completes:
```php
do_action('aia_after_analysis', $results);
```

#### aia_stock_alert
Fired when a stock alert is triggered:
```php
do_action('aia_stock_alert', $product_id, $alert_type, $current_stock);
```

### Filter Hooks

#### aia_analysis_data
Filter analysis results:
```php
$data = apply_filters('aia_analysis_data', $data, $context);
```

#### aia_ai_system_prompt
Customize AI system prompt:
```php
$prompt = apply_filters('aia_ai_system_prompt', $default_prompt, $context);
```

#### aia_notification_recipients
Filter notification recipients:
```php
$recipients = apply_filters('aia_notification_recipients', $emails, $alert_type);
```

## JavaScript Components

### UI Component System

Initialize components:
```javascript
// Toast notifications
AIA.UI.Toast.show('Message', 'success', 3000);

// Modal dialogs
const modal = AIA.UI.Modal.create({
    title: 'Modal Title',
    content: 'Modal content',
    size: 'medium'
});
modal.show();

// Progress indicators
AIA.UI.Progress.create('#progress-container', {
    value: 75,
    max: 100,
    variant: 'primary'
});
```

### Chart Components

Create charts using the Chart.js integration:

```javascript
// Line chart
AIA.Charts.createLineChart('canvas-id', {
    labels: ['Jan', 'Feb', 'Mar'],
    datasets: [{
        label: 'Sales',
        data: [100, 150, 200]
    }]
});

// Progress ring
AIA.Charts.createProgressRing('#container', 85, {
    label: 'Complete',
    color: '#00b862'
});
```

### AJAX Handlers

Making AJAX requests:

```javascript
jQuery.ajax({
    url: aia_admin.ajax_url,
    type: 'POST',
    data: {
        action: 'aia_your_action',
        nonce: aia_admin.nonce,
        data: yourData
    },
    success: function(response) {
        if (response.success) {
            // Handle success
        }
    }
});
```

## Testing

### PHP Unit Tests

Run PHP tests:
```bash
composer test
```

Write tests in `tests/` directory:

```php
class TestInventoryAnalysis extends WP_UnitTestCase {
    
    public function test_stock_calculation() {
        $module = new InventoryAnalysis();
        $result = $module->calculate_total_stock_value();
        
        $this->assertIsNumeric($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }
}
```

### JavaScript Tests

Run JavaScript tests:
```bash
npm test
```

### Integration Tests

Test with WooCommerce:

```php
public function test_woocommerce_integration() {
    // Create test product
    $product = WC_Helper_Product::create_simple_product();
    
    // Test inventory tracking
    $inventory = new InventoryAnalysis();
    $stock_value = $inventory->get_product_stock_value($product->get_id());
    
    $this->assertEquals(
        $product->get_price() * $product->get_stock_quantity(),
        $stock_value
    );
}
```

## Contributing

### Coding Standards

Follow WordPress coding standards:

```bash
# PHP
composer run phpcs

# JavaScript
npm run lint
```

### Pull Request Process

1. Fork the repository
2. Create your feature branch
3. Commit your changes with descriptive messages
4. Push to your fork
5. Submit a pull request

### Code Review Checklist

- [ ] Code follows WordPress coding standards
- [ ] All tests pass
- [ ] New features have tests
- [ ] Documentation is updated
- [ ] No security vulnerabilities
- [ ] Performance impact considered
- [ ] Backward compatibility maintained

## Debugging

### Enable Debug Mode

Add to `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('AIA_DEBUG', true);
```

### Debug Functions

```php
// Log debug information
aia_debug_log('Your debug message', $data);

// Get debug information
$debug_info = $plugin->get_debug_info();
```

### Common Issues

#### API Connection Failed
- Check API key validity
- Verify SSL certificates
- Check firewall settings

#### Missing Tables
- Run activation hook: `$plugin->activate()`
- Check database permissions

#### JavaScript Errors
- Clear browser cache
- Check for conflicts with other plugins
- Verify script dependencies

## Performance Optimization

### Caching

Use built-in caching:

```php
// Set cache
wp_cache_set('aia_data_key', $data, 'aia_cache', 3600);

// Get cache
$data = wp_cache_get('aia_data_key', 'aia_cache');
```

### Database Queries

Optimize queries:

```php
// Use prepare for security
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$table} WHERE product_id = %d LIMIT %d",
    $product_id,
    100
));

// Add indexes for performance
$database->add_index('inventory_logs', 'product_date', ['product_id', 'created_at']);
```

### Batch Processing

Process large datasets in batches:

```php
$batch_processor = new BatchProcessor();
$batch_processor->process($items, 100, function($batch) {
    // Process batch
});
```

## Security

### Data Validation

Always validate input:

```php
$product_id = absint($_POST['product_id']);
$message = sanitize_textarea_field($_POST['message']);
$email = sanitize_email($_POST['email']);
```

### Capability Checks

Check user permissions:

```php
if (!current_user_can('manage_woocommerce')) {
    wp_die(__('Insufficient permissions'));
}
```

### Nonce Verification

Verify nonces for all forms:

```php
check_ajax_referer('aia_ajax_nonce', 'nonce');
```

## Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WooCommerce Developer Docs](https://woocommerce.github.io/code-reference/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Plugin Support Forum](https://wordpress.org/support/plugin/ai-inventory-agent/)