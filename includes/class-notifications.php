<?php
/**
 * Enhanced Notification System for Media Wipe Plugin
 * 
 * Provides unified toast notifications with animations, queuing, and accessibility features.
 * 
 * @package MediaWipe
 * @since 1.1.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

/**
 * Media Wipe Notifications Class
 */
class Media_Wipe_Notifications {
    
    /**
     * Notification types
     */
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_INFO = 'info';
    
    /**
     * Default auto-dismiss time in milliseconds
     */
    const DEFAULT_DISMISS_TIME = 5000;
    
    /**
     * Initialize the notification system
     */
    public function __construct() {
        add_action('wp_ajax_media_wipe_dismiss_notice', array($this, 'ajax_dismiss_notice'));
        add_action('wp_ajax_media_wipe_reset_notices', array($this, 'ajax_reset_notices'));
        add_action('admin_footer', array($this, 'render_notification_container'));
    }
    
    /**
     * Dismiss a notice via AJAX
     */
    public function ajax_dismiss_notice() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'media_wipe_ajax_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'media-wipe')));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'media-wipe')));
        }
        
        $notice_id = sanitize_text_field($_POST['notice_id'] ?? '');
        $content_hash = sanitize_text_field($_POST['content_hash'] ?? '');
        
        if (empty($notice_id)) {
            wp_send_json_error(array('message' => __('Invalid notice ID.', 'media-wipe')));
        }
        
        // Store dismissal in user meta
        $dismissed_notices = get_user_meta(get_current_user_id(), 'media_wipe_dismissed_notices', true);
        if (!is_array($dismissed_notices)) {
            $dismissed_notices = array();
        }
        
        $dismissed_notices[$notice_id] = array(
            'dismissed_at' => current_time('mysql'),
            'content_hash' => $content_hash,
            'user_id' => get_current_user_id()
        );
        
        update_user_meta(get_current_user_id(), 'media_wipe_dismissed_notices', $dismissed_notices);
        
        wp_send_json_success(array('message' => __('Notice dismissed successfully.', 'media-wipe')));
    }
    
    /**
     * Reset all dismissed notices via AJAX
     */
    public function ajax_reset_notices() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'media_wipe_ajax_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'media-wipe')));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'media-wipe')));
        }
        
        delete_user_meta(get_current_user_id(), 'media_wipe_dismissed_notices');
        
        wp_send_json_success(array('message' => __('All notices reset successfully.', 'media-wipe')));
    }
    
    /**
     * Check if a notice is dismissed
     * 
     * @param string $notice_id Notice identifier
     * @param string $content_hash Content hash for change detection
     * @return bool True if dismissed
     */
    public function is_notice_dismissed($notice_id, $content_hash = '') {
        $dismissed_notices = get_user_meta(get_current_user_id(), 'media_wipe_dismissed_notices', true);
        
        if (!is_array($dismissed_notices) || !isset($dismissed_notices[$notice_id])) {
            return false;
        }
        
        $dismissed_notice = $dismissed_notices[$notice_id];
        
        // If content hash is provided and different, notice should be shown again
        if (!empty($content_hash) && isset($dismissed_notice['content_hash'])) {
            if ($dismissed_notice['content_hash'] !== $content_hash) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Generate content hash for notice
     * 
     * @param string $content Notice content
     * @return string Hash
     */
    public function generate_content_hash($content) {
        return md5($content);
    }
    
    /**
     * Create a dismissible notice
     * 
     * @param string $id Notice ID
     * @param string $type Notice type (success, warning, error, info)
     * @param string $title Notice title
     * @param string $content Notice content
     * @param array $options Additional options
     * @return string Notice HTML
     */
    public function create_dismissible_notice($id, $type, $title, $content, $options = array()) {
        $content_hash = $this->generate_content_hash($content);
        
        // Check if notice is dismissed
        if ($this->is_notice_dismissed($id, $content_hash)) {
            return '';
        }
        
        $defaults = array(
            'dismissible' => true,
            'auto_dismiss' => false,
            'dismiss_time' => self::DEFAULT_DISMISS_TIME,
            'show_icon' => true,
            'css_class' => ''
        );
        
        $options = wp_parse_args($options, $defaults);
        
        $icon = $this->get_notice_icon($type);
        $css_classes = array(
            'media-wipe-notice',
            'notice-' . $type,
            $options['css_class']
        );
        
        if ($options['dismissible']) {
            $css_classes[] = 'is-dismissible';
        }
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr(implode(' ', array_filter($css_classes))); ?>" 
             data-notice-id="<?php echo esc_attr($id); ?>"
             data-content-hash="<?php echo esc_attr($content_hash); ?>"
             data-auto-dismiss="<?php echo $options['auto_dismiss'] ? 'true' : 'false'; ?>"
             data-dismiss-time="<?php echo esc_attr($options['dismiss_time']); ?>">
            
            <?php if ($options['show_icon']): ?>
                <div class="notice-icon">
                    <?php echo $icon; ?>
                </div>
            <?php endif; ?>
            
            <div class="notice-content">
                <?php if (!empty($title)): ?>
                    <h3 class="notice-title"><?php echo esc_html($title); ?></h3>
                <?php endif; ?>
                
                <div class="notice-message">
                    <?php echo wp_kses_post($content); ?>
                </div>
            </div>
            
            <?php if ($options['dismissible']): ?>
                <button type="button" class="notice-dismiss" aria-label="<?php esc_attr_e('Dismiss this notice', 'media-wipe'); ?>">
                    <span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'media-wipe'); ?></span>
                    <span class="dashicons dashicons-dismiss"></span>
                </button>
            <?php endif; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get icon for notice type
     * 
     * @param string $type Notice type
     * @return string Icon HTML
     */
    private function get_notice_icon($type) {
        $icons = array(
            self::TYPE_SUCCESS => '<span class="dashicons dashicons-yes-alt"></span>',
            self::TYPE_WARNING => '<span class="dashicons dashicons-warning"></span>',
            self::TYPE_ERROR => '<span class="dashicons dashicons-dismiss"></span>',
            self::TYPE_INFO => '<span class="dashicons dashicons-info"></span>'
        );
        
        return $icons[$type] ?? $icons[self::TYPE_INFO];
    }
    
    /**
     * Render notification container in admin footer
     */
    public function render_notification_container() {
        // Only render on Media Wipe pages
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'media-wipe') === false) {
            return;
        }
        
        ?>
        <div id="media-wipe-notifications-container" class="media-wipe-notifications-container" aria-live="polite" aria-atomic="true">
            <!-- Toast notifications will be inserted here -->
        </div>
        
        <div id="media-wipe-progress-container" class="media-wipe-progress-container">
            <!-- Progress notifications will be inserted here -->
        </div>
        <?php
    }
    
    /**
     * Enqueue notification scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'media-wipe-notifications',
            MEDIA_WIPE_URL . 'assets/js/notifications.js',
            array('jquery'),
            MEDIA_WIPE_VERSION,
            true
        );
        
        wp_localize_script('media-wipe-notifications', 'mediaWipeNotifications', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('media_wipe_ajax_nonce'),
            'strings' => array(
                'dismiss' => __('Dismiss', 'media-wipe'),
                'close' => __('Close', 'media-wipe'),
                'error' => __('Error', 'media-wipe'),
                'success' => __('Success', 'media-wipe'),
                'warning' => __('Warning', 'media-wipe'),
                'info' => __('Information', 'media-wipe')
            )
        ));
    }
    
    /**
     * Create a toast notification
     * 
     * @param string $type Notification type
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $options Additional options
     * @return array Notification data for JavaScript
     */
    public function create_toast($type, $title, $message, $options = array()) {
        $defaults = array(
            'auto_dismiss' => true,
            'dismiss_time' => self::DEFAULT_DISMISS_TIME,
            'show_progress' => false,
            'progress_value' => 0,
            'actions' => array()
        );
        
        $options = wp_parse_args($options, $defaults);
        
        return array(
            'id' => uniqid('toast_'),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $this->get_notice_icon($type),
            'timestamp' => current_time('timestamp'),
            'options' => $options
        );
    }
    
    /**
     * Create a progress notification
     * 
     * @param string $id Progress ID
     * @param string $title Progress title
     * @param int $progress Progress percentage (0-100)
     * @param array $options Additional options
     * @return array Progress data for JavaScript
     */
    public function create_progress($id, $title, $progress = 0, $options = array()) {
        $defaults = array(
            'show_percentage' => true,
            'show_cancel' => false,
            'estimated_time' => null,
            'current_step' => '',
            'total_steps' => null
        );
        
        $options = wp_parse_args($options, $defaults);
        
        return array(
            'id' => $id,
            'title' => $title,
            'progress' => max(0, min(100, $progress)),
            'timestamp' => current_time('timestamp'),
            'options' => $options
        );
    }
    
    /**
     * Get notification preferences for current user
     * 
     * @return array User preferences
     */
    public function get_user_preferences() {
        $defaults = array(
            'show_success' => true,
            'show_warnings' => true,
            'show_errors' => true,
            'show_info' => true,
            'auto_dismiss_time' => self::DEFAULT_DISMISS_TIME,
            'sound_enabled' => false,
            'position' => 'top-right'
        );
        
        $preferences = get_user_meta(get_current_user_id(), 'media_wipe_notification_preferences', true);
        
        if (!is_array($preferences)) {
            $preferences = array();
        }
        
        return wp_parse_args($preferences, $defaults);
    }
    
    /**
     * Update notification preferences for current user
     * 
     * @param array $preferences New preferences
     * @return bool Success status
     */
    public function update_user_preferences($preferences) {
        $current_preferences = $this->get_user_preferences();
        $updated_preferences = wp_parse_args($preferences, $current_preferences);
        
        return update_user_meta(get_current_user_id(), 'media_wipe_notification_preferences', $updated_preferences);
    }
}

// Initialize the notification system
new Media_Wipe_Notifications();
