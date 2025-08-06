<?php

namespace AIA\Core;

/**
 * Memory Manager Class
 * 
 * Centralized memory management and monitoring for the plugin
 */
class MemoryManager {
    
    /**
     * Memory thresholds in bytes
     */
    const THRESHOLD_CRITICAL = 1073741824; // 1GB
    const THRESHOLD_HIGH = 838860800;      // 800MB
    const THRESHOLD_MEDIUM = 734003200;    // 700MB
    const THRESHOLD_LOW = 629145600;       // 600MB
    
    /**
     * Memory usage levels
     */
    const LEVEL_NORMAL = 'normal';
    const LEVEL_WARNING = 'warning';
    const LEVEL_HIGH = 'high';
    const LEVEL_CRITICAL = 'critical';
    
    /**
     * Get current memory usage in bytes
     * 
     * @return int Memory usage in bytes
     */
    public static function get_current_usage() {
        return memory_get_usage(true);
    }
    
    /**
     * Get memory usage level
     * 
     * @return string Memory level constant
     */
    public static function get_memory_level() {
        $usage = self::get_current_usage();
        
        if ($usage >= self::THRESHOLD_CRITICAL) {
            return self::LEVEL_CRITICAL;
        } elseif ($usage >= self::THRESHOLD_HIGH) {
            return self::LEVEL_HIGH;
        } elseif ($usage >= self::THRESHOLD_MEDIUM) {
            return self::LEVEL_WARNING;
        }
        
        return self::LEVEL_NORMAL;
    }
    
    /**
     * Check if memory usage is safe for operation
     * 
     * @param string $operation Operation type for logging
     * @param string $required_level Minimum required memory level
     * @return bool True if safe to proceed
     */
    public static function is_safe_for_operation($operation = 'general', $required_level = self::LEVEL_HIGH) {
        $current_level = self::get_memory_level();
        $is_safe = true;
        
        switch ($required_level) {
            case self::LEVEL_CRITICAL:
                $is_safe = ($current_level !== self::LEVEL_CRITICAL);
                break;
            case self::LEVEL_HIGH:
                $is_safe = !in_array($current_level, [self::LEVEL_CRITICAL, self::LEVEL_HIGH]);
                break;
            case self::LEVEL_WARNING:
                $is_safe = ($current_level === self::LEVEL_NORMAL);
                break;
        }
        
        if (!$is_safe && defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                'AIA Memory Manager: Operation "%s" blocked due to %s memory usage (%s)',
                $operation,
                $current_level,
                self::format_bytes(self::get_current_usage())
            ));
        }
        
        return $is_safe;
    }
    
    /**
     * Log memory usage for debugging
     * 
     * @param string $context Context for the memory check
     */
    public static function log_usage($context = '') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $usage = self::get_current_usage();
            $level = self::get_memory_level();
            $peak = memory_get_peak_usage(true);
            
            error_log(sprintf(
                'AIA Memory [%s]: Current: %s, Peak: %s, Level: %s%s',
                $context,
                self::format_bytes($usage),
                self::format_bytes($peak),
                $level,
                $context ? " - {$context}" : ''
            ));
        }
    }
    
    /**
     * Format bytes into human readable format
     * 
     * @param int $bytes Bytes to format
     * @return string Formatted string
     */
    public static function format_bytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf('%.2f %s', $bytes / pow(1024, $factor), $units[$factor]);
    }
    
    /**
     * Get memory statistics
     * 
     * @return array Memory statistics
     */
    public static function get_stats() {
        $current = self::get_current_usage();
        $peak = memory_get_peak_usage(true);
        $limit = ini_get('memory_limit');
        
        // Convert limit to bytes
        $limit_bytes = 0;
        if ($limit !== '-1') {
            $limit_bytes = self::convert_to_bytes($limit);
        }
        
        return [
            'current' => $current,
            'current_formatted' => self::format_bytes($current),
            'peak' => $peak,
            'peak_formatted' => self::format_bytes($peak),
            'limit' => $limit,
            'limit_bytes' => $limit_bytes,
            'level' => self::get_memory_level(),
            'usage_percentage' => $limit_bytes > 0 ? round(($current / $limit_bytes) * 100, 2) : 0
        ];
    }
    
    /**
     * Convert memory limit string to bytes
     * 
     * @param string $limit Memory limit string (e.g., '512M')
     * @return int Bytes
     */
    private static function convert_to_bytes($limit) {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
                // Fall through
            case 'm':
                $value *= 1024;
                // Fall through
            case 'k':
                $value *= 1024;
                break;
        }
        
        return $value;
    }
    
    /**
     * Check if plugin should continue loading based on memory
     * 
     * @return bool True if should continue loading
     */
    public static function should_continue_loading() {
        return self::is_safe_for_operation('plugin_loading', self::LEVEL_HIGH);
    }
    
    /**
     * Check if modules should be initialized
     * 
     * @return bool True if modules can be initialized
     */
    public static function can_initialize_modules() {
        return self::is_safe_for_operation('module_initialization', self::LEVEL_WARNING);
    }
}