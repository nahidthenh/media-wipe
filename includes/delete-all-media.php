<?php
// Add submenu for "Delete All Media"
add_action('admin_menu', 'media_wipe_all_menu');
function media_wipe_all_menu() {
    add_submenu_page(
        'upload.php',
        __('Delete All Media', 'media-wipe'),
        __('Delete All Media', 'media-wipe'),
        'manage_options',
        'media-wipe-all',
        'media_wipe_all_media_page'
    );
}

// Display the "Delete All Media" page
function media_wipe_all_media_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Delete All Media Files', 'media-wipe'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('media_wipe_all_action', 'media_wipe_all_nonce'); ?>
            <p><?php esc_html_e('This action will permanently delete all media files in your library.', 'media-wipe'); ?></p>
            <input type="submit" name="delete_all_media" class="button button-primary" value="<?php esc_attr_e('Delete All Media', 'media-wipe'); ?>" />
        </form>
    </div>
    <?php

    if (isset($_POST['delete_all_media']) && check_admin_referer('media_wipe_all_action', 'media_wipe_all_nonce')) {
        media_wipe_delete_all_media();
    }
}

// Delete all media
function media_wipe_delete_all_media() {
    // Get all media attachments
    $attachments = get_posts( array(
        'post_type' => 'attachment',
        'posts_per_page' => -1,
        'post_status' => 'any',
    ) );

    // Delete each attachment
    foreach ($attachments as $attachment) {
        wp_delete_attachment($attachment->ID, true);
    }

    // Show success notice
    add_action('admin_notices', function () {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('All media files have been deleted.', 'media-wipe') . '</p></div>';
    });
}
