<?php

namespace AIA\Modules;

use AIA\Core\Plugin;

/**
 * Demand Forecasting Module
 * 
 * Provides AI-powered demand forecasting for inventory management
 */
class DemandForecasting {
    
    /**
     * Module information
     * 
     * @var array
     */
    private $info = [
        'name' => 'Demand Forecasting',
        'description' => 'AI-powered demand forecasting for inventory optimization',
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
        // Avoid circular dependency - plugin instance will be set during init
    }
    
    /**
     * Initialize the module
     */
    public function init() {
        // Set plugin instance safely during init
        if (!$this->plugin && class_exists('AIA\\Core\\Plugin')) {
            $this->plugin = \AIA\Core\Plugin::get_instance();
        }
        
        if (!$this->plugin) {
            error_log('AIA DemandForecasting: Plugin instance not available during init');
            return;
        }
        
        // Register hooks
        $this->register_hooks();
    }
    
    /**
     * Register module hooks
     */
    private function register_hooks() {
        // Schedule forecasting tasks
        add_action('aia_daily_forecasting', [$this, 'run_daily_forecasting']);
        add_action('aia_weekly_forecasting', [$this, 'run_weekly_forecasting']);
        
        // AJAX handlers
        add_action('wp_ajax_aia_get_demand_forecast', [$this, 'handle_get_demand_forecast']);
        add_action('wp_ajax_aia_update_forecast_model', [$this, 'handle_update_forecast_model']);
    }
    
    /**
     * Run daily forecasting analysis
     */
    public function run_daily_analysis() {
        global $wpdb;
        $database = $this->plugin->get_database();
        
        try {
            // Get all active products
            $products = $wpdb->get_results($wpdb->prepare("
                SELECT ID, post_title 
                FROM {$wpdb->posts} 
                WHERE post_type = %s AND post_status = %s
                LIMIT 100
            ", 'product', 'publish'));
            
            foreach ($products as $product) {
                $this->forecast_product_demand($product->ID);
            }
            
            // Log completion
            error_log('AIA: Daily demand forecasting completed for ' . count($products) . ' products');
            
        } catch (\Exception $e) {
            error_log('AIA: Error in daily demand forecasting: ' . $e->getMessage());
        }
    }
    
    /**
     * Forecast demand for a specific product
     * 
     * @param int $product_id Product ID
     * @return array Forecast data
     */
    private function forecast_product_demand($product_id) {
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('demand_forecasts');
        
        // Get historical sales data
        $sales_data = $this->get_product_sales_history($product_id, 90);
        
        if (empty($sales_data)) {
            return ['error' => 'Insufficient sales data'];
        }
        
        // Simple moving average forecast
        $forecast = $this->calculate_moving_average_forecast($sales_data);
        
        // Calculate confidence score based on data consistency
        $confidence = $this->calculate_confidence_score($sales_data);
        
        // Save forecast to database
        $wpdb->insert(
            $table_name,
            [
                'product_id' => $product_id,
                'forecast_date' => date('Y-m-d', strtotime('+7 days')),
                'predicted_demand' => $forecast['predicted_demand'],
                'confidence_score' => $confidence,
                'seasonal_factor' => $forecast['seasonal_factor'],
                'trend_factor' => $forecast['trend_factor'],
                'model_version' => '1.0.0',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['%d', '%s', '%d', '%f', '%f', '%f', '%s', '%s', '%s']
        );
        
        return [
            'product_id' => $product_id,
            'forecast' => $forecast,
            'confidence' => $confidence
        ];
    }
    
    /**
     * Get product sales history
     * 
     * @param int $product_id Product ID
     * @param int $days Number of days to look back
     * @return array Sales data
     */
    private function get_product_sales_history($product_id, $days = 30) {
        global $wpdb;
        
        $start_date = date('Y-m-d', strtotime("-{$days} days"));
        
        $sales = $wpdb->get_results($wpdb->prepare("
            SELECT DATE(p.post_date) as sale_date, SUM(oim.meta_value) as quantity
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->prefix}woocommerce_order_items oi ON p.ID = oi.order_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing')
            AND oi.order_item_type = 'line_item'
            AND oim.meta_key = '_qty'
            AND oim.order_item_id IN (
                SELECT order_item_id 
                FROM {$wpdb->prefix}woocommerce_order_itemmeta
                WHERE meta_key = '_product_id' AND meta_value = %d
            )
            AND p.post_date >= %s
            GROUP BY DATE(p.post_date)
            ORDER BY sale_date ASC
        ", $product_id, $start_date));
        
        return $sales;
    }
    
    /**
     * Calculate moving average forecast
     * 
     * @param array $sales_data Historical sales data
     * @return array Forecast data
     */
    private function calculate_moving_average_forecast($sales_data) {
        $window_size = min(7, count($sales_data));
        $recent_sales = array_slice($sales_data, -$window_size);
        
        $total_quantity = 0;
        foreach ($recent_sales as $sale) {
            $total_quantity += $sale->quantity;
        }
        
        $average_daily_demand = $total_quantity / $window_size;
        
        // Simple trend calculation
        $trend_factor = 1.0;
        if (count($sales_data) > 14) {
            $first_half = array_slice($sales_data, 0, count($sales_data) / 2);
            $second_half = array_slice($sales_data, count($sales_data) / 2);
            
            $first_avg = array_sum(array_column($first_half, 'quantity')) / count($first_half);
            $second_avg = array_sum(array_column($second_half, 'quantity')) / count($second_half);
            
            if ($first_avg > 0) {
                $trend_factor = $second_avg / $first_avg;
            }
        }
        
        return [
            'predicted_demand' => round($average_daily_demand * 7 * $trend_factor),
            'seasonal_factor' => 1.0,
            'trend_factor' => $trend_factor
        ];
    }
    
    /**
     * Calculate confidence score
     * 
     * @param array $sales_data Sales data
     * @return float Confidence score (0-100)
     */
    private function calculate_confidence_score($sales_data) {
        if (count($sales_data) < 7) {
            return 30.0; // Low confidence with limited data
        }
        
        // Calculate standard deviation
        $quantities = array_column($sales_data, 'quantity');
        $mean = array_sum($quantities) / count($quantities);
        
        $variance = 0;
        foreach ($quantities as $qty) {
            $variance += pow($qty - $mean, 2);
        }
        $variance /= count($quantities);
        $std_dev = sqrt($variance);
        
        // Calculate coefficient of variation
        $cv = $mean > 0 ? ($std_dev / $mean) : 1;
        
        // Convert to confidence score (lower CV = higher confidence)
        $confidence = max(0, min(100, 100 * (1 - $cv)));
        
        // Adjust based on data points
        $data_factor = min(1, count($sales_data) / 30);
        $confidence *= $data_factor;
        
        return round($confidence, 2);
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
     * Handle get demand forecast AJAX request
     */
    public function handle_get_demand_forecast() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $product_id = intval($_POST['product_id'] ?? 0);
        
        if (!$product_id) {
            wp_send_json_error(__('Invalid product ID.', 'ai-inventory-agent'));
        }
        
        $forecast = $this->forecast_product_demand($product_id);
        wp_send_json_success($forecast);
    }
    
    /**
     * Handle update forecast model AJAX request
     */
    public function handle_update_forecast_model() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        // Trigger forecast update for all products
        $this->run_daily_analysis();
        
        wp_send_json_success(__('Forecast model updated successfully.', 'ai-inventory-agent'));
    }
}