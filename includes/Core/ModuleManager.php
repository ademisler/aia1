<?php

namespace AIA\Core;

/**
 * Module Manager Class
 * 
 * Handles registration, initialization, and management of plugin modules
 */
class ModuleManager {
    
    /**
     * Registered modules
     * 
     * @var array
     */
    private $modules = [];
    
    /**
     * Active module instances
     * 
     * @var array
     */
    private $active_modules = [];
    
    /**
     * Module dependencies
     * 
     * @var array
     */
    private $dependencies = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        // Module manager initialization
    }
    
    /**
     * Register a module
     * 
     * @param string $module_id Module identifier
     * @param string $class_name Module class name
     * @param array $dependencies Module dependencies
     * @return bool
     */
    public function register_module($module_id, $class_name, $dependencies = []) {
        if (isset($this->modules[$module_id])) {
            return false; // Module already registered
        }
        
        $this->modules[$module_id] = [
            'class' => $class_name,
            'active' => false,
            'instance' => null,
            'dependencies' => $dependencies
        ];
        
        $this->dependencies[$module_id] = $dependencies;
        
        return true;
    }
    
    /**
     * Initialize all modules
     */
    public function init_modules() {
        // Sort modules by dependencies
        $sorted_modules = $this->sort_modules_by_dependencies();
        
        foreach ($sorted_modules as $module_id) {
            $this->init_module($module_id);
        }
    }
    
    /**
     * Initialize a specific module
     * 
     * @param string $module_id Module identifier
     * @return bool
     */
    public function init_module($module_id) {
        if (!isset($this->modules[$module_id])) {
            return false;
        }
        
        if ($this->modules[$module_id]['active']) {
            return true; // Already initialized
        }
        
        // Check if module is enabled in settings
        if (!$this->is_module_enabled($module_id)) {
            return false;
        }
        
        // Initialize dependencies first
        foreach ($this->modules[$module_id]['dependencies'] as $dependency) {
            if (!$this->init_module($dependency)) {
                error_log("AIA: Failed to initialize dependency '{$dependency}' for module '{$module_id}'");
                return false;
            }
        }
        
        $class_name = $this->modules[$module_id]['class'];
        
        if (!class_exists($class_name)) {
            error_log("AIA: Module class '{$class_name}' not found for module '{$module_id}'");
            return false;
        }
        
        try {
            $instance = new $class_name();
            
            if (method_exists($instance, 'init')) {
                $instance->init();
            }
            
            $this->modules[$module_id]['instance'] = $instance;
            $this->modules[$module_id]['active'] = true;
            $this->active_modules[$module_id] = $instance;
            
            // Trigger module loaded action
            do_action('aia_module_loaded', $module_id, $instance);
            
            return true;
            
        } catch (\Exception $e) {
            error_log("AIA: Failed to initialize module '{$module_id}': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Deactivate a module
     * 
     * @param string $module_id Module identifier
     * @return bool
     */
    public function deactivate_module($module_id) {
        if (!isset($this->modules[$module_id]) || !$this->modules[$module_id]['active']) {
            return false;
        }
        
        $instance = $this->modules[$module_id]['instance'];
        
        if (method_exists($instance, 'deactivate')) {
            $instance->deactivate();
        }
        
        $this->modules[$module_id]['active'] = false;
        $this->modules[$module_id]['instance'] = null;
        unset($this->active_modules[$module_id]);
        
        // Trigger module deactivated action
        do_action('aia_module_deactivated', $module_id);
        
        return true;
    }
    
    /**
     * Get a module instance
     * 
     * @param string $module_id Module identifier
     * @return object|null
     */
    public function get_module($module_id) {
        if (isset($this->active_modules[$module_id])) {
            return $this->active_modules[$module_id];
        }
        
        return null;
    }
    
    /**
     * Check if a module is active
     * 
     * @param string $module_id Module identifier
     * @return bool
     */
    public function is_module_active($module_id) {
        return isset($this->modules[$module_id]) && $this->modules[$module_id]['active'];
    }
    
    /**
     * Check if a module is enabled in settings
     * 
     * @param string $module_id Module identifier
     * @return bool
     */
    private function is_module_enabled($module_id) {
        $plugin = Plugin::get_instance();
        
        // Map module IDs to setting keys
        $setting_map = [
            'ai_chat' => 'chat_enabled',
            'demand_forecasting' => 'forecasting_enabled',
            'notifications' => 'notifications_enabled',
            'reporting' => 'reports_enabled',
        ];
        
        $setting_key = $setting_map[$module_id] ?? $module_id . '_enabled';
        
        return $plugin->get_setting($setting_key) !== false;
    }
    
    /**
     * Get all registered modules
     * 
     * @return array
     */
    public function get_registered_modules() {
        return $this->modules;
    }
    
    /**
     * Get all active modules
     * 
     * @return array
     */
    public function get_active_modules() {
        return $this->active_modules;
    }
    
    /**
     * Sort modules by dependencies
     * 
     * @return array
     */
    private function sort_modules_by_dependencies() {
        $sorted = [];
        $visited = [];
        $visiting = [];
        
        foreach (array_keys($this->modules) as $module_id) {
            $this->visit_module($module_id, $sorted, $visited, $visiting);
        }
        
        return $sorted;
    }
    
    /**
     * Visit module for dependency sorting (DFS)
     * 
     * @param string $module_id Module identifier
     * @param array &$sorted Sorted modules array
     * @param array &$visited Visited modules array
     * @param array &$visiting Currently visiting modules array
     */
    private function visit_module($module_id, &$sorted, &$visited, &$visiting) {
        if (isset($visited[$module_id])) {
            return;
        }
        
        if (isset($visiting[$module_id])) {
            // Circular dependency detected
            error_log("AIA: Circular dependency detected for module '{$module_id}'");
            return;
        }
        
        $visiting[$module_id] = true;
        
        // Visit dependencies first
        if (isset($this->dependencies[$module_id])) {
            foreach ($this->dependencies[$module_id] as $dependency) {
                $this->visit_module($dependency, $sorted, $visited, $visiting);
            }
        }
        
        unset($visiting[$module_id]);
        $visited[$module_id] = true;
        $sorted[] = $module_id;
    }
    
    /**
     * Execute a method on all active modules
     * 
     * @param string $method Method name
     * @param array $args Method arguments
     * @return array Results from all modules
     */
    public function execute_on_modules($method, $args = []) {
        $results = [];
        
        foreach ($this->active_modules as $module_id => $instance) {
            if (method_exists($instance, $method)) {
                try {
                    $results[$module_id] = call_user_func_array([$instance, $method], $args);
                } catch (\Exception $e) {
                    error_log("AIA: Error executing '{$method}' on module '{$module_id}': " . $e->getMessage());
                    $results[$module_id] = false;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Get module information
     * 
     * @param string $module_id Module identifier
     * @return array|null
     */
    public function get_module_info($module_id) {
        if (!isset($this->modules[$module_id])) {
            return null;
        }
        
        $module = $this->modules[$module_id];
        $instance = $module['instance'];
        
        $info = [
            'id' => $module_id,
            'class' => $module['class'],
            'active' => $module['active'],
            'dependencies' => $module['dependencies'],
            'name' => $module_id,
            'description' => '',
            'version' => '1.0.0',
        ];
        
        // Get additional info from module instance if available
        if ($instance && method_exists($instance, 'get_info')) {
            $module_info = $instance->get_info();
            $info = array_merge($info, $module_info);
        }
        
        return $info;
    }
}