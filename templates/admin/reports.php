<?php if(!defined('ABSPATH')) exit; ?>
<div class="wrap">
  <div class="aia-header aia-header--reports">
    <div>
      <h1 class="aia-title"><span class="aia-icon" data-lucide="bar-chart-3"></span> <?php esc_html_e('Reports','ai-inventory-agent'); ?></h1>
      <p class="aia-sub"><?php esc_html_e('Generate and download inventory reports.','ai-inventory-agent'); ?></p>
    </div>
  </div>

  <div class="aia-card">
    <div class="aia-card__hd"><?php esc_html_e('Exports','ai-inventory-agent'); ?></div>
    <div class="aia-card__bd">
      <p>
        <a class="aia-btn aia-btn--primary" href="<?php echo esc_url( rest_url('aia/v1/reports/lowstock.csv') ); ?>"><?php esc_html_e('Download Low Stock CSV','ai-inventory-agent'); ?></a>
        <a class="aia-btn" href="<?php echo esc_url( rest_url('aia/v1/reports/summary.json') ); ?>"><?php esc_html_e('View Summary JSON','ai-inventory-agent'); ?></a>
      </p>
    </div>
  </div>
</div>