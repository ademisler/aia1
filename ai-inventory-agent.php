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
 * Requires at least: 6.0
 * Tested up to: 6.6
 * Requires PHP: 8.0
 * WC requires at least: 8.0
 * WC tested up to: 10.0
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
function aia_is_woocommerce_active() {
    if (is_multisite()) {
        // Check if WooCommerce is network activated
        if (array_key_exists('woocommerce/woocommerce.php', get_site_option('active_sitewide_plugins', []))) {
            return true;
        }
        // Check if WooCommerce is activated on current site
        return in_array('woocommerce/woocommerce.php', (array) get_option('active_plugins', []));
    } else {
        // Single site check
        return in_array('woocommerce/woocommerce.php', (array) get_option('active_plugins', []));
    }
}

// Check WooCommerce dependency
add_action('plugins_loaded', 'aia_check_woocommerce_dependency', 5);

function aia_check_woocommerce_dependency() {
    if (!aia_is_woocommerce_active()) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo __('AI Inventory Agent requires WooCommerce to be installed and activated.', 'ai-inventory-agent');
            echo '</p></div>';
        });
        
        // Deactivate plugin if WooCommerce is not active
        add_action('admin_init', function() {
            deactivate_plugins(AIA_PLUGIN_BASENAME);
        });
        return;
    }
}

// Enhanced Autoloader with error handling
spl_autoload_register(function ($class) {
    if (strpos($class, 'AIA\\') === 0) {
        $class = str_replace('AIA\\', '', $class);
        $class = str_replace('\\', '/', $class);
        $file = AIA_PLUGIN_DIR . 'includes/' . $class . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        } else {
            // Log missing class for debugging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("AIA Autoloader: Class file not found: {$file}");
            }
        }
    }
});

// Initialize the plugin with safety checks
add_action('plugins_loaded', function() {
    // Double-check WooCommerce is still active
    if (!aia_is_woocommerce_active()) {
        return;
    }
    
    // Check if core class exists
    if (!class_exists('AIA\\Core\\Plugin')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('AIA: Core Plugin class not found during initialization');
        }
        return;
    }
    
    try {
        AIA\Core\Plugin::get_instance();
    } catch (Exception $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('AIA: Plugin initialization failed: ' . $e->getMessage());
        }
        
        // Show admin notice about initialization failure
        add_action('admin_notices', function() use ($e) {
            echo '<div class="notice notice-error"><p>';
            echo sprintf(__('AI Inventory Agent failed to initialize: %s', 'ai-inventory-agent'), esc_html($e->getMessage()));
            echo '</p></div>';
        });
    }
}, 20);

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
register_uninstall_hook(__FILE__, 'aia_uninstall_plugin');

// Uninstall callback function
function aia_uninstall_plugin() {
    if (class_exists('AIA\\Core\\Uninstaller')) {
        AIA\Core\Uninstaller::uninstall();
    }
}