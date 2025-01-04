<?php

/**
 * Plugin Name: Media Wipe
 * Description: A simple plugin to delete all media files with one click.
 * Version: 1.0
 * Author: Md. Nahid Hasan
 * Author URI: https://mdnahidhasan.netlify.app
 * Text Domain: media-wipe
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Admin menu setup
add_action('admin_menu', 'media_wipe_menu');

function media_wipe_menu()
{
    add_media_page(
        __('Media Wipe', 'media-wipe'),
        __('Media Wipe', 'media-wipe'),
        'manage_options',
        'media-wipe',
        'media_wipe_page'
    );
}

// Add Settings link to plugin actions
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'media_wipe_settings_link');

function media_wipe_settings_link($links)
{
    $settings_link = '<a href="' . esc_url(admin_url('upload.php?page=media-wipe')) . '">' . __('Settings', 'media-wipe') . '</a>';
    array_unshift($links, $settings_link); // Add to the beginning of the links array
    return $links;
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
        <h1><?php esc_html_e('Media Wipe', 'media-wipe'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('media_wipe_action', 'media_wipe_nonce'); ?>
            <p>
                <strong><?php esc_html_e('Warning:', 'media-wipe'); ?></strong>
                <?php esc_html_e('This action will permanently delete all media files from your library.', 'media-wipe'); ?>
            </p>
            <input type="submit" name="delete_media" class="button button-primary" value="<?php esc_attr_e('Delete All Media', 'media-wipe'); ?>"
                onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete all media? This action is irreversible.', 'media-wipe')); ?>');">
        </form>
    </div>
<?php
}

// Function to delete all media files
function media_wipe_delete_all()
{
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
        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('All media files have been deleted successfully.', 'media-wipe') . '</p></div>';
        });
    } else {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__('No media files found to delete.', 'media-wipe') . '</p></div>';
        });
    }
}