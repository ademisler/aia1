<?php

namespace AIA\Core;

/**
 * Plugin Activator Class
 * 
 * Handles plugin activation tasks
 */
class Activator {
    
    /**
     * Activate the plugin
     */
    public static function activate() {
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            deactivate_plugins(AIA_PLUGIN_BASENAME);
            wp_die(__('AI Inventory Agent requires WordPress version 5.0 or higher.', 'ai-inventory-agent'));
        }
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            deactivate_plugins(AIA_PLUGIN_BASENAME);
            wp_die(__('AI Inventory Agent requires PHP version 7.4 or higher.', 'ai-inventory-agent'));
        }
        
        // Check memory limit
        $memory_limit = self::convert_to_bytes(ini_get('memory_limit'));
        $required_memory = 128 * 1024 * 1024; // 128MB
        if ($memory_limit < $required_memory && $memory_limit != -1) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-warning"><p>';
                echo __('AI Inventory Agent: Low PHP memory limit detected. Recommend at least 128MB for optimal performance.', 'ai-inventory-agent');
                echo '</p></div>';
            });
        }
        
        // Check if WooCommerce is active
        if (!self::is_woocommerce_active()) {
            deactivate_plugins(AIA_PLUGIN_BASENAME);
            wp_die(__('AI Inventory Agent requires WooCommerce to be installed and activated.', 'ai-inventory-agent'));
        }
        
        // Initialize database with error handling
        try {
            $database = new Database();
        } catch (Exception $e) {
            deactivate_plugins(AIA_PLUGIN_BASENAME);
            wp_die(sprintf(__('AI Inventory Agent database initialization failed: %s', 'ai-inventory-agent'), $e->getMessage()));
        }
        
        // Set default options
        self::set_default_options();
        
        // Create user roles and capabilities
        self::create_roles();
        
        // Schedule cron events
        self::schedule_events();
        
        // Initialize sample data if in debug mode
        self::init_sample_data();
        
        // Set activation flag
        update_option('aia_activated', true);
        update_option('aia_activation_time', current_time('mysql'));
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        $default_settings = [
            'ai_provider' => 'openai',
            'api_key' => '',
            'chat_enabled' => true,
            'forecasting_enabled' => true,
            'notifications_enabled' => true,
            'reports_enabled' => true,
            'low_stock_threshold' => 5,
            'critical_stock_threshold' => 1,
            'notification_email' => get_option('admin_email'),
            'report_frequency' => 'weekly',
            'system_prompt' => 'You are an AI inventory management assistant. Help users manage their WooCommerce store inventory efficiently.',
            'max_chat_history' => 50,
            'forecast_days' => 30,
            'min_sales_history_days' => 90,
            'seasonal_analysis_enabled' => true,
            'supplier_risk_monitoring' => true,
            'auto_reorder_suggestions' => true,
            'email_notifications' => true,
            'dashboard_notifications' => true,
            'slack_notifications' => false,
            'slack_webhook_url' => '',
            'report_email_recipients' => [get_option('admin_email')],
            'cache_duration' => 3600,
            'debug_mode' => false
        ];
        
        // Only set defaults if option doesn't exist
        if (!get_option('aia_settings')) {
            update_option('aia_settings', $default_settings);
        }
        
        // Set module activation status
        $module_status = [
            'ai_chat' => true,
            'inventory_analysis' => true,
            'demand_forecasting' => true,
            'supplier_analysis' => true,
            'notifications' => true,
            'reporting' => true
        ];
        
        if (!get_option('aia_module_status')) {
            update_option('aia_module_status', $module_status);
        }
    }
    
    /**
     * Schedule cron events
     */
    private static function schedule_events() {
        // Daily analysis
        if (!wp_next_scheduled('aia_daily_analysis')) {
            wp_schedule_event(time(), 'daily', 'aia_daily_analysis');
        }
        
        // Weekly reports
        if (!wp_next_scheduled('aia_weekly_report')) {
            wp_schedule_event(strtotime('next monday 9:00'), 'weekly', 'aia_weekly_report');
        }
        
        // Monthly reports
        if (!wp_next_scheduled('aia_monthly_report')) {
            wp_schedule_event(strtotime('first day of next month 9:00'), 'monthly', 'aia_monthly_report');
        }
        
        // Generate weekly reports (different from aia_weekly_report)
        if (!wp_next_scheduled('aia_generate_weekly_report')) {
            wp_schedule_event(strtotime('next monday 8:00'), 'weekly', 'aia_generate_weekly_report');
        }
        
        // Generate monthly reports (different from aia_monthly_report)
        if (!wp_next_scheduled('aia_generate_monthly_report')) {
            wp_schedule_event(strtotime('first day of next month 8:00'), 'monthly', 'aia_generate_monthly_report');
        }
        
        // Cache cleanup
        if (!wp_next_scheduled('aia_cache_cleanup')) {
            wp_schedule_event(time(), 'hourly', 'aia_cache_cleanup');
        }
        
        // Stock level checks
        if (!wp_next_scheduled('aia_stock_check')) {
            wp_schedule_event(time(), 'twicedaily', 'aia_stock_check');
        }
        
        // Daily forecasting
        if (!wp_next_scheduled('aia_daily_forecasting')) {
            wp_schedule_event(time(), 'daily', 'aia_daily_forecasting');
        }
        
        // Weekly forecasting
        if (!wp_next_scheduled('aia_weekly_forecasting')) {
            wp_schedule_event(strtotime('next monday 10:00'), 'weekly', 'aia_weekly_forecasting');
        }
        
        // Daily stock analysis
        if (!wp_next_scheduled('aia_daily_stock_analysis')) {
            wp_schedule_event(time(), 'daily', 'aia_daily_stock_analysis');
        }
        
        // Weekly supplier analysis
        if (!wp_next_scheduled('aia_weekly_supplier_analysis')) {
            wp_schedule_event(strtotime('next sunday 9:00'), 'weekly', 'aia_weekly_supplier_analysis');
        }
        
        // Stock alerts check
        if (!wp_next_scheduled('aia_check_stock_alerts')) {
            wp_schedule_event(time(), 'twicedaily', 'aia_check_stock_alerts');
        }
    }
    
    /**
     * Create user roles and capabilities
     */
    private static function create_roles() {
        // Add capabilities to existing roles
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('manage_aia');
            $admin_role->add_cap('view_aia_reports');
            $admin_role->add_cap('configure_aia');
        }
        
        $shop_manager_role = get_role('shop_manager');
        if ($shop_manager_role) {
            $shop_manager_role->add_cap('manage_aia');
            $shop_manager_role->add_cap('view_aia_reports');
        }
        
        // Create custom role for inventory managers
        add_role('inventory_manager', __('Inventory Manager', 'ai-inventory-agent'), [
            'read' => true,
            'manage_aia' => true,
            'view_aia_reports' => true,
            'edit_products' => true,
            'read_private_products' => true,
            'view_woocommerce_reports' => true
        ]);
    }
    
    /**
     * Initialize sample data (for demo purposes)
     */
    private static function init_sample_data() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // Only create sample data in debug mode
            self::create_sample_suppliers();
        }
    }
    
    /**
     * Check if WooCommerce is active
     * 
     * @return bool
     */
    private static function is_woocommerce_active() {
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
    
    /**
     * Create sample supplier data
     */
    private static function create_sample_suppliers() {
        $database = new Database();
        
        $sample_suppliers = [
            [
                'supplier_id' => 'SUP001',
                'supplier_name' => 'Global Electronics Supply',
                'delivery_time_avg' => 7.5,
                'delivery_time_variance' => 2.1,
                'quality_score' => 4.2,
                'reliability_score' => 4.0,
                'cost_competitiveness' => 3.8,
                'total_orders' => 156,
                'successful_deliveries' => 148,
                'risk_level' => 'low',
                'notes' => 'Reliable supplier with consistent quality'
            ],
            [
                'supplier_id' => 'SUP002',
                'supplier_name' => 'Fast Fashion Wholesale',
                'delivery_time_avg' => 12.3,
                'delivery_time_variance' => 4.8,
                'quality_score' => 3.5,
                'reliability_score' => 3.2,
                'cost_competitiveness' => 4.5,
                'total_orders' => 89,
                'successful_deliveries' => 76,
                'risk_level' => 'medium',
                'notes' => 'Good prices but variable quality'
            ],
            [
                'supplier_id' => 'SUP003',
                'supplier_name' => 'Premium Home Goods',
                'delivery_time_avg' => 5.2,
                'delivery_time_variance' => 1.1,
                'quality_score' => 4.8,
                'reliability_score' => 4.7,
                'cost_competitiveness' => 2.9,
                'total_orders' => 234,
                'successful_deliveries' => 231,
                'risk_level' => 'low',
                'notes' => 'Premium supplier with excellent quality'
            ]
        ];
        
        foreach ($sample_suppliers as $supplier) {
            $database->update_supplier_performance($supplier['supplier_id'], $supplier);
        }
    }
    
    /**
     * Convert memory limit string to bytes
     * 
     * @param string $value Memory limit value
     * @return int Bytes
     */
    private static function convert_to_bytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value)-1]);
        $value = (int) $value;
        
        switch($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
}