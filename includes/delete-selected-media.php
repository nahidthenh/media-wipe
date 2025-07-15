<?php
/**
 * Delete Selected Media Functionality
 *
 * This file handles the "Delete Selected Media" feature of the Media Wipe plugin.
 *
 * @package MediaWipe
 * @since 1.0.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Display the "Delete Selected Media" page
function media_wipe_unused_media_page() {
    // Set security headers
    media_wipe_set_security_headers();

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'media-wipe'));
    }

    // Handle form submission for deleting selected media
    if (isset($_POST['delete_selected_media']) && wp_verify_nonce($_POST['media_wipe_delete_selected_nonce'], 'media_wipe_delete_selected_action')) {
        $selected_ids = isset($_POST['selected_media_ids']) ? sanitize_text_field($_POST['selected_media_ids']) : '';

        if (!empty($selected_ids)) {
            $media_ids = explode(',', $selected_ids);
            $deleted_count = 0;

            foreach ($media_ids as $media_id) {
                $media_id = intval($media_id);
                if ($media_id > 0) {
                    $deleted = wp_delete_attachment($media_id, true);
                    if ($deleted) {
                        $deleted_count++;
                        // Log the deletion
                        media_wipe_log_activity('delete_selected', array(
                            'media_id' => $media_id,
                            'user_id' => get_current_user_id(),
                            'timestamp' => current_time('mysql')
                        ));
                    }
                }
            }

            if ($deleted_count > 0) {
                echo '<div class="notice notice-success is-dismissible"><p>' .
                     sprintf(esc_html__('Successfully deleted %d media files.', 'media-wipe'), $deleted_count) .
                     '</p></div>';
            }
        }
    }

    // Handle form submission with proper nonce verification
    $unused_media = array();
    if (isset($_POST['fetch_all_media'])) {
        // Verify nonce for form submission
        if (!wp_verify_nonce($_POST['media_wipe_unused_nonce'], 'media_wipe_unused_action')) {
            wp_die(esc_html__('Security check failed.', 'media-wipe'));
        }
        $unused_media = media_wipe_get_all_media();
    }

    // Get all media for DataTable
    $all_media = media_wipe_get_all_media();

    ?>
    <div class="wrap media-wipe-full-screen">
        <h1 class="wp-heading-inline">
            <?php esc_html_e('Delete Selected Media', 'media-wipe'); ?>
        </h1>

        <div class="media-wipe-delete-selected">
            <!-- Enhanced Description -->
            <div class="page-description">
                <h2><?php esc_html_e('Media Library Management', 'media-wipe'); ?></h2>
                <p><?php esc_html_e('Select specific media files to delete from your library. Use the search and filter options to find files quickly.', 'media-wipe'); ?></p>
                <div class="safety-notice">
                    <h3><?php esc_html_e('⚠️ Warning', 'media-wipe'); ?></h3>
                    <p><?php esc_html_e('Deleted files will be permanently removed and cannot be recovered. Please ensure you have backups before proceeding.', 'media-wipe'); ?></p>
                </div>
            </div>

            <!-- Professional DataTable -->
            <div class="datatable-container">
                <div class="datatable-header">
                    <h3><?php esc_html_e('Media Files', 'media-wipe'); ?></h3>
                    <div class="datatable-controls">
                        <button type="button" id="select-all-btn" class="button button-secondary">
                            <?php esc_html_e('Select All', 'media-wipe'); ?>
                        </button>
                        <button type="button" id="select-none-btn" class="button button-secondary">
                            <?php esc_html_e('Select None', 'media-wipe'); ?>
                        </button>
                        <button type="button" id="delete-selected-btn" class="button button-primary" disabled>
                            <span class="dashicons dashicons-trash"></span>
                            <?php esc_html_e('Delete Selected', 'media-wipe'); ?>
                        </button>
                    </div>
                </div>

                <table id="media-datatable" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Select', 'media-wipe'); ?></th>
                            <th><?php esc_html_e('Preview', 'media-wipe'); ?></th>
                            <th><?php esc_html_e('Title', 'media-wipe'); ?></th>
                            <th><?php esc_html_e('Type', 'media-wipe'); ?></th>
                            <th><?php esc_html_e('Size', 'media-wipe'); ?></th>
                            <th><?php esc_html_e('Date', 'media-wipe'); ?></th>
                            <th><?php esc_html_e('Actions', 'media-wipe'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($all_media)) : ?>
                            <?php foreach ($all_media as $media) :
                                $media_info = media_wipe_get_media_info($media->ID);
                                $is_document = media_wipe_is_document_type($media_info['mime_type']);
                            ?>
                                <tr data-media-id="<?php echo esc_attr($media->ID); ?>">
                                    <td>
                                        <input type="checkbox" class="media-checkbox" value="<?php echo esc_attr($media->ID); ?>" />
                                    </td>
                                    <td>
                                        <?php if ($is_document): ?>
                                            <div class="document-preview">
                                                <span class="file-icon"><?php echo media_wipe_get_file_icon($media_info['mime_type']); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <img src="<?php echo esc_url($media->guid); ?>" class="media-thumbnail" alt="<?php echo esc_attr($media->post_title); ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo esc_html($media->post_title); ?></strong>
                                    </td>
                                    <td>
                                        <span class="file-type-badge"><?php echo esc_html(media_wipe_get_file_extension($media_info['mime_type'])); ?></span>
                                    </td>
                                    <td data-order="<?php echo esc_attr($media_info['file_size']); ?>">
                                        <?php echo esc_html($media_info['file_size_formatted']); ?>
                                    </td>
                                    <td data-order="<?php echo esc_attr(strtotime($media->post_date)); ?>">
                                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($media->post_date))); ?>
                                    </td>
                                    <td>
                                        <button type="button" class="button button-small delete-single" data-media-id="<?php echo esc_attr($media->ID); ?>">
                                            <?php esc_html_e('Delete', 'media-wipe'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Hidden form for actual deletion -->
        <form id="delete-selected-form" method="post" style="display: none;">
            <?php wp_nonce_field('media_wipe_delete_selected_action', 'media_wipe_delete_selected_nonce'); ?>
            <input type="hidden" name="delete_selected_media" value="1" />
            <input type="hidden" name="selected_media_ids" id="selected-media-ids" value="" />
        </form>
    </div>

 <!-- Enhanced Confirmation Modal for Selected Media -->
<div id="delete-confirmation-modal" class="media-wipe-modal" style="display:none;">
    <div class="modal-overlay"></div>
    <div class="modal-content-medium">
        <div class="modal-header">
            <h2><?php esc_html_e('Confirm Deletion of Selected Media', 'media-wipe'); ?></h2>
            <button type="button" class="modal-close" id="close-selected-modal">&times;</button>
        </div>

        <div class="modal-body">
            <div class="confirmation-warning">
                <div class="warning-icon-large">⚠️</div>
                <p class="warning-text"><?php esc_html_e('You are about to permanently delete the selected media files.', 'media-wipe'); ?></p>
            </div>

            <div class="selected-files-summary">
                <h4><?php esc_html_e('Selected files for deletion:', 'media-wipe'); ?></h4>
                <div id="selected-files-list" class="selected-files-container">
                    <!-- Dynamically populated by JavaScript -->
                </div>
            </div>

            <div class="deletion-impact-notice">
                <h4><?php esc_html_e('Impact of deletion:', 'media-wipe'); ?></h4>
                <ul>
                    <li><?php esc_html_e('Files will be permanently removed from server', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Database entries will be deleted', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Content using these files may be affected', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('This action cannot be undone', 'media-wipe'); ?></li>
                </ul>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" id="delete-confirmation-no" class="button button-secondary">
                <?php esc_html_e('Cancel', 'media-wipe'); ?>
            </button>
            <button type="button" id="delete-confirmation-yes" class="button button-danger">
                <?php esc_html_e('Delete Selected Files', 'media-wipe'); ?>
            </button>
        </div>
    </div>
</div>


    <?php
}

// Function media_wipe_get_all_media() is now defined in includes/helper-functions.php

// Function media_wipe_get_unused_media() is now defined in includes/helper-functions.php

// Handle the AJAX request for deleting selected media
add_action('wp_ajax_media_wipe_delete_unused_media', 'media_wipe_delete_unused_media_ajax');

function media_wipe_delete_unused_media_ajax() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'media_wipe_ajax_nonce')) {
        wp_send_json_error(array('message' => esc_html__('Security check failed.', 'media-wipe')));
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions.', 'media-wipe')));
    }

    // Validate and sanitize input
    if (!isset($_POST['media_ids']) || !is_array($_POST['media_ids']) || empty($_POST['media_ids'])) {
        wp_send_json_error(array('message' => esc_html__('No media selected.', 'media-wipe')));
    }

    // Sanitize and validate media IDs
    $media_ids = media_wipe_validate_media_ids($_POST['media_ids']);

    if (empty($media_ids)) {
        wp_send_json_error(array('message' => esc_html__('Invalid media selection.', 'media-wipe')));
    }

    // Check rate limiting
    if (!media_wipe_check_rate_limit('delete_selected', count($media_ids))) {
        wp_send_json_error(array('message' => esc_html__('Too many deletion requests. Please wait 1 hour before trying again.', 'media-wipe')));
    }

    // Log the deletion attempt
    media_wipe_log_activity('delete_selected_attempt', array(
        'media_count' => count($media_ids),
        'media_ids' => $media_ids
    ));

    // Call the delete function
    $result = media_wipe_delete_unused_media($media_ids);

    if ($result['success']) {
        // Log successful deletion
        media_wipe_log_activity('delete_selected_success', array(
            'deleted_count' => $result['deleted_count'],
            'failed_count' => count($result['errors'])
        ));

        wp_send_json_success(array(
            'message' => sprintf(esc_html__('Successfully deleted %d media files.', 'media-wipe'), $result['deleted_count'])
        ));
    } else {
        wp_send_json_error(array('message' => $result['message']));
    }
}


// The actual delete function (enhanced with error handling)
function media_wipe_delete_unused_media($media_ids) {
    if (!is_array($media_ids) || empty($media_ids)) {
        return array(
            'success' => false,
            'message' => esc_html__('No media IDs provided.', 'media-wipe'),
            'deleted_count' => 0,
            'errors' => array()
        );
    }

    $deleted_count = 0;
    $errors = array();

    foreach ($media_ids as $media_id) {
        // Additional security check - ensure user can delete this specific attachment
        if (!current_user_can('delete_post', $media_id)) {
            $errors[] = array(
                'id' => $media_id,
                'error' => esc_html__('Insufficient permissions to delete this file.', 'media-wipe')
            );
            continue;
        }

        // Verify the attachment exists and is actually an attachment
        $attachment = get_post($media_id);
        if (!$attachment || $attachment->post_type !== 'attachment') {
            $errors[] = array(
                'id' => $media_id,
                'error' => esc_html__('File not found or invalid.', 'media-wipe')
            );
            continue;
        }

        // Attempt to delete the attachment
        $result = wp_delete_attachment($media_id, true);
        if ($result) {
            $deleted_count++;
        } else {
            $errors[] = array(
                'id' => $media_id,
                'error' => esc_html__('Failed to delete file.', 'media-wipe')
            );
        }
    }

    if (empty($errors)) {
        return array(
            'success' => true,
            'deleted_count' => $deleted_count,
            'errors' => array(),
            'message' => sprintf(esc_html__('Successfully deleted %d media files.', 'media-wipe'), $deleted_count)
        );
    } else {
        return array(
            'success' => $deleted_count > 0,
            'deleted_count' => $deleted_count,
            'errors' => $errors,
            'message' => sprintf(
                esc_html__('Deleted %d files successfully. %d files could not be deleted.', 'media-wipe'),
                $deleted_count,
                count($errors)
            )
        );
    }
}
