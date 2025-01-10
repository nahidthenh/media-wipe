<?php
// Add submenu for "Delete Selected Media"
add_action('admin_menu', 'media_wipe_unused_menu');
function media_wipe_unused_menu() {
    add_submenu_page(
        'upload.php',
        __('Delete Selected Media', 'media-wipe'),
        __('Delete Selected Media', 'media-wipe'),
        'manage_options',
        'media-wipe-unused',
        'media_wipe_unused_media_page'
    );
}

// Display the "Delete Selected Media" page
function media_wipe_unused_media_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Fetch unused media if the "Fetch All Media" button is clicked, else fetch none
    if (isset($_POST['fetch_all_media'])) {
        $unused_media = media_wipe_get_all_media();
    } else {
        $unused_media = media_wipe_get_unused_media();
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Delete Selected Media', 'media-wipe'); ?></h1>
        <form method="post" id="media-wipe-form">
            <?php wp_nonce_field('media_wipe_unused_action', 'media_wipe_unused_nonce'); ?>
            <p><?php esc_html_e('Warning! Deleted files will be permanently removed', 'media-wipe'); ?></p>
           
            <table id="media-wipe-unused-table" class="widefat fixed">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Select', 'media-wipe'); ?></th>
                        <th><?php esc_html_e('File Name', 'media-wipe'); ?></th>
                        <th><?php esc_html_e('Preview', 'media-wipe'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($unused_media)) : ?>
                        <?php foreach ($unused_media as $media) : ?>
                            <tr>
                                <td><input type="checkbox" name="delete_media[]" value="<?php echo esc_attr($media->ID); ?>"></td>
                                <td><?php echo esc_html($media->post_title); ?></td>
                                <td><img src="<?php echo esc_url($media->guid); ?>" style="width:50px;height:auto;"></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="3"><?php esc_html_e('No unused media found.', 'media-wipe'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <p>
                <button type="button" id="open-delete-modal" class="media-wipe-btn button button-danger"><?php esc_attr_e('Delete Selected Media', 'media-wipe'); ?></button>
                <input type="submit" name="fetch_all_media" class="media-wipe-btn button button-primary" value="<?php esc_attr_e('Fetch All Media', 'media-wipe'); ?>" />
            </p>
        </form>
    </div>

 <!-- Modal -->
<div id="delete-confirmation-modal" style="display:none;">
    <div class="modal-content">
        <p>Are you sure you want to delete the selected media files?</p>
        <button type="button" id="delete-confirmation-yes">Yes</button>
        <button type="button" id="delete-confirmation-no">No</button>
    </div>
</div>


    <?php
}

// Fetch all media (both used and unused)
function media_wipe_get_all_media() {
    global $wpdb;

    $all_media = $wpdb->get_results("
        SELECT ID, post_title, guid
        FROM {$wpdb->posts}
        WHERE post_type = 'attachment'
    ");

    return $all_media;
}

// Fetch unused media
function media_wipe_get_unused_media() {
    global $wpdb;

    $unused_media = $wpdb->get_results("
        SELECT ID, post_title, guid
        FROM {$wpdb->posts}
        WHERE post_type = 'attachment'
        AND post_parent = 0
    ");

    return $unused_media;
}

// Handle the AJAX request for deleting selected media
add_action('wp_ajax_media_wipe_delete_unused_media', 'media_wipe_delete_unused_media_ajax');

function media_wipe_delete_unused_media_ajax() {
    // Verify the nonce for security (optional)
    if (!isset($_POST['media_ids']) || !is_array($_POST['media_ids']) || empty($_POST['media_ids'])) {
        wp_send_json_error(array('message' => 'No media selected.'));
    }

    // Get the media IDs from the request
    $media_ids = $_POST['media_ids'];

    // Call the delete function
    media_wipe_delete_unused_media($media_ids);

    // Send a success response (no WordPress notices)
    wp_send_json_success(array('message' => 'Selected media deleted.'));
}


// The actual delete function
function media_wipe_delete_unused_media($media_ids) {
    if (!is_array($media_ids) || empty($media_ids)) {
        return;
    }

    foreach ($media_ids as $media_id) {
        wp_delete_attachment($media_id, true);
    }
}
