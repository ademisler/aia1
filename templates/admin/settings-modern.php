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
        <!-- Modern Settings Header -->
        <div class="aia-settings-header">
            <div class="aia-settings-header-bg">
                <div class="aia-settings-gradient-overlay"></div>
                <div class="aia-settings-gear-pattern">
                    <div class="aia-settings-gear aia-settings-gear--large">
                        <svg viewBox="0 0 24 24">
                            <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
                        </svg>
                    </div>
                    <div class="aia-settings-gear aia-settings-gear--medium">
                        <svg viewBox="0 0 24 24">
                            <path d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5a3.5,3.5 0 0,1 3.5,3.5A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.22,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.22,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.68 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"/>
                        </svg>
                    </div>
                    <div class="aia-settings-gear aia-settings-gear--small">
                        <svg viewBox="0 0 24 24">
                            <path d="M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8M12,10A2,2 0 0,0 10,12A2,2 0 0,0 12,14A2,2 0 0,0 14,12A2,2 0 0,0 12,10M10,22C9.75,22 9.54,21.82 9.5,21.58L9.13,18.93C8.5,18.68 7.96,18.34 7.44,17.94L4.95,18.95C4.73,19.03 4.46,18.95 4.34,18.73L2.34,15.27C2.22,15.05 2.27,14.78 2.46,14.63L4.57,12.97L4.5,12L4.57,11.03L2.46,9.37C2.27,9.22 2.22,8.95 2.34,8.73L4.34,5.27C4.46,5.05 4.73,4.96 4.95,5.05L7.44,6.05C7.96,5.66 8.5,5.32 9.13,5.07L9.5,2.42C9.54,2.18 9.75,2 10,2H14C14.25,2 14.46,2.18 14.5,2.42L14.87,5.07C15.5,5.32 16.04,5.66 16.56,6.05L19.05,5.05C19.27,4.96 19.54,5.05 19.66,5.27L21.66,8.73C21.78,8.95 21.73,9.22 21.54,9.37L19.43,11.03L19.5,12L19.43,12.97L21.54,14.63C21.73,14.78 21.78,15.05 21.66,15.27L19.66,18.73C19.54,18.95 19.27,19.03 19.05,18.95L16.56,17.94C16.04,18.34 15.5,18.68 14.87,18.93L14.5,21.58C14.46,21.82 14.25,22 14,22H10Z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="aia-settings-header-content">
                <div class="aia-settings-title-section">
                    <div class="aia-settings-icon-container">
                        <div class="aia-settings-icon-bg">
                            <svg class="aia-settings-icon" viewBox="0 0 24 24">
                                <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
                            </svg>
                        </div>
                        <div class="aia-settings-icon-glow"></div>
                    </div>
                    
                    <div class="aia-settings-text-content">
                        <h1 class="aia-settings-main-title">
                            <?php echo esc_html(get_admin_page_title()); ?>
                            <span class="aia-settings-title-badge">
                                <svg class="aia-settings-badge-icon" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <?php esc_html_e('Pro', 'ai-inventory-agent'); ?>
                            </span>
                        </h1>
                        <p class="aia-settings-subtitle">
                            <?php esc_html_e('Configure your AI Inventory Agent settings and customize your experience for optimal inventory management', 'ai-inventory-agent'); ?>
                        </p>
                        
                        <div class="aia-settings-breadcrumb">
                            <span class="aia-settings-breadcrumb-item">
                                <svg class="aia-settings-breadcrumb-icon" viewBox="0 0 24 24">
                                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                                </svg>
                                <?php esc_html_e('Dashboard', 'ai-inventory-agent'); ?>
                            </span>
                            <svg class="aia-settings-breadcrumb-separator" viewBox="0 0 24 24">
                                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                            </svg>
                            <span class="aia-settings-breadcrumb-item aia-settings-breadcrumb-item--active">
                                <?php esc_html_e('Settings', 'ai-inventory-agent'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="aia-settings-header-actions">
                    <div class="aia-settings-action-group">
                        <button type="button" class="aia-settings-btn aia-settings-btn--outline" id="aia-reset-settings">
                            <svg class="aia-settings-btn-icon" viewBox="0 0 24 24">
                                <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                            </svg>
                            <?php esc_html_e('Reset to Defaults', 'ai-inventory-agent'); ?>
                        </button>
                        
                        <div class="aia-settings-save-status">
                            <div class="aia-settings-save-indicator">
                                <div class="aia-settings-save-dot"></div>
                                <span class="aia-settings-save-text"><?php esc_html_e('All changes saved', 'ai-inventory-agent'); ?></span>
                            </div>
                        </div>
                        
                        <button type="submit" form="aia-settings-form" class="aia-settings-btn aia-settings-btn--primary">
                            <svg class="aia-settings-btn-icon" viewBox="0 0 24 24">
                                <path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/>
                            </svg>
                            <?php esc_html_e('Save Settings', 'ai-inventory-agent'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Settings Tabs -->
        <div class="aia-settings-tabs">
            <div class="aia-settings-tab-list" role="tablist">
                <button class="aia-settings-tab aia-settings-tab--active" data-tab="general" role="tab">
                    <div class="aia-settings-tab-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                    </div>
                    <span class="aia-settings-tab-label"><?php esc_html_e('General', 'ai-inventory-agent'); ?></span>
                </button>
                <button class="aia-settings-tab" data-tab="ai" role="tab">
                    <div class="aia-settings-tab-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <span class="aia-settings-tab-label"><?php esc_html_e('AI Configuration', 'ai-inventory-agent'); ?></span>
                </button>
                <button class="aia-settings-tab" data-tab="inventory" role="tab">
                    <div class="aia-settings-tab-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <span class="aia-settings-tab-label"><?php esc_html_e('Inventory', 'ai-inventory-agent'); ?></span>
                </button>
                <button class="aia-settings-tab" data-tab="notifications" role="tab">
                    <div class="aia-settings-tab-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                        </svg>
                    </div>
                    <span class="aia-settings-tab-label"><?php esc_html_e('Notifications', 'ai-inventory-agent'); ?></span>
                </button>
                <button class="aia-settings-tab" data-tab="advanced" role="tab">
                    <div class="aia-settings-tab-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z"/>
                        </svg>
                    </div>
                    <span class="aia-settings-tab-label"><?php esc_html_e('Advanced', 'ai-inventory-agent'); ?></span>
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