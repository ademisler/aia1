<?php

namespace AIA\Core;

class Plugin {
    private static ?Plugin $instance = null;

    public static function instance(): Plugin {
        if (!self::$instance) { self::$instance = new self(); }
        return self::$instance;
    }

    private function __construct() {
        $this->register_hooks();
    }

    private function register_hooks(): void {
        add_action('init', [$this, 'i18n']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('rest_api_init', [$this, 'register_rest']);
    }

    public function i18n(): void {
        load_plugin_textdomain('ai-inventory-agent', false, dirname(plugin_basename(AIA_PLUGIN_FILE)) . '/languages');
    }

    public function admin_menu(): void {
        add_menu_page(
            __('AI Inventory', 'ai-inventory-agent'),
            __('AI Inventory', 'ai-inventory-agent'),
            'manage_woocommerce',
            'aia',
            [$this, 'render_dashboard'],
            'dashicons-chart-area',
            56
        );
        add_submenu_page('aia', __('Dashboard','ai-inventory-agent'), __('Dashboard','ai-inventory-agent'), 'manage_woocommerce', 'aia', [$this,'render_dashboard']);
        add_submenu_page('aia', __('Chat','ai-inventory-agent'), __('Chat','ai-inventory-agent'), 'manage_woocommerce', 'aia-chat', [$this,'render_chat']);
        add_submenu_page('aia', __('Reports','ai-inventory-agent'), __('Reports','ai-inventory-agent'), 'manage_woocommerce', 'aia-reports', [$this,'render_reports']);
        add_submenu_page('aia', __('Settings','ai-inventory-agent'), __('Settings','ai-inventory-agent'), 'manage_options', 'aia-settings', [$this,'render_settings']);
    }

    public function enqueue_admin_assets($hook): void {
        if (strpos($hook, 'aia') === false) { return; }
        wp_enqueue_style('aia-admin', AIA_PLUGIN_URL . 'assets/css/admin.css', [], AIA_PLUGIN_VERSION);
        wp_enqueue_script('aia-admin', AIA_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], AIA_PLUGIN_VERSION, true);
        wp_localize_script('aia-admin', 'aia', [
            'ajax' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aia'),
            'rest' => esc_url_raw(rest_url('aia/v1/')),
        ]);
    }

    public function register_rest(): void {
        register_rest_route('aia/v1', '/inventory', [
            'methods'  => 'GET',
            'permission_callback' => function() { return current_user_can('manage_woocommerce') || current_user_can('view_woocommerce_reports'); },
            'callback' => [$this, 'rest_inventory'],
        ]);
        register_rest_route('aia/v1', '/chat', [
            'methods'  => 'POST',
            'permission_callback' => function() { return current_user_can('manage_woocommerce') || current_user_can('edit_shop_orders'); },
            'callback' => [$this, 'rest_chat'],
        ]);
        register_rest_route('aia/v1', '/reports', [
            'methods'  => 'GET',
            'permission_callback' => function() { return current_user_can('manage_woocommerce') || current_user_can('view_woocommerce_reports'); },
            'callback' => [$this, 'rest_reports'],
        ]);
    }

    public function rest_inventory(\WP_REST_Request $req) {
        return new \WP_REST_Response([
            'counts' => [ 'total_products' => 0, 'low_stock' => 0, 'out_of_stock' => 0 ],
            'updated_at' => current_time('mysql')
        ], 200);
    }

    public function rest_chat(\WP_REST_Request $req) {
        $message = sanitize_text_field($req->get_param('message'));
        if (!$message) { return new \WP_Error('invalid', __('Message required', 'ai-inventory-agent'), ['status'=>400]); }
        return new \WP_REST_Response([
            'response' => __('AI is not configured yet. Set your provider and API key in Settings.', 'ai-inventory-agent')
        ], 200);
    }

    public function rest_reports(\WP_REST_Request $req) {
        return new \WP_REST_Response([
            'reports' => [],
            'message' => __('Reporting service ready', 'ai-inventory-agent')
        ], 200);
    }

    // Renderers
    public function render_dashboard(): void { include AIA_PLUGIN_DIR . 'templates/admin/dashboard.php'; }
    public function render_chat(): void { include AIA_PLUGIN_DIR . 'templates/admin/chat.php'; }
    public function render_reports(): void { include AIA_PLUGIN_DIR . 'templates/admin/reports.php'; }
    public function render_settings(): void { include AIA_PLUGIN_DIR . 'templates/admin/settings.php'; }
}