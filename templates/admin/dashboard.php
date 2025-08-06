<?php
/**
 * Admin Dashboard Template - Enhanced Visual Hierarchy
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

<!-- Skip Link for Accessibility -->
<a href="#main-dashboard-content" class="aia-skip-link"><?php _e('Skip to dashboard content', 'ai-inventory-agent'); ?></a>

<div class="wrap aia-admin-page">
    <!-- Page Header with Visual Hierarchy -->
    <header class="aia-page-header">
        <div class="aia-page-header__content">
            <div class="aia-page-title">
                <svg class="aia-icon aia-icon--3xl aia-icon--primary" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-dashboard"></use>
                </svg>
                <div class="aia-page-title__content">
                    <h1 class="aia-heading-1"><?php esc_html_e('Dashboard', 'ai-inventory-agent'); ?></h1>
                    <p class="aia-body-large aia-text-secondary">
                        <?php esc_html_e('AI-powered inventory management at your fingertips', 'ai-inventory-agent'); ?>
                    </p>
                </div>
            </div>
            
            <!-- Quick Actions with Clear Hierarchy -->
            <div class="aia-page-actions">
                <button class="aia-button aia-button--secondary aia-icon-rotate" onclick="location.reload()">
                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-refresh"></use>
                    </svg>
                    <?php esc_html_e('Refresh', 'ai-inventory-agent'); ?>
                </button>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=ai-inventory-agent-chat')); ?>" 
                   class="aia-button aia-button--primary aia-morph-button">
                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-chat"></use>
                    </svg>
                    <?php esc_html_e('Open AI Chat', 'ai-inventory-agent'); ?>
                </a>
            </div>
        </div>
    </header>

    <main id="main-dashboard-content" class="aia-main-content">
        <!-- Status Alert Section -->
        <?php if (!empty($summary['alerts'])): ?>
        <div class="aia-alert-section aia-animate-on-scroll">
            <div class="aia-alert aia-alert--warning aia-heartbeat">
                <div class="aia-alert__icon">
                    <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-warning"></use>
                    </svg>
                </div>
                <div class="aia-alert__content">
                    <h3 class="aia-heading-4"><?php esc_html_e('Attention Required', 'ai-inventory-agent'); ?></h3>
                    <p class="aia-body-base"><?php esc_html_e('You have inventory items that need attention.', 'ai-inventory-agent'); ?></p>
                </div>
                <div class="aia-alert__actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=ai-inventory-agent-alerts')); ?>" 
                       class="aia-button aia-button--warning">
                        <?php esc_html_e('View Alerts', 'ai-inventory-agent'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Key Performance Indicators with Visual Hierarchy -->
        <div class="aia-kpi-section aia-animate-on-scroll">
            <div class="aia-section-header">
                <h2 class="aia-heading-2">
                    <svg class="aia-icon aia-icon--lg aia-icon--primary" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-analytics"></use>
                    </svg>
                    <?php esc_html_e('Inventory Overview', 'ai-inventory-agent'); ?>
                </h2>
                <p class="aia-body-base aia-text-tertiary">
                    <?php esc_html_e('Real-time insights into your inventory performance', 'ai-inventory-agent'); ?>
                </p>
            </div>
            
            <div class="aia-stats-grid aia-stagger-children">
                <div class="aia-stat-card aia-hover-lift">
                    <div class="aia-stat-card__icon aia-status-icon--info">
                        <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-inventory"></use>
                        </svg>
                    </div>
                    <div class="aia-stat-card__content">
                        <div class="aia-stat-card__value"><?php echo esc_html($summary['counts']['total_products'] ?? 0); ?></div>
                        <div class="aia-stat-card__label"><?php esc_html_e('Total Products', 'ai-inventory-agent'); ?></div>
                    </div>
                </div>
                <div class="aia-stat-box">
                    <span class="aia-stat-value"><?php echo esc_html($summary['counts']['in_stock'] ?? 0); ?></span>
                    <span class="aia-stat-label"><?php esc_html_e('In Stock', 'ai-inventory-agent'); ?></span>
                </div>
                <div class="aia-stat-box warning">
                    <span class="aia-stat-value"><?php echo esc_html($summary['counts']['low_stock'] ?? 0); ?></span>
                    <span class="aia-stat-label"><?php esc_html_e('Low Stock', 'ai-inventory-agent'); ?></span>
                </div>
                <div class="aia-stat-box error">
                    <span class="aia-stat-value"><?php echo esc_html($summary['counts']['out_of_stock'] ?? 0); ?></span>
                    <span class="aia-stat-label"><?php esc_html_e('Out of Stock', 'ai-inventory-agent'); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Stock Value -->
        <div class="aia-dashboard-widget">
            <h2><?php esc_html_e('Stock Value', 'ai-inventory-agent'); ?></h2>
            <div class="aia-value-display">
                <span class="aia-currency-value">
                    <?php echo wc_price($summary['values']['total_stock_value'] ?? 0); ?>
                </span>
                <span class="aia-value-label"><?php esc_html_e('Total Stock Value', 'ai-inventory-agent'); ?></span>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="aia-dashboard-widget">
            <h2><?php esc_html_e('Recent Activity', 'ai-inventory-agent'); ?></h2>
            <div class="aia-activity-list">
                <?php if (!empty($summary['activity']['recent_changes'])): ?>
                    <p><?php printf(
                        esc_html__('%d stock changes in the last 7 days', 'ai-inventory-agent'),
                        $summary['activity']['recent_changes']
                    ); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($summary['activity']['recent_sales'])): ?>
                    <p><?php printf(
                        esc_html__('%d orders completed (%.2f average)', 'ai-inventory-agent'),
                        $summary['activity']['recent_sales']['total_orders'],
                        $summary['activity']['recent_sales']['average_order_value']
                    ); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="aia-dashboard-widget">
            <h2><?php esc_html_e('Quick Actions', 'ai-inventory-agent'); ?></h2>
            <div class="aia-quick-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=ai-inventory-agent-chat')); ?>" class="button button-primary">
                    <span class="dashicons dashicons-format-chat"></span>
                    <?php esc_html_e('Open AI Chat', 'ai-inventory-agent'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ai-inventory-agent-reports')); ?>" class="button">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <?php esc_html_e('View Reports', 'ai-inventory-agent'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ai-inventory-agent-settings')); ?>" class="button">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php esc_html_e('Settings', 'ai-inventory-agent'); ?>
                </a>
            </div>
        </div>
        
        <!-- Low Stock Products -->
        <?php if (!empty($summary['alerts']['low_stock_products'])): ?>
        <div class="aia-dashboard-widget aia-full-width">
            <h2><?php esc_html_e('Low Stock Products', 'ai-inventory-agent'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Product', 'ai-inventory-agent'); ?></th>
                        <th><?php esc_html_e('Current Stock', 'ai-inventory-agent'); ?></th>
                        <th><?php esc_html_e('Price', 'ai-inventory-agent'); ?></th>
                        <th><?php esc_html_e('Actions', 'ai-inventory-agent'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($summary['alerts']['low_stock_products'], 0, 5) as $product): ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url(get_edit_post_link($product->ID)); ?>">
                                <?php echo esc_html($product->post_title); ?>
                            </a>
                        </td>
                        <td>
                            <span class="aia-stock-badge low">
                                <?php echo esc_html($product->stock_quantity); ?>
                            </span>
                        </td>
                        <td><?php echo wc_price($product->regular_price); ?></td>
                        <td>
                            <a href="<?php echo esc_url(get_edit_post_link($product->ID)); ?>" class="button button-small">
                                <?php esc_html_e('Edit', 'ai-inventory-agent'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>