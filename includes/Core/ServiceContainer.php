<?php

namespace AIA\Core;

/**
 * Service Container Class
 * 
 * Manages service registration and dependency injection to eliminate circular dependencies
 */
class ServiceContainer {
    
    /**
     * Container instance (Singleton)
     * 
     * @var ServiceContainer|null
     */
    private static $instance = null;
    
    /**
     * Registered services
     * 
     * @var array
     */
    private $services = [];
    
    /**
     * Service instances
     * 
     * @var array
     */
    private $instances = [];
    
    /**
     * Service definitions
     * 
     * @var array
     */
    private $definitions = [];
    
    /**
     * Currently resolving services (to detect circular dependencies)
     * 
     * @var array
     */
    private $resolving = [];
    
    /**
     * Get container instance
     * 
     * @return ServiceContainer
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor to enforce singleton
     */
    private function __construct() {
        $this->register_core_services();
    }
    
    /**
     * Register core services
     */
    private function register_core_services() {
        // Register core services
        $this->register('memory_manager', function() {
            return new MemoryManager();
        });
        
        $this->register('settings_manager', function() {
            return new SettingsManager();
        });
        
        $this->register('database', function() {
            return new Database();
        });
        
        $this->register('module_manager', function($container) {
            return new ModuleManager($container);
        });
        
        $this->register('admin_interface', function($container) {
            if (!is_admin()) {
                return null;
            }
            return new \AIA\Admin\AdminInterface($container);
        });
        
        $this->register('ai_provider_manager', function($container) {
            return new \AIA\API\AIProviderManager();
        });
        
        $this->register('integration_validator', function($container) {
            return new \AIA\Core\IntegrationValidator();
        });
    }
    
    /**
     * Register a service
     * 
     * @param string $name Service name
     * @param callable $factory Factory function
     * @param bool $singleton Whether to create as singleton
     */
    public function register($name, callable $factory, $singleton = true) {
        $this->services[$name] = [
            'factory' => $factory,
            'singleton' => $singleton
        ];
    }
    
    /**
     * Register a service definition with dependencies
     * 
     * @param string $name Service name
     * @param string $class Class name
     * @param array $dependencies Service dependencies
     * @param bool $singleton Whether to create as singleton
     */
    public function define($name, $class, array $dependencies = [], $singleton = true) {
        $this->definitions[$name] = [
            'class' => $class,
            'dependencies' => $dependencies,
            'singleton' => $singleton
        ];
    }
    
    /**
     * Get a service instance
     * 
     * @param string $name Service name
     * @return mixed Service instance
     * @throws \Exception If circular dependency detected or service not found
     */
    public function get($name) {
        // Check if already resolving (circular dependency)
        if (in_array($name, $this->resolving)) {
            throw new \Exception("Circular dependency detected for service: {$name}");
        }
        
        // Return existing singleton instance
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }
        
        // Mark as resolving
        $this->resolving[] = $name;
        
        try {
            $instance = null;
            
            // Check if it's a registered service
            if (isset($this->services[$name])) {
                $service = $this->services[$name];
                $instance = $service['factory']($this);
                
                // Store as singleton if configured
                if ($service['singleton'] && $instance !== null) {
                    $this->instances[$name] = $instance;
                }
            }
            // Check if it's a defined service
            elseif (isset($this->definitions[$name])) {
                $definition = $this->definitions[$name];
                $instance = $this->create_from_definition($definition);
                
                // Store as singleton if configured
                if ($definition['singleton'] && $instance !== null) {
                    $this->instances[$name] = $instance;
                }
            }
            else {
                throw new \Exception("Service not found: {$name}");
            }
            
            // Remove from resolving
            $this->resolving = array_diff($this->resolving, [$name]);
            
            return $instance;
            
        } catch (\Exception $e) {
            // Remove from resolving on error
            $this->resolving = array_diff($this->resolving, [$name]);
            throw $e;
        }
    }
    
    /**
     * Create instance from definition
     * 
     * @param array $definition Service definition
     * @return mixed Service instance
     */
    private function create_from_definition($definition) {
        $class = $definition['class'];
        $dependencies = $definition['dependencies'];
        
        if (empty($dependencies)) {
            return new $class();
        }
        
        // Resolve dependencies
        $resolved_dependencies = [];
        foreach ($dependencies as $dependency) {
            $resolved_dependencies[] = $this->get($dependency);
        }
        
        // Create instance with dependencies
        $reflection = new \ReflectionClass($class);
        return $reflection->newInstanceArgs($resolved_dependencies);
    }
    
    /**
     * Check if service exists
     * 
     * @param string $name Service name
     * @return bool
     */
    public function has($name) {
        return isset($this->services[$name]) || isset($this->definitions[$name]);
    }
    
    /**
     * Set a service instance directly
     * 
     * @param string $name Service name
     * @param mixed $instance Service instance
     */
    public function set($name, $instance) {
        $this->instances[$name] = $instance;
    }
    
    /**
     * Remove a service
     * 
     * @param string $name Service name
     */
    public function remove($name) {
        unset($this->services[$name], $this->definitions[$name], $this->instances[$name]);
    }
    
    /**
     * Get all registered service names
     * 
     * @return array Service names
     */
    public function getServiceNames() {
        return array_merge(
            array_keys($this->services),
            array_keys($this->definitions)
        );
    }
    
    /**
     * Clear all instances (for testing)
     */
    public function clearInstances() {
        $this->instances = [];
    }
    
    /**
     * Get service definition for debugging
     * 
     * @param string $name Service name
     * @return array|null Service definition
     */
    public function getDefinition($name) {
        if (isset($this->services[$name])) {
            return $this->services[$name];
        }
        
        if (isset($this->definitions[$name])) {
            return $this->definitions[$name];
        }
        
        return null;
    }
    
    /**
     * Register module services
     * 
     * @param array $modules Module configurations
     */
    public function register_modules(array $modules) {
        foreach ($modules as $module_id => $config) {
            $this->define(
                "module_{$module_id}",
                $config['class'],
                $config['dependencies'] ?? [],
                true
            );
        }
    }
    
    /**
     * Get module instance
     * 
     * @param string $module_id Module ID
     * @return mixed Module instance
     */
    public function get_module($module_id) {
        return $this->get("module_{$module_id}");
    }
    
    /**
     * Check if module is available
     * 
     * @param string $module_id Module ID
     * @return bool
     */
    public function has_module($module_id) {
        return $this->has("module_{$module_id}");
    }
}