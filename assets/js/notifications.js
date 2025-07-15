/**
 * Media Wipe Enhanced Notification System
 * 
 * Provides toast notifications, progress bars, and dismissible notices
 * with animations and accessibility features.
 * 
 * @package MediaWipe
 * @since 1.1.1
 */

(function ($) {
    'use strict';

    /**
     * MediaWipe Notifications Class
     */
    class MediaWipeNotifications {
        constructor() {
            this.notifications = new Map();
            this.progressBars = new Map();
            this.queue = [];
            this.maxVisible = 5;
            this.animationDuration = 300;

            this.init();
        }

        /**
         * Initialize the notification system
         */
        init() {
            this.createContainer();
            this.bindEvents();
            this.processQueue();
        }

        /**
         * Create notification containers if they don't exist
         */
        createContainer() {
            if (!$('#media-wipe-notifications-container').length) {
                $('body').append(`
                    <div id="media-wipe-notifications-container" 
                         class="media-wipe-notifications-container" 
                         aria-live="polite" aria-atomic="true">
                    </div>
                `);
            }

            if (!$('#media-wipe-progress-container').length) {
                $('body').append(`
                    <div id="media-wipe-progress-container" 
                         class="media-wipe-progress-container">
                    </div>
                `);
            }
        }

        /**
         * Bind event handlers
         */
        bindEvents() {
            // Handle dismissible notice close buttons
            $(document).on('click', '.media-wipe-notice .notice-dismiss', (e) => {
                this.dismissNotice(e.currentTarget);
            });

            // Handle toast notification close buttons
            $(document).on('click', '.toast-notification .toast-close', (e) => {
                this.closeToast($(e.currentTarget).closest('.toast-notification').attr('id'));
            });

            // Handle progress bar cancel buttons
            $(document).on('click', '.progress-notification .progress-cancel', (e) => {
                this.cancelProgress($(e.currentTarget).closest('.progress-notification').attr('id'));
            });

            // Auto-dismiss notices with auto-dismiss attribute
            $(document).on('DOMNodeInserted', '.media-wipe-notice[data-auto-dismiss="true"]', (e) => {
                const $notice = $(e.target);
                const dismissTime = parseInt($notice.data('dismiss-time')) || 5000;

                setTimeout(() => {
                    this.dismissNotice($notice.find('.notice-dismiss')[0]);
                }, dismissTime);
            });
        }

        /**
         * Show a toast notification
         * 
         * @param {string} type Notification type (success, warning, error, info)
         * @param {object} options Notification options
         */
        show(type, options = {}) {
            const defaults = {
                title: '',
                message: '',
                autoDismiss: true,
                dismissTime: 5000,
                showProgress: false,
                progressValue: 0,
                actions: []
            };

            const config = Object.assign(defaults, options);
            const id = 'toast_' + Date.now() + '_' + Math.random().toString(36).substring(2, 11);

            const notification = {
                id: id,
                type: type,
                config: config,
                timestamp: Date.now()
            };

            this.queue.push(notification);
            this.processQueue();

            return id;
        }

        /**
         * Show success notification
         */
        success(title, message, options = {}) {
            return this.show('success', Object.assign({ title, message }, options));
        }

        /**
         * Show warning notification
         */
        warning(title, message, options = {}) {
            return this.show('warning', Object.assign({ title, message }, options));
        }

        /**
         * Show error notification
         */
        error(title, message, options = {}) {
            return this.show('error', Object.assign({ title, message }, options));
        }

        /**
         * Show info notification
         */
        info(title, message, options = {}) {
            return this.show('info', Object.assign({ title, message }, options));
        }

        /**
         * Process notification queue
         */
        processQueue() {
            const container = $('#media-wipe-notifications-container');
            const visibleCount = container.children('.toast-notification').length;

            if (visibleCount >= this.maxVisible || this.queue.length === 0) {
                return;
            }

            const notification = this.queue.shift();
            this.renderToast(notification);

            // Process next in queue after animation
            setTimeout(() => {
                this.processQueue();
            }, 100);
        }

        /**
         * Render toast notification
         */
        renderToast(notification) {
            const { id, type, config } = notification;
            const icon = this.getIcon(type);

            const toast = $(`
                <div id="${id}" class="toast-notification toast-${type}" role="alert" aria-live="assertive">
                    <div class="toast-icon">
                        ${icon}
                    </div>
                    <div class="toast-content">
                        ${config.title ? `<div class="toast-title">${this.escapeHtml(config.title)}</div>` : ''}
                        <div class="toast-message">${this.escapeHtml(config.message)}</div>
                        ${config.showProgress ? this.renderProgressBar(config.progressValue) : ''}
                        ${config.actions.length ? this.renderActions(config.actions) : ''}
                    </div>
                    <button type="button" class="toast-close" aria-label="Close notification">
                        <span class="dashicons dashicons-dismiss"></span>
                    </button>
                    ${config.autoDismiss ? this.renderDismissTimer(config.dismissTime) : ''}
                </div>
            `);

            // Add to container with animation
            const container = $('#media-wipe-notifications-container');
            toast.css({ transform: 'translateX(100%)', opacity: 0 });
            container.append(toast);

            // Animate in
            setTimeout(() => {
                toast.css({ transform: 'translateX(0)', opacity: 1 });
            }, 10);

            // Store reference
            this.notifications.set(id, {
                element: toast,
                config: config,
                timestamp: Date.now()
            });

            // Auto-dismiss if enabled
            if (config.autoDismiss) {
                setTimeout(() => {
                    this.closeToast(id);
                }, config.dismissTime);
            }
        }

        /**
         * Close toast notification
         */
        closeToast(id) {
            const notification = this.notifications.get(id);
            if (!notification) return;

            const { element } = notification;

            // Animate out
            element.css({ transform: 'translateX(100%)', opacity: 0 });

            setTimeout(() => {
                element.remove();
                this.notifications.delete(id);
                this.processQueue();
            }, this.animationDuration);
        }

        /**
         * Update toast progress
         */
        updateProgress(id, progress, message = '') {
            const notification = this.notifications.get(id);
            if (!notification) return;

            const { element } = notification;
            const progressBar = element.find('.toast-progress-bar');
            const progressText = element.find('.toast-progress-text');

            if (progressBar.length) {
                progressBar.css('width', `${progress}%`);
                progressText.text(`${progress}%`);
            }

            if (message) {
                element.find('.toast-message').text(message);
            }
        }

        /**
         * Show progress notification
         */
        showProgress(id, title, progress = 0, options = {}) {
            const defaults = {
                showPercentage: true,
                showCancel: false,
                estimatedTime: null,
                currentStep: '',
                totalSteps: null
            };

            const config = Object.assign(defaults, options);

            const progressNotification = $(`
                <div id="${id}" class="progress-notification" role="status" aria-live="polite">
                    <div class="progress-header">
                        <div class="progress-title">${this.escapeHtml(title)}</div>
                        ${config.showCancel ? '<button type="button" class="progress-cancel">Cancel</button>' : ''}
                    </div>
                    <div class="progress-body">
                        <div class="progress-bar-container">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: ${progress}%"></div>
                            </div>
                            ${config.showPercentage ? `<div class="progress-percentage">${progress}%</div>` : ''}
                        </div>
                        ${config.currentStep ? `<div class="progress-step">${this.escapeHtml(config.currentStep)}</div>` : ''}
                        ${config.estimatedTime ? `<div class="progress-time">Estimated time: ${config.estimatedTime}</div>` : ''}
                    </div>
                </div>
            `);

            const container = $('#media-wipe-progress-container');
            container.append(progressNotification);

            this.progressBars.set(id, {
                element: progressNotification,
                config: config,
                progress: progress
            });

            return id;
        }

        /**
         * Update progress bar
         */
        updateProgressBar(id, progress, options = {}) {
            const progressBar = this.progressBars.get(id);
            if (!progressBar) return;

            const { element, config } = progressBar;

            // Update progress
            element.find('.progress-fill').css('width', `${progress}%`);

            if (config.showPercentage) {
                element.find('.progress-percentage').text(`${progress}%`);
            }

            // Update optional fields
            if (options.currentStep) {
                element.find('.progress-step').text(options.currentStep);
            }

            if (options.estimatedTime) {
                element.find('.progress-time').text(`Estimated time: ${options.estimatedTime}`);
            }

            // Update stored progress
            progressBar.progress = progress;

            // Auto-close when complete
            if (progress >= 100) {
                setTimeout(() => {
                    this.closeProgress(id);
                }, 2000);
            }
        }

        /**
         * Close progress notification
         */
        closeProgress(id) {
            const progressBar = this.progressBars.get(id);
            if (!progressBar) return;

            const { element } = progressBar;

            element.fadeOut(this.animationDuration, () => {
                element.remove();
                this.progressBars.delete(id);
            });
        }

        /**
         * Cancel progress operation
         */
        cancelProgress(id) {
            // Trigger cancel event
            $(document).trigger('media-wipe-progress-cancel', { id: id });
            this.closeProgress(id);
        }

        /**
         * Dismiss a notice
         */
        dismissNotice(element) {
            const $notice = $(element).closest('.media-wipe-notice');
            const noticeId = $notice.data('notice-id');
            const contentHash = $notice.data('content-hash');

            if (!noticeId) {
                $notice.fadeOut(this.animationDuration);
                return;
            }

            // Send AJAX request to store dismissal
            $.ajax({
                url: window.mediaWipeAjax ? window.mediaWipeAjax.ajaxurl : ajaxurl,
                type: 'POST',
                data: {
                    action: 'media_wipe_dismiss_notice',
                    nonce: window.mediaWipeAjax ? window.mediaWipeAjax.nonce : '',
                    notice_id: noticeId,
                    content_hash: contentHash
                },
                success: () => {
                    $notice.fadeOut(this.animationDuration);
                },
                error: () => {
                    // Still dismiss visually even if AJAX fails
                    $notice.fadeOut(this.animationDuration);
                }
            });
        }

        /**
         * Get icon for notification type
         */
        getIcon(type) {
            const icons = {
                success: '<span class="dashicons dashicons-yes-alt"></span>',
                warning: '<span class="dashicons dashicons-warning"></span>',
                error: '<span class="dashicons dashicons-dismiss"></span>',
                info: '<span class="dashicons dashicons-info"></span>'
            };

            return icons[type] || icons.info;
        }

        /**
         * Render progress bar for toast
         */
        renderProgressBar(progress) {
            return `
                <div class="toast-progress">
                    <div class="toast-progress-bar" style="width: ${progress}%"></div>
                    <div class="toast-progress-text">${progress}%</div>
                </div>
            `;
        }

        /**
         * Render action buttons for toast
         */
        renderActions(actions) {
            if (!actions.length) return '';

            const buttons = actions.map(action =>
                `<button type="button" class="toast-action" data-action="${action.id}">
                    ${this.escapeHtml(action.label)}
                </button>`
            ).join('');

            return `<div class="toast-actions">${buttons}</div>`;
        }

        /**
         * Render dismiss timer for auto-dismiss
         */
        renderDismissTimer(dismissTime) {
            return `
                <div class="toast-timer">
                    <div class="toast-timer-bar" style="animation-duration: ${dismissTime}ms"></div>
                </div>
            `;
        }

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        /**
         * Clear all notifications
         */
        clearAll() {
            this.notifications.forEach((_, id) => {
                this.closeToast(id);
            });

            this.progressBars.forEach((_, id) => {
                this.closeProgress(id);
            });

            this.queue = [];
        }

        /**
         * Get notification count
         */
        getCount() {
            return this.notifications.size + this.progressBars.size;
        }
    }

    // Initialize notification system and make it globally available
    $(document).ready(function () {
        window.MediaWipeNotifications = new MediaWipeNotifications();

        // Expose convenience methods globally
        window.mediaWipeNotify = {
            success: (title, message, options) => window.MediaWipeNotifications.success(title, message, options),
            warning: (title, message, options) => window.MediaWipeNotifications.warning(title, message, options),
            error: (title, message, options) => window.MediaWipeNotifications.error(title, message, options),
            info: (title, message, options) => window.MediaWipeNotifications.info(title, message, options),
            progress: (id, title, progress, options) => window.MediaWipeNotifications.showProgress(id, title, progress, options),
            updateProgress: (id, progress, options) => window.MediaWipeNotifications.updateProgressBar(id, progress, options)
        };
    });

})(jQuery);
