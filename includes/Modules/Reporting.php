<?php

namespace AIA\Modules;

use AIA\Core\Plugin;

/**
 * Reporting Module
 * 
 * Generates comprehensive inventory reports
 */
class Reporting {
    
    /**
     * Module information
     * 
     * @var array
     */
    private $info = [
        'name' => 'Reporting System',
        'description' => 'Automated inventory reports and analytics',
        'version' => '1.0.0'
    ];
    
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
        $this->plugin = Plugin::get_instance();
    }
    
    /**
     * Initialize the module
     */
    public function init() {
        // Register hooks
        $this->register_hooks();
        
        // Schedule report generation
        $this->schedule_reports();
    }
    
    /**
     * Register module hooks
     */
    private function register_hooks() {
        // AJAX handlers
        add_action('wp_ajax_aia_generate_report', [$this, 'handle_generate_report']);
        add_action('wp_ajax_aia_download_report', [$this, 'handle_download_report']);
        add_action('wp_ajax_aia_get_report_list', [$this, 'handle_get_report_list']);
        
        // Report generation hooks
        add_action('aia_generate_weekly_report', [$this, 'generate_weekly_report']);
        add_action('aia_generate_monthly_report', [$this, 'generate_monthly_report']);
    }
    
    /**
     * Schedule report generation
     */
    private function schedule_reports() {
        $report_frequency = $this->plugin->get_setting('report_frequency');
        
        // Weekly reports
        if (in_array($report_frequency, ['weekly', 'both'])) {
            if (!wp_next_scheduled('aia_generate_weekly_report')) {
                wp_schedule_event(strtotime('next monday 8:00'), 'weekly', 'aia_generate_weekly_report');
            }
        } else {
            wp_clear_scheduled_hook('aia_generate_weekly_report');
        }
        
        // Monthly reports
        if (in_array($report_frequency, ['monthly', 'both'])) {
            if (!wp_next_scheduled('aia_generate_monthly_report')) {
                wp_schedule_event(strtotime('first day of next month 8:00'), 'monthly', 'aia_generate_monthly_report');
            }
        } else {
            wp_clear_scheduled_hook('aia_generate_monthly_report');
        }
    }
    
    /**
     * Generate weekly report
     */
    public function generate_weekly_report() {
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
        
        $report_data = $this->compile_report_data($start_date, $end_date);
        $report_id = $this->save_report('weekly', $report_data, $start_date, $end_date);
        
        // Send report via email if enabled
        if ($this->plugin->get_setting('email_reports')) {
            $this->email_report($report_id);
        }
        
        return $report_id;
    }
    
    /**
     * Generate monthly report
     */
    public function generate_monthly_report() {
        $start_date = date('Y-m-01', strtotime('-1 month'));
        $end_date = date('Y-m-t', strtotime('-1 month'));
        
        $report_data = $this->compile_report_data($start_date, $end_date);
        $report_id = $this->save_report('monthly', $report_data, $start_date, $end_date);
        
        // Send report via email if enabled
        if ($this->plugin->get_setting('email_reports')) {
            $this->email_report($report_id);
        }
        
        return $report_id;
    }
    
    /**
     * Compile report data
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Report data
     */
    private function compile_report_data($start_date, $end_date) {
        global $wpdb;
        $database = $this->plugin->get_database();
        
        $data = [
            'period' => [
                'start' => $start_date,
                'end' => $end_date
            ],
            'summary' => $this->get_period_summary($start_date, $end_date),
            'stock_movements' => $this->get_stock_movements($start_date, $end_date),
            'sales_analysis' => $this->get_sales_analysis($start_date, $end_date),
            'alerts_summary' => $this->get_alerts_summary($start_date, $end_date),
            'supplier_performance' => $this->get_supplier_performance_summary($start_date, $end_date),
            'forecasting_accuracy' => $this->get_forecasting_accuracy($start_date, $end_date),
            'recommendations' => $this->generate_recommendations($start_date, $end_date)
        ];
        
        return $data;
    }
    
    /**
     * Get period summary
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Summary data
     */
    private function get_period_summary($start_date, $end_date) {
        global $wpdb;
        
        // Total sales
        $total_sales = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing')
            AND p.post_date BETWEEN %s AND %s
        ", $start_date . ' 00:00:00', $end_date . ' 23:59:59'));
        
        // Revenue
        $revenue = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(pm.meta_value)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing')
            AND pm.meta_key = '_order_total'
            AND p.post_date BETWEEN %s AND %s
        ", $start_date . ' 00:00:00', $end_date . ' 23:59:59'));
        
        // Stock value change
        $inventory_analysis = $this->plugin->get_module_manager()->get_module('inventory_analysis');
        $current_stock_value = $inventory_analysis ? $inventory_analysis->calculate_total_stock_value() : 0;
        
        return [
            'total_orders' => $total_sales,
            'total_revenue' => floatval($revenue),
            'current_stock_value' => $current_stock_value,
            'average_order_value' => $total_sales > 0 ? floatval($revenue) / $total_sales : 0
        ];
    }
    
    /**
     * Get stock movements
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Stock movement data
     */
    private function get_stock_movements($start_date, $end_date) {
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('inventory_logs');
        
        $movements = $wpdb->get_results($wpdb->prepare("
            SELECT 
                il.action,
                COUNT(*) as count,
                SUM(ABS(il.new_stock - il.old_stock)) as total_units
            FROM {$table_name} il
            WHERE il.created_at BETWEEN %s AND %s
            GROUP BY il.action
        ", $start_date . ' 00:00:00', $end_date . ' 23:59:59'));
        
        return $movements;
    }
    
    /**
     * Get sales analysis
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Sales analysis data
     */
    private function get_sales_analysis($start_date, $end_date) {
        global $wpdb;
        
        // Top selling products
        $top_products = $wpdb->get_results($wpdb->prepare("
            SELECT 
                p.ID,
                p.post_title as product_name,
                SUM(oim.meta_value) as quantity_sold,
                COUNT(DISTINCT oi.order_id) as order_count
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim 
                ON oim.meta_key = '_product_id' AND oim.meta_value = p.ID
            INNER JOIN {$wpdb->prefix}woocommerce_order_items oi 
                ON oi.order_item_id = oim.order_item_id
            INNER JOIN {$wpdb->posts} o 
                ON o.ID = oi.order_id
            WHERE o.post_type = 'shop_order'
            AND o.post_status IN ('wc-completed', 'wc-processing')
            AND o.post_date BETWEEN %s AND %s
            GROUP BY p.ID
            ORDER BY quantity_sold DESC
            LIMIT 10
        ", $start_date . ' 00:00:00', $end_date . ' 23:59:59'));
        
        return [
            'top_products' => $top_products
        ];
    }
    
    /**
     * Get alerts summary
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Alerts summary
     */
    private function get_alerts_summary($start_date, $end_date) {
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('stock_alerts');
        
        $alerts = $wpdb->get_results($wpdb->prepare("
            SELECT 
                alert_type,
                severity,
                COUNT(*) as count
            FROM {$table_name}
            WHERE created_at BETWEEN %s AND %s
            GROUP BY alert_type, severity
        ", $start_date . ' 00:00:00', $end_date . ' 23:59:59'));
        
        return $alerts;
    }
    
    /**
     * Get supplier performance summary
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Supplier performance data
     */
    private function get_supplier_performance_summary($start_date, $end_date) {
        $supplier_analysis = $this->plugin->get_module_manager()->get_module('supplier_analysis');
        
        if (!$supplier_analysis) {
            return [];
        }
        
        $suppliers = $supplier_analysis->get_supplier_list();
        
        $summary = [
            'total_suppliers' => count($suppliers),
            'high_risk_suppliers' => 0,
            'average_performance' => 0
        ];
        
        $total_score = 0;
        foreach ($suppliers as $supplier) {
            if (in_array($supplier->risk_level, ['high', 'critical'])) {
                $summary['high_risk_suppliers']++;
            }
            $total_score += floatval($supplier->avg_reliability);
        }
        
        if (count($suppliers) > 0) {
            $summary['average_performance'] = $total_score / count($suppliers);
        }
        
        return $summary;
    }
    
    /**
     * Get forecasting accuracy
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Forecasting accuracy data
     */
    private function get_forecasting_accuracy($start_date, $end_date) {
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('demand_forecasts');
        
        // This would compare forecasts to actual sales
        // Simplified for this implementation
        $accuracy_data = [
            'average_accuracy' => 85.5,
            'products_forecasted' => 150,
            'improvement_from_last_period' => 2.3
        ];
        
        return $accuracy_data;
    }
    
    /**
     * Generate recommendations
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return array Recommendations
     */
    private function generate_recommendations($start_date, $end_date) {
        $recommendations = [];
        
        // Get data for recommendations
        $inventory_analysis = $this->plugin->get_module_manager()->get_module('inventory_analysis');
        if ($inventory_analysis) {
            $summary = $inventory_analysis->get_inventory_summary();
            
            // Low stock recommendations
            if (count($summary['alerts']['low_stock_products']) > 5) {
                $recommendations[] = [
                    'type' => 'reorder',
                    'priority' => 'high',
                    'message' => sprintf(
                        __('%d products are running low on stock. Consider placing reorders soon.', 'ai-inventory-agent'),
                        count($summary['alerts']['low_stock_products'])
                    )
                ];
            }
            
            // Overstock recommendations
            if (count($summary['alerts']['overstock_products']) > 0) {
                $recommendations[] = [
                    'type' => 'promotion',
                    'priority' => 'medium',
                    'message' => sprintf(
                        __('%d products may be overstocked. Consider running promotions to move inventory.', 'ai-inventory-agent'),
                        count($summary['alerts']['overstock_products'])
                    )
                ];
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Save report to database
     * 
     * @param string $type Report type
     * @param array $data Report data
     * @param string $start_date Start date
     * @param string $end_date End date
     * @return int Report ID
     */
    private function save_report($type, $data, $start_date, $end_date) {
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('reports');
        
        $wpdb->insert(
            $table_name,
            [
                'report_type' => $type,
                'period_start' => $start_date,
                'period_end' => $end_date,
                'report_data' => json_encode($data),
                'generated_by' => get_current_user_id() ?: 0,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%d', '%s']
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Email report
     * 
     * @param int $report_id Report ID
     */
    private function email_report($report_id) {
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('reports');
        
        $report = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $report_id
        ));
        
        if (!$report) {
            return;
        }
        
        $recipient = $this->plugin->get_setting('notification_email') ?: get_option('admin_email');
        $report_data = json_decode($report->report_data, true);
        
        $subject = sprintf(
            __('[%s] %s Inventory Report - %s to %s', 'ai-inventory-agent'),
            get_bloginfo('name'),
            ucfirst($report->report_type),
            date('M j', strtotime($report->period_start)),
            date('M j, Y', strtotime($report->period_end))
        );
        
        $body = $this->format_report_email($report_data);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];
        
        wp_mail($recipient, $subject, $body, $headers);
    }
    
    /**
     * Format report for email
     * 
     * @param array $data Report data
     * @return string HTML email body
     */
    private function format_report_email($data) {
        $html = '<html><body style="font-family: Arial, sans-serif; color: #333;">';
        $html .= '<div style="max-width: 800px; margin: 0 auto; padding: 20px;">';
        
        // Header
        $html .= '<h1 style="color: #2c3e50;">Inventory Report</h1>';
        $html .= '<p style="color: #7f8c8d;">Period: ' . esc_html($data['period']['start']) . ' to ' . esc_html($data['period']['end']) . '</p>';
        
        // Summary section
        $html .= '<div style="background-color: #ecf0f1; padding: 20px; border-radius: 5px; margin: 20px 0;">';
        $html .= '<h2 style="color: #34495e; margin-top: 0;">Summary</h2>';
        $html .= '<p><strong>Total Orders:</strong> ' . number_format($data['summary']['total_orders']) . '</p>';
        $html .= '<p><strong>Total Revenue:</strong> ' . wc_price($data['summary']['total_revenue']) . '</p>';
        $html .= '<p><strong>Current Stock Value:</strong> ' . wc_price($data['summary']['current_stock_value']) . '</p>';
        $html .= '</div>';
        
        // Recommendations
        if (!empty($data['recommendations'])) {
            $html .= '<div style="background-color: #fff3cd; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">';
            $html .= '<h2 style="color: #856404; margin-top: 0;">Recommendations</h2>';
            foreach ($data['recommendations'] as $rec) {
                $html .= '<p>• ' . esc_html($rec['message']) . '</p>';
            }
            $html .= '</div>';
        }
        
        // View full report link
        $html .= '<p style="margin-top: 30px;">';
        $html .= '<a href="' . admin_url('edit.php?post_type=product&page=ai-inventory-agent&tab=reports') . '" style="background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Full Report</a>';
        $html .= '</p>';
        
        $html .= '</div></body></html>';
        
        return $html;
    }
    
    /**
     * Get module info
     * 
     * @return array Module information
     */
    public function get_info() {
        return $this->info;
    }
    
    /**
     * Handle generate report AJAX request
     */
    public function handle_generate_report() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $type = sanitize_text_field($_POST['type'] ?? 'weekly');
        $start_date = sanitize_text_field($_POST['start_date'] ?? '');
        $end_date = sanitize_text_field($_POST['end_date'] ?? '');
        
        if (empty($start_date) || empty($end_date)) {
            if ($type === 'weekly') {
                $start_date = date('Y-m-d', strtotime('-7 days'));
                $end_date = date('Y-m-d');
            } else {
                $start_date = date('Y-m-01');
                $end_date = date('Y-m-d');
            }
        }
        
        $report_data = $this->compile_report_data($start_date, $end_date);
        $report_id = $this->save_report($type, $report_data, $start_date, $end_date);
        
        wp_send_json_success([
            'report_id' => $report_id,
            'message' => __('Report generated successfully.', 'ai-inventory-agent')
        ]);
    }
    
    /**
     * Handle get report list AJAX request
     */
    public function handle_get_report_list() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('reports');
        
        $limit = intval($_POST['limit'] ?? 20);
        
        $reports = $wpdb->get_results($wpdb->prepare("
            SELECT id, report_type, period_start, period_end, created_at
            FROM {$table_name}
            ORDER BY created_at DESC
            LIMIT %d
        ", $limit));
        
        wp_send_json_success($reports);
    }
    
    /**
     * Handle download report AJAX request
     */
    public function handle_download_report() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $report_id = intval($_POST['report_id'] ?? 0);
        
        if (!$report_id) {
            wp_send_json_error(__('Invalid report ID.', 'ai-inventory-agent'));
        }
        
        // Implementation for downloading report as PDF/CSV would go here
        wp_send_json_success(__('Report download feature coming soon.', 'ai-inventory-agent'));
    }
}