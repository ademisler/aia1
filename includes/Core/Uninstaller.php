<?php

namespace AIA\Core;

/**
 * Plugin Uninstaller Class
 * 
 * Handles plugin uninstallation and cleanup
 */
class Uninstaller {
    
    /**
     * Uninstall the plugin
     */
    public static function uninstall() {
        // Check if user has permission to uninstall
        if (!current_user_can('activate_plugins')) {
            return;
        }
        
        // Check if uninstall is being called from the correct context
        if (!defined('WP_UNINSTALL_PLUGIN')) {
            return;
        }
        
        // Get user preference for data retention
        $keep_data = get_option('aia_keep_data_on_uninstall', false);
        
        if (!$keep_data) {
            // Remove all plugin data
            self::remove_plugin_data();
        }
        
        // Clear scheduled events (always do this)
        self::clear_scheduled_events();
        
        // Remove capabilities
        self::remove_capabilities();
        
        // Clear cache and temporary files
        self::cleanup_files();
    }
    
    /**
     * Remove all plugin data
     */
    private static function remove_plugin_data() {
        // Drop custom tables
        if (class_exists('AIA\\Core\\Database')) {
            $database = new Database();
            $database->drop_tables();
        }
        
        // Remove options
        self::remove_options();
        
        // Remove user meta
        self::remove_user_meta();
        
        // Remove post meta
        self::remove_post_meta();
    }
    
    /**
     * Remove plugin options
     */
    private static function remove_options() {
        global $wpdb;
        
        // Remove all AIA-related options
        $options_to_remove = [
            'aia_settings',
            'aia_module_status',
            'aia_activated',
            'aia_activation_time',
            'aia_deactivated',
            'aia_deactivation_time',
            'aia_version',
            'aia_db_version',
            'aia_keep_data_on_uninstall'
        ];
        
        foreach ($options_to_remove as $option) {
            delete_option($option);
        }
        
        // Remove options with dynamic names
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'aia_%'");
        
        // Remove transients
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aia_%' OR option_name LIKE '_transient_timeout_aia_%'");
    }
    
    /**
     * Remove user meta data
     */
    private static function remove_user_meta() {
        global $wpdb;
        
        // Remove all AIA-related user meta
        $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'aia_%'");
    }
    
    /**
     * Remove post meta data
     */
    private static function remove_post_meta() {
        global $wpdb;
        
        // Remove all AIA-related post meta
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'aia_%' OR meta_key LIKE '_aia_%'");
    }
    
    /**
     * Clear scheduled events
     */
    private static function clear_scheduled_events() {
        $events = [
            'aia_daily_analysis',
            'aia_weekly_report',
            'aia_monthly_report',
            'aia_cache_cleanup',
            'aia_stock_check'
        ];
        
        foreach ($events as $event) {
            wp_clear_scheduled_hook($event);
        }
    }
    
    /**
     * Remove capabilities from roles
     */
    private static function remove_capabilities() {
        // Remove capabilities from existing roles
        $roles_to_update = ['administrator', 'shop_manager'];
        $capabilities_to_remove = ['manage_aia', 'view_aia_reports', 'configure_aia'];
        
        foreach ($roles_to_update as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($capabilities_to_remove as $capability) {
                    $role->remove_cap($capability);
                }
            }
        }
        
        // Remove custom role
        remove_role('inventory_manager');
    }
    
    /**
     * Cleanup files and directories
     */
    private static function cleanup_files() {
        $upload_dir = wp_upload_dir();
        
        // Remove plugin upload directory
        $plugin_upload_dir = $upload_dir['basedir'] . '/ai-inventory-agent/';
        if (is_dir($plugin_upload_dir)) {
            self::delete_directory($plugin_upload_dir);
        }
        
        // Remove temporary directory
        $temp_dir = $upload_dir['basedir'] . '/aia-temp/';
        if (is_dir($temp_dir)) {
            self::delete_directory($temp_dir);
        }
        
        // Remove log files
        $log_dir = WP_CONTENT_DIR . '/aia-logs/';
        if (is_dir($log_dir)) {
            self::delete_directory($log_dir);
        }
        
        // Clear any cached files
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
    
    /**
     * Recursively delete directory
     * 
     * @param string $dir Directory path
     * @return bool
     */
    private static function delete_directory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                self::delete_directory($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * Create backup of plugin data before uninstall
     */
    private static function create_backup() {
        $backup_data = [
            'settings' => get_option('aia_settings', []),
            'module_status' => get_option('aia_module_status', []),
            'timestamp' => current_time('mysql'),
            'version' => AIA_PLUGIN_VERSION
        ];
        
        // Save backup to uploads directory
        $upload_dir = wp_upload_dir();
        $backup_file = $upload_dir['basedir'] . '/aia-backup-' . date('Y-m-d-H-i-s') . '.json';
        
        file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT));
        
        // Log backup creation
        error_log('AIA: Backup created at ' . $backup_file);
    }
}