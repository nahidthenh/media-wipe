<?php

/**
 * Plugin Name: Media Wipe
 * Description: A simple plugin to delete all media files with one click.
 * Version: 1.0
 * Author: Md. Nahid Hasan
 * Author URI: https://mdnahidhasan.netlify.app
 * Text Domain: media-wipe
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Admin menu setup
add_action('admin_menu', 'media_wipe_menu');

function media_wipe_menu()
{
    add_media_page(
        'Media Wipe',
        'Media Wipe',
        'manage_options',
        'media-wipe',
        'media_wipe_page'
    );
}

// Display the plugin page
function media_wipe_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['delete_media']) && check_admin_referer('media_wipe_action', 'media_wipe_nonce')) {
        media_wipe_delete_all();
    }

?>
    <div class="wrap">
        <h1>Media Wipe</h1>
        <form method="post">
            <?php wp_nonce_field('media_wipe_action', 'media_wipe_nonce'); ?>
            <p>
                <strong>Warning:</strong> This action will permanently delete all media files from your library.
            </p>
            <input type="submit" name="delete_media" class="button button-primary" value="Delete All Media" onclick="return confirm('Are you sure you want to delete all media? This action is irreversible.');">
        </form>
    </div>
<?php
}

// Function to delete all media files
function media_wipe_delete_all()
{
    global $wpdb;

    $media_query = new WP_Query([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
    ]);

    if ($media_query->have_posts()) {
        while ($media_query->have_posts()) {
            $media_query->the_post();
            $media_id = get_the_ID();
            wp_delete_attachment($media_id, true);
        }
        wp_reset_postdata();
        echo '<div class="updated"><p>All media files have been deleted successfully.</p></div>';
    } else {
        echo '<div class="updated"><p>No media files found to delete.</p></div>';
    }
}
