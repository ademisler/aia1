<?php

namespace AIA\Utils;

/**
 * Inventory Context Analyzer
 * 
 * Analyzes user messages to extract inventory-related context and intent
 */
class InventoryContext {
    
    /**
     * Keywords for different inventory operations
     * 
     * @var array
     */
    private $keywords = [
        'stock_check' => ['stock', 'inventory', 'quantity', 'available', 'remaining', 'left'],
        'sales_data' => ['sales', 'sold', 'revenue', 'orders', 'customers', 'popular', 'best selling'],
        'forecasting' => ['forecast', 'predict', 'future', 'demand', 'trend', 'seasonal', 'next month', 'next week'],
        'reorder' => ['reorder', 'purchase', 'buy', 'supplier', 'order', 'replenish'],
        'alerts' => ['alert', 'notification', 'low stock', 'out of stock', 'warning'],
        'analysis' => ['analyze', 'analysis', 'report', 'insights', 'performance', 'metrics'],
        'products' => ['product', 'item', 'sku', 'category', 'brand']
    ];
    
    /**
     * Product name patterns
     * 
     * @var array
     */
    private $product_patterns = [
        '/product\s+([a-zA-Z0-9\s\-_]+)/i',
        '/sku\s*:?\s*([a-zA-Z0-9\-_]+)/i',
        '/"([^"]+)"/i',
        /'([^']+)'/i'
    ];
    
    /**
     * Analyze message for inventory context
     * 
     * @param string $message User message
     * @return array Context data
     */
    public function analyze_message($message) {
        $message_lower = strtolower($message);
        
        $context = [
            'intent' => $this->detect_intent($message_lower),
            'products' => $this->extract_products($message),
            'categories' => $this->extract_categories($message),
            'time_frame' => $this->extract_time_frame($message_lower),
            'metrics' => $this->extract_metrics($message_lower),
            'needs_sales_data' => $this->needs_sales_data($message_lower),
            'needs_forecast' => $this->needs_forecast($message_lower),
            'urgency' => $this->detect_urgency($message_lower),
            'action_required' => $this->detect_action_required($message_lower)
        ];
        
        return $context;
    }
    
    /**
     * Detect user intent from message
     * 
     * @param string $message_lower Lowercase message
     * @return array Detected intents
     */
    private function detect_intent($message_lower) {
        $intents = [];
        
        foreach ($this->keywords as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message_lower, $keyword) !== false) {
                    $intents[] = $intent;
                    break;
                }
            }
        }
        
        return array_unique($intents);
    }
    
    /**
     * Extract product names from message
     * 
     * @param string $message Original message
     * @return array Product names
     */
    private function extract_products($message) {
        $products = [];
        
        // Try different patterns to extract product names
        foreach ($this->product_patterns as $pattern) {
            if (preg_match_all($pattern, $message, $matches)) {
                $products = array_merge($products, $matches[1]);
            }
        }
        
        // Look for existing product names in WooCommerce
        $existing_products = $this->find_existing_products($message);
        $products = array_merge($products, $existing_products);
        
        return array_unique(array_filter($products));
    }
    
    /**
     * Find existing WooCommerce products mentioned in message
     * 
     * @param string $message Message content
     * @return array Found product names
     */
    private function find_existing_products($message) {
        global $wpdb;
        
        $products = [];
        $words = explode(' ', $message);
        
        // Look for products with names containing message words
        foreach ($words as $word) {
            if (strlen($word) < 3) continue; // Skip short words
            
            $word = sanitize_text_field($word);
            
            $results = $wpdb->get_results($wpdb->prepare("
                SELECT post_title 
                FROM {$wpdb->posts} 
                WHERE post_type = 'product' 
                AND post_status = 'publish'
                AND post_title LIKE %s
                LIMIT 5
            ", '%' . $word . '%'));
            
            foreach ($results as $result) {
                $products[] = $result->post_title;
            }
        }
        
        return array_unique($products);
    }
    
    /**
     * Extract product categories from message
     * 
     * @param string $message Message content
     * @return array Category names
     */
    private function extract_categories($message) {
        global $wpdb;
        
        $categories = [];
        $message_lower = strtolower($message);
        
        // Get WooCommerce product categories
        $terms = $wpdb->get_results("
            SELECT name 
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
            WHERE tt.taxonomy = 'product_cat'
        ");
        
        foreach ($terms as $term) {
            if (strpos($message_lower, strtolower($term->name)) !== false) {
                $categories[] = $term->name;
            }
        }
        
        return array_unique($categories);
    }
    
    /**
     * Extract time frame from message
     * 
     * @param string $message_lower Lowercase message
     * @return array Time frame information
     */
    private function extract_time_frame($message_lower) {
        $time_patterns = [
            'today' => ['today', 'this day'],
            'yesterday' => ['yesterday'],
            'this_week' => ['this week', 'past week', 'last 7 days'],
            'this_month' => ['this month', 'past month', 'last 30 days'],
            'this_quarter' => ['this quarter', 'past quarter', 'last 90 days'],
            'this_year' => ['this year', 'past year', 'last 365 days'],
            'last_week' => ['last week', 'previous week'],
            'last_month' => ['last month', 'previous month'],
            'next_week' => ['next week', 'coming week'],
            'next_month' => ['next month', 'coming month']
        ];
        
        $detected_frames = [];
        
        foreach ($time_patterns as $frame => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($message_lower, $pattern) !== false) {
                    $detected_frames[] = $frame;
                    break;
                }
            }
        }
        
        return array_unique($detected_frames);
    }
    
    /**
     * Extract metrics mentioned in message
     * 
     * @param string $message_lower Lowercase message
     * @return array Metrics
     */
    private function extract_metrics($message_lower) {
        $metric_patterns = [
            'revenue' => ['revenue', 'income', 'earnings', 'sales amount'],
            'quantity' => ['quantity', 'units', 'pieces', 'items sold'],
            'orders' => ['orders', 'transactions', 'purchases'],
            'customers' => ['customers', 'buyers', 'clients'],
            'profit' => ['profit', 'margin', 'markup'],
            'conversion' => ['conversion', 'conversion rate'],
            'average_order' => ['average order', 'aov', 'order value']
        ];
        
        $detected_metrics = [];
        
        foreach ($metric_patterns as $metric => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($message_lower, $pattern) !== false) {
                    $detected_metrics[] = $metric;
                    break;
                }
            }
        }
        
        return array_unique($detected_metrics);
    }
    
    /**
     * Check if message needs sales data
     * 
     * @param string $message_lower Lowercase message
     * @return bool
     */
    private function needs_sales_data($message_lower) {
        $sales_indicators = [
            'sales', 'sold', 'revenue', 'orders', 'customers', 'popular', 
            'best selling', 'top products', 'performance', 'trend'
        ];
        
        foreach ($sales_indicators as $indicator) {
            if (strpos($message_lower, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if message needs forecasting
     * 
     * @param string $message_lower Lowercase message
     * @return bool
     */
    private function needs_forecast($message_lower) {
        $forecast_indicators = [
            'forecast', 'predict', 'future', 'demand', 'next', 'coming',
            'trend', 'seasonal', 'expect', 'anticipate'
        ];
        
        foreach ($forecast_indicators as $indicator) {
            if (strpos($message_lower, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect urgency level
     * 
     * @param string $message_lower Lowercase message
     * @return string Urgency level
     */
    private function detect_urgency($message_lower) {
        $urgency_patterns = [
            'critical' => ['urgent', 'critical', 'emergency', 'asap', 'immediately'],
            'high' => ['important', 'priority', 'soon', 'quickly'],
            'medium' => ['when possible', 'sometime', 'eventually'],
            'low' => ['no rush', 'whenever', 'later']
        ];
        
        foreach ($urgency_patterns as $level => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($message_lower, $pattern) !== false) {
                    return $level;
                }
            }
        }
        
        return 'medium'; // Default urgency
    }
    
    /**
     * Detect if action is required
     * 
     * @param string $message_lower Lowercase message
     * @return bool
     */
    private function detect_action_required($message_lower) {
        $action_indicators = [
            'order', 'buy', 'purchase', 'reorder', 'replenish', 'update',
            'change', 'set', 'adjust', 'create', 'generate', 'send'
        ];
        
        foreach ($action_indicators as $indicator) {
            if (strpos($message_lower, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get context summary for display
     * 
     * @param array $context Context data
     * @return string Summary
     */
    public function get_context_summary($context) {
        $summary_parts = [];
        
        if (!empty($context['intent'])) {
            $summary_parts[] = 'Intent: ' . implode(', ', $context['intent']);
        }
        
        if (!empty($context['products'])) {
            $summary_parts[] = 'Products: ' . implode(', ', array_slice($context['products'], 0, 3));
            if (count($context['products']) > 3) {
                $summary_parts[] = '... and ' . (count($context['products']) - 3) . ' more';
            }
        }
        
        if (!empty($context['time_frame'])) {
            $summary_parts[] = 'Time frame: ' . implode(', ', $context['time_frame']);
        }
        
        if ($context['urgency'] !== 'medium') {
            $summary_parts[] = 'Urgency: ' . $context['urgency'];
        }
        
        return implode(' | ', $summary_parts);
    }
}