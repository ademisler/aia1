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
    <!-- Minimal Chat Header -->
    <div class="aia-chat-header">
        <div class="aia-chat-header-content">
            <div class="aia-chat-title-section">
                <div class="aia-chat-icon-wrapper">
                    <svg class="aia-chat-icon" viewBox="0 0 24 24">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-3 12H7v-2h10v2zm0-3H7V9h10v2zm0-3H7V6h10v2z"/>
                    </svg>
                </div>
                
                <div class="aia-chat-text-content">
                    <h1 class="aia-chat-main-title">
                        <?php esc_html_e('AI Assistant', 'ai-inventory-agent'); ?>
                    </h1>
                    <p class="aia-chat-subtitle">
                        <?php esc_html_e('Get intelligent insights and recommendations for your inventory', 'ai-inventory-agent'); ?>
                    </p>
                </div>
            </div>
            
            <?php if ($is_configured): ?>
            <div class="aia-chat-status-badge">
                <div class="aia-chat-status-dot"></div>
                <span class="aia-chat-status-text"><?php esc_html_e('AI Ready', 'ai-inventory-agent'); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$is_configured): ?>
        <!-- Enhanced Configuration Notice -->
        <div class="aia-chat-config-notice">
            <div class="aia-chat-config-bg">
                <div class="aia-chat-config-pattern"></div>
            </div>
            <div class="aia-chat-config-content">
                <div class="aia-chat-config-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                    </svg>
                </div>
                <div class="aia-chat-config-text">
                    <h3><?php esc_html_e('AI Configuration Required', 'ai-inventory-agent'); ?></h3>
                    <p><?php esc_html_e('Configure your AI provider to unlock intelligent conversations about your inventory. Get personalized insights, recommendations, and instant answers.', 'ai-inventory-agent'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=aia-settings')); ?>" 
                       class="aia-chat-config-btn">
                        <svg class="aia-chat-config-btn-icon" viewBox="0 0 24 24">
                            <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
                        </svg>
                        <?php esc_html_e('Configure AI Settings', 'ai-inventory-agent'); ?>
                    </a>
                </div>
            </div>
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
            
            // Send to AI via AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'aia_chat',
                    message: message,
                    session_id: 'chat_' + Date.now(),
                    nonce: '<?php echo wp_create_nonce('aia_ajax_nonce'); ?>'
                },
                success: function(response) {
                    hideTypingIndicator();
                    console.log('Chat AJAX Response:', response); // Debug log
                    if (response.success && response.data) {
                        var aiMessage = response.data.response || response.data.message || 'AI response received.';
                        addMessageToChat('ai', aiMessage);
                    } else {
                        var errorMsg = '';
                        if (response.data) {
                            errorMsg = response.data.error || response.data.message || 'Unknown error occurred.';
                        } else {
                            errorMsg = '<?php esc_js(__('Sorry, I encountered an error. Please try again.', 'ai-inventory-agent')); ?>';
                        }
                        addMessageToChat('ai', errorMsg);
                        console.error('Chat Error Response:', response);
                    }
                },
                error: function(xhr, status, error) {
                    hideTypingIndicator();
                    console.error('Chat AJAX Error:', error, xhr.responseText);
                    addMessageToChat('ai', '<?php esc_js(__('Connection error. Please check your settings and try again.', 'ai-inventory-agent')); ?>');
                }
            });
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