/**
 * AI Inventory Agent - Advanced Components JavaScript
 * Enterprise-grade interactive functionality for complex UI patterns
 */

// Extend AIA namespace for advanced components
window.AIA = window.AIA || {};
AIA.Advanced = AIA.Advanced || {};

/**
 * Advanced Notification System
 * Toast-style notifications with actions and auto-dismiss
 */
AIA.Advanced.NotificationSystem = {
    stack: null,
    notifications: [],
    
    init: function() {
        this.createStack();
    },
    
    createStack: function() {
        if (!document.querySelector('.aia-notification-stack')) {
            const stack = document.createElement('div');
            stack.className = 'aia-notification-stack';
            document.body.appendChild(stack);
            this.stack = stack;
        } else {
            this.stack = document.querySelector('.aia-notification-stack');
        }
    },
    
    show: function(options) {
        const notification = {
            id: Date.now() + Math.random(),
            type: options.type || 'info',
            title: options.title,
            message: options.message,
            actions: options.actions || [],
            duration: options.duration || 5000,
            persistent: options.persistent || false
        };
        
        this.notifications.push(notification);
        this.render(notification);
        
        if (!notification.persistent) {
            setTimeout(() => {
                this.dismiss(notification.id);
            }, notification.duration);
        }
        
        return notification.id;
    },
    
    render: function(notification) {
        const iconMap = {
            success: 'aia-check',
            warning: 'aia-warning',
            error: 'aia-error',
            info: 'aia-info'
        };
        
        const actionsHtml = notification.actions.length > 0 ? `
            <div class="aia-notification__actions">
                ${notification.actions.map(action => `
                    <button class="aia-notification__action" data-action="${action.id}">
                        ${action.label}
                    </button>
                `).join('')}
            </div>
        ` : '';
        
        const html = `
            <div class="aia-notification aia-notification--${notification.type}" data-id="${notification.id}">
                <div class="aia-notification__icon">
                    <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                        <use href="${AIA_PLUGIN_URL}assets/icons/sprite.svg#${iconMap[notification.type]}"></use>
                    </svg>
                </div>
                <div class="aia-notification__content">
                    ${notification.title ? `<div class="aia-notification__title">${notification.title}</div>` : ''}
                    <div class="aia-notification__message">${notification.message}</div>
                    ${actionsHtml}
                </div>
                <button class="aia-notification__close" data-dismiss="${notification.id}">
                    <svg class="aia-icon aia-icon--xs" aria-hidden="true">
                        <use href="${AIA_PLUGIN_URL}assets/icons/sprite.svg#aia-x"></use>
                    </svg>
                </button>
            </div>
        `;
        
        this.stack.insertAdjacentHTML('beforeend', html);
        
        const element = this.stack.querySelector(`[data-id="${notification.id}"]`);
        
        // Trigger enter animation
        requestAnimationFrame(() => {
            element.classList.add('aia-notification--entering');
        });
        
        // Bind events
        element.querySelector('.aia-notification__close').addEventListener('click', () => {
            this.dismiss(notification.id);
        });
        
        // Bind action events
        notification.actions.forEach(action => {
            const button = element.querySelector(`[data-action="${action.id}"]`);
            if (button) {
                button.addEventListener('click', () => {
                    action.handler();
                    if (action.dismissAfter) {
                        this.dismiss(notification.id);
                    }
                });
            }
        });
    },
    
    dismiss: function(id) {
        const element = this.stack.querySelector(`[data-id="${id}"]`);
        if (element) {
            element.classList.add('aia-notification--exiting');
            element.classList.remove('aia-notification--entering');
            
            setTimeout(() => {
                element.remove();
                this.notifications = this.notifications.filter(n => n.id !== id);
            }, 200);
        }
    },
    
    dismissAll: function() {
        this.notifications.forEach(notification => {
            this.dismiss(notification.id);
        });
    },
    
    // Convenience methods
    success: function(message, title, options = {}) {
        return this.show({
            type: 'success',
            title: title,
            message: message,
            ...options
        });
    },
    
    error: function(message, title, options = {}) {
        return this.show({
            type: 'error',
            title: title,
            message: message,
            persistent: true,
            ...options
        });
    },
    
    warning: function(message, title, options = {}) {
        return this.show({
            type: 'warning',
            title: title,
            message: message,
            ...options
        });
    },
    
    info: function(message, title, options = {}) {
        return this.show({
            type: 'info',
            title: title,
            message: message,
            ...options
        });
    }
};

/**
 * Context Menu System
 * Right-click context menus with keyboard navigation
 */
AIA.Advanced.ContextMenu = {
    currentMenu: null,
    
    init: function() {
        this.bindGlobalEvents();
    },
    
    bindGlobalEvents: function() {
        const self = this;
        
        // Close menu on click outside
        document.addEventListener('click', function(e) {
            if (self.currentMenu && !e.target.closest('.aia-context-menu')) {
                self.close();
            }
        });
        
        // Close menu on escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && self.currentMenu) {
                self.close();
            }
        });
    },
    
    show: function(x, y, items) {
        this.close(); // Close any existing menu
        
        const menu = this.createMenu(items);
        document.body.appendChild(menu);
        
        // Position the menu
        this.positionMenu(menu, x, y);
        
        // Show the menu
        requestAnimationFrame(() => {
            menu.classList.add('aia-context-menu--active');
        });
        
        this.currentMenu = menu;
        
        // Focus first item for keyboard navigation
        const firstItem = menu.querySelector('.aia-context-menu__item');
        if (firstItem) {
            firstItem.focus();
        }
    },
    
    createMenu: function(items) {
        const menu = document.createElement('div');
        menu.className = 'aia-context-menu';
        
        const itemsHtml = items.map(item => {
            if (item.type === 'divider') {
                return '<div class="aia-context-menu__divider"></div>';
            }
            
            const iconHtml = item.icon ? `
                <svg class="aia-context-menu__item-icon aia-icon aia-icon--sm" aria-hidden="true">
                    <use href="${AIA_PLUGIN_URL}assets/icons/sprite.svg#${item.icon}"></use>
                </svg>
            ` : '';
            
            const shortcutHtml = item.shortcut ? `
                <span class="aia-context-menu__item-shortcut">${item.shortcut}</span>
            ` : '';
            
            const className = `aia-context-menu__item ${item.danger ? 'aia-context-menu__item--danger' : ''}`;
            
            return `
                <button class="${className}" data-action="${item.id}" tabindex="0">
                    ${iconHtml}
                    <span>${item.label}</span>
                    ${shortcutHtml}
                </button>
            `;
        }).join('');
        
        menu.innerHTML = itemsHtml;
        
        // Bind click events
        menu.querySelectorAll('.aia-context-menu__item').forEach(item => {
            item.addEventListener('click', (e) => {
                const action = e.currentTarget.dataset.action;
                const menuItem = items.find(i => i.id === action);
                if (menuItem && menuItem.handler) {
                    menuItem.handler();
                    this.close();
                }
            });
        });
        
        return menu;
    },
    
    positionMenu: function(menu, x, y) {
        // Get menu dimensions
        menu.style.visibility = 'hidden';
        menu.style.display = 'block';
        const rect = menu.getBoundingClientRect();
        menu.style.visibility = '';
        menu.style.display = '';
        
        // Get viewport dimensions
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Calculate position
        let left = x;
        let top = y;
        
        // Adjust if menu would go outside viewport
        if (left + rect.width > viewportWidth) {
            left = viewportWidth - rect.width - 10;
        }
        
        if (top + rect.height > viewportHeight) {
            top = viewportHeight - rect.height - 10;
        }
        
        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
    },
    
    close: function() {
        if (this.currentMenu) {
            this.currentMenu.classList.remove('aia-context-menu--active');
            setTimeout(() => {
                if (this.currentMenu) {
                    this.currentMenu.remove();
                    this.currentMenu = null;
                }
            }, 150);
        }
    }
};

/**
 * Advanced Modal System
 * Stackable modals with focus management
 */
AIA.Advanced.ModalSystem = {
    stack: [],
    
    init: function() {
        this.createContainer();
        this.bindGlobalEvents();
    },
    
    createContainer: function() {
        if (!document.querySelector('.aia-modal-stack')) {
            const container = document.createElement('div');
            container.className = 'aia-modal-stack';
            document.body.appendChild(container);
        }
    },
    
    bindGlobalEvents: function() {
        const self = this;
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && self.stack.length > 0) {
                self.close();
            }
        });
    },
    
    open: function(options) {
        const modal = {
            id: Date.now() + Math.random(),
            title: options.title,
            content: options.content,
            size: options.size || 'medium',
            actions: options.actions || [],
            onClose: options.onClose
        };
        
        this.stack.push(modal);
        this.render(modal);
        this.updateStackState();
        
        return modal.id;
    },
    
    render: function(modal) {
        const container = document.querySelector('.aia-modal-stack');
        
        const actionsHtml = modal.actions.length > 0 ? `
            <div class="aia-modal-footer">
                ${modal.actions.map(action => `
                    <button class="aia-button aia-button--${action.style || 'secondary'}" 
                            data-action="${action.id}">
                        ${action.label}
                    </button>
                `).join('')}
            </div>
        ` : '';
        
        const html = `
            <div class="aia-modal-overlay" data-modal="${modal.id}">
                <div class="aia-modal-container">
                    <div class="aia-modal-dialog aia-modal-dialog--${modal.size}">
                        <div class="aia-modal-header">
                            <h2 class="aia-modal-title">${modal.title}</h2>
                            <button class="aia-modal-close" data-close="${modal.id}">
                                <svg class="aia-icon aia-icon--sm" aria-hidden="true">
                                    <use href="${AIA_PLUGIN_URL}assets/icons/sprite.svg#aia-x"></use>
                                </svg>
                            </button>
                        </div>
                        <div class="aia-modal-body">
                            ${modal.content}
                        </div>
                        ${actionsHtml}
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
        
        const overlay = container.querySelector(`[data-modal="${modal.id}"]`);
        
        // Bind events
        overlay.querySelector('.aia-modal-close').addEventListener('click', () => {
            this.close(modal.id);
        });
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                this.close(modal.id);
            }
        });
        
        // Bind action events
        modal.actions.forEach(action => {
            const button = overlay.querySelector(`[data-action="${action.id}"]`);
            if (button) {
                button.addEventListener('click', () => {
                    action.handler(modal.id);
                });
            }
        });
        
        // Show modal
        requestAnimationFrame(() => {
            overlay.classList.add('aia-modal-overlay--active');
        });
    },
    
    close: function(id) {
        if (id) {
            // Close specific modal
            this.stack = this.stack.filter(modal => modal.id !== id);
            const overlay = document.querySelector(`[data-modal="${id}"]`);
            if (overlay) {
                const modal = this.stack.find(m => m.id === id);
                if (modal && modal.onClose) {
                    modal.onClose();
                }
                
                overlay.classList.remove('aia-modal-overlay--active');
                setTimeout(() => overlay.remove(), 200);
            }
        } else {
            // Close top modal
            const topModal = this.stack.pop();
            if (topModal) {
                this.close(topModal.id);
            }
        }
        
        this.updateStackState();
    },
    
    updateStackState: function() {
        const container = document.querySelector('.aia-modal-stack');
        container.classList.toggle('aia-modal-stack--active', this.stack.length > 0);
        
        // Manage body scroll
        document.body.style.overflow = this.stack.length > 0 ? 'hidden' : '';
    }
};

// Initialize advanced components when DOM is ready
jQuery(document).ready(function($) {
    // Initialize all advanced components
    AIA.Advanced.NotificationSystem.init();
    AIA.Advanced.ContextMenu.init();
    AIA.Advanced.ModalSystem.init();
    
    // Add keyboard shortcut help
    console.log('🚀 AIA Advanced Components loaded');
    
    // Example usage - can be removed in production
    if (window.location.search.includes('demo=1')) {
        setTimeout(() => {
            AIA.Advanced.NotificationSystem.success(
                'Advanced components have been loaded successfully!',
                'Welcome to AIA v2.0'
            );
        }, 1000);
    }
});