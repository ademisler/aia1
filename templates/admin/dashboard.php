<?php
/**
 * Admin Dashboard Template - Light & Professional
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

<div class="wrap aia-dashboard-light">
    <!-- Skip Link for Accessibility -->
    <a href="#aia-main-content" class="aia-sr-only aia-skip-link"><?php _e('Skip to main content', 'ai-inventory-agent'); ?></a>
    
    <!-- Professional Header -->
    <div class="aia-dashboard-header">
        <div class="aia-dashboard-title-section">
            <h1 class="aia-dashboard-main-title">
                <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-dashboard"></use>
                </svg>
                <?php esc_html_e('Inventory Dashboard', 'ai-inventory-agent'); ?>
            </h1>
            <p class="aia-dashboard-subtitle">
                <?php esc_html_e('AI-powered inventory management and insights', 'ai-inventory-agent'); ?>
            </p>
        </div>
        
        <div class="aia-dashboard-actions">
            <button class="aia-btn aia-btn--light" onclick="location.reload()" title="<?php esc_attr_e('Refresh data', 'ai-inventory-agent'); ?>">
                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-refresh"></use>
                </svg>
                <?php esc_html_e('Refresh', 'ai-inventory-agent'); ?>
            </button>
            
            <a href="<?php echo esc_url(admin_url('admin.php?page=aia-chat')); ?>" 
               class="aia-btn aia-btn--primary">
                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-chat"></use>
                </svg>
                <?php esc_html_e('AI Assistant', 'ai-inventory-agent'); ?>
            </a>
        </div>
    </div>

    <!-- Status Alert Section -->
    <?php if (!empty($summary['alerts'])): ?>
    <div class="aia-alert-banner">
        <div class="aia-alert-content">
            <div class="aia-alert-icon-wrapper">
                <svg class="aia-icon aia-icon--md aia-alert-icon" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-warning"></use>
                </svg>
            </div>
            <div class="aia-alert-text">
                <h4><?php esc_html_e('Attention Required', 'ai-inventory-agent'); ?></h4>
                <p><?php esc_html_e('You have inventory items that need immediate attention.', 'ai-inventory-agent'); ?></p>
            </div>
        </div>
        <a href="<?php echo esc_url(admin_url('admin.php?page=aia-alerts')); ?>" 
           class="aia-btn aia-btn--warning aia-btn--sm">
            <?php esc_html_e('View Details', 'ai-inventory-agent'); ?>
        </a>
    </div>
    <?php endif; ?>

    <!-- Key Metrics Grid -->
    <main id="aia-main-content" class="aia-metrics-section">
        <div class="aia-section-header">
            <h2 class="aia-section-title"><?php esc_html_e('Key Metrics', 'ai-inventory-agent'); ?></h2>
            <p class="aia-section-description"><?php esc_html_e('Real-time overview of your inventory status', 'ai-inventory-agent'); ?></p>
        </div>
        
        <div class="aia-metrics-grid">
            <div class="aia-metric-card aia-metric-card--primary">
                <div class="aia-metric-icon">
                    <svg class="aia-icon aia-icon--xl" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-inventory"></use>
                    </svg>
                </div>
                <div class="aia-metric-content">
                    <div class="aia-metric-number"><?php echo esc_html(number_format($summary['counts']['total_products'] ?? 0)); ?></div>
                    <div class="aia-metric-label"><?php esc_html_e('Total Products', 'ai-inventory-agent'); ?></div>
                    <div class="aia-metric-description"><?php esc_html_e('Active inventory items', 'ai-inventory-agent'); ?></div>
                </div>
            </div>

            <div class="aia-metric-card aia-metric-card--success">
                <div class="aia-metric-icon">
                    <svg class="aia-icon aia-icon--xl" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-check-circle"></use>
                    </svg>
                </div>
                <div class="aia-metric-content">
                    <div class="aia-metric-number"><?php echo esc_html(number_format($summary['counts']['in_stock'] ?? 0)); ?></div>
                    <div class="aia-metric-label"><?php esc_html_e('In Stock', 'ai-inventory-agent'); ?></div>
                    <div class="aia-metric-description"><?php esc_html_e('Available for sale', 'ai-inventory-agent'); ?></div>
                </div>
            </div>

            <div class="aia-metric-card aia-metric-card--warning">
                <div class="aia-metric-icon">
                    <svg class="aia-icon aia-icon--xl" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                    </svg>
                </div>
                <div class="aia-metric-content">
                    <div class="aia-metric-number"><?php echo esc_html(number_format($summary['counts']['low_stock'] ?? 0)); ?></div>
                    <div class="aia-metric-label"><?php esc_html_e('Low Stock', 'ai-inventory-agent'); ?></div>
                    <div class="aia-metric-description"><?php esc_html_e('Need reordering soon', 'ai-inventory-agent'); ?></div>
                </div>
            </div>

            <div class="aia-metric-card aia-metric-card--danger">
                <div class="aia-metric-icon">
                    <svg class="aia-icon aia-icon--xl" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-x-circle"></use>
                    </svg>
                </div>
                <div class="aia-metric-content">
                    <div class="aia-metric-number"><?php echo esc_html(number_format($summary['counts']['out_of_stock'] ?? 0)); ?></div>
                    <div class="aia-metric-label"><?php esc_html_e('Out of Stock', 'ai-inventory-agent'); ?></div>
                    <div class="aia-metric-description"><?php esc_html_e('Immediate attention required', 'ai-inventory-agent'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="aia-content-grid">
        <!-- Left Column -->
        <div class="aia-content-column">
            <!-- Stock Value Widget -->
            <div class="aia-widget">
                <div class="aia-widget-header">
                    <div class="aia-widget-header-content">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-dollar-sign"></use>
                            </svg>
                            <?php esc_html_e('Stock Value', 'ai-inventory-agent'); ?>
                        </h3>
                        <p class="aia-widget-subtitle"><?php esc_html_e('Total inventory worth', 'ai-inventory-agent'); ?></p>
                    </div>
                </div>
                <div class="aia-widget-content">
                    <div class="aia-value-showcase">
                        <div class="aia-value-main">
                            <?php echo wp_kses_post(wc_price($summary['values']['total_stock_value'] ?? 0)); ?>
                        </div>
                        <div class="aia-value-subtitle">
                            <?php esc_html_e('Total inventory value across all products', 'ai-inventory-agent'); ?>
                        </div>
                        <div class="aia-value-trend">
                            <svg class="aia-icon aia-icon--sm aia-trend-icon" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trending-up"></use>
                            </svg>
                            <span class="aia-trend-text"><?php esc_html_e('Updated just now', 'ai-inventory-agent'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Widget -->
            <div class="aia-widget">
                <div class="aia-widget-header">
                    <div class="aia-widget-header-content">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-activity"></use>
                            </svg>
                            <?php esc_html_e('Recent Activity', 'ai-inventory-agent'); ?>
                        </h3>
                        <p class="aia-widget-subtitle"><?php esc_html_e('Latest inventory changes', 'ai-inventory-agent'); ?></p>
                    </div>
                </div>
                <div class="aia-widget-content">
                    <div class="aia-activity-timeline">
                        <?php if (!empty($summary['activity']['recent_changes'])): ?>
                        <div class="aia-activity-item">
                            <div class="aia-activity-icon aia-activity-icon--info">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trending-up"></use>
                                </svg>
                            </div>
                            <div class="aia-activity-content">
                                <div class="aia-activity-title">
                                    <?php printf(
                                        esc_html__('%d Stock Changes', 'ai-inventory-agent'),
                                        $summary['activity']['recent_changes']
                                    ); ?>
                                </div>
                                <div class="aia-activity-description">
                                    <?php esc_html_e('In the last 7 days', 'ai-inventory-agent'); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($summary['activity']['recent_sales'])): ?>
                        <div class="aia-activity-item">
                            <div class="aia-activity-icon aia-activity-icon--success">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-shopping-cart"></use>
                                </svg>
                            </div>
                            <div class="aia-activity-content">
                                <div class="aia-activity-title">
                                    <?php printf(
                                        esc_html__('%d Orders Completed', 'ai-inventory-agent'),
                                        $summary['activity']['recent_sales']['total_orders']
                                    ); ?>
                                </div>
                                <div class="aia-activity-description">
                                    <?php printf(
                                        esc_html__('Average value: %s', 'ai-inventory-agent'),
                                        wc_price($summary['activity']['recent_sales']['average_order_value'])
                                    ); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (empty($summary['activity']['recent_changes']) && empty($summary['activity']['recent_sales'])): ?>
                        <div class="aia-activity-empty">
                            <svg class="aia-icon aia-icon--lg aia-empty-icon" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-clock"></use>
                            </svg>
                            <div class="aia-empty-text">
                                <?php esc_html_e('No recent activity to display', 'ai-inventory-agent'); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="aia-content-column">
            <!-- Quick Actions Widget -->
            <div class="aia-widget">
                <div class="aia-widget-header">
                    <div class="aia-widget-header-content">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-zap"></use>
                            </svg>
                            <?php esc_html_e('Quick Actions', 'ai-inventory-agent'); ?>
                        </h3>
                        <p class="aia-widget-subtitle"><?php esc_html_e('Frequently used features', 'ai-inventory-agent'); ?></p>
                    </div>
                </div>
                <div class="aia-widget-content">
                    <div class="aia-actions-list">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=aia-chat')); ?>" 
                           class="aia-action-item">
                            <div class="aia-action-icon aia-action-icon--primary">
                                <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-chat"></use>
                                </svg>
                            </div>
                            <div class="aia-action-content">
                                <div class="aia-action-title"><?php esc_html_e('AI Assistant', 'ai-inventory-agent'); ?></div>
                                <div class="aia-action-description"><?php esc_html_e('Get intelligent inventory insights', 'ai-inventory-agent'); ?></div>
                            </div>
                            <div class="aia-action-arrow">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-arrow-right"></use>
                                </svg>
                            </div>
                        </a>

                        <a href="<?php echo esc_url(admin_url('admin.php?page=aia-reports')); ?>" 
                           class="aia-action-item">
                            <div class="aia-action-icon aia-action-icon--info">
                                <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bar-chart"></use>
                                </svg>
                            </div>
                            <div class="aia-action-content">
                                <div class="aia-action-title"><?php esc_html_e('Analytics & Reports', 'ai-inventory-agent'); ?></div>
                                <div class="aia-action-description"><?php esc_html_e('Detailed inventory analytics', 'ai-inventory-agent'); ?></div>
                            </div>
                            <div class="aia-action-arrow">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-arrow-right"></use>
                                </svg>
                            </div>
                        </a>

                        <a href="<?php echo esc_url(admin_url('admin.php?page=aia-analysis')); ?>" 
                           class="aia-action-item">
                            <div class="aia-action-icon aia-action-icon--success">
                                <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-analytics"></use>
                                </svg>
                            </div>
                            <div class="aia-action-content">
                                <div class="aia-action-title"><?php esc_html_e('Inventory Analysis', 'ai-inventory-agent'); ?></div>
                                <div class="aia-action-description"><?php esc_html_e('Deep dive into inventory trends', 'ai-inventory-agent'); ?></div>
                            </div>
                            <div class="aia-action-arrow">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-arrow-right"></use>
                                </svg>
                            </div>
                        </a>

                        <a href="<?php echo esc_url(admin_url('admin.php?page=aia-settings')); ?>" 
                           class="aia-action-item">
                            <div class="aia-action-icon aia-action-icon--neutral">
                                <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-settings"></use>
                                </svg>
                            </div>
                            <div class="aia-action-content">
                                <div class="aia-action-title"><?php esc_html_e('Plugin Settings', 'ai-inventory-agent'); ?></div>
                                <div class="aia-action-description"><?php esc_html_e('Configure AI and preferences', 'ai-inventory-agent'); ?></div>
                            </div>
                            <div class="aia-action-arrow">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-arrow-right"></use>
                                </svg>
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
            <div class="aia-widget-header-content">
                <h3 class="aia-widget-title">
                    <svg class="aia-icon aia-icon--md" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                    </svg>
                    <?php esc_html_e('Products Requiring Attention', 'ai-inventory-agent'); ?>
                </h3>
                <p class="aia-widget-subtitle"><?php esc_html_e('Items with low or critical stock levels', 'ai-inventory-agent'); ?></p>
            </div>
            <div class="aia-widget-header-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=aia-alerts')); ?>" 
                   class="aia-btn aia-btn--light aia-btn--sm">
                    <?php esc_html_e('View All Alerts', 'ai-inventory-agent'); ?>
                </a>
            </div>
        </div>
        <div class="aia-widget-content aia-widget-content--no-padding">
            <div class="aia-table-wrapper">
                <table class="aia-table aia-table--modern">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Product', 'ai-inventory-agent'); ?></th>
                            <th><?php esc_html_e('Current Stock', 'ai-inventory-agent'); ?></th>
                            <th><?php esc_html_e('Status', 'ai-inventory-agent'); ?></th>
                            <th><?php esc_html_e('Price', 'ai-inventory-agent'); ?></th>
                            <th><?php esc_html_e('Actions', 'ai-inventory-agent'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($summary['alerts']['low_stock_products'], 0, 5) as $product): ?>
                        <tr class="aia-table-row">
                            <td>
                                <div class="aia-product-cell">
                                    <div class="aia-product-name"><?php echo esc_html($product->post_title); ?></div>
                                    <div class="aia-product-id">ID: <?php echo esc_html($product->ID); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="aia-stock-cell">
                                    <span class="aia-stock-number"><?php echo esc_html($product->stock_quantity); ?></span>
                                    <span class="aia-stock-unit"><?php esc_html_e('units', 'ai-inventory-agent'); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="aia-status-badge aia-status-badge--warning">
                                    <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                                    </svg>
                                    <?php esc_html_e('Low Stock', 'ai-inventory-agent'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="aia-price-cell"><?php echo wp_kses_post(wc_price($product->regular_price)); ?></span>
                            </td>
                            <td>
                                <div class="aia-table-actions">
                                    <a href="<?php echo esc_url(get_edit_post_link($product->ID)); ?>" 
                                       class="aia-btn aia-btn--sm aia-btn--outline"
                                       title="<?php esc_attr_e('Edit product', 'ai-inventory-agent'); ?>">
                                        <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-edit"></use>
                                        </svg>
                                        <?php esc_html_e('Edit', 'ai-inventory-agent'); ?>
                                    </a>
                                </div>
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