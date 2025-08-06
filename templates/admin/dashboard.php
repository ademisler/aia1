<?php
/**
 * Admin Dashboard Template - Clean & Modern
 * 
 * @package AI_Inventory_Agent
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get inventory summary
$inventory_analysis = $this->plugin->get_module_manager()->get_module('inventory_analysis');
$summary = $inventory_analysis ? $inventory_analysis->get_inventory_summary() : [];
?>

<div class="wrap aia-dashboard-clean">
    <!-- Clean Header -->
    <div class="aia-dashboard-header">
        <div class="aia-dashboard-title-section">
            <h1 class="aia-dashboard-main-title">
                <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-dashboard"></use>
                </svg>
                <?php esc_html_e('Inventory Dashboard', 'ai-inventory-agent'); ?>
            </h1>
            <p class="aia-dashboard-subtitle">
                <?php esc_html_e('Monitor and manage your inventory with AI-powered insights', 'ai-inventory-agent'); ?>
            </p>
        </div>
        
        <div class="aia-dashboard-actions">
            <button class="aia-btn aia-btn--secondary" onclick="location.reload()">
                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-refresh"></use>
                </svg>
                <?php esc_html_e('Refresh', 'ai-inventory-agent'); ?>
            </button>
            
            <a href="<?php echo esc_url(admin_url('admin.php?page=ai-inventory-agent-chat')); ?>" 
               class="aia-btn aia-btn--primary">
                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-chat"></use>
                </svg>
                <?php esc_html_e('AI Assistant', 'ai-inventory-agent'); ?>
            </a>
        </div>
    </div>

    <!-- Alert Section (if needed) -->
    <?php if (!empty($summary['alerts'])): ?>
    <div class="aia-alert-banner">
        <div class="aia-alert-content">
            <svg class="aia-icon aia-icon--md aia-alert-icon" aria-hidden="true">
                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-warning"></use>
            </svg>
            <div class="aia-alert-text">
                <h4><?php esc_html_e('Attention Required', 'ai-inventory-agent'); ?></h4>
                <p><?php esc_html_e('You have inventory items that need attention.', 'ai-inventory-agent'); ?></p>
            </div>
        </div>
        <a href="<?php echo esc_url(admin_url('admin.php?page=ai-inventory-agent-alerts')); ?>" 
           class="aia-btn aia-btn--warning aia-btn--sm">
            <?php esc_html_e('View Details', 'ai-inventory-agent'); ?>
        </a>
    </div>
    <?php endif; ?>

    <!-- Clean Stats Grid -->
    <div class="aia-stats-section">
        <div class="aia-stats-grid-clean">
            <div class="aia-stat-item">
                <div class="aia-stat-icon aia-stat-icon--primary">
                    <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-inventory"></use>
                    </svg>
                </div>
                <div class="aia-stat-content">
                    <div class="aia-stat-number"><?php echo esc_html($summary['counts']['total_products'] ?? 0); ?></div>
                    <div class="aia-stat-label"><?php esc_html_e('Total Products', 'ai-inventory-agent'); ?></div>
                </div>
            </div>

            <div class="aia-stat-item">
                <div class="aia-stat-icon aia-stat-icon--success">
                    <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-check-circle"></use>
                    </svg>
                </div>
                <div class="aia-stat-content">
                    <div class="aia-stat-number"><?php echo esc_html($summary['counts']['in_stock'] ?? 0); ?></div>
                    <div class="aia-stat-label"><?php esc_html_e('In Stock', 'ai-inventory-agent'); ?></div>
                </div>
            </div>

            <div class="aia-stat-item">
                <div class="aia-stat-icon aia-stat-icon--warning">
                    <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                    </svg>
                </div>
                <div class="aia-stat-content">
                    <div class="aia-stat-number"><?php echo esc_html($summary['counts']['low_stock'] ?? 0); ?></div>
                    <div class="aia-stat-label"><?php esc_html_e('Low Stock', 'ai-inventory-agent'); ?></div>
                </div>
            </div>

            <div class="aia-stat-item">
                <div class="aia-stat-icon aia-stat-icon--danger">
                    <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-x-circle"></use>
                    </svg>
                </div>
                <div class="aia-stat-content">
                    <div class="aia-stat-number"><?php echo esc_html($summary['counts']['out_of_stock'] ?? 0); ?></div>
                    <div class="aia-stat-label"><?php esc_html_e('Out of Stock', 'ai-inventory-agent'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="aia-dashboard-grid">
        <!-- Left Column -->
        <div class="aia-dashboard-column">
            <!-- Stock Value Widget -->
            <div class="aia-widget">
                <div class="aia-widget-header">
                    <h3 class="aia-widget-title">
                        <svg class="aia-icon aia-icon--md" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-dollar-sign"></use>
                        </svg>
                        <?php esc_html_e('Stock Value', 'ai-inventory-agent'); ?>
                    </h3>
                </div>
                <div class="aia-widget-content">
                    <div class="aia-value-display">
                        <div class="aia-value-amount">
                            <?php echo wc_price($summary['values']['total_stock_value'] ?? 0); ?>
                        </div>
                        <div class="aia-value-description">
                            <?php esc_html_e('Total inventory value across all products', 'ai-inventory-agent'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Widget -->
            <div class="aia-widget">
                <div class="aia-widget-header">
                    <h3 class="aia-widget-title">
                        <svg class="aia-icon aia-icon--md" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-activity"></use>
                        </svg>
                        <?php esc_html_e('Recent Activity', 'ai-inventory-agent'); ?>
                    </h3>
                </div>
                <div class="aia-widget-content">
                    <div class="aia-activity-list">
                        <?php if (!empty($summary['activity']['recent_changes'])): ?>
                        <div class="aia-activity-item">
                            <div class="aia-activity-icon">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trending-up"></use>
                                </svg>
                            </div>
                            <div class="aia-activity-text">
                                <?php printf(
                                    esc_html__('%d stock changes in the last 7 days', 'ai-inventory-agent'),
                                    $summary['activity']['recent_changes']
                                ); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($summary['activity']['recent_sales'])): ?>
                        <div class="aia-activity-item">
                            <div class="aia-activity-icon">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-shopping-cart"></use>
                                </svg>
                            </div>
                            <div class="aia-activity-text">
                                <?php printf(
                                    esc_html__('%d orders completed (%.2f average)', 'ai-inventory-agent'),
                                    $summary['activity']['recent_sales']['total_orders'],
                                    $summary['activity']['recent_sales']['average_order_value']
                                ); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="aia-dashboard-column">
            <!-- Quick Actions Widget -->
            <div class="aia-widget">
                <div class="aia-widget-header">
                    <h3 class="aia-widget-title">
                        <svg class="aia-icon aia-icon--md" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-zap"></use>
                        </svg>
                        <?php esc_html_e('Quick Actions', 'ai-inventory-agent'); ?>
                    </h3>
                </div>
                <div class="aia-widget-content">
                    <div class="aia-actions-grid">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=ai-inventory-agent-chat')); ?>" 
                           class="aia-action-card">
                            <div class="aia-action-icon">
                                <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-chat"></use>
                                </svg>
                            </div>
                            <div class="aia-action-content">
                                <div class="aia-action-title"><?php esc_html_e('AI Chat', 'ai-inventory-agent'); ?></div>
                                <div class="aia-action-description"><?php esc_html_e('Get AI assistance', 'ai-inventory-agent'); ?></div>
                            </div>
                        </a>

                        <a href="<?php echo esc_url(admin_url('admin.php?page=ai-inventory-agent-reports')); ?>" 
                           class="aia-action-card">
                            <div class="aia-action-icon">
                                <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bar-chart"></use>
                                </svg>
                            </div>
                            <div class="aia-action-content">
                                <div class="aia-action-title"><?php esc_html_e('Reports', 'ai-inventory-agent'); ?></div>
                                <div class="aia-action-description"><?php esc_html_e('View analytics', 'ai-inventory-agent'); ?></div>
                            </div>
                        </a>

                        <a href="<?php echo esc_url(admin_url('admin.php?page=ai-inventory-agent-settings')); ?>" 
                           class="aia-action-card">
                            <div class="aia-action-icon">
                                <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-settings"></use>
                                </svg>
                            </div>
                            <div class="aia-action-content">
                                <div class="aia-action-title"><?php esc_html_e('Settings', 'ai-inventory-agent'); ?></div>
                                <div class="aia-action-description"><?php esc_html_e('Configure plugin', 'ai-inventory-agent'); ?></div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Products Table (Full Width) -->
    <?php if (!empty($summary['alerts']['low_stock_products'])): ?>
    <div class="aia-widget aia-widget--full">
        <div class="aia-widget-header">
            <h3 class="aia-widget-title">
                <svg class="aia-icon aia-icon--md" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                </svg>
                <?php esc_html_e('Low Stock Products', 'ai-inventory-agent'); ?>
            </h3>
        </div>
        <div class="aia-widget-content">
            <div class="aia-table-container">
                <table class="aia-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Product', 'ai-inventory-agent'); ?></th>
                            <th><?php esc_html_e('Stock', 'ai-inventory-agent'); ?></th>
                            <th><?php esc_html_e('Price', 'ai-inventory-agent'); ?></th>
                            <th><?php esc_html_e('Actions', 'ai-inventory-agent'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($summary['alerts']['low_stock_products'], 0, 5) as $product): ?>
                        <tr>
                            <td>
                                <div class="aia-product-info">
                                    <strong><?php echo esc_html($product->post_title); ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="aia-badge aia-badge--warning">
                                    <?php echo esc_html($product->stock_quantity); ?>
                                </span>
                            </td>
                            <td>
                                <span class="aia-price"><?php echo wc_price($product->regular_price); ?></span>
                            </td>
                            <td>
                                <a href="<?php echo esc_url(get_edit_post_link($product->ID)); ?>" 
                                   class="aia-btn aia-btn--sm aia-btn--outline">
                                    <?php esc_html_e('Edit', 'ai-inventory-agent'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>