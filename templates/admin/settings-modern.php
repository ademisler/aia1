<?php
/**
 * Modern Settings Page Template
 * 
 * @package AI_Inventory_Agent
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('aia_settings', []);
?>

<div class="aia-dashboard">
    <div class="aia-dashboard-container">
        <!-- Settings Header -->
        <div class="aia-dashboard-header">
            <div class="aia-dashboard-title">
                <div class="aia-stat-icon">
                    <span>⚙️</span>
                </div>
                <div>
                    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                    <p class="aia-dashboard-subtitle"><?php esc_html_e('Configure your AI Inventory Agent settings', 'ai-inventory-agent'); ?></p>
                </div>
            </div>
            <div class="aia-dashboard-actions">
                <button type="button" class="aia-btn aia-btn-ghost aia-btn-sm" id="aia-reset-settings">
                    <span>🔄</span> <?php esc_html_e('Reset to Defaults', 'ai-inventory-agent'); ?>
                </button>
                <button type="submit" form="aia-settings-form" class="aia-btn aia-btn-primary">
                    <span>💾</span> <?php esc_html_e('Save Settings', 'ai-inventory-agent'); ?>
                </button>
            </div>
        </div>

        <!-- Settings Tabs -->
        <div class="aia-tabs">
            <div class="aia-tab-list" role="tablist">
                <button class="aia-tab active" data-tab="general" role="tab">
                    <span>🏠</span> <?php esc_html_e('General', 'ai-inventory-agent'); ?>
                </button>
                <button class="aia-tab" data-tab="ai" role="tab">
                    <span>🤖</span> <?php esc_html_e('AI Configuration', 'ai-inventory-agent'); ?>
                </button>
                <button class="aia-tab" data-tab="inventory" role="tab">
                    <span>📦</span> <?php esc_html_e('Inventory', 'ai-inventory-agent'); ?>
                </button>
                <button class="aia-tab" data-tab="notifications" role="tab">
                    <span>🔔</span> <?php esc_html_e('Notifications', 'ai-inventory-agent'); ?>
                </button>
                <button class="aia-tab" data-tab="advanced" role="tab">
                    <span>🔧</span> <?php esc_html_e('Advanced', 'ai-inventory-agent'); ?>
                </button>
            </div>

            <form id="aia-settings-form" method="post" action="options.php">
                <?php settings_fields('aia_settings_group'); ?>
                
                <!-- General Settings Tab -->
                <div id="general" class="aia-tab-content active">
                    <div class="aia-card">
                        <div class="aia-card-header">
                            <h3><?php esc_html_e('General Settings', 'ai-inventory-agent'); ?></h3>
                        </div>
                        <div class="aia-card-body">
                            <div class="aia-form-grid">
                                <div class="aia-input-group">
                                    <label class="aia-input-label" for="company_name">
                                        <?php esc_html_e('Company Name', 'ai-inventory-agent'); ?>
                                    </label>
                                    <input 
                                        type="text" 
                                        id="company_name" 
                                        name="aia_settings[company_name]" 
                                        value="<?php echo esc_attr($settings['company_name'] ?? get_bloginfo('name')); ?>" 
                                        class="aia-input"
                                    />
                                </div>

                                <div class="aia-input-group">
                                    <label class="aia-input-label" for="notification_email">
                                        <?php esc_html_e('Notification Email', 'ai-inventory-agent'); ?>
                                    </label>
                                    <input 
                                        type="email" 
                                        id="notification_email" 
                                        name="aia_settings[notification_email]" 
                                        value="<?php echo esc_attr($settings['notification_email'] ?? get_option('admin_email')); ?>" 
                                        class="aia-input"
                                    />
                                </div>

                                <div class="aia-input-group">
                                    <label class="aia-switch-group">
                                        <span class="aia-switch-label"><?php esc_html_e('Enable Debug Mode', 'ai-inventory-agent'); ?></span>
                                        <label class="aia-switch">
                                            <input 
                                                type="checkbox" 
                                                name="aia_settings[debug_mode]" 
                                                value="1" 
                                                <?php checked($settings['debug_mode'] ?? false, true); ?>
                                            />
                                            <span class="aia-switch-slider"></span>
                                        </label>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Configuration Tab -->
                <div id="ai" class="aia-tab-content">
                    <div class="aia-card">
                        <div class="aia-card-header">
                            <h3><?php esc_html_e('AI Provider Settings', 'ai-inventory-agent'); ?></h3>
                        </div>
                        <div class="aia-card-body">
                            <div class="aia-form-grid">
                                <div class="aia-input-group">
                                    <label class="aia-input-label"><?php esc_html_e('AI Provider', 'ai-inventory-agent'); ?></label>
                                    <div class="aia-radio-group">
                                        <label class="aia-radio">
                                            <input 
                                                type="radio" 
                                                name="aia_settings[ai_provider]" 
                                                value="openai" 
                                                <?php checked($settings['ai_provider'] ?? 'openai', 'openai'); ?>
                                            />
                                            <span class="aia-radio-mark"></span>
                                            <span>OpenAI (GPT-4)</span>
                                        </label>
                                        <label class="aia-radio">
                                            <input 
                                                type="radio" 
                                                name="aia_settings[ai_provider]" 
                                                value="gemini" 
                                                <?php checked($settings['ai_provider'] ?? 'openai', 'gemini'); ?>
                                            />
                                            <span class="aia-radio-mark"></span>
                                            <span>Google Gemini</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="aia-input-group">
                                    <label class="aia-input-label" for="api_key">
                                        <?php esc_html_e('API Key', 'ai-inventory-agent'); ?>
                                    </label>
                                    <div class="aia-input-with-action">
                                        <input 
                                            type="password" 
                                            id="api_key" 
                                            name="aia_settings[api_key]" 
                                            value="<?php echo esc_attr($settings['api_key'] ?? ''); ?>" 
                                            class="aia-input"
                                            placeholder="sk-..."
                                        />
                                        <button type="button" class="aia-btn aia-btn-ghost aia-btn-sm" id="test-api">
                                            <?php esc_html_e('Test Connection', 'ai-inventory-agent'); ?>
                                        </button>
                                    </div>
                                </div>

                                <div class="aia-input-group">
                                    <label class="aia-input-label" for="system_prompt">
                                        <?php esc_html_e('System Prompt', 'ai-inventory-agent'); ?>
                                    </label>
                                    <textarea 
                                        id="system_prompt" 
                                        name="aia_settings[system_prompt]" 
                                        rows="6" 
                                        class="aia-input"
                                        placeholder="<?php esc_attr_e('You are an AI inventory management assistant...', 'ai-inventory-agent'); ?>"
                                    ><?php echo esc_textarea($settings['system_prompt'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory Settings Tab -->
                <div id="inventory" class="aia-tab-content">
                    <div class="aia-card">
                        <div class="aia-card-header">
                            <h3><?php esc_html_e('Stock Thresholds', 'ai-inventory-agent'); ?></h3>
                        </div>
                        <div class="aia-card-body">
                            <div class="aia-form-grid">
                                <div class="aia-input-group">
                                    <label class="aia-input-label" for="low_stock_threshold">
                                        <?php esc_html_e('Low Stock Threshold', 'ai-inventory-agent'); ?>
                                    </label>
                                    <div class="aia-range">
                                        <input 
                                            type="range" 
                                            id="low_stock_threshold" 
                                            name="aia_settings[low_stock_threshold]" 
                                            value="<?php echo esc_attr($settings['low_stock_threshold'] ?? 5); ?>" 
                                            min="1" 
                                            max="50" 
                                            class="aia-range-input"
                                        />
                                        <div class="aia-range-value"><?php echo esc_html($settings['low_stock_threshold'] ?? 5); ?></div>
                                    </div>
                                </div>

                                <div class="aia-input-group">
                                    <label class="aia-input-label" for="critical_stock_threshold">
                                        <?php esc_html_e('Critical Stock Threshold', 'ai-inventory-agent'); ?>
                                    </label>
                                    <div class="aia-range">
                                        <input 
                                            type="range" 
                                            id="critical_stock_threshold" 
                                            name="aia_settings[critical_stock_threshold]" 
                                            value="<?php echo esc_attr($settings['critical_stock_threshold'] ?? 1); ?>" 
                                            min="0" 
                                            max="20" 
                                            class="aia-range-input"
                                        />
                                        <div class="aia-range-value"><?php echo esc_html($settings['critical_stock_threshold'] ?? 1); ?></div>
                                    </div>
                                </div>

                                <div class="aia-input-group">
                                    <label class="aia-input-label" for="overstock_threshold">
                                        <?php esc_html_e('Overstock Threshold (%)', 'ai-inventory-agent'); ?>
                                    </label>
                                    <div class="aia-range">
                                        <input 
                                            type="range" 
                                            id="overstock_threshold" 
                                            name="aia_settings[overstock_threshold]" 
                                            value="<?php echo esc_attr($settings['overstock_threshold'] ?? 150); ?>" 
                                            min="100" 
                                            max="300" 
                                            step="10"
                                            class="aia-range-input"
                                        />
                                        <div class="aia-range-value"><?php echo esc_html($settings['overstock_threshold'] ?? 150); ?>%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="aia-card">
                        <div class="aia-card-header">
                            <h3><?php esc_html_e('Forecasting Settings', 'ai-inventory-agent'); ?></h3>
                        </div>
                        <div class="aia-card-body">
                            <div class="aia-form-grid">
                                <div class="aia-input-group">
                                    <label class="aia-input-label" for="forecast_days">
                                        <?php esc_html_e('Forecast Period (Days)', 'ai-inventory-agent'); ?>
                                    </label>
                                    <select id="forecast_days" name="aia_settings[forecast_days]" class="aia-select-input">
                                        <option value="7" <?php selected($settings['forecast_days'] ?? 30, 7); ?>>7 <?php esc_html_e('Days', 'ai-inventory-agent'); ?></option>
                                        <option value="14" <?php selected($settings['forecast_days'] ?? 30, 14); ?>>14 <?php esc_html_e('Days', 'ai-inventory-agent'); ?></option>
                                        <option value="30" <?php selected($settings['forecast_days'] ?? 30, 30); ?>>30 <?php esc_html_e('Days', 'ai-inventory-agent'); ?></option>
                                        <option value="60" <?php selected($settings['forecast_days'] ?? 30, 60); ?>>60 <?php esc_html_e('Days', 'ai-inventory-agent'); ?></option>
                                        <option value="90" <?php selected($settings['forecast_days'] ?? 30, 90); ?>>90 <?php esc_html_e('Days', 'ai-inventory-agent'); ?></option>
                                    </select>
                                </div>

                                <div class="aia-input-group">
                                    <label class="aia-switch-group">
                                        <span class="aia-switch-label"><?php esc_html_e('Enable Auto-Reordering', 'ai-inventory-agent'); ?></span>
                                        <label class="aia-switch">
                                            <input 
                                                type="checkbox" 
                                                name="aia_settings[auto_reorder]" 
                                                value="1" 
                                                <?php checked($settings['auto_reorder'] ?? false, true); ?>
                                            />
                                            <span class="aia-switch-slider"></span>
                                        </label>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div id="notifications" class="aia-tab-content">
                    <div class="aia-card">
                        <div class="aia-card-header">
                            <h3><?php esc_html_e('Email Notifications', 'ai-inventory-agent'); ?></h3>
                        </div>
                        <div class="aia-card-body">
                            <div class="aia-form-grid">
                                <div class="aia-checkbox-group">
                                    <label class="aia-checkbox">
                                        <input 
                                            type="checkbox" 
                                            name="aia_settings[notify_low_stock]" 
                                            value="1" 
                                            <?php checked($settings['notify_low_stock'] ?? true, true); ?>
                                        />
                                        <span class="aia-checkbox-mark"></span>
                                        <span><?php esc_html_e('Low Stock Alerts', 'ai-inventory-agent'); ?></span>
                                    </label>
                                    
                                    <label class="aia-checkbox">
                                        <input 
                                            type="checkbox" 
                                            name="aia_settings[notify_out_of_stock]" 
                                            value="1" 
                                            <?php checked($settings['notify_out_of_stock'] ?? true, true); ?>
                                        />
                                        <span class="aia-checkbox-mark"></span>
                                        <span><?php esc_html_e('Out of Stock Alerts', 'ai-inventory-agent'); ?></span>
                                    </label>
                                    
                                    <label class="aia-checkbox">
                                        <input 
                                            type="checkbox" 
                                            name="aia_settings[notify_overstock]" 
                                            value="1" 
                                            <?php checked($settings['notify_overstock'] ?? false, true); ?>
                                        />
                                        <span class="aia-checkbox-mark"></span>
                                        <span><?php esc_html_e('Overstock Alerts', 'ai-inventory-agent'); ?></span>
                                    </label>
                                    
                                    <label class="aia-checkbox">
                                        <input 
                                            type="checkbox" 
                                            name="aia_settings[notify_supplier_issues]" 
                                            value="1" 
                                            <?php checked($settings['notify_supplier_issues'] ?? true, true); ?>
                                        />
                                        <span class="aia-checkbox-mark"></span>
                                        <span><?php esc_html_e('Supplier Risk Alerts', 'ai-inventory-agent'); ?></span>
                                    </label>
                                </div>

                                <div class="aia-input-group">
                                    <label class="aia-input-label" for="notification_frequency">
                                        <?php esc_html_e('Notification Frequency', 'ai-inventory-agent'); ?>
                                    </label>
                                    <select id="notification_frequency" name="aia_settings[notification_frequency]" class="aia-select-input">
                                        <option value="realtime" <?php selected($settings['notification_frequency'] ?? 'daily', 'realtime'); ?>><?php esc_html_e('Real-time', 'ai-inventory-agent'); ?></option>
                                        <option value="hourly" <?php selected($settings['notification_frequency'] ?? 'daily', 'hourly'); ?>><?php esc_html_e('Hourly', 'ai-inventory-agent'); ?></option>
                                        <option value="daily" <?php selected($settings['notification_frequency'] ?? 'daily', 'daily'); ?>><?php esc_html_e('Daily Digest', 'ai-inventory-agent'); ?></option>
                                        <option value="weekly" <?php selected($settings['notification_frequency'] ?? 'daily', 'weekly'); ?>><?php esc_html_e('Weekly Summary', 'ai-inventory-agent'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Tab -->
                <div id="advanced" class="aia-tab-content">
                    <div class="aia-card">
                        <div class="aia-card-header">
                            <h3><?php esc_html_e('Performance Settings', 'ai-inventory-agent'); ?></h3>
                        </div>
                        <div class="aia-card-body">
                            <div class="aia-form-grid">
                                <div class="aia-input-group">
                                    <label class="aia-input-label" for="cache_duration">
                                        <?php esc_html_e('Cache Duration (Minutes)', 'ai-inventory-agent'); ?>
                                    </label>
                                    <input 
                                        type="number" 
                                        id="cache_duration" 
                                        name="aia_settings[cache_duration]" 
                                        value="<?php echo esc_attr($settings['cache_duration'] ?? 60); ?>" 
                                        min="0" 
                                        max="1440"
                                        class="aia-input"
                                    />
                                </div>

                                <div class="aia-input-group">
                                    <label class="aia-input-label" for="batch_size">
                                        <?php esc_html_e('Batch Processing Size', 'ai-inventory-agent'); ?>
                                    </label>
                                    <input 
                                        type="number" 
                                        id="batch_size" 
                                        name="aia_settings[batch_size]" 
                                        value="<?php echo esc_attr($settings['batch_size'] ?? 100); ?>" 
                                        min="10" 
                                        max="1000"
                                        step="10"
                                        class="aia-input"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="aia-card aia-card-glass">
                        <div class="aia-card-header">
                            <h3><?php esc_html_e('Danger Zone', 'ai-inventory-agent'); ?></h3>
                        </div>
                        <div class="aia-card-body">
                            <div class="aia-alert aia-alert-warning">
                                <span>⚠️</span>
                                <div>
                                    <strong><?php esc_html_e('Warning:', 'ai-inventory-agent'); ?></strong>
                                    <?php esc_html_e('These actions are irreversible. Please proceed with caution.', 'ai-inventory-agent'); ?>
                                </div>
                            </div>
                            
                            <div class="aia-button-group">
                                <button type="button" class="aia-btn aia-btn-ghost" id="clear-cache">
                                    <?php esc_html_e('Clear All Cache', 'ai-inventory-agent'); ?>
                                </button>
                                <button type="button" class="aia-btn aia-btn-ghost" id="reset-database">
                                    <?php esc_html_e('Reset Database Tables', 'ai-inventory-agent'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize tabs
    AIA.UI.Tabs.init('.aia-tabs');
    
    // Range slider value updates
    $('.aia-range-input').on('input', function() {
        $(this).siblings('.aia-range-value').text($(this).val() + ($(this).attr('id').includes('overstock') ? '%' : ''));
    });
    
    // Test API connection
    $('#test-api').on('click', function() {
        const $button = $(this);
        const originalText = $button.text();
        
        $button.text('Testing...').prop('disabled', true);
        
        $.ajax({
            url: aia_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'aia_test_api_connection',
                nonce: aia_admin.nonce,
                provider: $('input[name="aia_settings[ai_provider]"]:checked').val(),
                api_key: $('#api_key').val()
            },
            success: function(response) {
                if (response.success) {
                    AIA.UI.Toast.show('API connection successful!', 'success');
                } else {
                    AIA.UI.Toast.show('API connection failed: ' + response.data, 'error');
                }
            },
            error: function() {
                AIA.UI.Toast.show('An error occurred while testing the connection.', 'error');
            },
            complete: function() {
                $button.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Form submission
    $('#aia-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');
        const originalText = $submitButton.text();
        
        $submitButton.html('<span class="aia-spinner aia-spinner-sm"></span> Saving...').prop('disabled', true);
        
        $.ajax({
            url: aia_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'aia_save_settings',
                nonce: aia_admin.nonce,
                settings: $form.serialize()
            },
            success: function(response) {
                if (response.success) {
                    AIA.UI.Toast.show('Settings saved successfully!', 'success');
                } else {
                    AIA.UI.Toast.show('Failed to save settings: ' + response.data, 'error');
                }
            },
            error: function() {
                AIA.UI.Toast.show('An error occurred while saving settings.', 'error');
            },
            complete: function() {
                $submitButton.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>