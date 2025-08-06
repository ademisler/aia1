<?php
/**
 * Admin Alerts Template - Light Theme
 * 
 * @package AI_Inventory_Agent
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get notifications module
$plugin_instance = \AIA\Core\Plugin::get_instance();
$notifications_module = $plugin_instance ? $plugin_instance->get_module_manager()->get_module('notifications') : null;
$inventory_analysis = $plugin_instance ? $plugin_instance->get_module_manager()->get_module('inventory_analysis') : null;

// Get current alerts
$low_stock_products = [];
$out_of_stock_products = [];
$critical_stock_products = [];

if ($inventory_analysis) {
    $summary = $inventory_analysis->get_inventory_summary();
    $low_stock_products = $summary['alerts']['low_stock_products'] ?? [];
    $out_of_stock_products = $summary['alerts']['out_of_stock_products'] ?? [];
    
    // Separate critical from low stock
    $settings = get_option('aia_settings', []);
    $critical_threshold = $settings['critical_stock_threshold'] ?? 1;
    
    foreach ($low_stock_products as $key => $product) {
        if ($product->stock_quantity <= $critical_threshold) {
            $critical_stock_products[] = $product;
            unset($low_stock_products[$key]);
        }
    }
}

// Alert statistics
$total_alerts = count($out_of_stock_products) + count($critical_stock_products) + count($low_stock_products);
$critical_count = count($out_of_stock_products) + count($critical_stock_products);
$warning_count = count($low_stock_products);
?>

<div class="wrap aia-alerts-light">
    <!-- Standardized Page Header -->
    <div class="aia-page-header">
        <div class="aia-page-header-content">
            <h1 class="aia-page-title">
                <svg class="aia-icon" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                </svg>
                <?php esc_html_e('Stock Alerts', 'ai-inventory-agent'); ?>
            </h1>
            <p class="aia-page-subtitle">
                <?php esc_html_e('Monitor and manage inventory alerts and notifications', 'ai-inventory-agent'); ?>
            </p>
        </div>
        
        <div class="aia-page-header-actions">
            <button class="aia-btn aia-btn--light" onclick="location.reload()">
                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-refresh"></use>
                </svg>
                <?php esc_html_e('Refresh Alerts', 'ai-inventory-agent'); ?>
            </button>
            
            <button class="aia-btn aia-btn--primary" onclick="exportAlerts()">
                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-download"></use>
                </svg>
                <?php esc_html_e('Export Report', 'ai-inventory-agent'); ?>
            </button>
        </div>
    </div>

    <!-- Alert Statistics -->
    <div class="aia-alert-stats-section">
        <div class="aia-alert-stats-grid">
            <div class="aia-alert-stat-card aia-alert-stat-card--total">
                <div class="aia-alert-stat-icon">
                    <svg class="aia-icon aia-icon--xl" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bell"></use>
                    </svg>
                </div>
                <div class="aia-alert-stat-content">
                    <div class="aia-alert-stat-number"><?php echo esc_html(number_format($total_alerts)); ?></div>
                    <div class="aia-alert-stat-label"><?php esc_html_e('Total Alerts', 'ai-inventory-agent'); ?></div>
                    <div class="aia-alert-stat-description"><?php esc_html_e('Active stock notifications', 'ai-inventory-agent'); ?></div>
                </div>
            </div>

            <div class="aia-alert-stat-card aia-alert-stat-card--critical">
                <div class="aia-alert-stat-icon">
                    <svg class="aia-icon aia-icon--xl" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-circle"></use>
                    </svg>
                </div>
                <div class="aia-alert-stat-content">
                    <div class="aia-alert-stat-number"><?php echo esc_html(number_format($critical_count)); ?></div>
                    <div class="aia-alert-stat-label"><?php esc_html_e('Critical Alerts', 'ai-inventory-agent'); ?></div>
                    <div class="aia-alert-stat-description"><?php esc_html_e('Require immediate attention', 'ai-inventory-agent'); ?></div>
                </div>
            </div>

            <div class="aia-alert-stat-card aia-alert-stat-card--warning">
                <div class="aia-alert-stat-icon">
                    <svg class="aia-icon aia-icon--xl" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                    </svg>
                </div>
                <div class="aia-alert-stat-content">
                    <div class="aia-alert-stat-number"><?php echo esc_html(number_format($warning_count)); ?></div>
                    <div class="aia-alert-stat-label"><?php esc_html_e('Warning Alerts', 'ai-inventory-agent'); ?></div>
                    <div class="aia-alert-stat-description"><?php esc_html_e('Monitor closely', 'ai-inventory-agent'); ?></div>
                </div>
            </div>

            <div class="aia-alert-stat-card aia-alert-stat-card--success">
                <div class="aia-alert-stat-icon">
                    <svg class="aia-icon aia-icon--xl" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-check-circle"></use>
                    </svg>
                </div>
                <div class="aia-alert-stat-content">
                    <div class="aia-alert-stat-number"><?php echo esc_html(number_format(max(0, 100 - $total_alerts))); ?>%</div>
                    <div class="aia-alert-stat-label"><?php esc_html_e('Health Score', 'ai-inventory-agent'); ?></div>
                    <div class="aia-alert-stat-description"><?php esc_html_e('Overall inventory status', 'ai-inventory-agent'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Content Grid -->
    <div class="aia-alerts-content-grid">
        <!-- Left Column - Alert Settings -->
        <div class="aia-alerts-column">
            <div class="aia-widget">
                <div class="aia-widget-header">
                    <div class="aia-widget-header-content">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-settings"></use>
                            </svg>
                            <?php esc_html_e('Alert Configuration', 'ai-inventory-agent'); ?>
                        </h3>
                        <p class="aia-widget-subtitle"><?php esc_html_e('Configure thresholds and notifications', 'ai-inventory-agent'); ?></p>
                    </div>
                </div>
                <div class="aia-widget-content">
                    <form method="post" action="options.php" class="aia-alert-settings-form">
                        <?php 
                        settings_fields('aia_settings');
                        $settings = get_option('aia_settings', []);
                        ?>
                        
                        <div class="aia-form-group">
                            <label for="alerts_low_stock_threshold" class="aia-form-label">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                                </svg>
                                <?php esc_html_e('Low Stock Threshold', 'ai-inventory-agent'); ?>
                            </label>
                            <input type="number" 
                                   id="alerts_low_stock_threshold" 
                                   name="aia_settings[low_stock_threshold]" 
                                   value="<?php echo esc_attr($settings['low_stock_threshold'] ?? 5); ?>" 
                                   min="1" 
                                   class="aia-form-input" />
                            <p class="aia-form-description"><?php esc_html_e('Alert when stock falls below this number', 'ai-inventory-agent'); ?></p>
                        </div>

                        <div class="aia-form-group">
                            <label for="alerts_critical_stock_threshold" class="aia-form-label">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-circle"></use>
                                </svg>
                                <?php esc_html_e('Critical Stock Threshold', 'ai-inventory-agent'); ?>
                            </label>
                            <input type="number" 
                                   id="alerts_critical_stock_threshold" 
                                   name="aia_settings[critical_stock_threshold]" 
                                   value="<?php echo esc_attr($settings['critical_stock_threshold'] ?? 1); ?>" 
                                   min="0" 
                                   class="aia-form-input" />
                            <p class="aia-form-description"><?php esc_html_e('Critical alert when stock falls to this level or below', 'ai-inventory-agent'); ?></p>
                        </div>

                        <div class="aia-form-group">
                            <label for="alerts_notification_email" class="aia-form-label">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-mail"></use>
                                </svg>
                                <?php esc_html_e('Notification Email', 'ai-inventory-agent'); ?>
                            </label>
                            <input type="email" 
                                   id="alerts_notification_email" 
                                   name="aia_settings[notification_email]" 
                                   value="<?php echo esc_attr($settings['notification_email'] ?? get_option('admin_email')); ?>" 
                                   class="aia-form-input" />
                            <p class="aia-form-description"><?php esc_html_e('Email address to receive stock alerts', 'ai-inventory-agent'); ?></p>
                        </div>

                        <div class="aia-form-group aia-form-group--checkbox">
                            <label for="alerts_notifications_enabled" class="aia-checkbox-label">
                                <input type="checkbox" 
                                       id="alerts_notifications_enabled" 
                                       name="aia_settings[notifications_enabled]" 
                                       value="1" 
                                       class="aia-checkbox"
                                       <?php checked($settings['notifications_enabled'] ?? true); ?> />
                                <span class="aia-checkbox-indicator"></span>
                                <span class="aia-checkbox-text">
                                    <?php esc_html_e('Enable Email Notifications', 'ai-inventory-agent'); ?>
                                    <small><?php esc_html_e('Send email notifications for stock alerts', 'ai-inventory-agent'); ?></small>
                                </span>
                            </label>
                        </div>

                        <div class="aia-form-actions">
                            <button type="submit" class="aia-btn aia-btn--primary">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-check"></use>
                                </svg>
                                <?php esc_html_e('Save Settings', 'ai-inventory-agent'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column - Alert Summary -->
        <div class="aia-alerts-column">
            <div class="aia-widget">
                <div class="aia-widget-header">
                    <div class="aia-widget-header-content">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bar-chart"></use>
                            </svg>
                            <?php esc_html_e('Alert Overview', 'ai-inventory-agent'); ?>
                        </h3>
                        <p class="aia-widget-subtitle"><?php esc_html_e('Quick summary of current alerts', 'ai-inventory-agent'); ?></p>
                    </div>
                </div>
                <div class="aia-widget-content">
                    <div class="aia-alert-summary">
                        <div class="aia-summary-item aia-summary-item--critical">
                            <div class="aia-summary-icon">
                                <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-x-circle"></use>
                                </svg>
                            </div>
                            <div class="aia-summary-content">
                                <div class="aia-summary-number"><?php echo esc_html(count($out_of_stock_products)); ?></div>
                                <div class="aia-summary-label"><?php esc_html_e('Out of Stock', 'ai-inventory-agent'); ?></div>
                                <div class="aia-summary-description"><?php esc_html_e('Products completely out of stock', 'ai-inventory-agent'); ?></div>
                            </div>
                        </div>

                        <div class="aia-summary-item aia-summary-item--warning">
                            <div class="aia-summary-icon">
                                <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-circle"></use>
                                </svg>
                            </div>
                            <div class="aia-summary-content">
                                <div class="aia-summary-number"><?php echo esc_html(count($critical_stock_products)); ?></div>
                                <div class="aia-summary-label"><?php esc_html_e('Critical Stock', 'ai-inventory-agent'); ?></div>
                                <div class="aia-summary-description"><?php esc_html_e('Products at critical stock levels', 'ai-inventory-agent'); ?></div>
                            </div>
                        </div>

                        <div class="aia-summary-item aia-summary-item--info">
                            <div class="aia-summary-icon">
                                <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                                </svg>
                            </div>
                            <div class="aia-summary-content">
                                <div class="aia-summary-number"><?php echo esc_html(count($low_stock_products)); ?></div>
                                <div class="aia-summary-label"><?php esc_html_e('Low Stock', 'ai-inventory-agent'); ?></div>
                                <div class="aia-summary-description"><?php esc_html_e('Products running low on stock', 'ai-inventory-agent'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Alerts Section -->
    <?php if (!empty($out_of_stock_products) || !empty($critical_stock_products)): ?>
    <div class="aia-widget aia-widget--full aia-critical-alerts-widget">
        <div class="aia-widget-header">
            <div class="aia-widget-header-content">
                <h3 class="aia-widget-title">
                    <svg class="aia-icon aia-icon--md" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-circle"></use>
                    </svg>
                    <?php esc_html_e('Critical Alerts - Immediate Action Required', 'ai-inventory-agent'); ?>
                </h3>
                <p class="aia-widget-subtitle"><?php esc_html_e('Products that are out of stock or at critical levels', 'ai-inventory-agent'); ?></p>
            </div>
            <div class="aia-widget-header-actions">
                <button class="aia-btn aia-btn--warning aia-btn--sm" onclick="markAllAsViewed('critical')">
                    <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-check"></use>
                    </svg>
                    <?php esc_html_e('Mark All Viewed', 'ai-inventory-agent'); ?>
                </button>
            </div>
        </div>
        <div class="aia-widget-content aia-widget-content--no-padding">
            <div class="aia-alert-list">
                <!-- Out of Stock Products -->
                <?php foreach (array_slice($out_of_stock_products, 0, 15) as $product): ?>
                <div class="aia-alert-item aia-alert-item--critical">
                    <div class="aia-alert-severity">
                        <div class="aia-severity-indicator aia-severity-indicator--critical">
                            <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-x-circle"></use>
                            </svg>
                        </div>
                        <span class="aia-severity-label"><?php esc_html_e('OUT OF STOCK', 'ai-inventory-agent'); ?></span>
                    </div>
                    <div class="aia-alert-product-info">
                        <div class="aia-product-name"><?php echo esc_html($product->post_title); ?></div>
                        <div class="aia-product-details">
                            <span class="aia-product-id">ID: <?php echo esc_html($product->ID); ?></span>
                            <span class="aia-product-stock"><?php esc_html_e('Stock:', 'ai-inventory-agent'); ?> <strong class="aia-stock-critical">0</strong></span>
                            <span class="aia-product-price"><?php echo wp_kses_post(wc_price($product->regular_price ?? 0)); ?></span>
                        </div>
                    </div>
                    <div class="aia-alert-timestamp">
                        <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-clock"></use>
                        </svg>
                        <span><?php echo esc_html(human_time_diff(time() - 3600)); ?> <?php esc_html_e('ago', 'ai-inventory-agent'); ?></span>
                    </div>
                    <div class="aia-alert-actions">
                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $product->ID . '&action=edit')); ?>" 
                           class="aia-btn aia-btn--primary aia-btn--sm">
                            <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-edit"></use>
                            </svg>
                            <?php esc_html_e('Restock Now', 'ai-inventory-agent'); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Critical Stock Products -->
                <?php foreach (array_slice($critical_stock_products, 0, 15) as $product): ?>
                <div class="aia-alert-item aia-alert-item--warning">
                    <div class="aia-alert-severity">
                        <div class="aia-severity-indicator aia-severity-indicator--warning">
                            <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-circle"></use>
                            </svg>
                        </div>
                        <span class="aia-severity-label"><?php esc_html_e('CRITICAL', 'ai-inventory-agent'); ?></span>
                    </div>
                    <div class="aia-alert-product-info">
                        <div class="aia-product-name"><?php echo esc_html($product->post_title); ?></div>
                        <div class="aia-product-details">
                            <span class="aia-product-id">ID: <?php echo esc_html($product->ID); ?></span>
                            <span class="aia-product-stock"><?php esc_html_e('Stock:', 'ai-inventory-agent'); ?> <strong class="aia-stock-warning"><?php echo esc_html($product->stock_quantity); ?></strong></span>
                            <span class="aia-product-price"><?php echo wp_kses_post(wc_price($product->regular_price ?? 0)); ?></span>
                        </div>
                    </div>
                    <div class="aia-alert-timestamp">
                        <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-clock"></use>
                        </svg>
                        <span><?php echo esc_html(human_time_diff(time() - 1800)); ?> <?php esc_html_e('ago', 'ai-inventory-agent'); ?></span>
                    </div>
                    <div class="aia-alert-actions">
                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $product->ID . '&action=edit')); ?>" 
                           class="aia-btn aia-btn--warning aia-btn--sm">
                            <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-edit"></use>
                            </svg>
                            <?php esc_html_e('Manage Stock', 'ai-inventory-agent'); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Low Stock Warnings Section -->
    <?php if (!empty($low_stock_products)): ?>
    <div class="aia-widget aia-widget--full aia-warning-alerts-widget">
        <div class="aia-widget-header">
            <div class="aia-widget-header-content">
                <h3 class="aia-widget-title">
                    <svg class="aia-icon aia-icon--md" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                    </svg>
                    <?php esc_html_e('Low Stock Warnings', 'ai-inventory-agent'); ?>
                </h3>
                <p class="aia-widget-subtitle"><?php esc_html_e('Products that are running low and should be monitored', 'ai-inventory-agent'); ?></p>
            </div>
            <div class="aia-widget-header-actions">
                <button class="aia-btn aia-btn--light aia-btn--sm" onclick="markAllAsViewed('warning')">
                    <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-check"></use>
                    </svg>
                    <?php esc_html_e('Mark All Viewed', 'ai-inventory-agent'); ?>
                </button>
            </div>
        </div>
        <div class="aia-widget-content aia-widget-content--no-padding">
            <div class="aia-alert-list">
                <?php foreach (array_slice($low_stock_products, 0, 20) as $product): ?>
                <div class="aia-alert-item aia-alert-item--info">
                    <div class="aia-alert-severity">
                        <div class="aia-severity-indicator aia-severity-indicator--info">
                            <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                            </svg>
                        </div>
                        <span class="aia-severity-label"><?php esc_html_e('LOW STOCK', 'ai-inventory-agent'); ?></span>
                    </div>
                    <div class="aia-alert-product-info">
                        <div class="aia-product-name"><?php echo esc_html($product->post_title); ?></div>
                        <div class="aia-product-details">
                            <span class="aia-product-id">ID: <?php echo esc_html($product->ID); ?></span>
                            <span class="aia-product-stock"><?php esc_html_e('Stock:', 'ai-inventory-agent'); ?> <strong class="aia-stock-low"><?php echo esc_html($product->stock_quantity); ?></strong></span>
                            <span class="aia-product-price"><?php echo wp_kses_post(wc_price($product->regular_price ?? 0)); ?></span>
                        </div>
                    </div>
                    <div class="aia-alert-timestamp">
                        <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-clock"></use>
                        </svg>
                        <span><?php echo esc_html(human_time_diff(time() - 900)); ?> <?php esc_html_e('ago', 'ai-inventory-agent'); ?></span>
                    </div>
                    <div class="aia-alert-actions">
                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $product->ID . '&action=edit')); ?>" 
                           class="aia-btn aia-btn--outline aia-btn--sm">
                            <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-edit"></use>
                            </svg>
                            <?php esc_html_e('Review Stock', 'ai-inventory-agent'); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- No Alerts State -->
    <?php if (empty($out_of_stock_products) && empty($critical_stock_products) && empty($low_stock_products)): ?>
    <div class="aia-widget aia-widget--full">
        <div class="aia-widget-content">
            <div class="aia-success-state">
                <div class="aia-success-icon">
                    <svg class="aia-icon aia-icon--xxl" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-check-circle"></use>
                    </svg>
                </div>
                <h3 class="aia-success-title"><?php esc_html_e('All Good!', 'ai-inventory-agent'); ?></h3>
                <p class="aia-success-description">
                    <?php esc_html_e('No stock alerts at this time. All your products have adequate stock levels.', 'ai-inventory-agent'); ?>
                </p>
                <div class="aia-success-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=aia-analysis')); ?>" 
                       class="aia-btn aia-btn--primary">
                        <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-analytics"></use>
                        </svg>
                        <?php esc_html_e('View Analysis', 'ai-inventory-agent'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=aia-dashboard')); ?>" 
                       class="aia-btn aia-btn--light">
                        <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-home"></use>
                        </svg>
                        <?php esc_html_e('Back to Dashboard', 'ai-inventory-agent'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Export functionality
    window.exportAlerts = function() {
        // Placeholder for export functionality
        alert('<?php esc_js_e('Export functionality will be implemented in a future version.', 'ai-inventory-agent'); ?>');
    };
    
    // Mark alerts as viewed
    window.markAllAsViewed = function(type) {
        // Placeholder for marking alerts as viewed
        var message = type === 'critical' ? 
            '<?php esc_js_e('All critical alerts marked as viewed.', 'ai-inventory-agent'); ?>' :
            '<?php esc_js_e('All warning alerts marked as viewed.', 'ai-inventory-agent'); ?>';
        
        // Simulate marking as viewed with visual feedback
        $('.aia-alert-item').each(function() {
            if ((type === 'critical' && $(this).hasClass('aia-alert-item--critical')) ||
                (type === 'critical' && $(this).hasClass('aia-alert-item--warning')) ||
                (type === 'warning' && $(this).hasClass('aia-alert-item--info'))) {
                $(this).fadeOut(300).fadeIn(300);
            }
        });
        
        // Show success message
        setTimeout(function() {
            alert(message);
        }, 600);
    };
    
    // Form validation
    $('.aia-alert-settings-form').on('submit', function(e) {
        var lowThreshold = parseInt($('#alerts_low_stock_threshold').val());
        var criticalThreshold = parseInt($('#alerts_critical_stock_threshold').val());
        
        if (criticalThreshold >= lowThreshold) {
            e.preventDefault();
            alert('<?php esc_js_e('Critical threshold must be lower than low stock threshold.', 'ai-inventory-agent'); ?>');
            return false;
        }
    });
    
    // Auto-refresh alerts every 5 minutes
    setInterval(function() {
        // In a real implementation, this would refresh alert data via AJAX
        console.log('Auto-refreshing alerts...');
    }, 300000);
});
</script>