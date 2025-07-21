<?php
/**
 * Unused Media Scanner Class
 *
 * Handles scanning and detection of unused media files
 *
 * @package MediaWipe
 * @since 1.2.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Media Wipe Unused Media Scanner
 */
class MediaWipeUnusedScanner {
    
    /**
     * Scan configuration
     */
    private $config = array();
    
    /**
     * Scan progress data
     */
    private $progress = array(
        'total_files' => 0,
        'processed' => 0,
        'unused_found' => 0,
        'current_file' => '',
        'status' => 'idle'
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_media_wipe_start_unused_scan', array($this, 'ajax_start_scan'));
        add_action('wp_ajax_media_wipe_get_scan_progress', array($this, 'ajax_get_progress'));
        add_action('wp_ajax_media_wipe_get_unused_results', array($this, 'ajax_get_results'));
        add_action('wp_ajax_media_wipe_delete_unused_files', array($this, 'ajax_delete_unused'));
    }
    
    /**
     * Start unused media scan via AJAX
     */
    public function ajax_start_scan() {
        // Verify nonce and capabilities
        if (!wp_verify_nonce($_POST['nonce'], 'media_wipe_unused_scan') || !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed.', 'media-wipe')));
        }
        
        // Get scan configuration
        $this->config = array(
            'exclude_recent' => isset($_POST['exclude_recent']) ? (bool) $_POST['exclude_recent'] : true,
            'exclude_featured' => isset($_POST['exclude_featured']) ? (bool) $_POST['exclude_featured'] : true,
            'scan_depth' => isset($_POST['scan_depth']) ? sanitize_text_field($_POST['scan_depth']) : 'basic'
        );
        
        // Initialize scan
        $this->init_scan();
        
        // Start scanning process
        $results = $this->perform_scan();
        
        wp_send_json_success(array(
            'message' => esc_html__('Scan completed successfully.', 'media-wipe'),
            'results' => $results
        ));
    }
    
    /**
     * Get scan progress via AJAX
     */
    public function ajax_get_progress() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Insufficient permissions.', 'media-wipe')));
        }
        
        $progress = get_transient('media_wipe_scan_progress');
        wp_send_json_success($progress ? $progress : $this->progress);
    }
    
    /**
     * Get scan results via AJAX
     */
    public function ajax_get_results() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Insufficient permissions.', 'media-wipe')));
        }
        
        $results = get_transient('media_wipe_unused_results');
        wp_send_json_success($results ? $results : array());
    }
    
    /**
     * Delete unused media via AJAX
     */
    public function ajax_delete_unused() {
        // Debug logging (disabled in production)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Media Wipe: ajax_delete_unused called');
            error_log('Media Wipe: POST data: ' . print_r($_POST, true));
        }

        // Verify nonce and capabilities (try both specific and global nonce actions)
        $nonce_check = wp_verify_nonce($_POST['nonce'], 'media_wipe_delete_unused');
        if (!$nonce_check) {
            $nonce_check = wp_verify_nonce($_POST['nonce'], 'media_wipe_ajax_nonce');
            error_log('Media Wipe: Trying global nonce action');
        }
        $capability_check = current_user_can('manage_options');

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Media Wipe: Nonce check result: ' . ($nonce_check ? 'PASS' : 'FAIL'));
            error_log('Media Wipe: Capability check result: ' . ($capability_check ? 'PASS' : 'FAIL'));
            error_log('Media Wipe: Received nonce: ' . $_POST['nonce']);
            error_log('Media Wipe: Tried nonce actions: media_wipe_delete_unused, media_wipe_ajax_nonce');
        }

        if (!$nonce_check || !$capability_check) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Media Wipe: Security check failed');
            }
            wp_send_json_error(array('message' => esc_html__('Security check failed.', 'media-wipe')));
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Media Wipe: Security check passed');
        }

        $selected_ids = isset($_POST['selected_ids']) ? array_map('intval', explode(',', $_POST['selected_ids'])) : array();

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Media Wipe: Raw selected_ids: ' . $_POST['selected_ids']);
            error_log('Media Wipe: Processed selected_ids: ' . print_r($selected_ids, true));
            error_log('Media Wipe: Selected IDs count: ' . count($selected_ids));
        }

        if (empty($selected_ids)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Media Wipe: No files selected for deletion');
            }
            wp_send_json_error(array('message' => esc_html__('No files selected for deletion.', 'media-wipe')));
        }

        $deleted_count = 0;
        $errors = array();
        $debug_info = array();

        foreach ($selected_ids as $attachment_id) {
            $safety_check = $this->is_safe_to_delete($attachment_id);

            // Debug info only in debug mode
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $debug_info[] = "ID $attachment_id: Safe to delete = " . ($safety_check ? 'Yes' : 'No');
            }

            if ($safety_check) {
                $deleted = wp_delete_attachment($attachment_id, true);
                if ($deleted) {
                    $deleted_count++;

                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        $debug_info[] = "ID $attachment_id: Successfully deleted";
                    }

                    // Log the deletion
                    if (function_exists('media_wipe_log_activity')) {
                        media_wipe_log_activity('delete_unused', array(
                            'attachment_id' => $attachment_id,
                            'user_id' => get_current_user_id(),
                            'timestamp' => current_time('mysql')
                        ));
                    }
                } else {
                    $errors[] = sprintf(esc_html__('Failed to delete file ID: %d', 'media-wipe'), $attachment_id);

                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        $debug_info[] = "ID $attachment_id: wp_delete_attachment failed";
                    }
                }
            } else {
                $errors[] = sprintf(esc_html__('File ID %d is not safe to delete.', 'media-wipe'), $attachment_id);

                if (defined('WP_DEBUG') && WP_DEBUG) {
                    $debug_info[] = "ID $attachment_id: Failed safety check";
                }
            }
        }

        $message = $deleted_count > 0 ?
            sprintf(esc_html__('Successfully deleted %d unused media files.', 'media-wipe'), $deleted_count) :
            esc_html__('No files were deleted.', 'media-wipe');

        $response_data = array(
            'deleted_count' => $deleted_count,
            'errors' => $errors,
            'message' => $message
        );

        // Only include debug info in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG && !empty($debug_info)) {
            $response_data['debug_info'] = $debug_info;
        }

        wp_send_json_success($response_data);
    }
    
    /**
     * Initialize scan process
     */
    private function init_scan() {
        // Get all media files
        $media_files = $this->get_all_media_files();
        
        $this->progress = array(
            'total_files' => count($media_files),
            'processed' => 0,
            'unused_found' => 0,
            'current_file' => '',
            'status' => 'scanning'
        );
        
        // Store progress
        set_transient('media_wipe_scan_progress', $this->progress, HOUR_IN_SECONDS);
        
        // Clear previous results
        delete_transient('media_wipe_unused_results');
    }
    
    /**
     * Perform the actual scan
     */
    private function perform_scan() {
        $media_files = $this->get_all_media_files();
        $unused_files = array();
        
        foreach ($media_files as $attachment) {
            // Update progress
            $this->progress['processed']++;
            $this->progress['current_file'] = $attachment->post_title;
            set_transient('media_wipe_scan_progress', $this->progress, HOUR_IN_SECONDS);
            
            // Check if file is unused
            $usage_data = $this->scan_media_usage($attachment->ID);
            
            if ($usage_data['is_unused']) {
                $this->progress['unused_found']++;
                $unused_files[] = $this->format_unused_file_data($attachment, $usage_data);
            }
        }
        
        // Update final progress
        $this->progress['status'] = 'completed';
        set_transient('media_wipe_scan_progress', $this->progress, HOUR_IN_SECONDS);
        
        // Store results
        set_transient('media_wipe_unused_results', $unused_files, HOUR_IN_SECONDS);
        
        return array(
            'total_scanned' => $this->progress['total_files'],
            'unused_found' => $this->progress['unused_found'],
            'files' => $unused_files
        );
    }
    
    /**
     * Get all media files to scan
     */
    private function get_all_media_files() {
        $args = array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );
        
        // Exclude recent files if configured
        if ($this->config['exclude_recent']) {
            $args['date_query'] = array(
                array(
                    'before' => date('Y-m-d', strtotime('-30 days')),
                    'inclusive' => false,
                ),
            );
        }
        
        $attachment_ids = get_posts($args);
        
        // Get full attachment objects
        $attachments = array();
        foreach ($attachment_ids as $id) {
            $attachment = get_post($id);
            if ($attachment) {
                $attachments[] = $attachment;
            }
        }
        
        return $attachments;
    }
    
    /**
     * Scan media usage for a specific attachment
     */
    private function scan_media_usage($attachment_id) {
        $usage_contexts = array();
        $confidence_score = 100;
        
        // Check post content
        $post_usage = $this->scan_post_content($attachment_id);
        if (!empty($post_usage)) {
            $usage_contexts['posts'] = $post_usage;
        }
        
        // Check featured images
        if ($this->config['exclude_featured']) {
            $featured_usage = $this->scan_featured_images($attachment_id);
            if (!empty($featured_usage)) {
                $usage_contexts['featured'] = $featured_usage;
            }
        }
        
        // Check widget content
        $widget_usage = $this->scan_widget_content($attachment_id);
        if (!empty($widget_usage)) {
            $usage_contexts['widgets'] = $widget_usage;
        }
        
        // Check menu items
        $menu_usage = $this->scan_menu_items($attachment_id);
        if (!empty($menu_usage)) {
            $usage_contexts['menus'] = $menu_usage;
        }
        
        // Check customizer settings
        $customizer_usage = $this->scan_customizer_settings($attachment_id);
        if (!empty($customizer_usage)) {
            $usage_contexts['customizer'] = $customizer_usage;
        }


        
        // Advanced scan if configured
        if ($this->config['scan_depth'] === 'advanced') {
            $theme_usage = $this->scan_theme_files($attachment_id);
            if (!empty($theme_usage)) {
                $usage_contexts['theme'] = $theme_usage;
                $confidence_score -= 15; // Lower confidence for theme scanning
            }
        }
        
        // Calculate final confidence
        $is_unused = empty($usage_contexts);
        if ($is_unused && $this->config['scan_depth'] === 'advanced') {
            $confidence_score = max(75, $confidence_score); // Lower confidence for advanced scan
        }
        
        return array(
            'is_unused' => $is_unused,
            'confidence_score' => $confidence_score,
            'usage_contexts' => $usage_contexts
        );
    }



    /**
     * Simple and effective scan for post content and media usage
     */
    private function scan_post_content($attachment_id) {
        global $wpdb;

        $attachment_url = wp_get_attachment_url($attachment_id);
        $filename = basename($attachment_url);
        $attachment_id_str = (string) $attachment_id;

        $usage = array();

        // Get all image sizes for this attachment
        $all_urls = array($attachment_url);
        if (wp_attachment_is_image($attachment_id)) {
            $image_sizes = array('thumbnail', 'medium', 'medium_large', 'large');
            foreach ($image_sizes as $size) {
                $image_data = wp_get_attachment_image_src($attachment_id, $size);
                if ($image_data && !empty($image_data[0])) {
                    $all_urls[] = $image_data[0];
                }
            }
        }
        $all_urls = array_unique($all_urls);

        // 1. Search in post content (all post statuses, all post types)
        $content_found = false;
        foreach ($all_urls as $url) {
            if (!$content_found) {
                $posts = $wpdb->get_results($wpdb->prepare("
                    SELECT ID, post_title, post_type, post_status
                    FROM {$wpdb->posts}
                    WHERE (post_content LIKE %s OR post_excerpt LIKE %s)
                    AND post_status IN ('publish', 'draft', 'private', 'future', 'pending')
                    AND post_type NOT IN ('attachment', 'revision', 'nav_menu_item', 'customize_changeset', 'oembed_cache')
                    LIMIT 1
                ", '%' . $wpdb->esc_like($url) . '%', '%' . $wpdb->esc_like($url) . '%'));

                if (!empty($posts)) {
                    $usage['posts'] = $posts;
                    $content_found = true;
                }
            }
        }

        // Also check filename
        if (!$content_found) {
            $posts = $wpdb->get_results($wpdb->prepare("
                SELECT ID, post_title, post_type, post_status
                FROM {$wpdb->posts}
                WHERE (post_content LIKE %s OR post_excerpt LIKE %s)
                AND post_status IN ('publish', 'draft', 'private', 'future', 'pending')
                AND post_type NOT IN ('attachment', 'revision', 'nav_menu_item', 'customize_changeset', 'oembed_cache')
                LIMIT 1
            ", '%' . $wpdb->esc_like($filename) . '%', '%' . $wpdb->esc_like($filename) . '%'));

            if (!empty($posts)) {
                $usage['posts'] = $posts;
                $content_found = true;
            }
        }

        // 2. Search in post meta (custom fields, ACF fields, etc.)
        if (!$content_found) {
            $meta_usage = $wpdb->get_results($wpdb->prepare("
                SELECT p.ID, p.post_title, p.post_type, pm.meta_key, pm.meta_value
                FROM {$wpdb->postmeta} pm
                JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE (pm.meta_value LIKE %s OR pm.meta_value LIKE %s OR pm.meta_value = %s)
                AND p.post_status IN ('publish', 'draft', 'private', 'future', 'pending')
                AND p.post_type NOT IN ('attachment', 'revision', 'nav_menu_item')
                LIMIT 1
            ", '%' . $wpdb->esc_like($filename) . '%', '%' . $wpdb->esc_like($attachment_url) . '%', $attachment_id_str));

            if (!empty($meta_usage)) {
                $usage['meta'] = $meta_usage;
                $content_found = true;
            }
        }

        // 3. Search in serialized meta data (for complex fields)
        if (!$content_found) {
            $serialized_meta = $wpdb->get_results($wpdb->prepare("
                SELECT p.ID, p.post_title, p.post_type, pm.meta_key
                FROM {$wpdb->postmeta} pm
                JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE (pm.meta_value LIKE %s OR pm.meta_value LIKE %s)
                AND p.post_status IN ('publish', 'draft', 'private', 'future', 'pending')
                AND p.post_type NOT IN ('attachment', 'revision', 'nav_menu_item')
                LIMIT 1
            ", '%"' . $attachment_id_str . '"%', '%"' . $wpdb->esc_like($filename) . '"%'));

            if (!empty($serialized_meta)) {
                $usage['serialized_meta'] = $serialized_meta;
                $content_found = true;
            }
        }

        // 4. Check for gallery shortcodes and attachment IDs
        if (!$content_found) {
            $gallery_usage = $wpdb->get_results($wpdb->prepare("
                SELECT ID, post_title, post_type
                FROM {$wpdb->posts}
                WHERE (post_content LIKE %s OR post_content LIKE %s OR post_content LIKE %s)
                AND post_status IN ('publish', 'draft', 'private', 'future', 'pending')
                AND post_type NOT IN ('attachment', 'revision', 'nav_menu_item')
                LIMIT 1
            ", '%ids="' . $attachment_id . '"%', '%ids="' . $attachment_id . ',%', '%,' . $attachment_id . ',%'));

            if (!empty($gallery_usage)) {
                $usage['galleries'] = $gallery_usage;
            }
        }

        return $usage;
    }

    /**
     * Scan featured images
     */
    private function scan_featured_images($attachment_id) {
        global $wpdb;

        $posts = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID, p.post_title, p.post_type
            FROM {$wpdb->postmeta} pm
            JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_thumbnail_id'
            AND pm.meta_value = %d
            AND p.post_status = 'publish'
        ", $attachment_id));

        return $posts;
    }

    /**
     * Simple widget content scanning
     */
    private function scan_widget_content($attachment_id) {
        $usage = array();
        $attachment_url = wp_get_attachment_url($attachment_id);
        $filename = basename($attachment_url);

        // Check media widgets
        $media_widgets = get_option('widget_media_image', array());
        foreach ($media_widgets as $widget_id => $widget_data) {
            if (isset($widget_data['attachment_id']) && $widget_data['attachment_id'] == $attachment_id) {
                $usage[] = array(
                    'widget_type' => 'media_image',
                    'widget_id' => $widget_id,
                    'title' => isset($widget_data['title']) ? $widget_data['title'] : ''
                );
                break; // Found usage, no need to continue
            }
        }

        // Check text widgets if no usage found yet
        if (empty($usage)) {
            $text_widgets = get_option('widget_text', array());
            foreach ($text_widgets as $widget_id => $widget_data) {
                if (isset($widget_data['text'])) {
                    if (strpos($widget_data['text'], $attachment_url) !== false ||
                        strpos($widget_data['text'], $filename) !== false) {
                        $usage[] = array(
                            'widget_type' => 'text',
                            'widget_id' => $widget_id,
                            'title' => isset($widget_data['title']) ? $widget_data['title'] : ''
                        );
                        break;
                    }
                }
            }
        }

        return $usage;
    }

    /**
     * Scan menu items
     */
    private function scan_menu_items($attachment_id) {
        $attachment_url = wp_get_attachment_url($attachment_id);
        $usage = array();

        // Get all menus
        $menus = wp_get_nav_menus();

        foreach ($menus as $menu) {
            $menu_items = wp_get_nav_menu_items($menu->term_id);

            foreach ($menu_items as $item) {
                // Check if menu item uses this attachment
                if ($item->object === 'attachment' && $item->object_id == $attachment_id) {
                    $usage[] = array(
                        'menu_name' => $menu->name,
                        'item_title' => $item->title,
                        'item_id' => $item->ID
                    );
                }
            }
        }

        return $usage;
    }

    /**
     * Scan customizer settings
     */
    private function scan_customizer_settings($attachment_id) {
        $usage = array();

        // Common customizer settings that might contain media
        $settings_to_check = array(
            'custom_logo',
            'header_image',
            'background_image',
            'site_icon'
        );

        foreach ($settings_to_check as $setting) {
            $value = get_theme_mod($setting);

            if ($setting === 'custom_logo' || $setting === 'site_icon') {
                // These store attachment IDs
                if ($value == $attachment_id) {
                    $usage[] = array(
                        'setting' => $setting,
                        'type' => 'attachment_id'
                    );
                }
            }
        }

        return $usage;
    }

    /**
     * Format unused file data for display
     */
    private function format_unused_file_data($attachment, $usage_data) {
        $file_size = filesize(get_attached_file($attachment->ID));
        $file_type = get_post_mime_type($attachment->ID);

        return array(
            'id' => $attachment->ID,
            'title' => $attachment->post_title,
            'filename' => basename(get_attached_file($attachment->ID)),
            'url' => wp_get_attachment_url($attachment->ID),
            'thumbnail' => wp_get_attachment_image_url($attachment->ID, 'thumbnail'),
            'file_size' => $file_size,
            'file_size_formatted' => size_format($file_size),
            'file_type' => $file_type,
            'upload_date' => $attachment->post_date,
            'confidence_score' => $usage_data['confidence_score'],
            'usage_contexts' => $usage_data['usage_contexts']
        );
    }

    /**
     * Simple theme file scanning for media usage (Advanced mode)
     */
    private function scan_theme_files($attachment_id) {
        $usage = array();
        $attachment_url = wp_get_attachment_url($attachment_id);
        $filename = basename($attachment_url);

        // Get active theme directory
        $theme_dir = get_template_directory();

        // Only scan main theme files to avoid performance issues
        $files_to_scan = array(
            $theme_dir . '/style.css',
            $theme_dir . '/functions.php',
            $theme_dir . '/index.php',
            $theme_dir . '/header.php',
            $theme_dir . '/footer.php'
        );

        foreach ($files_to_scan as $file_path) {
            if (file_exists($file_path) && is_readable($file_path)) {
                $file_content = @file_get_contents($file_path);
                if ($file_content !== false) {
                    if (strpos($file_content, $attachment_url) !== false ||
                        strpos($file_content, $filename) !== false) {
                        $usage[] = array(
                            'file' => str_replace(ABSPATH, '', $file_path),
                            'type' => 'theme_file'
                        );
                        break; // Found usage, no need to continue
                    }
                }
            }
        }

        return $usage;
    }



    /**
     * Check if attachment is safe to delete
     */
    private function is_safe_to_delete($attachment_id) {
        // Verify attachment exists
        if (!get_post($attachment_id)) {
            return false;
        }

        // Re-scan to ensure it's still unused
        $usage_data = $this->scan_media_usage($attachment_id);

        // Debug logging (disabled in production)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Media Wipe Debug - Attachment $attachment_id: " . json_encode(array(
                'is_unused' => $usage_data['is_unused'],
                'confidence_score' => $usage_data['confidence_score'],
                'usage_contexts' => array_keys($usage_data['usage_contexts'])
            )));
        }

        // Only delete if still unused and confidence is reasonable (lowered threshold for testing)
        return $usage_data['is_unused'] && $usage_data['confidence_score'] >= 60;
    }
}
