<?php
/**
 * Plugin Name: Media Wipe
 * Description: A plugin to delete all media files or unused media files with one click.
 * Version: 1.0.2
 * Author: Md. Nahid Hasan
 * Author URI: https://mdnahidhasan.netlify.app
 * Text Domain: media-wipe
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

 
// Prevent direct access

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'includes/delete-all-media.php';
include_once plugin_dir_path(__FILE__) . 'includes/delete-selected-media.php';

// Register plugin constants (optional)
define( 'MEDIA_WIPE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MEDIA_WIPE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

// Enqueue Styles and Scripts
add_action('admin_enqueue_scripts', 'media_wipe_enqueue_assets');
function media_wipe_enqueue_assets() {
    // Correctly enqueuing the CSS and JS files
    wp_enqueue_style('media-wipe-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
    wp_enqueue_script('media-wipe-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', array('jquery'), null, true);
}
