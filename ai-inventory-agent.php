<?php
/**
 * Plugin Name: AI Inventory Agent (AIA)
 * Plugin URI: https://example.com/ai-inventory-agent
 * Description: AI-powered inventory management plugin for WooCommerce stores with intelligent stock analysis, demand forecasting, and automated recommendations.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: ai-inventory-agent
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AIA_PLUGIN_FILE', __FILE__);
define('AIA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIA_PLUGIN_VERSION', '1.0.0');
define('AIA_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo __('AI Inventory Agent requires WooCommerce to be installed and activated.', 'ai-inventory-agent');
        echo '</p></div>';
    });
    return;
}

// Autoloader
spl_autoload_register(function ($class) {
    if (strpos($class, 'AIA\\') === 0) {
        $class = str_replace('AIA\\', '', $class);
        $class = str_replace('\\', '/', $class);
        $file = AIA_PLUGIN_DIR . 'includes/' . $class . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Initialize the plugin
add_action('plugins_loaded', function() {
    if (class_exists('AIA\\Core\\Plugin')) {
        AIA\Core\Plugin::get_instance();
    }
});

// Activation hook
register_activation_hook(__FILE__, function() {
    if (class_exists('AIA\\Core\\Activator')) {
        AIA\Core\Activator::activate();
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    if (class_exists('AIA\\Core\\Deactivator')) {
        AIA\Core\Deactivator::deactivate();
    }
});

// Uninstall hook
register_uninstall_hook(__FILE__, function() {
    if (class_exists('AIA\\Core\\Uninstaller')) {
        AIA\Core\Uninstaller::uninstall();
    }
});