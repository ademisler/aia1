<?php

namespace AIA\API;

/**
 * OpenAI API Provider
 * 
 * Handles communication with OpenAI API
 */
class OpenAIProvider {
    
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
    private $api_endpoint = 'https://api.openai.com/v1/chat/completions';
    
    /**
     * Default model
     * 
     * @var string
     */
    private $default_model = 'gpt-3.5-turbo';
    
    /**
     * Constructor
     * 
     * @param string $api_key OpenAI API key
     */
    public function __construct($api_key) {
        $this->api_key = $api_key;
    }
    
    /**
     * Generate response from OpenAI
     * 
     * @param array $conversation Conversation messages
     * @param array $options Additional options
     * @return array Response data
     * @throws Exception
     */
    public function generate_response($conversation, $options = []) {
        $model = $options['model'] ?? $this->default_model;
        $max_tokens = $options['max_tokens'] ?? 1000;
        $temperature = $options['temperature'] ?? 0.7;
        
        $request_body = [
            'model' => $model,
            'messages' => $conversation,
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
            'stream' => false
        ];
        
        $response = $this->make_request($request_body);
        
        if (isset($response['error'])) {
            throw new \Exception('OpenAI API Error: ' . $response['error']['message']);
        }
        
        if (!isset($response['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid response from OpenAI API');
        }
        
        return [
            'content' => $response['choices'][0]['message']['content'],
            'model' => $response['model'] ?? $model,
            'tokens' => $response['usage']['total_tokens'] ?? null,
            'finish_reason' => $response['choices'][0]['finish_reason'] ?? null
        ];
    }
    
    /**
     * Make HTTP request to OpenAI API
     * 
     * @param array $body Request body
     * @return array Response data
     * @throws Exception
     */
    private function make_request($body) {
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
                'User-Agent' => 'AI-Inventory-Agent/1.0.0'
            ],
            'body' => json_encode($body),
            'method' => 'POST',
            'timeout' => 30,
            'data_format' => 'body'
        ];
        
        $response = wp_remote_request($this->api_endpoint, $args);
        
        if (is_wp_error($response)) {
            throw new \Exception('HTTP Error: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            $error_data = json_decode($body, true);
            $error_message = $error_data['error']['message'] ?? 'Unknown error';
            throw new \Exception("OpenAI API returned status {$status_code}: {$error_message}");
        }
        
        $decoded_response = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from OpenAI API');
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
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            'gpt-3.5-turbo-16k' => 'GPT-3.5 Turbo 16K',
            'gpt-4' => 'GPT-4',
            'gpt-4-32k' => 'GPT-4 32K',
            'gpt-4-turbo-preview' => 'GPT-4 Turbo Preview'
        ];
    }
    
    /**
     * Test API connection
     * 
     * @return array Test result
     */
    public function test_connection() {
        try {
            $test_conversation = [
                [
                    'role' => 'user',
                    'content' => 'Hello, this is a test message.'
                ]
            ];
            
            $response = $this->generate_response($test_conversation, [
                'max_tokens' => 10,
                'temperature' => 0
            ]);
            
            return [
                'success' => true,
                'message' => 'Connection successful',
                'model' => $response['model']
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}