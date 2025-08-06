<?php
/**
 * Admin Chat Template - Light Theme
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

// Get inventory summary for sidebar
$inventory_analysis = $this->plugin->get_module_manager()->get_module('inventory_analysis');
$summary = $inventory_analysis ? $inventory_analysis->get_inventory_summary() : [];
?>

<div class="wrap aia-chat-light">
    <!-- Standardized Page Header -->
    <div class="aia-page-header">
        <div class="aia-page-header-content">
            <h1 class="aia-page-title">
                <svg class="aia-icon" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-chat"></use>
                </svg>
                <?php esc_html_e('AI Assistant', 'ai-inventory-agent'); ?>
            </h1>
            <p class="aia-page-subtitle">
                <?php esc_html_e('Get intelligent insights and recommendations for your inventory', 'ai-inventory-agent'); ?>
            </p>
        </div>
        
        <?php if ($is_configured): ?>
        <div class="aia-page-header-actions">
            <div class="aia-chat-status-badge">
                <div class="aia-status-indicator aia-status-indicator--online"></div>
                <span class="aia-status-text"><?php esc_html_e('AI Ready', 'ai-inventory-agent'); ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!$is_configured): ?>
        <!-- Configuration Notice -->
        <div class="aia-config-notice">
            <div class="aia-notice-content">
                <div class="aia-notice-icon">
                    <svg class="aia-icon aia-icon--lg" aria-hidden="true">
                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-settings"></use>
                    </svg>
                </div>
                <div class="aia-notice-text">
                    <h3><?php esc_html_e('AI Configuration Required', 'ai-inventory-agent'); ?></h3>
                    <p><?php esc_html_e('Please configure your AI provider settings to start using the intelligent assistant.', 'ai-inventory-agent'); ?></p>
                </div>
            </div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=aia-settings')); ?>" 
               class="aia-btn aia-btn--primary">
                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                    <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-arrow-right"></use>
                </svg>
                <?php esc_html_e('Configure Settings', 'ai-inventory-agent'); ?>
            </a>
        </div>
    <?php else: ?>
        <!-- Chat Interface -->
        <div class="aia-chat-container">
            <!-- Main Chat Area -->
            <div class="aia-chat-main">
                <!-- Chat Header -->
                <div class="aia-chat-header">
                    <div class="aia-chat-header-content">
                        <div class="aia-ai-avatar">
                            <svg class="aia-icon aia-icon--xl" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bot"></use>
                            </svg>
                        </div>
                        <div class="aia-ai-info">
                            <h2 class="aia-ai-name"><?php esc_html_e('AI Inventory Assistant', 'ai-inventory-agent'); ?></h2>
                            <div class="aia-ai-status">
                                <div class="aia-status-dot"></div>
                                <span><?php esc_html_e('Online & Ready to Help', 'ai-inventory-agent'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="aia-chat-header-actions">
                        <button class="aia-btn aia-btn--light aia-btn--sm aia-clear-chat" 
                                title="<?php esc_attr_e('Clear conversation', 'ai-inventory-agent'); ?>">
                            <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trash"></use>
                            </svg>
                            <?php esc_html_e('Clear Chat', 'ai-inventory-agent'); ?>
                        </button>
                    </div>
                </div>

                <!-- Chat Messages -->
                <div class="aia-chat-messages" id="aia-chat-messages">
                    <!-- Welcome Message -->
                    <div class="aia-message aia-message--ai">
                        <div class="aia-message-avatar">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bot"></use>
                            </svg>
                        </div>
                        <div class="aia-message-bubble">
                            <div class="aia-message-content">
                                <p><?php esc_html_e('Welcome! I\'m your AI inventory assistant. I can help you with stock analysis, product recommendations, supplier insights, and much more.', 'ai-inventory-agent'); ?></p>
                                <p><?php esc_html_e('What would you like to know about your inventory today?', 'ai-inventory-agent'); ?></p>
                            </div>
                            <div class="aia-message-meta">
                                <span class="aia-message-time"><?php echo current_time('H:i'); ?></span>
                            </div>
                        </div>
                    </div>


                </div>



                <!-- Chat Input -->
                <div class="aia-chat-input-section">
                    <form class="aia-chat-form" id="aia-chat-form">
                        <div class="aia-input-wrapper">
                            <div class="aia-input-container">
                                <textarea 
                                    name="message" 
                                    id="aia-chat-input" 
                                    class="aia-chat-input" 
                                    placeholder="<?php esc_attr_e('Ask me anything about your inventory...', 'ai-inventory-agent'); ?>"
                                    rows="1"
                                ></textarea>
                                <button type="submit" class="aia-send-button" id="aia-chat-send">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-arrow-right"></use>
                                    </svg>
                                    <span class="aia-sr-only"><?php esc_html_e('Send message', 'ai-inventory-agent'); ?></span>
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="aia-chat-sidebar">
                <!-- Quick Stats Widget -->
                <div class="aia-sidebar-widget">
                    <div class="aia-widget-header">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bar-chart"></use>
                            </svg>
                            <?php esc_html_e('Inventory Overview', 'ai-inventory-agent'); ?>
                        </h3>
                    </div>
                    <div class="aia-widget-content">
                        <div class="aia-stats-grid" id="aia-quick-stats">
                            <div class="aia-stat-item">
                                <div class="aia-stat-icon aia-stat-icon--primary">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-inventory"></use>
                                    </svg>
                                </div>
                                <div class="aia-stat-content">
                                    <div class="aia-stat-number"><?php echo esc_html(number_format($summary['counts']['total_products'] ?? 0)); ?></div>
                                    <div class="aia-stat-label"><?php esc_html_e('Total Products', 'ai-inventory-agent'); ?></div>
                                </div>
                            </div>
                            <div class="aia-stat-item">
                                <div class="aia-stat-icon aia-stat-icon--warning">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                                    </svg>
                                </div>
                                <div class="aia-stat-content">
                                    <div class="aia-stat-number"><?php echo esc_html(number_format($summary['counts']['low_stock'] ?? 0)); ?></div>
                                    <div class="aia-stat-label"><?php esc_html_e('Low Stock', 'ai-inventory-agent'); ?></div>
                                </div>
                            </div>
                            <div class="aia-stat-item">
                                <div class="aia-stat-icon aia-stat-icon--danger">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-x-circle"></use>
                                    </svg>
                                </div>
                                <div class="aia-stat-content">
                                    <div class="aia-stat-number"><?php echo esc_html(number_format($summary['counts']['out_of_stock'] ?? 0)); ?></div>
                                    <div class="aia-stat-label"><?php esc_html_e('Out of Stock', 'ai-inventory-agent'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Suggested Questions Widget -->
                <div class="aia-sidebar-widget">
                    <div class="aia-widget-header">
                        <h3 class="aia-widget-title">
                            <svg class="aia-icon aia-icon--md" aria-hidden="true">
                                <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-help-circle"></use>
                            </svg>
                            <?php esc_html_e('Popular Questions', 'ai-inventory-agent'); ?>
                        </h3>
                    </div>
                    <div class="aia-widget-content">
                        <div class="aia-questions-list">
                            <button class="aia-question-item" data-message="What products are running low on stock?">
                                <div class="aia-question-icon">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-alert-triangle"></use>
                                    </svg>
                                </div>
                                <div class="aia-question-text">
                                    <?php esc_html_e('What products are running low on stock?', 'ai-inventory-agent'); ?>
                                </div>
                            </button>
                            <button class="aia-question-item" data-message="Show me the top selling products this month">
                                <div class="aia-question-icon">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-trending-up"></use>
                                    </svg>
                                </div>
                                <div class="aia-question-text">
                                    <?php esc_html_e('Show me the top selling products this month', 'ai-inventory-agent'); ?>
                                </div>
                            </button>
                            <button class="aia-question-item" data-message="Which suppliers have the best performance?">
                                <div class="aia-question-icon">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-users"></use>
                                    </svg>
                                </div>
                                <div class="aia-question-text">
                                    <?php esc_html_e('Which suppliers have the best performance?', 'ai-inventory-agent'); ?>
                                </div>
                            </button>
                            <button class="aia-question-item" data-message="What products should I reorder soon?">
                                <div class="aia-question-icon">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-refresh"></use>
                                    </svg>
                                </div>
                                <div class="aia-question-text">
                                    <?php esc_html_e('What products should I reorder soon?', 'ai-inventory-agent'); ?>
                                </div>
                            </button>
                            <button class="aia-question-item" data-message="Analyze my inventory turnover rate">
                                <div class="aia-question-icon">
                                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                        <use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-analytics"></use>
                                    </svg>
                                </div>
                                <div class="aia-question-text">
                                    <?php esc_html_e('Analyze my inventory turnover rate', 'ai-inventory-agent'); ?>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Handle suggestion clicks
    $('.aia-suggestion-chip, .aia-question-item').on('click', function(e) {
        e.preventDefault();
        var message = $(this).data('message');
        if (message) {
            $('#aia-chat-input').val(message).focus();
        }
    });
    
    // Auto-resize textarea
    $('#aia-chat-input').on('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
    
    // Handle form submission
    $('#aia-chat-form').on('submit', function(e) {
        e.preventDefault();
        var message = $('#aia-chat-input').val().trim();
        if (message) {
            // Add user message to chat
            addMessageToChat('user', message);
            $('#aia-chat-input').val('').css('height', 'auto');
            
            // Show typing indicator
            showTypingIndicator();
            
            // Send to AI (placeholder - implement actual AI integration)
            setTimeout(function() {
                hideTypingIndicator();
                addMessageToChat('ai', 'Thank you for your message. AI integration is in development.');
            }, 2000);
        }
    });
    
    // Clear chat functionality
    $('.aia-clear-chat').on('click', function() {
        if (confirm('<?php esc_js(__('Are you sure you want to clear the chat history?', 'ai-inventory-agent')); ?>')) {
            $('#aia-chat-messages').find('.aia-message:not(:first-child)').remove();
            $('#aia-chat-messages').find('.aia-capabilities-card').show();
        }
    });
    
    function addMessageToChat(type, content) {
        var messageHtml = '<div class="aia-message aia-message--' + type + '">';
        
        if (type === 'user') {
            messageHtml += '<div class="aia-message-bubble aia-message-bubble--user">';
            messageHtml += '<div class="aia-message-content">' + content + '</div>';
            messageHtml += '<div class="aia-message-meta">';
            messageHtml += '<span class="aia-message-time">' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + '</span>';
            messageHtml += '</div>';
            messageHtml += '</div>';
        } else {
            messageHtml += '<div class="aia-message-avatar">';
            messageHtml += '<svg class="aia-icon aia-icon--md" aria-hidden="true">';
            messageHtml += '<use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bot"></use>';
            messageHtml += '</svg>';
            messageHtml += '</div>';
            messageHtml += '<div class="aia-message-bubble">';
            messageHtml += '<div class="aia-message-content">' + content + '</div>';
            messageHtml += '<div class="aia-message-meta">';
            messageHtml += '<span class="aia-message-time">' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + '</span>';
            messageHtml += '</div>';
            messageHtml += '</div>';
        }
        
        messageHtml += '</div>';
        
        $('#aia-chat-messages').append(messageHtml);
        $('#aia-chat-messages').scrollTop($('#aia-chat-messages')[0].scrollHeight);
        
        // Hide capabilities card after first user message
        if (type === 'user') {
            $('.aia-capabilities-card').fadeOut();
        }
    }
    
    function showTypingIndicator() {
        var typingHtml = '<div class="aia-message aia-message--ai aia-typing-indicator">';
        typingHtml += '<div class="aia-message-avatar">';
        typingHtml += '<svg class="aia-icon aia-icon--md" aria-hidden="true">';
        typingHtml += '<use href="<?php echo AIA_PLUGIN_URL; ?>assets/icons/sprite.svg#aia-bot"></use>';
        typingHtml += '</svg>';
        typingHtml += '</div>';
        typingHtml += '<div class="aia-message-bubble">';
        typingHtml += '<div class="aia-typing-dots">';
        typingHtml += '<span></span><span></span><span></span>';
        typingHtml += '</div>';
        typingHtml += '</div>';
        typingHtml += '</div>';
        
        $('#aia-chat-messages').append(typingHtml);
        $('#aia-chat-messages').scrollTop($('#aia-chat-messages')[0].scrollHeight);
    }
    
    function hideTypingIndicator() {
        $('.aia-typing-indicator').remove();
    }
});
</script>