<?php if(!defined('ABSPATH')) exit; ?>
<div class="wrap">
  <div class="aia-header aia-header--settings">
    <div>
      <h1 class="aia-title"><span class="aia-icon" data-lucide="settings-2"></span> <?php esc_html_e('Settings','ai-inventory-agent'); ?></h1>
      <p class="aia-sub"><?php esc_html_e('Configure AI provider and thresholds.','ai-inventory-agent'); ?></p>
    </div>
  </div>

  <div class="aia-card">
    <div class="aia-card__hd"><?php esc_html_e('General','ai-inventory-agent'); ?></div>
    <div class="aia-card__bd">
      <form class="aia-form" id="aia-settings-form">
        <label><?php esc_html_e('AI Provider','ai-inventory-agent'); ?></label>
        <select class="aia-input" name="ai_provider">
          <option>OpenAI</option>
          <option>Gemini</option>
        </select>
        <p class="description"><?php esc_html_e('Select your provider and enter API key.','ai-inventory-agent'); ?></p>

        <label style="margin-top:12px;display:block;"><?php esc_html_e('API Key','ai-inventory-agent'); ?></label>
        <input class="aia-input" type="password" name="api_key" placeholder="sk-..."/>

        <label style="margin-top:12px;display:block;"><?php esc_html_e('Low stock threshold','ai-inventory-agent'); ?></label>
        <input class="aia-input" type="number" name="low_stock_threshold" min="0" value="5"/>

        <p style="margin-top:12px;"><button type="submit" class="aia-btn aia-btn--primary"><?php esc_html_e('Save Settings','ai-inventory-agent'); ?></button></p>
      </form>
    </div>
  </div>
</div>