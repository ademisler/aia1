<?php
/**
 * Admin Analysis Page Template
 * 
 * @package AIA
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get inventory analysis data
$plugin_instance = \AIA\Core\Plugin::get_instance();
$inventory_analysis = $plugin_instance ? $plugin_instance->get_module_manager()->get_module('inventory_analysis') : null;
$summary = $inventory_analysis ? $inventory_analysis->get_inventory_summary() : [];
?>

<div class="wrap aia-analysis-page">
    <h1><?php _e('Inventory Analysis', 'ai-inventory-agent'); ?></h1>
    
    <div class="aia-analysis-container">
        <!-- Stock Overview Cards -->
        <div class="aia-cards-row">
            <div class="aia-card">
                <h3><?php _e('Total Products', 'ai-inventory-agent'); ?></h3>
                <div class="aia-stat-value"><?php echo esc_html($summary['total_products'] ?? 0); ?></div>
            </div>
            
            <div class="aia-card">
                <h3><?php _e('In Stock', 'ai-inventory-agent'); ?></h3>
                <div class="aia-stat-value"><?php echo esc_html($summary['in_stock_count'] ?? 0); ?></div>
            </div>
            
            <div class="aia-card aia-warning">
                <h3><?php _e('Low Stock', 'ai-inventory-agent'); ?></h3>
                <div class="aia-stat-value"><?php echo esc_html($summary['low_stock_count'] ?? 0); ?></div>
            </div>
            
            <div class="aia-card aia-danger">
                <h3><?php _e('Out of Stock', 'ai-inventory-agent'); ?></h3>
                <div class="aia-stat-value"><?php echo esc_html($summary['out_of_stock_count'] ?? 0); ?></div>
            </div>
        </div>

        <!-- Stock Value Information -->
        <div class="aia-cards-row">
            <div class="aia-card">
                <h3><?php _e('Total Stock Value', 'ai-inventory-agent'); ?></h3>
                <div class="aia-stat-value"><?php echo wc_price($summary['total_stock_value'] ?? 0); ?></div>
            </div>
            
            <div class="aia-card">
                <h3><?php _e('Low Stock Value', 'ai-inventory-agent'); ?></h3>
                <div class="aia-stat-value"><?php echo wc_price($summary['low_stock_value'] ?? 0); ?></div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="aia-section">
            <h2><?php _e('Recent Stock Changes', 'ai-inventory-agent'); ?></h2>
            <div class="aia-table-container">
                <?php if (!empty($summary['recent_stock_changes'])): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Product', 'ai-inventory-agent'); ?></th>
                                <th><?php _e('Action', 'ai-inventory-agent'); ?></th>
                                <th><?php _e('Old Stock', 'ai-inventory-agent'); ?></th>
                                <th><?php _e('New Stock', 'ai-inventory-agent'); ?></th>
                                <th><?php _e('Date', 'ai-inventory-agent'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($summary['recent_stock_changes'], 0, 10) as $change): ?>
                                <tr>
                                    <td><?php echo esc_html($change->product_name ?? 'Unknown Product'); ?></td>
                                    <td><?php echo esc_html(ucfirst($change->action ?? 'unknown')); ?></td>
                                    <td><?php echo esc_html($change->old_stock ?? '-'); ?></td>
                                    <td><?php echo esc_html($change->new_stock ?? '-'); ?></td>
                                    <td><?php echo esc_html($change->created_at ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No recent stock changes found.', 'ai-inventory-agent'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Low Stock Products -->
        <?php if (!empty($summary['low_stock_products'])): ?>
        <div class="aia-section">
            <h2><?php _e('Low Stock Products', 'ai-inventory-agent'); ?></h2>
            <div class="aia-table-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Product', 'ai-inventory-agent'); ?></th>
                            <th><?php _e('Current Stock', 'ai-inventory-agent'); ?></th>
                            <th><?php _e('Price', 'ai-inventory-agent'); ?></th>
                            <th><?php _e('Actions', 'ai-inventory-agent'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($summary['low_stock_products'], 0, 10) as $product): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($product->post_title); ?></strong>
                                </td>
                                <td>
                                    <span class="aia-stock-level aia-low"><?php echo esc_html($product->stock_quantity); ?></span>
                                </td>
                                <td><?php echo wc_price($product->regular_price ?? 0); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $product->ID . '&action=edit')); ?>" 
                                       class="button button-small"><?php _e('Edit', 'ai-inventory-agent'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.aia-analysis-page .aia-cards-row {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.aia-analysis-page .aia-card {
    flex: 1;
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
}

.aia-analysis-page .aia-card.aia-warning {
    border-left: 4px solid #ffb900;
}

.aia-analysis-page .aia-card.aia-danger {
    border-left: 4px solid #d63638;
}

.aia-analysis-page .aia-stat-value {
    font-size: 2em;
    font-weight: bold;
    margin-top: 10px;
}

.aia-analysis-page .aia-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.aia-analysis-page .aia-stock-level.aia-low {
    color: #d63638;
    font-weight: bold;
}

.aia-analysis-page .aia-table-container {
    overflow-x: auto;
}
</style>