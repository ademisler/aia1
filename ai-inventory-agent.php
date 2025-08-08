<?php
/**
 * Plugin Name: AI Inventory Agent (AIA)
 * Description: Clean, modern AI-powered inventory assistant for WooCommerce with professional UI and robust REST API.
 * Version: 3.1.2
 * Author: AIA Team
 * Text Domain: ai-inventory-agent
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * WC requires at least: 8.0
 */

if (!defined('ABSPATH')) { exit; }

// Constants
define('AIA_PLUGIN_FILE', __FILE__);
define('AIA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIA_PLUGIN_VERSION', '3.1.2');

// Simple autoloader (PSR-4 like)
spl_autoload_register(function($class) {
    if (strpos($class, 'AIA\\') !== 0) { return; }
    $rel = substr($class, 4); // remove AIA\\
    $rel = str_replace('\\', DIRECTORY_SEPARATOR, $rel);
    $path = AIA_PLUGIN_DIR . 'includes/' . $rel . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

// WooCommerce check
function aia_wc_active() {
    return class_exists('WooCommerce');
}

// Activation/Deactivation
register_activation_hook(__FILE__, function() {
    if (!aia_wc_active()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('AI Inventory Agent requires WooCommerce to be active.', 'ai-inventory-agent'));
    }
});

register_deactivation_hook(__FILE__, function() {
    // Placeholder for scheduled cleanups if needed
});

// Bootstrap
add_action('plugins_loaded', function() {
    if (!aia_wc_active()) { return; }
    AIA\Core\Plugin::instance();
}, 20);