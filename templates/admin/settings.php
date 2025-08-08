<?php if(!defined('ABSPATH')) exit; ?>
<div class="wrap">
  <div class="aia-header">
    <div>
      <h1 class="aia-title"><?php esc_html_e('Settings','ai-inventory-agent'); ?></h1>
      <p class="aia-sub"><?php esc_html_e('Configure AI provider and thresholds.','ai-inventory-agent'); ?></p>
    </div>
  </div>

  <div class="aia-card">
    <div class="aia-card__hd"><?php esc_html_e('General','ai-inventory-agent'); ?></div>
    <div class="aia-card__bd">
      <form class="aia-form">
        <label><?php esc_html_e('AI Provider','ai-inventory-agent'); ?></label>
        <select class="aia-input" disabled>
          <option>OpenAI</option>
          <option>Gemini</option>
        </select>
        <p class="description"><?php esc_html_e('Provider setup will be implemented here.','ai-inventory-agent'); ?></p>
      </form>
    </div>
  </div>
</div>