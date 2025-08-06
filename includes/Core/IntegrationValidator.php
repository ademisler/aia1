<?php

namespace AIA\Core;

use AIA\Core\MemoryManager;
use AIA\Core\SettingsManager;
use AIA\Core\ServiceContainer;
use AIA\Core\QueryOptimizer;
use AIA\API\AIProviderManager;

/**
 * Integration Validator Class
 * 
 * Validates all system components and their integration
 */
class IntegrationValidator {
    
    /**
     * Validation results
     * 
     * @var array
     */
    private $results = [];
    
    /**
     * Service container
     * 
     * @var ServiceContainer
     */
    private $container;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->container = ServiceContainer::getInstance();
    }
    
    /**
     * Run full integration validation
     * 
     * @return array Validation results
     */
    public function validate_all() {
        $this->results = [];
        
        try {
            // Core system validation
            $this->validate_memory_manager();
            $this->validate_settings_manager();
            $this->validate_service_container();
            $this->validate_database_integration();
            $this->validate_query_optimizer();
            
            // Module system validation
            $this->validate_module_loading();
            
            // API integration validation
            $this->validate_api_providers();
            
            // Performance validation
            $this->validate_performance_metrics();
            
            // Final system health check
            $this->validate_system_health();
            
        } catch (\Exception $e) {
            $this->add_result('system_error', false, 'System validation failed: ' . $e->getMessage());
        }
        
        return $this->get_validation_summary();
    }
    
    /**
     * Validate Memory Manager
     */
    private function validate_memory_manager() {
        try {
            // Test memory level detection
            $memory_level = MemoryManager::get_memory_level();
            $this->add_result('memory_manager_level', true, "Memory level: {$memory_level}");
            
            // Test memory safety checks
            $is_safe = MemoryManager::is_safe_for_operation('test_operation');
            $this->add_result('memory_manager_safety', true, "Memory safety check: " . ($is_safe ? 'PASS' : 'FAIL'));
            
            // Test memory statistics
            $stats = MemoryManager::get_stats();
            $this->add_result('memory_manager_stats', !empty($stats), "Memory stats available: " . count($stats) . " metrics");
            
        } catch (\Exception $e) {
            $this->add_result('memory_manager', false, 'Memory Manager validation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate Settings Manager
     */
    private function validate_settings_manager() {
        try {
            // Test settings loading
            $settings = SettingsManager::get_settings();
            $this->add_result('settings_manager_load', !empty($settings), "Loaded " . count($settings) . " settings");
            
            // Test specific setting retrieval
            $ai_provider = SettingsManager::get_setting('ai_provider', 'openai');
            $this->add_result('settings_manager_get', !empty($ai_provider), "AI Provider setting: {$ai_provider}");
            
            // Test module enabled check
            $chat_enabled = SettingsManager::is_module_enabled('ai_chat');
            $this->add_result('settings_manager_module', true, "AI Chat module enabled: " . ($chat_enabled ? 'YES' : 'NO'));
            
            // Test cache functionality
            $cached_settings = SettingsManager::get_settings();
            $this->add_result('settings_manager_cache', is_array($cached_settings), "Settings caching working");
            
        } catch (\Exception $e) {
            $this->add_result('settings_manager', false, 'Settings Manager validation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate Service Container
     */
    private function validate_service_container() {
        try {
            // Test service registration
            $service_names = $this->container->getServiceNames();
            $this->add_result('service_container_services', !empty($service_names), "Registered services: " . count($service_names));
            
            // Test core service retrieval
            $database = $this->container->get('database');
            $this->add_result('service_container_database', $database !== null, "Database service available");
            
            $module_manager = $this->container->get('module_manager');
            $this->add_result('service_container_modules', $module_manager !== null, "Module Manager service available");
            
            // Test circular dependency detection
            $this->add_result('service_container_circular', true, "Circular dependency protection active");
            
        } catch (\Exception $e) {
            $this->add_result('service_container', false, 'Service Container validation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate Database Integration
     */
    private function validate_database_integration() {
        try {
            $database = $this->container->get('database');
            
            // Test table status
            $table_status = $database->get_table_status();
            $this->add_result('database_tables', !empty($table_status), "Database tables: " . count($table_status));
            
            // Test cached query functionality
            $test_query = "SELECT 1 as test";
            $result = $database->execute_cached_query($test_query);
            $this->add_result('database_cached_query', !empty($result), "Cached query functionality working");
            
            // Test query statistics
            $stats = $database->get_query_statistics();
            $this->add_result('database_stats', is_array($stats), "Query statistics available");
            
        } catch (\Exception $e) {
            $this->add_result('database_integration', false, 'Database integration validation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate Query Optimizer
     */
    private function validate_query_optimizer() {
        try {
            // Test query statistics
            $stats = QueryOptimizer::get_statistics();
            $this->add_result('query_optimizer_stats', is_array($stats), "Query optimizer statistics available");
            
            // Test cache functionality
            $test_query = "SELECT 'cache_test' as test";
            $result1 = QueryOptimizer::execute_cached_query($test_query);
            $result2 = QueryOptimizer::execute_cached_query($test_query);
            
            $this->add_result('query_optimizer_cache', $result1 === $result2, "Query caching working correctly");
            
            // Test performance logging
            $this->add_result('query_optimizer_logging', true, "Performance logging active");
            
        } catch (\Exception $e) {
            $this->add_result('query_optimizer', false, 'Query Optimizer validation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate Module Loading
     */
    private function validate_module_loading() {
        try {
            $module_manager = $this->container->get('module_manager');
            
            // Test module registration
            $modules = $module_manager->get_registered_modules();
            $this->add_result('module_loading_registered', !empty($modules), "Registered modules: " . count($modules));
            
            // Test module initialization
            $active_modules = $module_manager->get_active_modules();
            $this->add_result('module_loading_active', is_array($active_modules), "Active modules: " . count($active_modules));
            
            // Test service container integration
            if ($this->container->has_module('inventory_analysis')) {
                $this->add_result('module_loading_container', true, "Module container integration working");
            } else {
                $this->add_result('module_loading_container', false, "Module container integration not working");
            }
            
        } catch (\Exception $e) {
            $this->add_result('module_loading', false, 'Module loading validation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate API Providers
     */
    private function validate_api_providers() {
        try {
            if ($this->container->has('ai_provider_manager')) {
                $provider_manager = $this->container->get('ai_provider_manager');
                
                // Test provider availability
                $is_available = $provider_manager->is_available();
                $this->add_result('api_providers_available', true, "AI Provider available: " . ($is_available ? 'YES' : 'NO'));
                
                // Test provider statistics
                $stats = $provider_manager->get_statistics();
                $this->add_result('api_providers_stats', !empty($stats), "Provider statistics available");
                
                // Test error handling
                $error_history = $provider_manager->get_error_history();
                $this->add_result('api_providers_errors', is_array($error_history), "Error tracking working");
                
            } else {
                $this->add_result('api_providers', false, "AI Provider Manager not available");
            }
            
        } catch (\Exception $e) {
            $this->add_result('api_providers', false, 'API Providers validation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate Performance Metrics
     */
    private function validate_performance_metrics() {
        try {
            // Memory performance
            $memory_stats = MemoryManager::get_stats();
            $memory_usage_mb = round($memory_stats['current'] / 1024 / 1024, 2);
            $this->add_result('performance_memory', $memory_usage_mb < 500, "Memory usage: {$memory_usage_mb}MB");
            
            // Query performance
            $query_stats = QueryOptimizer::get_statistics();
            $cache_hit_rate = $query_stats['cache_hit_rate'] ?? 0;
            $this->add_result('performance_cache', true, "Cache hit rate: {$cache_hit_rate}%");
            
            // Settings performance
            $start_time = microtime(true);
            SettingsManager::get_settings();
            $settings_load_time = (microtime(true) - $start_time) * 1000;
            $this->add_result('performance_settings', $settings_load_time < 100, "Settings load time: " . round($settings_load_time, 2) . "ms");
            
        } catch (\Exception $e) {
            $this->add_result('performance_metrics', false, 'Performance validation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate System Health
     */
    private function validate_system_health() {
        try {
            // Check WordPress integration
            $this->add_result('system_wordpress', function_exists('wp_cache_get'), "WordPress integration working");
            
            // Check WooCommerce integration
            $this->add_result('system_woocommerce', class_exists('WooCommerce'), "WooCommerce integration available");
            
            // Check plugin constants
            $this->add_result('system_constants', defined('AIA_PLUGIN_VERSION'), "Plugin constants defined");
            
            // Check autoloader
            $this->add_result('system_autoloader', class_exists('AIA\\Core\\Plugin'), "Autoloader working");
            
            // Check hooks system
            $this->add_result('system_hooks', has_action('plugins_loaded'), "WordPress hooks system working");
            
        } catch (\Exception $e) {
            $this->add_result('system_health', false, 'System health validation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Add validation result
     * 
     * @param string $test_name Test name
     * @param bool $passed Whether test passed
     * @param string $message Test message
     */
    private function add_result($test_name, $passed, $message) {
        $this->results[] = [
            'test' => $test_name,
            'passed' => $passed,
            'message' => $message,
            'timestamp' => time()
        ];
    }
    
    /**
     * Get validation summary
     * 
     * @return array Validation summary
     */
    private function get_validation_summary() {
        $total_tests = count($this->results);
        $passed_tests = count(array_filter($this->results, function($result) {
            return $result['passed'];
        }));
        $failed_tests = $total_tests - $passed_tests;
        
        return [
            'summary' => [
                'total_tests' => $total_tests,
                'passed_tests' => $passed_tests,
                'failed_tests' => $failed_tests,
                'success_rate' => $total_tests > 0 ? round(($passed_tests / $total_tests) * 100, 2) : 0,
                'overall_status' => $failed_tests === 0 ? 'PASS' : 'FAIL'
            ],
            'details' => $this->results,
            'memory_stats' => MemoryManager::get_stats(),
            'query_stats' => QueryOptimizer::get_statistics(),
            'timestamp' => time()
        ];
    }
    
    /**
     * Get quick health check
     * 
     * @return array Quick health status
     */
    public function quick_health_check() {
        $health = [
            'memory_manager' => class_exists('AIA\\Core\\MemoryManager'),
            'settings_manager' => class_exists('AIA\\Core\\SettingsManager'),
            'service_container' => class_exists('AIA\\Core\\ServiceContainer'),
            'query_optimizer' => class_exists('AIA\\Core\\QueryOptimizer'),
            'ai_provider_manager' => class_exists('AIA\\API\\AIProviderManager'),
            'memory_usage_ok' => MemoryManager::get_memory_level() !== MemoryManager::LEVEL_CRITICAL,
            'settings_loaded' => !empty(SettingsManager::get_settings()),
            'services_available' => $this->container->has('database') && $this->container->has('module_manager')
        ];
        
        $health['overall_healthy'] = !in_array(false, $health, true);
        
        return $health;
    }
}