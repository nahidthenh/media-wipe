<?php
/**
 * Admin Menu Management for Media Wipe Plugin
 * 
 * This file handles the creation of the main admin menu and submenus
 * for the Media Wipe plugin in the WordPress admin dashboard.
 * 
 * @package MediaWipe
 * @since 1.0.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the main Media Wipe admin menu and submenus
 */
add_action('admin_menu', 'media_wipe_admin_menu');

function media_wipe_admin_menu() {
    // Add main menu page
    add_menu_page(
        __('Media Wipe', 'media-wipe'),                    // Page title
        __('Media Wipe', 'media-wipe'),                    // Menu title
        'manage_options',                                   // Capability
        'media-wipe',                                      // Menu slug
        'media_wipe_dashboard_page',                       // Function
        'dashicons-trash',                                 // Icon (WordPress dashicon)
        58                                                 // Position (after Media menu)
    );

    // Add Dashboard submenu (rename the first submenu)
    add_submenu_page(
        'media-wipe',                                      // Parent slug
        __('Dashboard', 'media-wipe'),                     // Page title
        __('Dashboard', 'media-wipe'),                     // Menu title
        'manage_options',                                  // Capability
        'media-wipe',                                      // Menu slug (same as parent)
        'media_wipe_dashboard_page'                        // Function
    );

    // Add Delete All Media submenu
    add_submenu_page(
        'media-wipe',                                      // Parent slug
        __('Delete All Media', 'media-wipe'),              // Page title
        __('Delete All Media', 'media-wipe'),              // Menu title
        'manage_options',                                  // Capability
        'media-wipe-delete-all',                          // Menu slug
        'media_wipe_all_media_page'                       // Function
    );

    // Add Delete Selected Media submenu
    add_submenu_page(
        'media-wipe',                                      // Parent slug
        __('Delete Selected Media', 'media-wipe'),         // Page title
        __('Delete Selected Media', 'media-wipe'),         // Menu title
        'manage_options',                                  // Capability
        'media-wipe-delete-selected',                     // Menu slug
        'media_wipe_unused_media_page'                    // Function
    );

    // Add Settings submenu
    add_submenu_page(
        'media-wipe',                                      // Parent slug
        __('Settings', 'media-wipe'),                      // Page title
        __('Settings', 'media-wipe'),                      // Menu title
        'manage_options',                                  // Capability
        'media-wipe-settings',                            // Menu slug
        'media_wipe_settings_page'                        // Function
    );

    // Add Security Audit submenu
    add_submenu_page(
        'media-wipe',                                      // Parent slug
        __('Security Audit', 'media-wipe'),               // Page title
        __('Security Audit', 'media-wipe'),               // Menu title
        'manage_options',                                  // Capability
        'media-wipe-security',                            // Menu slug
        'media_wipe_security_page'                        // Function
    );

    // Add Help & Support submenu
    add_submenu_page(
        'media-wipe',                                      // Parent slug
        __('Help & Support', 'media-wipe'),               // Page title
        __('Help & Support', 'media-wipe'),               // Menu title
        'manage_options',                                  // Capability
        'media-wipe-help',                                // Menu slug
        'media_wipe_help_page'                            // Function
    );
}

/**
 * Dashboard page content
 */
function media_wipe_dashboard_page() {
    // Set security headers
    media_wipe_set_security_headers();

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'media-wipe'));
    }

    // Get media statistics with error handling
    $media_stats = function_exists('media_wipe_get_media_statistics') ? media_wipe_get_media_statistics() : array(
        'total' => 0,
        'images' => 0,
        'documents' => 0,
        'videos' => 0,
        'audio' => 0,
        'other' => 0
    );

    // Get recent activity with error handling
    $recent_activity = function_exists('media_wipe_get_recent_activity') ? media_wipe_get_recent_activity() : array();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">
            <?php esc_html_e('Media Wipe Dashboard', 'media-wipe'); ?>
        </h1>
        
        <div class="media-wipe-dashboard">
            <!-- Welcome Section -->
            <div class="welcome-panel">
                <div class="welcome-panel-content">
                    <h2><?php esc_html_e('Welcome to Media Wipe', 'media-wipe'); ?></h2>
                    <p class="about-description">
                        <?php esc_html_e('Manage your WordPress media library with powerful deletion tools. Clean up unused files, delete selected media, or perform bulk operations safely.', 'media-wipe'); ?>
                    </p>
                    <div class="mw-welcome-panel-column-container">
                        <div class="mw-welcome-panel-column">
                            <h3><?php esc_html_e('Quick Actions', 'media-wipe'); ?></h3>
                            <a class="button button-primary button-hero" href="<?php echo esc_url(admin_url('admin.php?page=media-wipe-delete-selected')); ?>">
                                <?php esc_html_e('Delete Selected Media', 'media-wipe'); ?>
                            </a>
                            <p><?php esc_html_e('Choose specific files to delete from your media library.', 'media-wipe'); ?></p>
                        </div>
                        <div class="mw-welcome-panel-column">
                            <h3><?php esc_html_e('Bulk Operations', 'media-wipe'); ?></h3>
                            <a class="button button-secondary" href="<?php echo esc_url(admin_url('admin.php?page=media-wipe-delete-all')); ?>">
                                <?php esc_html_e('Delete All Media', 'media-wipe'); ?>
                            </a>
                            <p><?php esc_html_e('Remove all media files from your library (use with caution).', 'media-wipe'); ?></p>
                        </div>
                        <div class="mw-welcome-panel-column">
                            <h3><?php esc_html_e('Need Help?', 'media-wipe'); ?></h3>
                            <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=media-wipe-help')); ?>">
                                <?php esc_html_e('View Documentation', 'media-wipe'); ?>
                            </a>
                            <p><?php esc_html_e('Learn how to use Media Wipe safely and effectively.', 'media-wipe'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="media-wipe-stats-section">
                <h2><?php esc_html_e('Media Library Statistics', 'media-wipe'); ?></h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📁</div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo esc_html($media_stats['total']); ?></span>
                            <span class="stat-label"><?php esc_html_e('Total Files', 'media-wipe'); ?></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🖼️</div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo esc_html($media_stats['images']); ?></span>
                            <span class="stat-label"><?php esc_html_e('Images', 'media-wipe'); ?></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📄</div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo esc_html($media_stats['documents']); ?></span>
                            <span class="stat-label"><?php esc_html_e('Documents', 'media-wipe'); ?></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🎥</div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo esc_html($media_stats['videos']); ?></span>
                            <span class="stat-label"><?php esc_html_e('Videos', 'media-wipe'); ?></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🎵</div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo esc_html($media_stats['audio']); ?></span>
                            <span class="stat-label"><?php esc_html_e('Audio', 'media-wipe'); ?></span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📦</div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo esc_html($media_stats['other']); ?></span>
                            <span class="stat-label"><?php esc_html_e('Other', 'media-wipe'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Safety Notice -->
            <div class="media-wipe-safety-notice">
                <div class="notice notice-warning">
                    <h3><?php esc_html_e('⚠️ Important Safety Information', 'media-wipe'); ?></h3>
                    <p><?php esc_html_e('Media Wipe performs permanent deletions that cannot be undone. Always create a complete backup of your website before using any deletion features.', 'media-wipe'); ?></p>
                    <p><?php esc_html_e('Essential safety steps: Test on a staging site first, backup your database and files, review selected files carefully, and consider the impact on your content.', 'media-wipe'); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Settings page content
 */
function media_wipe_settings_page() {
    // Set security headers
    media_wipe_set_security_headers();

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'media-wipe'));
    }

    // Handle form submission
    if (isset($_POST['submit']) && check_admin_referer('media_wipe_settings_action', 'media_wipe_settings_nonce')) {
        media_wipe_save_settings();
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully.', 'media-wipe') . '</p></div>';
    }

    $settings = media_wipe_get_settings();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Media Wipe Settings', 'media-wipe'); ?></h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('media_wipe_settings_action', 'media_wipe_settings_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Confirmation Requirements', 'media-wipe'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="require_backup_confirmation" value="1" <?php checked($settings['require_backup_confirmation'], 1); ?>>
                                <?php esc_html_e('Require backup confirmation for delete all operations', 'media-wipe'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="require_text_confirmation" value="1" <?php checked($settings['require_text_confirmation'], 1); ?>>
                                <?php esc_html_e('Require typing confirmation text for delete all operations', 'media-wipe'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Document Preview', 'media-wipe'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="show_document_preview" value="1" <?php checked($settings['show_document_preview'], 1); ?>>
                            <?php esc_html_e('Show document preview in confirmation dialogs', 'media-wipe'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Logging', 'media-wipe'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_logging" value="1" <?php checked($settings['enable_logging'], 1); ?>>
                            <?php esc_html_e('Enable deletion activity logging', 'media-wipe'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Keep a log of deletion activities for audit purposes.', 'media-wipe'); ?></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Security Audit page content
 */
function media_wipe_security_page() {
    // Set security headers
    media_wipe_set_security_headers();

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'media-wipe'));
    }

    // Handle log clearing
    if (isset($_POST['clear_logs']) && check_admin_referer('media_wipe_clear_logs', 'clear_logs_nonce')) {
        delete_option('media_wipe_activity_log');
        delete_option('media_wipe_security_log');
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Security logs cleared successfully.', 'media-wipe') . '</p></div>';
    }

    $activity_logs = get_option('media_wipe_activity_log', array());
    $security_logs = get_option('media_wipe_security_log', array());

    // Reverse to show most recent first
    $activity_logs = array_reverse($activity_logs);
    $security_logs = array_reverse($security_logs);
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Security Audit Log', 'media-wipe'); ?></h1>

        <div class="security-audit-overview">
            <div class="audit-stats">
                <div class="stat-card">
                    <h3><?php esc_html_e('Activity Logs', 'media-wipe'); ?></h3>
                    <span class="stat-number"><?php echo count($activity_logs); ?></span>
                </div>
                <div class="stat-card">
                    <h3><?php esc_html_e('Security Events', 'media-wipe'); ?></h3>
                    <span class="stat-number"><?php echo count($security_logs); ?></span>
                </div>
            </div>

            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('media_wipe_clear_logs', 'clear_logs_nonce'); ?>
                <input type="submit" name="clear_logs" class="button button-secondary"
                       value="<?php esc_attr_e('Clear All Logs', 'media-wipe'); ?>"
                       onclick="return confirm('<?php esc_attr_e('Are you sure you want to clear all logs? This action cannot be undone.', 'media-wipe'); ?>');">
            </form>
        </div>

        <!-- Security Events Tab -->
        <h2><?php esc_html_e('Recent Security Events', 'media-wipe'); ?></h2>
        <?php if (!empty($security_logs)): ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Timestamp', 'media-wipe'); ?></th>
                        <th><?php esc_html_e('User', 'media-wipe'); ?></th>
                        <th><?php esc_html_e('Event', 'media-wipe'); ?></th>
                        <th><?php esc_html_e('IP Address', 'media-wipe'); ?></th>
                        <th><?php esc_html_e('Details', 'media-wipe'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($security_logs, 0, 20) as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log['timestamp']); ?></td>
                            <td><?php echo esc_html($log['user_login']); ?></td>
                            <td>
                                <span class="security-event-<?php echo esc_attr($log['event']); ?>">
                                    <?php echo esc_html(ucwords(str_replace('_', ' ', $log['event']))); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($log['ip_address']); ?></td>
                            <td>
                                <?php if (!empty($log['data'])): ?>
                                    <details>
                                        <summary><?php esc_html_e('View Details', 'media-wipe'); ?></summary>
                                        <pre><?php echo esc_html(print_r($log['data'], true)); ?></pre>
                                    </details>
                                <?php else: ?>
                                    <em><?php esc_html_e('No additional data', 'media-wipe'); ?></em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php esc_html_e('No security events recorded.', 'media-wipe'); ?></p>
        <?php endif; ?>

        <!-- Activity Logs Tab -->
        <h2><?php esc_html_e('Recent Activity', 'media-wipe'); ?></h2>
        <?php if (!empty($activity_logs)): ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Timestamp', 'media-wipe'); ?></th>
                        <th><?php esc_html_e('User', 'media-wipe'); ?></th>
                        <th><?php esc_html_e('Action', 'media-wipe'); ?></th>
                        <th><?php esc_html_e('IP Address', 'media-wipe'); ?></th>
                        <th><?php esc_html_e('Details', 'media-wipe'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($activity_logs, 0, 20) as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log['timestamp']); ?></td>
                            <td><?php echo esc_html($log['user_login']); ?></td>
                            <td>
                                <span class="activity-<?php echo esc_attr($log['action']); ?>">
                                    <?php echo esc_html(ucwords(str_replace('_', ' ', $log['action']))); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($log['ip_address']); ?></td>
                            <td>
                                <?php if (!empty($log['data'])): ?>
                                    <details>
                                        <summary><?php esc_html_e('View Details', 'media-wipe'); ?></summary>
                                        <pre><?php echo esc_html(print_r($log['data'], true)); ?></pre>
                                    </details>
                                <?php else: ?>
                                    <em><?php esc_html_e('No additional data', 'media-wipe'); ?></em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php esc_html_e('No activity recorded.', 'media-wipe'); ?></p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Help & Support page content
 */
function media_wipe_help_page() {
    // Set security headers
    media_wipe_set_security_headers();

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'media-wipe'));
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Media Wipe Help & Support', 'media-wipe'); ?></h1>
        
        <div class="media-wipe-help-content">
            <div class="help-section">
                <h2><?php esc_html_e('Getting Started', 'media-wipe'); ?></h2>
                <p><?php esc_html_e('Media Wipe provides powerful tools for managing your WordPress media library. Here\'s how to use each feature safely:', 'media-wipe'); ?></p>
                
                <h3><?php esc_html_e('Delete Selected Media', 'media-wipe'); ?></h3>
                <ol>
                    <li><?php esc_html_e('Navigate to Media Wipe > Delete Selected Media', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Click "Fetch All Media" to load all media files', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Select the files you want to delete using checkboxes', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Click "Delete Selected Media" and confirm your action', 'media-wipe'); ?></li>
                </ol>
                
                <h3><?php esc_html_e('Delete All Media', 'media-wipe'); ?></h3>
                <ol>
                    <li><?php esc_html_e('Navigate to Media Wipe > Delete All Media', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Review the media library statistics', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Click "Delete All Media Files" to open the confirmation dialog', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Complete all confirmation requirements', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Type "DELETE ALL MEDIA" to confirm', 'media-wipe'); ?></li>
                </ol>
            </div>
            
            <div class="help-section">
                <h2><?php esc_html_e('Safety Guidelines', 'media-wipe'); ?></h2>
                <div class="notice notice-warning">
                    <p><strong><?php esc_html_e('Important:', 'media-wipe'); ?></strong> <?php esc_html_e('All deletions performed by Media Wipe are permanent and cannot be undone.', 'media-wipe'); ?></p>
                </div>
                
                <h3><?php esc_html_e('Before Using Media Wipe:', 'media-wipe'); ?></h3>
                <ul>
                    <li><?php esc_html_e('Create a complete backup of your website (files and database)', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Test the plugin on a staging site first', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Review which files will be deleted', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Consider the impact on your content and design', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Ensure you have alternative copies of important files', 'media-wipe'); ?></li>
                </ul>
            </div>
            
            <div class="help-section">
                <h2><?php esc_html_e('Troubleshooting', 'media-wipe'); ?></h2>
                
                <h3><?php esc_html_e('Common Issues:', 'media-wipe'); ?></h3>
                <dl>
                    <dt><?php esc_html_e('Files not appearing in the list', 'media-wipe'); ?></dt>
                    <dd><?php esc_html_e('Make sure to click "Fetch All Media" to load all files. Some files may be filtered based on their attachment status.', 'media-wipe'); ?></dd>
                    
                    <dt><?php esc_html_e('Deletion process seems slow', 'media-wipe'); ?></dt>
                    <dd><?php esc_html_e('Large media libraries may take time to process. The plugin deletes files safely one by one to prevent server timeouts.', 'media-wipe'); ?></dd>
                    
                    <dt><?php esc_html_e('Some files couldn\'t be deleted', 'media-wipe'); ?></dt>
                    <dd><?php esc_html_e('Files may be protected by file permissions or in use by other plugins. Check your server error logs for details.', 'media-wipe'); ?></dd>
                </dl>
            </div>
            
            <div class="help-section">
                <h2><?php esc_html_e('Support', 'media-wipe'); ?></h2>
                <p><?php esc_html_e('If you need additional help or encounter issues:', 'media-wipe'); ?></p>
                <ul>
                    <li><?php esc_html_e('Check the plugin documentation', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Review your server error logs', 'media-wipe'); ?></li>
                    <li><?php esc_html_e('Contact the plugin author for support', 'media-wipe'); ?></li>
                </ul>
                
                <p>
                    <strong><?php esc_html_e('Plugin Version:', 'media-wipe'); ?></strong> 1.0.4<br>
                    <strong><?php esc_html_e('Author:', 'media-wipe'); ?></strong> Md. Nahid Hasan<br>
                    <strong><?php esc_html_e('Website:', 'media-wipe'); ?></strong> <a href="https://mdnahidhasan.netlify.app" target="_blank">mdnahidhasan.netlify.app</a>
                </p>
            </div>
        </div>
    </div>
    <?php
}
