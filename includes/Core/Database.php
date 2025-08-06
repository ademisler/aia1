<?php

namespace AIA\Core;

/**
 * Database Class
 * 
 * Handles database operations and custom table management
 */
class Database {
    
    /**
     * WordPress database instance
     * 
     * @var wpdb
     */
    private $wpdb;
    
    /**
     * Plugin table prefix
     * 
     * @var string
     */
    private $table_prefix;
    
    /**
     * Custom tables
     * 
     * @var array
     */
    private $tables = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_prefix = $wpdb->prefix . 'aia_';
        
        $this->define_tables();
        
        // Only create tables during activation or when needed
        if (is_admin() && (current_user_can('activate_plugins') || get_option('aia_db_version') !== AIA_PLUGIN_VERSION)) {
            $this->create_tables();
        }
        
        // Declare HPOS compatibility
        $this->declare_hpos_compatibility();
    }
    
    /**
     * Define custom tables
     */
    private function define_tables() {
        $this->tables = [
            'inventory_logs' => [
                'name' => $this->table_prefix . 'inventory_logs',
                'schema' => "
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    product_id bigint(20) unsigned NOT NULL,
                    action varchar(50) NOT NULL,
                    old_stock int(11) DEFAULT NULL,
                    new_stock int(11) DEFAULT NULL,
                    change_reason varchar(255) DEFAULT NULL,
                    user_id bigint(20) unsigned DEFAULT NULL,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY product_id (product_id),
                    KEY action (action),
                    KEY created_at (created_at)
                "
            ],
            'reports' => [
                'name' => $this->table_prefix . 'reports',
                'schema' => "
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    report_type varchar(50) NOT NULL,
                    period_start date NOT NULL,
                    period_end date NOT NULL,
                    report_data longtext NOT NULL,
                    generated_by bigint(20) unsigned DEFAULT NULL,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY report_type (report_type),
                    KEY period_start (period_start),
                    KEY period_end (period_end),
                    KEY created_at (created_at)
                "
            ],
            'demand_forecasts' => [
                'name' => $this->table_prefix . 'demand_forecasts',
                'schema' => "
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    product_id bigint(20) unsigned NOT NULL,
                    forecast_date date NOT NULL,
                    predicted_demand int(11) NOT NULL,
                    confidence_score decimal(5,2) DEFAULT NULL,
                    seasonal_factor decimal(5,2) DEFAULT NULL,
                    trend_factor decimal(5,2) DEFAULT NULL,
                    model_version varchar(20) DEFAULT NULL,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY product_forecast (product_id, forecast_date),
                    KEY forecast_date (forecast_date),
                    KEY created_at (created_at)
                "
            ],
            'supplier_performance' => [
                'name' => $this->table_prefix . 'supplier_performance',
                'schema' => "
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    supplier_id varchar(100) NOT NULL,
                    supplier_name varchar(255) NOT NULL,
                    product_id bigint(20) unsigned DEFAULT NULL,
                    delivery_time_avg decimal(5,2) DEFAULT NULL,
                    delivery_time_variance decimal(5,2) DEFAULT NULL,
                    quality_score decimal(5,2) DEFAULT NULL,
                    reliability_score decimal(5,2) DEFAULT NULL,
                    cost_competitiveness decimal(5,2) DEFAULT NULL,
                    total_orders int(11) DEFAULT 0,
                    successful_deliveries int(11) DEFAULT 0,
                    last_order_date date DEFAULT NULL,
                    risk_level enum('low','medium','high','critical') DEFAULT 'medium',
                    notes text,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY supplier_id (supplier_id),
                    KEY product_id (product_id),
                    KEY risk_level (risk_level),
                    KEY last_order_date (last_order_date)
                "
            ],
            'ai_conversations' => [
                'name' => $this->table_prefix . 'ai_conversations',
                'schema' => "
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    session_id varchar(100) NOT NULL,
                    user_id bigint(20) unsigned NOT NULL,
                    message_type enum('user','assistant') NOT NULL,
                    message text NOT NULL,
                    context_data longtext DEFAULT NULL,
                    ai_provider varchar(50) DEFAULT NULL,
                    model_used varchar(100) DEFAULT NULL,
                    tokens_used int(11) DEFAULT NULL,
                    processing_time decimal(8,3) DEFAULT NULL,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY session_id (session_id),
                    KEY user_id (user_id),
                    KEY message_type (message_type),
                    KEY created_at (created_at)
                "
            ],
            'stock_alerts' => [
                'name' => $this->table_prefix . 'stock_alerts',
                'schema' => "
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    product_id bigint(20) unsigned NOT NULL,
                    alert_type enum('low_stock','out_of_stock','overstock','reorder_point') NOT NULL,
                    current_stock int(11) NOT NULL,
                    threshold_value int(11) NOT NULL,
                    severity enum('info','warning','critical') DEFAULT 'warning',
                    status enum('active','acknowledged','resolved') DEFAULT 'active',
                    message text DEFAULT NULL,
                    notified_users text DEFAULT NULL,
                    acknowledged_by bigint(20) unsigned DEFAULT NULL,
                    acknowledged_at datetime DEFAULT NULL,
                    resolved_at datetime DEFAULT NULL,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY product_id (product_id),
                    KEY alert_type (alert_type),
                    KEY severity (severity),
                    KEY status (status),
                    KEY created_at (created_at)
                "
            ],
            'reports_cache' => [
                'name' => $this->table_prefix . 'reports_cache',
                'schema' => "
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    report_type varchar(100) NOT NULL,
                    report_key varchar(255) NOT NULL,
                    report_data longtext NOT NULL,
                    parameters text DEFAULT NULL,
                    expires_at datetime NOT NULL,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY report_cache (report_type, report_key),
                    KEY expires_at (expires_at),
                    KEY created_at (created_at)
                "
            ]
        ];
    }
    
    /**
     * Create custom tables
     */
    private function create_tables() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        foreach ($this->tables as $table_key => $table_info) {
            $sql = "CREATE TABLE {$table_info['name']} ({$table_info['schema']}) {$this->get_charset_collate()};";
            
            $result = dbDelta($sql);
            
            // Log any table creation issues
            if (defined('WP_DEBUG') && WP_DEBUG && empty($result)) {
                error_log("AIA Database: Failed to create/update table {$table_info['name']}");
            }
        }
        
        // Update database version
        update_option('aia_db_version', AIA_PLUGIN_VERSION);
    }
    
    /**
     * Declare HPOS compatibility
     */
    private function declare_hpos_compatibility() {
        add_action('before_woocommerce_init', function() {
            if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                    'custom_order_tables', 
                    AIA_PLUGIN_FILE, 
                    true
                );
                
                // Also declare compatibility with new Cart and Checkout blocks
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                    'cart_checkout_blocks', 
                    AIA_PLUGIN_FILE, 
                    true
                );
            }
        });
    }
    
    /**
     * Get charset collate for table creation
     * 
     * @return string
     */
    private function get_charset_collate() {
        return $this->wpdb->get_charset_collate();
    }
    
    /**
     * Get table name
     * 
     * @param string $table_key Table key
     * @return string|null
     */
    public function get_table_name($table_key) {
        return $this->tables[$table_key]['name'] ?? null;
    }
    
    /**
     * Log inventory change
     * 
     * @param int $product_id Product ID
     * @param string $action Action performed
     * @param int $old_stock Old stock level
     * @param int $new_stock New stock level
     * @param string $reason Reason for change
     * @param int $user_id User ID
     * @return int|false Insert ID or false on failure
     */
    public function log_inventory_change($product_id, $action, $old_stock = null, $new_stock = null, $reason = null, $user_id = null) {
        $table = $this->get_table_name('inventory_logs');
        
        return $this->wpdb->insert(
            $table,
            [
                'product_id' => $product_id,
                'action' => $action,
                'old_stock' => $old_stock,
                'new_stock' => $new_stock,
                'change_reason' => $reason,
                'user_id' => $user_id ?: get_current_user_id(),
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%d', '%d', '%s', '%d', '%s']
        );
    }
    
    /**
     * Save demand forecast
     * 
     * @param int $product_id Product ID
     * @param string $forecast_date Forecast date
     * @param int $predicted_demand Predicted demand
     * @param array $additional_data Additional forecast data
     * @return int|false Insert ID or false on failure
     */
    public function save_demand_forecast($product_id, $forecast_date, $predicted_demand, $additional_data = []) {
        $table = $this->get_table_name('demand_forecasts');
        
        $data = array_merge([
            'product_id' => $product_id,
            'forecast_date' => $forecast_date,
            'predicted_demand' => $predicted_demand,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ], $additional_data);
        
        // Use INSERT ... ON DUPLICATE KEY UPDATE
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '%s'));
        $updates = implode(', ', array_map(fn($key) => "{$key} = VALUES({$key})", array_keys($data)));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders}) ON DUPLICATE KEY UPDATE {$updates}";
        
        return $this->wpdb->query($this->wpdb->prepare($sql, array_values($data)));
    }
    
    /**
     * Update supplier performance
     * 
     * @param string $supplier_id Supplier ID
     * @param array $performance_data Performance data
     * @return int|false Insert ID or false on failure
     */
    public function update_supplier_performance($supplier_id, $performance_data) {
        $table = $this->get_table_name('supplier_performance');
        
        $existing = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT id FROM {$table} WHERE supplier_id = %s", $supplier_id)
        );
        
        $data = array_merge([
            'supplier_id' => $supplier_id,
            'updated_at' => current_time('mysql')
        ], $performance_data);
        
        if ($existing) {
            return $this->wpdb->update($table, $data, ['id' => $existing->id]);
        } else {
            $data['created_at'] = current_time('mysql');
            return $this->wpdb->insert($table, $data);
        }
    }
    
    /**
     * Save AI conversation
     * 
     * @param string $session_id Session ID
     * @param string $message_type Message type (user/assistant)
     * @param string $message Message content
     * @param array $metadata Additional metadata
     * @return int|false Insert ID or false on failure
     */
    public function save_ai_conversation($session_id, $message_type, $message, $metadata = []) {
        $table = $this->get_table_name('ai_conversations');
        
        return $this->wpdb->insert(
            $table,
            [
                'session_id' => $session_id,
                'user_id' => get_current_user_id(),
                'message_type' => $message_type,
                'message' => $message,
                'context_data' => !empty($metadata['context']) ? json_encode($metadata['context']) : null,
                'ai_provider' => $metadata['provider'] ?? null,
                'model_used' => $metadata['model'] ?? null,
                'tokens_used' => $metadata['tokens'] ?? null,
                'processing_time' => $metadata['processing_time'] ?? null,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%f', '%s']
        );
    }
    
    /**
     * Create stock alert
     * 
     * @param int $product_id Product ID
     * @param string $alert_type Alert type
     * @param int $current_stock Current stock level
     * @param int $threshold_value Threshold value
     * @param array $additional_data Additional alert data
     * @return int|false Insert ID or false on failure
     */
    public function create_stock_alert($product_id, $alert_type, $current_stock, $threshold_value, $additional_data = []) {
        $table = $this->get_table_name('stock_alerts');
        
        // Check if similar alert already exists and is active
        $existing = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT id FROM {$table} WHERE product_id = %d AND alert_type = %s AND status = 'active'",
            $product_id,
            $alert_type
        ));
        
        if ($existing) {
            return false; // Don't create duplicate alerts
        }
        
        $data = array_merge([
            'product_id' => $product_id,
            'alert_type' => $alert_type,
            'current_stock' => $current_stock,
            'threshold_value' => $threshold_value,
            'created_at' => current_time('mysql')
        ], $additional_data);
        
        return $this->wpdb->insert($table, $data);
    }
    
    /**
     * Cache report data
     * 
     * @param string $report_type Report type
     * @param string $report_key Report key
     * @param mixed $report_data Report data
     * @param int $expires_in Expiration time in seconds
     * @param array $parameters Report parameters
     * @return int|false Insert ID or false on failure
     */
    public function cache_report($report_type, $report_key, $report_data, $expires_in = 3600, $parameters = []) {
        $table = $this->get_table_name('reports_cache');
        
        $expires_at = date('Y-m-d H:i:s', time() + $expires_in);
        
        $data = [
            'report_type' => $report_type,
            'report_key' => $report_key,
            'report_data' => is_array($report_data) || is_object($report_data) ? json_encode($report_data) : $report_data,
            'parameters' => !empty($parameters) ? json_encode($parameters) : null,
            'expires_at' => $expires_at,
            'created_at' => current_time('mysql')
        ];
        
        // Use INSERT ... ON DUPLICATE KEY UPDATE
        return $this->wpdb->replace($table, $data);
    }
    
    /**
     * Get cached report
     * 
     * @param string $report_type Report type
     * @param string $report_key Report key
     * @return mixed|null Cached data or null if not found/expired
     */
    public function get_cached_report($report_type, $report_key) {
        $table = $this->get_table_name('reports_cache');
        
        $cached = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT report_data, expires_at FROM {$table} WHERE report_type = %s AND report_key = %s AND expires_at > NOW()",
            $report_type,
            $report_key
        ));
        
        if (!$cached) {
            return null;
        }
        
        $data = json_decode($cached->report_data, true);
        return $data !== null ? $data : $cached->report_data;
    }
    
    /**
     * Clean expired cache entries
     */
    public function clean_expired_cache() {
        $table = $this->get_table_name('reports_cache');
        return $this->wpdb->query("DELETE FROM {$table} WHERE expires_at < NOW()");
    }
    
    /**
     * Get inventory logs
     * 
     * @param array $filters Filters
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function get_inventory_logs($filters = [], $limit = 50, $offset = 0) {
        $table = $this->get_table_name('inventory_logs');
        
        $where_clauses = ['1=1'];
        $where_values = [];
        
        if (!empty($filters['product_id'])) {
            $where_clauses[] = 'product_id = %d';
            $where_values[] = $filters['product_id'];
        }
        
        if (!empty($filters['action'])) {
            $where_clauses[] = 'action = %s';
            $where_values[] = $filters['action'];
        }
        
        if (!empty($filters['date_from'])) {
            $where_clauses[] = 'created_at >= %s';
            $where_values[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = 'created_at <= %s';
            $where_values[] = $filters['date_to'];
        }
        
        $where_clause = implode(' AND ', $where_clauses);
        
        $sql = "SELECT * FROM {$table} WHERE {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $where_values[] = $limit;
        $where_values[] = $offset;
        
        return $this->wpdb->get_results($this->wpdb->prepare($sql, $where_values));
    }
    
    /**
     * Drop all plugin tables
     */
    public function drop_tables() {
        foreach ($this->tables as $table_info) {
            $this->wpdb->query("DROP TABLE IF EXISTS {$table_info['name']}");
        }
    }
    
    /**
     * Get table name by key
     * 
     * @param string $table_key Table key
     * @return string|null Table name or null if not found
     */
    public function get_table_name($table_key) {
        return isset($this->tables[$table_key]) ? $this->tables[$table_key]['name'] : null;
    }
    
    /**
     * Check if table exists
     * 
     * @param string $table_key Table key
     * @return bool
     */
    public function table_exists($table_key) {
        $table_name = $this->get_table_name($table_key);
        if (!$table_name) {
            return false;
        }
        
        $result = $this->wpdb->get_var($this->wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        ));
        
        return $result === $table_name;
    }
    
    /**
     * Get all plugin tables status
     * 
     * @return array
     */
    public function get_tables_status() {
        $status = [];
        
        foreach ($this->tables as $key => $table) {
            $status[$key] = [
                'name' => $table['name'],
                'exists' => $this->table_exists($key)
            ];
        }
        
        return $status;
    }
}