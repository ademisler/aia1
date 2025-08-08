<?php if(!defined('ABSPATH')) exit; ?>
<div class="wrap">
  <div class="aia-header aia-header--chat">
    <div>
      <h1 class="aia-title"><span class="aia-icon" data-lucide="message-square"></span> <?php esc_html_e('AI Chat','ai-inventory-agent'); ?></h1>
      <p class="aia-sub"><?php esc_html_e('Ask inventory-related questions and get instant insights.','ai-inventory-agent'); ?></p>
    </div>
  </div>

  <div class="aia-card">
    <div class="aia-card__hd" style="display:flex;justify-content:space-between;align-items:center;">
      <span><?php esc_html_e('Conversation','ai-inventory-agent'); ?></span>
      <span style="display:flex;gap:8px;">
        <button type="button" class="aia-btn" id="aia-chat-copy"><?php esc_html_e('Copy','ai-inventory-agent'); ?></button>
        <button type="button" class="aia-btn" id="aia-chat-clear"><?php esc_html_e('Clear','ai-inventory-agent'); ?></button>
      </span>
    </div>
    <div class="aia-card__bd">
      <div id="aia-chat-list" style="min-height:160px;border:1px dashed rgba(2,6,23,.12);border-radius:10px;padding:10px 12px;margin-bottom:10px;overflow:auto;max-height:320px;"></div>
      <form id="aia-chat-form" class="aia-form">
        <textarea id="aia-chat-input" class="aia-textarea" placeholder="<?php esc_attr_e('Type your message...','ai-inventory-agent'); ?>"></textarea>
        <p class="description"><?php esc_html_e('AI provider not configured yet. Responses will be generic.','ai-inventory-agent'); ?></p>
        <p style="margin-top:10px;"><button class="aia-btn aia-btn--primary" type="submit"><?php esc_html_e('Send','ai-inventory-agent'); ?></button></p>
      </form>
    </div>
  </div>
</div>