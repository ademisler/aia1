<?php

namespace AIA\Modules;

use AIA\Core\Plugin;

/**
 * Supplier Analysis Module
 * 
 * Analyzes supplier performance and manages supplier relationships
 */
class SupplierAnalysis {
    
    /**
     * Module information
     * 
     * @var array
     */
    private $info = [
        'name' => 'Supplier Analysis',
        'description' => 'Supplier performance tracking and analysis',
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
    }
    
    /**
     * Register module hooks
     */
    private function register_hooks() {
        // AJAX handlers
        add_action('wp_ajax_aia_get_supplier_list', [$this, 'handle_get_supplier_list']);
        add_action('wp_ajax_aia_get_supplier_performance', [$this, 'handle_get_supplier_performance']);
        add_action('wp_ajax_aia_update_supplier_data', [$this, 'handle_update_supplier_data']);
        
        // Scheduled tasks
        add_action('aia_weekly_supplier_analysis', [$this, 'run_weekly_analysis']);
    }
    
    /**
     * Get supplier list
     * 
     * @return array Supplier list
     */
    public function get_supplier_list() {
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('supplier_performance');
        
        $suppliers = $wpdb->get_results("
            SELECT DISTINCT supplier_id, supplier_name, risk_level, 
                   AVG(reliability_score) as avg_reliability,
                   AVG(quality_score) as avg_quality,
                   COUNT(DISTINCT product_id) as product_count,
                   MAX(last_order_date) as last_order
            FROM {$table_name}
            GROUP BY supplier_id, supplier_name, risk_level
            ORDER BY avg_reliability DESC
        ");
        
        return $suppliers;
    }
    
    /**
     * Get supplier performance metrics
     * 
     * @param string $supplier_id Supplier ID
     * @return array Performance metrics
     */
    public function get_supplier_performance($supplier_id) {
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('supplier_performance');
        
        $performance = $wpdb->get_row($wpdb->prepare("
            SELECT supplier_id, supplier_name,
                   AVG(delivery_time_avg) as avg_delivery_time,
                   AVG(delivery_time_variance) as delivery_variance,
                   AVG(quality_score) as avg_quality,
                   AVG(reliability_score) as avg_reliability,
                   AVG(cost_competitiveness) as avg_cost_score,
                   SUM(total_orders) as total_orders,
                   SUM(successful_deliveries) as successful_deliveries,
                   MAX(last_order_date) as last_order,
                   risk_level
            FROM {$table_name}
            WHERE supplier_id = %s
            GROUP BY supplier_id, supplier_name, risk_level
        ", $supplier_id));
        
        if (!$performance) {
            return null;
        }
        
        // Calculate additional metrics
        $performance->success_rate = $performance->total_orders > 0 
            ? ($performance->successful_deliveries / $performance->total_orders) * 100 
            : 0;
        
        $performance->overall_score = $this->calculate_overall_score($performance);
        
        // Get product-specific performance
        $products = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, sp.*
            FROM {$table_name} sp
            INNER JOIN {$wpdb->posts} p ON sp.product_id = p.ID
            WHERE sp.supplier_id = %s
            ORDER BY sp.quality_score DESC
        ", $supplier_id));
        
        $performance->products = $products;
        
        return $performance;
    }
    
    /**
     * Calculate overall supplier score
     * 
     * @param object $performance Performance data
     * @return float Overall score
     */
    private function calculate_overall_score($performance) {
        $weights = [
            'quality' => 0.3,
            'reliability' => 0.3,
            'delivery' => 0.2,
            'cost' => 0.2
        ];
        
        $delivery_score = max(0, 100 - ($performance->avg_delivery_time * 2));
        
        $score = (
            $performance->avg_quality * $weights['quality'] +
            $performance->avg_reliability * $weights['reliability'] +
            $delivery_score * $weights['delivery'] +
            $performance->avg_cost_score * $weights['cost']
        );
        
        return round($score, 2);
    }
    
    /**
     * Update supplier risk level
     * 
     * @param string $supplier_id Supplier ID
     * @return string New risk level
     */
    public function update_supplier_risk_level($supplier_id) {
        $performance = $this->get_supplier_performance($supplier_id);
        
        if (!$performance) {
            return 'unknown';
        }
        
        $risk_level = 'low';
        
        // Determine risk based on metrics
        if ($performance->overall_score < 50) {
            $risk_level = 'critical';
        } elseif ($performance->overall_score < 70) {
            $risk_level = 'high';
        } elseif ($performance->overall_score < 85) {
            $risk_level = 'medium';
        }
        
        // Additional risk factors
        if ($performance->success_rate < 80) {
            $risk_level = $this->increase_risk_level($risk_level);
        }
        
        if ($performance->avg_delivery_time > 14) {
            $risk_level = $this->increase_risk_level($risk_level);
        }
        
        // Update in database
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('supplier_performance');
        
        $wpdb->update(
            $table_name,
            ['risk_level' => $risk_level],
            ['supplier_id' => $supplier_id],
            ['%s'],
            ['%s']
        );
        
        return $risk_level;
    }
    
    /**
     * Increase risk level
     * 
     * @param string $current_level Current risk level
     * @return string New risk level
     */
    private function increase_risk_level($current_level) {
        $levels = ['low', 'medium', 'high', 'critical'];
        $current_index = array_search($current_level, $levels);
        
        if ($current_index !== false && $current_index < count($levels) - 1) {
            return $levels[$current_index + 1];
        }
        
        return $current_level;
    }
    
    /**
     * Run weekly supplier analysis
     */
    public function run_weekly_analysis() {
        $suppliers = $this->get_supplier_list();
        
        foreach ($suppliers as $supplier) {
            $this->update_supplier_risk_level($supplier->supplier_id);
        }
        
        // Send alerts for high-risk suppliers
        $this->check_supplier_alerts();
    }
    
    /**
     * Check and send supplier alerts
     */
    private function check_supplier_alerts() {
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('supplier_performance');
        
        $high_risk_suppliers = $wpdb->get_results("
            SELECT DISTINCT supplier_id, supplier_name, risk_level
            FROM {$table_name}
            WHERE risk_level IN ('high', 'critical')
        ");
        
        if (!empty($high_risk_suppliers)) {
            $notifications = $this->plugin->get_module_manager()->get_module('notifications');
            if ($notifications) {
                foreach ($high_risk_suppliers as $supplier) {
                    $notifications->send_supplier_alert($supplier);
                }
            }
        }
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
     * Handle get supplier list AJAX request
     */
    public function handle_get_supplier_list() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $suppliers = $this->get_supplier_list();
        wp_send_json_success($suppliers);
    }
    
    /**
     * Handle get supplier performance AJAX request
     */
    public function handle_get_supplier_performance() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $supplier_id = sanitize_text_field($_POST['supplier_id'] ?? '');
        
        if (empty($supplier_id)) {
            wp_send_json_error(__('Invalid supplier ID.', 'ai-inventory-agent'));
        }
        
        $performance = $this->get_supplier_performance($supplier_id);
        
        if (!$performance) {
            wp_send_json_error(__('Supplier not found.', 'ai-inventory-agent'));
        }
        
        wp_send_json_success($performance);
    }
    
    /**
     * Handle update supplier data AJAX request
     */
    public function handle_update_supplier_data() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $supplier_id = sanitize_text_field($_POST['supplier_id'] ?? '');
        $data = $_POST['data'] ?? [];
        
        if (empty($supplier_id)) {
            wp_send_json_error(__('Invalid supplier ID.', 'ai-inventory-agent'));
        }
        
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('supplier_performance');
        
        $update_data = [];
        $format = [];
        
        // Validate and prepare update data
        if (isset($data['quality_score'])) {
            $update_data['quality_score'] = floatval($data['quality_score']);
            $format[] = '%f';
        }
        
        if (isset($data['reliability_score'])) {
            $update_data['reliability_score'] = floatval($data['reliability_score']);
            $format[] = '%f';
        }
        
        if (isset($data['notes'])) {
            $update_data['notes'] = sanitize_textarea_field($data['notes']);
            $format[] = '%s';
        }
        
        if (!empty($update_data)) {
            $update_data['updated_at'] = current_time('mysql');
            $format[] = '%s';
            
            $result = $wpdb->update(
                $table_name,
                $update_data,
                ['supplier_id' => $supplier_id],
                $format,
                ['%s']
            );
            
            if ($result !== false) {
                // Update risk level
                $new_risk_level = $this->update_supplier_risk_level($supplier_id);
                
                wp_send_json_success([
                    'message' => __('Supplier data updated successfully.', 'ai-inventory-agent'),
                    'risk_level' => $new_risk_level
                ]);
            } else {
                wp_send_json_error(__('Failed to update supplier data.', 'ai-inventory-agent'));
            }
        } else {
            wp_send_json_error(__('No valid data to update.', 'ai-inventory-agent'));
        }
    }
}