# Changelog

All notable changes to AI Inventory Agent will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.7] - 2025-01-08

### 🚨 Critical Duplicate ID & Authorization Fixes

#### Fixed
- **Complete Duplicate HTML ID Resolution**
  - Fixed WordPress Settings API forms creating duplicate IDs with custom templates
  - Renamed all WordPress Settings API field IDs with 'settings_' prefix to avoid conflicts
  - Settings page now uses only WordPress Settings API (removed duplicate manual forms)
  - No more DOM validation errors in browser console

- **Authorization/Permission Issues**
  - Fixed non-existent 'configure_aia' capability causing permission errors
  - Changed to standard 'manage_options' capability for settings access
  - Fixed template context issues where $this->plugin was undefined
  - All admin templates now properly access plugin instance via Plugin::get_instance()

- **Enhanced API Key Validation**
  - Added comprehensive Gemini API key format validation
  - Improved error messages for specific HTTP status codes (400, 401, 403, 429)
  - Added debugging information for API connection failures
  - Better error handling for empty or invalid API keys

#### Technical Details
- **AdminInterface.php**: 
  - Renamed all WordPress Settings API field IDs: ai_provider → settings_ai_provider, etc.
  - Fixed permission check from 'configure_aia' to 'manage_options'
- **templates/admin/*.php**: 
  - Fixed plugin instance access from $this->plugin to Plugin::get_instance()
  - Added null checks for safe module access
- **GeminiProvider.php**: 
  - Enhanced error handling with specific status code messages
  - Added API key length validation and debugging info
- **settings.php**: 
  - Simplified to use only WordPress Settings API (removed duplicate manual forms)
  - Eliminated all duplicate form field rendering

#### New Features
- **Comprehensive API Debugging**: Detailed error messages with API key validation info
- **Better Error Messages**: User-friendly messages for common API authentication issues

## [1.0.6] - 2025-01-08

### 🚨 Critical JavaScript & API Fixes

#### Fixed
- **Duplicate HTML IDs Error**
  - Fixed DOM validation errors: "Found 2 elements with non-unique id"
  - Renamed duplicate form field IDs in alerts.php template to avoid conflicts
  - Fixed ai_provider, api_key, critical_stock_threshold, low_stock_threshold, notification_email, and system_prompt duplicates
  - All admin pages now validate correctly without DOM warnings

- **Missing JavaScript Object Error**
  - Fixed "Uncaught ReferenceError: aia_ajax is not defined" in admin.js line 405
  - Corrected JavaScript localization from 'aia_admin' to 'aia_ajax' in AdminInterface.php
  - API connection test button now works correctly

- **Gemini API Configuration**
  - Updated to latest Gemini API v1beta endpoint with correct headers
  - Changed API authentication from query parameter to X-goog-api-key header
  - Updated default model from 'gemini-pro' to 'gemini-2.0-flash' (latest)
  - Added support for Gemini 2.0 Flash model
  - Improved error handling and connection testing

#### Technical Details
- **alerts.php**: Renamed all form field IDs with 'alerts_' prefix to prevent conflicts
- **AdminInterface.php**: Fixed wp_localize_script object name from 'aia_admin' to 'aia_ajax'
- **GeminiProvider.php**: 
  - Updated API endpoint to use X-goog-api-key header instead of query parameter
  - Updated default model to 'gemini-2.0-flash'
  - Improved test_connection() method with better error messages
  - Updated available models list with latest Gemini models

#### New Features
- **Enhanced API Testing**: Better connection test with detailed success/error messages
- **Latest Gemini Models**: Support for Gemini 2.0 Flash and updated model selection

## [1.0.5] - 2025-01-08

### 🚨 Critical Admin Interface Fix

#### Fixed
- **Null Plugin Instance Error**
  - Fixed fatal error: "Call to a member function get_setting() on null" in InventoryAnalysis.php line 204
  - Added null checks and safe plugin instance initialization in all module methods
  - Fixed plugin instance availability in get_low_stock_products(), get_out_of_stock_products(), and other inventory methods
  - Added proper error handling and logging for debugging

- **Missing Template Files**
  - Created missing admin template files: analysis.php, alerts.php, reports.php
  - Fixed "Failed to open stream: No such file or directory" warnings
  - All admin pages now load correctly without template errors

#### Technical Details
- **InventoryAnalysis.php**: Added plugin instance checks in 5+ methods
  - get_low_stock_products(): Added null check and safe initialization
  - get_out_of_stock_products(): Added null check and safe initialization  
  - calculate_low_stock_value(): Added null check and safe initialization
  - check_stock_alerts(): Added null check and safe initialization
  - get_recent_stock_changes(): Added null check and safe initialization
  - on_stock_change(): Added null check and safe initialization

- **Template Files Created**:
  - templates/admin/analysis.php: Complete inventory analysis dashboard
  - templates/admin/alerts.php: Stock alerts management interface
  - templates/admin/reports.php: Reports generation and management interface

#### New Features
- **Analysis Page**: Stock overview cards, recent activity tracking, low stock products table
- **Alerts Page**: Alert settings, critical/low stock warnings, management actions
- **Reports Page**: Quick statistics, report generation, settings management

## [1.0.4] - 2025-01-08

### 🚨 Critical Hotfix

#### Fixed
- **Undefined Constant Error**
  - Fixed fatal error: "Undefined constant AIA_VERSION" in AdminInterface.php line 626
  - Corrected all instances of incorrect `AIA_VERSION` to proper `AIA_PLUGIN_VERSION` constant
  - Fixed admin asset enqueuing that was causing critical errors
  - Plugin admin interface now loads correctly without fatal errors

#### Technical Details
- **AdminInterface.php**: Fixed 4 instances of `AIA_VERSION` → `AIA_PLUGIN_VERSION`
  - Line 626: CSS enqueuing version parameter
  - Line 634: Admin script enqueuing version parameter  
  - Line 643: UI components script enqueuing version parameter
  - Line 661: Charts script enqueuing version parameter

## [1.0.3] - 2025-01-08

### 🚨 Critical Memory Fix

#### Fixed
- **Memory Exhaustion Issues**
  - Fixed fatal error: "Allowed memory size of 1073741824 bytes exhausted" that was causing site freezing
  - Eliminated circular dependencies in plugin initialization that created infinite loops
  - Added memory usage monitoring and protection mechanisms (700MB-900MB thresholds)
  - Fixed circular dependency between Plugin class and ModuleManager
  - Fixed circular dependency between Plugin class and AdminInterface
  - Fixed circular dependencies in all module classes (AIChat, Notifications, Reporting, etc.)

#### Technical Details
- **Plugin.php**: Added recursion prevention and memory checks in `get_instance()` method
- **ModuleManager.php**: Removed `Plugin::get_instance()` call from `is_module_enabled()` to break circular dependency
- **AdminInterface.php**: Added `set_plugin_instance()` method to safely set plugin reference after initialization
- **All Modules**: Moved `Plugin::get_instance()` calls from constructors to `init()` methods
- **Database.php**: Added memory checks before table creation operations
- **Main Plugin File**: Added multiple initialization guards and memory monitoring

#### Performance Improvements
- Plugin initialization now uses significantly less memory
- Prevented infinite recursion that was exhausting server resources
- Added proper error handling and logging for debugging
- Implemented safer module loading sequence

## [1.0.2] - 2025-01-08

### 🚨 Critical Bug Fixes

#### Fixed
- **Plugin Activation Issues**
  - Fixed fatal error: Cannot redeclare `AIA\Core\Database::get_table_name()` method (duplicate method declaration removed)
  - Fixed PHP parse error in `InventoryContext.php` line 36 (corrected regex pattern escaping)
  - Resolved all PHP syntax errors preventing plugin activation
  - Plugin now successfully activates without errors

#### Technical Details
- **Database.php**: Removed duplicate `get_table_name()` method declaration (line 531)
- **InventoryContext.php**: Fixed regex pattern syntax by properly escaping single quotes in pattern `/'([^']+)'/i` → `/\'([^\']+)\'/i`
- Verified syntax validation on all 22 PHP files in the plugin

## [1.0.1] - 2025-01-08

### 🔧 Bug Fixes & Improvements

#### Fixed
- **WooCommerce Compatibility**
  - Enhanced WooCommerce dependency checking
  - Improved multisite support detection
  - Fixed plugin activation issues on multisite installations

- **Plugin Stability**
  - Refactored uninstall hook to use separate callback function
  - Enhanced error handling throughout the plugin
  - Improved dependency validation

- **Core Improvements**
  - Updated compatibility for WordPress 6.0+ and WooCommerce 10.0+
  - Enhanced plugin initialization process
  - Better error reporting and debugging

#### Changed
- Updated minimum requirements documentation
- Improved plugin architecture for better maintainability
- Enhanced code organization and structure

## [1.0.0] - 2024-01-15

### 🎉 Initial Release

#### Added
- **Core Features**
  - AI-powered inventory management system
  - Real-time stock tracking and analysis
  - WooCommerce integration
  - Multisite support

- **AI Integration**
  - OpenAI GPT-4 support
  - Google Gemini integration
  - Intelligent chat assistant
  - Context-aware responses

- **Inventory Analysis**
  - Real-time stock monitoring
  - Low stock alerts
  - Overstock detection
  - Stock value calculations
  - Movement tracking

- **Demand Forecasting**
  - AI-powered demand predictions
  - Seasonal trend analysis
  - Reorder point calculations
  - Confidence scoring

- **Supplier Management**
  - Performance tracking
  - Risk assessment
  - Lead time monitoring
  - Quality scoring

- **Notification System**
  - Email alerts
  - Admin notices
  - Real-time notifications
  - Customizable thresholds

- **Reporting**
  - Weekly/Monthly reports
  - PDF export
  - Email delivery
  - Custom report generation

- **Modern UI/UX**
  - Design token system
  - Component library
  - Animated interfaces
  - Dark mode support
  - Mobile responsive

- **Developer Features**
  - Modular architecture
  - Hook system
  - REST API endpoints
  - Rate limiting
  - Extensive documentation

#### Security
- SQL injection prevention
- XSS protection
- Nonce verification
- Capability checks
- Input sanitization
- Output escaping

#### Performance
- Database query optimization
- Caching system
- Batch processing
- Lazy loading
- Efficient AJAX handling

### Fixed
- WooCommerce multisite compatibility
- Exception handling in all modules
- Memory leak prevention in large datasets
- API error handling improvements
- JavaScript undefined variables
- Missing module file errors

### Changed
- Improved error messages
- Enhanced user feedback
- Better code organization
- Optimized database queries
- Updated dependencies

## [0.9.0-beta] - 2024-01-01

### Added
- Beta testing features
- Initial AI integration
- Basic inventory tracking

### Changed
- Plugin architecture redesign
- Database schema updates

### Fixed
- Various beta bugs
- Performance issues

## [0.1.0-alpha] - 2023-12-01

### Added
- Initial plugin structure
- Basic WooCommerce hooks
- Database tables creation
- Admin menu integration

---

## Upgrade Notes

### From 0.x to 1.0.0
1. Backup your database before upgrading
2. Deactivate the old version
3. Upload and activate the new version
4. Re-enter your API keys in settings
5. Run the database upgrade if prompted

## Version Support

- **1.0.0**: Full support
- **0.9.0-beta**: Limited support until 2024-06-01
- **0.1.0-alpha**: No longer supported

## Future Releases

### [1.1.0] - Planned Q2 2024
- Voice input for AI chat
- Mobile app integration
- Advanced analytics dashboard
- Custom AI training

### [1.2.0] - Planned Q3 2024
- Multi-language support
- Barcode scanning
- Warehouse management
- B2B features

---

For detailed release notes, visit our [GitHub Releases](https://github.com/yourusername/ai-inventory-agent/releases) page.