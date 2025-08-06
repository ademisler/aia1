<?php

namespace AIA\Modules;

use AIA\Core\Plugin;
use WC_Product;

/**
 * Notifications Module
 * 
 * Handles all notification functionality for inventory alerts
 */
class Notifications {
    
    /**
     * Module information
     * 
     * @var array
     */
    private $info = [
        'name' => 'Notifications System',
        'description' => 'Email and dashboard notifications for inventory alerts',
        'version' => '1.0.0'
    ];
    
    /**
     * Plugin instance
     * 
     * @var Plugin
     */
    private $plugin;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Avoid circular dependency - plugin instance will be set during init
    }
    
    /**
     * Initialize the module
     */
    public function init() {
        // Set plugin instance safely during init
        if (!$this->plugin && class_exists('AIA\\Core\\Plugin')) {
            $this->plugin = \AIA\Core\Plugin::get_instance();
        }
        
        if (!$this->plugin) {
            error_log('AIA Notifications: Plugin instance not available during init');
            return;
        }
        
        // Register hooks
        $this->register_hooks();
    }
    
    /**
     * Register module hooks
     */
    private function register_hooks() {
        // Admin notices
        add_action('admin_notices', [$this, 'display_admin_notices']);
        
        // AJAX handlers
        add_action('wp_ajax_aia_dismiss_notice', [$this, 'handle_dismiss_notice']);
        add_action('wp_ajax_aia_get_notifications', [$this, 'handle_get_notifications']);
        
        // Scheduled tasks
        add_action('aia_check_stock_alerts', [$this, 'check_all_stock_alerts']);
    }
    
    /**
     * Check stock levels for a product
     * 
     * @param WC_Product|int $product Product object or ID
     */
    public function check_stock_levels($product) {
        if (is_numeric($product)) {
            $product = wc_get_product($product);
        }
        
        if (!$product || !$product->managing_stock()) {
            return;
        }
        
        $stock_quantity = $product->get_stock_quantity();
        $product_id = $product->get_id();
        $product_name = $product->get_name();
        
        // Get thresholds
        $low_stock_threshold = $this->plugin->get_setting('low_stock_threshold') ?: 5;
        $critical_stock_threshold = $this->plugin->get_setting('critical_stock_threshold') ?: 1;
        
        // Check for out of stock
        if ($stock_quantity <= 0) {
            $this->create_stock_alert(
                $product_id,
                'out_of_stock',
                $stock_quantity,
                0,
                'critical',
                sprintf(__('Product "%s" is out of stock!', 'ai-inventory-agent'), $product_name)
            );
        }
        // Check for critical stock
        elseif ($stock_quantity <= $critical_stock_threshold) {
            $this->create_stock_alert(
                $product_id,
                'low_stock',
                $stock_quantity,
                $critical_stock_threshold,
                'critical',
                sprintf(__('Product "%s" has critically low stock (%d units).', 'ai-inventory-agent'), $product_name, $stock_quantity)
            );
        }
        // Check for low stock
        elseif ($stock_quantity <= $low_stock_threshold) {
            $this->create_stock_alert(
                $product_id,
                'low_stock',
                $stock_quantity,
                $low_stock_threshold,
                'warning',
                sprintf(__('Product "%s" has low stock (%d units).', 'ai-inventory-agent'), $product_name, $stock_quantity)
            );
        }
        // Check for overstock (if product has max stock set)
        else {
            $max_stock = get_post_meta($product_id, '_max_stock_threshold', true);
            if ($max_stock && $stock_quantity > $max_stock * 1.5) {
                $this->create_stock_alert(
                    $product_id,
                    'overstock',
                    $stock_quantity,
                    $max_stock,
                    'info',
                    sprintf(__('Product "%s" may be overstocked (%d units).', 'ai-inventory-agent'), $product_name, $stock_quantity)
                );
            }
        }
    }
    
    /**
     * Create or update a stock alert
     * 
     * @param int $product_id Product ID
     * @param string $alert_type Alert type
     * @param int $current_stock Current stock level
     * @param int $threshold_value Threshold value
     * @param string $severity Severity level
     * @param string $message Alert message
     */
    private function create_stock_alert($product_id, $alert_type, $current_stock, $threshold_value, $severity, $message) {
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('stock_alerts');
        
        // Check if alert already exists and is active
        $existing_alert = $wpdb->get_row($wpdb->prepare("
            SELECT id, status 
            FROM {$table_name}
            WHERE product_id = %d 
            AND alert_type = %s 
            AND status = 'active'
            ORDER BY created_at DESC
            LIMIT 1
        ", $product_id, $alert_type));
        
        if (!$existing_alert) {
            // Create new alert
            $wpdb->insert(
                $table_name,
                [
                    'product_id' => $product_id,
                    'alert_type' => $alert_type,
                    'current_stock' => $current_stock,
                    'threshold_value' => $threshold_value,
                    'severity' => $severity,
                    'status' => 'active',
                    'message' => $message,
                    'created_at' => current_time('mysql')
                ],
                ['%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s']
            );
            
            // Send notification
            $this->send_stock_alert_notification($product_id, $alert_type, $message, $severity);
        } else {
            // Update existing alert
            $wpdb->update(
                $table_name,
                [
                    'current_stock' => $current_stock,
                    'message' => $message,
                    'severity' => $severity
                ],
                ['id' => $existing_alert->id],
                ['%d', '%s', '%s'],
                ['%d']
            );
        }
    }
    
    /**
     * Send stock alert notification
     * 
     * @param int $product_id Product ID
     * @param string $alert_type Alert type
     * @param string $message Alert message
     * @param string $severity Severity level
     */
    private function send_stock_alert_notification($product_id, $alert_type, $message, $severity) {
        if (!$this->plugin->get_setting('notifications_enabled')) {
            return;
        }
        
        $recipient = $this->plugin->get_setting('notification_email') ?: get_option('admin_email');
        $product = wc_get_product($product_id);
        
        if (!$product) {
            return;
        }
        
        $subject = sprintf(
            __('[%s] Stock Alert: %s', 'ai-inventory-agent'),
            get_bloginfo('name'),
            $product->get_name()
        );
        
        $body = $this->get_email_template('stock_alert', [
            'product_name' => $product->get_name(),
            'product_sku' => $product->get_sku(),
            'alert_type' => $alert_type,
            'message' => $message,
            'severity' => $severity,
            'product_url' => get_edit_post_link($product_id),
            'current_stock' => $product->get_stock_quantity(),
            'manage_stock_url' => admin_url('edit.php?post_type=product&page=ai-inventory-agent')
        ]);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];
        
        wp_mail($recipient, $subject, $body, $headers);
    }
    
    /**
     * Send supplier alert
     * 
     * @param object $supplier Supplier data
     */
    public function send_supplier_alert($supplier) {
        if (!$this->plugin->get_setting('notifications_enabled')) {
            return;
        }
        
        $recipient = $this->plugin->get_setting('notification_email') ?: get_option('admin_email');
        
        $subject = sprintf(
            __('[%s] Supplier Risk Alert: %s', 'ai-inventory-agent'),
            get_bloginfo('name'),
            $supplier->supplier_name
        );
        
        $body = $this->get_email_template('supplier_alert', [
            'supplier_name' => $supplier->supplier_name,
            'risk_level' => $supplier->risk_level,
            'manage_url' => admin_url('edit.php?post_type=product&page=ai-inventory-agent&tab=suppliers')
        ]);
        
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];
        
        wp_mail($recipient, $subject, $body, $headers);
    }
    
    /**
     * Get email template
     * 
     * @param string $template Template name
     * @param array $variables Template variables
     * @return string Email body
     */
    private function get_email_template($template, $variables = []) {
        $template_file = AIA_PLUGIN_DIR . 'templates/emails/' . $template . '.php';
        
        if (!file_exists($template_file)) {
            // Return default template
            return $this->get_default_email_template($template, $variables);
        }
        
        ob_start();
        extract($variables);
        include $template_file;
        return ob_get_clean();
    }
    
    /**
     * Get default email template
     * 
     * @param string $template Template name
     * @param array $variables Template variables
     * @return string Email body
     */
    private function get_default_email_template($template, $variables) {
        $html = '<html><body style="font-family: Arial, sans-serif; color: #333;">';
        $html .= '<div style="max-width: 600px; margin: 0 auto; padding: 20px;">';
        
        switch ($template) {
            case 'stock_alert':
                $html .= '<h2 style="color: #e74c3c;">Stock Alert</h2>';
                $html .= '<p><strong>Product:</strong> ' . esc_html($variables['product_name']) . '</p>';
                if (!empty($variables['product_sku'])) {
                    $html .= '<p><strong>SKU:</strong> ' . esc_html($variables['product_sku']) . '</p>';
                }
                $html .= '<p><strong>Current Stock:</strong> ' . esc_html($variables['current_stock']) . '</p>';
                $html .= '<p><strong>Alert:</strong> ' . esc_html($variables['message']) . '</p>';
                $html .= '<p><a href="' . esc_url($variables['product_url']) . '" style="background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">View Product</a></p>';
                break;
                
            case 'supplier_alert':
                $html .= '<h2 style="color: #e74c3c;">Supplier Risk Alert</h2>';
                $html .= '<p><strong>Supplier:</strong> ' . esc_html($variables['supplier_name']) . '</p>';
                $html .= '<p><strong>Risk Level:</strong> <span style="color: #e74c3c; text-transform: uppercase;">' . esc_html($variables['risk_level']) . '</span></p>';
                $html .= '<p>This supplier has been flagged as high risk. Please review their performance metrics and consider alternative suppliers.</p>';
                $html .= '<p><a href="' . esc_url($variables['manage_url']) . '" style="background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Manage Suppliers</a></p>';
                break;
        }
        
        $html .= '<hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">';
        $html .= '<p style="font-size: 12px; color: #999;">This is an automated message from AI Inventory Agent. Please do not reply to this email.</p>';
        $html .= '</div></body></html>';
        
        return $html;
    }
    
    /**
     * Display admin notices
     */
    public function display_admin_notices() {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }
        
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('stock_alerts');
        
        // Get active alerts
        $alerts = $wpdb->get_results("
            SELECT sa.*, p.post_title as product_name
            FROM {$table_name} sa
            INNER JOIN {$wpdb->posts} p ON sa.product_id = p.ID
            WHERE sa.status = 'active'
            AND sa.severity IN ('warning', 'critical')
            ORDER BY 
                FIELD(sa.severity, 'critical', 'warning', 'info'),
                sa.created_at DESC
            LIMIT 5
        ");
        
        foreach ($alerts as $alert) {
            $class = $alert->severity === 'critical' ? 'notice-error' : 'notice-warning';
            $dismissible = $alert->severity !== 'critical' ? 'is-dismissible' : '';
            
            ?>
            <div class="notice <?php echo esc_attr($class); ?> <?php echo esc_attr($dismissible); ?> aia-stock-alert" data-alert-id="<?php echo esc_attr($alert->id); ?>">
                <p>
                    <strong><?php esc_html_e('Stock Alert:', 'ai-inventory-agent'); ?></strong>
                    <?php echo esc_html($alert->message); ?>
                    <a href="<?php echo esc_url(get_edit_post_link($alert->product_id)); ?>" class="button button-small" style="margin-left: 10px;">
                        <?php esc_html_e('View Product', 'ai-inventory-agent'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Check all products for stock alerts
     */
    public function check_all_stock_alerts() {
        $args = [
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_manage_stock',
                    'value' => 'yes'
                ]
            ]
        ];
        
        $products = get_posts($args);
        
        foreach ($products as $product_post) {
            $product = wc_get_product($product_post->ID);
            if ($product) {
                $this->check_stock_levels($product);
            }
        }
    }
    
    /**
     * Get module info
     * 
     * @return array Module information
     */
    public function get_info() {
        return $this->info;
    }
    
    /**
     * Handle dismiss notice AJAX request
     */
    public function handle_dismiss_notice() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        $alert_id = intval($_POST['alert_id'] ?? 0);
        
        if (!$alert_id) {
            wp_send_json_error(__('Invalid alert ID.', 'ai-inventory-agent'));
        }
        
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('stock_alerts');
        
        $result = $wpdb->update(
            $table_name,
            [
                'status' => 'acknowledged',
                'acknowledged_by' => get_current_user_id(),
                'acknowledged_at' => current_time('mysql')
            ],
            ['id' => $alert_id],
            ['%s', '%d', '%s'],
            ['%d']
        );
        
        if ($result !== false) {
            wp_send_json_success(__('Alert dismissed.', 'ai-inventory-agent'));
        } else {
            wp_send_json_error(__('Failed to dismiss alert.', 'ai-inventory-agent'));
        }
    }
    
    /**
     * Handle get notifications AJAX request
     */
    public function handle_get_notifications() {
        check_ajax_referer('aia_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('Insufficient permissions.', 'ai-inventory-agent'));
        }
        
        global $wpdb;
        $database = $this->plugin->get_database();
        $table_name = $database->get_table_name('stock_alerts');
        
        $status = sanitize_text_field($_POST['status'] ?? 'active');
        $limit = intval($_POST['limit'] ?? 20);
        
        $alerts = $wpdb->get_results($wpdb->prepare("
            SELECT sa.*, p.post_title as product_name
            FROM {$table_name} sa
            INNER JOIN {$wpdb->posts} p ON sa.product_id = p.ID
            WHERE sa.status = %s
            ORDER BY sa.created_at DESC
            LIMIT %d
        ", $status, $limit));
        
        wp_send_json_success($alerts);
    }
}