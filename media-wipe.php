<?php
/**
 * Plugin Name: Media Wipe
 * Plugin URI: https://mdnahidhasan.netlify.app/media-wipe
 * Description: A comprehensive WordPress plugin to safely delete media files with advanced confirmation systems, document preview, and security audit logging.
 * Version: 1.1.2
 * Author: Md. Nahid Hasan
 * Author URI: https://mdnahidhasan.netlify.app
 * Text Domain: media-wipe
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package MediaWipe
 * @author Md. Nahid Hasan
 * @copyright 2025 Md. Nahid Hasan
 * @license GPL-2.0-or-later
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct access denied.' );
}

/**
 * Define plugin constants
 */
if ( ! defined( 'MEDIA_WIPE_VERSION' ) ) {
    define( 'MEDIA_WIPE_VERSION', '1.1.2' );
}

if ( ! defined( 'MEDIA_WIPE_PLUGIN_FILE' ) ) {
    define( 'MEDIA_WIPE_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'MEDIA_WIPE_PLUGIN_URL' ) ) {
    define( 'MEDIA_WIPE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'MEDIA_WIPE_PLUGIN_PATH' ) ) {
    define( 'MEDIA_WIPE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'MEDIA_WIPE_PLUGIN_BASENAME' ) ) {
    define( 'MEDIA_WIPE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * Main plugin class
 */
class Media_Wipe_Plugin {

    /**
     * Plugin instance
     *
     * @var Media_Wipe_Plugin
     */
    private static $instance = null;

    /**
     * Get plugin instance
     *
     * @return Media_Wipe_Plugin
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize the plugin
     */
    private function init() {
        // Load text domain for translations
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        // Initialize plugin components
        add_action( 'init', array( $this, 'init_components' ) );

        // Register activation and deactivation hooks
        register_activation_hook( MEDIA_WIPE_PLUGIN_FILE, array( $this, 'activate' ) );
        register_deactivation_hook( MEDIA_WIPE_PLUGIN_FILE, array( $this, 'deactivate' ) );

        // Add admin enqueue scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Load plugin text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'media-wipe',
            false,
            dirname( MEDIA_WIPE_PLUGIN_BASENAME ) . '/languages'
        );
    }

    /**
     * Initialize plugin components
     */
    public function init_components() {
        // Include necessary files
        $this->include_files();
    }

    /**
     * Include required files
     */
    private function include_files() {
        $includes = array(
            'includes/helper-functions.php',
            'includes/class-datatable.php',
            'includes/class-notifications.php',
            'includes/admin-menu.php',
            'includes/delete-all-media.php',
            'includes/delete-selected-media.php'
        );

        foreach ( $includes as $file ) {
            $file_path = MEDIA_WIPE_PLUGIN_PATH . $file;
            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            } else {
                error_log( sprintf( 'Media Wipe: Required file not found: %s', $file_path ) );
            }
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $default_settings = array(
            'require_backup_confirmation' => 1,
            'require_text_confirmation' => 1,
            'show_document_preview' => 1,
            'enable_logging' => 1
        );

        add_option( 'media_wipe_settings', $default_settings );
        add_option( 'media_wipe_version', MEDIA_WIPE_VERSION );

        // Log activation
        if ( function_exists( 'media_wipe_log_activity' ) ) {
            media_wipe_log_activity( 'plugin_activated', array(
                'version' => MEDIA_WIPE_VERSION
            ) );
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear transients
        delete_transient( 'media_wipe_rate_limit_' . get_current_user_id() . '_delete_selected' );
        delete_transient( 'media_wipe_rate_limit_' . get_current_user_id() . '_delete_all' );
        delete_transient( 'media_wipe_rate_limit_' . get_current_user_id() . '_fetch_media' );

        // Log deactivation
        if ( function_exists( 'media_wipe_log_activity' ) ) {
            media_wipe_log_activity( 'plugin_deactivated', array(
                'version' => MEDIA_WIPE_VERSION
            ) );
        }
    }

    /**
     * Enqueue admin assets (CSS and JavaScript)
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on Media Wipe pages
        if ( strpos( $hook, 'media-wipe' ) === false && strpos( $hook, 'toplevel_page_media-wipe' ) === false ) {
            return;
        }

        // Enqueue CSS and JS files with proper versioning
        wp_enqueue_style(
            'media-wipe-admin-style',
            MEDIA_WIPE_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            MEDIA_WIPE_VERSION
        );

        wp_enqueue_script(
            'media-wipe-admin-script',
            MEDIA_WIPE_PLUGIN_URL . 'assets/js/admin-script.js',
            array( 'jquery' ),
            MEDIA_WIPE_VERSION,
            true
        );

        // Enqueue DataTables.net library for delete selected media page
        if (strpos($hook, 'delete-selected') !== false) {
            // DataTables CSS
            wp_enqueue_style(
                'datatables-css',
                'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css',
                array(),
                '1.13.7'
            );

            // DataTables JS
            wp_enqueue_script(
                'datatables-js',
                'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
                array( 'jquery' ),
                '1.13.7',
                true
            );

            // Custom DataTable script
            wp_enqueue_script(
                'media-wipe-datatable',
                MEDIA_WIPE_PLUGIN_URL . 'assets/js/datatable.js',
                array( 'jquery', 'datatables-js' ),
                MEDIA_WIPE_VERSION,
                true
            );
        }

        // Enqueue notifications script
        wp_enqueue_script(
            'media-wipe-notifications',
            MEDIA_WIPE_PLUGIN_URL . 'assets/js/notifications.js',
            array( 'jquery' ),
            MEDIA_WIPE_VERSION,
            true
        );

        // Localize script for AJAX with proper nonce
        wp_localize_script( 'media-wipe-admin-script', 'mediaWipeAjax', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'media_wipe_ajax_nonce' ),
            'strings' => array(
                'confirmDelete'    => __( 'Are you sure you want to delete the selected media files?', 'media-wipe' ),
                'noMediaSelected'  => __( 'Please select media to delete.', 'media-wipe' ),
                'deletingFiles'    => __( 'Deleting files...', 'media-wipe' ),
                'networkError'     => __( 'Network error occurred. Please try again.', 'media-wipe' ),
                'deleteSuccess'    => __( 'Media files have been deleted successfully.', 'media-wipe' ),
                'deleteError'      => __( 'An error occurred during deletion.', 'media-wipe' ),
            ),
        ) );
    }
}

/**
 * Initialize the plugin
 */
function media_wipe_init() {
    return Media_Wipe_Plugin::get_instance();
}

// Initialize the plugin
media_wipe_init();
