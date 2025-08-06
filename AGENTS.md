# AI Inventory Agent - AI Coding Guidelines

## 📋 Table of Contents

1. [AI Architecture Principles](#ai-architecture-principles)
2. [AI Provider Integration](#ai-provider-integration)
3. [Context Management](#context-management)
4. [Prompt Engineering](#prompt-engineering)
5. [Error Handling & Resilience](#error-handling--resilience)
6. [Performance & Rate Limiting](#performance--rate-limiting)
7. [Security & Data Privacy](#security--data-privacy)
8. [Testing AI Components](#testing-ai-components)
9. [Monitoring & Analytics](#monitoring--analytics)
10. [Best Practices](#best-practices)

---

## AI Architecture Principles

### 1. **Provider Abstraction**

Always implement AI providers through a common interface to ensure interchangeability:

```php
interface AIProviderInterface {
    public function generate_response($conversation, $options = []);
    public function validate_api_key($api_key);
    public function get_models();
    public function get_usage_stats();
}
```

### 2. **Modular Design**

AI functionality should be modular and loosely coupled:

```php
class AIChat {
    private $ai_provider;
    private $context_analyzer;
    private $conversation_manager;
    
    public function __construct($provider, $context, $conversation) {
        $this->ai_provider = $provider;
        $this->context_analyzer = $context;
        $this->conversation_manager = $conversation;
    }
}
```

### 3. **Dependency Injection**

Inject dependencies rather than creating them directly:

```php
// ✅ Good
$ai_chat = new AIChat($openai_provider, $inventory_context, $conversation_manager);

// ❌ Bad
class AIChat {
    public function __construct() {
        $this->provider = new OpenAIProvider(); // Hard dependency
    }
}
```

---

## AI Provider Integration

### 1. **Multiple Provider Support**

Support multiple AI providers with graceful fallbacks:

```php
class AIProviderManager {
    private $providers = [];
    private $fallback_order = ['openai', 'gemini', 'local'];
    
    public function get_response($message, $context = []) {
        foreach ($this->fallback_order as $provider_name) {
            try {
                $provider = $this->providers[$provider_name];
                return $provider->generate_response($message, $context);
            } catch (Exception $e) {
                error_log("AI Provider {$provider_name} failed: " . $e->getMessage());
                continue;
            }
        }
        
        throw new Exception('All AI providers failed');
    }
}
```

### 2. **Provider Configuration**

Store provider configurations securely:

```php
class OpenAIProvider implements AIProviderInterface {
    private $api_key;
    private $model;
    private $base_url;
    
    public function __construct($config) {
        $this->api_key = $config['api_key'] ?? '';
        $this->model = $config['model'] ?? 'gpt-3.5-turbo';
        $this->base_url = $config['base_url'] ?? 'https://api.openai.com/v1';
        
        if (empty($this->api_key)) {
            throw new InvalidArgumentException('API key is required');
        }
    }
}
```

### 3. **Request Validation**

Always validate requests before sending to AI providers:

```php
public function generate_response($conversation, $options = []) {
    // Validate conversation structure
    if (!is_array($conversation) || empty($conversation)) {
        throw new InvalidArgumentException('Invalid conversation format');
    }
    
    // Validate message roles
    foreach ($conversation as $message) {
        if (!isset($message['role']) || !isset($message['content'])) {
            throw new InvalidArgumentException('Invalid message format');
        }
        
        if (!in_array($message['role'], ['system', 'user', 'assistant'])) {
            throw new InvalidArgumentException('Invalid message role: ' . $message['role']);
        }
    }
    
    // Sanitize and validate options
    $options = $this->sanitize_options($options);
    
    return $this->make_request($conversation, $options);
}
```

---

## Context Management

### 1. **Inventory Context Analysis**

Analyze user messages to extract inventory-related context:

```php
class InventoryContext {
    private $keywords = [
        'stock_check' => ['stock', 'inventory', 'quantity', 'available'],
        'low_stock' => ['low', 'running out', 'almost empty'],
        'reorder' => ['reorder', 'purchase', 'buy', 'supplier'],
        'forecast' => ['predict', 'forecast', 'future', 'trend']
    ];
    
    public function analyze($message) {
        $context = [
            'intent' => $this->extract_intent($message),
            'products' => $this->extract_products($message),
            'time_frame' => $this->extract_time_frame($message),
            'urgency' => $this->assess_urgency($message)
        ];
        
        return $context;
    }
}
```

### 2. **Dynamic Context Building**

Build context dynamically based on current inventory state:

```php
public function build_inventory_context($user_message) {
    $context = [];
    
    // Current inventory snapshot
    $context['current_stock'] = $this->get_current_stock_summary();
    
    // Recent changes
    $context['recent_changes'] = $this->get_recent_stock_changes(7);
    
    // Low stock alerts
    $context['alerts'] = $this->get_active_alerts();
    
    // User-specific context
    $context['user_preferences'] = $this->get_user_preferences();
    
    return $context;
}
```

### 3. **Context Caching**

Cache expensive context operations:

```php
public function get_inventory_context($cache_key = null) {
    $cache_key = $cache_key ?: 'aia_inventory_context_' . get_current_user_id();
    
    $context = wp_cache_get($cache_key, 'aia_context');
    
    if ($context === false) {
        $context = $this->build_inventory_context();
        wp_cache_set($cache_key, $context, 'aia_context', 300); // 5 minutes
    }
    
    return $context;
}
```

---

## Prompt Engineering

### 1. **System Prompt Design**

Design comprehensive system prompts:

```php
private function build_system_prompt($context = []) {
    $base_prompt = "You are an AI inventory management assistant for a WooCommerce store. ";
    $base_prompt .= "Your role is to help users manage their inventory efficiently. ";
    
    // Add context-specific instructions
    if (!empty($context['current_stock'])) {
        $base_prompt .= "\nCurrent inventory status:\n";
        $base_prompt .= "- Total products: " . $context['current_stock']['total'] . "\n";
        $base_prompt .= "- Low stock items: " . $context['current_stock']['low_stock'] . "\n";
        $base_prompt .= "- Out of stock items: " . $context['current_stock']['out_of_stock'] . "\n";
    }
    
    // Add behavioral guidelines
    $base_prompt .= "\nGuidelines:\n";
    $base_prompt .= "- Provide specific, actionable recommendations\n";
    $base_prompt .= "- Use data from the inventory context when available\n";
    $base_prompt .= "- Be concise but informative\n";
    $base_prompt .= "- Ask for clarification when needed\n";
    
    return apply_filters('aia_system_prompt', $base_prompt, $context);
}
```

### 2. **Dynamic Prompt Enhancement**

Enhance prompts based on user intent:

```php
public function enhance_prompt_for_intent($base_prompt, $intent, $context) {
    switch ($intent) {
        case 'stock_check':
            $base_prompt .= "\nFocus on providing current stock levels and availability.";
            break;
            
        case 'low_stock':
            $base_prompt .= "\nPrioritize identifying low stock items and reorder suggestions.";
            break;
            
        case 'forecast':
            $base_prompt .= "\nUse historical data to provide demand forecasting insights.";
            break;
            
        case 'reorder':
            $base_prompt .= "\nFocus on supplier information and reorder recommendations.";
            break;
    }
    
    return $base_prompt;
}
```

### 3. **Response Formatting**

Structure AI responses consistently:

```php
private function format_ai_response($raw_response, $context) {
    return [
        'content' => $this->sanitize_response_content($raw_response['content']),
        'metadata' => [
            'model' => $raw_response['model'],
            'tokens_used' => $raw_response['tokens'],
            'processing_time' => $context['processing_time'],
            'context_used' => $context['context_summary']
        ],
        'suggestions' => $this->extract_action_suggestions($raw_response['content']),
        'confidence' => $this->calculate_confidence_score($raw_response)
    ];
}
```

---

## Error Handling & Resilience

### 1. **Graceful Degradation**

Handle AI failures gracefully:

```php
public function process_message($message, $session_id = null) {
    try {
        // Try AI processing
        return $this->process_with_ai($message, $session_id);
        
    } catch (AIProviderException $e) {
        // Log the error
        error_log('AI Provider Error: ' . $e->getMessage());
        
        // Fall back to rule-based processing
        return $this->process_with_rules($message, $session_id);
        
    } catch (Exception $e) {
        // Log unexpected errors
        error_log('Unexpected AI Error: ' . $e->getMessage());
        
        // Return helpful error message
        return [
            'success' => false,
            'error' => __('AI service temporarily unavailable. Please try again later.', 'ai-inventory-agent'),
            'fallback' => $this->get_fallback_suggestions($message)
        ];
    }
}
```

### 2. **Retry Logic**

Implement intelligent retry mechanisms:

```php
private function make_request_with_retry($request_data, $max_retries = 3) {
    $attempt = 0;
    $backoff_delay = 1; // seconds
    
    while ($attempt < $max_retries) {
        try {
            return $this->make_api_request($request_data);
            
        } catch (RateLimitException $e) {
            $attempt++;
            if ($attempt >= $max_retries) {
                throw $e;
            }
            
            // Exponential backoff
            sleep($backoff_delay * pow(2, $attempt - 1));
            
        } catch (TemporaryException $e) {
            $attempt++;
            if ($attempt >= $max_retries) {
                throw $e;
            }
            
            sleep($backoff_delay);
            
        } catch (PermanentException $e) {
            // Don't retry permanent errors
            throw $e;
        }
    }
}
```

### 3. **Error Classification**

Classify errors for appropriate handling:

```php
class AIErrorHandler {
    public function classify_error($exception) {
        if (strpos($exception->getMessage(), 'rate limit') !== false) {
            return 'rate_limit';
        }
        
        if (strpos($exception->getMessage(), 'invalid api key') !== false) {
            return 'authentication';
        }
        
        if (strpos($exception->getMessage(), 'timeout') !== false) {
            return 'timeout';
        }
        
        if ($exception->getCode() >= 500) {
            return 'server_error';
        }
        
        return 'unknown';
    }
    
    public function get_user_message($error_type) {
        $messages = [
            'rate_limit' => __('AI service is busy. Please try again in a moment.', 'ai-inventory-agent'),
            'authentication' => __('AI service configuration error. Please contact administrator.', 'ai-inventory-agent'),
            'timeout' => __('Request timed out. Please try again.', 'ai-inventory-agent'),
            'server_error' => __('AI service temporarily unavailable.', 'ai-inventory-agent'),
            'unknown' => __('An unexpected error occurred.', 'ai-inventory-agent')
        ];
        
        return $messages[$error_type] ?? $messages['unknown'];
    }
}
```

---

## Performance & Rate Limiting

### 1. **Request Rate Limiting**

Implement rate limiting to prevent API abuse:

```php
class AIRateLimiter extends RateLimiter {
    public function check_ai_request_limit($user_id, $provider = 'default') {
        $limits = [
            'openai' => ['requests' => 20, 'window' => 60],
            'gemini' => ['requests' => 30, 'window' => 60],
            'default' => ['requests' => 15, 'window' => 60]
        ];
        
        $limit_config = $limits[$provider] ?? $limits['default'];
        
        if (!$this->is_allowed(
            "ai_request_{$provider}",
            $limit_config['requests'],
            $limit_config['window'],
            "user_{$user_id}"
        )) {
            throw new RateLimitException(
                sprintf(
                    __('Rate limit exceeded. Please wait %d seconds before trying again.', 'ai-inventory-agent'),
                    $this->get_remaining_time("ai_request_{$provider}", "user_{$user_id}")
                )
            );
        }
    }
}
```

### 2. **Response Caching**

Cache AI responses for repeated queries:

```php
public function get_cached_response($message, $context_hash) {
    $cache_key = 'aia_ai_response_' . md5($message . $context_hash);
    
    $cached = wp_cache_get($cache_key, 'aia_ai_responses');
    
    if ($cached !== false && $this->is_cache_valid($cached)) {
        return $cached;
    }
    
    return false;
}

public function cache_response($message, $context_hash, $response, $ttl = 3600) {
    $cache_key = 'aia_ai_response_' . md5($message . $context_hash);
    
    $cache_data = [
        'response' => $response,
        'timestamp' => time(),
        'ttl' => $ttl
    ];
    
    wp_cache_set($cache_key, $cache_data, 'aia_ai_responses', $ttl);
}
```

### 3. **Async Processing**

Use async processing for heavy AI operations:

```php
public function process_bulk_analysis($products) {
    // Queue the job for background processing
    wp_schedule_single_event(time(), 'aia_process_bulk_analysis', [$products]);
    
    return [
        'status' => 'queued',
        'job_id' => uniqid('aia_bulk_'),
        'estimated_time' => count($products) * 2 // seconds
    ];
}

// Hook for background processing
add_action('aia_process_bulk_analysis', function($products) {
    $ai_analyzer = new AIInventoryAnalyzer();
    
    foreach ($products as $product) {
        try {
            $analysis = $ai_analyzer->analyze_product($product);
            update_post_meta($product['id'], '_aia_analysis', $analysis);
            
        } catch (Exception $e) {
            error_log("AI Analysis failed for product {$product['id']}: " . $e->getMessage());
        }
    }
});
```

---

## Security & Data Privacy

### 1. **Data Sanitization**

Always sanitize data before sending to AI providers:

```php
private function sanitize_for_ai($data) {
    // Remove sensitive information
    $sensitive_keys = ['password', 'api_key', 'token', 'secret'];
    
    if (is_array($data)) {
        foreach ($sensitive_keys as $key) {
            unset($data[$key]);
        }
        
        // Recursively sanitize nested arrays
        array_walk_recursive($data, function(&$value) {
            if (is_string($value)) {
                $value = sanitize_text_field($value);
            }
        });
    } else if (is_string($data)) {
        $data = sanitize_textarea_field($data);
    }
    
    return $data;
}
```

### 2. **API Key Management**

Secure API key storage and usage:

```php
class AIKeyManager {
    public function store_api_key($provider, $api_key) {
        // Encrypt the API key before storage
        $encrypted_key = $this->encrypt_key($api_key);
        
        update_option("aia_{$provider}_api_key", $encrypted_key);
        
        // Clear any cached instances
        wp_cache_delete("aia_provider_{$provider}", 'aia_providers');
    }
    
    public function get_api_key($provider) {
        $encrypted_key = get_option("aia_{$provider}_api_key");
        
        if (empty($encrypted_key)) {
            return null;
        }
        
        return $this->decrypt_key($encrypted_key);
    }
    
    private function encrypt_key($key) {
        if (!defined('AIA_ENCRYPTION_KEY')) {
            define('AIA_ENCRYPTION_KEY', wp_salt('auth'));
        }
        
        return openssl_encrypt($key, 'AES-256-CBC', AIA_ENCRYPTION_KEY, 0, substr(md5(AIA_ENCRYPTION_KEY), 0, 16));
    }
    
    private function decrypt_key($encrypted_key) {
        if (!defined('AIA_ENCRYPTION_KEY')) {
            define('AIA_ENCRYPTION_KEY', wp_salt('auth'));
        }
        
        return openssl_decrypt($encrypted_key, 'AES-256-CBC', AIA_ENCRYPTION_KEY, 0, substr(md5(AIA_ENCRYPTION_KEY), 0, 16));
    }
}
```

### 3. **Data Retention**

Implement data retention policies:

```php
class AIDataRetention {
    public function cleanup_old_conversations($days = 30) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'aia_chat_sessions';
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table} WHERE created_at < %s",
            $cutoff_date
        ));
        
        error_log("AIA: Cleaned up {$deleted} old conversation records");
        
        return $deleted;
    }
    
    public function anonymize_user_data($user_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'aia_chat_sessions';
        
        $wpdb->update(
            $table,
            ['user_id' => 0, 'user_data' => ''],
            ['user_id' => $user_id],
            ['%d', '%s'],
            ['%d']
        );
    }
}
```

---

## Testing AI Components

### 1. **Mock AI Providers**

Create mock providers for testing:

```php
class MockAIProvider implements AIProviderInterface {
    private $responses = [];
    private $call_count = 0;
    
    public function set_mock_response($response) {
        $this->responses[] = $response;
    }
    
    public function generate_response($conversation, $options = []) {
        if (empty($this->responses)) {
            return [
                'content' => 'Mock AI response',
                'model' => 'mock-model',
                'tokens' => 50
            ];
        }
        
        $response = $this->responses[$this->call_count % count($this->responses)];
        $this->call_count++;
        
        return $response;
    }
    
    public function get_call_count() {
        return $this->call_count;
    }
}
```

### 2. **Integration Tests**

Test AI integration with real scenarios:

```php
class AIIntegrationTest extends WP_UnitTestCase {
    private $ai_chat;
    private $mock_provider;
    
    public function setUp() {
        parent::setUp();
        
        $this->mock_provider = new MockAIProvider();
        $this->ai_chat = new AIChat($this->mock_provider);
    }
    
    public function test_stock_inquiry_response() {
        // Set up mock inventory data
        $this->create_test_products();
        
        // Set expected AI response
        $this->mock_provider->set_mock_response([
            'content' => 'You have 5 products with low stock levels.',
            'model' => 'test-model',
            'tokens' => 25
        ]);
        
        // Test the interaction
        $response = $this->ai_chat->process_message('What products are low on stock?');
        
        $this->assertTrue($response['success']);
        $this->assertStringContains('5 products', $response['data']['response']);
        $this->assertEquals(1, $this->mock_provider->get_call_count());
    }
    
    private function create_test_products() {
        // Create test products with various stock levels
        for ($i = 1; $i <= 10; $i++) {
            $product = WC_Helper_Product::create_simple_product();
            $product->set_stock_quantity($i < 6 ? 2 : 20); // First 5 are low stock
            $product->save();
        }
    }
}
```

### 3. **Performance Tests**

Test AI response times and resource usage:

```php
public function test_ai_response_performance() {
    $start_time = microtime(true);
    $start_memory = memory_get_usage();
    
    $response = $this->ai_chat->process_message('Generate inventory report');
    
    $end_time = microtime(true);
    $end_memory = memory_get_usage();
    
    $processing_time = $end_time - $start_time;
    $memory_used = $end_memory - $start_memory;
    
    // Assert performance constraints
    $this->assertLessThan(5.0, $processing_time, 'AI response took too long');
    $this->assertLessThan(10 * 1024 * 1024, $memory_used, 'AI processing used too much memory');
}
```

---

## Monitoring & Analytics

### 1. **AI Usage Tracking**

Track AI usage for analytics:

```php
class AIUsageTracker {
    public function track_request($provider, $tokens_used, $processing_time, $success = true) {
        $usage_data = [
            'provider' => $provider,
            'tokens_used' => $tokens_used,
            'processing_time' => $processing_time,
            'success' => $success,
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id()
        ];
        
        // Store in database
        $this->store_usage_data($usage_data);
        
        // Update daily counters
        $this->update_daily_counters($provider, $tokens_used, $success);
    }
    
    public function get_usage_stats($period = 'month') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'aia_usage_stats';
        $date_condition = $this->get_date_condition($period);
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT provider, 
                    COUNT(*) as requests,
                    SUM(tokens_used) as total_tokens,
                    AVG(processing_time) as avg_processing_time,
                    SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_requests
             FROM {$table} 
             WHERE {$date_condition}
             GROUP BY provider",
            $this->get_date_params($period)
        ));
    }
}
```

### 2. **Error Monitoring**

Monitor AI errors and failures:

```php
class AIErrorMonitor {
    public function log_error($provider, $error_type, $error_message, $context = []) {
        $error_data = [
            'provider' => $provider,
            'error_type' => $error_type,
            'error_message' => $error_message,
            'context' => json_encode($context),
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id()
        ];
        
        // Store error
        $this->store_error($error_data);
        
        // Check if we need to alert administrators
        $this->check_error_threshold($provider, $error_type);
    }
    
    private function check_error_threshold($provider, $error_type) {
        $error_count = $this->get_recent_error_count($provider, $error_type, 60); // Last hour
        
        if ($error_count >= 10) {
            $this->send_admin_alert($provider, $error_type, $error_count);
        }
    }
}
```

### 3. **Performance Metrics**

Collect performance metrics:

```php
class AIPerformanceCollector {
    public function collect_metrics($operation, $start_time, $end_time, $metadata = []) {
        $metrics = [
            'operation' => $operation,
            'duration' => $end_time - $start_time,
            'memory_peak' => memory_get_peak_usage(),
            'timestamp' => $start_time,
            'metadata' => $metadata
        ];
        
        // Send to metrics collector
        $this->send_metrics($metrics);
        
        // Update performance dashboard
        $this->update_dashboard_metrics($operation, $metrics);
    }
}
```

---

## Best Practices

### 1. **Code Organization**

```php
// ✅ Good: Organized, single responsibility
class AIInventoryAnalyzer {
    private $ai_provider;
    private $inventory_context;
    
    public function analyze_stock_levels($products) {
        $context = $this->inventory_context->build_context($products);
        return $this->ai_provider->generate_response($this->build_analysis_prompt($context));
    }
}

// ❌ Bad: Everything in one class
class AIEverything {
    public function do_everything($data) {
        // 500 lines of mixed responsibilities
    }
}
```

### 2. **Configuration Management**

```php
// ✅ Good: Centralized configuration
class AIConfig {
    const DEFAULT_SETTINGS = [
        'max_tokens' => 1000,
        'temperature' => 0.7,
        'timeout' => 30,
        'retry_attempts' => 3
    ];
    
    public static function get($key, $default = null) {
        $settings = get_option('aia_ai_settings', self::DEFAULT_SETTINGS);
        return $settings[$key] ?? $default;
    }
}

// Usage
$max_tokens = AIConfig::get('max_tokens');
```

### 3. **Logging Standards**

```php
// ✅ Good: Structured logging
class AILogger {
    public function log_ai_request($provider, $request_data, $response_data, $performance_metrics) {
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'provider' => $provider,
            'request_hash' => md5(json_encode($request_data)),
            'response_tokens' => $response_data['tokens'] ?? 0,
            'processing_time' => $performance_metrics['duration'],
            'success' => $response_data['success'] ?? false
        ];
        
        error_log('AIA_AI_REQUEST: ' . json_encode($log_entry));
    }
}
```

### 4. **Documentation Standards**

```php
/**
 * Process inventory message with AI
 * 
 * @param string $message User message
 * @param array $context Optional context data
 * @param array $options AI provider options
 * 
 * @return array {
 *     @type bool   $success Whether the request was successful
 *     @type string $response AI generated response
 *     @type array  $metadata Response metadata including tokens, model, etc.
 *     @type array  $suggestions Extracted action suggestions
 * }
 * 
 * @throws AIProviderException When AI provider fails
 * @throws RateLimitException When rate limit is exceeded
 * 
 * @since 1.0.0
 */
public function process_inventory_message($message, $context = [], $options = []) {
    // Implementation
}
```

### 5. **Version Compatibility**

```php
// Handle different AI provider API versions
class OpenAIProvider implements AIProviderInterface {
    private $api_version;
    
    public function __construct($config) {
        $this->api_version = $config['api_version'] ?? 'v1';
    }
    
    public function generate_response($conversation, $options = []) {
        switch ($this->api_version) {
            case 'v1':
                return $this->generate_response_v1($conversation, $options);
            case 'v2':
                return $this->generate_response_v2($conversation, $options);
            default:
                throw new UnsupportedVersionException("API version {$this->api_version} not supported");
        }
    }
}
```

---

## Implementation Checklist

### ✅ **Pre-Implementation**
- [ ] Define clear AI use cases and requirements
- [ ] Choose appropriate AI providers
- [ ] Design provider abstraction layer
- [ ] Plan error handling strategy
- [ ] Define performance requirements

### ✅ **During Implementation**
- [ ] Implement provider interface
- [ ] Add comprehensive error handling
- [ ] Implement rate limiting
- [ ] Add request/response validation
- [ ] Include security measures
- [ ] Write unit tests

### ✅ **Post-Implementation**
- [ ] Set up monitoring and logging
- [ ] Configure performance alerts
- [ ] Document API usage
- [ ] Train team on best practices
- [ ] Plan for provider updates

### ✅ **Ongoing Maintenance**
- [ ] Monitor usage and costs
- [ ] Review and update prompts
- [ ] Analyze error patterns
- [ ] Optimize performance
- [ ] Update documentation

---

## Resources

- [OpenAI API Documentation](https://platform.openai.com/docs)
- [Google Gemini API Documentation](https://ai.google.dev/docs)
- [WordPress Plugin Development](https://developer.wordpress.org/plugins/)
- [WooCommerce Development](https://woocommerce.github.io/code-reference/)
- [AI Safety Guidelines](https://www.anthropic.com/ai-safety)

---

**Remember:** AI integration should enhance user experience while maintaining security, performance, and reliability standards. Always test thoroughly and monitor in production.