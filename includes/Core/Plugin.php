<?php

namespace AIA\Core;

use AIA\Core\ModuleManager;
use AIA\Core\Database;
use AIA\Core\MemoryManager;
use AIA\Core\SettingsManager;
use AIA\Core\ServiceContainer;
use AIA\Admin\AdminInterface;
use AIA\Utils\RateLimiter;

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
     * Plugin settings (deprecated - use SettingsManager)
     * 
     * @var array
     * @deprecated Use SettingsManager::get_settings() instead
     */
    private $settings;
    
    /**
     * Service container instance
     * 
     * @var ServiceContainer
     */
    private $container;
    
    /**
     * Get plugin instance (Singleton pattern)
     * 
     * @return Plugin
     */
    public static function get_instance() {
        if (self::$instance === null) {
            // Memory protection using centralized manager
            if (!MemoryManager::should_continue_loading()) {
                MemoryManager::log_usage('singleton_initialization_blocked');
                return null;
            }
            
            // Prevent infinite recursion
            static $initializing = false;
            if ($initializing) {
                error_log('AIA: Circular dependency detected in Plugin::get_instance()');
                return null;
            }
            
            $initializing = true;
            MemoryManager::log_usage('plugin_instance_creation');
            self::$instance = new self();
            $initializing = false;
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
        // Memory check using centralized manager
        if (!MemoryManager::can_initialize_modules()) {
            MemoryManager::log_usage('plugin_init_blocked');
            return;
        }
        
        try {
            // Initialize service container first
            $this->container = ServiceContainer::getInstance();
            
            // Load text domain for translations
            add_action('init', [$this, 'load_textdomain']);
            
            // Initialize services via container
            $this->initialize_services();

            // Initialize asset optimizer early
            if (class_exists('AIA\\Core\\AssetOptimizer')) {
                \AIA\Core\AssetOptimizer::init();
            }
            
            // Register WordPress hooks
            $this->register_hooks();
            
            // Initialize modules with memory check using centralized manager
            if (MemoryManager::can_initialize_modules()) {
                MemoryManager::log_usage('before_module_initialization');
                $this->init_modules();
                MemoryManager::log_usage('after_module_initialization');
            } else {
                MemoryManager::log_usage('module_initialization_skipped');
            }
            
        } catch (\Exception $e) {
            error_log('AIA: Plugin initialization error: ' . $e->getMessage());
            
            // Show admin notice if in admin
            if (is_admin()) {
                add_action('admin_notices', function() use ($e) {
                    echo '<div class="notice notice-error"><p>';
                    echo esc_html__('AI Inventory Agent initialization failed: ', 'ai-inventory-agent');
                    echo esc_html($e->getMessage());
                    echo '</p></div>';
                });
            }
        }
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
        // Use centralized settings manager
        $this->settings = SettingsManager::get_settings();
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            MemoryManager::log_usage('settings_loaded');
            error_log('AIA Plugin: Settings loaded via SettingsManager - AI Provider: ' . ($this->settings['ai_provider'] ?? 'not set') . ', API Key Length: ' . strlen($this->settings['api_key'] ?? ''));
        }
    }
    
    /**
     * Reload plugin settings (force refresh from database)
     */
    public function reload_settings() {
        // Force refresh from SettingsManager
        $this->settings = SettingsManager::get_settings(true);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            MemoryManager::log_usage('settings_reloaded');
            error_log('AIA Plugin: Settings reloaded via SettingsManager - AI Provider: ' . ($this->settings['ai_provider'] ?? 'not set') . ', API Key Length: ' . strlen($this->settings['api_key'] ?? ''));
        }
    }
    
    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        // Enqueue scripts and styles (handled by AdminInterface to avoid duplication)
        // add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        // add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        
        // WooCommerce Blocks integration
        add_action('woocommerce_blocks_loaded', [$this, 'register_checkout_block_integration']);
        
        // AJAX handlers
        add_action('wp_ajax_aia_chat', [$this, 'handle_chat_ajax']);
        add_action('wp_ajax_aia_get_inventory_data', [$this, 'handle_inventory_data_ajax']);
        
        // WooCommerce hooks - Compatible with HPOS
        add_action('woocommerce_product_set_stock', [$this, 'on_stock_change'], 10, 1);
        add_action('woocommerce_order_status_completed', [$this, 'on_order_completed'], 10, 1);
        
        // HPOS compatibility hooks
        add_action('woocommerce_new_order', [$this, 'on_new_order'], 10, 1);
        add_action('woocommerce_update_order', [$this, 'on_update_order'], 10, 1);
        
        // Block-based checkout compatibility
        add_action('woocommerce_store_api_checkout_order_processed', [$this, 'on_block_checkout_order_processed'], 10, 1);
        
        // Scheduled events
        add_action('aia_daily_analysis', [$this, 'run_daily_analysis']);
        add_action('aia_weekly_report', [$this, 'generate_weekly_report']);
        add_action('aia_monthly_report', [$this, 'generate_monthly_report']);
        
        // REST API routes for consistent backend across frontend/admin
        add_action('rest_api_init', function() {
            register_rest_route('aia/v1', '/inventory', [
                'methods' => 'GET',
                'callback' => function(\WP_REST_Request $request) {
                    if (!current_user_can('manage_woocommerce') && !current_user_can('view_woocommerce_reports')) {
                        return new \WP_Error('forbidden', __('Insufficient permissions.', 'ai-inventory-agent'), ['status' => 403]);
                    }
                    $inventory_analysis = $this->module_manager->get_module('inventory_analysis');
                    if (!$inventory_analysis) {
                        return new \WP_Error('not_available', __('Inventory Analysis module not available.', 'ai-inventory-agent'), ['status' => 500]);
                    }
                    return rest_ensure_response($inventory_analysis->get_inventory_summary());
                },
                'permission_callback' => '__return_true'
            ]);
            
            register_rest_route('aia/v1', '/chat', [
                'methods' => 'POST',
                'callback' => function(\WP_REST_Request $request) {
                    if (!current_user_can('manage_woocommerce') && !current_user_can('edit_shop_orders')) {
                        return new \WP_Error('forbidden', __('Insufficient permissions.', 'ai-inventory-agent'), ['status' => 403]);
                    }
                    $message = sanitize_text_field($request->get_param('message'));
                    $session_id = sanitize_text_field($request->get_param('session_id'));
                    if (empty($message)) {
                        return new \WP_Error('bad_request', __('Message cannot be empty.', 'ai-inventory-agent'), ['status' => 400]);
                    }
                    $this->ensure_chat_module_enabled();
                    $ai_chat = $this->module_manager->get_module('ai_chat');
                    if (!$ai_chat) {
                        $this->module_manager->init_module('ai_chat');
                        $ai_chat = $this->module_manager->get_module('ai_chat');
                    }
                    if (!$ai_chat) {
                        return new \WP_Error('not_available', __('AI Chat module not available.', 'ai-inventory-agent'), ['status' => 500]);
                    }
                    $response = $ai_chat->process_message($message, $session_id);
                    if (isset($response['success']) && $response['success']) {
                        return rest_ensure_response([
                            'response' => $response['response'] ?? '',
                            'session_id' => $response['session_id'] ?? $session_id,
                            'processing_time' => $response['processing_time'] ?? 0,
                        ]);
                    }
                    return new \WP_Error('chat_failed', $response['error'] ?? __('Failed to process message', 'ai-inventory-agent'), ['status' => 500]);
                },
                'permission_callback' => '__return_true'
            ]);
            
            register_rest_route('aia/v1', '/reports', [
                'methods' => 'GET',
                'callback' => function(\WP_REST_Request $request) {
                    if (!current_user_can('manage_woocommerce') && !current_user_can('view_woocommerce_reports')) {
                        return new \WP_Error('forbidden', __('Insufficient permissions.', 'ai-inventory-agent'), ['status' => 403]);
                    }
                    $reporting = $this->module_manager->get_module('reporting');
                    if (!$reporting) {
                        return new \WP_Error('not_available', __('Reporting module not available.', 'ai-inventory-agent'), ['status' => 500]);
                    }
                    // Provide a lightweight reports list/summary endpoint
                    if (method_exists($reporting, 'get_recent_reports')) {
                        return rest_ensure_response($reporting->get_recent_reports());
                    }
                    return rest_ensure_response(['message' => __('Reporting endpoint ready', 'ai-inventory-agent')]);
                },
                'permission_callback' => '__return_true'
            ]);
        });
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
     * Register checkout block integration
     */
    public function register_checkout_block_integration() {
        if (class_exists('Automattic\WooCommerce\Blocks\Integrations\IntegrationRegistry')) {
            add_action(
                'woocommerce_blocks_checkout_block_registration',
                function($integration_registry) {
                    // Register our checkout block integration if needed
                    // This ensures compatibility with WooCommerce 10.0+ block-based checkout
                }
            );
        }
    }
    
    /**
     * Ensure AI Chat module is enabled in settings
     */
    private function ensure_chat_module_enabled() {
        $settings = get_option('aia_settings', []);
        
        // If chat_enabled is not set or is false, enable it
        if (!isset($settings['chat_enabled']) || !$settings['chat_enabled']) {
            $settings['chat_enabled'] = true;
            update_option('aia_settings', $settings);
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('AIA: Enabled chat module in settings');
            }
        }
    }
    
    /**
     * Handle chat AJAX requests
     */
    public function handle_chat_ajax() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        // Enhanced permission check for WooCommerce 10.0+
        if (!current_user_can('manage_woocommerce') && !current_user_can('edit_shop_orders')) {
            wp_send_json_error(__('Insufficient permissions.', 'ai-inventory-agent'), 403);
            return;
        }
        
        // Apply rate limiting - 20 requests per minute
        RateLimiter::check_ajax_limit('ai_chat', 20, 60);
        
        $message = sanitize_text_field($_POST['message'] ?? '');
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        
        if (empty($message)) {
            wp_send_json_error(__('Message cannot be empty.', 'ai-inventory-agent'));
        }
        
        // Ensure chat module is enabled
        $this->ensure_chat_module_enabled();
        
        $ai_chat = $this->module_manager->get_module('ai_chat');
        
        // If module is still not available, try to initialize it
        if (!$ai_chat) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('AIA: Chat module not found, attempting to initialize...');
            }
            $this->module_manager->init_module('ai_chat');
            $ai_chat = $this->module_manager->get_module('ai_chat');
        }
        if ($ai_chat) {
            $response = $ai_chat->process_message($message, $session_id);
            
            // Ensure response format is correct
            if ($response && isset($response['success']) && $response['success']) {
                wp_send_json_success([
                    'response' => $response['response'] ?? 'AI response received',
                    'session_id' => $response['session_id'] ?? $session_id,
                    'processing_time' => $response['processing_time'] ?? 0
                ]);
            } else {
                wp_send_json_error([
                    'message' => $response['error'] ?? 'Failed to process message'
                ]);
            }
        } else {
            // Debug information
            $debug_info = [];
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $settings = get_option('aia_settings', []);
                $debug_info = [
                    'chat_enabled' => isset($settings['chat_enabled']) ? $settings['chat_enabled'] : 'not set',
                    'api_key_configured' => !empty($settings['api_key']),
                    'ai_provider' => $settings['ai_provider'] ?? 'not set',
                    'registered_modules' => array_keys($this->module_manager->get_registered_modules()),
                    'active_modules' => array_keys($this->module_manager->get_active_modules()),
                ];
                error_log('AIA Chat Debug: ' . json_encode($debug_info));
            }
            
            wp_send_json_error([
                'message' => __('AI Chat module not available.', 'ai-inventory-agent'),
                'debug' => $debug_info
            ]);
        }
    }
    
    /**
     * Handle inventory data AJAX requests
     */
    public function handle_inventory_data_ajax() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        // Enhanced permission check for WooCommerce 10.0+
        if (!current_user_can('manage_woocommerce') && !current_user_can('view_woocommerce_reports')) {
            wp_send_json_error(__('Insufficient permissions.', 'ai-inventory-agent'), 403);
            return;
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
     * Handle new order events (HPOS compatible)
     */
    public function on_new_order($order) {
        // Handle both order ID and order object for HPOS compatibility
        $order_id = is_numeric($order) ? $order : $order->get_id();
        
        $inventory_analysis = $this->module_manager->get_module('inventory_analysis');
        if ($inventory_analysis) {
            $inventory_analysis->track_new_order($order_id);
        }
    }
    
    /**
     * Handle order update events (HPOS compatible)
     */
    public function on_update_order($order) {
        // Handle both order ID and order object for HPOS compatibility
        $order_id = is_numeric($order) ? $order : $order->get_id();
        
        $inventory_analysis = $this->module_manager->get_module('inventory_analysis');
        if ($inventory_analysis) {
            $inventory_analysis->track_order_update($order_id);
        }
    }
    
    /**
     * Handle block-based checkout order processed events
     */
    public function on_block_checkout_order_processed($order) {
        $order_id = is_numeric($order) ? $order : $order->get_id();
        
        $inventory_analysis = $this->module_manager->get_module('inventory_analysis');
        if ($inventory_analysis) {
            $inventory_analysis->process_block_checkout_order($order_id);
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
            // Return all settings via SettingsManager
            return SettingsManager::get_settings();
        }
        
        // Use SettingsManager for consistent access
        return SettingsManager::get_setting($key);
    }
    
    /**
     * Update plugin setting
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    public function update_setting($key, $value) {
        // Delegate to SettingsManager to ensure cache consistency
        return SettingsManager::update_setting($key, $value);
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
    
    /**
     * Initialize services via service container
     */
    private function initialize_services() {
        // Get services from container
        $this->database = $this->container->get('database');
        $this->module_manager = $this->container->get('module_manager');
        
        // Load plugin settings
        $this->load_settings();
        
        // Initialize admin interface
        if (is_admin()) {
            $this->admin_interface = $this->container->get('admin_interface');
            if ($this->admin_interface) {
                $this->admin_interface->set_plugin_instance($this);
            }
        }
        
        MemoryManager::log_usage('services_initialized');
    }
    
    /**
     * Get service container instance
     * 
     * @return ServiceContainer
     */
    public function get_container() {
        return $this->container;
    }
    
    /**
     * Get service from container
     * 
     * @param string $service_name Service name
     * @return mixed Service instance
     */
    public function get_service($service_name) {
        return $this->container->get($service_name);
    }
    
    /**
     * Run system health check
     * 
     * @return array Health check results
     */
    public function run_health_check() {
        try {
            $validator = $this->container->get('integration_validator');
            return $validator->quick_health_check();
        } catch (\Exception $e) {
            return [
                'overall_healthy' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Run full integration validation
     * 
     * @return array Validation results
     */
    public function run_integration_validation() {
        try {
            $validator = $this->container->get('integration_validator');
            return $validator->validate_all();
        } catch (\Exception $e) {
            return [
                'summary' => [
                    'overall_status' => 'FAIL',
                    'error' => $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Get system performance statistics
     * 
     * @return array Performance statistics
     */
    public function get_performance_stats() {
        return [
            'memory' => MemoryManager::get_stats(),
            'queries' => $this->database->get_query_statistics(),
            'settings_cache' => [
                'enabled' => true,
                'expiration' => SettingsManager::CACHE_EXPIRATION
            ],
            'modules' => [
                'registered' => count($this->module_manager->get_registered_modules()),
                'active' => count($this->module_manager->get_active_modules())
            ]
        ];
    }
}