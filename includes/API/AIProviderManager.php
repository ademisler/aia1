<?php

namespace AIA\API;

use AIA\Core\MemoryManager;
use AIA\Core\SettingsManager;
use AIA\Utils\RateLimiter;

/**
 * AI Provider Manager
 * 
 * Centralized management of AI providers with error handling and fallbacks
 */
class AIProviderManager {
    
    /**
     * Registered providers
     * 
     * @var array
     */
    private $providers = [];
    
    /**
     * Active provider instance
     * 
     * @var AIProviderInterface|null
     */
    private $active_provider = null;
    
    /**
     * Fallback providers
     * 
     * @var array
     */
    private $fallback_providers = [];
    
    /**
     * Rate limiter instance
     * 
     * @var RateLimiter
     */
    private $rate_limiter;
    
    /**
     * Error history
     * 
     * @var array
     */
    private $error_history = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->rate_limiter = new RateLimiter();
        $this->register_default_providers();
        $this->initialize_active_provider();
    }
    
    /**
     * Register default providers
     */
    private function register_default_providers() {
        $this->providers = [
            'openai' => [
                'class' => '\\AIA\\API\\OpenAIProvider',
                'name' => 'OpenAI GPT',
                'priority' => 1,
                'capabilities' => ['chat', 'completion', 'embedding']
            ],
            'gemini' => [
                'class' => '\\AIA\\API\\GeminiProvider', 
                'name' => 'Google Gemini',
                'priority' => 2,
                'capabilities' => ['chat', 'completion']
            ]
        ];
    }
    
    /**
     * Initialize active provider based on settings
     */
    private function initialize_active_provider() {
        $provider_name = SettingsManager::get_setting('ai_provider', 'openai');
        $api_key = SettingsManager::get_setting('api_key', '');
        
        if (empty($api_key)) {
            return;
        }
        
        try {
            $this->active_provider = $this->create_provider($provider_name, $api_key);
        } catch (\Exception $e) {
            error_log("AIA: Failed to initialize primary AI provider '{$provider_name}': " . $e->getMessage());
            $this->try_fallback_providers($api_key);
        }
    }
    
    /**
     * Create provider instance
     * 
     * @param string $provider_name Provider name
     * @param string $api_key API key
     * @return AIProviderInterface Provider instance
     * @throws \Exception If provider creation fails
     */
    private function create_provider($provider_name, $api_key) {
        if (!isset($this->providers[$provider_name])) {
            throw new \Exception("Unknown AI provider: {$provider_name}");
        }
        
        $provider_config = $this->providers[$provider_name];
        $class_name = $provider_config['class'];
        
        if (!class_exists($class_name)) {
            throw new \Exception("Provider class not found: {$class_name}");
        }
        
        return new $class_name($api_key);
    }
    
    /**
     * Try fallback providers
     * 
     * @param string $api_key API key
     */
    private function try_fallback_providers($api_key) {
        $primary_provider = SettingsManager::get_setting('ai_provider', 'openai');
        
        foreach ($this->providers as $provider_name => $config) {
            if ($provider_name === $primary_provider) {
                continue; // Skip primary provider
            }
            
            try {
                $provider = $this->create_provider($provider_name, $api_key);
                $test_result = $provider->test_connection();
                
                if ($test_result['success']) {
                    $this->active_provider = $provider;
                    $this->fallback_providers[] = $provider_name;
                    
                    error_log("AIA: Successfully switched to fallback provider: {$provider_name}");
                    break;
                }
            } catch (\Exception $e) {
                error_log("AIA: Fallback provider '{$provider_name}' also failed: " . $e->getMessage());
                continue;
            }
        }
    }
    
    /**
     * Generate AI response with error handling and retries
     * 
     * @param array $conversation Conversation history
     * @param array $options Additional options
     * @return array Response data
     * @throws \Exception If all providers fail
     */
    public function generate_response($conversation, $options = []) {
        if (!$this->active_provider) {
            throw new \Exception('No AI provider available');
        }
        
        // Memory check
        if (!MemoryManager::is_safe_for_operation('ai_request', MemoryManager::LEVEL_WARNING)) {
            throw new \Exception('Insufficient memory for AI request');
        }
        
        // Rate limiting check
        if (!$this->rate_limiter->is_allowed('ai_request')) {
            throw new \Exception('Rate limit exceeded for AI requests');
        }
        
        $attempts = 0;
        $max_attempts = 3;
        $last_exception = null;
        
        while ($attempts < $max_attempts) {
            try {
                MemoryManager::log_usage('ai_request_start');
                
                $response = $this->active_provider->generate_response($conversation, $options);
                
                // Log successful request
                $this->rate_limiter->record_request('ai_request');
                MemoryManager::log_usage('ai_request_success');
                
                return $response;
                
            } catch (\Exception $e) {
                $attempts++;
                $last_exception = $e;
                
                $this->log_error($e, $attempts);
                
                // Try to switch provider on certain errors
                if ($this->should_switch_provider($e) && $attempts < $max_attempts) {
                    $this->try_switch_provider();
                }
                
                // Wait before retry
                if ($attempts < $max_attempts) {
                    sleep(min($attempts * 2, 10)); // Exponential backoff, max 10 seconds
                }
            }
        }
        
        // All attempts failed
        throw new \Exception("AI request failed after {$max_attempts} attempts. Last error: " . $last_exception->getMessage());
    }
    
    /**
     * Check if should switch provider based on error
     * 
     * @param \Exception $exception Exception to check
     * @return bool True if should switch
     */
    private function should_switch_provider(\Exception $exception) {
        $message = strtolower($exception->getMessage());
        
        // Switch on these error types
        $switch_triggers = [
            'rate limit',
            'quota exceeded',
            'service unavailable',
            'timeout',
            'network error'
        ];
        
        foreach ($switch_triggers as $trigger) {
            if (strpos($message, $trigger) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Try to switch to different provider
     */
    private function try_switch_provider() {
        $current_provider = $this->get_active_provider_name();
        $api_key = SettingsManager::get_setting('api_key', '');
        
        foreach ($this->providers as $provider_name => $config) {
            if ($provider_name === $current_provider) {
                continue;
            }
            
            try {
                $provider = $this->create_provider($provider_name, $api_key);
                $test_result = $provider->test_connection();
                
                if ($test_result['success']) {
                    $this->active_provider = $provider;
                    error_log("AIA: Switched to provider: {$provider_name}");
                    return;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
    }
    
    /**
     * Get active provider name
     * 
     * @return string|null Provider name
     */
    public function get_active_provider_name() {
        if (!$this->active_provider) {
            return null;
        }
        
        foreach ($this->providers as $name => $config) {
            if (get_class($this->active_provider) === $config['class']) {
                return $name;
            }
        }
        
        return null;
    }
    
    /**
     * Test connection to active provider
     * 
     * @return array Test result
     */
    public function test_connection() {
        if (!$this->active_provider) {
            return [
                'success' => false,
                'error' => 'No active provider',
                'provider' => null
            ];
        }
        
        try {
            $result = $this->active_provider->test_connection();
            $result['provider'] = $this->get_active_provider_name();
            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $this->get_active_provider_name()
            ];
        }
    }
    
    /**
     * Validate API key for specific provider
     * 
     * @param string $provider_name Provider name
     * @param string $api_key API key
     * @return array Validation result
     */
    public function validate_api_key($provider_name, $api_key) {
        try {
            $provider = $this->create_provider($provider_name, $api_key);
            return $provider->validate_api_key($api_key);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider_name
            ];
        }
    }
    
    /**
     * Get available providers
     * 
     * @return array Available providers
     */
    public function get_available_providers() {
        return $this->providers;
    }
    
    /**
     * Get provider capabilities
     * 
     * @param string $provider_name Provider name
     * @return array Capabilities
     */
    public function get_provider_capabilities($provider_name) {
        return $this->providers[$provider_name]['capabilities'] ?? [];
    }
    
    /**
     * Check if provider is available
     * 
     * @return bool True if available
     */
    public function is_available() {
        return $this->active_provider !== null;
    }
    
    /**
     * Log error with context
     * 
     * @param \Exception $exception Exception
     * @param int $attempt Attempt number
     */
    private function log_error(\Exception $exception, $attempt) {
        $error_data = [
            'timestamp' => time(),
            'provider' => $this->get_active_provider_name(),
            'attempt' => $attempt,
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'memory_usage' => MemoryManager::get_current_usage()
        ];
        
        $this->error_history[] = $error_data;
        
        // Keep only last 50 errors
        if (count($this->error_history) > 50) {
            $this->error_history = array_slice($this->error_history, -50);
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("AIA API Error (attempt {$attempt}): " . json_encode($error_data));
        }
    }
    
    /**
     * Get error history
     * 
     * @return array Error history
     */
    public function get_error_history() {
        return $this->error_history;
    }
    
    /**
     * Clear error history
     */
    public function clear_error_history() {
        $this->error_history = [];
    }
    
    /**
     * Get provider statistics
     * 
     * @return array Statistics
     */
    public function get_statistics() {
        $stats = [
            'active_provider' => $this->get_active_provider_name(),
            'fallback_providers' => $this->fallback_providers,
            'error_count' => count($this->error_history),
            'rate_limit_status' => $this->rate_limiter->get_status('ai_request'),
            'memory_usage' => MemoryManager::get_stats()
        ];
        
        if ($this->active_provider) {
            try {
                $stats['provider_stats'] = $this->active_provider->get_usage_stats();
            } catch (\Exception $e) {
                $stats['provider_stats'] = ['error' => $e->getMessage()];
            }
        }
        
        return $stats;
    }
    
    /**
     * Reset provider (force reinitialization)
     */
    public function reset() {
        $this->active_provider = null;
        $this->fallback_providers = [];
        $this->error_history = [];
        $this->initialize_active_provider();
    }
}