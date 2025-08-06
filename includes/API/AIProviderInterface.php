<?php

namespace AIA\API;

/**
 * AI Provider Interface
 * 
 * Standardized interface for all AI providers
 */
interface AIProviderInterface {
    
    /**
     * Initialize the provider with API key
     * 
     * @param string $api_key API key
     * @throws \Exception If initialization fails
     */
    public function __construct($api_key);
    
    /**
     * Generate AI response from conversation
     * 
     * @param array $conversation Conversation history
     * @param array $options Additional options
     * @return array Response with content, tokens, etc.
     * @throws \Exception If API call fails
     */
    public function generate_response($conversation, $options = []);
    
    /**
     * Validate API key
     * 
     * @param string $api_key API key to validate
     * @return array Validation result with success status and details
     */
    public function validate_api_key($api_key);
    
    /**
     * Get available models
     * 
     * @return array List of available models
     */
    public function get_models();
    
    /**
     * Get provider name
     * 
     * @return string Provider name
     */
    public function get_name();
    
    /**
     * Get provider capabilities
     * 
     * @return array Provider capabilities
     */
    public function get_capabilities();
    
    /**
     * Get rate limits information
     * 
     * @return array Rate limits
     */
    public function get_rate_limits();
    
    /**
     * Check if provider is available
     * 
     * @return bool True if available
     */
    public function is_available();
    
    /**
     * Get usage statistics
     * 
     * @return array Usage statistics
     */
    public function get_usage_stats();
    
    /**
     * Test connection to provider
     * 
     * @return array Test result
     */
    public function test_connection();
}