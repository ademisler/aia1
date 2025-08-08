<?php

namespace AIA\Admin;

use AIA\Core\Plugin;

/**
 * Admin Interface Class
 * 
 * Handles WordPress admin interface, menus, and pages
 */
class AdminInterface {
    
    /**
     * Plugin instance
     * 
     * @var Plugin
     */
    private $plugin;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Avoid circular dependency - plugin instance will be set during init
        $this->init();
    }
    
    /**
     * Initialize admin interface
     */
    private function init() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_notices', [$this, 'show_admin_notices']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widgets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // AJAX handlers
        add_action('wp_ajax_aia_save_settings', [$this, 'handle_save_settings']);
        add_action('wp_ajax_aia_test_api_connection', [$this, 'handle_test_api_connection']);
        add_action('wp_ajax_aia_dismiss_notice', [$this, 'handle_dismiss_notice']);
    }
    
    /**
     * Set plugin instance (called after plugin initialization)
     */
    public function set_plugin_instance($plugin) {
        $this->plugin = $plugin;
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __('AI Inventory Agent', 'ai-inventory-agent'),
            __('AI Inventory', 'ai-inventory-agent'),
            'manage_woocommerce',
            'ai-inventory-agent',
            [$this, 'render_dashboard_page'],
            'dashicons-chart-area',
            56
        );
        
        // Dashboard submenu
        add_submenu_page(
            'ai-inventory-agent',
            __('Dashboard', 'ai-inventory-agent'),
            __('Dashboard', 'ai-inventory-agent'),
            'manage_woocommerce',
            'ai-inventory-agent',
            [$this, 'render_dashboard_page']
        );
        
        // AI Chat submenu
        add_submenu_page(
            'ai-inventory-agent',
            __('AI Chat', 'ai-inventory-agent'),
            __('AI Chat', 'ai-inventory-agent'),
            'manage_woocommerce',
            'aia-chat',
            [$this, 'render_chat_page']
        );
        
        // Inventory Analysis submenu
        add_submenu_page(
            'ai-inventory-agent',
            __('Inventory Analysis', 'ai-inventory-agent'),
            __('Analysis', 'ai-inventory-agent'),
            'manage_woocommerce',
            'aia-analysis',
            [$this, 'render_analysis_page']
        );
        
        // Alerts submenu
        add_submenu_page(
            'ai-inventory-agent',
            __('Stock Alerts', 'ai-inventory-agent'),
            __('Alerts', 'ai-inventory-agent'),
            'manage_woocommerce',
            'aia-alerts',
            [$this, 'render_alerts_page']
        );
        
        // Reports submenu
        add_submenu_page(
            'ai-inventory-agent',
            __('Reports', 'ai-inventory-agent'),
            __('Reports', 'ai-inventory-agent'),
            'manage_woocommerce',
            'aia-reports',
            [$this, 'render_reports_page']
        );
        
        // Settings submenu
        add_submenu_page(
            'ai-inventory-agent',
            __('Settings', 'ai-inventory-agent'),
            __('Settings', 'ai-inventory-agent'),
            'manage_options',
            'aia-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('aia_settings_group', 'aia_settings', [
            'sanitize_callback' => [$this, 'sanitize_settings']
        ]);
        
        // General Settings Section
        add_settings_section(
            'aia_general_section',
            __('General Settings', 'ai-inventory-agent'),
            [$this, 'render_general_section'],
            'ai-inventory-agent'
        );
        
        // AI Provider Settings
        add_settings_field(
            'ai_provider',
            __('AI Provider', 'ai-inventory-agent'),
            [$this, 'render_ai_provider_field'],
            'ai-inventory-agent',
            'aia_general_section'
        );
        
        add_settings_field(
            'api_key',
            __('API Key', 'ai-inventory-agent'),
            [$this, 'render_api_key_field'],
            'ai-inventory-agent',
            'aia_general_section'
        );
        
        add_settings_field(
            'system_prompt',
            __('System Prompt', 'ai-inventory-agent'),
            [$this, 'render_system_prompt_field'],
            'ai-inventory-agent',
            'aia_general_section'
        );
        
        // Inventory Settings Section
        add_settings_section(
            'aia_inventory_section',
            __('Inventory Settings', 'ai-inventory-agent'),
            [$this, 'render_inventory_section'],
            'ai-inventory-agent'
        );
        
        add_settings_field(
            'low_stock_threshold',
            __('Low Stock Threshold', 'ai-inventory-agent'),
            [$this, 'render_low_stock_threshold_field'],
            'ai-inventory-agent',
            'aia_inventory_section'
        );
        
        add_settings_field(
            'critical_stock_threshold',
            __('Critical Stock Threshold', 'ai-inventory-agent'),
            [$this, 'render_critical_stock_threshold_field'],
            'ai-inventory-agent',
            'aia_inventory_section'
        );
        
        // Notification Settings Section
        add_settings_section(
            'aia_notification_section',
            __('Notification Settings', 'ai-inventory-agent'),
            [$this, 'render_notification_section'],
            'ai-inventory-agent'
        );
        
        add_settings_field(
            'notification_email',
            __('Notification Email', 'ai-inventory-agent'),
            [$this, 'render_notification_email_field'],
            'ai-inventory-agent',
            'aia_notification_section'
        );
        
        add_settings_field(
            'email_notifications',
            __('Email Notifications', 'ai-inventory-agent'),
            [$this, 'render_email_notifications_field'],
            'ai-inventory-agent',
            'aia_notification_section'
        );
    }
    
    /**
     * Sanitize settings
     * 
     * @param array $input Input settings
     * @return array Sanitized settings
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        
        $sanitized['ai_provider'] = sanitize_text_field($input['ai_provider'] ?? 'openai');
        $sanitized['api_key'] = sanitize_text_field($input['api_key'] ?? '');
        $sanitized['system_prompt'] = sanitize_textarea_field($input['system_prompt'] ?? '');
        
        $sanitized['low_stock_threshold'] = absint($input['low_stock_threshold'] ?? 5);
        $sanitized['critical_stock_threshold'] = absint($input['critical_stock_threshold'] ?? 1);
        
        $sanitized['notification_email'] = sanitize_email($input['notification_email'] ?? get_option('admin_email'));
        $sanitized['email_notifications'] = !empty($input['email_notifications']);
        $sanitized['dashboard_notifications'] = !empty($input['dashboard_notifications']);
        
        $sanitized['chat_enabled'] = !empty($input['chat_enabled']);
        $sanitized['forecasting_enabled'] = !empty($input['forecasting_enabled']);
        $sanitized['notifications_enabled'] = !empty($input['notifications_enabled']);
        $sanitized['reports_enabled'] = !empty($input['reports_enabled']);
        
        $sanitized['report_frequency'] = sanitize_text_field($input['report_frequency'] ?? 'weekly');
        
        return $sanitized;
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        $inventory_analysis = $this->plugin->get_module_manager()->get_module('inventory_analysis');
        $summary = $inventory_analysis ? $inventory_analysis->get_inventory_summary() : [];
        
        include AIA_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }
    
    /**
     * Render chat page
     */
    public function render_chat_page() {
        include AIA_PLUGIN_DIR . 'templates/admin/chat.php';
    }
    
    /**
     * Render analysis page
     */
    public function render_analysis_page() {
        include AIA_PLUGIN_DIR . 'templates/admin/analysis.php';
    }
    
    /**
     * Render alerts page
     */
    public function render_alerts_page() {
        include AIA_PLUGIN_DIR . 'templates/admin/alerts.php';
    }
    
    /**
     * Render reports page
     */
    public function render_reports_page() {
        include AIA_PLUGIN_DIR . 'templates/admin/reports.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        include AIA_PLUGIN_DIR . 'templates/admin/settings.php';
    }
    
    /**
     * Render general settings section
     */
    public function render_general_section() {
        echo '<p>' . __('Configure the AI provider and general plugin settings.', 'ai-inventory-agent') . '</p>';
    }
    
    /**
     * Render inventory settings section
     */
    public function render_inventory_section() {
        echo '<p>' . __('Configure inventory thresholds and analysis settings.', 'ai-inventory-agent') . '</p>';
    }
    
    /**
     * Render notification settings section
     */
    public function render_notification_section() {
        echo '<p>' . __('Configure how and when you receive notifications.', 'ai-inventory-agent') . '</p>';
    }
    
    /**
     * Render AI provider field
     */
    public function render_ai_provider_field() {
        $settings = $this->plugin->get_setting();
        $current_provider = $settings['ai_provider'] ?? 'openai';
        
        echo '<select name="aia_settings[ai_provider]" id="settings_ai_provider">';
        echo '<option value="openai"' . selected($current_provider, 'openai', false) . '>OpenAI (GPT)</option>';
        echo '<option value="gemini"' . selected($current_provider, 'gemini', false) . '>Google Gemini</option>';
        echo '</select>';
        echo '<p class="description">' . __('Choose your AI provider for chat and analysis features.', 'ai-inventory-agent') . '</p>';
    }
    
    /**
     * Render API key field
     */
    public function render_api_key_field() {
        $settings = $this->plugin->get_setting();
        $api_key = $settings['api_key'] ?? '';
        
        echo '<input type="password" name="aia_settings[api_key]" id="settings_api_key" value="' . esc_attr($api_key) . '" class="regular-text" />';
        echo '<button type="button" id="settings_test_api_connection" class="button button-secondary" style="margin-left: 10px;">' . __('Test Connection', 'ai-inventory-agent') . '</button>';
        echo '<p class="description">' . __('Enter your AI provider API key. This is required for AI features to work.', 'ai-inventory-agent') . '</p>';
        echo '<div id="api_test_result" style="margin-top: 10px;"></div>';
    }
    
    /**
     * Render system prompt field
     */
    public function render_system_prompt_field() {
        $settings = $this->plugin->get_setting();
        $system_prompt = $settings['system_prompt'] ?? 'You are an AI inventory management assistant. Help users manage their WooCommerce store inventory efficiently.';
        
        echo '<textarea name="aia_settings[system_prompt]" id="settings_system_prompt" rows="4" cols="50" class="large-text">' . esc_textarea($system_prompt) . '</textarea>';
        echo '<p class="description">' . __('This prompt defines how the AI assistant behaves. You can customize it to match your store\'s needs.', 'ai-inventory-agent') . '</p>';
    }
    
    /**
     * Render low stock threshold field
     */
    public function render_low_stock_threshold_field() {
        $settings = $this->plugin->get_setting();
        $threshold = $settings['low_stock_threshold'] ?? 5;
        
        echo '<input type="number" name="aia_settings[low_stock_threshold]" id="settings_low_stock_threshold" value="' . esc_attr($threshold) . '" min="0" class="small-text" />';
        echo '<p class="description">' . __('Products with stock at or below this level will be flagged as low stock.', 'ai-inventory-agent') . '</p>';
    }
    
    /**
     * Render critical stock threshold field
     */
    public function render_critical_stock_threshold_field() {
        $settings = $this->plugin->get_setting();
        $threshold = $settings['critical_stock_threshold'] ?? 1;
        
        echo '<input type="number" name="aia_settings[critical_stock_threshold]" id="settings_critical_stock_threshold" value="' . esc_attr($threshold) . '" min="0" class="small-text" />';
        echo '<p class="description">' . __('Products with stock at or below this level will be flagged as out of stock.', 'ai-inventory-agent') . '</p>';
    }
    
    /**
     * Render notification email field
     */
    public function render_notification_email_field() {
        $settings = $this->plugin->get_setting();
        $email = $settings['notification_email'] ?? get_option('admin_email');
        
        echo '<input type="email" name="aia_settings[notification_email]" id="settings_notification_email" value="' . esc_attr($email) . '" class="regular-text" />';
        echo '<p class="description">' . __('Email address where notifications will be sent.', 'ai-inventory-agent') . '</p>';
    }
    
    /**
     * Render email notifications field
     */
    public function render_email_notifications_field() {
        $settings = $this->plugin->get_setting();
        $enabled = $settings['email_notifications'] ?? true;
        
        echo '<label><input type="checkbox" name="aia_settings[email_notifications]" value="1"' . checked($enabled, true, false) . ' /> ';
        echo __('Send email notifications for stock alerts', 'ai-inventory-agent') . '</label>';
    }
    
    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        // Check if API key is configured
        $api_key = $this->plugin->get_setting('api_key');
        if (empty($api_key) && !get_user_meta(get_current_user_id(), 'aia_api_key_notice_dismissed', true)) {
            echo '<div class="notice notice-warning is-dismissible" data-notice="api_key">';
            echo '<p><strong>' . __('AI Inventory Agent:', 'ai-inventory-agent') . '</strong> ';
            echo sprintf(
                __('Please configure your AI provider API key in the <a href="%s">settings</a> to enable AI features.', 'ai-inventory-agent'),
                admin_url('admin.php?page=aia-settings')
            );
            echo '</p>';
            echo '</div>';
        }
        
        // Check for WooCommerce
        if (!$this->is_woocommerce_active()) {
            echo '<div class="notice notice-error">';
            echo '<p><strong>' . __('AI Inventory Agent:', 'ai-inventory-agent') . '</strong> ';
            echo __('WooCommerce is required for this plugin to work properly.', 'ai-inventory-agent');
            echo '</p>';
            echo '</div>';
        }
        
        // Show success message after settings save
        if (isset($_GET['settings-updated']) && sanitize_text_field($_GET['settings-updated']) === 'true') {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . __('Settings saved successfully!', 'ai-inventory-agent') . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Add dashboard widgets
     */
    public function add_dashboard_widgets() {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }
        
        wp_add_dashboard_widget(
            'aia_inventory_summary',
            __('Inventory Summary - AI Agent', 'ai-inventory-agent'),
            [$this, 'render_dashboard_widget']
        );
    }
    
    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        $inventory_analysis = $this->plugin->get_module_manager()->get_module('inventory_analysis');
        
        if (!$inventory_analysis) {
            echo '<p>' . __('Inventory analysis module not available.', 'ai-inventory-agent') . '</p>';
            return;
        }
        
        $summary = $inventory_analysis->get_inventory_summary();
        
        echo '<div class="aia-dashboard-widget">';
        
        // Quick stats
        echo '<div class="aia-quick-stats">';
        echo '<div class="aia-stat">';
        echo '<span class="aia-stat-number">' . number_format($summary['counts']['total_products'] ?? 0) . '</span>';
        echo '<span class="aia-stat-label">' . __('Total Products', 'ai-inventory-agent') . '</span>';
        echo '</div>';
        
        echo '<div class="aia-stat">';
        echo '<span class="aia-stat-number">' . number_format($summary['counts']['low_stock'] ?? 0) . '</span>';
        echo '<span class="aia-stat-label">' . __('Low Stock', 'ai-inventory-agent') . '</span>';
        echo '</div>';
        
        echo '<div class="aia-stat">';
        echo '<span class="aia-stat-number">' . number_format($summary['counts']['out_of_stock'] ?? 0) . '</span>';
        echo '<span class="aia-stat-label">' . __('Out of Stock', 'ai-inventory-agent') . '</span>';
        echo '</div>';
        echo '</div>';
        
        // Quick actions
        echo '<div class="aia-quick-actions">';
        echo '<a href="' . admin_url('admin.php?page=ai-inventory-agent') . '" class="button button-primary">' . __('View Dashboard', 'ai-inventory-agent') . '</a>';
        echo '<a href="' . admin_url('admin.php?page=aia-chat') . '" class="button button-secondary">' . __('AI Chat', 'ai-inventory-agent') . '</a>';
        echo '</div>';
        
        echo '</div>';
    }
    
    /**
     * Handle save settings AJAX request
     */
    public function handle_save_settings() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        // Debug logging - show all POST data
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('AIA Settings Save: All POST data: ' . print_r($_POST, true));
        }
        
        // Handle FormData - directly get aia_settings array from POST
        $settings_data = $_POST['aia_settings'] ?? [];
        
        // If still empty, try to extract from individual fields
        if (empty($settings_data)) {
            $settings_data = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'aia_settings[') === 0) {
                    $setting_key = str_replace(['aia_settings[', ']'], '', $key);
                    $settings_data[$setting_key] = $value;
                }
            }
        }
        
        if (empty($settings_data)) {
            wp_send_json_error(__('No settings data received. Please check form data.', 'ai-inventory-agent'));
        }
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('AIA Settings Save: Parsed settings data: ' . print_r($settings_data, true));
        }
        
        $sanitized_settings = $this->sanitize_settings($settings_data);
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('AIA Settings Save: Sanitized settings: ' . print_r($sanitized_settings, true));
        }
        
        // Update settings
        $updated_count = 0;
        foreach ($sanitized_settings as $key => $value) {
            if ($this->plugin->update_setting($key, $value)) {
                $updated_count++;
            }
        }
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("AIA Settings Save: Updated {$updated_count} settings");
        }
        
        // Force reload settings to ensure AI provider gets updated
        $this->plugin->reload_settings();
        
        // Verify settings were saved
        $saved_api_key = $this->plugin->get_setting('api_key');
        $saved_provider = $this->plugin->get_setting('ai_provider');
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("AIA Settings Save: Verification - Provider: {$saved_provider}, API Key Length: " . strlen($saved_api_key ?: ''));
        }
        
        // Try to reinitialize AI Chat module if it exists
        $module_manager = $this->plugin->get_module_manager();
        if ($module_manager) {
            $ai_chat = $module_manager->get_module('ai_chat');
            if ($ai_chat && method_exists($ai_chat, 'init')) {
                $ai_chat->init();
            }
        }
        
        wp_send_json_success([
            'message' => __('Settings saved successfully!', 'ai-inventory-agent'),
            'updated_count' => $updated_count,
            'provider' => $saved_provider,
            'api_key_length' => strlen($saved_api_key ?: '')
        ]);
    }
    
    /**
     * Handle test API connection AJAX request
     */
    public function handle_test_api_connection() {
        try {
            check_ajax_referer('aia_ajax_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_error(__('Insufficient permissions.', 'ai-inventory-agent'));
                return;
            }
            
            $provider = sanitize_text_field($_POST['provider'] ?? '');
            $api_key = sanitize_text_field($_POST['api_key'] ?? '');
            
            if (empty($provider) || empty($api_key)) {
                wp_send_json_error(__('Provider and API key are required.', 'ai-inventory-agent'));
                return;
            }
            
            // Debug logging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("AIA API Test: Testing {$provider} connection with key length: " . strlen($api_key));
            }
            
            // Test the connection
            switch ($provider) {
                case 'openai':
                    $provider_instance = new \AIA\API\OpenAIProvider($api_key);
                    break;
                case 'gemini':
                    $provider_instance = new \AIA\API\GeminiProvider($api_key);
                    break;
                default:
                    wp_send_json_error(__('Unknown provider.', 'ai-inventory-agent'));
                    return;
            }
            
            $result = $provider_instance->test_connection();
            
            // Debug logging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("AIA API Test Result: " . json_encode($result));
            }
            
            if ($result['success']) {
                wp_send_json_success([
                    'message' => __('Connection successful!', 'ai-inventory-agent'),
                    'details' => $result
                ]);
            } else {
                wp_send_json_error([
                    'message' => $result['message'],
                    'debug' => $result['debug'] ?? []
                ]);
            }
            
        } catch (\Exception $e) {
            error_log('AIA API Test Exception: ' . $e->getMessage());
            wp_send_json_error([
                'message' => __('Connection test failed: ', 'ai-inventory-agent') . $e->getMessage(),
                'error_type' => 'exception'
            ]);
        }
    }
    
    /**
     * Handle dismiss notice AJAX request
     */
    public function handle_dismiss_notice() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        $notice = sanitize_text_field($_POST['notice'] ?? '');
        
        if ($notice === 'api_key') {
            update_user_meta(get_current_user_id(), 'aia_api_key_notice_dismissed', true);
        }
        
        wp_send_json_success();
    }
    
    /**
     * Get admin page URL
     * 
     * @param string $page Page slug
     * @return string Admin URL
     */
    public function get_admin_url($page = 'ai-inventory-agent') {
        return admin_url('admin.php?page=' . $page);
    }
    
    /**
     * Check if current screen is plugin admin page
     * 
     * @return bool
     */
    public function is_plugin_admin_page() {
        $screen = get_current_screen();
        return $screen && strpos($screen->id, 'ai-inventory-agent') !== false;
    }
    
    /**
     * Check if WooCommerce is active
     * 
     * @return bool
     */
    private function is_woocommerce_active() {
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
     * Enqueue admin assets
     * 
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'ai-inventory-agent') === false && 
            strpos($hook, 'aia-') === false) {
            return;
        }
        
        // Check if asset optimization is enabled
        $settings = get_option('aia_settings', []);
        $use_optimization = !empty($settings['enable_asset_optimization']) && !defined('WP_DEBUG');
        
        if ($use_optimization && class_exists('AIA\Core\AssetOptimizer')) {
            // Use optimized assets
            \AIA\Core\AssetOptimizer::optimize_admin_assets();
            return;
        }
        
        // Fallback: Use combined CSS file instead of multiple files
        wp_enqueue_style(
            'aia-combined-css',
            AIA_PLUGIN_URL . 'assets/css/aia-combined.css',
            [],
            AIA_PLUGIN_VERSION
        );
        
        // Use optimized JavaScript
        wp_enqueue_script(
            'aia-optimized-js',
            AIA_PLUGIN_URL . 'assets/js/aia-optimized.js',
            ['jquery'],
            AIA_PLUGIN_VERSION,
            true
        );
        
        // Chart.js only when needed
        if (in_array($hook, ['toplevel_page_ai-inventory-agent', 'ai-inventory-agent_page_aia-analysis', 'ai-inventory-agent_page_aia-reports'])) {
            wp_enqueue_script(
                'chartjs',
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
                [],
                '4.4.0',
                true
            );
        }
        
        // Localize script data
        wp_localize_script('aia-optimized-js', 'aia_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aia_ajax_nonce'),
            'plugin_url' => AIA_PLUGIN_URL,
            'strings' => [
                'loading' => __('Loading...', 'ai-inventory-agent'),
                'error' => __('An error occurred. Please try again.', 'ai-inventory-agent'),
                'success' => __('Operation completed successfully.', 'ai-inventory-agent'),
                'confirm' => __('Are you sure?', 'ai-inventory-agent'),
                'cancel' => __('Cancel', 'ai-inventory-agent'),
                'save' => __('Save', 'ai-inventory-agent'),
            ],
            'settings' => [
                'optimization_enabled' => $use_optimization,
                'debug_mode' => defined('WP_DEBUG') && WP_DEBUG
            ]
        ]);
    }
    
    /**
     * Legacy method - kept for backwards compatibility
     * Now redirects to optimized asset loading
     */
    private function enqueue_legacy_assets() {
        // This method is now deprecated
        // Assets are loaded via the optimized system above
    }
    
    /**
     * Add asset optimization setting to SettingsManager defaults
     */
    public function add_optimization_setting() {
        // Add asset optimization setting to defaults
        $settings = get_option('aia_settings', []);
        if (!isset($settings['enable_asset_optimization'])) {
            $settings['enable_asset_optimization'] = true;
            update_option('aia_settings', $settings);
        }
    }
    
    /**
     * Get asset optimization statistics
     */
    public function get_asset_statistics() {
        if (class_exists('AIA\Core\AssetOptimizer')) {
            return \AIA\Core\AssetOptimizer::get_statistics();
        }
        
        return [
            'enabled' => false,
            'message' => 'Asset optimization not available'
        ];
    }
}