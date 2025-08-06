<?php

namespace AIA\Core;

/**
 * Settings Manager Class
 * 
 * Centralized settings management with caching and validation
 */
class SettingsManager {
    
    /**
     * Settings cache key
     */
    const CACHE_KEY = 'aia_settings_cache';
    
    /**
     * Cache expiration time (1 hour)
     */
    const CACHE_EXPIRATION = 3600;
    
    /**
     * Default settings
     * 
     * @var array
     */
    private static $defaults = [
        'ai_provider' => 'openai',
        'api_key' => '',
        'model' => 'gpt-4',
        'low_stock_threshold' => 5,
        'critical_stock_threshold' => 1,
        'overstock_threshold' => 150,
        'enable_notifications' => true,
        'notification_email' => '',
        'enable_forecasting' => true,
        'forecast_days' => 30,
        'enable_ai_chat' => true,
        'enable_inventory_analysis' => true,
        'enable_demand_forecasting' => true,
        'enable_supplier_analysis' => true,
        'enable_notifications' => true,
        'enable_reporting' => true,
        'debug_mode' => false,
        'cache_duration' => 3600,
        'batch_size' => 100
    ];
    
    /**
     * Cached settings
     * 
     * @var array|null
     */
    private static $cache = null;
    
    /**
     * Get all settings with caching
     * 
     * @param bool $force_refresh Force refresh from database
     * @return array Settings array
     */
    public static function get_settings($force_refresh = false) {
        // Return cached settings if available and not forcing refresh
        if (!$force_refresh && self::$cache !== null) {
            return self::$cache;
        }
        
        // Try to get from WordPress transient cache
        $cached_settings = get_transient(self::CACHE_KEY);
        if (!$force_refresh && $cached_settings !== false) {
            self::$cache = $cached_settings;
            return self::$cache;
        }
        
        // Load from database
        $db_settings = get_option('aia_settings', []);
        
        // Merge with defaults
        $settings = wp_parse_args($db_settings, self::$defaults);
        
        // Validate settings
        $settings = self::validate_settings($settings);
        
        // Cache the settings
        self::$cache = $settings;
        set_transient(self::CACHE_KEY, $settings, self::CACHE_EXPIRATION);
        
        return $settings;
    }
    
    /**
     * Get a specific setting value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed Setting value
     */
    public static function get_setting($key, $default = null) {
        $settings = self::get_settings();
        
        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }
        
        // Return default from our defaults array if available
        if ($default === null && array_key_exists($key, self::$defaults)) {
            return self::$defaults[$key];
        }
        
        return $default;
    }
    
    /**
     * Update settings
     * 
     * @param array $new_settings Settings to update
     * @return bool Success status
     */
    public static function update_settings($new_settings) {
        // Get current settings
        $current_settings = self::get_settings();
        
        // Merge with new settings
        $updated_settings = array_merge($current_settings, $new_settings);
        
        // Validate settings
        $updated_settings = self::validate_settings($updated_settings);
        
        // Save to database
        $success = update_option('aia_settings', $updated_settings);
        
        if ($success) {
            // Clear cache
            self::clear_cache();
            
            // Update our cache
            self::$cache = $updated_settings;
            set_transient(self::CACHE_KEY, $updated_settings, self::CACHE_EXPIRATION);
            
            // Trigger action for other components
            do_action('aia_settings_updated', $updated_settings, $new_settings);
        }
        
        return $success;
    }
    
    /**
     * Update a single setting
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool Success status
     */
    public static function update_setting($key, $value) {
        return self::update_settings([$key => $value]);
    }
    
    /**
     * Validate settings
     * 
     * @param array $settings Settings to validate
     * @return array Validated settings
     */
    private static function validate_settings($settings) {
        // AI Provider validation
        if (!in_array($settings['ai_provider'], ['openai', 'gemini'])) {
            $settings['ai_provider'] = self::$defaults['ai_provider'];
        }
        
        // Numeric validations
        $numeric_fields = [
            'low_stock_threshold' => 1,
            'critical_stock_threshold' => 1,
            'overstock_threshold' => 100,
            'forecast_days' => 1,
            'cache_duration' => 300,
            'batch_size' => 10
        ];
        
        foreach ($numeric_fields as $field => $min_value) {
            if (!is_numeric($settings[$field]) || $settings[$field] < $min_value) {
                $settings[$field] = self::$defaults[$field];
            }
        }
        
        // Boolean validations
        $boolean_fields = [
            'enable_notifications',
            'enable_forecasting',
            'enable_ai_chat',
            'enable_inventory_analysis',
            'enable_demand_forecasting',
            'enable_supplier_analysis',
            'enable_reporting',
            'debug_mode'
        ];
        
        foreach ($boolean_fields as $field) {
            $settings[$field] = (bool) $settings[$field];
        }
        
        // Email validation
        if (!empty($settings['notification_email']) && !is_email($settings['notification_email'])) {
            $settings['notification_email'] = '';
        }
        
        // API key sanitization
        $settings['api_key'] = sanitize_text_field($settings['api_key']);
        
        return $settings;
    }
    
    /**
     * Clear settings cache
     */
    public static function clear_cache() {
        self::$cache = null;
        delete_transient(self::CACHE_KEY);
    }
    
    /**
     * Reset settings to defaults
     * 
     * @return bool Success status
     */
    public static function reset_to_defaults() {
        $success = update_option('aia_settings', self::$defaults);
        
        if ($success) {
            self::clear_cache();
            do_action('aia_settings_reset');
        }
        
        return $success;
    }
    
    /**
     * Get default settings
     * 
     * @return array Default settings
     */
    public static function get_defaults() {
        return self::$defaults;
    }
    
    /**
     * Check if a module is enabled
     * 
     * @param string $module_id Module identifier
     * @return bool True if enabled
     */
    public static function is_module_enabled($module_id) {
        $setting_key = "enable_{$module_id}";
        return self::get_setting($setting_key, true); // Default to enabled
    }
    
    /**
     * Get settings for export
     * 
     * @return array Settings without sensitive data
     */
    public static function get_exportable_settings() {
        $settings = self::get_settings();
        
        // Remove sensitive information
        unset($settings['api_key']);
        
        return $settings;
    }
    
    /**
     * Import settings
     * 
     * @param array $import_settings Settings to import
     * @param bool $merge Whether to merge with existing settings
     * @return bool Success status
     */
    public static function import_settings($import_settings, $merge = true) {
        if (!is_array($import_settings)) {
            return false;
        }
        
        if ($merge) {
            return self::update_settings($import_settings);
        } else {
            // Replace all settings
            $validated_settings = self::validate_settings($import_settings);
            $success = update_option('aia_settings', $validated_settings);
            
            if ($success) {
                self::clear_cache();
                do_action('aia_settings_imported', $validated_settings);
            }
            
            return $success;
        }
    }
    
    /**
     * Get settings schema for validation
     * 
     * @return array Settings schema
     */
    public static function get_schema() {
        return [
            'ai_provider' => [
                'type' => 'string',
                'enum' => ['openai', 'gemini'],
                'default' => 'openai'
            ],
            'api_key' => [
                'type' => 'string',
                'sensitive' => true,
                'default' => ''
            ],
            'model' => [
                'type' => 'string',
                'default' => 'gpt-4'
            ],
            'low_stock_threshold' => [
                'type' => 'integer',
                'minimum' => 1,
                'default' => 5
            ],
            'critical_stock_threshold' => [
                'type' => 'integer',
                'minimum' => 1,
                'default' => 1
            ],
            'overstock_threshold' => [
                'type' => 'integer',
                'minimum' => 100,
                'default' => 150
            ],
            'enable_notifications' => [
                'type' => 'boolean',
                'default' => true
            ],
            'notification_email' => [
                'type' => 'string',
                'format' => 'email',
                'default' => ''
            ],
            'enable_forecasting' => [
                'type' => 'boolean',
                'default' => true
            ],
            'forecast_days' => [
                'type' => 'integer',
                'minimum' => 1,
                'maximum' => 365,
                'default' => 30
            ],
            'debug_mode' => [
                'type' => 'boolean',
                'default' => false
            ],
            'cache_duration' => [
                'type' => 'integer',
                'minimum' => 300,
                'default' => 3600
            ],
            'batch_size' => [
                'type' => 'integer',
                'minimum' => 10,
                'maximum' => 1000,
                'default' => 100
            ]
        ];
    }
}