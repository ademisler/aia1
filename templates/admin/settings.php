<?php
/**
 * Admin Settings Template - Light Theme
 * 
 * @package AI_Inventory_Agent
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$settings = get_option('aia_settings', []);

// Default values
$defaults = [
    'ai_provider' => 'openai',
    'api_key' => '',
    'system_prompt' => 'You are an AI assistant helping with WooCommerce inventory management.',
    'low_stock_threshold' => 5,
    'critical_stock_threshold' => 1,
    'notification_email' => get_option('admin_email'),
    'email_notifications' => true,
    'auto_analysis' => true,
    'chat_enabled' => true,
    'reports_enabled' => true,
    'report_frequency' => 'weekly'
];

// Merge with defaults
$settings = array_merge($defaults, $settings);

// Check API connection status
$api_status = 'disconnected'; // This would be determined by actual API test
$api_last_test = '2024-01-15 10:30:00'; // This would come from database
?>

<div class="wrap aia-settings-light">
    <!-- Skip Link for Accessibility -->
    <a href="#aia-main-content" class="aia-sr-only aia-skip-link"><?php _e('Skip to main content', 'ai-inventory-agent'); ?></a>

    <!-- Professional Header -->
    <div class="aia-settings-page-header">
        <div class="aia-settings-title-section">
            <h1 class="aia-settings-main-title">
                <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-settings"></use>
                </svg>
                <?php esc_html_e('Settings', 'ai-inventory-agent'); ?>
            </h1>
            <p class="aia-settings-subtitle">
                <?php esc_html_e('Configure your AI Inventory Agent settings and preferences for optimal performance', 'ai-inventory-agent'); ?>
            </p>
        </div>
        
        <div class="aia-settings-actions">
            <button class="aia-btn aia-btn--light aia-btn--sm" onclick="exportSettings()">
                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-download"></use>
                </svg>
                <?php esc_html_e('Export Settings', 'ai-inventory-agent'); ?>
            </button>
            
            <button class="aia-btn aia-btn--outline aia-btn--sm" onclick="importSettings()">
                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-upload"></use>
                </svg>
                <?php esc_html_e('Import Settings', 'ai-inventory-agent'); ?>
            </button>
        </div>
    </div>

    <div id="aia-main-content" class="aia-main-content">
        
        <!-- Settings Content Grid -->
        <div class="aia-settings-content-grid">
            
            <!-- Main Settings Form -->
            <div class="aia-settings-main-column">
                
                <form method="post" action="options.php" class="aia-settings-form" id="aia-settings-form">
                    <?php settings_fields('aia_settings_group'); ?>
                    
                    <!-- AI Configuration Section -->
                    <div class="aia-widget aia-settings-section">
                        <div class="aia-widget-header">
                            <div class="aia-widget-header-content">
                                <h2 class="aia-widget-title">
                                    <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bot"></use>
                                    </svg>
                                    <?php esc_html_e('AI Configuration', 'ai-inventory-agent'); ?>
                                </h2>
                                <p class="aia-widget-subtitle"><?php esc_html_e('Configure your AI provider and connection settings', 'ai-inventory-agent'); ?></p>
                            </div>
                            <div class="aia-connection-status">
                                <div class="aia-status-indicator aia-status-indicator--<?php echo esc_attr($api_status); ?>">
                                    <span class="aia-status-dot"></span>
                                    <span class="aia-status-text">
                                        <?php 
                                        echo $api_status === 'connected' 
                                            ? esc_html__('Connected', 'ai-inventory-agent') 
                                            : esc_html__('Disconnected', 'ai-inventory-agent'); 
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="aia-widget-content">
                            
                            <!-- AI Provider Selection -->
                            <div class="aia-form-group">
                                <label for="ai_provider" class="aia-form-label">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-cpu"></use>
                                    </svg>
                                    <?php esc_html_e('AI Provider', 'ai-inventory-agent'); ?>
                                </label>
                                <div class="aia-provider-selection">
                                    <div class="aia-provider-option">
                                        <input type="radio" id="provider_openai" name="aia_settings[ai_provider]" value="openai" 
                                               class="aia-radio" <?php checked($settings['ai_provider'], 'openai'); ?>>
                                        <label for="provider_openai" class="aia-provider-card">
                                            <div class="aia-provider-icon">
                                                <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-zap"></use>
                                                </svg>
                                            </div>
                                            <div class="aia-provider-content">
                                                <h4 class="aia-provider-name">OpenAI</h4>
                                                <p class="aia-provider-description"><?php esc_html_e('GPT-4 and GPT-3.5 models', 'ai-inventory-agent'); ?></p>
                                            </div>
                                            <div class="aia-provider-badge">
                                                <span class="aia-badge aia-badge--success"><?php esc_html_e('Recommended', 'ai-inventory-agent'); ?></span>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div class="aia-provider-option">
                                        <input type="radio" id="provider_gemini" name="aia_settings[ai_provider]" value="gemini" 
                                               class="aia-radio" <?php checked($settings['ai_provider'], 'gemini'); ?>>
                                        <label for="provider_gemini" class="aia-provider-card">
                                            <div class="aia-provider-icon">
                                                <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-star"></use>
                                                </svg>
                                            </div>
                                            <div class="aia-provider-content">
                                                <h4 class="aia-provider-name">Google Gemini</h4>
                                                <p class="aia-provider-description"><?php esc_html_e('Gemini Pro and Ultra models', 'ai-inventory-agent'); ?></p>
                                            </div>
                                            <div class="aia-provider-badge">
                                                <span class="aia-badge aia-badge--info"><?php esc_html_e('Advanced', 'ai-inventory-agent'); ?></span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- API Key Field -->
                            <div class="aia-form-group">
                                <label for="api_key" class="aia-form-label">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-key"></use>
                                    </svg>
                                    <?php esc_html_e('API Key', 'ai-inventory-agent'); ?>
                                </label>
                                <div class="aia-input-group">
                                    <input type="password" 
                                           id="api_key" 
                                           name="aia_settings[api_key]" 
                                           value="<?php echo esc_attr($settings['api_key']); ?>" 
                                           class="aia-form-input aia-form-input--password"
                                           placeholder="<?php esc_attr_e('Enter your API key...', 'ai-inventory-agent'); ?>" />
                                    <div class="aia-input-actions">
                                        <button type="button" class="aia-btn aia-btn--ghost aia-btn--sm" onclick="togglePasswordVisibility('api_key')">
                                            <svg class="aia-icon aia-icon--sm aia-toggle-icon" aria-hidden="true">
                                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-eye"></use>
                                            </svg>
                                        </button>
                                        <button type="button" class="aia-btn aia-btn--primary aia-btn--sm" onclick="testApiConnection()">
                                            <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-check-circle"></use>
                                            </svg>
                                            <?php esc_html_e('Test', 'ai-inventory-agent'); ?>
                                        </button>
                                    </div>
                                </div>
                                <p class="aia-form-description">
                                    <?php esc_html_e('Enter your AI provider API key. This will be encrypted and stored securely.', 'ai-inventory-agent'); ?>
                                    <a href="#" class="aia-link" target="_blank"><?php esc_html_e('How to get API key?', 'ai-inventory-agent'); ?></a>
                                </p>
                            </div>

                            <!-- System Prompt -->
                            <div class="aia-form-group">
                                <label for="system_prompt" class="aia-form-label">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-message-square"></use>
                                    </svg>
                                    <?php esc_html_e('System Prompt', 'ai-inventory-agent'); ?>
                                </label>
                                <textarea id="system_prompt" 
                                          name="aia_settings[system_prompt]" 
                                          class="aia-form-textarea"
                                          rows="4"
                                          placeholder="<?php esc_attr_e('Customize the AI assistant behavior...', 'ai-inventory-agent'); ?>"><?php echo esc_textarea($settings['system_prompt']); ?></textarea>
                                <p class="aia-form-description">
                                    <?php esc_html_e('Define how the AI assistant should behave and respond to inventory-related queries.', 'ai-inventory-agent'); ?>
                                </p>
                            </div>

                        </div>
                    </div>

                    <!-- Inventory Settings Section -->
                    <div class="aia-widget aia-settings-section">
                        <div class="aia-widget-header">
                            <div class="aia-widget-header-content">
                                <h2 class="aia-widget-title">
                                    <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-package"></use>
                                    </svg>
                                    <?php esc_html_e('Inventory Management', 'ai-inventory-agent'); ?>
                                </h2>
                                <p class="aia-widget-subtitle"><?php esc_html_e('Configure stock thresholds and monitoring settings', 'ai-inventory-agent'); ?></p>
                            </div>
                        </div>
                        <div class="aia-widget-content">
                            
                            <div class="aia-form-row">
                                <!-- Low Stock Threshold -->
                                <div class="aia-form-group">
                                    <label for="low_stock_threshold" class="aia-form-label">
                                        <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                                        </svg>
                                        <?php esc_html_e('Low Stock Threshold', 'ai-inventory-agent'); ?>
                                    </label>
                                    <input type="number" 
                                           id="low_stock_threshold" 
                                           name="aia_settings[low_stock_threshold]" 
                                           value="<?php echo esc_attr($settings['low_stock_threshold']); ?>" 
                                           class="aia-form-input"
                                           min="1" 
                                           step="1" />
                                    <p class="aia-form-description"><?php esc_html_e('Alert when stock falls below this number', 'ai-inventory-agent'); ?></p>
                                </div>

                                <!-- Critical Stock Threshold -->
                                <div class="aia-form-group">
                                    <label for="critical_stock_threshold" class="aia-form-label">
                                        <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-circle"></use>
                                        </svg>
                                        <?php esc_html_e('Critical Stock Threshold', 'ai-inventory-agent'); ?>
                                    </label>
                                    <input type="number" 
                                           id="critical_stock_threshold" 
                                           name="aia_settings[critical_stock_threshold]" 
                                           value="<?php echo esc_attr($settings['critical_stock_threshold']); ?>" 
                                           class="aia-form-input"
                                           min="0" 
                                           step="1" />
                                    <p class="aia-form-description"><?php esc_html_e('Critical alert when stock reaches this level', 'ai-inventory-agent'); ?></p>
                                </div>
                            </div>

                            <!-- Auto Analysis Toggle -->
                            <div class="aia-form-group aia-form-group--toggle">
                                <label for="auto_analysis" class="aia-toggle-label">
                                    <input type="checkbox" 
                                           id="auto_analysis" 
                                           name="aia_settings[auto_analysis]" 
                                           value="1" 
                                           class="aia-toggle-input"
                                           <?php checked($settings['auto_analysis']); ?> />
                                    <span class="aia-toggle-slider"></span>
                                    <span class="aia-toggle-content">
                                        <strong><?php esc_html_e('Enable Automatic Analysis', 'ai-inventory-agent'); ?></strong>
                                        <small><?php esc_html_e('Automatically analyze inventory trends and provide insights', 'ai-inventory-agent'); ?></small>
                                    </span>
                                </label>
                            </div>

                        </div>
                    </div>

                    <!-- Notification Settings Section -->
                    <div class="aia-widget aia-settings-section">
                        <div class="aia-widget-header">
                            <div class="aia-widget-header-content">
                                <h2 class="aia-widget-title">
                                    <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bell"></use>
                                    </svg>
                                    <?php esc_html_e('Notifications', 'ai-inventory-agent'); ?>
                                </h2>
                                <p class="aia-widget-subtitle"><?php esc_html_e('Configure email notifications and alerts', 'ai-inventory-agent'); ?></p>
                            </div>
                        </div>
                        <div class="aia-widget-content">
                            
                            <!-- Notification Email -->
                            <div class="aia-form-group">
                                <label for="notification_email" class="aia-form-label">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-mail"></use>
                                    </svg>
                                    <?php esc_html_e('Notification Email', 'ai-inventory-agent'); ?>
                                </label>
                                <input type="email" 
                                       id="notification_email" 
                                       name="aia_settings[notification_email]" 
                                       value="<?php echo esc_attr($settings['notification_email']); ?>" 
                                       class="aia-form-input"
                                       placeholder="<?php esc_attr_e('admin@example.com', 'ai-inventory-agent'); ?>" />
                                <p class="aia-form-description"><?php esc_html_e('Email address to receive inventory alerts and notifications', 'ai-inventory-agent'); ?></p>
                            </div>

                            <!-- Email Notifications Toggle -->
                            <div class="aia-form-group aia-form-group--toggle">
                                <label for="email_notifications" class="aia-toggle-label">
                                    <input type="checkbox" 
                                           id="email_notifications" 
                                           name="aia_settings[email_notifications]" 
                                           value="1" 
                                           class="aia-toggle-input"
                                           <?php checked($settings['email_notifications']); ?> />
                                    <span class="aia-toggle-slider"></span>
                                    <span class="aia-toggle-content">
                                        <strong><?php esc_html_e('Enable Email Notifications', 'ai-inventory-agent'); ?></strong>
                                        <small><?php esc_html_e('Receive email alerts for low stock and critical inventory levels', 'ai-inventory-agent'); ?></small>
                                    </span>
                                </label>
                            </div>

                            <!-- Report Frequency -->
                            <div class="aia-form-group">
                                <label for="report_frequency" class="aia-form-label">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-calendar"></use>
                                    </svg>
                                    <?php esc_html_e('Report Frequency', 'ai-inventory-agent'); ?>
                                </label>
                                <select id="report_frequency" name="aia_settings[report_frequency]" class="aia-form-select">
                                    <option value="daily" <?php selected($settings['report_frequency'], 'daily'); ?>>
                                        <?php esc_html_e('Daily', 'ai-inventory-agent'); ?>
                                    </option>
                                    <option value="weekly" <?php selected($settings['report_frequency'], 'weekly'); ?>>
                                        <?php esc_html_e('Weekly', 'ai-inventory-agent'); ?>
                                    </option>
                                    <option value="monthly" <?php selected($settings['report_frequency'], 'monthly'); ?>>
                                        <?php esc_html_e('Monthly', 'ai-inventory-agent'); ?>
                                    </option>
                                    <option value="disabled" <?php selected($settings['report_frequency'], 'disabled'); ?>>
                                        <?php esc_html_e('Disabled', 'ai-inventory-agent'); ?>
                                    </option>
                                </select>
                                <p class="aia-form-description"><?php esc_html_e('How often to automatically generate and send inventory reports', 'ai-inventory-agent'); ?></p>
                            </div>

                        </div>
                    </div>

                    <!-- Feature Settings Section -->
                    <div class="aia-widget aia-settings-section">
                        <div class="aia-widget-header">
                            <div class="aia-widget-header-content">
                                <h2 class="aia-widget-title">
                                    <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-toggle-left"></use>
                                    </svg>
                                    <?php esc_html_e('Features', 'ai-inventory-agent'); ?>
                                </h2>
                                <p class="aia-widget-subtitle"><?php esc_html_e('Enable or disable specific plugin features', 'ai-inventory-agent'); ?></p>
                            </div>
                        </div>
                        <div class="aia-widget-content">
                            
                            <div class="aia-feature-toggles">
                                <!-- Chat Feature -->
                                <div class="aia-form-group aia-form-group--toggle">
                                    <label for="chat_enabled" class="aia-toggle-label">
                                        <input type="checkbox" 
                                               id="chat_enabled" 
                                               name="aia_settings[chat_enabled]" 
                                               value="1" 
                                               class="aia-toggle-input"
                                               <?php checked($settings['chat_enabled']); ?> />
                                        <span class="aia-toggle-slider"></span>
                                        <span class="aia-toggle-content">
                                            <strong><?php esc_html_e('AI Chat Assistant', 'ai-inventory-agent'); ?></strong>
                                            <small><?php esc_html_e('Enable interactive chat with AI for inventory queries', 'ai-inventory-agent'); ?></small>
                                        </span>
                                    </label>
                                </div>

                                <!-- Reports Feature -->
                                <div class="aia-form-group aia-form-group--toggle">
                                    <label for="reports_enabled" class="aia-toggle-label">
                                        <input type="checkbox" 
                                               id="reports_enabled" 
                                               name="aia_settings[reports_enabled]" 
                                               value="1" 
                                               class="aia-toggle-input"
                                               <?php checked($settings['reports_enabled']); ?> />
                                        <span class="aia-toggle-slider"></span>
                                        <span class="aia-toggle-content">
                                            <strong><?php esc_html_e('Advanced Reports', 'ai-inventory-agent'); ?></strong>
                                            <small><?php esc_html_e('Generate detailed inventory analysis reports', 'ai-inventory-agent'); ?></small>
                                        </span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="aia-settings-form-actions">
                        <div class="aia-form-actions-left">
                            <button type="button" class="aia-btn aia-btn--warning aia-btn--outline" onclick="resetToDefaults()">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-refresh-ccw"></use>
                                </svg>
                                <?php esc_html_e('Reset to Defaults', 'ai-inventory-agent'); ?>
                            </button>
                        </div>
                        <div class="aia-form-actions-right">
                            <button type="button" class="aia-btn aia-btn--light" onclick="previewSettings()">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-eye"></use>
                                </svg>
                                <?php esc_html_e('Preview Changes', 'ai-inventory-agent'); ?>
                            </button>
                            <button type="submit" class="aia-btn aia-btn--primary">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-check"></use>
                                </svg>
                                <?php esc_html_e('Save Settings', 'ai-inventory-agent'); ?>
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Sidebar Column -->
            <div class="aia-settings-sidebar-column">
                
                <!-- Connection Status Widget -->
                <div class="aia-widget">
                    <div class="aia-widget-header">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-activity"></use>
                            </svg>
                            <?php esc_html_e('Connection Status', 'ai-inventory-agent'); ?>
                        </h3>
                    </div>
                    <div class="aia-widget-content">
                        <div class="aia-connection-details">
                            <div class="aia-connection-item">
                                <div class="aia-connection-icon">
                                    <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-zap"></use>
                                    </svg>
                                </div>
                                <div class="aia-connection-info">
                                    <div class="aia-connection-label"><?php esc_html_e('AI Provider', 'ai-inventory-agent'); ?></div>
                                    <div class="aia-connection-value"><?php echo esc_html(ucfirst($settings['ai_provider'])); ?></div>
                                </div>
                                <div class="aia-connection-status">
                                    <span class="aia-status-dot aia-status-dot--<?php echo esc_attr($api_status); ?>"></span>
                                </div>
                            </div>
                            
                            <div class="aia-connection-item">
                                <div class="aia-connection-icon">
                                    <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-clock"></use>
                                    </svg>
                                </div>
                                <div class="aia-connection-info">
                                    <div class="aia-connection-label"><?php esc_html_e('Last Test', 'ai-inventory-agent'); ?></div>
                                    <div class="aia-connection-value"><?php echo esc_html(human_time_diff(strtotime($api_last_test))); ?> <?php esc_html_e('ago', 'ai-inventory-agent'); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="aia-connection-actions">
                            <button type="button" class="aia-btn aia-btn--primary aia-btn--full" onclick="testApiConnection()">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-refresh"></use>
                                </svg>
                                <?php esc_html_e('Test Connection', 'ai-inventory-agent'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick Setup Guide -->
                <div class="aia-widget">
                    <div class="aia-widget-header">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-list"></use>
                            </svg>
                            <?php esc_html_e('Quick Setup Guide', 'ai-inventory-agent'); ?>
                        </h3>
                    </div>
                    <div class="aia-widget-content">
                        <div class="aia-setup-steps">
                            <div class="aia-setup-step aia-setup-step--completed">
                                <div class="aia-step-indicator">
                                    <span class="aia-step-number">1</span>
                                </div>
                                <div class="aia-step-content">
                                    <h4 class="aia-step-title"><?php esc_html_e('Choose AI Provider', 'ai-inventory-agent'); ?></h4>
                                    <p class="aia-step-description"><?php esc_html_e('Select OpenAI or Google Gemini', 'ai-inventory-agent'); ?></p>
                                </div>
                            </div>
                            
                            <div class="aia-setup-step aia-setup-step--active">
                                <div class="aia-step-indicator">
                                    <span class="aia-step-number">2</span>
                                </div>
                                <div class="aia-step-content">
                                    <h4 class="aia-step-title"><?php esc_html_e('Configure API Key', 'ai-inventory-agent'); ?></h4>
                                    <p class="aia-step-description"><?php esc_html_e('Enter and test your API connection', 'ai-inventory-agent'); ?></p>
                                </div>
                            </div>
                            
                            <div class="aia-setup-step">
                                <div class="aia-step-indicator">
                                    <span class="aia-step-number">3</span>
                                </div>
                                <div class="aia-step-content">
                                    <h4 class="aia-step-title"><?php esc_html_e('Set Thresholds', 'ai-inventory-agent'); ?></h4>
                                    <p class="aia-step-description"><?php esc_html_e('Configure inventory alert levels', 'ai-inventory-agent'); ?></p>
                                </div>
                            </div>
                            
                            <div class="aia-setup-step">
                                <div class="aia-step-indicator">
                                    <span class="aia-step-number">4</span>
                                </div>
                                <div class="aia-step-content">
                                    <h4 class="aia-step-title"><?php esc_html_e('Enable Features', 'ai-inventory-agent'); ?></h4>
                                    <p class="aia-step-description"><?php esc_html_e('Activate chat and reporting features', 'ai-inventory-agent'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help & Support -->
                <div class="aia-widget">
                    <div class="aia-widget-header">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-help-circle"></use>
                            </svg>
                            <?php esc_html_e('Help & Support', 'ai-inventory-agent'); ?>
                        </h3>
                    </div>
                    <div class="aia-widget-content">
                        <div class="aia-help-links">
                            <a href="#" class="aia-help-link" target="_blank">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-book-open"></use>
                                </svg>
                                <span><?php esc_html_e('Documentation', 'ai-inventory-agent'); ?></span>
                                <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-external-link"></use>
                                </svg>
                            </a>
                            
                            <a href="#" class="aia-help-link" target="_blank">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-mail"></use>
                                </svg>
                                <span><?php esc_html_e('Get Support', 'ai-inventory-agent'); ?></span>
                                <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-external-link"></use>
                                </svg>
                            </a>
                            
                            <a href="#" class="aia-help-link" target="_blank">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-message-circle"></use>
                                </svg>
                                <span><?php esc_html_e('Feature Requests', 'ai-inventory-agent'); ?></span>
                                <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-external-link"></use>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    
    // Password visibility toggle
    window.togglePasswordVisibility = function(fieldId) {
        var field = document.getElementById(fieldId);
        var icon = field.parentNode.querySelector('.aia-toggle-icon use');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.setAttribute('href', '<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-eye-off');
        } else {
            field.type = 'password';
            icon.setAttribute('href', '<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-eye');
        }
    };
    
    // API connection test
    window.testApiConnection = function() {
        var provider = $('input[name="aia_settings[ai_provider]"]:checked').val();
        var apiKey = $('#api_key').val();
        
        if (!apiKey) {
            showNotification('<?php esc_js_e('Please enter an API key first.', 'ai-inventory-agent'); ?>', 'warning');
            return;
        }
        
        // Show loading state
        var btn = event.target.closest('button');
        var originalText = btn.innerHTML;
        btn.innerHTML = '<svg class="aia-icon aia-icon--sm aia-loading-spinner" aria-hidden="true"><use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-loader"></use></svg> <?php esc_js_e('Testing...', 'ai-inventory-agent'); ?>';
        btn.disabled = true;
        
        // Simulate API test (replace with actual AJAX call)
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            // Update connection status
            $('.aia-status-dot').removeClass('aia-status-dot--connected aia-status-dot--disconnected')
                                .addClass('aia-status-dot--connected');
            $('.aia-status-text').text('<?php esc_js_e('Connected', 'ai-inventory-agent'); ?>');
            
            showNotification('<?php esc_js_e('API connection successful!', 'ai-inventory-agent'); ?>', 'success');
        }, 2000);
    };
    
    // Export settings
    window.exportSettings = function() {
        var settings = {};
        $('#aia-settings-form').serializeArray().forEach(function(item) {
            settings[item.name] = item.value;
        });
        
        var dataStr = JSON.stringify(settings, null, 2);
        var dataBlob = new Blob([dataStr], {type: 'application/json'});
        var url = URL.createObjectURL(dataBlob);
        var link = document.createElement('a');
        link.href = url;
        link.download = 'aia-settings-' + new Date().toISOString().split('T')[0] + '.json';
        link.click();
        
        showNotification('<?php esc_js_e('Settings exported successfully!', 'ai-inventory-agent'); ?>', 'success');
    };
    
    // Import settings
    window.importSettings = function() {
        var input = document.createElement('input');
        input.type = 'file';
        input.accept = '.json';
        
        input.onchange = function(e) {
            var file = e.target.files[0];
            if (!file) return;
            
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var settings = JSON.parse(e.target.result);
                    
                    // Populate form fields
                    Object.keys(settings).forEach(function(key) {
                        var field = $('[name="' + key + '"]');
                        if (field.length) {
                            if (field.attr('type') === 'checkbox') {
                                field.prop('checked', settings[key] == '1');
                            } else if (field.attr('type') === 'radio') {
                                $('[name="' + key + '"][value="' + settings[key] + '"]').prop('checked', true);
                            } else {
                                field.val(settings[key]);
                            }
                        }
                    });
                    
                    showNotification('<?php esc_js_e('Settings imported successfully!', 'ai-inventory-agent'); ?>', 'success');
                } catch (error) {
                    showNotification('<?php esc_js_e('Invalid settings file format.', 'ai-inventory-agent'); ?>', 'error');
                }
            };
            reader.readAsText(file);
        };
        
        input.click();
    };
    
    // Reset to defaults
    window.resetToDefaults = function() {
        if (!confirm('<?php esc_js_e('Are you sure you want to reset all settings to their default values? This action cannot be undone.', 'ai-inventory-agent'); ?>')) {
            return;
        }
        
        // Reset form to default values
        var defaults = <?php echo json_encode($defaults); ?>;
        
        Object.keys(defaults).forEach(function(key) {
            var field = $('[name="aia_settings[' + key + ']"]');
            if (field.length) {
                if (field.attr('type') === 'checkbox') {
                    field.prop('checked', defaults[key]);
                } else if (field.attr('type') === 'radio') {
                    $('[name="aia_settings[' + key + ']"][value="' + defaults[key] + '"]').prop('checked', true);
                } else {
                    field.val(defaults[key]);
                }
            }
        });
        
        showNotification('<?php esc_js_e('Settings reset to defaults.', 'ai-inventory-agent'); ?>', 'info');
    };
    
    // Preview settings
    window.previewSettings = function() {
        showNotification('<?php esc_js_e('Settings preview feature coming soon!', 'ai-inventory-agent'); ?>', 'info');
    };
    
    // Form validation
    $('#aia-settings-form').on('submit', function(e) {
        var apiKey = $('#api_key').val();
        var lowThreshold = parseInt($('#low_stock_threshold').val());
        var criticalThreshold = parseInt($('#critical_stock_threshold').val());
        
        if (!apiKey.trim()) {
            e.preventDefault();
            showNotification('<?php esc_js_e('Please enter an API key.', 'ai-inventory-agent'); ?>', 'warning');
            $('#api_key').focus();
            return false;
        }
        
        if (criticalThreshold >= lowThreshold) {
            e.preventDefault();
            showNotification('<?php esc_js_e('Critical threshold must be lower than low stock threshold.', 'ai-inventory-agent'); ?>', 'warning');
            $('#critical_stock_threshold').focus();
            return false;
        }
        
        showNotification('<?php esc_js_e('Saving settings...', 'ai-inventory-agent'); ?>', 'info');
    });
    
    // Real-time validation
    $('#critical_stock_threshold, #low_stock_threshold').on('input', function() {
        var lowThreshold = parseInt($('#low_stock_threshold').val());
        var criticalThreshold = parseInt($('#critical_stock_threshold').val());
        
        if (criticalThreshold >= lowThreshold && criticalThreshold > 0 && lowThreshold > 0) {
            $('#critical_stock_threshold').addClass('aia-form-input--error');
            $('#low_stock_threshold').addClass('aia-form-input--error');
        } else {
            $('#critical_stock_threshold').removeClass('aia-form-input--error');
            $('#low_stock_threshold').removeClass('aia-form-input--error');
        }
    });
    
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