<?php
/**
 * Delete All Media Functionality
 *
 * This file handles the "Delete All Media" feature of the Media Wipe plugin.
 *
 * @package MediaWipe
 * @since 1.0.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Display the "Delete All Media" page
function media_wipe_all_media_page() {
    // Set security headers
    media_wipe_set_security_headers();

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'media-wipe'));
    }

    // Get media statistics for the confirmation dialog
    $media_stats = media_wipe_get_media_statistics();

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Delete All Media Files', 'media-wipe'); ?></h1>

        <div class="media-wipe-stats-card">
            <h3><?php esc_html_e('Media Library Overview', 'media-wipe'); ?></h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html($media_stats['total']); ?></span>
                    <span class="stat-label"><?php esc_html_e('Total Files', 'media-wipe'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html($media_stats['images']); ?></span>
                    <span class="stat-label"><?php esc_html_e('Images', 'media-wipe'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html($media_stats['documents']); ?></span>
                    <span class="stat-label"><?php esc_html_e('Documents', 'media-wipe'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html($media_stats['videos']); ?></span>
                    <span class="stat-label"><?php esc_html_e('Videos', 'media-wipe'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html($media_stats['audio']); ?></span>
                    <span class="stat-label"><?php esc_html_e('Audio', 'media-wipe'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html($media_stats['other']); ?></span>
                    <span class="stat-label"><?php esc_html_e('Other', 'media-wipe'); ?></span>
                </div>
            </div>
        </div>

        <!-- Modern Safety Section -->
        <section class="mw-page-safety-section collapsible" data-section="delete-all-safety">
            <div class="mw-page-safety-notice">
                <div class="mw-page-safety-header">
                    <span class="dashicons dashicons-shield-alt"></span>
                    <h3><?php esc_html_e('Critical Safety Warning', 'media-wipe'); ?></h3>
                    <span class="mw-page-safety-toggle dashicons dashicons-arrow-up-alt2"></span>
                </div>
                <div class="mw-page-safety-content">
                    <p><?php esc_html_e('This action will permanently delete ALL media files from your WordPress media library. This includes all images, videos, audio files, documents, file variations, thumbnails, metadata, and attachment records.', 'media-wipe'); ?></p>
                    <div class="mw-page-safety-grid">
                        <div class="mw-page-safety-item">
                            <span class="dashicons dashicons-backup"></span>
                            <h4><?php esc_html_e('Backup Required', 'media-wipe'); ?></h4>
                            <p><?php esc_html_e('Create a complete backup of your website before proceeding. This action cannot be undone.', 'media-wipe'); ?></p>
                        </div>
                        <div class="mw-page-safety-item">
                            <span class="dashicons dashicons-admin-site-alt3"></span>
                            <h4><?php esc_html_e('Test on Staging', 'media-wipe'); ?></h4>
                            <p><?php esc_html_e('Always test this operation on a staging environment before running on production.', 'media-wipe'); ?></p>
                        </div>
                        <div class="mw-page-safety-item">
                            <span class="dashicons dashicons-warning"></span>
                            <h4><?php esc_html_e('Permanent Deletion', 'media-wipe'); ?></h4>
                            <p><?php esc_html_e('All media files will be permanently removed from your server and cannot be recovered.', 'media-wipe'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <form method="post" id="media-wipe-all-form">
            <?php wp_nonce_field('media_wipe_all_action', 'media_wipe_all_nonce'); ?>
            <button type="button" id="open-delete-all-modal" class="button button-danger media-wipe-btn-large">
                <?php esc_html_e('Delete All Media Files', 'media-wipe'); ?>
            </button>
        </form>
    </div>

    <!-- Enhanced Confirmation Modal -->
    <div id="delete-all-confirmation-modal" class="media-wipe-modal" style="display:none;">
        <div class="modal-overlay"></div>
        <div class="modal-content-large">
            <div class="modal-header">
                <h2><?php esc_html_e('Confirm Deletion of All Media Files', 'media-wipe'); ?></h2>
                <button type="button" class="modal-close" id="close-delete-all-modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="confirmation-warning">
                    <div class="warning-icon-large">🚨</div>
                    <p class="warning-text"><?php esc_html_e('You are about to permanently delete ALL media files from your WordPress Media Library.', 'media-wipe'); ?></p>
                </div>

                <div class="deletion-summary">
                    <h4><?php esc_html_e('Files to be deleted:', 'media-wipe'); ?></h4>
                    <div class="summary-stats">
                        <span><?php echo sprintf(esc_html__('%d total files', 'media-wipe'), $media_stats['total']); ?></span>
                        <?php if ($media_stats['documents'] > 0): ?>
                            <span class="document-count"><?php echo sprintf(esc_html__('%d documents (PDF, DOC, etc.)', 'media-wipe'), $media_stats['documents']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($media_stats['documents'] > 0): ?>
                <div class="document-preview-section">
                    <h4><?php esc_html_e('Document Files Preview:', 'media-wipe'); ?></h4>
                    <div id="document-preview-container">
                        <?php echo media_wipe_get_document_preview_html(); ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                // Get settings to determine what confirmations to show
                $settings = media_wipe_get_settings();
                $show_backup_confirmation = $settings['require_backup_confirmation'];
                $show_text_confirmation = $settings['require_text_confirmation'];
                ?>

                <?php if ($show_backup_confirmation): ?>
                <div class="confirmation-checklist">
                    <h4><?php esc_html_e('Before proceeding, please confirm:', 'media-wipe'); ?></h4>
                    <label class="checkbox-item">
                        <input type="checkbox" id="backup-confirmed" required>
                        <span><?php esc_html_e('I have created a complete backup of my website', 'media-wipe'); ?></span>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" id="understand-permanent" required>
                        <span><?php esc_html_e('I understand this action is permanent and cannot be undone', 'media-wipe'); ?></span>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" id="accept-responsibility" required>
                        <span><?php esc_html_e('I accept full responsibility for this action', 'media-wipe'); ?></span>
                    </label>
                </div>
                <?php endif; ?>

                <?php if ($show_text_confirmation): ?>
                <div class="final-confirmation">
                    <label for="confirmation-text"><?php esc_html_e('Type "DELETE ALL MEDIA" to confirm:', 'media-wipe'); ?></label>
                    <input type="text" id="confirmation-text" placeholder="<?php esc_attr_e('Type DELETE ALL MEDIA here', 'media-wipe'); ?>" autocomplete="off">
                    <div class="confirmation-help">
                        <span class="dashicons dashicons-info"></span>
                        <span><?php esc_html_e('Type exactly DELETE ALL MEDIA in uppercase or lowercase', 'media-wipe'); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!$show_backup_confirmation && !$show_text_confirmation): ?>
                <div class="simple-confirmation">
                    <div class="warning-message">
                        <span class="dashicons dashicons-warning"></span>
                        <p><strong><?php esc_html_e('Warning:', 'media-wipe'); ?></strong> <?php esc_html_e('This will permanently delete all media files from your website. This action cannot be undone.', 'media-wipe'); ?></p>
                    </div>
                    <label class="checkbox-item">
                        <input type="checkbox" id="final-confirm" required>
                        <span><?php esc_html_e('I understand and want to proceed with deleting all media files', 'media-wipe'); ?></span>
                    </label>
                </div>
                <?php endif; ?>
            </div>

            <div class="modal-footer">
                <button type="button" id="cancel-delete-all" class="button button-secondary">
                    <?php esc_html_e('Cancel', 'media-wipe'); ?>
                </button>
                <button type="button" id="confirm-delete-all" class="button button-danger" disabled>
                    <?php esc_html_e('Delete All Media Files', 'media-wipe'); ?>
                </button>
            </div>
        </div>
    </div>
    <?php
}

// Get media statistics for dashboard with security enhancements
function media_wipe_get_media_statistics() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return array(
            'total' => 0,
            'images' => 0,
            'documents' => 0,
            'videos' => 0,
            'audio' => 0,
            'other' => 0
        );
    }

    global $wpdb;

    $stats = array(
        'total' => 0,
        'images' => 0,
        'documents' => 0,
        'videos' => 0,
        'audio' => 0,
        'other' => 0
    );

    // Use prepared statement for security
    $attachments = $wpdb->get_results($wpdb->prepare("
        SELECT post_mime_type
        FROM {$wpdb->posts}
        WHERE post_type = %s
    ", 'attachment'));

    if ($attachments) {
        foreach ($attachments as $attachment) {
            $stats['total']++;
            $mime_type = sanitize_mime_type($attachment->post_mime_type);

            if (strpos($mime_type, 'image/') === 0) {
                $stats['images']++;
            } elseif (media_wipe_is_document_type($mime_type)) {
                $stats['documents']++;
            } elseif (strpos($mime_type, 'video/') === 0) {
                $stats['videos']++;
            } elseif (strpos($mime_type, 'audio/') === 0) {
                $stats['audio']++;
            } else {
                $stats['other']++;
            }
        }
    }

    return $stats;
}

/**
 * Get document preview HTML for confirmation modal with improved performance
 *
 * @since 1.0.0
 * @return string HTML for document preview
 */
function media_wipe_get_document_preview_html() {
    // Check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return '';
    }

    global $wpdb;

    // Define document MIME types
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
    );

    // Create placeholders for prepared statement
    $placeholders = implode( ',', array_fill( 0, count( $document_types ), '%s' ) );

    // Prepare query parameters
    $query_params = array_merge( array( 'attachment' ), $document_types );

    // Get documents with prepared statement
    $documents = $wpdb->get_results( $wpdb->prepare(
        "SELECT ID, post_title, post_mime_type, guid
        FROM {$wpdb->posts}
        WHERE post_type = %s
        AND post_mime_type IN ($placeholders)
        ORDER BY post_date DESC
        LIMIT 10",
        $query_params
    ) );

    if ( empty( $documents ) ) {
        return '<p class="no-documents">' . esc_html__( 'No document files found.', 'media-wipe' ) . '</p>';
    }

    $html = '<div class="document-preview-grid">';

    foreach ( $documents as $doc ) {
        $file_icon      = media_wipe_get_file_icon( $doc->post_mime_type );
        $file_extension = media_wipe_get_file_extension( $doc->post_mime_type );

        $html .= '<div class="document-item">';
        $html .= '<div class="document-icon">' . $file_icon . '</div>';
        $html .= '<div class="document-info">';
        $html .= '<span class="document-name">' . esc_html( $doc->post_title ) . '</span>';
        $html .= '<span class="document-type">' . esc_html( $file_extension ) . '</span>';
        $html .= '</div>';
        $html .= '</div>';
    }

    // Check if there are more documents
    if ( count( $documents ) >= 10 ) {
        $total_docs = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*)
            FROM {$wpdb->posts}
            WHERE post_type = %s
            AND post_mime_type IN ($placeholders)",
            $query_params
        ) );

        if ( $total_docs > 10 ) {
            $html .= '<div class="document-item more-documents">';
            $html .= '<div class="document-icon">📄</div>';
            $html .= '<div class="document-info">';
            $html .= '<span class="document-name">' . sprintf(
                esc_html__( '+ %d more documents', 'media-wipe' ),
                $total_docs - 10
            ) . '</span>';
            $html .= '</div>';
            $html .= '</div>';
        }
    }

    $html .= '</div>';

    return $html;
}

// Functions media_wipe_get_file_icon() and media_wipe_get_file_extension()
// are now defined in includes/helper-functions.php to avoid duplication

// Handle AJAX request for deleting all media
add_action('wp_ajax_media_wipe_delete_all_media', 'media_wipe_delete_all_media_ajax');

function media_wipe_delete_all_media_ajax() {
    // Set security headers
    media_wipe_set_security_headers();

    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'media_wipe_all_action')) {
        media_wipe_log_security_event('nonce_verification_failed', array(
            'action' => 'delete_all_media',
            'provided_nonce' => isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : 'none'
        ));
        wp_send_json_error(array('message' => esc_html__('Security check failed.', 'media-wipe')));
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        media_wipe_log_security_event('insufficient_permissions', array(
            'action' => 'delete_all_media',
            'user_id' => get_current_user_id()
        ));
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions.', 'media-wipe')));
    }

    // Get settings to check what validations are required
    $settings = media_wipe_get_settings();

    // Verify confirmation text only if required by settings
    if ($settings['require_text_confirmation']) {
        $confirmation = isset($_POST['confirmation']) ? sanitize_text_field($_POST['confirmation']) : '';
        if ($confirmation !== 'DELETE ALL MEDIA') {
            media_wipe_log_security_event('invalid_confirmation', array(
                'action' => 'delete_all_media',
                'provided_confirmation' => $confirmation
            ));
            wp_send_json_error(array('message' => esc_html__('Confirmation text does not match.', 'media-wipe')));
        }
    }

    // Check rate limiting
    if (!media_wipe_check_rate_limit('delete_all', 1)) {
        media_wipe_log_security_event('rate_limit_exceeded', array(
            'action' => 'delete_all_media'
        ));
        wp_send_json_error(array('message' => esc_html__('Too many deletion requests. Please wait 1 hour before trying again.', 'media-wipe')));
    }

    // Log the deletion attempt
    media_wipe_log_activity('delete_all_attempt', array(
        'confirmation_provided' => true
    ));

    // Perform the deletion
    $result = media_wipe_delete_all_media();

    if ($result['success']) {
        // Log successful deletion
        media_wipe_log_activity('delete_all_success', array(
            'deleted_count' => $result['deleted_count']
        ));

        wp_send_json_success(array(
            'message' => sprintf(esc_html__('Successfully deleted %d media files.', 'media-wipe'), $result['deleted_count'])
        ));
    } else {
        // Log failed deletion
        media_wipe_log_activity('delete_all_failed', array(
            'error_message' => $result['message'],
            'deleted_count' => $result['deleted_count'],
            'error_count' => count($result['errors'])
        ));

        wp_send_json_error(array('message' => $result['message']));
    }
}

/**
 * Delete all media files with enhanced error handling and performance optimization
 *
 * @since 1.0.0
 * @return array Result array with success status, counts, and messages
 */
function media_wipe_delete_all_media() {
    // Verify user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return array(
            'success'       => false,
            'deleted_count' => 0,
            'errors'        => array(),
            'message'       => esc_html__( 'Insufficient permissions.', 'media-wipe' ),
        );
    }

    // Increase time limit for large operations
    if ( ! ini_get( 'safe_mode' ) ) {
        set_time_limit( 300 ); // 5 minutes
    }

    // Get all media attachments with optimized query
    $attachments = get_posts( array(
        'post_type'      => 'attachment',
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ) );

    if ( empty( $attachments ) ) {
        return array(
            'success'       => true,
            'deleted_count' => 0,
            'errors'        => array(),
            'message'       => esc_html__( 'No media files found to delete.', 'media-wipe' ),
        );
    }

    $deleted_count = 0;
    $errors        = array();
    $total_files   = count( $attachments );

    // Process deletions in batches to prevent memory issues
    $batch_size = apply_filters( 'media_wipe_delete_batch_size', 50 );
    $batches    = array_chunk( $attachments, $batch_size );

    foreach ( $batches as $batch ) {
        foreach ( $batch as $attachment_id ) {
            // Additional security check
            if ( ! current_user_can( 'delete_post', $attachment_id ) ) {
                $errors[] = array(
                    'id'    => $attachment_id,
                    'error' => esc_html__( 'Insufficient permissions to delete this file.', 'media-wipe' ),
                );
                continue;
            }

            // Attempt to delete the attachment
            $result = wp_delete_attachment( $attachment_id, true );
            if ( $result ) {
                $deleted_count++;

                // Clear any related caches
                wp_cache_delete( 'media_wipe_info_' . $attachment_id, 'media_wipe' );
            } else {
                $errors[] = array(
                    'id'    => $attachment_id,
                    'error' => esc_html__( 'Failed to delete file.', 'media-wipe' ),
                );
            }
        }

        // Allow other processes to run between batches
        if ( function_exists( 'wp_suspend_cache_addition' ) ) {
            wp_suspend_cache_addition( false );
        }
    }

    // Prepare result
    if ( empty( $errors ) ) {
        return array(
            'success'       => true,
            'deleted_count' => $deleted_count,
            'errors'        => array(),
            'message'       => sprintf(
                esc_html__( 'Successfully deleted %d media files.', 'media-wipe' ),
                $deleted_count
            ),
        );
    } else {
        $success = $deleted_count > 0;
        return array(
            'success'       => $success,
            'deleted_count' => $deleted_count,
            'errors'        => $errors,
            'message'       => sprintf(
                esc_html__( 'Deleted %d of %d files. %d files could not be deleted.', 'media-wipe' ),
                $deleted_count,
                $total_files,
                count( $errors )
            ),
        );
    }
}
