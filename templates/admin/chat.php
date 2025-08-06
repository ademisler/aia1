<?php
/**
 * Admin Chat Template
 * 
 * @package AI_Inventory_Agent
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if AI is configured
$ai_provider = $this->plugin->get_setting('ai_provider');
$api_key = $this->plugin->get_setting('api_key');
$is_configured = !empty($ai_provider) && !empty($api_key);
?>

<div class="wrap aia-chat-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if (!$is_configured): ?>
        <div class="notice notice-warning">
            <p>
                <?php esc_html_e('AI provider is not configured. Please configure your AI settings to use the chat feature.', 'ai-inventory-agent'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ai-inventory-agent-settings')); ?>" class="button button-primary">
                    <?php esc_html_e('Configure Settings', 'ai-inventory-agent'); ?>
                </a>
            </p>
        </div>
    <?php else: ?>
        <!-- Skip Link for Accessibility -->
        <a href="#main-chat-content" class="aia-skip-link"><?php _e('Skip to chat content', 'ai-inventory-agent'); ?></a>
        
        <div class="aia-ai-chat-container" id="main-chat-content">
            <div class="aia-ai-chat-header">
                <div class="aia-ai-avatar">
                    <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bot"></use>
                    </svg>
                </div>
                <div class="aia-ai-info">
                    <h2><?php esc_html_e('AI Inventory Assistant', 'ai-inventory-agent'); ?></h2>
                    <div class="aia-ai-status">
                        <div class="aia-ai-status-indicator"></div>
                        <span><?php esc_html_e('Online & Ready', 'ai-inventory-agent'); ?></span>
                    </div>
                </div>
                <div class="aia-chat-actions">
                    <button class="aia-button aia-button--secondary aia-button--sm aia-clear-chat">
                        <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trash"></use>
                        </svg>
                        <?php esc_html_e('Clear Chat', 'ai-inventory-agent'); ?>
                    </button>
                </div>
            </div>
            
            <div class="aia-ai-chat-messages" id="aia-chat-messages">
                <!-- Welcome Message -->
                <div class="aia-message aia-message--ai">
                    <div class="aia-message-avatar">
                        <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                            <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bot"></use>
                        </svg>
                    </div>
                    <div class="aia-message-content">
                        <p><?php esc_html_e('Welcome! I\'m your AI inventory assistant. How can I help you manage your inventory today?', 'ai-inventory-agent'); ?></p>
                        <div class="aia-confidence-meter">
                            <span class="aia-confidence-label"><?php esc_html_e('Confidence:', 'ai-inventory-agent'); ?></span>
                            <div class="aia-confidence-bar">
                                <div class="aia-confidence-fill aia-confidence-fill--high" style="width: 95%"></div>
                            </div>
                            <span class="aia-confidence-value">95%</span>
                        </div>
                        <div class="aia-message-timestamp"><?php echo current_time('H:i'); ?></div>
                    </div>
                </div>
                
                <!-- AI Insights Card -->
                <div class="aia-insight-card aia-animate-on-scroll">
                    <div class="aia-insight-header">
                        <div class="aia-insight-icon">
                            <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-analytics"></use>
                            </svg>
                        </div>
                        <h3 class="aia-insight-title"><?php esc_html_e('What I Can Help With', 'ai-inventory-agent'); ?></h3>
                    </div>
                    <div class="aia-insight-content">
                        <ul>
                            <li><?php esc_html_e('Current stock levels and inventory status', 'ai-inventory-agent'); ?></li>
                            <li><?php esc_html_e('Product recommendations and reorder suggestions', 'ai-inventory-agent'); ?></li>
                            <li><?php esc_html_e('Sales trends and demand forecasting', 'ai-inventory-agent'); ?></li>
                            <li><?php esc_html_e('Supplier performance and risk analysis', 'ai-inventory-agent'); ?></li>
                            <li><?php esc_html_e('Inventory optimization strategies', 'ai-inventory-agent'); ?></li>
                        </ul>
                    </div>
                    <div class="aia-insight-actions">
                        <button class="aia-button aia-button--primary aia-button--sm" onclick="AIA.Chat.sendSuggestion('Show current stock levels')">
                            <?php esc_html_e('Check Stock Levels', 'ai-inventory-agent'); ?>
                        </button>
                        <button class="aia-button aia-button--secondary aia-button--sm" onclick="AIA.Chat.sendSuggestion('Analyze inventory trends')">
                            <?php esc_html_e('Analyze Trends', 'ai-inventory-agent'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- AI Suggestions -->
            <div class="aia-ai-suggestions" id="aia-suggestions">
                <div class="aia-suggestion-chip" onclick="AIA.Chat.sendSuggestion('What products need reordering?')">
                    <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert"></use>
                    </svg>
                    <?php esc_html_e('Reorder Alerts', 'ai-inventory-agent'); ?>
                </div>
                <div class="aia-suggestion-chip" onclick="AIA.Chat.sendSuggestion('Show top selling products')">
                    <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trending-up"></use>
                    </svg>
                    <?php esc_html_e('Top Sellers', 'ai-inventory-agent'); ?>
                </div>
                <div class="aia-suggestion-chip" onclick="AIA.Chat.sendSuggestion('Inventory optimization tips')">
                    <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-settings"></use>
                    </svg>
                    <?php esc_html_e('Optimization Tips', 'ai-inventory-agent'); ?>
                </div>
            </div>
            
            <form class="aia-chat-form" id="aia-chat-form">
                <div class="aia-chat-input-wrapper">
                    <textarea 
                        name="message" 
                        id="aia-chat-input" 
                        class="aia-chat-input auto-resize" 
                        placeholder="<?php esc_attr_e('Type your message here...', 'ai-inventory-agent'); ?>"
                        rows="1"
                    ></textarea>
                    <button type="submit" class="button button-primary aia-chat-send" id="aia-chat-send">
                        <span class="dashicons dashicons-arrow-right-alt"></span>
                        <span class="screen-reader-text"><?php esc_html_e('Send', 'ai-inventory-agent'); ?></span>
                    </button>
                </div>
            </form>
            
            <div class="aia-chat-footer">
                <p class="description">
                    <?php printf(
                        esc_html__('Powered by %s | Press Enter to send, Shift+Enter for new line', 'ai-inventory-agent'),
                        esc_html(ucfirst($ai_provider))
                    ); ?>
                </p>
            </div>
        </div>
        
        <!-- Sidebar with quick info -->
        <div class="aia-chat-sidebar">
            <div class="aia-sidebar-widget">
                <h3><?php esc_html_e('Quick Stats', 'ai-inventory-agent'); ?></h3>
                <div id="aia-quick-stats">
                    <div class="aia-loading">
                        <span class="spinner is-active"></span>
                    </div>
                </div>
            </div>
            
            <div class="aia-sidebar-widget">
                <h3><?php esc_html_e('Suggested Questions', 'ai-inventory-agent'); ?></h3>
                <ul class="aia-suggested-questions">
                    <li><a href="#" class="aia-suggestion" data-message="What products are running low on stock?"><?php esc_html_e('What products are running low on stock?', 'ai-inventory-agent'); ?></a></li>
                    <li><a href="#" class="aia-suggestion" data-message="Show me the top selling products this month"><?php esc_html_e('Show me the top selling products this month', 'ai-inventory-agent'); ?></a></li>
                    <li><a href="#" class="aia-suggestion" data-message="Which suppliers have the best performance?"><?php esc_html_e('Which suppliers have the best performance?', 'ai-inventory-agent'); ?></a></li>
                    <li><a href="#" class="aia-suggestion" data-message="What products should I reorder soon?"><?php esc_html_e('What products should I reorder soon?', 'ai-inventory-agent'); ?></a></li>
                    <li><a href="#" class="aia-suggestion" data-message="Analyze my inventory turnover rate"><?php esc_html_e('Analyze my inventory turnover rate', 'ai-inventory-agent'); ?></a></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Load quick stats
    if ($('#aia-quick-stats').length) {
        $.ajax({
            url: aia_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aia_get_inventory_data',
                nonce: aia_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var html = '<ul>';
                    html += '<li><strong>' + data.counts.total_products + '</strong> ' + '<?php esc_html_e('Total Products', 'ai-inventory-agent'); ?>' + '</li>';
                    html += '<li><strong>' + data.counts.low_stock + '</strong> ' + '<?php esc_html_e('Low Stock', 'ai-inventory-agent'); ?>' + '</li>';
                    html += '<li><strong>' + data.counts.out_of_stock + '</strong> ' + '<?php esc_html_e('Out of Stock', 'ai-inventory-agent'); ?>' + '</li>';
                    html += '</ul>';
                    $('#aia-quick-stats').html(html);
                }
            }
        });
    }
    
    // Handle suggested questions
    $('.aia-suggestion').on('click', function(e) {
        e.preventDefault();
        var message = $(this).data('message');
        $('#aia-chat-input').val(message).focus();
    });
});
</script>