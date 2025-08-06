/**
 * AI Inventory Agent - Admin JavaScript
 */

(function($) {
    'use strict';

    // Global AIA object
    window.AIA = window.AIA || {};

    // Initialize when document is ready
    jQuery(document).ready(function($) {
        AIA.init();
    });

    /**
     * Main AIA object
     */
    AIA = {
        
        /**
         * Initialize the admin interface
         */
        init: function() {
            this.bindEvents();
            this.initChat();
            this.initDashboard();
            this.initSettings();
            this.initNotices();
        },

        /**
         * Bind global events
         */
        bindEvents: function() {
            // Handle AJAX errors globally
            $(document).ajaxError(function(event, xhr, settings, error) {
                if (xhr.status === 403) {
                    AIA.showNotice('error', aia_ajax.strings.error + ' (403: Forbidden)');
                } else if (xhr.status === 500) {
                    AIA.showNotice('error', aia_ajax.strings.error + ' (500: Server Error)');
                }
            });

            // Auto-resize textareas
            $('textarea.auto-resize').on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        },

        /**
         * Initialize chat functionality
         */
        initChat: function() {
            if (!$('.aia-chat-container').length) return;

            this.chat = {
                container: $('.aia-chat-container'),
                messages: $('.aia-chat-messages'),
                form: $('.aia-chat-form'),
                input: $('.aia-chat-form textarea'),
                sendBtn: $('.aia-send-button'),
                sessionId: this.generateSessionId(),
                isLoading: false
            };

            this.bindChatEvents();
            this.loadChatHistory();
        },

        /**
         * Bind chat events
         */
        bindChatEvents: function() {
            var self = this;

            // Send message on form submit
            this.chat.form.on('submit', function(e) {
                e.preventDefault();
                self.sendMessage();
            });

            // Send message on button click
            this.chat.sendBtn.on('click', function(e) {
                e.preventDefault();
                self.sendMessage();
            });

            // Send message on Enter (but not Shift+Enter)
            this.chat.input.on('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    self.sendMessage();
                }
            });

            // Auto-resize chat input
            this.chat.input.on('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });

            // Clear chat button
            $('.aia-chat-clear').on('click', function() {
                self.clearChat();
            });
        },

        /**
         * Send chat message
         */
        sendMessage: function() {
            if (this.chat.isLoading) return;

            var message = this.chat.input.val().trim();
            if (!message) return;

            this.chat.isLoading = true;
            this.chat.sendBtn.prop('disabled', true);

            // Add user message to chat
            this.addChatMessage('user', message);
            this.chat.input.val('');
            this.chat.input[0].style.height = 'auto';

            // Show loading indicator
            var loadingId = this.addLoadingMessage();

            // Send AJAX request
            $.ajax({
                url: aia_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'aia_chat',
                    nonce: aia_ajax.nonce,
                    message: message,
                    session_id: this.chat.sessionId
                },
                success: function(response) {
                    console.log('Chat Response:', response);
                    if (response.success) {
                        // Remove loading message and add AI response
                        $('#' + loadingId).remove();
                        self.addChatMessage('assistant', response.data.response);
                        
                        // Update session ID if provided
                        if (response.data.session_id) {
                            self.chat.sessionId = response.data.session_id;
                        }
                    } else {
                        $('#' + loadingId).remove();
                        var errorMsg = response.data;
                        if (typeof response.data === 'object') {
                            errorMsg = response.data.message || JSON.stringify(response.data);
                        }
                        console.error('Chat Error:', response.data);
                        self.addChatMessage('assistant', 'Error: ' + errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    $('#' + loadingId).remove();
                    console.error('Chat AJAX Error:', status, error, xhr.responseText);
                    self.addChatMessage('assistant', aia_ajax.strings.error + ' (' + status + ')');
                },
                complete: function() {
                    self.chat.isLoading = false;
                    self.chat.sendBtn.prop('disabled', false);
                    self.chat.input.focus();
                }
            });
        },

        /**
         * Add message to chat
         */
        addChatMessage: function(type, content) {
            var time = new Date().toLocaleTimeString();
            var messageClass = type === 'user' ? 'aia-message aia-message--user' : 'aia-message aia-message--ai';
            
            var messageHtml = `
                <div class="${messageClass}">
                    <div class="aia-message-avatar">
                        <svg class="aia-icon aia-icon--md" aria-hidden="true">
                            <use href="${aia_ajax.plugin_url}assets/icons/sprite.svg#${type === 'user' ? 'aia-user' : 'aia-bot'}"></use>
                        </svg>
                    </div>
                    <div class="aia-message-bubble ${type === 'user' ? 'aia-message-bubble--user' : ''}">
                        <div class="aia-message-content">${this.formatMessage(content)}</div>
                        <div class="aia-message-meta">
                            <span class="aia-message-time">${time}</span>
                        </div>
                    </div>
                </div>
            `;

            this.chat.messages.append(messageHtml);
            this.scrollChatToBottom();
        },

        /**
         * Add loading message
         */
        addLoadingMessage: function() {
            var loadingId = 'loading-' + Date.now();
            var loadingHtml = `
                <div class="aia-message aia-message--ai" id="${loadingId}">
                    <div class="aia-message-avatar">
                        <svg class="aia-icon aia-icon--md" aria-hidden="true">
                            <use href="${aia_ajax.plugin_url}assets/icons/sprite.svg#aia-bot"></use>
                        </svg>
                    </div>
                    <div class="aia-message-bubble">
                        <div class="aia-message-content">
                            <div class="aia-typing-indicator">
                                <div class="aia-typing-dot"></div>
                                <div class="aia-typing-dot"></div>
                                <div class="aia-typing-dot"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            this.chat.messages.append(loadingHtml);
            this.scrollChatToBottom();
            
            return loadingId;
        },

        /**
         * Format message content
         */
        formatMessage: function(content) {
            // Ensure content is a string
            if (typeof content !== 'string') {
                content = String(content || '');
            }
            
            // Convert line breaks to <br>
            content = content.replace(/\n/g, '<br>');
            
            // Simple markdown-like formatting
            content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            content = content.replace(/\*(.*?)\*/g, '<em>$1</em>');
            content = content.replace(/`(.*?)`/g, '<code>$1</code>');
            
            return content;
        },

        /**
         * Scroll chat to bottom
         */
        scrollChatToBottom: function() {
            this.chat.messages.scrollTop(this.chat.messages[0].scrollHeight);
        },

        /**
         * Load chat history
         */
        loadChatHistory: function() {
            // Implementation for loading chat history
            // This would typically load recent messages from the database
        },

        /**
         * Clear chat
         */
        clearChat: function() {
            if (!confirm('Are you sure you want to clear the chat history?')) {
                return;
            }

            $.ajax({
                url: aia_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'aia_clear_chat_history',
                    nonce: aia_ajax.nonce,
                    session_id: this.chat.sessionId
                },
                success: function(response) {
                    if (response.success) {
                        AIA.chat.messages.empty();
                        AIA.showNotice('success', 'Chat history cleared.');
                    } else {
                        AIA.showNotice('error', response.data);
                    }
                }
            });
        },

        /**
         * Generate session ID
         */
        generateSessionId: function() {
            return 'aia_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        },

        /**
         * Initialize dashboard
         */
        initDashboard: function() {
            if (!$('.aia-dashboard-page').length) return;

            this.loadDashboardData();
            this.initDashboardRefresh();
        },

        /**
         * Load dashboard data
         */
        loadDashboardData: function() {
            $.ajax({
                url: aia_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'aia_get_inventory_summary',
                    nonce: aia_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AIA.updateDashboardStats(response.data);
                    }
                }
            });
        },

        /**
         * Update dashboard statistics
         */
        updateDashboardStats: function(data) {
            // Update stat numbers
            $('.aia-stat[data-stat="total_products"] .aia-stat-number').text(data.counts.total_products || 0);
            $('.aia-stat[data-stat="low_stock"] .aia-stat-number').text(data.counts.low_stock || 0);
            $('.aia-stat[data-stat="out_of_stock"] .aia-stat-number').text(data.counts.out_of_stock || 0);
            $('.aia-stat[data-stat="in_stock"] .aia-stat-number').text(data.counts.in_stock || 0);

            // Update stat colors based on values
            var lowStockStat = $('.aia-stat[data-stat="low_stock"]');
            var outOfStockStat = $('.aia-stat[data-stat="out_of_stock"]');

            if (data.counts.low_stock > 0) {
                lowStockStat.addClass('warning');
            }

            if (data.counts.out_of_stock > 0) {
                outOfStockStat.addClass('danger');
            }
        },

        /**
         * Initialize dashboard refresh
         */
        initDashboardRefresh: function() {
            var self = this;

            // Refresh button
            $('.aia-refresh-dashboard').on('click', function(e) {
                e.preventDefault();
                $(this).prop('disabled', true).text('Refreshing...');
                
                self.loadDashboardData();
                
                setTimeout(function() {
                    $('.aia-refresh-dashboard').prop('disabled', false).text('Refresh');
                }, 2000);
            });

            // Auto-refresh every 5 minutes
            setInterval(function() {
                self.loadDashboardData();
            }, 300000);
        },

        /**
         * Initialize settings
         */
        initSettings: function() {
            if (!$('.aia-settings-page').length) return;

            this.bindSettingsEvents();
        },

        /**
         * Bind settings events
         */
        bindSettingsEvents: function() {
            var self = this;

            // Test API connection
            $('#test_api_connection').on('click', function() {
                self.testApiConnection();
            });

            // Save settings via AJAX
            $('.aia-settings-form').on('submit', function(e) {
                // Let the form submit normally for now
                // Could be enhanced with AJAX saving later
            });

            // Provider change handler
            $('#ai_provider').on('change', function() {
                var provider = $(this).val();
                self.updateProviderInstructions(provider);
            });
        },

        /**
         * Test API connection
         */
        testApiConnection: function() {
            var provider = $('#ai_provider').val();
            var apiKey = $('#api_key').val();
            var resultDiv = $('#api_test_result');
            var button = $('#test_api_connection');

            if (!apiKey) {
                resultDiv.html('<div class="aia-api-test-result error">Please enter an API key first.</div>');
                return;
            }

            button.prop('disabled', true).text('Testing...');
            resultDiv.html('<div class="aia-api-test-result">Testing connection...</div>');

            $.ajax({
                url: aia_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'aia_test_api_connection',
                    nonce: aia_ajax.nonce,
                    provider: provider,
                    api_key: apiKey
                },
                success: function(response) {
                    if (response.success) {
                        resultDiv.html('<div class="aia-api-test-result success">✓ ' + response.data.message + '</div>');
                    } else {
                        resultDiv.html('<div class="aia-api-test-result error">✗ ' + response.data + '</div>');
                    }
                },
                error: function() {
                    resultDiv.html('<div class="aia-api-test-result error">✗ Connection test failed</div>');
                },
                complete: function() {
                    button.prop('disabled', false).text('Test Connection');
                }
            });
        },

        /**
         * Update provider instructions
         */
        updateProviderInstructions: function(provider) {
            var instructions = {
                'openai': 'Enter your OpenAI API key. You can get one from the OpenAI dashboard.',
                'gemini': 'Enter your Google AI API key. You can get one from the Google AI Studio.'
            };

            var description = instructions[provider] || 'Enter your API key for the selected provider.';
            $('#api_key').siblings('.description').text(description);
        },

        /**
         * Initialize notices
         */
        initNotices: function() {
            // Handle dismissible notices
            $(document).on('click', '.notice-dismiss', function() {
                var notice = $(this).closest('.notice');
                var noticeType = notice.data('notice');
                
                if (noticeType) {
                    $.ajax({
                        url: aia_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'aia_dismiss_notice',
                            nonce: aia_ajax.nonce,
                            notice: noticeType
                        }
                    });
                }
            });
        },

        /**
         * Show notice
         */
        showNotice: function(type, message) {
            var noticeClass = 'notice-' + type;
            var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
            
            $('.wrap h1').after(notice);
            
            // Auto-remove after 5 seconds
            setTimeout(function() {
                notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Utility functions
         */
        utils: {
            /**
             * Format number with thousands separator
             */
            formatNumber: function(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            },

            /**
             * Format currency
             */
            formatCurrency: function(amount, currency) {
                currency = currency || '$';
                return currency + this.formatNumber(amount.toFixed(2));
            },

            /**
             * Format date
             */
            formatDate: function(date) {
                return new Date(date).toLocaleDateString();
            },

            /**
             * Debounce function
             */
            debounce: function(func, wait) {
                var timeout;
                return function executedFunction() {
                    var context = this;
                    var args = arguments;
                    var later = function() {
                        timeout = null;
                        func.apply(context, args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
        }
    };

})(jQuery);