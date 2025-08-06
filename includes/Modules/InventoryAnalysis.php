<?php

namespace AIA\Modules;

use AIA\Core\Plugin;
use WC_Product;

/**
 * Inventory Analysis Module
 * 
 * Provides comprehensive inventory analysis and insights
 */
class InventoryAnalysis {
    
    /**
     * Module information
     * 
     * @var array
     */
    private $info = [
        'name' => 'Inventory Analysis',
        'description' => 'Comprehensive inventory analysis and insights',
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
        
        // Schedule analysis tasks
        $this->schedule_analysis_tasks();
    }
    
    /**
     * Register module hooks
     */
    private function register_hooks() {
        // WooCommerce hooks
        add_action('woocommerce_product_set_stock', [$this, 'on_stock_change'], 10, 1);
        add_action('woocommerce_order_status_completed', [$this, 'on_order_completed'], 10, 1);
        add_action('woocommerce_new_product', [$this, 'on_new_product'], 10, 1);
        
        // Custom hooks
        add_action('aia_daily_analysis', [$this, 'run_daily_analysis']);
        
        // AJAX handlers
        add_action('wp_ajax_aia_get_inventory_summary', [$this, 'handle_get_inventory_summary']);
        add_action('wp_ajax_aia_get_product_analysis', [$this, 'handle_get_product_analysis']);
        add_action('wp_ajax_aia_get_stock_alerts', [$this, 'handle_get_stock_alerts']);
    }
    
    /**
     * Schedule analysis tasks
     */
    private function schedule_analysis_tasks() {
        // Daily stock level analysis
        if (!wp_next_scheduled('aia_daily_stock_analysis')) {
            wp_schedule_event(time(), 'daily', 'aia_daily_stock_analysis');
        }
        
        // Weekly trend analysis
        if (!wp_next_scheduled('aia_weekly_trend_analysis')) {
            wp_schedule_event(strtotime('next sunday 2:00'), 'weekly', 'aia_weekly_trend_analysis');
        }
    }
    
    /**
     * Get comprehensive inventory summary
     * 
     * @return array Inventory summary data
     */
    public function get_inventory_summary() {
        $cache_key = 'aia_inventory_summary';
        $summary = wp_cache_get($cache_key);
        
        if ($summary === false) {
            global $wpdb;
            
            // Basic inventory counts
            $total_products = $this->get_total_products();
            $in_stock_products = $this->get_in_stock_products();
            $low_stock_products = $this->get_low_stock_products();
            $out_of_stock_products = $this->get_out_of_stock_products();
            $overstock_products = $this->get_overstock_products();
            
            // Stock value calculations
            $total_stock_value = $this->calculate_total_stock_value();
            $low_stock_value = $this->calculate_low_stock_value();
            
            // Recent activity
            $recent_stock_changes = $this->get_recent_stock_changes();
            $recent_sales = $this->get_recent_sales_summary();
            
            // Performance metrics
            $stock_turnover = $this->calculate_stock_turnover();
            $top_movers = $this->get_top_moving_products();
            $slow_movers = $this->get_slow_moving_products();
            
            $summary = [
                'counts' => [
                    'total_products' => $total_products,
                    'in_stock' => $in_stock_products,
                    'low_stock' => count($low_stock_products),
                    'out_of_stock' => count($out_of_stock_products),
                    'overstock' => count($overstock_products)
                ],
                'values' => [
                    'total_stock_value' => $total_stock_value,
                    'low_stock_value' => $low_stock_value,
                    'average_product_value' => $total_products > 0 ? $total_stock_value / $total_products : 0
                ],
                'activity' => [
                    'recent_changes' => count($recent_stock_changes),
                    'recent_sales' => $recent_sales
                ],
                'performance' => [
                    'stock_turnover' => $stock_turnover,
                    'top_movers' => $top_movers,
                    'slow_movers' => $slow_movers
                ],
                'alerts' => [
                    'low_stock_products' => $low_stock_products,
                    'out_of_stock_products' => $out_of_stock_products,
                    'overstock_products' => $overstock_products
                ]
            ];
            
            wp_cache_set($cache_key, $summary, '', 300); // Cache for 5 minutes
        }
        
        return $summary;
    }
    
    /**
     * Get total number of products
     * 
     * @return int Total products
     */
    private function get_total_products() {
        global $wpdb;
        
        return (int) $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->posts} 
            WHERE post_type = 'product' 
            AND post_status = 'publish'
        ");
    }
    
    /**
     * Get number of in-stock products
     * 
     * @return int In-stock products
     */
    private function get_in_stock_products() {
        global $wpdb;
        
        return (int) $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'product' 
            AND p.post_status = 'publish'
            AND pm.meta_key = '_stock'
            AND CAST(pm.meta_value AS UNSIGNED) > 0
        ");
    }
    
    /**
     * Get low stock products
     * 
     * @return array Low stock products
     */
    private function get_low_stock_products() {
        global $wpdb;
        
        $low_stock_threshold = $this->plugin->get_setting('low_stock_threshold');
        $critical_stock_threshold = $this->plugin->get_setting('critical_stock_threshold');
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, pm.meta_value as stock_quantity,
                   pm2.meta_value as regular_price
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_regular_price'
            WHERE p.post_type = 'product' 
            AND p.post_status = 'publish'
            AND pm.meta_key = '_stock'
            AND CAST(pm.meta_value AS UNSIGNED) <= %d
            AND CAST(pm.meta_value AS UNSIGNED) > %d
            ORDER BY CAST(pm.meta_value AS UNSIGNED) ASC
        ", $low_stock_threshold, $critical_stock_threshold));
    }
    
    /**
     * Get out of stock products
     * 
     * @return array Out of stock products
     */
    private function get_out_of_stock_products() {
        global $wpdb;
        
        $critical_stock_threshold = $this->plugin->get_setting('critical_stock_threshold');
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, pm.meta_value as stock_quantity,
                   pm2.meta_value as regular_price
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_regular_price'
            WHERE p.post_type = 'product' 
            AND p.post_status = 'publish'
            AND pm.meta_key = '_stock'
            AND CAST(pm.meta_value AS UNSIGNED) <= %d
            ORDER BY p.post_title ASC
        ", $critical_stock_threshold));
    }
    
    /**
     * Get overstock products (products with excessive inventory)
     * 
     * @return array Overstock products
     */
    private function get_overstock_products() {
        global $wpdb;
        
        // Define overstock as products with more than 90 days of inventory based on recent sales
        $date_from = date('Y-m-d', strtotime('-90 days'));
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, 
                   pm_stock.meta_value as current_stock,
                   COALESCE(sales.total_sold, 0) as sold_90_days,
                   pm_price.meta_value as regular_price
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock'
            LEFT JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_regular_price'
            LEFT JOIN (
                SELECT oim2.meta_value as product_id, SUM(oim.meta_value) as total_sold
                FROM {$wpdb->prefix}woocommerce_order_items oi
                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim2 ON oi.order_item_id = oim2.order_item_id
                INNER JOIN {$wpdb->posts} o ON oi.order_id = o.ID
                WHERE o.post_type = 'shop_order'
                AND o.post_status IN ('wc-completed', 'wc-processing')
                AND o.post_date >= %s
                AND oim.meta_key = '_qty'
                AND oim2.meta_key = '_product_id'
                GROUP BY oim2.meta_value
            ) sales ON p.ID = sales.product_id
            WHERE p.post_type = 'product' 
            AND p.post_status = 'publish'
            AND CAST(pm_stock.meta_value AS UNSIGNED) > 0
            HAVING current_stock > (sold_90_days * 2) AND current_stock > 10
            ORDER BY (current_stock / GREATEST(sold_90_days, 1)) DESC
            LIMIT 20
        ", $date_from));
        
        return $results;
    }
    
    /**
     * Calculate total stock value
     * 
     * @return float Total stock value
     */
    private function calculate_total_stock_value() {
        global $wpdb;
        
        $result = $wpdb->get_var("
            SELECT SUM(CAST(pm_stock.meta_value AS UNSIGNED) * CAST(pm_price.meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock'
            INNER JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_regular_price'
            WHERE p.post_type = 'product' 
            AND p.post_status = 'publish'
            AND CAST(pm_stock.meta_value AS UNSIGNED) > 0
            AND CAST(pm_price.meta_value AS DECIMAL(10,2)) > 0
        ");
        
        return (float) $result ?: 0;
    }
    
    /**
     * Calculate low stock value
     * 
     * @return float Low stock value
     */
    private function calculate_low_stock_value() {
        global $wpdb;
        
        $low_stock_threshold = $this->plugin->get_setting('low_stock_threshold');
        
        $result = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(CAST(pm_stock.meta_value AS UNSIGNED) * CAST(pm_price.meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock'
            INNER JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_regular_price'
            WHERE p.post_type = 'product' 
            AND p.post_status = 'publish'
            AND CAST(pm_stock.meta_value AS UNSIGNED) <= %d
            AND CAST(pm_stock.meta_value AS UNSIGNED) > 0
            AND CAST(pm_price.meta_value AS DECIMAL(10,2)) > 0
        ", $low_stock_threshold));
        
        return (float) $result ?: 0;
    }
    
    /**
     * Get recent stock changes
     * 
     * @param int $days Number of days to look back
     * @return array Recent stock changes
     */
    private function get_recent_stock_changes($days = 7) {
        $database = $this->plugin->get_database();
        $table = $database->get_table_name('inventory_logs');
        
        global $wpdb;
        
        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT il.*, p.post_title as product_name
            FROM {$table} il
            INNER JOIN {$wpdb->posts} p ON il.product_id = p.ID
            WHERE il.created_at >= %s
            ORDER BY il.created_at DESC
            LIMIT 50
        ", $date_from));
    }
    
    /**
     * Get recent sales summary
     * 
     * @param int $days Number of days to look back
     * @return array Sales summary
     */
    private function get_recent_sales_summary($days = 7) {
        global $wpdb;
        
        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $result = $wpdb->get_row($wpdb->prepare("
            SELECT COUNT(*) as total_orders,
                   SUM(pm.meta_value) as total_revenue
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_order_total'
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing')
            AND p.post_date >= %s
        ", $date_from));
        
        return [
            'total_orders' => (int) $result->total_orders,
            'total_revenue' => (float) $result->total_revenue,
            'average_order_value' => $result->total_orders > 0 ? $result->total_revenue / $result->total_orders : 0
        ];
    }
    
    /**
     * Calculate stock turnover rate
     * 
     * @return float Stock turnover rate
     */
    private function calculate_stock_turnover() {
        global $wpdb;
        
        // Calculate based on last 90 days
        $date_from = date('Y-m-d', strtotime('-90 days'));
        
        // Get total sales in the period
        $total_sales = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(oim.meta_value)
            FROM {$wpdb->prefix}woocommerce_order_items oi
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
            INNER JOIN {$wpdb->posts} o ON oi.order_id = o.ID
            WHERE o.post_type = 'shop_order'
            AND o.post_status IN ('wc-completed', 'wc-processing')
            AND o.post_date >= %s
            AND oim.meta_key = '_qty'
        ", $date_from));
        
        // Get average inventory
        $total_inventory = $wpdb->get_var("
            SELECT SUM(CAST(pm.meta_value AS UNSIGNED))
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'product' 
            AND p.post_status = 'publish'
            AND pm.meta_key = '_stock'
            AND CAST(pm.meta_value AS UNSIGNED) > 0
        ");
        
        if ($total_inventory > 0) {
            // Annualized turnover rate
            return ($total_sales / $total_inventory) * (365 / 90);
        }
        
        return 0;
    }
    
    /**
     * Get top moving products
     * 
     * @param int $limit Number of products to return
     * @return array Top moving products
     */
    private function get_top_moving_products($limit = 10) {
        global $wpdb;
        
        $date_from = date('Y-m-d', strtotime('-30 days'));
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, SUM(oim.meta_value) as total_sold,
                   pm.meta_value as current_stock
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->prefix}woocommerce_order_items oi ON p.ID = oi.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim2 ON oi.order_item_id = oim2.order_item_id
            INNER JOIN {$wpdb->posts} o ON oi.order_id = o.ID
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_stock'
            WHERE o.post_type = 'shop_order'
            AND o.post_status IN ('wc-completed', 'wc-processing')
            AND o.post_date >= %s
            AND oim.meta_key = '_qty'
            AND oim2.meta_key = '_product_id'
            AND oim2.meta_value = p.ID
            GROUP BY p.ID
            ORDER BY total_sold DESC
            LIMIT %d
        ", $date_from, $limit));
    }
    
    /**
     * Get slow moving products
     * 
     * @param int $limit Number of products to return
     * @return array Slow moving products
     */
    private function get_slow_moving_products($limit = 10) {
        global $wpdb;
        
        $date_from = date('Y-m-d', strtotime('-90 days'));
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, 
                   COALESCE(SUM(oim.meta_value), 0) as total_sold,
                   pm.meta_value as current_stock,
                   pm2.meta_value as regular_price
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim2 ON p.ID = oim2.meta_value AND oim2.meta_key = '_product_id'
            LEFT JOIN {$wpdb->prefix}woocommerce_order_items oi ON oim2.order_item_id = oi.order_item_id
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id AND oim.meta_key = '_qty'
            LEFT JOIN {$wpdb->posts} o ON oi.order_id = o.ID AND o.post_type = 'shop_order' AND o.post_status IN ('wc-completed', 'wc-processing') AND o.post_date >= %s
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_stock'
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_regular_price'
            WHERE p.post_type = 'product' 
            AND p.post_status = 'publish'
            AND CAST(pm.meta_value AS UNSIGNED) > 5
            GROUP BY p.ID
            HAVING total_sold <= 2
            ORDER BY total_sold ASC, CAST(pm.meta_value AS UNSIGNED) DESC
            LIMIT %d
        ", $date_from, $limit));
    }
    
    /**
     * Handle stock change event
     * 
     * @param WC_Product $product Product object
     */
    public function on_stock_change($product) {
        if (!$product instanceof WC_Product) {
            return;
        }
        
        $product_id = $product->get_id();
        $new_stock = $product->get_stock_quantity();
        
        // Get previous stock level
        $old_stock = get_post_meta($product_id, '_previous_stock', true);
        
        // Log the change
        $database = $this->plugin->get_database();
        $database->log_inventory_change(
            $product_id,
            'stock_change',
            $old_stock ?: null,
            $new_stock,
            'Stock level updated',
            get_current_user_id()
        );
        
        // Update previous stock meta
        update_post_meta($product_id, '_previous_stock', $new_stock);
        
        // Clear cache
        wp_cache_delete('aia_inventory_summary');
        
        // Check for alerts
        $this->check_stock_alerts($product);
    }
    
    /**
     * Handle order completed event
     * 
     * @param int $order_id Order ID
     */
    public function on_order_completed($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        // Log sales data for each product
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $quantity = $item->get_quantity();
            
            $database = $this->plugin->get_database();
            $database->log_inventory_change(
                $product_id,
                'sale',
                null,
                null,
                "Sold {$quantity} units - Order #{$order_id}",
                null
            );
        }
        
        // Clear cache
        wp_cache_delete('aia_inventory_summary');
        wp_cache_delete('aia_recent_sales');
    }
    
    /**
     * Handle new product event
     * 
     * @param int $product_id Product ID
     */
    public function on_new_product($product_id) {
        $database = $this->plugin->get_database();
        $database->log_inventory_change(
            $product_id,
            'new_product',
            null,
            null,
            'New product created',
            get_current_user_id()
        );
        
        // Clear cache
        wp_cache_delete('aia_inventory_summary');
    }
    
    /**
     * Check for stock alerts
     * 
     * @param WC_Product $product Product object
     */
    private function check_stock_alerts($product) {
        $stock_quantity = $product->get_stock_quantity();
        $low_stock_threshold = $this->plugin->get_setting('low_stock_threshold');
        $critical_stock_threshold = $this->plugin->get_setting('critical_stock_threshold');
        
        $database = $this->plugin->get_database();
        
        if ($stock_quantity <= $critical_stock_threshold) {
            $database->create_stock_alert(
                $product->get_id(),
                'out_of_stock',
                $stock_quantity,
                $critical_stock_threshold,
                [
                    'severity' => 'critical',
                    'message' => sprintf('Product "%s" is out of stock (Current: %d)', $product->get_name(), $stock_quantity)
                ]
            );
        } elseif ($stock_quantity <= $low_stock_threshold) {
            $database->create_stock_alert(
                $product->get_id(),
                'low_stock',
                $stock_quantity,
                $low_stock_threshold,
                [
                    'severity' => 'warning',
                    'message' => sprintf('Product "%s" is low on stock (Current: %d)', $product->get_name(), $stock_quantity)
                ]
            );
        }
    }
    
    /**
     * Run daily analysis
     */
    public function run_daily_analysis() {
        // Clear all caches
        wp_cache_delete('aia_inventory_summary');
        wp_cache_delete('aia_recent_sales');
        wp_cache_delete('aia_inventory_stats');
        
        // Pre-generate summary to warm cache
        $this->get_inventory_summary();
        
        // Log analysis completion
        error_log('AIA: Daily inventory analysis completed');
    }
    
    /**
     * Update sales data from order
     * 
     * @param int $order_id Order ID
     */
    public function update_sales_data($order_id) {
        $this->on_order_completed($order_id);
    }
    
    /**
     * Handle get inventory summary AJAX request
     */
    public function handle_get_inventory_summary() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $summary = $this->get_inventory_summary();
        wp_send_json_success($summary);
    }
    
    /**
     * Handle get product analysis AJAX request
     */
    public function handle_get_product_analysis() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $product_id = intval($_POST['product_id'] ?? 0);
        
        if (!$product_id) {
            wp_send_json_error(__('Product ID is required.', 'ai-inventory-agent'));
        }
        
        $analysis = $this->get_product_analysis($product_id);
        wp_send_json_success($analysis);
    }
    
    /**
     * Handle get stock alerts AJAX request
     */
    public function handle_get_stock_alerts() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $alerts = $this->get_active_stock_alerts();
        wp_send_json_success($alerts);
    }
    
    /**
     * Get product analysis
     * 
     * @param int $product_id Product ID
     * @return array Product analysis
     */
    private function get_product_analysis($product_id) {
        // Implementation for detailed product analysis
        // This would include sales trends, stock history, forecasting, etc.
        return [
            'product_id' => $product_id,
            'analysis' => 'Detailed product analysis would go here'
        ];
    }
    
    /**
     * Get active stock alerts
     * 
     * @return array Active alerts
     */
    private function get_active_stock_alerts() {
        global $wpdb;
        
        $database = $this->plugin->get_database();
        $table = $database->get_table_name('stock_alerts');
        
        return $wpdb->get_results("
            SELECT sa.*, p.post_title as product_name
            FROM {$table} sa
            INNER JOIN {$wpdb->posts} p ON sa.product_id = p.ID
            WHERE sa.status = 'active'
            ORDER BY sa.severity DESC, sa.created_at DESC
            LIMIT 50
        ");
    }
    
    /**
     * Get module information
     * 
     * @return array Module info
     */
    public function get_info() {
        return $this->info;
    }
    
    /**
     * Deactivate module
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('aia_daily_stock_analysis');
        wp_clear_scheduled_hook('aia_weekly_trend_analysis');
        
        // Clear cache
        wp_cache_delete('aia_inventory_summary');
        wp_cache_delete('aia_recent_sales');
        wp_cache_delete('aia_inventory_stats');
    }
}