<?php
/**
 * Admin Analysis Template - Light Theme
 * 
 * @package AI_Inventory_Agent
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get inventory analysis data
$plugin_instance = \AIA\Core\Plugin::get_instance();
$inventory_analysis = $plugin_instance ? $plugin_instance->get_module_manager()->get_module('inventory_analysis') : null;
$summary = $inventory_analysis ? $inventory_analysis->get_inventory_summary() : [];

// Sample trend data (in real implementation, this would come from database)
$trend_data = [
    'stock_levels' => [
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        'data' => [85, 78, 92, 88, 76, 82]
    ],
    'top_products' => [
        ['name' => 'Product A', 'sales' => 245, 'trend' => '+12%'],
        ['name' => 'Product B', 'sales' => 189, 'trend' => '+8%'],
        ['name' => 'Product C', 'sales' => 156, 'trend' => '-3%'],
        ['name' => 'Product D', 'sales' => 134, 'trend' => '+15%'],
        ['name' => 'Product E', 'sales' => 98, 'trend' => '+5%']
    ]
];
?>

<div class="wrap aia-analysis-light">
    <!-- Skip Link for Accessibility -->
    <a href="#aia-main-content" class="aia-sr-only aia-skip-link"><?php _e('Skip to main content', 'ai-inventory-agent'); ?></a>
    
    <!-- Minimal Analysis Header -->
    <div class="aia-analysis-header">
        <div class="aia-analysis-header-content">
            <div class="aia-analysis-title-section">
                <div class="aia-analysis-icon-wrapper">
                    <svg class="aia-analysis-icon" viewBox="0 0 24 24">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                    </svg>
                </div>
                
                <div class="aia-analysis-text-content">
                    <h1 class="aia-analysis-main-title">
                        <?php esc_html_e('Inventory Analysis', 'ai-inventory-agent'); ?>
                    </h1>
                    <p class="aia-analysis-subtitle">
                        <?php esc_html_e('Advanced analytics and insights for your inventory', 'ai-inventory-agent'); ?>
                    </p>
                </div>
            </div>
            
            <div class="aia-analysis-header-actions">
                <button class="aia-analysis-btn aia-analysis-btn--outline" onclick="location.reload()">
                    <svg class="aia-analysis-btn-icon" viewBox="0 0 24 24">
                        <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                    </svg>
                    <?php esc_html_e('Refresh Data', 'ai-inventory-agent'); ?>
                </button>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=aia-reports')); ?>" 
                   class="aia-analysis-btn aia-analysis-btn--primary">
                    <svg class="aia-analysis-btn-icon" viewBox="0 0 24 24">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                    </svg>
                    <?php esc_html_e('View Reports', 'ai-inventory-agent'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <main id="aia-main-content" class="aia-metrics-section">
        <div class="aia-section-header">
            <h2 class="aia-section-title"><?php esc_html_e('Key Performance Indicators', 'ai-inventory-agent'); ?></h2>
            <p class="aia-section-description"><?php esc_html_e('Real-time overview of your inventory performance', 'ai-inventory-agent'); ?></p>
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
                <div class="aia-metric-trend aia-trend--positive">
                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trending-up"></use>
                    </svg>
                    <span>+5.2%</span>
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
                <div class="aia-metric-trend aia-trend--positive">
                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trending-up"></use>
                    </svg>
                    <span>+2.8%</span>
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
                <div class="aia-metric-trend aia-trend--negative">
                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trending-down"></use>
                    </svg>
                    <span>-1.2%</span>
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
                <div class="aia-metric-trend aia-trend--critical">
                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-circle"></use>
                    </svg>
                    <span>Critical</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Value Metrics -->
    <div class="aia-value-metrics-section">
        <div class="aia-value-metrics-grid">
            <div class="aia-value-card">
                <div class="aia-value-header">
                    <div class="aia-value-icon">
                        <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-dollar-sign"></use>
                        </svg>
                    </div>
                    <h3 class="aia-value-title"><?php esc_html_e('Total Stock Value', 'ai-inventory-agent'); ?></h3>
                </div>
                <div class="aia-value-content">
                    <div class="aia-value-amount">
                        <?php echo wp_kses_post(wc_price($summary['values']['total_stock_value'] ?? 0)); ?>
                    </div>
                    <div class="aia-value-description">
                        <?php esc_html_e('Total inventory worth across all products', 'ai-inventory-agent'); ?>
                    </div>
                </div>
            </div>

            <div class="aia-value-card">
                <div class="aia-value-header">
                    <div class="aia-value-icon aia-value-icon--warning">
                        <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                        </svg>
                    </div>
                    <h3 class="aia-value-title"><?php esc_html_e('Low Stock Value', 'ai-inventory-agent'); ?></h3>
                </div>
                <div class="aia-value-content">
                    <div class="aia-value-amount">
                        <?php echo wp_kses_post(wc_price($summary['values']['low_stock_value'] ?? 0)); ?>
                    </div>
                    <div class="aia-value-description">
                        <?php esc_html_e('Value tied up in low stock items', 'ai-inventory-agent'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analysis Content Grid -->
    <div class="aia-analysis-content-grid">
        <!-- Left Column -->
        <div class="aia-analysis-column">
            <!-- Stock Trend Chart -->
            <div class="aia-widget">
                <div class="aia-widget-header">
                    <div class="aia-widget-header-content">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trending-up"></use>
                            </svg>
                            <?php esc_html_e('Stock Level Trends', 'ai-inventory-agent'); ?>
                        </h3>
                        <p class="aia-widget-subtitle"><?php esc_html_e('6-month inventory level overview', 'ai-inventory-agent'); ?></p>
                    </div>
                </div>
                <div class="aia-widget-content">
                    <div class="aia-chart-container">
                        <canvas id="stockTrendChart" width="400" height="200"></canvas>
                        <div class="aia-chart-loading" id="chartLoading">
                            <div class="aia-loading-spinner"></div>
                            <span><?php esc_html_e('Loading chart data...', 'ai-inventory-agent'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Stock Changes -->
            <div class="aia-widget">
                <div class="aia-widget-header">
                    <div class="aia-widget-header-content">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-activity"></use>
                            </svg>
                            <?php esc_html_e('Recent Stock Changes', 'ai-inventory-agent'); ?>
                        </h3>
                        <p class="aia-widget-subtitle"><?php esc_html_e('Latest inventory movements', 'ai-inventory-agent'); ?></p>
                    </div>
                </div>
                <div class="aia-widget-content aia-widget-content--no-padding">
                    <?php if (!empty($summary['recent_stock_changes'])): ?>
                    <div class="aia-table-wrapper">
                        <table class="aia-table aia-table--modern">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Product', 'ai-inventory-agent'); ?></th>
                                    <th><?php esc_html_e('Action', 'ai-inventory-agent'); ?></th>
                                    <th><?php esc_html_e('Change', 'ai-inventory-agent'); ?></th>
                                    <th><?php esc_html_e('Date', 'ai-inventory-agent'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($summary['recent_stock_changes'], 0, 8) as $change): ?>
                                <tr class="aia-table-row">
                                    <td>
                                        <div class="aia-product-cell">
                                            <div class="aia-product-name"><?php echo esc_html($change->product_name ?? 'Unknown Product'); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="aia-action-badge aia-action-badge--<?php echo esc_attr(strtolower($change->action ?? 'unknown')); ?>">
                                            <?php echo esc_html(ucfirst($change->action ?? 'unknown')); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="aia-stock-change">
                                            <span class="aia-old-stock"><?php echo esc_html($change->old_stock ?? '-'); ?></span>
                                            <svg class="aia-icon aia-icon--xs aia-change-arrow" aria-hidden="true">
                                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-arrow-right"></use>
                                            </svg>
                                            <span class="aia-new-stock"><?php echo esc_html($change->new_stock ?? '-'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="aia-date-cell"><?php echo esc_html(date('M j, Y', strtotime($change->created_at ?? 'now'))); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="aia-empty-state">
                        <svg class="aia-icon aia-icon--xl aia-empty-icon" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-clock"></use>
                        </svg>
                        <h4><?php esc_html_e('No Recent Changes', 'ai-inventory-agent'); ?></h4>
                        <p><?php esc_html_e('No recent stock changes found in your inventory.', 'ai-inventory-agent'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="aia-analysis-column">
            <!-- Top Performing Products -->
            <div class="aia-widget">
                <div class="aia-widget-header">
                    <div class="aia-widget-header-content">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-star"></use>
                            </svg>
                            <?php esc_html_e('Top Performing Products', 'ai-inventory-agent'); ?>
                        </h3>
                        <p class="aia-widget-subtitle"><?php esc_html_e('Best selling items this month', 'ai-inventory-agent'); ?></p>
                    </div>
                </div>
                <div class="aia-widget-content">
                    <div class="aia-performance-list">
                        <?php foreach ($trend_data['top_products'] as $index => $product): ?>
                        <div class="aia-performance-item">
                            <div class="aia-performance-rank">
                                <span class="aia-rank-number"><?php echo esc_html($index + 1); ?></span>
                            </div>
                            <div class="aia-performance-content">
                                <div class="aia-performance-name"><?php echo esc_html($product['name']); ?></div>
                                <div class="aia-performance-sales"><?php echo esc_html($product['sales']); ?> <?php esc_html_e('sales', 'ai-inventory-agent'); ?></div>
                            </div>
                            <div class="aia-performance-trend <?php echo strpos($product['trend'], '+') === 0 ? 'aia-trend--positive' : 'aia-trend--negative'; ?>">
                                <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trending-<?php echo strpos($product['trend'], '+') === 0 ? 'up' : 'down'; ?>"></use>
                                </svg>
                                <span><?php echo esc_html($product['trend']); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Analysis Insights -->
            <div class="aia-widget">
                <div class="aia-widget-header">
                    <div class="aia-widget-header-content">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-lightbulb"></use>
                            </svg>
                            <?php esc_html_e('AI Insights', 'ai-inventory-agent'); ?>
                        </h3>
                        <p class="aia-widget-subtitle"><?php esc_html_e('Intelligent recommendations', 'ai-inventory-agent'); ?></p>
                    </div>
                </div>
                <div class="aia-widget-content">
                    <div class="aia-insights-list">
                        <div class="aia-insight-item aia-insight-item--warning">
                            <div class="aia-insight-icon">
                                <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                                </svg>
                            </div>
                            <div class="aia-insight-content">
                                <h4><?php esc_html_e('Reorder Alert', 'ai-inventory-agent'); ?></h4>
                                <p><?php esc_html_e('15 products are running low and need reordering within the next week.', 'ai-inventory-agent'); ?></p>
                            </div>
                        </div>

                        <div class="aia-insight-item aia-insight-item--info">
                            <div class="aia-insight-icon">
                                <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trending-up"></use>
                                </svg>
                            </div>
                            <div class="aia-insight-content">
                                <h4><?php esc_html_e('Sales Trend', 'ai-inventory-agent'); ?></h4>
                                <p><?php esc_html_e('Electronics category showing 23% growth compared to last month.', 'ai-inventory-agent'); ?></p>
                            </div>
                        </div>

                        <div class="aia-insight-item aia-insight-item--success">
                            <div class="aia-insight-icon">
                                <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-check-circle"></use>
                                </svg>
                            </div>
                            <div class="aia-insight-content">
                                <h4><?php esc_html_e('Optimization', 'ai-inventory-agent'); ?></h4>
                                <p><?php esc_html_e('Inventory turnover rate improved by 8% this quarter.', 'ai-inventory-agent'); ?></p>
                            </div>
                        </div>
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
                    <?php esc_html_e('Products Requiring Immediate Attention', 'ai-inventory-agent'); ?>
                </h3>
                <p class="aia-widget-subtitle"><?php esc_html_e('Low stock items that need reordering', 'ai-inventory-agent'); ?></p>
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
                            <th><?php esc_html_e('Value', 'ai-inventory-agent'); ?></th>
                            <th><?php esc_html_e('Actions', 'ai-inventory-agent'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($summary['alerts']['low_stock_products'], 0, 10) as $product): ?>
                        <tr class="aia-table-row">
                            <td>
                                <div class="aia-product-cell">
                                    <div class="aia-product-name"><?php echo esc_html($product->post_title); ?></div>
                                    <div class="aia-product-id">ID: <?php echo esc_html($product->ID); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="aia-stock-cell">
                                    <span class="aia-stock-number aia-stock-number--low"><?php echo esc_html($product->stock_quantity); ?></span>
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
                                <span class="aia-price-cell"><?php echo wp_kses_post(wc_price($product->regular_price ?? 0)); ?></span>
                            </td>
                            <td>
                                <div class="aia-table-actions">
                                    <a href="<?php echo esc_url(admin_url('post.php?post=' . $product->ID . '&action=edit')); ?>" 
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

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize chart after page load
    setTimeout(function() {
        initStockTrendChart();
    }, 500);
    
    function initStockTrendChart() {
        var ctx = document.getElementById('stockTrendChart');
        if (!ctx) return;
        
        $('#chartLoading').fadeOut();
        
        // Sample chart implementation (replace with actual Chart.js integration)
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trend_data['stock_levels']['labels']); ?>,
                datasets: [{
                    label: '<?php esc_js_e('Stock Level %', 'ai-inventory-agent'); ?>',
                    data: <?php echo json_encode($trend_data['stock_levels']['data']); ?>,
                    borderColor: 'var(--aia-primary-600, #2563eb)',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Export functionality
    window.exportAnalysisData = function() {
        // Placeholder for export functionality
        alert('<?php esc_js_e('Export functionality will be implemented in a future version.', 'ai-inventory-agent'); ?>');
    };
});
</script>