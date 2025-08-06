<?php

namespace AIA\API;

/**
 * Gemini API Provider
 * 
 * Handles communication with Google Gemini API
 */
class GeminiProvider {
    
    /**
     * API key
     * 
     * @var string
     */
    private $api_key;
    
    /**
     * API endpoint
     * 
     * @var string
     */
    private $api_endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/';
    
    /**
     * Default model
     * 
     * @var string
     */
    private $default_model = 'gemini-2.0-flash';
    
    /**
     * Constructor
     * 
     * @param string $api_key Gemini API key
     */
    public function __construct($api_key) {
        $this->api_key = $api_key;
    }
    
    /**
     * Generate response from Gemini
     * 
     * @param array $conversation Conversation messages
     * @param array $options Additional options
     * @return array Response data
     * @throws Exception
     */
    public function generate_response($conversation, $options = []) {
        $model = $options['model'] ?? $this->default_model;
        $temperature = $options['temperature'] ?? 0.7;
        $max_tokens = $options['max_tokens'] ?? 1000;
        
        // Convert OpenAI format to Gemini format
        $gemini_messages = $this->convert_conversation_format($conversation);
        
        $request_body = [
            'contents' => $gemini_messages['contents'],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => $max_tokens,
                'topP' => 0.8,
                'topK' => 10
            ]
        ];
        
        // Add system instruction if present
        if (!empty($gemini_messages['system_instruction'])) {
            $request_body['systemInstruction'] = [
                'parts' => [
                    ['text' => $gemini_messages['system_instruction']]
                ]
            ];
        }
        
        $endpoint = $this->api_endpoint . $model . ':generateContent';
        
        $response = $this->make_request($endpoint, $request_body);
        
        if (isset($response['error'])) {
            throw new \Exception('Gemini API Error: ' . $response['error']['message']);
        }
        
        if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \Exception('Invalid response from Gemini API');
        }
        
        return [
            'content' => $response['candidates'][0]['content']['parts'][0]['text'],
            'model' => $model,
            'tokens' => $response['usageMetadata']['totalTokenCount'] ?? null,
            'finish_reason' => $response['candidates'][0]['finishReason'] ?? null
        ];
    }
    
    /**
     * Convert OpenAI conversation format to Gemini format
     * 
     * @param array $conversation OpenAI format conversation
     * @return array Gemini format conversation
     */
    private function convert_conversation_format($conversation) {
        $contents = [];
        $system_instruction = '';
        
        foreach ($conversation as $message) {
            if ($message['role'] === 'system') {
                $system_instruction = $message['content'];
                continue;
            }
            
            $role = $message['role'] === 'assistant' ? 'model' : 'user';
            
            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $message['content']]
                ]
            ];
        }
        
        return [
            'contents' => $contents,
            'system_instruction' => $system_instruction
        ];
    }
    
    /**
     * Make HTTP request to Gemini API
     * 
     * @param string $endpoint API endpoint
     * @param array $body Request body
     * @return array Response data
     * @throws Exception
     */
    private function make_request($endpoint, $body) {
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('AIA Gemini: Making request to ' . $endpoint);
            error_log('AIA Gemini: Request body: ' . json_encode($body, JSON_PRETTY_PRINT));
        }
        
        $args = [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->api_key,  // Ensure lowercase header name
                'User-Agent' => 'AI-Inventory-Agent/2.2.5'
            ],
            'body' => json_encode($body),
            'method' => 'POST',
            'timeout' => 30,
            'data_format' => 'body'
        ];
        
        $response = wp_remote_request($endpoint, $args);
        
        if (is_wp_error($response)) {
            $error_message = 'HTTP Error: ' . $response->get_error_message();
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('AIA Gemini: ' . $error_message);
            }
            throw new \Exception($error_message);
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('AIA Gemini: Response status: ' . $status_code);
            error_log('AIA Gemini: Response body: ' . $response_body);
        }
        
        if ($status_code !== 200) {
            $error_data = json_decode($response_body, true);
            $error_message = 'Unknown error';
            
            if (isset($error_data['error']['message'])) {
                $error_message = $error_data['error']['message'];
            } elseif (isset($error_data['error'])) {
                $error_message = is_string($error_data['error']) ? $error_data['error'] : json_encode($error_data['error']);
            } else {
                $error_message = $response_body;
            }
            
            // Add specific error messages for common issues
            if ($status_code === 400) {
                $error_message = "Bad Request: {$error_message}. Please check your API key and request format.";
            } elseif ($status_code === 401) {
                $error_message = "Authentication failed: Invalid API key. Please check your Gemini API key.";
            } elseif ($status_code === 403) {
                $error_message = "Access forbidden: {$error_message}. Please check your API key permissions and ensure the Gemini API is enabled.";
            } elseif ($status_code === 429) {
                $error_message = "Rate limit exceeded: {$error_message}. Please try again later.";
            } elseif ($status_code === 404) {
                $error_message = "API endpoint not found. Please check if you're using the correct Gemini API model.";
            }
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("AIA Gemini: API Error ({$status_code}): {$error_message}");
            }
            
            throw new \Exception("Gemini API Error ({$status_code}): {$error_message}");
        }
        
        $decoded_response = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_message = 'Invalid JSON response from Gemini API: ' . json_last_error_msg();
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('AIA Gemini: ' . $error_message);
            }
            throw new \Exception($error_message);
        }
        
        return $decoded_response;
    }
    
    /**
     * Get available models
     * 
     * @return array Available models
     */
    public function get_available_models() {
        return [
            'gemini-2.0-flash' => 'Gemini 2.0 Flash (Latest)',
            'gemini-1.5-pro' => 'Gemini 1.5 Pro',
            'gemini-1.5-flash' => 'Gemini 1.5 Flash',
            'gemini-pro' => 'Gemini Pro (Legacy)'
        ];
    }
    
    /**
     * Test API connection
     * 
     * @return array Test result
     */
    public function test_connection() {
        try {
            // Validate API key format
            if (empty($this->api_key)) {
                throw new \Exception('API key is empty');
            }
            
            // Gemini API keys are typically 39 characters long
            if (strlen($this->api_key) < 30) {
                throw new \Exception('API key seems too short. Gemini API keys are typically 39 characters long.');
            }
            
            // More flexible validation - just check if it looks like a valid API key
            if (!preg_match('/^[A-Za-z0-9_-]+$/', $this->api_key)) {
                throw new \Exception('API key contains invalid characters. Please check your Gemini API key format.');
            }
            
            $test_conversation = [
                [
                    'role' => 'user',
                    'content' => 'Hello, please respond with just "OK" to test the connection.'
                ]
            ];
            
            $response = $this->generate_response($test_conversation, [
                'max_tokens' => 10,
                'temperature' => 0
            ]);
            
            return [
                'success' => true,
                'message' => 'Connection successful! Model: ' . $response['model'],
                'model' => $response['model'],
                'response' => $response['content'],
                'api_key_length' => strlen($this->api_key),
                'tokens_used' => $response['tokens'] ?? 0
            ];
            
        } catch (\Exception $e) {
            // Add debugging information
            $debug_info = [
                'api_key_length' => strlen($this->api_key),
                'api_key_starts_with' => substr($this->api_key, 0, 6) . '...',
                'endpoint' => $this->api_endpoint . $this->default_model . ':generateContent',
                'error_message' => $e->getMessage()
            ];
            
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'debug' => $debug_info
            ];
        }
    }
}