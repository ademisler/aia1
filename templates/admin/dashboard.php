<?php if(!defined('ABSPATH')) exit; ?>
<div class="wrap">
  <div class="aia-header aia-header--dashboard">
    <div>
      <h1 class="aia-title"><span class="aia-icon" data-lucide="layout-dashboard"></span> <?php esc_html_e('Inventory Dashboard','ai-inventory-agent'); ?></h1>
      <p class="aia-sub"><?php esc_html_e('Overview of key metrics and recent activity.','ai-inventory-agent'); ?></p>
    </div>
    <a href="<?php echo esc_url(admin_url('admin.php?page=aia-chat')); ?>" class="aia-btn aia-btn--primary"><?php esc_html_e('Open AI Chat','ai-inventory-agent'); ?></a>
  </div>

  <div class="aia-grid">
    <div class="aia-card">
      <div class="aia-card__hd"><?php esc_html_e('Low Stock','ai-inventory-agent'); ?></div>
      <div class="aia-card__bd">
        <ul id="aia-low-stock-list" style="margin:0;padding-left:16px;"></ul>
        <p style="margin-top:10px;"><small><?php esc_html_e('Tip: Filter by category to focus on a segment.','ai-inventory-agent'); ?></small></p>
      </div>
    </div>

    <div class="aia-card">
      <div class="aia-card__hd"><?php esc_html_e('Key Metrics','ai-inventory-agent'); ?></div>
      <div class="aia-card__bd">
        <ul>
          <li><?php esc_html_e('Total products','ai-inventory-agent'); ?>: <strong id="aia-metric-total">0</strong></li>
          <li><?php esc_html_e('Low stock','ai-inventory-agent'); ?>: <strong id="aia-metric-low">0</strong></li>
          <li><?php esc_html_e('Out of stock','ai-inventory-agent'); ?>: <strong id="aia-metric-oos">0</strong></li>
        </ul>
      </div>
    </div>

    <div class="aia-card">
      <div class="aia-card__hd"><?php esc_html_e('Inventory Trend (sample)','ai-inventory-agent'); ?></div>
      <div class="aia-card__bd">
        <canvas id="aia-chart" height="120"></canvas>
      </div>
    </div>
  </div>
</div>