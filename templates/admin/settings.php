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
        <select class="aia-input" name="ai_provider" id="aia-provider">
          <option value="openai">OpenAI</option>
          <option value="gemini">Gemini</option>
          <option value="dummy">Dummy</option>
        </select>
        <p class="description"><?php esc_html_e('Select your provider and enter API key.','ai-inventory-agent'); ?></p>

        <label style="margin-top:12px;display:block;"><?php esc_html_e('API Key','ai-inventory-agent'); ?></label>
        <input class="aia-input" type="password" name="api_key" id="aia-api-key" placeholder="sk-..."/>

        <label style="margin-top:12px;display:block;"><?php esc_html_e('Model (optional)','ai-inventory-agent'); ?></label>
        <input class="aia-input" type="text" name="model" id="aia-model" placeholder="gpt-3.5-turbo / gemini-pro"/>

        <label style="margin-top:12px;display:block;"><?php esc_html_e('Low stock threshold','ai-inventory-agent'); ?></label>
        <input class="aia-input" type="number" name="low_stock_threshold" id="aia-low-th" min="0" value="5"/>

        <div style="margin-top:12px; display:flex; gap:8px; align-items:center;">
          <button type="submit" class="aia-btn aia-btn--primary"><?php esc_html_e('Save Settings','ai-inventory-agent'); ?></button>
          <button type="button" class="aia-btn" id="aia-test-connection"><?php esc_html_e('Test Connection','ai-inventory-agent'); ?></button>
          <span id="aia-test-result" class="description" aria-live="polite"></span>
        </div>
      </form>
    </div>
  </div>
</div>