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
?>

<div class="wrap aia-reports-page">
    <h1><?php _e('Inventory Reports', 'ai-inventory-agent'); ?></h1>
    
    <div class="aia-reports-container">
        
        <!-- Quick Stats -->
        <div class="aia-section">
            <h2><?php _e('Quick Statistics', 'ai-inventory-agent'); ?></h2>
            <div class="aia-stats-grid">
                <div class="aia-stat-card">
                    <div class="aia-stat-number"><?php echo esc_html($summary['total_products'] ?? 0); ?></div>
                    <div class="aia-stat-label"><?php _e('Total Products', 'ai-inventory-agent'); ?></div>
                </div>
                <div class="aia-stat-card">
                    <div class="aia-stat-number"><?php echo wc_price($summary['total_stock_value'] ?? 0); ?></div>
                    <div class="aia-stat-label"><?php _e('Total Stock Value', 'ai-inventory-agent'); ?></div>
                </div>
                <div class="aia-stat-card">
                    <div class="aia-stat-number"><?php echo esc_html($summary['low_stock_count'] ?? 0); ?></div>
                    <div class="aia-stat-label"><?php _e('Low Stock Items', 'ai-inventory-agent'); ?></div>
                </div>
                <div class="aia-stat-card">
                    <div class="aia-stat-number"><?php echo esc_html($summary['out_of_stock_count'] ?? 0); ?></div>
                    <div class="aia-stat-label"><?php _e('Out of Stock', 'ai-inventory-agent'); ?></div>
                </div>
            </div>
        </div>

        <!-- Report Generation -->
        <div class="aia-section">
            <h2><?php _e('Generate Reports', 'ai-inventory-agent'); ?></h2>
            <div class="aia-report-generator">
                
                <div class="aia-report-type-grid">
                    
                    <!-- Inventory Summary Report -->
                    <div class="aia-report-card">
                        <div class="aia-report-icon">📊</div>
                        <h3><?php _e('Inventory Summary', 'ai-inventory-agent'); ?></h3>
                        <p><?php _e('Complete overview of your current inventory status, stock levels, and values.', 'ai-inventory-agent'); ?></p>
                        <div class="aia-report-actions">
                            <button type="button" class="button button-primary aia-generate-report" data-type="inventory_summary">
                                <?php _e('Generate Report', 'ai-inventory-agent'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Low Stock Report -->
                    <div class="aia-report-card">
                        <div class="aia-report-icon">⚠️</div>
                        <h3><?php _e('Low Stock Report', 'ai-inventory-agent'); ?></h3>
                        <p><?php _e('Detailed report of all products with low or critical stock levels.', 'ai-inventory-agent'); ?></p>
                        <div class="aia-report-actions">
                            <button type="button" class="button button-primary aia-generate-report" data-type="low_stock">
                                <?php _e('Generate Report', 'ai-inventory-agent'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Stock Movement Report -->
                    <div class="aia-report-card">
                        <div class="aia-report-icon">📈</div>
                        <h3><?php _e('Stock Movement', 'ai-inventory-agent'); ?></h3>
                        <p><?php _e('Track stock changes, sales velocity, and inventory turnover rates.', 'ai-inventory-agent'); ?></p>
                        <div class="aia-report-actions">
                            <button type="button" class="button button-primary aia-generate-report" data-type="stock_movement">
                                <?php _e('Generate Report', 'ai-inventory-agent'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Performance Report -->
                    <div class="aia-report-card">
                        <div class="aia-report-icon">🎯</div>
                        <h3><?php _e('Performance Report', 'ai-inventory-agent'); ?></h3>
                        <p><?php _e('Analyze top performing products, slow movers, and profitability metrics.', 'ai-inventory-agent'); ?></p>
                        <div class="aia-report-actions">
                            <button type="button" class="button button-primary aia-generate-report" data-type="performance">
                                <?php _e('Generate Report', 'ai-inventory-agent'); ?>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Report Settings -->
        <div class="aia-section">
            <h2><?php _e('Report Settings', 'ai-inventory-agent'); ?></h2>
            <form method="post" action="options.php">
                <?php 
                settings_fields('aia_settings');
                $settings = get_option('aia_settings', []);
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="report_frequency"><?php _e('Automatic Report Frequency', 'ai-inventory-agent'); ?></label>
                        </th>
                        <td>
                            <select id="report_frequency" name="aia_settings[report_frequency]">
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
                            <p class="description"><?php _e('How often to automatically generate and email reports.', 'ai-inventory-agent'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php _e('Enable Reports', 'ai-inventory-agent'); ?>
                        </th>
                        <td>
                            <label for="reports_enabled">
                                <input type="checkbox" 
                                       id="reports_enabled" 
                                       name="aia_settings[reports_enabled]" 
                                       value="1" 
                                       <?php checked($settings['reports_enabled'] ?? true); ?> />
                                <?php _e('Enable automatic report generation and email delivery', 'ai-inventory-agent'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>

        <!-- Recent Reports -->
        <div class="aia-section">
            <h2><?php _e('Recent Reports', 'ai-inventory-agent'); ?></h2>
            <div class="aia-recent-reports">
                <p><?php _e('No reports generated yet. Use the report generator above to create your first report.', 'ai-inventory-agent'); ?></p>
                
                <!-- This would be populated with actual report history -->
                <div class="aia-report-history" style="display: none;">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Report Type', 'ai-inventory-agent'); ?></th>
                                <th><?php _e('Generated', 'ai-inventory-agent'); ?></th>
                                <th><?php _e('Status', 'ai-inventory-agent'); ?></th>
                                <th><?php _e('Actions', 'ai-inventory-agent'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Report history items would go here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Generation Modal -->
<div id="aia-report-modal" class="aia-modal" style="display: none;">
    <div class="aia-modal-content">
        <div class="aia-modal-header">
            <h2><?php _e('Generating Report...', 'ai-inventory-agent'); ?></h2>
            <span class="aia-modal-close">&times;</span>
        </div>
        <div class="aia-modal-body">
            <div class="aia-loading-spinner"></div>
            <p><?php _e('Please wait while we generate your report. This may take a few moments.', 'ai-inventory-agent'); ?></p>
        </div>
    </div>
</div>

<style>
.aia-reports-page .aia-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.aia-reports-page .aia-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.aia-reports-page .aia-stat-card {
    text-align: center;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.aia-reports-page .aia-stat-number {
    font-size: 2em;
    font-weight: bold;
    color: #2271b1;
    margin-bottom: 8px;
}

.aia-reports-page .aia-stat-label {
    color: #666;
    font-size: 14px;
}

.aia-reports-page .aia-report-type-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.aia-reports-page .aia-report-card {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    background: #fafafa;
}

.aia-reports-page .aia-report-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.aia-reports-page .aia-report-card h3 {
    margin-bottom: 10px;
    color: #333;
}

.aia-reports-page .aia-report-card p {
    color: #666;
    margin-bottom: 20px;
    line-height: 1.5;
}

.aia-reports-page .aia-report-actions {
    margin-top: 15px;
}

.aia-modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.aia-modal-content {
    background-color: #fff;
    margin: 15% auto;
    padding: 0;
    border-radius: 4px;
    width: 500px;
    max-width: 90%;
}

.aia-modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.aia-modal-header h2 {
    margin: 0;
}

.aia-modal-close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.aia-modal-body {
    padding: 20px;
    text-align: center;
}

.aia-loading-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #2271b1;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin: 20px auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Report generation handling
    $('.aia-generate-report').on('click', function() {
        var reportType = $(this).data('type');
        $('#aia-report-modal').show();
        
        // Simulate report generation (replace with actual AJAX call)
        setTimeout(function() {
            $('#aia-report-modal').hide();
            alert('Report generated successfully! (This is a demo - implement actual report generation)');
        }, 3000);
    });
    
    // Modal close handling
    $('.aia-modal-close, .aia-modal').on('click', function(e) {
        if (e.target === this) {
            $('#aia-report-modal').hide();
        }
    });
});
</script>