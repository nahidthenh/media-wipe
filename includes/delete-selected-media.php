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
                                <td><input type="checkbox" name="delete_media[]" value="<?php echo esc_attr($media->ID); ?>" class="media-checkbox"></td>
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
                <input type="submit" name="fetch_all_media" class="media-wipe-btn button button-primary" value="<?php esc_attr_e('Fetch All Media', 'media-wipe'); ?>" />
                <button type="button" id="delete-media-btn" class="media-wipe-btn button button-danger"><?php esc_attr_e('Delete Selected Media', 'media-wipe'); ?></button>
            </p>
        </form>
    </div>

    <!-- Modal HTML -->
    <div id="deleteModal" style="display:none;" class="media-wipe-modal">
        <div class="media-wipe-modal-content">
            <span class="media-wipe-close-btn">&times;</span>
            <h2><?php esc_html_e('Are you sure you want to delete the selected media?', 'media-wipe'); ?></h2>
            <p><?php esc_html_e('This action cannot be undone.', 'media-wipe'); ?></p>
            <button id="confirm-delete" class="button button-danger"><?php esc_html_e('Yes, Delete', 'media-wipe'); ?></button>
            <button id="cancel-delete" class="button"><?php esc_html_e('Cancel', 'media-wipe'); ?></button>
        </div>
    </div>

    <?php

    // Handle the deletion of selected media
    if (isset($_POST['delete_unused_media']) && check_admin_referer('media_wipe_unused_action', 'media_wipe_unused_nonce')) {
        media_wipe_delete_unused_media($_POST['delete_media']);
    }
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

// Delete selected media
function media_wipe_delete_unused_media($media_ids) {
    if (!is_array($media_ids) || empty($media_ids)) {
        return;
    }

    foreach ($media_ids as $media_id) {
        wp_delete_attachment($media_id, true);
    }

    add_action('admin_notices', function () {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Selected unused media files have been deleted.', 'media-wipe') . '</p></div>';
    });
}
