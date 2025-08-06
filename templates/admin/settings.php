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