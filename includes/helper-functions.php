<?php
/**
 * Helper Functions for Media Wipe Plugin
 *
 * This file contains utility functions used throughout the plugin.
 *
 * @package MediaWipe
 * @since 1.0.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get plugin settings with defaults and caching
 *
 * @since 1.0.0
 * @return array Plugin settings
 */
function media_wipe_get_settings() {
    static $settings_cache = null;

    // Return cached settings if available
    if ( null !== $settings_cache ) {
        return $settings_cache;
    }

    $defaults = array(
        'require_backup_confirmation' => 1,
        'require_text_confirmation'   => 1,
        'show_document_preview'       => 1,
        'enable_logging'              => 1,
    );

    $settings = get_option( 'media_wipe_settings', array() );

    // Validate settings
    $settings = is_array( $settings ) ? $settings : array();

    // Cache the result
    $settings_cache = wp_parse_args( $settings, $defaults );

    return $settings_cache;
}

/**
 * Save plugin settings with validation
 *
 * @since 1.0.0
 * @return bool True on success, false on failure
 */
function media_wipe_save_settings() {
    // Verify user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return false;
    }

    // Validate and sanitize settings
    $settings = array(
        'require_backup_confirmation' => isset( $_POST['require_backup_confirmation'] ) ? 1 : 0,
        'require_text_confirmation'   => isset( $_POST['require_text_confirmation'] ) ? 1 : 0,
        'show_document_preview'       => isset( $_POST['show_document_preview'] ) ? 1 : 0,
        'enable_logging'              => isset( $_POST['enable_logging'] ) ? 1 : 0,
    );

    // Update settings
    $result = update_option( 'media_wipe_settings', $settings );

    // Clear settings cache
    wp_cache_delete( 'media_wipe_settings', 'options' );

    // Log settings change
    if ( function_exists( 'media_wipe_log_activity' ) ) {
        media_wipe_log_activity( 'settings_updated', array(
            'settings' => $settings,
        ) );
    }

    return $result;
}

/**
 * Log deletion activity
 *
 * @param string $action The action performed
 * @param array $data Additional data to log
 */
function media_wipe_log_activity($action, $data = array()) {
    $settings = media_wipe_get_settings();

    if (!$settings['enable_logging']) {
        return;
    }

    $log_entry = array(
        'timestamp' => current_time('mysql'),
        'user_id' => get_current_user_id(),
        'user_login' => wp_get_current_user()->user_login,
        'action' => $action,
        'data' => $data,
        'ip_address' => media_wipe_get_user_ip()
    );

    $logs = get_option('media_wipe_activity_log', array());
    $logs[] = $log_entry;

    // Keep only the last 100 log entries
    if (count($logs) > 100) {
        $logs = array_slice($logs, -100);
    }

    update_option('media_wipe_activity_log', $logs);
}

/**
 * Get recent activity for dashboard
 *
 * @param int $limit Number of recent activities to return
 * @return array Recent activities
 */
function media_wipe_get_recent_activity($limit = 5) {
    $logs = get_option('media_wipe_activity_log', array());
    return array_slice(array_reverse($logs), 0, $limit);
}

/**
 * Get user IP address
 *
 * @return string User IP address
 */
function media_wipe_get_user_ip() {
    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');

    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }

    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
}

/**
 * Format file size in human readable format
 *
 * @param int $size File size in bytes
 * @return string Formatted file size
 */
function media_wipe_format_file_size($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }

    return round($size, 2) . ' ' . $units[$i];
}

/**
 * Check if current user can perform media operations
 *
 * @return bool True if user has permission
 */
function media_wipe_user_can_delete_media() {
    return current_user_can('manage_options') || current_user_can('delete_posts');
}

/**
 * Sanitize and validate media IDs
 *
 * @param array $media_ids Array of media IDs to validate
 * @return array Validated media IDs
 */
function media_wipe_validate_media_ids($media_ids) {
    if (!is_array($media_ids)) {
        return array();
    }

    $validated_ids = array();

    foreach ($media_ids as $id) {
        $id = intval($id);
        if ($id > 0 && get_post_type($id) === 'attachment') {
            $validated_ids[] = $id;
        }
    }

    return $validated_ids;
}

/**
 * Get media file information with caching and error handling
 *
 * @since 1.0.0
 * @param int $attachment_id Attachment ID
 * @return array|false Media file information or false if not found
 */
function media_wipe_get_media_info( $attachment_id ) {
    // Validate input
    $attachment_id = absint( $attachment_id );
    if ( $attachment_id <= 0 ) {
        return false;
    }

    // Check cache first
    $cache_key = 'media_wipe_info_' . $attachment_id;
    $cached_info = wp_cache_get( $cache_key, 'media_wipe' );
    if ( false !== $cached_info ) {
        return $cached_info;
    }

    // Get attachment post
    $attachment = get_post( $attachment_id );
    if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
        wp_cache_set( $cache_key, false, 'media_wipe', 300 ); // Cache negative result for 5 minutes
        return false;
    }

    // Get file information
    $file_path = get_attached_file( $attachment_id );
    $file_size = 0;

    if ( $file_path && file_exists( $file_path ) ) {
        $file_size = filesize( $file_path );
        if ( false === $file_size ) {
            $file_size = 0;
        }
    }

    // Prepare media info array
    $media_info = array(
        'id'                 => $attachment_id,
        'title'              => sanitize_text_field( $attachment->post_title ),
        'filename'           => $file_path ? sanitize_file_name( basename( $file_path ) ) : '',
        'mime_type'          => sanitize_mime_type( $attachment->post_mime_type ),
        'file_size'          => $file_size,
        'file_size_formatted' => media_wipe_format_file_size( $file_size ),
        'upload_date'        => $attachment->post_date,
        'url'                => wp_get_attachment_url( $attachment_id ),
    );

    // Cache the result for 1 hour
    wp_cache_set( $cache_key, $media_info, 'media_wipe', 3600 );

    return $media_info;
}

/**
 * Check if attachment is used in content
 *
 * @param int $attachment_id Attachment ID
 * @return bool True if attachment is used
 */
function media_wipe_is_attachment_used($attachment_id) {
    global $wpdb;

    // Check if attachment is set as featured image
    $featured_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND meta_value = %d",
        $attachment_id
    ));

    if ($featured_count > 0) {
        return true;
    }

    // Check if attachment URL is used in post content
    $attachment_url = wp_get_attachment_url($attachment_id);
    if ($attachment_url) {
        $url_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE %s AND post_status = 'publish'",
            '%' . $wpdb->esc_like($attachment_url) . '%'
        ));

        if ($url_count > 0) {
            return true;
        }
    }

    return false;
}

/**
 * Check if MIME type is a document type
 *
 * @param string $mime_type MIME type to check
 * @return bool True if it's a document type
 */
function media_wipe_is_document_type($mime_type) {
    $document_types = array(
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
        'application/rtf',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.oasis.opendocument.presentation'
    );

    return in_array($mime_type, $document_types);
}

/**
 * Get document thumbnail URL if available
 *
 * @param int $attachment_id Attachment ID
 * @return string|false Thumbnail URL or false if not available
 */
function media_wipe_get_document_thumbnail($attachment_id) {
    // Check if WordPress can generate a thumbnail for this document
    $thumbnail = wp_get_attachment_image_src($attachment_id, 'thumbnail');

    if ($thumbnail && !empty($thumbnail[0])) {
        return $thumbnail[0];
    }

    return false;
}

/**
 * Generate document preview HTML for a specific attachment
 *
 * @param int $attachment_id Attachment ID
 * @return string HTML for document preview
 */
function media_wipe_get_document_preview_item($attachment_id) {
    $media_info = media_wipe_get_media_info($attachment_id);

    if (!$media_info || !media_wipe_is_document_type($media_info['mime_type'])) {
        return '';
    }

    $thumbnail_url = media_wipe_get_document_thumbnail($attachment_id);
    $file_icon = media_wipe_get_file_icon($media_info['mime_type']);
    $file_extension = media_wipe_get_file_extension($media_info['mime_type']);

    $html = '<div class="document-preview-item" data-attachment-id="' . esc_attr($attachment_id) . '">';

    if ($thumbnail_url) {
        $html .= '<div class="document-thumbnail">';
        $html .= '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr($media_info['title']) . '">';
        $html .= '<div class="document-overlay">' . $file_extension . '</div>';
        $html .= '</div>';
    } else {
        $html .= '<div class="document-icon-large">' . $file_icon . '</div>';
    }

    $html .= '<div class="document-info">';
    $html .= '<span class="document-name">' . esc_html($media_info['title']) . '</span>';
    $html .= '<span class="document-details">' . esc_html($file_extension) . ' • ' . esc_html($media_info['file_size_formatted']) . '</span>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

/**
 * Rate limiting for deletion operations
 *
 * @param string $action The action being performed
 * @param int $count Number of items being processed
 * @return bool True if within rate limits
 */
function media_wipe_check_rate_limit($action, $count = 1) {
    $user_id = get_current_user_id();
    $transient_key = 'media_wipe_rate_limit_' . $user_id . '_' . $action;

    // Get current rate limit data
    $rate_data = get_transient($transient_key);

    if (!$rate_data) {
        $rate_data = array(
            'count' => 0,
            'first_request' => time()
        );
    }

    // Define rate limits (per hour)
    $limits = array(
        'delete_selected' => 500, // Max 500 files per hour
        'delete_all' => 5,        // Max 5 delete all operations per hour
        'fetch_media' => 50       // Max 50 fetch operations per hour
    );

    $limit = isset($limits[$action]) ? $limits[$action] : 100;

    // Reset if more than an hour has passed
    if (time() - $rate_data['first_request'] > 3600) {
        $rate_data = array(
            'count' => 0,
            'first_request' => time()
        );
    }

    // Check if adding this request would exceed the limit
    if ($rate_data['count'] + $count > $limit) {
        return false;
    }

    // Update the count
    $rate_data['count'] += $count;

    // Store for 1 hour
    set_transient($transient_key, $rate_data, 3600);

    return true;
}

/**
 * Enhanced input sanitization for media IDs
 *
 * @param array $input Raw input array
 * @return array Sanitized and validated array
 */
function media_wipe_sanitize_media_ids($input) {
    if (!is_array($input)) {
        return array();
    }

    $sanitized = array();

    foreach ($input as $id) {
        // Convert to integer and validate
        $id = intval($id);

        if ($id > 0) {
            // Additional check to ensure it's actually an attachment
            $post = get_post($id);
            if ($post && $post->post_type === 'attachment') {
                $sanitized[] = $id;
            }
        }
    }

    return $sanitized;
}

/**
 * Security headers for admin pages
 */
function media_wipe_set_security_headers() {
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
    }
}

/**
 * Validate file operations permissions
 *
 * @param int $attachment_id Attachment ID
 * @param string $operation Operation type (delete, view, etc.)
 * @return bool True if operation is allowed
 */
function media_wipe_validate_file_operation($attachment_id, $operation = 'delete') {
    // Basic validation
    if (!is_numeric($attachment_id) || $attachment_id <= 0) {
        return false;
    }

    // Check if attachment exists
    $attachment = get_post($attachment_id);
    if (!$attachment || $attachment->post_type !== 'attachment') {
        return false;
    }

    // Check user capabilities based on operation
    switch ($operation) {
        case 'delete':
            return current_user_can('delete_post', $attachment_id);
        case 'view':
            return current_user_can('read_post', $attachment_id);
        default:
            return current_user_can('edit_post', $attachment_id);
    }
}

/**
 * Log security events
 *
 * @param string $event Event type
 * @param array $data Event data
 */
function media_wipe_log_security_event($event, $data = array()) {
    $log_entry = array(
        'timestamp' => current_time('mysql'),
        'user_id' => get_current_user_id(),
        'user_login' => wp_get_current_user()->user_login,
        'event' => $event,
        'data' => $data,
        'ip_address' => media_wipe_get_user_ip(),
        'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : ''
    );

    $security_logs = get_option('media_wipe_security_log', array());
    $security_logs[] = $log_entry;

    // Keep only the last 50 security log entries
    if (count($security_logs) > 50) {
        $security_logs = array_slice($security_logs, -50);
    }

    update_option('media_wipe_security_log', $security_logs);
}