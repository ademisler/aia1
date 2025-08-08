<?php

namespace AIA\Core;

use AIA\Settings\Settings;
use AIA\API\DummyProvider;
use AIA\API\AIProviderInterface;

class Plugin {
    private static ?Plugin $instance = null;
    private AIProviderInterface $provider;

    public static function instance(): Plugin {
        if (!self::$instance) { self::$instance = new self(); }
        return self::$instance;
    }

    private function __construct() {
        $this->provider = $this->makeProvider();
        $this->register_hooks();
    }

    private function makeProvider(): AIProviderInterface {
        $s = Settings::get();
        // For now always DummyProvider; extend with real providers later
        return new DummyProvider($s['api_key']);
    }

    private function register_hooks(): void {
        add_action('init', [$this, 'i18n']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('rest_api_init', [$this, 'register_rest']);
        add_action('wp_ajax_aia_save_settings', [$this, 'ajax_save_settings']);
    }

    public function i18n(): void {
        load_plugin_textdomain('ai-inventory-agent', false, dirname(plugin_basename(AIA_PLUGIN_FILE)) . '/languages');
    }

    public function admin_menu(): void {
        add_menu_page(__('AI Inventory', 'ai-inventory-agent'), __('AI Inventory', 'ai-inventory-agent'), 'manage_woocommerce', 'aia', [$this, 'render_dashboard'], 'dashicons-chart-area', 56);
        add_submenu_page('aia', __('Dashboard','ai-inventory-agent'), __('Dashboard','ai-inventory-agent'), 'manage_woocommerce', 'aia', [$this,'render_dashboard']);
        add_submenu_page('aia', __('Chat','ai-inventory-agent'), __('Chat','ai-inventory-agent'), 'manage_woocommerce', 'aia-chat', [$this,'render_chat']);
        add_submenu_page('aia', __('Reports','ai-inventory-agent'), __('Reports','ai-inventory-agent'), 'manage_woocommerce', 'aia-reports', [$this,'render_reports']);
        add_submenu_page('aia', __('Settings','ai-inventory-agent'), __('Settings','ai-inventory-agent'), 'manage_options', 'aia-settings', [$this,'render_settings']);
    }

    public function enqueue_admin_assets($hook): void {
        if (strpos($hook, 'aia') === false) { return; }
        wp_enqueue_style('aia-admin', AIA_PLUGIN_URL . 'assets/css/admin.css', [], AIA_PLUGIN_VERSION);
        // Chart.js for simple charts
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [], '4.4.0', true);
        // Lucide icons CDN
        wp_enqueue_script('lucide', 'https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js', [], '0.469.0', true);
        wp_enqueue_script('aia-admin', AIA_PLUGIN_URL . 'assets/js/admin.js', ['jquery','lucide','chartjs'], AIA_PLUGIN_VERSION, true);
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
        register_rest_route('aia/v1', '/inventory/low', [
            'methods'  => 'GET',
            'permission_callback' => function() { return current_user_can('manage_woocommerce') || current_user_can('view_woocommerce_reports'); },
            'callback' => [$this, 'rest_inventory_low'],
            'args' => [ 'limit' => [ 'default' => 10, 'sanitize_callback' => 'absint' ] ],
        ]);
        register_rest_route('aia/v1', '/provider/test', [
            'methods'  => 'GET',
            'permission_callback' => function() { return current_user_can('manage_options'); },
            'callback' => [$this, 'rest_provider_test'],
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
        register_rest_route('aia/v1', '/reports/summary.json', [
            'methods'  => 'GET',
            'permission_callback' => function() { return current_user_can('manage_woocommerce') || current_user_can('view_woocommerce_reports'); },
            'callback' => [$this, 'rest_report_summary_json'],
        ]);
        register_rest_route('aia/v1', '/reports/lowstock.csv', [
            'methods'  => 'GET',
            'permission_callback' => function() { return current_user_can('manage_woocommerce') || current_user_can('view_woocommerce_reports'); },
            'callback' => [$this, 'rest_report_lowstock_csv'],
        ]);
        register_rest_route('aia/v1', '/settings', [
            'methods'  => 'GET',
            'permission_callback' => function() { return current_user_can('manage_options'); },
            'callback' => function(){ return new \WP_REST_Response(Settings::get(), 200); },
        ]);
    }

    public function rest_inventory(\WP_REST_Request $req) { $inv=new Inventory(); return new \WP_REST_Response($inv->get_summary(), 200); }
    public function rest_inventory_low(\WP_REST_Request $req) { $inv=new Inventory(); $limit = absint($req->get_param('limit')); $page = absint($req->get_param('page')); $cat = $req->get_param('category'); return new \WP_REST_Response($inv->get_low_stock($limit?:10, $page?:1, $cat?:null), 200); }

    public function rest_provider_test(\WP_REST_Request $req) { $res=$this->provider->testConnection(); return new \WP_REST_Response($res, 200); }

    public function rest_chat(\WP_REST_Request $req) {
        $message = sanitize_text_field($req->get_param('message'));
        if (!$message) { return new \WP_Error('invalid', __('Message required', 'ai-inventory-agent'), ['status'=>400]); }
        $conv = [ ['role'=>'user','content'=>$message] ];
        $res = $this->provider->chat($conv);
        if (!($res['success'] ?? false)) { return new \WP_Error('chat_failed', __('AI request failed','ai-inventory-agent'), ['status'=>500]); }
        return new \WP_REST_Response(['response' => $res['response']], 200);
    }

    public function rest_reports(\WP_REST_Request $req) { return new \WP_REST_Response(['reports' => [], 'message' => __('Reporting service ready', 'ai-inventory-agent')], 200); }

    public function rest_report_summary_json(\WP_REST_Request $req) {
        $inv=new Inventory();
        $data = [ 'summary'=>$inv->get_summary(), 'generated_at'=> current_time('mysql') ];
        return new \WP_REST_Response($data, 200, [ 'Content-Type' => 'application/json; charset=utf-8' ]);
    }

    public function rest_report_lowstock_csv(\WP_REST_Request $req) {
        $inv=new Inventory();
        $rows = $inv->get_low_stock(100);
        $csv = "id,name,stock,edit_url\n";
        foreach ($rows as $r) {
            $csv .= sprintf("%d,\"%s\",%d,%s\n", (int)$r['id'], str_replace('"','""',$r['name']??''), (int)($r['stock']??0), $r['edit_url']??'');
        }
        return new \WP_REST_Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="low-stock.csv"'
        ]);
    }

    public function ajax_save_settings(): void {
        check_ajax_referer('aia', 'nonce');
        if (!current_user_can('manage_options')) { wp_send_json_error(['message'=>'forbidden'], 403); }
        $data = [
            'ai_provider' => $_POST['ai_provider'] ?? null,
            'api_key' => $_POST['api_key'] ?? null,
            'low_stock_threshold' => $_POST['low_stock_threshold'] ?? null,
        ];
        Settings::update(array_filter($data, fn($v)=> $v!==null));
        // refresh provider
        $this->provider = $this->makeProvider();
        wp_send_json_success(Settings::get());
    }

    // Renderers
    public function render_dashboard(): void { include AIA_PLUGIN_DIR . 'templates/admin/dashboard.php'; }
    public function render_chat(): void { include AIA_PLUGIN_DIR . 'templates/admin/chat.php'; }
    public function render_reports(): void { include AIA_PLUGIN_DIR . 'templates/admin/reports.php'; }
    public function render_settings(): void { include AIA_PLUGIN_DIR . 'templates/admin/settings.php'; }
}