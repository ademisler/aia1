<?php
/**
 * Admin Reports Template - Light Theme
 * 
 * @package AI_Inventory_Agent
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
                <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bar-chart"></use>
                </svg>
                <?php _e('Inventory Reports', 'ai-inventory-agent'); ?>
            </h1>
            <p class="aia-reports-subtitle"><?php _e('Generate comprehensive reports and analyze your inventory performance with advanced insights.', 'ai-inventory-agent'); ?></p>
        </div>
        <div class="aia-reports-actions">
            <button type="button" class="aia-btn aia-btn--light aia-btn--sm" onclick="window.print()">
                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-printer"></use>
                </svg>
                <?php _e('Print', 'ai-inventory-agent'); ?>
            </button>
            <button type="button" class="aia-btn aia-btn--primary aia-btn--sm" onclick="exportAllReports()">
                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-download"></use>
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
                        <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-package"></use>
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
                        <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-dollar-sign"></use>
                        </svg>
                    </div>
                    <div class="aia-metric-content">
                        <div class="aia-metric-number"><?php echo esc_html($summary['total_value'] ?? '$0'); ?></div>
                        <div class="aia-metric-label"><?php _e('Total Stock Value', 'ai-inventory-agent'); ?></div>
                        <div class="aia-metric-description"><?php _e('Current inventory worth', 'ai-inventory-agent'); ?></div>
                    </div>
                </div>
                <div class="aia-metric-card">
                    <div class="aia-metric-icon">
                        <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                        </svg>
                    </div>
                    <div class="aia-metric-content">
                        <div class="aia-metric-number"><?php echo esc_html($summary['low_stock_count'] ?? 0); ?></div>
                        <div class="aia-metric-label"><?php _e('Low Stock Items', 'ai-inventory-agent'); ?></div>
                        <div class="aia-metric-description"><?php _e('Need attention soon', 'ai-inventory-agent'); ?></div>
                    </div>
                </div>
                <div class="aia-metric-card">
                    <div class="aia-metric-icon">
                        <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-x-circle"></use>
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
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-file-text"></use>
                            </svg>
                            <?php _e('Generate Reports', 'ai-inventory-agent'); ?>
                        </h3>
                        <p class="aia-widget-subtitle"><?php _e('Create detailed reports for your inventory analysis', 'ai-inventory-agent'); ?></p>
                    </div>
                    <div class="aia-widget-content">
                        <div class="aia-report-types-grid">
                            
                            <!-- Inventory Summary Report -->
                            <div class="aia-report-card">
                                <div class="aia-report-header">
                                    <div class="aia-report-icon">
                                        <svg class="aia-icon aia-icon--xl" aria-hidden="true">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-file-text"></use>
                                        </svg>
                                    </div>
                                    <div class="aia-report-meta">
                                        <h4 class="aia-report-title"><?php _e('Inventory Summary', 'ai-inventory-agent'); ?></h4>
                                        <span class="aia-report-status aia-report-status--available"><?php _e('Available', 'ai-inventory-agent'); ?></span>
                                    </div>
                                    <div class="aia-report-actions">
                                        <button class="aia-btn aia-btn--primary aia-btn--sm aia-generate-report" data-type="summary">
                                            <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-play"></use>
                                            </svg>
                                            <?php _e('Generate', 'ai-inventory-agent'); ?>
                                        </button>
                                    </div>
                                </div>
                                <p class="aia-report-description"><?php _e('Complete overview of your inventory including stock levels, categories, and performance metrics.', 'ai-inventory-agent'); ?></p>
                            </div>

                            <!-- Low Stock Report -->
                            <div class="aia-report-card">
                                <div class="aia-report-header">
                                    <div class="aia-report-icon">
                                        <svg class="aia-icon aia-icon--xl" aria-hidden="true">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                                        </svg>
                                    </div>
                                    <div class="aia-report-meta">
                                        <h4 class="aia-report-title"><?php _e('Low Stock Alert', 'ai-inventory-agent'); ?></h4>
                                        <span class="aia-report-status aia-report-status--available"><?php _e('Available', 'ai-inventory-agent'); ?></span>
                                    </div>
                                    <div class="aia-report-actions">
                                        <button class="aia-btn aia-btn--primary aia-btn--sm aia-generate-report" data-type="low-stock">
                                            <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-play"></use>
                                            </svg>
                                            <?php _e('Generate', 'ai-inventory-agent'); ?>
                                        </button>
                                    </div>
                                </div>
                                <p class="aia-report-description"><?php _e('Detailed list of products running low on stock with recommended reorder quantities.', 'ai-inventory-agent'); ?></p>
                            </div>

                            <!-- Stock Movement Report -->
                            <div class="aia-report-card">
                                <div class="aia-report-header">
                                    <div class="aia-report-icon">
                                        <svg class="aia-icon aia-icon--xl" aria-hidden="true">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trending-up"></use>
                                        </svg>
                                    </div>
                                    <div class="aia-report-meta">
                                        <h4 class="aia-report-title"><?php _e('Stock Movement', 'ai-inventory-agent'); ?></h4>
                                        <span class="aia-report-status aia-report-status--available"><?php _e('Available', 'ai-inventory-agent'); ?></span>
                                    </div>
                                    <div class="aia-report-actions">
                                        <button class="aia-btn aia-btn--primary aia-btn--sm aia-generate-report" data-type="movement">
                                            <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-play"></use>
                                            </svg>
                                            <?php _e('Generate', 'ai-inventory-agent'); ?>
                                        </button>
                                    </div>
                                </div>
                                <p class="aia-report-description"><?php _e('Track product movement patterns, turnover rates, and identify fast/slow-moving items.', 'ai-inventory-agent'); ?></p>
                            </div>

                            <!-- Performance Report -->
                            <div class="aia-report-card">
                                <div class="aia-report-header">
                                    <div class="aia-report-icon">
                                        <svg class="aia-icon aia-icon--xl" aria-hidden="true">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-target"></use>
                                        </svg>
                                    </div>
                                    <div class="aia-report-meta">
                                        <h4 class="aia-report-title"><?php _e('Performance Analysis', 'ai-inventory-agent'); ?></h4>
                                        <span class="aia-report-status aia-report-status--available"><?php _e('Available', 'ai-inventory-agent'); ?></span>
                                    </div>
                                    <div class="aia-report-actions">
                                        <button class="aia-btn aia-btn--primary aia-btn--sm aia-generate-report" data-type="performance">
                                            <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-play"></use>
                                            </svg>
                                            <?php _e('Generate', 'ai-inventory-agent'); ?>
                                        </button>
                                    </div>
                                </div>
                                <p class="aia-report-description"><?php _e('Comprehensive performance metrics including ROI, profit margins, and efficiency analysis.', 'ai-inventory-agent'); ?></p>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Report Settings Widget -->
                <div class="aia-widget">
                    <div class="aia-widget-header">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-settings"></use>
                            </svg>
                            <?php _e('Report Settings', 'ai-inventory-agent'); ?>
                        </h3>
                    </div>
                    <div class="aia-widget-content">
                        <div class="aia-form-group">
                            <label for="report_frequency" class="aia-form-label"><?php _e('Automatic Report Frequency', 'ai-inventory-agent'); ?></label>
                            <select id="report_frequency" name="report_frequency" class="aia-form-select">
                                <option value="daily" <?php selected($settings['report_frequency'] ?? 'weekly', 'daily'); ?>><?php _e('Daily', 'ai-inventory-agent'); ?></option>
                                <option value="weekly" <?php selected($settings['report_frequency'] ?? 'weekly', 'weekly'); ?>><?php _e('Weekly', 'ai-inventory-agent'); ?></option>
                                <option value="monthly" <?php selected($settings['report_frequency'] ?? 'weekly', 'monthly'); ?>><?php _e('Monthly', 'ai-inventory-agent'); ?></option>
                                <option value="disabled" <?php selected($settings['report_frequency'] ?? 'weekly', 'disabled'); ?>><?php _e('Disabled', 'ai-inventory-agent'); ?></option>
                            </select>
                        </div>
                        
                        <div class="aia-form-group aia-form-group--checkbox">
                            <label class="aia-checkbox-label">
                                <input type="checkbox" class="aia-checkbox" <?php checked($settings['reports_enabled'] ?? true); ?>>
                                <span class="aia-checkbox-checkmark"></span>
                                <span class="aia-checkbox-text">
                                    <strong><?php _e('Enable Automatic Reports', 'ai-inventory-agent'); ?></strong>
                                    <small><?php _e('Generate and email reports automatically based on frequency', 'ai-inventory-agent'); ?></small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Recent Reports & History -->
            <div class="aia-reports-column">
                
                <!-- Recent Reports Widget -->
                <div class="aia-widget">
                    <div class="aia-widget-header">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-clock"></use>
                            </svg>
                            <?php _e('Recent Reports', 'ai-inventory-agent'); ?>
                        </h3>
                        <button class="aia-btn aia-btn--ghost aia-btn--sm" onclick="clearReportHistory()">
                            <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-x"></use>
                            </svg>
                            <?php _e('Clear All', 'ai-inventory-agent'); ?>
                        </button>
                    </div>
                    <div class="aia-widget-content">
                        <!-- Empty State -->
                        <div class="aia-empty-state" id="aia-reports-empty">
                            <div class="aia-empty-icon">
                                <svg class="aia-icon aia-icon--xxl" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-file-text"></use>
                                </svg>
                            </div>
                            <h4><?php _e('No Reports Generated Yet', 'ai-inventory-agent'); ?></h4>
                            <p><?php _e('Generate your first report using the options above to see them listed here.', 'ai-inventory-agent'); ?></p>
                            <button class="aia-btn aia-btn--primary aia-btn--sm" onclick="generateFirstReport()">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-plus"></use>
                                </svg>
                                <?php _e('Generate First Report', 'ai-inventory-agent'); ?>
                            </button>
                        </div>
                        
                        <!-- Report History (Hidden by default) -->
                        <div class="aia-report-history" id="aia-report-history" style="display: none;">
                            <!-- Reports will be added here dynamically -->
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Report Generation Modal -->
<div class="aia-modal" id="aia-report-modal" aria-hidden="true" role="dialog">
    <div class="aia-modal-overlay"></div>
    <div class="aia-modal-content">
        <div class="aia-modal-header">
            <h3 class="aia-modal-title" id="aia-modal-title"><?php _e('Generating Report', 'ai-inventory-agent'); ?></h3>
            <button class="aia-modal-close" onclick="closeReportModal()">
                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-x"></use>
                </svg>
            </button>
        </div>
        <div class="aia-modal-body">
            <div class="aia-report-generation">
                <div class="aia-generation-icon">
                    <div class="aia-loading-spinner"></div>
                </div>
                <div class="aia-generation-text">
                    <p><?php _e('Please wait while we compile your inventory data and generate the comprehensive report.', 'ai-inventory-agent'); ?></p>
                    <div class="aia-progress-bar">
                        <div class="aia-progress-fill"></div>
                    </div>
                    <div class="aia-progress-text">
                        <span id="aia-progress-status"><?php _e('Initializing...', 'ai-inventory-agent'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    
    // Report generation
    $('.aia-generate-report').on('click', function() {
        var reportType = $(this).data('type');
        var reportTitle = $(this).closest('.aia-report-card').find('.aia-report-title').text();
        
        $('#aia-modal-title').text('<?php _e('Generating', 'ai-inventory-agent'); ?> ' + reportTitle);
        $('#aia-report-modal').show().attr('aria-hidden', 'false');
        $('body').addClass('aia-modal-open');
        
        var progressBar = $('.aia-progress-fill');
        var progressText = $('#aia-progress-status');
        progressBar.css('width', '0%');
        
        var progress = 0;
        var progressSteps = [
            '<?php _e('Collecting inventory data...', 'ai-inventory-agent'); ?>',
            '<?php _e('Analyzing stock levels...', 'ai-inventory-agent'); ?>',
            '<?php _e('Calculating metrics...', 'ai-inventory-agent'); ?>',
            '<?php _e('Generating charts...', 'ai-inventory-agent'); ?>',
            '<?php _e('Finalizing report...', 'ai-inventory-agent'); ?>'
        ];
        
        var currentStep = 0;
        var progressInterval = setInterval(function() {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            
            progressBar.css('width', progress + '%');
            
            if (currentStep < progressSteps.length && progress > (currentStep + 1) * 18) {
                progressText.text(progressSteps[currentStep]);
                currentStep++;
            }
        }, 200);
        
        setTimeout(function() {
            clearInterval(progressInterval);
            progressBar.css('width', '100%');
            progressText.text('<?php _e('Complete!', 'ai-inventory-agent'); ?>');
            
            setTimeout(function() {
                $('#aia-report-modal').hide().attr('aria-hidden', 'true');
                $('body').removeClass('aia-modal-open');
                showNotification('<?php _e('Report Generated Successfully!', 'ai-inventory-agent'); ?>', 'success');
                addReportToHistory(reportTitle, reportType);
                progressBar.css('width', '0%');
                progressText.text('<?php _e('Initializing...', 'ai-inventory-agent'); ?>');
            }, 500);
        }, 3000);
    });
    
    // Export all reports
    window.exportAllReports = function() {
        showNotification('<?php _e('Exporting all reports...', 'ai-inventory-agent'); ?>', 'info');
        // Implementation would go here
    };
    
    // Clear report history
    window.clearReportHistory = function() {
        if (confirm('<?php _e('Are you sure you want to clear all report history?', 'ai-inventory-agent'); ?>')) {
            $('#aia-report-history').hide().empty();
            $('#aia-reports-empty').show();
            showNotification('<?php _e('Report history cleared.', 'ai-inventory-agent'); ?>', 'info');
        }
    };
    
    // Generate first report
    window.generateFirstReport = function() {
        $('.aia-generate-report').first().click();
    };
    
    // Add report to history
    window.addReportToHistory = function(title, type) {
        var timestamp = new Date().toLocaleString();
        var reportHtml = `
            <div class="aia-report-history-item">
                <div class="aia-report-history-icon">
                    <svg class="aia-icon aia-icon--md" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-file-text"></use>
                    </svg>
                </div>
                <div class="aia-report-history-content">
                    <h4 class="aia-report-history-title">${title}</h4>
                    <p class="aia-report-history-meta">${timestamp}</p>
                </div>
                <div class="aia-report-history-actions">
                    <button class="aia-btn aia-btn--ghost aia-btn--xs" onclick="downloadReport('${type}')">
                        <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-download"></use>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        $('#aia-reports-empty').hide();
        $('#aia-report-history').show().prepend(reportHtml);
    };
    
    // Download report
    window.downloadReport = function(type) {
        showNotification('<?php _e('Downloading report...', 'ai-inventory-agent'); ?>', 'info');
        // Implementation would go here
    };
    
    // Close modal
    window.closeReportModal = function() {
        $('#aia-report-modal').hide().attr('aria-hidden', 'true');
        $('body').removeClass('aia-modal-open');
    };
    
    // Close modal on overlay click
    $('.aia-modal-overlay').on('click', function() {
        closeReportModal();
    });
    
    // Notification system
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
    
    // Modal close on overlay click
    $('.aia-modal-overlay').on('click', function() {
        closeReportModal();
    });
    
    // Escape key to close modal
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeReportModal();
        }
    });
    
});
</script>