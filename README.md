# AI Inventory Agent (AIA) - WordPress WooCommerce Plugin

An AI-powered inventory management plugin for WooCommerce stores that provides intelligent stock analysis, demand forecasting, and automated recommendations using advanced AI technologies.

## 🧠 Key Features

### AI-Powered Chat Interface
- Natural language inventory management
- Support for OpenAI (GPT) and Google Gemini
- Context-aware responses based on store data
- Customizable system prompts for different business needs

### Intelligent Inventory Analysis
- Real-time stock level monitoring
- Low stock and out-of-stock alerts
- Overstock identification
- Stock turnover analysis
- Top and slow-moving product identification

### Demand Forecasting
- AI-driven demand predictions
- Seasonal analysis and trends
- Historical sales data integration
- Configurable forecasting periods

### Supplier Analysis & Risk Management
- Supplier performance scoring
- Delivery time tracking
- Quality and reliability metrics
- Risk level assessment

### Automated Notifications
- Email alerts for critical stock levels
- Dashboard notifications
- Customizable alert thresholds
- Multi-channel notification support

### Advanced Reporting
- Automated report generation (PDF/HTML)
- Weekly and monthly reports
- Performance metrics and insights
- Customizable report templates

## 🚀 Installation

1. **Download** the plugin files
2. **Upload** to your WordPress site's `/wp-content/plugins/` directory
3. **Activate** the plugin through the WordPress admin panel
4. **Configure** your AI provider API key in the settings
5. **Set up** inventory thresholds and notification preferences

## ⚙️ Configuration

### AI Provider Setup

1. Navigate to **AI Inventory → Settings**
2. Choose your AI provider:
   - **OpenAI**: Requires OpenAI API key
   - **Google Gemini**: Requires Google AI API key
3. Enter your API key and test the connection
4. Customize the system prompt for your business needs

### Inventory Thresholds

- **Low Stock Threshold**: Default 5 units
- **Critical Stock Threshold**: Default 1 unit
- Configure based on your business requirements

### Notification Settings

- Set notification email addresses
- Enable/disable email and dashboard notifications
- Configure alert frequencies

## 🏗️ Plugin Architecture

### Modular Structure

The plugin is built with a modular, extensible architecture:

```
ai-inventory-agent/
├── ai-inventory-agent.php          # Main plugin file
├── includes/
│   ├── Core/                       # Core functionality
│   │   ├── Plugin.php             # Main plugin class
│   │   ├── ModuleManager.php      # Module management
│   │   ├── Database.php           # Database operations
│   │   ├── Activator.php          # Plugin activation
│   │   ├── Deactivator.php        # Plugin deactivation
│   │   └── Uninstaller.php        # Plugin uninstallation
│   ├── Modules/                    # Feature modules
│   │   ├── AIChat.php             # AI chat functionality
│   │   ├── InventoryAnalysis.php  # Inventory analysis
│   │   ├── DemandForecasting.php  # Demand forecasting
│   │   ├── SupplierAnalysis.php   # Supplier analysis
│   │   ├── Notifications.php      # Notification system
│   │   └── Reporting.php          # Report generation
│   ├── API/                        # AI provider integrations
│   │   ├── OpenAIProvider.php     # OpenAI integration
│   │   └── GeminiProvider.php     # Google Gemini integration
│   ├── Admin/                      # WordPress admin interface
│   │   └── AdminInterface.php     # Admin pages and menus
│   └── Utils/                      # Utility classes
│       └── InventoryContext.php   # Context analysis
├── assets/                         # Static assets
│   ├── css/
│   │   └── admin.css              # Admin styles
│   ├── js/
│   │   └── admin.js               # Admin JavaScript
│   └── images/                    # Plugin images
├── templates/                      # Template files
│   └── admin/                     # Admin page templates
│       ├── dashboard.php
│       ├── chat.php
│       ├── settings.php
│       └── ...
└── languages/                     # Translation files
```

### Core Classes

#### Plugin.php
Main plugin coordinator that:
- Initializes all modules
- Manages plugin settings
- Handles WordPress hooks
- Coordinates module communication

#### ModuleManager.php
Manages plugin modules with:
- Dynamic module loading
- Dependency resolution
- Module lifecycle management
- Extensible architecture for custom modules

#### Database.php
Handles all database operations:
- Custom table creation and management
- Inventory logging
- Demand forecast storage
- Supplier performance tracking
- AI conversation history

### Module System

Each module is self-contained and follows a standard interface:

```php
class ModuleExample {
    public function init() { /* Module initialization */ }
    public function get_info() { /* Module information */ }
    public function deactivate() { /* Cleanup on deactivation */ }
}
```

## 🔌 API Integration

### OpenAI Integration
- Supports GPT-3.5 Turbo, GPT-4, and newer models
- Configurable parameters (temperature, max tokens)
- Error handling and retry logic
- Token usage tracking

### Google Gemini Integration
- Supports Gemini Pro and Gemini Pro Vision
- Automatic format conversion from OpenAI format
- System instruction support
- Usage analytics

## 📊 Database Schema

The plugin creates several custom tables:

### aia_inventory_logs
Tracks all inventory changes:
- Product ID and action type
- Old and new stock levels
- Change reason and user
- Timestamp

### aia_demand_forecasts
Stores AI-generated forecasts:
- Product ID and forecast date
- Predicted demand and confidence score
- Seasonal and trend factors
- Model version

### aia_supplier_performance
Supplier metrics and scoring:
- Supplier identification and contact info
- Performance metrics (delivery time, quality, etc.)
- Risk assessment levels
- Historical data

### aia_ai_conversations
Chat history and context:
- Session management
- Message storage (user/assistant)
- Context data and metadata
- Performance metrics

### aia_stock_alerts
Alert management:
- Product and alert type
- Current stock and thresholds
- Severity levels and status
- Notification tracking

### aia_reports_cache
Report caching system:
- Report type and identification
- Cached data and parameters
- Expiration management

## 🎯 Usage Examples

### AI Chat Queries

```
"What products are running low on stock?"
"Show me sales trends for the last month"
"Which suppliers have the best performance?"
"Forecast demand for winter jackets"
"Generate a reorder report for electronics"
```

### Programmatic Usage

```php
// Get plugin instance
$aia = AIA\Core\Plugin::get_instance();

// Get inventory analysis
$analysis = $aia->get_module_manager()->get_module('inventory_analysis');
$summary = $analysis->get_inventory_summary();

// Send AI chat message
$ai_chat = $aia->get_module_manager()->get_module('ai_chat');
$response = $ai_chat->process_message("What's my current stock status?");
```

## 🔧 Customization

### Custom System Prompts

Customize the AI behavior by modifying the system prompt:

```
You are a specialized inventory assistant for [Your Store Name]. 
Focus on [specific industry] products and prioritize [business priorities].
Always consider [specific constraints or requirements].
```

### Adding Custom Modules

Create custom modules by extending the base structure:

```php
namespace AIA\Modules;

class CustomModule {
    public function init() {
        // Module initialization
        add_action('custom_hook', [$this, 'custom_method']);
    }
    
    public function get_info() {
        return [
            'name' => 'Custom Module',
            'description' => 'Custom functionality',
            'version' => '1.0.0'
        ];
    }
}
```

Register the module:

```php
$module_manager->register_module('custom_module', 'AIA\\Modules\\CustomModule');
```

## 🔒 Security Features

- **API Key Encryption**: Secure storage of AI provider keys
- **Permission Checks**: WordPress capability-based access control
- **Input Sanitization**: All user inputs are properly sanitized
- **Nonce Verification**: CSRF protection for all AJAX requests
- **SQL Injection Prevention**: Prepared statements for all database queries

## 🌐 Internationalization

The plugin is fully translatable:
- Text domain: `ai-inventory-agent`
- Translation files in `/languages/` directory
- RTL language support
- Contextual translations for AI responses

## 📈 Performance Optimization

- **Caching System**: Intelligent caching of reports and analysis
- **Database Optimization**: Indexed tables and optimized queries
- **Lazy Loading**: Modules loaded only when needed
- **Background Processing**: Heavy operations run in background
- **Rate Limiting**: API call throttling to prevent quota exhaustion

## 🛠️ Development

### Requirements
- WordPress 6.0+ (Tested up to 6.6)
- WooCommerce 8.0+ (Tested up to 10.0)
- PHP 8.0+ (Compatible with PHP 8.3)
- MySQL 5.7+ or MariaDB 10.2+
- Memory: 128MB+ recommended

### Development Setup
1. Clone the repository
2. Install dependencies (if any)
3. Configure development environment
4. Set up AI provider API keys for testing

### Contributing
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📝 Changelog

### Version 1.0.7
- **CRITICAL DUPLICATE ID & AUTHORIZATION FIXES**: Complete resolution of DOM validation and permission errors
- Fixed WordPress Settings API conflicts causing duplicate HTML ID errors
- Fixed authorization issues with non-existent capabilities and template context problems
- Enhanced Gemini API key validation with comprehensive error messages and debugging

### Version 1.0.6
- **CRITICAL JAVASCRIPT & API FIXES**: Fixed DOM validation errors and JavaScript issues
- Fixed "aia_ajax is not defined" error preventing API connection tests
- Updated Gemini API to v1beta with proper authentication headers
- Updated to latest Gemini 2.0 Flash model with improved error handling

### Version 1.0.5
- **CRITICAL ADMIN INTERFACE FIX**: Fixed null plugin instance errors in InventoryAnalysis module
- Fixed missing template files causing admin page errors
- Added complete admin interface templates (analysis, alerts, reports pages)
- Improved plugin instance handling with proper null checks

### Version 1.0.4
- **CRITICAL HOTFIX**: Fixed fatal error "Undefined constant AIA_VERSION" in AdminInterface.php
- Fixed admin asset enqueuing causing critical errors
- Plugin admin interface now loads correctly

### Version 1.0.3
- **CRITICAL MEMORY FIX**: Fixed fatal memory exhaustion error causing site freezing
- Fixed circular dependencies in plugin initialization
- Added memory usage monitoring and protection mechanisms
- Improved plugin initialization performance and stability

### Version 1.0.2
- Fixed fatal activation errors and PHP parse errors
- Plugin activation now works correctly

### Version 1.0.1
- Enhanced WooCommerce compatibility
- Improved multisite support

### Version 1.0.0
- Initial release
- AI chat interface with OpenAI and Gemini support
- Comprehensive inventory analysis
- Demand forecasting capabilities
- Supplier performance tracking
- Automated notification system
- Report generation and caching
- Modular architecture
- WordPress admin integration

## 📚 Documentation

### For Users
- [User Guide](docs/USER_GUIDE.md) - Complete guide for using the plugin
- [Installation Guide](docs/installation.md) - Step-by-step installation
- [FAQ](docs/USER_GUIDE.md#faq) - Frequently asked questions

### For Developers
- [Developer Guide](docs/DEVELOPER_GUIDE.md) - Technical documentation
- [API Reference](docs/API_REFERENCE.md) - REST API endpoints
- [AGENTS.md](AGENTS.md) - AI coding guidelines
- [Contributing](CONTRIBUTING.md) - How to contribute

### Quick Links
- [Latest Release](releases/ai-inventory-agent-v1.0.2.zip)
- [Changelog](CHANGELOG.md)
- [Support Forum](https://wordpress.org/support/plugin/ai-inventory-agent/)
- [Report Issues](https://github.com/yourusername/ai-inventory-agent/issues)

## 📄 License

This plugin is licensed under the GPL v2 or later.

## 🆘 Support

For support, feature requests, or bug reports:
1. Check the documentation
2. Search existing issues
3. Create a new issue with detailed information
4. Include WordPress and WooCommerce versions
5. Provide error logs if applicable

## 🙏 Credits

- Built with WordPress and WooCommerce APIs
- AI integration with OpenAI and Google Gemini
- UI components inspired by WordPress design system
- Icons and graphics from WordPress Dashicons

---

**AI Inventory Agent** - Revolutionizing inventory management with artificial intelligence.
