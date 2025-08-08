<?php if(!defined('ABSPATH')) exit; ?>
<div class="wrap">
  <div class="aia-header">
    <div>
      <h1 class="aia-title"><?php esc_html_e('Inventory Dashboard','ai-inventory-agent'); ?></h1>
      <p class="aia-sub"><?php esc_html_e('Overview of key metrics and recent activity.','ai-inventory-agent'); ?></p>
    </div>
    <a href="<?php echo esc_url(admin_url('admin.php?page=aia-chat')); ?>" class="aia-btn aia-btn--primary"><?php esc_html_e('Open AI Chat','ai-inventory-agent'); ?></a>
  </div>

  <div class="aia-grid">
    <div class="aia-card">
      <div class="aia-card__hd"><?php esc_html_e('Key Metrics','ai-inventory-agent'); ?></div>
      <div class="aia-card__bd">
        <ul>
          <li><?php esc_html_e('Total products','ai-inventory-agent'); ?>: 0</li>
          <li><?php esc_html_e('Low stock','ai-inventory-agent'); ?>: 0</li>
          <li><?php esc_html_e('Out of stock','ai-inventory-agent'); ?>: 0</li>
        </ul>
      </div>
    </div>
  </div>
</div>