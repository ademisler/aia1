<?php

namespace AIA\Modules;

use AIA\Core\Plugin;
use AIA\API\OpenAIProvider;
use AIA\API\GeminiProvider;
use AIA\Utils\InventoryContext;

/**
 * AI Chat Module
 * 
 * Handles AI-powered chat functionality for inventory management
 */
class AIChat {
    
    /**
     * Module information
     * 
     * @var array
     */
    private $info = [
        'name' => 'AI Chat Assistant',
        'description' => 'AI-powered chat interface for inventory management assistance',
        'version' => '1.0.0'
    ];
    
    /**
     * AI provider instance
     * 
     * @var object
     */
    private $ai_provider;
    
    /**
     * Plugin instance
     * 
     * @var Plugin
     */
    private $plugin;
    
    /**
     * Inventory context helper
     * 
     * @var InventoryContext
     */
    private $inventory_context;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin = Plugin::get_instance();
        $this->inventory_context = new InventoryContext();
    }
    
    /**
     * Initialize the module
     */
    public function init() {
        // Initialize AI provider
        $this->init_ai_provider();
        
        // Register hooks
        $this->register_hooks();
    }
    
    /**
     * Initialize AI provider based on settings
     */
    private function init_ai_provider() {
        $provider = $this->plugin->get_setting('ai_provider');
        $api_key = $this->plugin->get_setting('api_key');
        
        if (empty($api_key)) {
            error_log('AIA: No API key configured for AI provider');
            return;
        }
        
        switch ($provider) {
            case 'openai':
                $this->ai_provider = new OpenAIProvider($api_key);
                break;
            case 'gemini':
                $this->ai_provider = new GeminiProvider($api_key);
                break;
            default:
                error_log("AIA: Unknown AI provider: {$provider}");
                break;
        }
    }
    
    /**
     * Register module hooks
     */
    private function register_hooks() {
        // AJAX handlers for chat
        add_action('wp_ajax_aia_send_message', [$this, 'handle_send_message']);
        add_action('wp_ajax_aia_get_chat_history', [$this, 'handle_get_chat_history']);
        add_action('wp_ajax_aia_clear_chat_history', [$this, 'handle_clear_chat_history']);
        
        // Shortcode for frontend chat
        add_shortcode('aia_chat', [$this, 'render_chat_shortcode']);
    }
    
    /**
     * Process a chat message
     * 
     * @param string $message User message
     * @param string $session_id Chat session ID
     * @return array Response data
     */
    public function process_message($message, $session_id = null) {
        if (!$this->ai_provider) {
            return [
                'success' => false,
                'error' => __('AI provider not configured', 'ai-inventory-agent')
            ];
        }
        
        $session_id = $session_id ?: $this->generate_session_id();
        
        // Get inventory context
        $context = $this->get_inventory_context($message);
        
        // Get chat history for context
        $chat_history = $this->get_chat_history($session_id, 10);
        
        // Build system prompt
        $system_prompt = $this->build_system_prompt($context);
        
        // Prepare conversation for AI
        $conversation = $this->prepare_conversation($chat_history, $message, $system_prompt);
        
        try {
            $start_time = microtime(true);
            
            // Send to AI provider
            $ai_response = $this->ai_provider->generate_response($conversation);
            
            $processing_time = microtime(true) - $start_time;
            
            // Save conversation to database
            $this->save_conversation($session_id, 'user', $message);
            $this->save_conversation($session_id, 'assistant', $ai_response['content'], [
                'provider' => $this->plugin->get_setting('ai_provider'),
                'model' => $ai_response['model'] ?? null,
                'tokens' => $ai_response['tokens'] ?? null,
                'processing_time' => $processing_time,
                'context' => $context
            ]);
            
            return [
                'success' => true,
                'response' => $ai_response['content'],
                'session_id' => $session_id,
                'context' => $context,
                'processing_time' => round($processing_time, 3)
            ];
            
        } catch (\Exception $e) {
            error_log('AIA Chat Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => __('Failed to get AI response. Please try again.', 'ai-inventory-agent')
            ];
        }
    }
    
    /**
     * Get inventory context for the message
     * 
     * @param string $message User message
     * @return array Context data
     */
    private function get_inventory_context($message) {
        return $this->inventory_context->analyze_message($message);
    }
    
    /**
     * Build system prompt with context
     * 
     * @param array $context Inventory context
     * @return string System prompt
     */
    private function build_system_prompt($context) {
        $base_prompt = $this->plugin->get_setting('system_prompt');
        
        $context_info = [];
        
        // Add current date and time
        $context_info[] = "Current date and time: " . current_time('Y-m-d H:i:s');
        
        // Add store information
        $store_name = get_bloginfo('name');
        $context_info[] = "Store name: {$store_name}";
        
        // Add inventory summary
        if (!empty($context['products'])) {
            $context_info[] = "Products mentioned: " . implode(', ', $context['products']);
        }
        
        // Add current inventory stats
        $inventory_stats = $this->get_inventory_stats();
        if ($inventory_stats) {
            $context_info[] = "Current inventory status:";
            $context_info[] = "- Total products: " . $inventory_stats['total_products'];
            $context_info[] = "- Low stock products: " . $inventory_stats['low_stock_count'];
            $context_info[] = "- Out of stock products: " . $inventory_stats['out_of_stock_count'];
        }
        
        // Add recent sales data if relevant
        if ($context['needs_sales_data']) {
            $sales_data = $this->get_recent_sales_data();
            if ($sales_data) {
                $context_info[] = "Recent sales (last 7 days): " . $sales_data['total_sales'] . " orders";
                $context_info[] = "Top selling products: " . implode(', ', $sales_data['top_products']);
            }
        }
        
        $context_string = implode("\n", $context_info);
        
        return $base_prompt . "\n\nCurrent context:\n" . $context_string . "\n\nPlease provide helpful, accurate advice based on this information.";
    }
    
    /**
     * Prepare conversation for AI provider
     * 
     * @param array $chat_history Previous messages
     * @param string $current_message Current user message
     * @param string $system_prompt System prompt
     * @return array Formatted conversation
     */
    private function prepare_conversation($chat_history, $current_message, $system_prompt) {
        $conversation = [
            [
                'role' => 'system',
                'content' => $system_prompt
            ]
        ];
        
        // Add chat history
        foreach ($chat_history as $message) {
            $conversation[] = [
                'role' => $message->message_type === 'user' ? 'user' : 'assistant',
                'content' => $message->message
            ];
        }
        
        // Add current message
        $conversation[] = [
            'role' => 'user',
            'content' => $current_message
        ];
        
        return $conversation;
    }
    
    /**
     * Get inventory statistics
     * 
     * @return array|null Inventory stats
     */
    private function get_inventory_stats() {
        $stats = wp_cache_get('aia_inventory_stats');
        
        if ($stats === false) {
            global $wpdb;
            
            // Get total products
            $total_products = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) 
                FROM {$wpdb->posts} 
                WHERE post_type = %s 
                AND post_status = %s
            ", 'product', 'publish'));
            
            // Get low stock threshold
            $low_stock_threshold = $this->plugin->get_setting('low_stock_threshold');
            
            // Get low stock count
            $low_stock_count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) 
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = 'product' 
                AND p.post_status = 'publish'
                AND pm.meta_key = '_stock'
                AND CAST(pm.meta_value AS UNSIGNED) <= %d
                AND CAST(pm.meta_value AS UNSIGNED) > 0
            ", $low_stock_threshold));
            
            // Get out of stock count
            $out_of_stock_count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) 
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                WHERE p.post_type = %s 
                AND p.post_status = %s
                AND pm.meta_key = %s
                AND (pm.meta_value = '0' OR pm.meta_value = '')
            ", 'product', 'publish', '_stock'));
            
            $stats = [
                'total_products' => (int) $total_products,
                'low_stock_count' => (int) $low_stock_count,
                'out_of_stock_count' => (int) $out_of_stock_count
            ];
            
            wp_cache_set('aia_inventory_stats', $stats, '', 300); // Cache for 5 minutes
        }
        
        return $stats;
    }
    
    /**
     * Get recent sales data
     * 
     * @return array|null Sales data
     */
    private function get_recent_sales_data() {
        $sales_data = wp_cache_get('aia_recent_sales');
        
        if ($sales_data === false) {
            global $wpdb;
            
            $date_from = date('Y-m-d H:i:s', strtotime('-7 days'));
            
            // Get total sales
            $total_sales = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) 
                FROM {$wpdb->posts} 
                WHERE post_type = 'shop_order' 
                AND post_status IN ('wc-completed', 'wc-processing')
                AND post_date >= %s
            ", $date_from));
            
            // Get top selling products
            $top_products = $wpdb->get_results($wpdb->prepare("
                SELECT p.post_title, SUM(oim.meta_value) as quantity
                FROM {$wpdb->posts} o
                INNER JOIN {$wpdb->prefix}woocommerce_order_items oi ON o.ID = oi.order_id
                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim2 ON oi.order_item_id = oim2.order_item_id
                INNER JOIN {$wpdb->posts} p ON oim2.meta_value = p.ID
                WHERE o.post_type = 'shop_order'
                AND o.post_status IN ('wc-completed', 'wc-processing')
                AND o.post_date >= %s
                AND oim.meta_key = '_qty'
                AND oim2.meta_key = '_product_id'
                GROUP BY p.ID
                ORDER BY quantity DESC
                LIMIT 5
            ", $date_from));
            
            $sales_data = [
                'total_sales' => (int) $total_sales,
                'top_products' => array_column($top_products, 'post_title')
            ];
            
            wp_cache_set('aia_recent_sales', $sales_data, '', 300); // Cache for 5 minutes
        }
        
        return $sales_data;
    }
    
    /**
     * Save conversation to database
     * 
     * @param string $session_id Session ID
     * @param string $message_type Message type (user/assistant)
     * @param string $message Message content
     * @param array $metadata Additional metadata
     */
    private function save_conversation($session_id, $message_type, $message, $metadata = []) {
        $database = $this->plugin->get_database();
        $database->save_ai_conversation($session_id, $message_type, $message, $metadata);
    }
    
    /**
     * Get chat history
     * 
     * @param string $session_id Session ID
     * @param int $limit Number of messages to retrieve
     * @return array Chat history
     */
    private function get_chat_history($session_id, $limit = 20) {
        global $wpdb;
        
        $table = $this->plugin->get_database()->get_table_name('ai_conversations');
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT message_type, message, created_at
            FROM {$table}
            WHERE session_id = %s
            ORDER BY created_at DESC
            LIMIT %d
        ", $session_id, $limit));
    }
    
    /**
     * Generate session ID
     * 
     * @return string Session ID
     */
    private function generate_session_id() {
        return 'aia_' . wp_generate_uuid4();
    }
    
    /**
     * Handle send message AJAX request
     */
    public function handle_send_message() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        
        if (empty($message)) {
            wp_send_json_error(__('Message cannot be empty.', 'ai-inventory-agent'));
        }
        
        $response = $this->process_message($message, $session_id);
        
        if ($response['success']) {
            wp_send_json_success($response);
        } else {
            wp_send_json_error($response['error']);
        }
    }
    
    /**
     * Handle get chat history AJAX request
     */
    public function handle_get_chat_history() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        $limit = intval($_POST['limit'] ?? 20);
        
        if (empty($session_id)) {
            wp_send_json_error(__('Session ID is required.', 'ai-inventory-agent'));
        }
        
        $history = $this->get_chat_history($session_id, $limit);
        
        wp_send_json_success(['history' => array_reverse($history)]);
    }
    
    /**
     * Handle clear chat history AJAX request
     */
    public function handle_clear_chat_history() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        
        if (empty($session_id)) {
            wp_send_json_error(__('Session ID is required.', 'ai-inventory-agent'));
        }
        
        global $wpdb;
        $table = $this->plugin->get_database()->get_table_name('ai_conversations');
        
        $result = $wpdb->delete($table, ['session_id' => $session_id], ['%s']);
        
        if ($result !== false) {
            wp_send_json_success(__('Chat history cleared.', 'ai-inventory-agent'));
        } else {
            wp_send_json_error(__('Failed to clear chat history.', 'ai-inventory-agent'));
        }
    }
    
    /**
     * Render chat shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_chat_shortcode($atts) {
        $atts = shortcode_atts([
            'height' => '400px',
            'theme' => 'default'
        ], $atts);
        
        ob_start();
        include AIA_PLUGIN_DIR . 'templates/chat-widget.php';
        return ob_get_clean();
    }
    
    /**
     * Get module information
     * 
     * @return array Module info
     */
    public function get_info() {
        return $this->info;
    }
    
    /**
     * Deactivate module
     */
    public function deactivate() {
        // Clean up any resources
        wp_cache_delete('aia_inventory_stats');
        wp_cache_delete('aia_recent_sales');
    }
}