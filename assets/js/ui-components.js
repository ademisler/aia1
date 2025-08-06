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
     * Enhanced Dropdown Component
     */
    AIA.UI.EnhancedDropdown = {
        init: function() {
            $(document).on('click', '.aia-dropdown__trigger', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const dropdown = $(this).closest('.aia-dropdown');
                const isActive = dropdown.hasClass('aia-dropdown--active');
                
                // Close all other dropdowns
                $('.aia-dropdown').removeClass('aia-dropdown--active');
                
                // Toggle current dropdown
                if (!isActive) {
                    dropdown.addClass('aia-dropdown--active');
                }
            });
            
            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.aia-dropdown').length) {
                    $('.aia-dropdown').removeClass('aia-dropdown--active');
                }
            });
        }
    };

    /**
     * Toggle Component
     */
    AIA.UI.Toggle = {
        init: function() {
            $(document).on('change', '.aia-toggle__input', function() {
                const isChecked = $(this).is(':checked');
                $(this).trigger('aia:toggle:change', { checked: isChecked });
            });
        }
    };

    /**
     * Loading States
     */
    AIA.UI.Loading = {
        show: function(element, text = '') {
            element.addClass('aia-btn--loading');
            if (text) {
                element.data('original-text', element.text()).text(text);
            }
        },
        
        hide: function(element) {
            element.removeClass('aia-btn--loading');
            const originalText = element.data('original-text');
            if (originalText) {
                element.text(originalText).removeData('original-text');
            }
        }
    };

    /**
     * Animation Controller
     */
    AIA.UI.Animation = {
        // Intersection Observer for scroll animations
        observer: null,
        
        init: function() {
            this.initScrollAnimations();
            this.initStaggerAnimations();
            this.initMicrointeractions();
        },
        
        initScrollAnimations: function() {
            if ('IntersectionObserver' in window) {
                this.observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('aia-in-view');
                        }
                    });
                }, { threshold: 0.1 });
                
                // Observe all scroll animation elements
                $('.aia-animate-on-scroll').each((index, element) => {
                    this.observer.observe(element);
                });
            }
        },
        
        initStaggerAnimations: function() {
            $('.aia-stagger-children').each(function() {
                const children = $(this).children();
                children.each(function(index) {
                    $(this).css('animation-delay', (index * 0.1) + 's');
                });
            });
        },
        
        initMicrointeractions: function() {
            // Add hover lift to cards
            $('.aia-card').addClass('aia-hover-lift');
            
            // Add press feedback to buttons
            $('.aia-button, .aia-btn').addClass('aia-press-feedback');
            
            // Add glow focus to form elements
            $('.aia-form-input, .aia-form-select, .aia-form-textarea').addClass('aia-glow-focus');
        },
        
        // Animate progress bar
        animateProgress: function(element, targetWidth, duration = 1500) {
            const progressBar = element.find('.aia-progress__bar');
            progressBar.css('--progress-width', targetWidth + '%');
            element.addClass('aia-progress-animated');
        },
        
        // Show notification with animation
        showNotification: function(message, type = 'info', duration = 5000) {
            const notification = $(`
                <div class="aia-notification aia-notification--${type} aia-notification-enter">
                    <div class="aia-notification__content">
                        <svg class="aia-icon aia-icon--sm">
                            <use href="${AIA_PLUGIN_URL}assets/icons/sprite.svg#aia-${type === 'success' ? 'check' : type}"></use>
                        </svg>
                        <span>${message}</span>
                    </div>
                    <button class="aia-notification__close">
                        <svg class="aia-icon aia-icon--xs">
                            <use href="${AIA_PLUGIN_URL}assets/icons/sprite.svg#aia-error"></use>
                        </svg>
                    </button>
                </div>
            `);
            
            // Add to container
            let container = $('.aia-notification-container');
            if (!container.length) {
                container = $('<div class="aia-notification-container"></div>').appendTo('body');
            }
            
            notification.appendTo(container);
            
            // Auto remove
            if (duration > 0) {
                setTimeout(() => {
                    this.hideNotification(notification);
                }, duration);
            }
            
            // Close button
            notification.find('.aia-notification__close').on('click', () => {
                this.hideNotification(notification);
            });
            
            return notification;
        },
        
        hideNotification: function(notification) {
            notification.addClass('aia-notification-exit');
            setTimeout(() => {
                notification.remove();
            }, 300);
        },
        
        // Skeleton loading
        showSkeleton: function(element, lines = 3) {
            const skeleton = $('<div class="aia-skeleton-wrapper"></div>');
            for (let i = 0; i < lines; i++) {
                skeleton.append('<div class="aia-skeleton" style="height: 16px; margin-bottom: 8px;"></div>');
            }
            element.html(skeleton);
        },
        
        hideSkeleton: function(element, content) {
            element.html(content);
        },
        
        // Attention animation
        drawAttention: function(element) {
            element.addClass('aia-attention');
            setTimeout(() => {
                element.removeClass('aia-attention');
            }, 1800); // 3 iterations * 0.6s
        },
        
        // Heartbeat for urgent items
        startHeartbeat: function(element) {
            element.addClass('aia-heartbeat');
        },
        
        stopHeartbeat: function(element) {
            element.removeClass('aia-heartbeat');
        }
    };

    /**
     * Initialize all components
     */
    jQuery(document).ready(function($) {
        AIA.UI.Dropdown.init();
        AIA.UI.EnhancedDropdown.init();
        AIA.UI.Tooltip.init();
        AIA.UI.Toggle.init();
        AIA.UI.Animation.init();
        
        // Initialize tabs if present
        $('.aia-tabs').each(function() {
            AIA.UI.Tabs.init(this);
        });
        
        // Add page load animation
        $('body').addClass('aia-page-loaded');
        
        // Initialize advanced interactions
        AIA.UI.AdvancedInteractions.init();
    });
    
    /**
     * Advanced Interactions System
     * Enterprise-level microinteractions and animations
     */
    AIA.UI.AdvancedInteractions = {
        init: function() {
            this.initScrollAnimations();
            this.initParallaxEffects();
            this.initMagneticButtons();
            this.initRippleEffects();
            this.initHoverEffects();
            this.initIntersectionObserver();
            this.initPerformanceOptimizations();
        },
        
        /**
         * Initialize scroll-triggered animations
         */
        initScrollAnimations: function() {
            const animatedElements = document.querySelectorAll('.aia-animate-on-scroll');
            
            if (animatedElements.length === 0) return;
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('aia-in-view');
                        
                        // Stagger children animation
                        if (entry.target.classList.contains('aia-stagger-children')) {
                            const children = entry.target.children;
                            Array.from(children).forEach((child, index) => {
                                setTimeout(() => {
                                    child.classList.add('aia-stagger-visible');
                                }, index * 100);
                            });
                        }
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });
            
            animatedElements.forEach(el => observer.observe(el));
        },
        
        /**
         * Initialize parallax effects
         */
        initParallaxEffects: function() {
            const parallaxElements = document.querySelectorAll('.aia-parallax-element');
            
            if (parallaxElements.length === 0 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            
            let ticking = false;
            
            const updateParallax = () => {
                const scrollY = window.pageYOffset;
                
                parallaxElements.forEach(element => {
                    const speed = element.dataset.parallaxSpeed || 0.5;
                    const yPos = -(scrollY * speed);
                    element.style.transform = `translateY(${yPos}px)`;
                });
                
                ticking = false;
            };
            
            const requestTick = () => {
                if (!ticking) {
                    requestAnimationFrame(updateParallax);
                    ticking = true;
                }
            };
            
            window.addEventListener('scroll', requestTick, { passive: true });
        },
        
        /**
         * Initialize magnetic button effects
         */
        initMagneticButtons: function() {
            const magneticButtons = document.querySelectorAll('.aia-magnetic-button');
            
            magneticButtons.forEach(button => {
                button.addEventListener('mousemove', (e) => {
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                    
                    const rect = button.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;
                    
                    const deltaX = (e.clientX - centerX) * 0.2;
                    const deltaY = (e.clientY - centerY) * 0.2;
                    
                    button.style.transform = `translate(${deltaX}px, ${deltaY}px) scale(1.05)`;
                });
                
                button.addEventListener('mouseleave', () => {
                    button.style.transform = 'translate(0, 0) scale(1)';
                });
            });
        },
        
        /**
         * Initialize ripple effects
         */
        initRippleEffects: function() {
            const rippleButtons = document.querySelectorAll('.aia-ripple-effect');
            
            rippleButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                    
                    const rect = button.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    const ripple = document.createElement('span');
                    ripple.className = 'aia-ripple-circle';
                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255, 255, 255, 0.3);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: aia-ripple 0.6s ease-out;
                        pointer-events: none;
                    `;
                    
                    button.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        },
        
        /**
         * Initialize advanced hover effects
         */
        initHoverEffects: function() {
            // Tilt effect
            const tiltElements = document.querySelectorAll('.aia-hover-tilt');
            
            tiltElements.forEach(element => {
                element.addEventListener('mousemove', (e) => {
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                    
                    const rect = element.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;
                    
                    const rotateX = (e.clientY - centerY) / 10;
                    const rotateY = (centerX - e.clientX) / 10;
                    
                    element.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                });
                
                element.addEventListener('mouseleave', () => {
                    element.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg)';
                });
            });
            
            // Shine effect
            const shineElements = document.querySelectorAll('.aia-hover-shine');
            
            shineElements.forEach(element => {
                element.addEventListener('mouseenter', () => {
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                    
                    const shine = element.querySelector('::before');
                    if (shine) {
                        shine.style.left = '100%';
                    }
                });
            });
        },
        
        /**
         * Initialize intersection observer for performance
         */
        initIntersectionObserver: function() {
            // Lazy load heavy animations
            const heavyAnimationElements = document.querySelectorAll('.aia-particle-system, .aia-glass-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('aia-animation-active');
                    } else {
                        entry.target.classList.remove('aia-animation-active');
                    }
                });
            }, {
                threshold: 0.1
            });
            
            heavyAnimationElements.forEach(el => observer.observe(el));
        },
        
        /**
         * Performance optimizations
         */
        initPerformanceOptimizations: function() {
            // Add GPU acceleration to animated elements
            const animatedElements = document.querySelectorAll(`
                .aia-hover-lift,
                .aia-hover-lift-rotate,
                .aia-morph-button,
                .aia-magnetic-button,
                .aia-card-stack
            `);
            
            animatedElements.forEach(element => {
                element.classList.add('aia-gpu-accelerated');
            });
            
            // Throttle scroll events
            let scrollTimer = null;
            window.addEventListener('scroll', () => {
                if (scrollTimer !== null) {
                    clearTimeout(scrollTimer);
                }
                scrollTimer = setTimeout(() => {
                    document.body.classList.add('aia-scrolling');
                    setTimeout(() => {
                        document.body.classList.remove('aia-scrolling');
                    }, 150);
                }, 150);
            }, { passive: true });
        }
    };
    
    /**
     * Advanced Loading States
     */
    AIA.UI.LoadingStates = {
        /**
         * Show skeleton loading
         */
        showSkeleton: function(container, type = 'default') {
            const skeletonTemplates = {
                default: `
                    <div class="aia-skeleton-loader aia-skeleton-loader--title"></div>
                    <div class="aia-skeleton-loader aia-skeleton-loader--text"></div>
                    <div class="aia-skeleton-loader aia-skeleton-loader--text"></div>
                `,
                card: `
                    <div class="aia-skeleton-loader aia-skeleton-loader--avatar"></div>
                    <div class="aia-skeleton-loader aia-skeleton-loader--title"></div>
                    <div class="aia-skeleton-loader aia-skeleton-loader--text"></div>
                `,
                table: `
                    <div class="aia-skeleton-loader" style="height: 40px; margin-bottom: 8px;"></div>
                    <div class="aia-skeleton-loader" style="height: 30px; margin-bottom: 8px;"></div>
                    <div class="aia-skeleton-loader" style="height: 30px; margin-bottom: 8px;"></div>
                    <div class="aia-skeleton-loader" style="height: 30px; margin-bottom: 8px;"></div>
                `
            };
            
            $(container).html(skeletonTemplates[type] || skeletonTemplates.default);
        },
        
        /**
         * Show liquid loading
         */
        showLiquidLoader: function(container) {
            $(container).html('<div class="aia-liquid-loader"></div>');
        },
        
        /**
         * Show advanced spinner
         */
        showAdvancedSpinner: function(container) {
            $(container).html('<div class="aia-spinner-advanced"></div>');
        },
        
        /**
         * Hide loading state
         */
        hide: function(container) {
            $(container).find('.aia-skeleton-loader, .aia-liquid-loader, .aia-spinner-advanced').remove();
        }
    };
    
    /**
     * Microinteractions Manager
     */
    AIA.UI.Microinteractions = {
        /**
         * Add success feedback
         */
        success: function(element, callback) {
            const $el = $(element);
            $el.addClass('aia-success-feedback');
            
            setTimeout(() => {
                $el.removeClass('aia-success-feedback');
                if (callback) callback();
            }, 1000);
        },
        
        /**
         * Add error shake
         */
        error: function(element, callback) {
            const $el = $(element);
            $el.addClass('aia-error-shake');
            
            setTimeout(() => {
                $el.removeClass('aia-error-shake');
                if (callback) callback();
            }, 600);
        },
        
        /**
         * Add attention pulse
         */
        attention: function(element, duration = 2000) {
            const $el = $(element);
            $el.addClass('aia-attention');
            
            setTimeout(() => {
                $el.removeClass('aia-attention');
            }, duration);
        },
        
        /**
         * Add bounce effect
         */
        bounce: function(element) {
            const $el = $(element);
            $el.addClass('aia-elastic-entrance');
            
            setTimeout(() => {
                $el.removeClass('aia-elastic-entrance');
            }, 600);
        }
    };

})(jQuery);