<?php
/**
 * Admin Alerts Page Template
 * 
 * @package AIA
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get notifications module
$notifications_module = $this->plugin->get_module_manager()->get_module('notifications');
$inventory_analysis = $this->plugin->get_module_manager()->get_module('inventory_analysis');

// Get current alerts
$low_stock_products = [];
$out_of_stock_products = [];

if ($inventory_analysis) {
    $summary = $inventory_analysis->get_inventory_summary();
    $low_stock_products = $summary['low_stock_products'] ?? [];
    $out_of_stock_products = $summary['out_of_stock_products'] ?? [];
}
?>

<div class="wrap aia-alerts-page">
    <h1><?php _e('Stock Alerts', 'ai-inventory-agent'); ?></h1>
    
    <div class="aia-alerts-container">
        
        <!-- Alert Settings -->
        <div class="aia-section">
            <h2><?php _e('Alert Settings', 'ai-inventory-agent'); ?></h2>
            <form method="post" action="options.php">
                <?php 
                settings_fields('aia_settings');
                $settings = get_option('aia_settings', []);
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="low_stock_threshold"><?php _e('Low Stock Threshold', 'ai-inventory-agent'); ?></label>
                        </th>
                        <td>
                            <input type="number" 
                                   id="low_stock_threshold" 
                                   name="aia_settings[low_stock_threshold]" 
                                   value="<?php echo esc_attr($settings['low_stock_threshold'] ?? 5); ?>" 
                                   min="1" />
                            <p class="description"><?php _e('Alert when stock falls below this number.', 'ai-inventory-agent'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="critical_stock_threshold"><?php _e('Critical Stock Threshold', 'ai-inventory-agent'); ?></label>
                        </th>
                        <td>
                            <input type="number" 
                                   id="critical_stock_threshold" 
                                   name="aia_settings[critical_stock_threshold]" 
                                   value="<?php echo esc_attr($settings['critical_stock_threshold'] ?? 1); ?>" 
                                   min="0" />
                            <p class="description"><?php _e('Critical alert when stock falls to this level or below.', 'ai-inventory-agent'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="notification_email"><?php _e('Notification Email', 'ai-inventory-agent'); ?></label>
                        </th>
                        <td>
                            <input type="email" 
                                   id="notification_email" 
                                   name="aia_settings[notification_email]" 
                                   value="<?php echo esc_attr($settings['notification_email'] ?? get_option('admin_email')); ?>" 
                                   class="regular-text" />
                            <p class="description"><?php _e('Email address to receive stock alerts.', 'ai-inventory-agent'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php _e('Enable Notifications', 'ai-inventory-agent'); ?>
                        </th>
                        <td>
                            <label for="notifications_enabled">
                                <input type="checkbox" 
                                       id="notifications_enabled" 
                                       name="aia_settings[notifications_enabled]" 
                                       value="1" 
                                       <?php checked($settings['notifications_enabled'] ?? true); ?> />
                                <?php _e('Send email notifications for stock alerts', 'ai-inventory-agent'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>

        <!-- Critical Alerts -->
        <?php if (!empty($out_of_stock_products)): ?>
        <div class="aia-section aia-critical-alerts">
            <h2><?php _e('Critical Alerts - Out of Stock', 'ai-inventory-agent'); ?></h2>
            <div class="aia-alert-list">
                <?php foreach (array_slice($out_of_stock_products, 0, 20) as $product): ?>
                    <div class="aia-alert-item aia-critical">
                        <div class="aia-alert-icon">⚠️</div>
                        <div class="aia-alert-content">
                            <strong><?php echo esc_html($product->post_title); ?></strong>
                            <div class="aia-alert-details">
                                <?php _e('Stock:', 'ai-inventory-agent'); ?> <span class="aia-stock-zero"><?php echo esc_html($product->stock_quantity); ?></span>
                                | <?php _e('Price:', 'ai-inventory-agent'); ?> <?php echo wc_price($product->regular_price ?? 0); ?>
                            </div>
                        </div>
                        <div class="aia-alert-actions">
                            <a href="<?php echo esc_url(admin_url('post.php?post=' . $product->ID . '&action=edit')); ?>" 
                               class="button button-small button-primary"><?php _e('Restock', 'ai-inventory-agent'); ?></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Low Stock Alerts -->
        <?php if (!empty($low_stock_products)): ?>
        <div class="aia-section aia-warning-alerts">
            <h2><?php _e('Low Stock Warnings', 'ai-inventory-agent'); ?></h2>
            <div class="aia-alert-list">
                <?php foreach (array_slice($low_stock_products, 0, 20) as $product): ?>
                    <div class="aia-alert-item aia-warning">
                        <div class="aia-alert-icon">⚡</div>
                        <div class="aia-alert-content">
                            <strong><?php echo esc_html($product->post_title); ?></strong>
                            <div class="aia-alert-details">
                                <?php _e('Stock:', 'ai-inventory-agent'); ?> <span class="aia-stock-low"><?php echo esc_html($product->stock_quantity); ?></span>
                                | <?php _e('Price:', 'ai-inventory-agent'); ?> <?php echo wc_price($product->regular_price ?? 0); ?>
                            </div>
                        </div>
                        <div class="aia-alert-actions">
                            <a href="<?php echo esc_url(admin_url('post.php?post=' . $product->ID . '&action=edit')); ?>" 
                               class="button button-small"><?php _e('Manage Stock', 'ai-inventory-agent'); ?></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- No Alerts Message -->
        <?php if (empty($out_of_stock_products) && empty($low_stock_products)): ?>
        <div class="aia-section aia-no-alerts">
            <div class="aia-success-message">
                <div class="aia-success-icon">✅</div>
                <h3><?php _e('All Good!', 'ai-inventory-agent'); ?></h3>
                <p><?php _e('No stock alerts at this time. All your products have adequate stock levels.', 'ai-inventory-agent'); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.aia-alerts-page .aia-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.aia-alerts-page .aia-critical-alerts {
    border-left: 4px solid #d63638;
}

.aia-alerts-page .aia-warning-alerts {
    border-left: 4px solid #ffb900;
}

.aia-alerts-page .aia-alert-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.aia-alerts-page .aia-alert-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-radius: 4px;
    background: #f9f9f9;
}

.aia-alerts-page .aia-alert-item.aia-critical {
    border-left: 4px solid #d63638;
}

.aia-alerts-page .aia-alert-item.aia-warning {
    border-left: 4px solid #ffb900;
}

.aia-alerts-page .aia-alert-icon {
    font-size: 24px;
    margin-right: 15px;
}

.aia-alerts-page .aia-alert-content {
    flex: 1;
}

.aia-alerts-page .aia-alert-details {
    color: #666;
    font-size: 13px;
    margin-top: 5px;
}

.aia-alerts-page .aia-stock-zero {
    color: #d63638;
    font-weight: bold;
}

.aia-alerts-page .aia-stock-low {
    color: #ffb900;
    font-weight: bold;
}

.aia-alerts-page .aia-alert-actions {
    margin-left: 15px;
}

.aia-alerts-page .aia-no-alerts {
    text-align: center;
    padding: 40px 20px;
}

.aia-alerts-page .aia-success-message {
    max-width: 400px;
    margin: 0 auto;
}

.aia-alerts-page .aia-success-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.aia-alerts-page .aia-success-message h3 {
    color: #46b450;
    margin-bottom: 10px;
}
</style>