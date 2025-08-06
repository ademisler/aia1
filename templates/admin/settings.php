<?php
/**
 * Settings Page Template - Modern Layout
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Skip Link for Accessibility -->
<a href="#main-content" class="aia-skip-link"><?php _e('Skip to main content', 'ai-inventory-agent'); ?></a>

<div class="wrap aia-admin-page">
    <!-- Page Header -->
    <header class="aia-page-header">
        <div class="aia-page-header__content">
            <div class="aia-page-title">
                <svg class="aia-icon aia-icon--xl aia-icon--primary" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-settings"></use>
                </svg>
                <h1 class="aia-heading-1"><?php _e('Settings', 'ai-inventory-agent'); ?></h1>
            </div>
            <p class="aia-body-base aia-text-secondary">
                <?php _e('Configure your AI Inventory Agent settings and preferences.', 'ai-inventory-agent'); ?>
            </p>
        </div>
    </header>

    <main id="main-content" class="aia-main-content">
        <div class="aia-layout-grid">
            <!-- Settings Form -->
            <div class="aia-layout-grid__main">
                <div class="aia-card">
                    <div class="aia-card__header">
                        <h2 class="aia-heading-3"><?php _e('Plugin Configuration', 'ai-inventory-agent'); ?></h2>
                        <p class="aia-body-small aia-text-tertiary">
                            <?php _e('Adjust settings to customize your AI assistant experience.', 'ai-inventory-agent'); ?>
                        </p>
                    </div>
                    
                    <div class="aia-card__content">
                        <form method="post" action="options.php" class="aia-settings-form">
                            <?php
                            settings_fields('aia_settings_group');
                            do_settings_sections('ai-inventory-agent');
                            ?>
                            
                            <div class="aia-form-actions">
                                <?php submit_button(
                                    __('Save Settings', 'ai-inventory-agent'), 
                                    'aia-button aia-button--primary', 
                                    'submit', 
                                    false, 
                                    ['id' => 'submit-settings']
                                ); ?>
                                
                                <button type="button" class="aia-button aia-button--secondary" id="reset-settings">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-refresh"></use>
                                    </svg>
                                    <?php _e('Reset to Defaults', 'ai-inventory-agent'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="aia-layout-grid__sidebar">
                <!-- Quick Setup Guide -->
                <div class="aia-card">
                    <div class="aia-card__header">
                        <h3 class="aia-heading-4">
                            <svg class="aia-icon aia-icon--sm aia-icon--success" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-info"></use>
                            </svg>
                            <?php _e('Quick Setup', 'ai-inventory-agent'); ?>
                        </h3>
                    </div>
                    
                    <div class="aia-card__content">
                        <div class="aia-setup-steps">
                            <div class="aia-setup-step">
                                <div class="aia-setup-step__icon">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bot"></use>
                                    </svg>
                                </div>
                                <div class="aia-setup-step__content">
                                    <h4 class="aia-body-base aia-text-primary"><?php _e('Choose AI Provider', 'ai-inventory-agent'); ?></h4>
                                    <p class="aia-body-small aia-text-tertiary"><?php _e('Select OpenAI or Google Gemini', 'ai-inventory-agent'); ?></p>
                                </div>
                            </div>
                            
                            <div class="aia-setup-step">
                                <div class="aia-setup-step__icon">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-settings"></use>
                                    </svg>
                                </div>
                                <div class="aia-setup-step__content">
                                    <h4 class="aia-body-base aia-text-primary"><?php _e('Configure API Key', 'ai-inventory-agent'); ?></h4>
                                    <p class="aia-body-small aia-text-tertiary"><?php _e('Enter and test your API connection', 'ai-inventory-agent'); ?></p>
                                </div>
                            </div>
                            
                            <div class="aia-setup-step">
                                <div class="aia-setup-step__icon">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-inventory"></use>
                                    </svg>
                                </div>
                                <div class="aia-setup-step__content">
                                    <h4 class="aia-body-base aia-text-primary"><?php _e('Set Thresholds', 'ai-inventory-agent'); ?></h4>
                                    <p class="aia-body-small aia-text-tertiary"><?php _e('Configure inventory alert levels', 'ai-inventory-agent'); ?></p>
                                </div>
                            </div>
                            
                            <div class="aia-setup-step">
                                <div class="aia-setup-step__icon">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-check"></use>
                                    </svg>
                                </div>
                                <div class="aia-setup-step__content">
                                    <h4 class="aia-body-base aia-text-primary"><?php _e('Start Using', 'ai-inventory-agent'); ?></h4>
                                    <p class="aia-body-small aia-text-tertiary"><?php _e('Save settings and enjoy AI assistance', 'ai-inventory-agent'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help & Support -->
                <div class="aia-card">
                    <div class="aia-card__header">
                        <h3 class="aia-heading-4">
                            <svg class="aia-icon aia-icon--sm aia-icon--secondary" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-info"></use>
                            </svg>
                            <?php _e('Help & Support', 'ai-inventory-agent'); ?>
                        </h3>
                    </div>
                    
                    <div class="aia-card__content">
                        <div class="aia-help-links">
                            <a href="#" class="aia-help-link">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-external-link"></use>
                                </svg>
                                <?php _e('Documentation', 'ai-inventory-agent'); ?>
                            </a>
                            
                            <a href="#" class="aia-help-link">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-mail"></use>
                                </svg>
                                <?php _e('Get Support', 'ai-inventory-agent'); ?>
                            </a>
                            
                            <a href="#" class="aia-help-link">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-external-link"></use>
                                </svg>
                                <?php _e('Feature Requests', 'ai-inventory-agent'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Live region for dynamic updates -->
<div aria-live="polite" aria-atomic="true" class="aia-live-region" id="aia-live-region"></div>