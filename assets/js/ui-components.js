/**
 * AI Inventory Agent - UI Components
 * Modern, reusable UI component behaviors
 */

(function($) {
    'use strict';

    // Component namespace
    window.AIA = window.AIA || {};
    window.AIA.UI = window.AIA.UI || {};

    /**
     * Toast Notification Component
     */
    AIA.UI.Toast = {
        container: null,
        
        init: function() {
            if (!this.container) {
                this.container = $('<div class="aia-toast-container"></div>').appendTo('body');
            }
        },
        
        show: function(message, type = 'info', duration = 3000) {
            this.init();
            
            const toast = $(`
                <div class="aia-toast aia-toast-${type}">
                    <div class="aia-toast-icon">
                        ${this.getIcon(type)}
                    </div>
                    <div class="aia-toast-message">${message}</div>
                    <button class="aia-toast-close">&times;</button>
                </div>
            `);
            
            toast.appendTo(this.container).hide().fadeIn(300);
            
            // Auto dismiss
            if (duration > 0) {
                setTimeout(() => {
                    this.dismiss(toast);
                }, duration);
            }
            
            // Close button
            toast.find('.aia-toast-close').on('click', () => {
                this.dismiss(toast);
            });
            
            return toast;
        },
        
        dismiss: function(toast) {
            toast.fadeOut(300, function() {
                $(this).remove();
            });
        },
        
        getIcon: function(type) {
            const icons = {
                'info': '💡',
                'success': '✅',
                'warning': '⚠️',
                'error': '❌'
            };
            return icons[type] || icons.info;
        }
    };

    /**
     * Modal Component
     */
    AIA.UI.Modal = {
        create: function(options = {}) {
            const defaults = {
                title: '',
                content: '',
                size: 'medium', // small, medium, large
                closeButton: true,
                backdrop: true,
                keyboard: true,
                onShow: null,
                onHide: null
            };
            
            const settings = $.extend({}, defaults, options);
            
            const modal = $(`
                <div class="aia-modal" tabindex="-1">
                    <div class="aia-modal-backdrop"></div>
                    <div class="aia-modal-dialog aia-modal-${settings.size}">
                        <div class="aia-modal-content">
                            <div class="aia-modal-header">
                                <h3 class="aia-modal-title">${settings.title}</h3>
                                ${settings.closeButton ? '<button class="aia-modal-close">&times;</button>' : ''}
                            </div>
                            <div class="aia-modal-body">
                                ${settings.content}
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            // Event handlers
            modal.find('.aia-modal-close, .aia-modal-backdrop').on('click', function() {
                modal.hide();
                if (settings.onHide) settings.onHide(modal);
            });
            
            if (settings.keyboard) {
                $(document).on('keydown.modal', function(e) {
                    if (e.key === 'Escape') {
                        modal.hide();
                        if (settings.onHide) settings.onHide(modal);
                    }
                });
            }
            
            modal.show = function() {
                $('body').append(modal);
                modal.fadeIn(200);
                if (settings.onShow) settings.onShow(modal);
                return modal;
            };
            
            modal.hide = function() {
                modal.fadeOut(200, function() {
                    modal.remove();
                    $(document).off('keydown.modal');
                });
                return modal;
            };
            
            return modal;
        }
    };

    /**
     * Progress Bar Component
     */
    AIA.UI.Progress = {
        create: function(container, options = {}) {
            const defaults = {
                value: 0,
                max: 100,
                showLabel: true,
                animated: true,
                striped: false,
                variant: 'primary' // primary, success, warning, danger
            };
            
            const settings = $.extend({}, defaults, options);
            
            const progress = $(`
                <div class="aia-progress ${settings.striped ? 'aia-progress-striped' : ''}">
                    <div class="aia-progress-bar aia-progress-${settings.variant} ${settings.animated ? 'aia-progress-animated' : ''}"
                         role="progressbar"
                         style="width: ${(settings.value / settings.max) * 100}%"
                         aria-valuenow="${settings.value}"
                         aria-valuemin="0"
                         aria-valuemax="${settings.max}">
                        ${settings.showLabel ? settings.value + '%' : ''}
                    </div>
                </div>
            `);
            
            $(container).html(progress);
            
            return {
                update: function(value) {
                    const percentage = (value / settings.max) * 100;
                    progress.find('.aia-progress-bar')
                        .css('width', percentage + '%')
                        .attr('aria-valuenow', value)
                        .text(settings.showLabel ? value + '%' : '');
                },
                
                setVariant: function(variant) {
                    progress.find('.aia-progress-bar')
                        .removeClass(`aia-progress-${settings.variant}`)
                        .addClass(`aia-progress-${variant}`);
                    settings.variant = variant;
                }
            };
        }
    };

    /**
     * Dropdown Component
     */
    AIA.UI.Dropdown = {
        init: function() {
            $(document).on('click', '.aia-dropdown-toggle', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $toggle = $(this);
                const $dropdown = $toggle.next('.aia-dropdown-menu');
                
                // Close other dropdowns
                $('.aia-dropdown-menu').not($dropdown).removeClass('show');
                
                // Toggle current dropdown
                $dropdown.toggleClass('show');
                
                // Position dropdown
                const toggleOffset = $toggle.offset();
                const toggleHeight = $toggle.outerHeight();
                
                $dropdown.css({
                    top: toggleOffset.top + toggleHeight,
                    left: toggleOffset.left
                });
            });
            
            // Close dropdowns on outside click
            $(document).on('click', function() {
                $('.aia-dropdown-menu').removeClass('show');
            });
            
            // Prevent dropdown from closing when clicking inside
            $(document).on('click', '.aia-dropdown-menu', function(e) {
                e.stopPropagation();
            });
        }
    };

    /**
     * Tabs Component
     */
    AIA.UI.Tabs = {
        init: function(container) {
            const $container = $(container);
            const $tabs = $container.find('.aia-tab');
            const $contents = $container.find('.aia-tab-content');
            
            $tabs.on('click', function(e) {
                e.preventDefault();
                
                const $tab = $(this);
                const target = $tab.data('tab');
                
                // Update active states
                $tabs.removeClass('active');
                $tab.addClass('active');
                
                // Show content
                $contents.removeClass('active').hide();
                $container.find(`#${target}`).addClass('active').fadeIn(200);
            });
            
            // Activate first tab
            $tabs.first().trigger('click');
        }
    };

    /**
     * Tooltip Component
     */
    AIA.UI.Tooltip = {
        init: function() {
            $(document).on('mouseenter', '[data-tooltip]', function() {
                const $element = $(this);
                const text = $element.data('tooltip');
                const position = $element.data('tooltip-position') || 'top';
                
                const $tooltip = $(`<div class="aia-tooltip-popup aia-tooltip-${position}">${text}</div>`);
                $('body').append($tooltip);
                
                const elementOffset = $element.offset();
                const elementWidth = $element.outerWidth();
                const elementHeight = $element.outerHeight();
                const tooltipWidth = $tooltip.outerWidth();
                const tooltipHeight = $tooltip.outerHeight();
                
                let top, left;
                
                switch (position) {
                    case 'top':
                        top = elementOffset.top - tooltipHeight - 10;
                        left = elementOffset.left + (elementWidth - tooltipWidth) / 2;
                        break;
                    case 'bottom':
                        top = elementOffset.top + elementHeight + 10;
                        left = elementOffset.left + (elementWidth - tooltipWidth) / 2;
                        break;
                    case 'left':
                        top = elementOffset.top + (elementHeight - tooltipHeight) / 2;
                        left = elementOffset.left - tooltipWidth - 10;
                        break;
                    case 'right':
                        top = elementOffset.top + (elementHeight - tooltipHeight) / 2;
                        left = elementOffset.left + elementWidth + 10;
                        break;
                }
                
                $tooltip.css({ top: top, left: left }).fadeIn(200);
                
                $element.data('tooltip-element', $tooltip);
            });
            
            $(document).on('mouseleave', '[data-tooltip]', function() {
                const $tooltip = $(this).data('tooltip-element');
                if ($tooltip) {
                    $tooltip.fadeOut(200, function() {
                        $(this).remove();
                    });
                }
            });
        }
    };

    /**
     * Initialize all components
     */
    $(document).ready(function() {
        AIA.UI.Dropdown.init();
        AIA.UI.Tooltip.init();
        
        // Initialize tabs if present
        $('.aia-tabs').each(function() {
            AIA.UI.Tabs.init(this);
        });
    });

})(jQuery);