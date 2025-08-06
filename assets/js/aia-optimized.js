/**
 * AI Inventory Agent - Optimized JavaScript
 * Modern ES6+ implementation with performance optimizations
 * Version: 2.1.0
 */

(function() {
    'use strict';

    // Global AIA namespace
    window.AIA = window.AIA || {};

    /**
     * Utility functions
     */
    const Utils = {
        /**
         * Debounce function calls
         * @param {Function} func Function to debounce
         * @param {number} wait Wait time in milliseconds
         * @returns {Function} Debounced function
         */
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        /**
         * Throttle function calls
         * @param {Function} func Function to throttle
         * @param {number} limit Time limit in milliseconds
         * @returns {Function} Throttled function
         */
        throttle(func, limit) {
            let inThrottle;
            return function executedFunction(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },

        /**
         * Create DOM element with attributes
         * @param {string} tag HTML tag name
         * @param {Object} attributes Element attributes
         * @param {string} content Inner content
         * @returns {HTMLElement} Created element
         */
        createElement(tag, attributes = {}, content = '') {
            const element = document.createElement(tag);
            
            Object.entries(attributes).forEach(([key, value]) => {
                if (key === 'className') {
                    element.className = value;
                } else if (key === 'dataset') {
                    Object.entries(value).forEach(([dataKey, dataValue]) => {
                        element.dataset[dataKey] = dataValue;
                    });
                } else {
                    element.setAttribute(key, value);
                }
            });
            
            if (content) {
                element.innerHTML = content;
            }
            
            return element;
        },

        /**
         * Make AJAX request with modern fetch API
         * @param {string} url Request URL
         * @param {Object} options Request options
         * @returns {Promise} Request promise
         */
        async request(url, options = {}) {
            const defaults = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-WP-Nonce': window.aia_ajax?.nonce || ''
                },
                credentials: 'same-origin'
            };

            const config = { ...defaults, ...options };

            try {
                const response = await fetch(url, config);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Request failed:', error);
                throw error;
            }
        }
    };

    /**
     * Toast Notification System
     */
    class ToastNotification {
        constructor() {
            this.container = null;
            this.init();
        }

        init() {
            if (!this.container) {
                this.container = Utils.createElement('div', {
                    className: 'aia-toast-container',
                    style: 'position: fixed; top: 20px; right: 20px; z-index: 9999;'
                });
                document.body.appendChild(this.container);
            }
        }

        show(message, type = 'info', duration = 3000) {
            const toast = Utils.createElement('div', {
                className: `aia-toast aia-toast--${type}`,
                style: 'margin-bottom: 10px; opacity: 0; transform: translateX(100%); transition: all 0.3s ease;'
            }, `
                <div class="aia-toast__content">
                    <span class="aia-toast__icon">${this.getIcon(type)}</span>
                    <span class="aia-toast__message">${message}</span>
                    <button class="aia-toast__close" aria-label="Close">&times;</button>
                </div>
            `);

            this.container.appendChild(toast);

            // Animate in
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(0)';
            });

            // Close button handler
            const closeBtn = toast.querySelector('.aia-toast__close');
            closeBtn?.addEventListener('click', () => this.dismiss(toast));

            // Auto dismiss
            if (duration > 0) {
                setTimeout(() => this.dismiss(toast), duration);
            }

            return toast;
        }

        dismiss(toast) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }

        getIcon(type) {
            const icons = {
                info: '💡',
                success: '✅',
                warning: '⚠️',
                error: '❌'
            };
            return icons[type] || icons.info;
        }
    }

    /**
     * Modal Dialog System
     */
    class ModalDialog {
        constructor(options = {}) {
            this.options = {
                title: '',
                content: '',
                size: 'medium',
                closeButton: true,
                backdrop: true,
                keyboard: true,
                onShow: null,
                onHide: null,
                ...options
            };
            
            this.element = null;
            this.isOpen = false;
            this.create();
        }

        create() {
            const modalHTML = `
                <div class="aia-modal__backdrop"></div>
                <div class="aia-modal__dialog aia-modal--${this.options.size}">
                    <div class="aia-modal__content">
                        <div class="aia-modal__header">
                            <h3 class="aia-modal__title">${this.options.title}</h3>
                            ${this.options.closeButton ? '<button class="aia-modal__close" aria-label="Close">&times;</button>' : ''}
                        </div>
                        <div class="aia-modal__body">
                            ${this.options.content}
                        </div>
                    </div>
                </div>
            `;

            this.element = Utils.createElement('div', {
                className: 'aia-modal',
                tabindex: '-1',
                role: 'dialog',
                'aria-hidden': 'true'
            }, modalHTML);

            document.body.appendChild(this.element);
            this.bindEvents();
        }

        bindEvents() {
            // Close button
            const closeBtn = this.element.querySelector('.aia-modal__close');
            closeBtn?.addEventListener('click', () => this.hide());

            // Backdrop click
            if (this.options.backdrop) {
                const backdrop = this.element.querySelector('.aia-modal__backdrop');
                backdrop?.addEventListener('click', () => this.hide());
            }

            // Keyboard events
            if (this.options.keyboard) {
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.isOpen) {
                        this.hide();
                    }
                });
            }
        }

        show() {
            if (this.isOpen) return;

            this.isOpen = true;
            this.element.style.display = 'flex';
            this.element.setAttribute('aria-hidden', 'false');
            
            // Focus management
            const firstFocusable = this.element.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            firstFocusable?.focus();

            // Prevent body scroll
            document.body.style.overflow = 'hidden';

            // Callback
            if (typeof this.options.onShow === 'function') {
                this.options.onShow(this);
            }
        }

        hide() {
            if (!this.isOpen) return;

            this.isOpen = false;
            this.element.style.display = 'none';
            this.element.setAttribute('aria-hidden', 'true');
            
            // Restore body scroll
            document.body.style.overflow = '';

            // Callback
            if (typeof this.options.onHide === 'function') {
                this.options.onHide(this);
            }
        }

        destroy() {
            if (this.element && this.element.parentNode) {
                this.element.parentNode.removeChild(this.element);
            }
        }
    }

    /**
     * Chat System
     */
    class ChatSystem {
        constructor(container) {
            this.container = container;
            this.messages = container.querySelector('.aia-chat-messages');
            this.form = container.querySelector('.aia-chat-form');
            this.input = container.querySelector('textarea');
            this.sendBtn = container.querySelector('.aia-send-button');
            this.sessionId = this.generateSessionId();
            this.isLoading = false;
            
            this.init();
        }

        init() {
            if (!this.container) return;
            
            this.bindEvents();
            this.loadHistory();
        }

        bindEvents() {
            // Form submission
            this.form?.addEventListener('submit', (e) => {
                e.preventDefault();
                this.sendMessage();
            });

            // Send button
            this.sendBtn?.addEventListener('click', (e) => {
                e.preventDefault();
                this.sendMessage();
            });

            // Enter key (without shift)
            this.input?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            // Auto-resize textarea
            this.input?.addEventListener('input', this.autoResizeTextarea.bind(this));
        }

        async sendMessage() {
            const message = this.input?.value?.trim();
            if (!message || this.isLoading) return;

            this.isLoading = true;
            this.input.value = '';
            this.updateSendButton(true);

            // Add user message
            this.addMessage('user', message);

            // Add loading indicator
            const loadingId = this.addLoadingMessage();

            try {
                const formData = new FormData();
                formData.append('action', 'aia_chat');
                formData.append('message', message);
                formData.append('session_id', this.sessionId);
                formData.append('nonce', window.aia_ajax?.nonce || '');

                const response = await Utils.request(window.aia_ajax?.ajax_url || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData
                });

                // Remove loading message
                this.removeMessage(loadingId);

                if (response.success) {
                    this.addMessage('ai', response.data.response);
                    this.sessionId = response.data.session_id || this.sessionId;
                } else {
                    this.addMessage('error', response.data?.message || 'An error occurred');
                }

            } catch (error) {
                this.removeMessage(loadingId);
                this.addMessage('error', 'Failed to send message. Please try again.');
            } finally {
                this.isLoading = false;
                this.updateSendButton(false);
                this.input?.focus();
            }
        }

        addMessage(type, content) {
            const time = new Date().toLocaleTimeString();
            const messageClass = `aia-chat-message aia-chat-message--${type}`;
            
            const messageEl = Utils.createElement('div', {
                className: messageClass,
                dataset: { timestamp: Date.now() }
            }, `
                <div class="aia-chat-message__content">${content}</div>
                <div class="aia-chat-message__time">${time}</div>
            `);

            this.messages?.appendChild(messageEl);
            this.scrollToBottom();
            
            return messageEl.dataset.timestamp;
        }

        addLoadingMessage() {
            const loadingId = 'loading-' + Date.now();
            const loadingEl = Utils.createElement('div', {
                className: 'aia-chat-message aia-chat-message--loading',
                dataset: { messageId: loadingId }
            }, `
                <div class="aia-chat-message__content">
                    <div class="aia-loading-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            `);

            this.messages?.appendChild(loadingEl);
            this.scrollToBottom();
            
            return loadingId;
        }

        removeMessage(messageId) {
            const message = this.messages?.querySelector(`[data-message-id="${messageId}"]`);
            message?.remove();
        }

        updateSendButton(loading) {
            if (!this.sendBtn) return;
            
            this.sendBtn.disabled = loading;
            this.sendBtn.textContent = loading ? 'Sending...' : 'Send';
        }

        autoResizeTextarea() {
            if (!this.input) return;
            
            this.input.style.height = 'auto';
            this.input.style.height = this.input.scrollHeight + 'px';
        }

        scrollToBottom() {
            if (!this.messages) return;
            
            this.messages.scrollTop = this.messages.scrollHeight;
        }

        generateSessionId() {
            return 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }

        async loadHistory() {
            // Implementation for loading chat history
            // This would typically load recent messages from the server
        }
    }

    /**
     * Form Validation
     */
    class FormValidator {
        constructor(form) {
            this.form = form;
            this.errors = new Map();
            this.init();
        }

        init() {
            if (!this.form) return;

            this.form.addEventListener('submit', (e) => {
                if (!this.validate()) {
                    e.preventDefault();
                    this.showErrors();
                }
            });

            // Real-time validation
            const inputs = this.form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', Utils.debounce(() => this.validateField(input), 300));
            });
        }

        validate() {
            this.errors.clear();
            
            const inputs = this.form.querySelectorAll('[required], [data-validate]');
            inputs.forEach(input => this.validateField(input));
            
            return this.errors.size === 0;
        }

        validateField(field) {
            const value = field.value.trim();
            const fieldName = field.name || field.id;
            
            // Clear previous error
            this.errors.delete(fieldName);
            this.clearFieldError(field);

            // Required validation
            if (field.hasAttribute('required') && !value) {
                this.addError(fieldName, 'This field is required');
                this.showFieldError(field, 'This field is required');
                return false;
            }

            // Type-specific validation
            if (value) {
                const type = field.type || field.dataset.validate;
                
                switch (type) {
                    case 'email':
                        if (!this.isValidEmail(value)) {
                            this.addError(fieldName, 'Please enter a valid email address');
                            this.showFieldError(field, 'Please enter a valid email address');
                            return false;
                        }
                        break;
                        
                    case 'url':
                        if (!this.isValidUrl(value)) {
                            this.addError(fieldName, 'Please enter a valid URL');
                            this.showFieldError(field, 'Please enter a valid URL');
                            return false;
                        }
                        break;
                        
                    case 'number':
                        if (!this.isValidNumber(value)) {
                            this.addError(fieldName, 'Please enter a valid number');
                            this.showFieldError(field, 'Please enter a valid number');
                            return false;
                        }
                        break;
                }
            }

            return true;
        }

        addError(field, message) {
            this.errors.set(field, message);
        }

        showFieldError(field, message) {
            field.classList.add('aia-form-field--error');
            
            let errorEl = field.parentNode.querySelector('.aia-form-error');
            if (!errorEl) {
                errorEl = Utils.createElement('div', {
                    className: 'aia-form-error',
                    role: 'alert'
                });
                field.parentNode.appendChild(errorEl);
            }
            
            errorEl.textContent = message;
        }

        clearFieldError(field) {
            field.classList.remove('aia-form-field--error');
            const errorEl = field.parentNode.querySelector('.aia-form-error');
            errorEl?.remove();
        }

        showErrors() {
            const firstErrorField = this.form.querySelector('.aia-form-field--error');
            firstErrorField?.focus();
        }

        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        isValidUrl(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        }

        isValidNumber(value) {
            return !isNaN(value) && !isNaN(parseFloat(value));
        }
    }

    /**
     * Performance Monitor
     */
    class PerformanceMonitor {
        constructor() {
            this.metrics = new Map();
            this.observers = new Map();
        }

        startTimer(name) {
            this.metrics.set(name, performance.now());
        }

        endTimer(name) {
            const startTime = this.metrics.get(name);
            if (startTime) {
                const duration = performance.now() - startTime;
                this.metrics.set(name, duration);
                return duration;
            }
            return 0;
        }

        observeElementLoad(selector, callback) {
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            callback(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                });

                const elements = document.querySelectorAll(selector);
                elements.forEach(el => observer.observe(el));
                
                this.observers.set(selector, observer);
            }
        }

        getMetrics() {
            return Object.fromEntries(this.metrics);
        }
    }

    /**
     * Main AIA Application
     */
    class AIAApplication {
        constructor() {
            this.components = new Map();
            this.toast = new ToastNotification();
            this.performance = new PerformanceMonitor();
            
            this.init();
        }

        init() {
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initializeComponents());
            } else {
                this.initializeComponents();
            }
        }

        initializeComponents() {
            this.performance.startTimer('app_init');

            // Initialize chat systems
            this.initChat();
            
            // Initialize forms
            this.initForms();
            
            // Initialize UI components
            this.initUIComponents();
            
            // Initialize settings
            this.initSettings();

            this.performance.endTimer('app_init');
        }

        initChat() {
            const chatContainers = document.querySelectorAll('.aia-chat-container');
            chatContainers.forEach((container, index) => {
                const chat = new ChatSystem(container);
                this.components.set(`chat_${index}`, chat);
            });
        }

        initForms() {
            const forms = document.querySelectorAll('.aia-form');
            forms.forEach((form, index) => {
                const validator = new FormValidator(form);
                this.components.set(`form_${index}`, validator);
            });
        }

        initUIComponents() {
            // Initialize dropdowns
            this.initDropdowns();
            
            // Initialize tabs
            this.initTabs();
            
            // Initialize tooltips
            this.initTooltips();
        }

        initDropdowns() {
            const dropdowns = document.querySelectorAll('.aia-dropdown-toggle');
            dropdowns.forEach(toggle => {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const dropdown = toggle.closest('.aia-dropdown');
                    const isActive = dropdown.classList.contains('aia-dropdown--active');
                    
                    // Close all dropdowns
                    document.querySelectorAll('.aia-dropdown--active').forEach(d => {
                        d.classList.remove('aia-dropdown--active');
                    });
                    
                    // Toggle current dropdown
                    if (!isActive) {
                        dropdown.classList.add('aia-dropdown--active');
                    }
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', () => {
                document.querySelectorAll('.aia-dropdown--active').forEach(dropdown => {
                    dropdown.classList.remove('aia-dropdown--active');
                });
            });
        }

        initTabs() {
            const tabContainers = document.querySelectorAll('.aia-tabs');
            tabContainers.forEach(container => {
                const tabs = container.querySelectorAll('.aia-tab');
                const contents = container.querySelectorAll('.aia-tab-content');

                tabs.forEach(tab => {
                    tab.addEventListener('click', (e) => {
                        e.preventDefault();
                        
                        const target = tab.dataset.tab;
                        
                        // Update active states
                        tabs.forEach(t => t.classList.remove('aia-tab--active'));
                        contents.forEach(c => c.classList.remove('active'));
                        
                        tab.classList.add('aia-tab--active');
                        const targetContent = container.querySelector(`#${target}`);
                        targetContent?.classList.add('active');
                    });
                });
            });
        }

        initTooltips() {
            const tooltipElements = document.querySelectorAll('[data-tooltip]');
            tooltipElements.forEach(element => {
                let tooltip = null;

                element.addEventListener('mouseenter', () => {
                    const text = element.dataset.tooltip;
                    const position = element.dataset.tooltipPosition || 'top';

                    tooltip = Utils.createElement('div', {
                        className: `aia-tooltip aia-tooltip--${position}`,
                        role: 'tooltip'
                    }, text);

                    document.body.appendChild(tooltip);
                    this.positionTooltip(element, tooltip, position);
                });

                element.addEventListener('mouseleave', () => {
                    if (tooltip) {
                        tooltip.remove();
                        tooltip = null;
                    }
                });
            });
        }

        initSettings() {
            // API key testing
            const testButton = document.getElementById('test_api_connection');
            if (testButton) {
                testButton.addEventListener('click', this.testAPIConnection.bind(this));
            }

            // Settings form auto-save
            const settingsForm = document.getElementById('aia-settings-form');
            if (settingsForm) {
                const autoSave = Utils.debounce(() => {
                    this.autoSaveSettings(settingsForm);
                }, 2000);

                settingsForm.addEventListener('input', autoSave);
            }
        }

        async testAPIConnection() {
            const provider = document.getElementById('ai_provider')?.value;
            const apiKey = document.getElementById('api_key')?.value;
            const resultDiv = document.getElementById('api_test_result');
            const button = document.getElementById('test_api_connection');

            if (!provider || !apiKey) {
                this.toast.show('Please select a provider and enter an API key', 'warning');
                return;
            }

            button.disabled = true;
            button.textContent = 'Testing...';

            try {
                const formData = new FormData();
                formData.append('action', 'aia_test_api_connection');
                formData.append('provider', provider);
                formData.append('api_key', apiKey);
                formData.append('nonce', window.aia_ajax?.nonce || '');

                const response = await Utils.request(window.aia_ajax?.ajax_url, {
                    method: 'POST',
                    body: formData
                });

                if (response.success) {
                    this.toast.show('API connection successful!', 'success');
                    if (resultDiv) {
                        resultDiv.innerHTML = '<span class="aia-text-success">✓ Connection successful</span>';
                    }
                } else {
                    this.toast.show(response.data?.message || 'API connection failed', 'error');
                    if (resultDiv) {
                        resultDiv.innerHTML = '<span class="aia-text-error">✗ Connection failed</span>';
                    }
                }

            } catch (error) {
                this.toast.show('Failed to test API connection', 'error');
                if (resultDiv) {
                    resultDiv.innerHTML = '<span class="aia-text-error">✗ Test failed</span>';
                }
            } finally {
                button.disabled = false;
                button.textContent = 'Test Connection';
            }
        }

        async autoSaveSettings(form) {
            const formData = new FormData(form);
            formData.append('action', 'aia_auto_save_settings');
            formData.append('nonce', window.aia_ajax?.nonce || '');

            try {
                await Utils.request(window.aia_ajax?.ajax_url, {
                    method: 'POST',
                    body: formData
                });

                this.toast.show('Settings auto-saved', 'info', 1500);
            } catch (error) {
                // Silently fail for auto-save
            }
        }

        positionTooltip(element, tooltip, position) {
            const elementRect = element.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();

            let top, left;

            switch (position) {
                case 'top':
                    top = elementRect.top - tooltipRect.height - 8;
                    left = elementRect.left + (elementRect.width - tooltipRect.width) / 2;
                    break;
                case 'bottom':
                    top = elementRect.bottom + 8;
                    left = elementRect.left + (elementRect.width - tooltipRect.width) / 2;
                    break;
                case 'left':
                    top = elementRect.top + (elementRect.height - tooltipRect.height) / 2;
                    left = elementRect.left - tooltipRect.width - 8;
                    break;
                case 'right':
                    top = elementRect.top + (elementRect.height - tooltipRect.height) / 2;
                    left = elementRect.right + 8;
                    break;
                default:
                    top = elementRect.top - tooltipRect.height - 8;
                    left = elementRect.left + (elementRect.width - tooltipRect.width) / 2;
            }

            tooltip.style.position = 'fixed';
            tooltip.style.top = Math.max(8, top) + 'px';
            tooltip.style.left = Math.max(8, Math.min(window.innerWidth - tooltipRect.width - 8, left)) + 'px';
        }

        showNotice(type, message) {
            this.toast.show(message, type);
        }

        getComponent(name) {
            return this.components.get(name);
        }

        getPerformanceMetrics() {
            return this.performance.getMetrics();
        }
    }

    // Initialize application
    window.AIA.app = new AIAApplication();
    
    // Expose utilities
    window.AIA.Utils = Utils;
    window.AIA.ToastNotification = ToastNotification;
    window.AIA.ModalDialog = ModalDialog;

    // Legacy jQuery compatibility (if needed)
    if (window.jQuery) {
        window.jQuery(document).ready(() => {
            // Legacy initialization code can go here
        });
    }

})();