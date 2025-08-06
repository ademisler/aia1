<?php

namespace AIA\Core;

use AIA\Core\ModuleManager;
use AIA\Core\Database;
use AIA\Admin\AdminInterface;

/**
 * Main Plugin Class
 * 
 * Handles plugin initialization and coordinates all modules
 */
class Plugin {
    
    /**
     * Plugin instance
     * 
     * @var Plugin
     */
    private static $instance = null;
    
    /**
     * Module manager instance
     * 
     * @var ModuleManager
     */
    private $module_manager;
    
    /**
     * Database handler instance
     * 
     * @var Database
     */
    private $database;
    
    /**
     * Admin interface instance
     * 
     * @var AdminInterface
     */
    private $admin_interface;
    
    /**
     * Plugin settings
     * 
     * @var array
     */
    private $settings;
    
    /**
     * Get plugin instance (Singleton pattern)
     * 
     * @return Plugin
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - Initialize plugin
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize plugin components
     */
    private function init() {
        // Load text domain for translations
        add_action('init', [$this, 'load_textdomain']);
        
        // Initialize database
        $this->database = new Database();
        
        // Initialize module manager
        $this->module_manager = new ModuleManager();
        
        // Initialize admin interface
        if (is_admin()) {
            $this->admin_interface = new AdminInterface();
        }
        
        // Load plugin settings
        $this->load_settings();
        
        // Register hooks
        $this->register_hooks();
        
        // Initialize modules
        $this->init_modules();
    }
    
    /**
     * Load plugin text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'ai-inventory-agent',
            false,
            dirname(AIA_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Load plugin settings
     */
    private function load_settings() {
        $this->settings = get_option('aia_settings', [
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
        ]);
    }
    
    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        
        // AJAX handlers
        add_action('wp_ajax_aia_chat', [$this, 'handle_chat_ajax']);
        add_action('wp_ajax_aia_get_inventory_data', [$this, 'handle_inventory_data_ajax']);
        
        // WooCommerce hooks
        add_action('woocommerce_product_set_stock', [$this, 'on_stock_change'], 10, 1);
        add_action('woocommerce_order_status_completed', [$this, 'on_order_completed'], 10, 1);
        
        // Scheduled events
        add_action('aia_daily_analysis', [$this, 'run_daily_analysis']);
        add_action('aia_weekly_report', [$this, 'generate_weekly_report']);
        add_action('aia_monthly_report', [$this, 'generate_monthly_report']);
    }
    
    /**
     * Initialize all modules
     */
    private function init_modules() {
        // Register core modules
        $this->module_manager->register_module('ai_chat', 'AIA\\Modules\\AIChat');
        $this->module_manager->register_module('inventory_analysis', 'AIA\\Modules\\InventoryAnalysis');
        $this->module_manager->register_module('demand_forecasting', 'AIA\\Modules\\DemandForecasting');
        $this->module_manager->register_module('supplier_analysis', 'AIA\\Modules\\SupplierAnalysis');
        $this->module_manager->register_module('notifications', 'AIA\\Modules\\Notifications');
        $this->module_manager->register_module('reporting', 'AIA\\Modules\\Reporting');
        
        // Initialize active modules
        $this->module_manager->init_modules();
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on AIA admin pages
        if (strpos($hook, 'ai-inventory-agent') === false) {
            return;
        }
        
        wp_enqueue_script(
            'aia-admin-js',
            AIA_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery', 'wp-util'],
            AIA_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'aia-admin-css',
            AIA_PLUGIN_URL . 'assets/css/admin.css',
            [],
            AIA_PLUGIN_VERSION
        );
        
        // Localize script with data
        wp_localize_script('aia-admin-js', 'aia_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aia_ajax_nonce'),
            'strings' => [
                'loading' => __('Loading...', 'ai-inventory-agent'),
                'error' => __('An error occurred. Please try again.', 'ai-inventory-agent'),
                'success' => __('Operation completed successfully.', 'ai-inventory-agent'),
            ]
        ]);
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts() {
        // Frontend scripts if needed
    }
    
    /**
     * Handle chat AJAX requests
     */
    public function handle_chat_ajax() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        // Apply rate limiting - 20 requests per minute
        \AIA\Utils\RateLimiter::check_ajax_limit('ai_chat', 20, 60);
        
        $message = sanitize_text_field($_POST['message'] ?? '');
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        
        if (empty($message)) {
            wp_send_json_error(__('Message cannot be empty.', 'ai-inventory-agent'));
        }
        
        $ai_chat = $this->module_manager->get_module('ai_chat');
        if ($ai_chat) {
            $response = $ai_chat->process_message($message, $session_id);
            wp_send_json_success($response);
        } else {
            wp_send_json_error(__('AI Chat module not available.', 'ai-inventory-agent'));
        }
    }
    
    /**
     * Handle inventory data AJAX requests
     */
    public function handle_inventory_data_ajax() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $inventory_analysis = $this->module_manager->get_module('inventory_analysis');
        if ($inventory_analysis) {
            $data = $inventory_analysis->get_inventory_summary();
            wp_send_json_success($data);
        } else {
            wp_send_json_error(__('Inventory Analysis module not available.', 'ai-inventory-agent'));
        }
    }
    
    /**
     * Handle stock change events
     */
    public function on_stock_change($product) {
        $notifications = $this->module_manager->get_module('notifications');
        if ($notifications) {
            $notifications->check_stock_levels($product);
        }
    }
    
    /**
     * Handle completed order events
     */
    public function on_order_completed($order_id) {
        $inventory_analysis = $this->module_manager->get_module('inventory_analysis');
        if ($inventory_analysis) {
            $inventory_analysis->update_sales_data($order_id);
        }
    }
    
    /**
     * Run daily analysis
     */
    public function run_daily_analysis() {
        $demand_forecasting = $this->module_manager->get_module('demand_forecasting');
        if ($demand_forecasting) {
            $demand_forecasting->run_daily_analysis();
        }
    }
    
    /**
     * Generate weekly report
     */
    public function generate_weekly_report() {
        $reporting = $this->module_manager->get_module('reporting');
        if ($reporting) {
            $reporting->generate_weekly_report();
        }
    }
    
    /**
     * Generate monthly report
     */
    public function generate_monthly_report() {
        $reporting = $this->module_manager->get_module('reporting');
        if ($reporting) {
            $reporting->generate_monthly_report();
        }
    }
    
    /**
     * Get plugin settings
     * 
     * @param string $key Optional setting key
     * @return mixed
     */
    public function get_setting($key = null) {
        if ($key === null) {
            return $this->settings;
        }
        
        return $this->settings[$key] ?? null;
    }
    
    /**
     * Update plugin setting
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    public function update_setting($key, $value) {
        $this->settings[$key] = $value;
        return update_option('aia_settings', $this->settings);
    }
    
    /**
     * Get module manager instance
     * 
     * @return ModuleManager
     */
    public function get_module_manager() {
        return $this->module_manager;
    }
    
    /**
     * Get database instance
     * 
     * @return Database
     */
    public function get_database() {
        return $this->database;
    }
}