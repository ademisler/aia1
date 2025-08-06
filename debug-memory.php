<?php
/**
 * Debug script to test AI Inventory Agent memory usage
 * Place this in your WordPress root and access via browser to test
 */

// WordPress bootstrap
require_once 'wp-config.php';
require_once ABSPATH . 'wp-settings.php';

echo "<h1>AI Inventory Agent Memory Debug</h1>\n";
echo "<p>Initial Memory Usage: " . memory_get_usage(true) . " bytes (" . round(memory_get_usage(true) / 1024 / 1024, 2) . " MB)</p>\n";

// Check if plugin is active
$active_plugins = get_option('active_plugins', []);
$plugin_active = in_array('ai-inventory-agent/ai-inventory-agent.php', $active_plugins);

echo "<p>Plugin Active: " . ($plugin_active ? 'Yes' : 'No') . "</p>\n";

if ($plugin_active) {
    // Check if classes exist
    echo "<p>Core Plugin Class Exists: " . (class_exists('AIA\\Core\\Plugin') ? 'Yes' : 'No') . "</p>\n";
    
    if (class_exists('AIA\\Core\\Plugin')) {
        try {
            $instance = AIA\Core\Plugin::get_instance();
            echo "<p>Plugin Instance Created: " . ($instance ? 'Yes' : 'No') . "</p>\n";
            echo "<p>Memory After Plugin Load: " . memory_get_usage(true) . " bytes (" . round(memory_get_usage(true) / 1024 / 1024, 2) . " MB)</p>\n";
        } catch (Exception $e) {
            echo "<p style='color: red;'>Plugin Initialization Error: " . esc_html($e->getMessage()) . "</p>\n";
        }
    }
}

echo "<p>Peak Memory Usage: " . memory_get_peak_usage(true) . " bytes (" . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB)</p>\n";
echo "<p>Memory Limit: " . ini_get('memory_limit') . "</p>\n";