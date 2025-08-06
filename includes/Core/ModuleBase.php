<?php

namespace AIA\Core;

use AIA\Core\ServiceContainer;
use AIA\Core\SettingsManager;
use AIA\Core\MemoryManager;

/**
 * Module Base Class
 * 
 * Base class for all plugin modules with standardized initialization
 */
abstract class ModuleBase {
    
    /**
     * Module ID
     * 
     * @var string
     */
    protected $module_id;
    
    /**
     * Module information
     * 
     * @var array
     */
    protected $info = [];
    
    /**
     * Service container instance
     * 
     * @var ServiceContainer
     */
    protected $container;
    
    /**
     * Whether module is initialized
     * 
     * @var bool
     */
    protected $initialized = false;
    
    /**
     * Module dependencies (other modules)
     * 
     * @var array
     */
    protected $dependencies = [];
    
    /**
     * Constructor
     * 
     * @param ServiceContainer $container Service container instance
     */
    public function __construct(ServiceContainer $container = null) {
        $this->container = $container ?: ServiceContainer::getInstance();
        $this->module_id = $this->get_module_id();
        $this->info = $this->get_module_info();
    }
    
    /**
     * Initialize the module
     * 
     * @return bool Success status
     */
    public function init() {
        if ($this->initialized) {
            return true;
        }
        
        // Check if module is enabled
        if (!$this->is_enabled()) {
            return false;
        }
        
        // Memory check
        if (!MemoryManager::is_safe_for_operation("module_{$this->module_id}_init", MemoryManager::LEVEL_WARNING)) {
            return false;
        }
        
        try {
            // Initialize dependencies
            if (!$this->initialize_dependencies()) {
                return false;
            }
            
            // Module-specific initialization
            $this->on_init();
            
            // Register hooks
            $this->register_hooks();
            
            $this->initialized = true;
            
            MemoryManager::log_usage("module_{$this->module_id}_initialized");
            
            return true;
            
        } catch (\Exception $e) {
            error_log("AIA: Module '{$this->module_id}' initialization failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Module-specific initialization (to be implemented by child classes)
     */
    protected function on_init() {
        // Override in child classes
    }
    
    /**
     * Register module hooks (to be implemented by child classes)
     */
    protected function register_hooks() {
        // Override in child classes
    }
    
    /**
     * Get module ID (to be implemented by child classes)
     * 
     * @return string Module ID
     */
    abstract protected function get_module_id();
    
    /**
     * Get module information (to be implemented by child classes)
     * 
     * @return array Module information
     */
    abstract protected function get_module_info();
    
    /**
     * Initialize module dependencies
     * 
     * @return bool Success status
     */
    protected function initialize_dependencies() {
        foreach ($this->dependencies as $dependency) {
            try {
                $dep_instance = $this->container->get_module($dependency);
                if (!$dep_instance) {
                    error_log("AIA: Module '{$this->module_id}' dependency '{$dependency}' not available");
                    return false;
                }
            } catch (\Exception $e) {
                error_log("AIA: Module '{$this->module_id}' failed to load dependency '{$dependency}': " . $e->getMessage());
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if module is enabled
     * 
     * @return bool True if enabled
     */
    public function is_enabled() {
        return SettingsManager::is_module_enabled($this->module_id);
    }
    
    /**
     * Check if module is initialized
     * 
     * @return bool True if initialized
     */
    public function is_initialized() {
        return $this->initialized;
    }
    
    /**
     * Get module ID
     * 
     * @return string Module ID
     */
    public function get_id() {
        return $this->module_id;
    }
    
    /**
     * Get module information
     * 
     * @return array Module information
     */
    public function get_info() {
        return $this->info;
    }
    
    /**
     * Get module dependencies
     * 
     * @return array Dependencies
     */
    public function get_dependencies() {
        return $this->dependencies;
    }
    
    /**
     * Get service from container
     * 
     * @param string $service_name Service name
     * @return mixed Service instance
     */
    protected function get_service($service_name) {
        return $this->container->get($service_name);
    }
    
    /**
     * Get another module instance
     * 
     * @param string $module_id Module ID
     * @return mixed|null Module instance
     */
    protected function get_module($module_id) {
        try {
            return $this->container->get_module($module_id);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Get database service
     * 
     * @return Database Database instance
     */
    protected function get_database() {
        return $this->get_service('database');
    }
    
    /**
     * Get settings value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed Setting value
     */
    protected function get_setting($key, $default = null) {
        return SettingsManager::get_setting($key, $default);
    }
    
    /**
     * Log memory usage with module context
     * 
     * @param string $context Context string
     */
    protected function log_memory_usage($context = '') {
        $full_context = "module_{$this->module_id}";
        if ($context) {
            $full_context .= "_{$context}";
        }
        MemoryManager::log_usage($full_context);
    }
    
    /**
     * Check if memory is safe for operation
     * 
     * @param string $operation Operation name
     * @param string $level Required memory level
     * @return bool True if safe
     */
    protected function is_memory_safe($operation = 'general', $level = MemoryManager::LEVEL_WARNING) {
        return MemoryManager::is_safe_for_operation("module_{$this->module_id}_{$operation}", $level);
    }
    
    /**
     * Module cleanup (called during deactivation)
     */
    public function cleanup() {
        // Override in child classes if needed
    }
    
    /**
     * Module activation hook
     */
    public function activate() {
        // Override in child classes if needed
    }
    
    /**
     * Module deactivation hook
     */
    public function deactivate() {
        // Override in child classes if needed
    }
}