<?php

namespace AIA\Core;

use AIA\Core\MemoryManager;

/**
 * Query Optimizer Class
 * 
 * Optimizes database queries and implements caching strategies
 */
class QueryOptimizer {
    
    /**
     * Cache prefix
     */
    const CACHE_PREFIX = 'aia_query_';
    
    /**
     * Default cache expiration (1 hour)
     */
    const DEFAULT_CACHE_EXPIRATION = 3600;
    
    /**
     * Query cache
     * 
     * @var array
     */
    private static $query_cache = [];
    
    /**
     * Query statistics
     * 
     * @var array
     */
    private static $query_stats = [
        'total_queries' => 0,
        'cached_queries' => 0,
        'cache_hits' => 0,
        'cache_misses' => 0,
        'total_time' => 0
    ];
    
    /**
     * Slow query threshold (in seconds)
     */
    const SLOW_QUERY_THRESHOLD = 2.0;
    
    /**
     * Execute optimized query with caching
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @param int $cache_expiration Cache expiration in seconds
     * @param string $cache_group Cache group
     * @return mixed Query results
     */
    public static function execute_cached_query($query, $params = [], $cache_expiration = self::DEFAULT_CACHE_EXPIRATION, $cache_group = 'default') {
        global $wpdb;
        
        $start_time = microtime(true);
        self::$query_stats['total_queries']++;
        
        // Generate cache key
        $cache_key = self::generate_cache_key($query, $params, $cache_group);
        
        // Try to get from cache
        $cached_result = self::get_from_cache($cache_key);
        if ($cached_result !== false) {
            self::$query_stats['cache_hits']++;
            self::log_query_performance($query, microtime(true) - $start_time, true);
            return $cached_result;
        }
        
        self::$query_stats['cache_misses']++;
        
        // Memory check before executing query
        if (!MemoryManager::is_safe_for_operation('database_query', MemoryManager::LEVEL_WARNING)) {
            throw new \Exception('Insufficient memory for database query');
        }
        
        // Execute query
        try {
            if (empty($params)) {
                $results = $wpdb->get_results($query, ARRAY_A);
            } else {
                $prepared_query = $wpdb->prepare($query, $params);
                $results = $wpdb->get_results($prepared_query, ARRAY_A);
            }
            
            if ($wpdb->last_error) {
                throw new \Exception('Database error: ' . $wpdb->last_error);
            }
            
            // Cache the results
            self::set_cache($cache_key, $results, $cache_expiration);
            
            $execution_time = microtime(true) - $start_time;
            self::$query_stats['total_time'] += $execution_time;
            
            self::log_query_performance($query, $execution_time, false);
            
            return $results;
            
        } catch (\Exception $e) {
            error_log('AIA Query Optimizer Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Execute optimized count query
     * 
     * @param string $table Table name
     * @param array $conditions WHERE conditions
     * @param int $cache_expiration Cache expiration
     * @return int Count result
     */
    public static function get_count($table, $conditions = [], $cache_expiration = self::DEFAULT_CACHE_EXPIRATION) {
        global $wpdb;
        
        $where_clause = '';
        $params = [];
        
        if (!empty($conditions)) {
            $where_parts = [];
            foreach ($conditions as $column => $value) {
                $where_parts[] = "{$column} = %s";
                $params[] = $value;
            }
            $where_clause = 'WHERE ' . implode(' AND ', $where_parts);
        }
        
        $query = "SELECT COUNT(*) as count FROM {$table} {$where_clause}";
        $cache_key = self::generate_cache_key($query, $params, 'count');
        
        // Try cache first
        $cached_result = self::get_from_cache($cache_key);
        if ($cached_result !== false) {
            return (int) $cached_result;
        }
        
        // Execute query
        if (empty($params)) {
            $result = $wpdb->get_var($query);
        } else {
            $prepared_query = $wpdb->prepare($query, $params);
            $result = $wpdb->get_var($prepared_query);
        }
        
        $count = (int) $result;
        self::set_cache($cache_key, $count, $cache_expiration);
        
        return $count;
    }
    
    /**
     * Execute batch insert with optimization
     * 
     * @param string $table Table name
     * @param array $data Array of data to insert
     * @param int $batch_size Batch size
     * @return bool Success status
     */
    public static function batch_insert($table, $data, $batch_size = 100) {
        global $wpdb;
        
        if (empty($data)) {
            return true;
        }
        
        // Memory check
        if (!MemoryManager::is_safe_for_operation('batch_insert', MemoryManager::LEVEL_WARNING)) {
            throw new \Exception('Insufficient memory for batch insert');
        }
        
        $chunks = array_chunk($data, $batch_size);
        
        foreach ($chunks as $chunk) {
            $values = [];
            $placeholders = [];
            
            // Get columns from first row
            $columns = array_keys($chunk[0]);
            $column_count = count($columns);
            
            foreach ($chunk as $row) {
                $row_placeholders = array_fill(0, $column_count, '%s');
                $placeholders[] = '(' . implode(',', $row_placeholders) . ')';
                $values = array_merge($values, array_values($row));
            }
            
            $query = sprintf(
                "INSERT INTO %s (%s) VALUES %s",
                $table,
                implode(',', $columns),
                implode(',', $placeholders)
            );
            
            $prepared_query = $wpdb->prepare($query, $values);
            $result = $wpdb->query($prepared_query);
            
            if ($result === false) {
                throw new \Exception('Batch insert failed: ' . $wpdb->last_error);
            }
        }
        
        // Clear related caches
        self::clear_table_cache($table);
        
        return true;
    }
    
    /**
     * Generate cache key for query
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @param string $group Cache group
     * @return string Cache key
     */
    private static function generate_cache_key($query, $params = [], $group = 'default') {
        $key_data = [
            'query' => $query,
            'params' => $params,
            'group' => $group
        ];
        
        return self::CACHE_PREFIX . md5(serialize($key_data));
    }
    
    /**
     * Get from cache
     * 
     * @param string $cache_key Cache key
     * @return mixed Cached data or false
     */
    private static function get_from_cache($cache_key) {
        // Try WordPress transient first
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
        
        // Try object cache
        return wp_cache_get($cache_key, 'aia_queries');
    }
    
    /**
     * Set cache
     * 
     * @param string $cache_key Cache key
     * @param mixed $data Data to cache
     * @param int $expiration Expiration in seconds
     */
    private static function set_cache($cache_key, $data, $expiration) {
        // Set WordPress transient
        set_transient($cache_key, $data, $expiration);
        
        // Set object cache
        wp_cache_set($cache_key, $data, 'aia_queries', $expiration);
    }
    
    /**
     * Clear cache by pattern
     * 
     * @param string $pattern Cache key pattern
     */
    public static function clear_cache_pattern($pattern) {
        global $wpdb;
        
        // Clear transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . self::CACHE_PREFIX . $pattern . '%'
            )
        );
        
        // Clear object cache group
        wp_cache_flush_group('aia_queries');
    }
    
    /**
     * Clear all table-related caches
     * 
     * @param string $table Table name
     */
    public static function clear_table_cache($table) {
        self::clear_cache_pattern($table);
    }
    
    /**
     * Log query performance
     * 
     * @param string $query SQL query
     * @param float $execution_time Execution time
     * @param bool $from_cache Whether result was from cache
     */
    private static function log_query_performance($query, $execution_time, $from_cache) {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        $log_data = [
            'query' => substr($query, 0, 100) . (strlen($query) > 100 ? '...' : ''),
            'execution_time' => round($execution_time * 1000, 2) . 'ms',
            'from_cache' => $from_cache,
            'memory_usage' => MemoryManager::format_bytes(MemoryManager::get_current_usage())
        ];
        
        // Log slow queries
        if (!$from_cache && $execution_time > self::SLOW_QUERY_THRESHOLD) {
            error_log('AIA Slow Query: ' . json_encode($log_data));
        }
        
        // Debug logging
        if (defined('AIA_DEBUG_QUERIES') && AIA_DEBUG_QUERIES) {
            error_log('AIA Query: ' . json_encode($log_data));
        }
    }
    
    /**
     * Get query statistics
     * 
     * @return array Query statistics
     */
    public static function get_statistics() {
        $stats = self::$query_stats;
        
        if ($stats['total_queries'] > 0) {
            $stats['cache_hit_rate'] = round(($stats['cache_hits'] / $stats['total_queries']) * 100, 2);
            $stats['average_time'] = round($stats['total_time'] / $stats['total_queries'] * 1000, 2); // in ms
        } else {
            $stats['cache_hit_rate'] = 0;
            $stats['average_time'] = 0;
        }
        
        return $stats;
    }
    
    /**
     * Reset statistics
     */
    public static function reset_statistics() {
        self::$query_stats = [
            'total_queries' => 0,
            'cached_queries' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'total_time' => 0
        ];
    }
    
    /**
     * Optimize database tables
     * 
     * @param array $tables Tables to optimize
     * @return array Optimization results
     */
    public static function optimize_tables($tables = []) {
        global $wpdb;
        
        if (empty($tables)) {
            // Get all AIA tables
            $tables = $wpdb->get_col(
                "SHOW TABLES LIKE '{$wpdb->prefix}aia_%'"
            );
        }
        
        $results = [];
        
        foreach ($tables as $table) {
            try {
                // Analyze table
                $wpdb->query("ANALYZE TABLE {$table}");
                
                // Optimize table
                $optimize_result = $wpdb->get_results("OPTIMIZE TABLE {$table}", ARRAY_A);
                
                $results[$table] = [
                    'success' => true,
                    'details' => $optimize_result
                ];
                
            } catch (\Exception $e) {
                $results[$table] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Clear all query caches
     */
    public static function clear_all_caches() {
        global $wpdb;
        
        // Clear all AIA transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . self::CACHE_PREFIX . '%'
            )
        );
        
        // Clear object cache
        wp_cache_flush_group('aia_queries');
        
        // Reset in-memory cache
        self::$query_cache = [];
    }
}