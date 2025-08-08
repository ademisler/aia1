<?php if(!defined('ABSPATH')) exit; ?>
<div class="wrap">
  <div class="aia-header">
    <div>
      <h1 class="aia-title"><?php esc_html_e('AI Chat','ai-inventory-agent'); ?></h1>
      <p class="aia-sub"><?php esc_html_e('Ask inventory-related questions and get instant insights.','ai-inventory-agent'); ?></p>
    </div>
  </div>

  <div class="aia-card">
    <div class="aia-card__hd"><?php esc_html_e('Conversation','ai-inventory-agent'); ?></div>
    <div class="aia-card__bd">
      <form id="aia-chat-form" class="aia-form">
        <textarea id="aia-chat-input" class="aia-textarea" placeholder="<?php esc_attr_e('Type your message...','ai-inventory-agent'); ?>"></textarea>
        <p class="description"><?php esc_html_e('AI provider not configured yet. Responses will be generic.','ai-inventory-agent'); ?></p>
        <p style="margin-top:10px;"><button class="aia-btn aia-btn--primary" type="submit"><?php esc_html_e('Send','ai-inventory-agent'); ?></button></p>
      </form>
    </div>
  </div>
</div>