<?php
/**
 * Settings Page Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap aia-settings-page">
    <div class="aia-header">
        <div class="aia-logo">
            <h1><?php _e('AI Inventory Agent Settings', 'ai-inventory-agent'); ?></h1>
        </div>
    </div>

    <form method="post" action="options.php" class="aia-settings-form">
        <?php
        settings_fields('aia_settings_group');
        do_settings_sections('ai-inventory-agent');
        ?>

        <div class="aia-settings-sections">
            <!-- General Settings -->
            <div class="aia-settings-section">
                <h3><?php _e('AI Provider Configuration', 'ai-inventory-agent'); ?></h3>
                
                <div class="aia-form-row">
                    <label class="aia-form-label" for="ai_provider">
                        <?php _e('AI Provider', 'ai-inventory-agent'); ?>
                    </label>
                    <div class="aia-form-field">
                        <?php
                        $settings = $this->plugin->get_setting();
                        $current_provider = $settings['ai_provider'] ?? 'openai';
                        ?>
                        <select name="aia_settings[ai_provider]" id="ai_provider">
                            <option value="openai" <?php selected($current_provider, 'openai'); ?>>
                                <?php _e('OpenAI (GPT)', 'ai-inventory-agent'); ?>
                            </option>
                            <option value="gemini" <?php selected($current_provider, 'gemini'); ?>>
                                <?php _e('Google Gemini', 'ai-inventory-agent'); ?>
                            </option>
                        </select>
                        <p class="aia-form-description">
                            <?php _e('Choose your AI provider for chat and analysis features.', 'ai-inventory-agent'); ?>
                        </p>
                    </div>
                </div>

                <div class="aia-form-row">
                    <label class="aia-form-label" for="api_key">
                        <?php _e('API Key', 'ai-inventory-agent'); ?>
                    </label>
                    <div class="aia-form-field">
                        <input type="password" 
                               name="aia_settings[api_key]" 
                               id="api_key" 
                               value="<?php echo esc_attr($settings['api_key'] ?? ''); ?>" 
                               class="regular-text" />
                        <button type="button" id="test_api_connection" class="button button-secondary" style="margin-left: 10px;">
                            <?php _e('Test Connection', 'ai-inventory-agent'); ?>
                        </button>
                        <p class="aia-form-description">
                            <?php _e('Enter your AI provider API key. This is required for AI features to work.', 'ai-inventory-agent'); ?>
                        </p>
                        <div id="api_test_result"></div>
                    </div>
                </div>

                <div class="aia-form-row">
                    <label class="aia-form-label" for="system_prompt">
                        <?php _e('System Prompt', 'ai-inventory-agent'); ?>
                    </label>
                    <div class="aia-form-field">
                        <textarea name="aia_settings[system_prompt]" 
                                  id="system_prompt" 
                                  rows="4" 
                                  class="large-text"><?php echo esc_textarea($settings['system_prompt'] ?? 'You are an AI inventory management assistant. Help users manage their WooCommerce store inventory efficiently.'); ?></textarea>
                        <p class="aia-form-description">
                            <?php _e('This prompt defines how the AI assistant behaves. You can customize it to match your store\'s needs.', 'ai-inventory-agent'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Inventory Settings -->
            <div class="aia-settings-section">
                <h3><?php _e('Inventory Thresholds', 'ai-inventory-agent'); ?></h3>
                
                <div class="aia-form-row">
                    <label class="aia-form-label" for="low_stock_threshold">
                        <?php _e('Low Stock Threshold', 'ai-inventory-agent'); ?>
                    </label>
                    <div class="aia-form-field">
                        <input type="number" 
                               name="aia_settings[low_stock_threshold]" 
                               id="low_stock_threshold" 
                               value="<?php echo esc_attr($settings['low_stock_threshold'] ?? 5); ?>" 
                               min="0" 
                               class="small-text" />
                        <p class="aia-form-description">
                            <?php _e('Products with stock at or below this level will be flagged as low stock.', 'ai-inventory-agent'); ?>
                        </p>
                    </div>
                </div>

                <div class="aia-form-row">
                    <label class="aia-form-label" for="critical_stock_threshold">
                        <?php _e('Critical Stock Threshold', 'ai-inventory-agent'); ?>
                    </label>
                    <div class="aia-form-field">
                        <input type="number" 
                               name="aia_settings[critical_stock_threshold]" 
                               id="critical_stock_threshold" 
                               value="<?php echo esc_attr($settings['critical_stock_threshold'] ?? 1); ?>" 
                               min="0" 
                               class="small-text" />
                        <p class="aia-form-description">
                            <?php _e('Products with stock at or below this level will be flagged as out of stock.', 'ai-inventory-agent'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="aia-settings-section">
                <h3><?php _e('Notifications', 'ai-inventory-agent'); ?></h3>
                
                <div class="aia-form-row">
                    <label class="aia-form-label" for="notification_email">
                        <?php _e('Notification Email', 'ai-inventory-agent'); ?>
                    </label>
                    <div class="aia-form-field">
                        <input type="email" 
                               name="aia_settings[notification_email]" 
                               id="notification_email" 
                               value="<?php echo esc_attr($settings['notification_email'] ?? get_option('admin_email')); ?>" 
                               class="regular-text" />
                        <p class="aia-form-description">
                            <?php _e('Email address where notifications will be sent.', 'ai-inventory-agent'); ?>
                        </p>
                    </div>
                </div>

                <div class="aia-form-row">
                    <label class="aia-form-label">
                        <?php _e('Email Notifications', 'ai-inventory-agent'); ?>
                    </label>
                    <div class="aia-form-field">
                        <label>
                            <input type="checkbox" 
                                   name="aia_settings[email_notifications]" 
                                   value="1" 
                                   <?php checked($settings['email_notifications'] ?? true, true); ?> />
                            <?php _e('Send email notifications for stock alerts', 'ai-inventory-agent'); ?>
                        </label>
                    </div>
                </div>

                <div class="aia-form-row">
                    <label class="aia-form-label">
                        <?php _e('Dashboard Notifications', 'ai-inventory-agent'); ?>
                    </label>
                    <div class="aia-form-field">
                        <label>
                            <input type="checkbox" 
                                   name="aia_settings[dashboard_notifications]" 
                                   value="1" 
                                   <?php checked($settings['dashboard_notifications'] ?? true, true); ?> />
                            <?php _e('Show notifications in WordPress dashboard', 'ai-inventory-agent'); ?>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Module Settings -->
            <div class="aia-settings-section">
                <h3><?php _e('Module Settings', 'ai-inventory-agent'); ?></h3>
                
                <div class="aia-form-row">
                    <label class="aia-form-label">
                        <?php _e('Active Modules', 'ai-inventory-agent'); ?>
                    </label>
                    <div class="aia-form-field">
                        <label>
                            <input type="checkbox" 
                                   name="aia_settings[chat_enabled]" 
                                   value="1" 
                                   <?php checked($settings['chat_enabled'] ?? true, true); ?> />
                            <?php _e('AI Chat Assistant', 'ai-inventory-agent'); ?>
                        </label>
                        <br><br>
                        <label>
                            <input type="checkbox" 
                                   name="aia_settings[forecasting_enabled]" 
                                   value="1" 
                                   <?php checked($settings['forecasting_enabled'] ?? true, true); ?> />
                            <?php _e('Demand Forecasting', 'ai-inventory-agent'); ?>
                        </label>
                        <br><br>
                        <label>
                            <input type="checkbox" 
                                   name="aia_settings[notifications_enabled]" 
                                   value="1" 
                                   <?php checked($settings['notifications_enabled'] ?? true, true); ?> />
                            <?php _e('Stock Notifications', 'ai-inventory-agent'); ?>
                        </label>
                        <br><br>
                        <label>
                            <input type="checkbox" 
                                   name="aia_settings[reports_enabled]" 
                                   value="1" 
                                   <?php checked($settings['reports_enabled'] ?? true, true); ?> />
                            <?php _e('Automated Reports', 'ai-inventory-agent'); ?>
                        </label>
                    </div>
                </div>

                <div class="aia-form-row">
                    <label class="aia-form-label" for="report_frequency">
                        <?php _e('Report Frequency', 'ai-inventory-agent'); ?>
                    </label>
                    <div class="aia-form-field">
                        <select name="aia_settings[report_frequency]" id="report_frequency">
                            <option value="daily" <?php selected($settings['report_frequency'] ?? 'weekly', 'daily'); ?>>
                                <?php _e('Daily', 'ai-inventory-agent'); ?>
                            </option>
                            <option value="weekly" <?php selected($settings['report_frequency'] ?? 'weekly', 'weekly'); ?>>
                                <?php _e('Weekly', 'ai-inventory-agent'); ?>
                            </option>
                            <option value="monthly" <?php selected($settings['report_frequency'] ?? 'weekly', 'monthly'); ?>>
                                <?php _e('Monthly', 'ai-inventory-agent'); ?>
                            </option>
                        </select>
                        <p class="aia-form-description">
                            <?php _e('How often should automated reports be generated and sent.', 'ai-inventory-agent'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <?php submit_button(__('Save Settings', 'ai-inventory-agent'), 'primary', 'submit', true, ['class' => 'aia-button']); ?>
    </form>

    <!-- Quick Setup Guide -->
    <div class="aia-settings-section">
        <h3><?php _e('Quick Setup Guide', 'ai-inventory-agent'); ?></h3>
        <div class="aia-alert info">
            <p><strong><?php _e('Getting Started:', 'ai-inventory-agent'); ?></strong></p>
            <ol>
                <li><?php _e('Choose your AI provider (OpenAI or Google Gemini)', 'ai-inventory-agent'); ?></li>
                <li><?php _e('Enter your API key and test the connection', 'ai-inventory-agent'); ?></li>
                <li><?php _e('Configure your inventory thresholds', 'ai-inventory-agent'); ?></li>
                <li><?php _e('Set up your notification preferences', 'ai-inventory-agent'); ?></li>
                <li><?php _e('Enable the modules you want to use', 'ai-inventory-agent'); ?></li>
                <li><?php _e('Save your settings and start using the AI assistant!', 'ai-inventory-agent'); ?></li>
            </ol>
        </div>
    </div>
</div>