<?php
/**
 * Plugin Name: Media Wipe
 * Description: A plugin to delete all media files or unused media files with one click.
 * Version: 1.0.1
 * Author: Md. Nahid Hasan
 * Author URI: https://mdnahidhasan.netlify.app
 * Text Domain: media-wipe
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include feature files
require_once plugin_dir_path(__FILE__) . 'includes/delete-all-media.php';
require_once plugin_dir_path(__FILE__) . 'includes/delete-selected-media.php';

// Add Settings link to the plugin action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'media_wipe_add_settings_link');

function media_wipe_add_settings_link($links)
{
    $settings_link = '<a href="' . esc_url(admin_url('upload.php?page=media-wipe')) . '">' . __('Settings', 'media-wipe') . '</a>';
    array_unshift($links, $settings_link); // Add to the beginning of the links array
    return $links;
}