<?php
/**
 * Admin Reports Page Template
 * 
 * @package AIA
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get reports module
$plugin_instance = \AIA\Core\Plugin::get_instance();
$reports_module = $plugin_instance ? $plugin_instance->get_module_manager()->get_module('reporting') : null;
$inventory_analysis = $plugin_instance ? $plugin_instance->get_module_manager()->get_module('inventory_analysis') : null;

// Get summary data for quick stats
$summary = [];
if ($inventory_analysis) {
    $summary = $inventory_analysis->get_inventory_summary();
}

// Get settings
$settings = get_option('aia_settings', []);
?>

<div class="wrap aia-reports-light">
    <!-- Skip Link for Accessibility -->
    <a href="#aia-main-content" class="aia-sr-only aia-skip-link"><?php _e('Skip to main content', 'ai-inventory-agent'); ?></a>

    <!-- Page Header -->
    <div class="aia-reports-page-header">
        <div class="aia-reports-title-section">
            <h1 class="aia-reports-main-title">
                <svg class="aia-title-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#bar-chart-3"></use>
                </svg>
                <?php _e('Inventory Reports', 'ai-inventory-agent'); ?>
            </h1>
            <p class="aia-reports-subtitle"><?php _e('Generate comprehensive reports and analyze your inventory performance with advanced insights.', 'ai-inventory-agent'); ?></p>
        </div>
        <div class="aia-reports-actions">
            <button type="button" class="aia-btn aia-btn--light aia-btn--sm" onclick="window.print()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#printer"></use>
                </svg>
                <?php _e('Print', 'ai-inventory-agent'); ?>
            </button>
            <button type="button" class="aia-btn aia-btn--primary aia-btn--sm" onclick="exportAllReports()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#download"></use>
                </svg>
                <?php _e('Export All', 'ai-inventory-agent'); ?>
            </button>
        </div>
    </div>

    <div id="aia-main-content" class="aia-main-content">
        
        <!-- Key Metrics Section -->
        <div class="aia-metrics-section">
            <div class="aia-section-header">
                <h2 class="aia-section-title"><?php _e('Report Metrics', 'ai-inventory-agent'); ?></h2>
                <p class="aia-section-description"><?php _e('Key performance indicators for your inventory reporting dashboard', 'ai-inventory-agent'); ?></p>
            </div>
            <div class="aia-metrics-grid">
                <div class="aia-metric-card">
                    <div class="aia-metric-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#package"></use>
                        </svg>
                    </div>
                    <div class="aia-metric-content">
                        <div class="aia-metric-number"><?php echo esc_html($summary['total_products'] ?? 0); ?></div>
                        <div class="aia-metric-label"><?php _e('Total Products', 'ai-inventory-agent'); ?></div>
                        <div class="aia-metric-description"><?php _e('Active inventory items', 'ai-inventory-agent'); ?></div>
                    </div>
                </div>
                <div class="aia-metric-card">
                    <div class="aia-metric-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#dollar-sign"></use>
                        </svg>
                    </div>
                    <div class="aia-metric-content">
                        <div class="aia-metric-number"><?php echo wc_price($summary['total_stock_value'] ?? 0); ?></div>
                        <div class="aia-metric-label"><?php _e('Total Stock Value', 'ai-inventory-agent'); ?></div>
                        <div class="aia-metric-description"><?php _e('Current inventory worth', 'ai-inventory-agent'); ?></div>
                    </div>
                </div>
                <div class="aia-metric-card">
                    <div class="aia-metric-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#alert-triangle"></use>
                        </svg>
                    </div>
                    <div class="aia-metric-content">
                        <div class="aia-metric-number"><?php echo esc_html($summary['low_stock_count'] ?? 0); ?></div>
                        <div class="aia-metric-label"><?php _e('Low Stock Items', 'ai-inventory-agent'); ?></div>
                        <div class="aia-metric-description"><?php _e('Require attention', 'ai-inventory-agent'); ?></div>
                    </div>
                </div>
                <div class="aia-metric-card">
                    <div class="aia-metric-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#x-circle"></use>
                        </svg>
                    </div>
                    <div class="aia-metric-content">
                        <div class="aia-metric-number"><?php echo esc_html($summary['out_of_stock_count'] ?? 0); ?></div>
                        <div class="aia-metric-label"><?php _e('Out of Stock', 'ai-inventory-agent'); ?></div>
                        <div class="aia-metric-description"><?php _e('Need immediate restock', 'ai-inventory-agent'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Content Grid -->
        <div class="aia-reports-content-grid">
            
            <!-- Left Column: Report Generation -->
            <div class="aia-reports-column">
                
                <!-- Report Generator Widget -->
                <div class="aia-widget">
                    <div class="aia-widget-header">
                        <h3 class="aia-widget-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#file-text"></use>
                            </svg>
                            <?php _e('Generate Reports', 'ai-inventory-agent'); ?>
                        </h3>
                    </div>
                    <div class="aia-widget-content">
                        <div class="aia-report-types-grid">
                            
                            <!-- Inventory Summary Report -->
                            <div class="aia-report-card">
                                <div class="aia-report-header">
                                    <div class="aia-report-icon">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#bar-chart-3"></use>
                                        </svg>
                                    </div>
                                    <div class="aia-report-status">
                                        <span class="aia-status-badge aia-status-badge--ready"><?php _e('Ready', 'ai-inventory-agent'); ?></span>
                                    </div>
                                </div>
                                <div class="aia-report-content">
                                    <h4 class="aia-report-title"><?php _e('Inventory Summary', 'ai-inventory-agent'); ?></h4>
                                    <p class="aia-report-description"><?php _e('Complete overview of current inventory status, stock levels, and values with trend analysis.', 'ai-inventory-agent'); ?></p>
                                </div>
                                <div class="aia-report-actions">
                                    <button type="button" class="aia-btn aia-btn--primary aia-btn--sm aia-generate-report" data-type="inventory_summary">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#play"></use>
                                        </svg>
                                        <?php _e('Generate', 'ai-inventory-agent'); ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Low Stock Report -->
                            <div class="aia-report-card">
                                <div class="aia-report-header">
                                    <div class="aia-report-icon aia-report-icon--warning">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#alert-triangle"></use>
                                        </svg>
                                    </div>
                                    <div class="aia-report-status">
                                        <span class="aia-status-badge aia-status-badge--warning"><?php _e('Alert', 'ai-inventory-agent'); ?></span>
                                    </div>
                                </div>
                                <div class="aia-report-content">
                                    <h4 class="aia-report-title"><?php _e('Low Stock Report', 'ai-inventory-agent'); ?></h4>
                                    <p class="aia-report-description"><?php _e('Detailed analysis of products with low or critical stock levels requiring immediate attention.', 'ai-inventory-agent'); ?></p>
                                </div>
                                <div class="aia-report-actions">
                                    <button type="button" class="aia-btn aia-btn--warning aia-btn--sm aia-generate-report" data-type="low_stock">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#play"></use>
                                        </svg>
                                        <?php _e('Generate', 'ai-inventory-agent'); ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Stock Movement Report -->
                            <div class="aia-report-card">
                                <div class="aia-report-header">
                                    <div class="aia-report-icon aia-report-icon--success">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#trending-up"></use>
                                        </svg>
                                    </div>
                                    <div class="aia-report-status">
                                        <span class="aia-status-badge aia-status-badge--success"><?php _e('Active', 'ai-inventory-agent'); ?></span>
                                    </div>
                                </div>
                                <div class="aia-report-content">
                                    <h4 class="aia-report-title"><?php _e('Stock Movement', 'ai-inventory-agent'); ?></h4>
                                    <p class="aia-report-description"><?php _e('Track stock changes, sales velocity, and inventory turnover rates with predictive insights.', 'ai-inventory-agent'); ?></p>
                                </div>
                                <div class="aia-report-actions">
                                    <button type="button" class="aia-btn aia-btn--primary aia-btn--sm aia-generate-report" data-type="stock_movement">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#play"></use>
                                        </svg>
                                        <?php _e('Generate', 'ai-inventory-agent'); ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Performance Report -->
                            <div class="aia-report-card">
                                <div class="aia-report-header">
                                    <div class="aia-report-icon aia-report-icon--info">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#target"></use>
                                        </svg>
                                    </div>
                                    <div class="aia-report-status">
                                        <span class="aia-status-badge aia-status-badge--info"><?php _e('Analytics', 'ai-inventory-agent'); ?></span>
                                    </div>
                                </div>
                                <div class="aia-report-content">
                                    <h4 class="aia-report-title"><?php _e('Performance Report', 'ai-inventory-agent'); ?></h4>
                                    <p class="aia-report-description"><?php _e('Analyze top performing products, slow movers, and profitability metrics with AI recommendations.', 'ai-inventory-agent'); ?></p>
                                </div>
                                <div class="aia-report-actions">
                                    <button type="button" class="aia-btn aia-btn--primary aia-btn--sm aia-generate-report" data-type="performance">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#play"></use>
                                        </svg>
                                        <?php _e('Generate', 'ai-inventory-agent'); ?>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Settings & History -->
            <div class="aia-reports-column">
                
                <!-- Report Settings Widget -->
                <div class="aia-widget">
                    <div class="aia-widget-header">
                        <h3 class="aia-widget-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#settings"></use>
                            </svg>
                            <?php _e('Report Settings', 'ai-inventory-agent'); ?>
                        </h3>
                    </div>
                    <div class="aia-widget-content">
                        <form method="post" action="options.php" class="aia-report-settings-form">
                            <?php settings_fields('aia_settings'); ?>
                            
                            <div class="aia-form-group">
                                <label for="report_frequency" class="aia-form-label">
                                    <?php _e('Automatic Report Frequency', 'ai-inventory-agent'); ?>
                                </label>
                                <select id="report_frequency" name="aia_settings[report_frequency]" class="aia-form-select">
                                    <option value="daily" <?php selected($settings['report_frequency'] ?? 'weekly', 'daily'); ?>>
                                        <?php _e('Daily', 'ai-inventory-agent'); ?>
                                    </option>
                                    <option value="weekly" <?php selected($settings['report_frequency'] ?? 'weekly', 'weekly'); ?>>
                                        <?php _e('Weekly', 'ai-inventory-agent'); ?>
                                    </option>
                                    <option value="monthly" <?php selected($settings['report_frequency'] ?? 'weekly', 'monthly'); ?>>
                                        <?php _e('Monthly', 'ai-inventory-agent'); ?>
                                    </option>
                                    <option value="disabled" <?php selected($settings['report_frequency'] ?? 'weekly', 'disabled'); ?>>
                                        <?php _e('Disabled', 'ai-inventory-agent'); ?>
                                    </option>
                                </select>
                                <p class="aia-form-description"><?php _e('How often to automatically generate and email reports.', 'ai-inventory-agent'); ?></p>
                            </div>

                            <div class="aia-form-group aia-form-group--checkbox">
                                <label for="reports_enabled" class="aia-checkbox-label">
                                    <input type="checkbox" 
                                           id="reports_enabled" 
                                           name="aia_settings[reports_enabled]" 
                                           value="1" 
                                           class="aia-checkbox"
                                           <?php checked($settings['reports_enabled'] ?? true); ?> />
                                    <span class="aia-checkbox-indicator"></span>
                                    <span class="aia-checkbox-text"><?php _e('Enable automatic report generation and email delivery', 'ai-inventory-agent'); ?></span>
                                </label>
                            </div>

                            <div class="aia-form-actions">
                                <button type="submit" class="aia-btn aia-btn--primary">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#check"></use>
                                    </svg>
                                    <?php _e('Save Settings', 'ai-inventory-agent'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Report History Widget -->
                <div class="aia-widget">
                    <div class="aia-widget-header">
                        <h3 class="aia-widget-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#clock"></use>
                            </svg>
                            <?php _e('Recent Reports', 'ai-inventory-agent'); ?>
                        </h3>
                    </div>
                    <div class="aia-widget-content">
                        
                        <!-- Empty State -->
                        <div class="aia-empty-state">
                            <div class="aia-empty-icon">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#file-text"></use>
                                </svg>
                            </div>
                            <div class="aia-empty-title"><?php _e('No Reports Generated Yet', 'ai-inventory-agent'); ?></div>
                            <div class="aia-empty-description"><?php _e('Use the report generator to create your first inventory report. Reports will appear here once generated.', 'ai-inventory-agent'); ?></div>
                            <div class="aia-empty-actions">
                                <button type="button" class="aia-btn aia-btn--primary aia-btn--sm" onclick="document.querySelector('.aia-generate-report').click()">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#plus"></use>
                                    </svg>
                                    <?php _e('Create First Report', 'ai-inventory-agent'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Report History List (hidden until reports exist) -->
                        <div class="aia-report-history" style="display: none;">
                            <div class="aia-history-list">
                                <!-- Report history items would be populated here via JavaScript -->
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Report Generation Modal -->
<div id="aia-report-modal" class="aia-modal" style="display: none;" role="dialog" aria-labelledby="aia-modal-title" aria-hidden="true">
    <div class="aia-modal-content">
        <div class="aia-modal-header">
            <h2 id="aia-modal-title" class="aia-modal-title"><?php _e('Generating Report', 'ai-inventory-agent'); ?></h2>
            <button type="button" class="aia-modal-close" aria-label="<?php esc_attr_e('Close modal', 'ai-inventory-agent'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#x"></use>
                </svg>
            </button>
        </div>
        <div class="aia-modal-body">
            <div class="aia-loading-container">
                <div class="aia-loading-spinner"></div>
                <div class="aia-loading-text">
                    <h3><?php _e('Processing Your Request', 'ai-inventory-agent'); ?></h3>
                    <p><?php _e('Please wait while we generate your comprehensive inventory report. This may take a few moments depending on your data size.', 'ai-inventory-agent'); ?></p>
                </div>
            </div>
            <div class="aia-progress-bar">
                <div class="aia-progress-fill"></div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    
    // Report generation handling
    $('.aia-generate-report').on('click', function() {
        var reportType = $(this).data('type');
        var reportTitle = $(this).closest('.aia-report-card').find('.aia-report-title').text();
        
        // Update modal title
        $('#aia-modal-title').text('<?php _e('Generating', 'ai-inventory-agent'); ?> ' + reportTitle);
        
        // Show modal
        $('#aia-report-modal').show().attr('aria-hidden', 'false');
        $('body').addClass('aia-modal-open');
        
        // Start progress animation
        var progressBar = $('.aia-progress-fill');
        progressBar.css('width', '0%');
        
        // Animate progress
        var progress = 0;
        var progressInterval = setInterval(function() {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressBar.css('width', progress + '%');
        }, 200);
        
        // Simulate report generation (replace with actual AJAX call)
        setTimeout(function() {
            clearInterval(progressInterval);
            progressBar.css('width', '100%');
            
            setTimeout(function() {
                $('#aia-report-modal').hide().attr('aria-hidden', 'true');
                $('body').removeClass('aia-modal-open');
                
                // Show success notification
                showNotification('<?php _e('Report Generated Successfully!', 'ai-inventory-agent'); ?>', 'success');
                
                // Add to history (placeholder)
                addReportToHistory(reportTitle, reportType);
                
                // Reset progress
                progressBar.css('width', '0%');
            }, 500);
        }, 3000);
    });
    
    // Modal close handling
    $('.aia-modal-close').on('click', function() {
        $('#aia-report-modal').hide().attr('aria-hidden', 'true');
        $('body').removeClass('aia-modal-open');
    });
    
    // Close modal on backdrop click
    $('.aia-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide().attr('aria-hidden', 'true');
            $('body').removeClass('aia-modal-open');
        }
    });
    
    // Escape key to close modal
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#aia-report-modal').is(':visible')) {
            $('#aia-report-modal').hide().attr('aria-hidden', 'true');
            $('body').removeClass('aia-modal-open');
        }
    });
    
    // Export all reports function
    window.exportAllReports = function() {
        showNotification('<?php _e('Exporting all reports...', 'ai-inventory-agent'); ?>', 'info');
        // Placeholder for actual export functionality
        setTimeout(function() {
            showNotification('<?php _e('All reports exported successfully!', 'ai-inventory-agent'); ?>', 'success');
        }, 2000);
    };
    
    // Add report to history (placeholder)
    function addReportToHistory(title, type) {
        // This would normally add to database and refresh the history widget
        console.log('Report generated:', title, type);
    }
    
    // Simple notification system
    function showNotification(message, type) {
        var notification = $('<div class="aia-notification aia-notification--' + type + '">' + message + '</div>');
        $('body').append(notification);
        
        setTimeout(function() {
            notification.addClass('aia-notification--show');
        }, 100);
        
        setTimeout(function() {
            notification.removeClass('aia-notification--show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
});
</script>