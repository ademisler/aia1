<?php

namespace AIA\Core;

/**
 * Plugin Deactivator Class
 * 
 * Handles plugin deactivation tasks
 */
class Deactivator {
    
    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Clear scheduled events
        self::clear_scheduled_events();
        
        // Clear any temporary data
        self::clear_temporary_data();
        
        // Set deactivation flag
        update_option('aia_deactivated', true);
        update_option('aia_deactivation_time', current_time('mysql'));
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Clear all scheduled events
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
            $timestamp = wp_next_scheduled($event);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $event);
            }
        }
        
        // Clear all instances of recurring events
        wp_clear_scheduled_hook('aia_daily_analysis');
        wp_clear_scheduled_hook('aia_weekly_report');
        wp_clear_scheduled_hook('aia_monthly_report');
        wp_clear_scheduled_hook('aia_cache_cleanup');
        wp_clear_scheduled_hook('aia_stock_check');
    }
    
    /**
     * Clear temporary data
     */
    private static function clear_temporary_data() {
        // Clear cache
        if (class_exists('AIA\\Core\\Database')) {
            $database = new Database();
            $database->clean_expired_cache();
        }
        
        // Clear transients
        self::clear_transients();
        
        // Clear any temporary files
        self::clear_temporary_files();
    }
    
    /**
     * Clear plugin transients
     */
    private static function clear_transients() {
        global $wpdb;
        
        // Delete all AIA-related transients
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aia_%' OR option_name LIKE '_transient_timeout_aia_%'");
    }
    
    /**
     * Clear temporary files
     */
    private static function clear_temporary_files() {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/aia-temp/';
        
        if (is_dir($temp_dir)) {
            self::delete_directory($temp_dir);
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
}