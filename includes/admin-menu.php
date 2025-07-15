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
                <div class="mw-welcome-panel-content">
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

            <!-- Plugin Information -->
            <div class="media-wipe-plugin-info">
                <div class="plugin-info-card">
                    <div class="plugin-info-header">
                        <span class="dashicons dashicons-admin-plugins"></span>
                        <h3><?php esc_html_e('Plugin Information', 'media-wipe'); ?></h3>
                    </div>
                    <div class="plugin-info-content">
                        <div class="info-item">
                            <span class="info-label"><?php esc_html_e('Current Version:', 'media-wipe'); ?></span>
                            <span class="info-value version-badge"><?php echo esc_html(MEDIA_WIPE_VERSION); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><?php esc_html_e('WordPress Version:', 'media-wipe'); ?></span>
                            <span class="info-value"><?php echo esc_html(get_bloginfo('version')); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><?php esc_html_e('PHP Version:', 'media-wipe'); ?></span>
                            <span class="info-value"><?php echo esc_html(PHP_VERSION); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><?php esc_html_e('Last Updated:', 'media-wipe'); ?></span>
                            <span class="info-value"><?php echo esc_html(date_i18n(get_option('date_format'), filemtime(MEDIA_WIPE_PLUGIN_FILE))); ?></span>
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
    <div class="mw-wrap media-wipe-help-page">
        <!-- Hero Section -->
        <div class="mw-hero-section">
            <div class="mw-hero-content">
                <div class="mw-hero-icon">
                    <span class="dashicons dashicons-trash"></span>
                </div>
                <h1><?php esc_html_e('Media Wipe Help & Support', 'media-wipe'); ?></h1>
                <p class="mw-hero-subtitle"><?php esc_html_e('Everything you need to know about managing your WordPress media library safely and efficiently.', 'media-wipe'); ?></p>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="mw-quick-nav">
            <div class="mw-nav-item" data-target="getting-started">
                <span class="dashicons dashicons-controls-play"></span>
                <span><?php esc_html_e('Getting Started', 'media-wipe'); ?></span>
            </div>
            <div class="mw-nav-item" data-target="features">
                <span class="dashicons dashicons-star-filled"></span>
                <span><?php esc_html_e('Features', 'media-wipe'); ?></span>
            </div>
            <div class="mw-nav-item" data-target="safety">
                <span class="dashicons dashicons-shield"></span>
                <span><?php esc_html_e('Safety Guide', 'media-wipe'); ?></span>
            </div>
            <div class="mw-nav-item" data-target="troubleshooting">
                <span class="dashicons dashicons-sos"></span>
                <span><?php esc_html_e('Troubleshooting', 'media-wipe'); ?></span>
            </div>
            <div class="mw-nav-item" data-target="support">
                <span class="dashicons dashicons-email"></span>
                <span><?php esc_html_e('Support', 'media-wipe'); ?></span>
            </div>
        </div>

        <!-- Content Sections -->
        <div class="mw-help-content">
            <!-- Getting Started Section -->
            <section id="getting-started" class="mw-help-section">
                <div class="mw-section-header">
                    <span class="mw-section-icon dashicons dashicons-controls-play"></span>
                    <h2><?php esc_html_e('Getting Started', 'media-wipe'); ?></h2>
                    <p><?php esc_html_e('Learn how to use Media Wipe\'s powerful features to manage your media library effectively.', 'media-wipe'); ?></p>
                </div>

                <div class="mw-feature-cards">
                    <div class="mw-feature-card">
                        <div class="mw-card-icon">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <h3><?php esc_html_e('Delete Selected Media', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Choose specific files to delete with our professional DataTable interface.', 'media-wipe'); ?></p>
                        <div class="mw-steps">
                            <div class="mw-step">
                                <span class="mw-step-number">1</span>
                                <span><?php esc_html_e('Navigate to Media Wipe → Delete Selected Media', 'media-wipe'); ?></span>
                            </div>
                            <div class="mw-step">
                                <span class="mw-step-number">2</span>
                                <span><?php esc_html_e('Browse and search your media files', 'media-wipe'); ?></span>
                            </div>
                            <div class="mw-step">
                                <span class="mw-step-number">3</span>
                                <span><?php esc_html_e('Select files using checkboxes', 'media-wipe'); ?></span>
                            </div>
                            <div class="mw-step">
                                <span class="mw-step-number">4</span>
                                <span><?php esc_html_e('Click "Delete Selected" and confirm', 'media-wipe'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="mw-feature-card">
                        <div class="mw-card-icon">
                            <span class="dashicons dashicons-trash"></span>
                        </div>
                        <h3><?php esc_html_e('Delete All Media', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Remove all media files with our secure multi-step confirmation process.', 'media-wipe'); ?></p>
                        <div class="mw-steps">
                            <div class="mw-step">
                                <span class="mw-step-number">1</span>
                                <span><?php esc_html_e('Navigate to Media Wipe → Delete All Media', 'media-wipe'); ?></span>
                            </div>
                            <div class="mw-step">
                                <span class="mw-step-number">2</span>
                                <span><?php esc_html_e('Review media library statistics', 'media-wipe'); ?></span>
                            </div>
                            <div class="mw-step">
                                <span class="mw-step-number">3</span>
                                <span><?php esc_html_e('Complete backup verification', 'media-wipe'); ?></span>
                            </div>
                            <div class="mw-step">
                                <span class="mw-step-number">4</span>
                                <span><?php esc_html_e('Type confirmation text to proceed', 'media-wipe'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Features Section -->
            <section id="features" class="mw-help-section">
                <div class="mw-section-header">
                    <span class="mw-section-icon dashicons dashicons-star-filled"></span>
                    <h2><?php esc_html_e('Key Features', 'media-wipe'); ?></h2>
                    <p><?php esc_html_e('Discover the powerful capabilities that make Media Wipe the ultimate media management solution.', 'media-wipe'); ?></p>
                </div>

                <div class="mw-features-grid">
                    <div class="mw-feature-item">
                        <span class="dashicons dashicons-admin-customizer"></span>
                        <h3><?php esc_html_e('Selective Deletion', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Choose specific files to delete with advanced filtering options.', 'media-wipe'); ?></p>
                    </div>
                    <div class="mw-feature-item">
                        <span class="dashicons dashicons-database"></span>
                        <h3><?php esc_html_e('Bulk Operations', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Process thousands of files with memory-efficient batch operations.', 'media-wipe'); ?></p>
                    </div>
                    <div class="mw-feature-item">
                        <span class="dashicons dashicons-shield"></span>
                        <h3><?php esc_html_e('Safety Protocols', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Multi-step confirmation process prevents accidental deletions.', 'media-wipe'); ?></p>
                    </div>
                    <div class="mw-feature-item">
                        <span class="dashicons dashicons-visibility"></span>
                        <h3><?php esc_html_e('File Preview', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Preview documents and images before deletion.', 'media-wipe'); ?></p>
                    </div>
                    <div class="mw-feature-item">
                        <span class="dashicons dashicons-chart-area"></span>
                        <h3><?php esc_html_e('Media Analytics', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Comprehensive statistics about your media library.', 'media-wipe'); ?></p>
                    </div>
                    <div class="mw-feature-item">
                        <span class="dashicons dashicons-smartphone"></span>
                        <h3><?php esc_html_e('Mobile Friendly', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Fully responsive interface works on all devices.', 'media-wipe'); ?></p>
                    </div>
                </div>
            </section>

            <!-- Safety Guidelines Section -->
            <section id="safety" class="mw-help-section">
                <div class="mw-section-header">
                    <span class="mw-section-icon dashicons dashicons-shield"></span>
                    <h2><?php esc_html_e('Safety Guidelines', 'media-wipe'); ?></h2>
                    <p><?php esc_html_e('Follow these essential safety practices to protect your website when using Media Wipe.', 'media-wipe'); ?></p>
                </div>

                <div class="mw-safety-alert">
                    <div class="mw-alert-icon">
                        <span class="dashicons dashicons-warning"></span>
                    </div>
                    <div class="mw-alert-content">
                        <h3><?php esc_html_e('Important Warning', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('All deletions performed by Media Wipe are permanent and cannot be undone. Always create backups before proceeding.', 'media-wipe'); ?></p>
                    </div>
                </div>

                <div class="mw-safety-checklist">
                    <h3><?php esc_html_e('Pre-Deletion Checklist', 'media-wipe'); ?></h3>
                    <div class="mw-checklist-item">
                        <span class="dashicons dashicons-yes"></span>
                        <p><?php esc_html_e('Create a complete backup of your website (files and database)', 'media-wipe'); ?></p>
                    </div>
                    <div class="mw-checklist-item">
                        <span class="dashicons dashicons-yes"></span>
                        <p><?php esc_html_e('Test the plugin on a staging site first', 'media-wipe'); ?></p>
                    </div>
                    <div class="mw-checklist-item">
                        <span class="dashicons dashicons-yes"></span>
                        <p><?php esc_html_e('Review which files will be deleted', 'media-wipe'); ?></p>
                    </div>
                    <div class="mw-checklist-item">
                        <span class="dashicons dashicons-yes"></span>
                        <p><?php esc_html_e('Consider the impact on your content and design', 'media-wipe'); ?></p>
                    </div>
                    <div class="mw-checklist-item">
                        <span class="dashicons dashicons-yes"></span>
                        <p><?php esc_html_e('Ensure you have alternative copies of important files', 'media-wipe'); ?></p>
                    </div>
                </div>
            </section>

            <!-- Troubleshooting Section -->
            <section id="troubleshooting" class="mw-help-section">
                <div class="mw-section-header">
                    <span class="mw-section-icon dashicons dashicons-sos"></span>
                    <h2><?php esc_html_e('Troubleshooting', 'media-wipe'); ?></h2>
                    <p><?php esc_html_e('Solutions for common issues you might encounter while using Media Wipe.', 'media-wipe'); ?></p>
                </div>

                <div class="mw-faq-container">
                    <div class="mw-faq-item">
                        <div class="mw-faq-question">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <h3><?php esc_html_e('Files not appearing in the list', 'media-wipe'); ?></h3>
                        </div>
                        <div class="mw-faq-answer">
                            <p><?php esc_html_e('Make sure to click "Fetch All Media" to load all files. Some files may be filtered based on their attachment status. Try refreshing the page or clearing your browser cache.', 'media-wipe'); ?></p>
                        </div>
                    </div>
                    <div class="mw-faq-item">
                        <div class="mw-faq-question">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <h3><?php esc_html_e('Deletion process seems slow', 'media-wipe'); ?></h3>
                        </div>
                        <div class="mw-faq-answer">
                            <p><?php esc_html_e('Large media libraries may take time to process. The plugin deletes files safely one by one to prevent server timeouts. For very large libraries, consider deleting in smaller batches.', 'media-wipe'); ?></p>
                        </div>
                    </div>
                    <div class="mw-faq-item">
                        <div class="mw-faq-question">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <h3><?php esc_html_e('Some files couldn\'t be deleted', 'media-wipe'); ?></h3>
                        </div>
                        <div class="mw-faq-answer">
                            <p><?php esc_html_e('Files may be protected by file permissions or in use by other plugins. Check your server error logs for details. You may need to adjust file permissions or deactivate conflicting plugins.', 'media-wipe'); ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Support Section -->
            <section id="support" class="mw-help-section">
                <div class="mw-section-header">
                    <span class="mw-section-icon dashicons dashicons-email"></span>
                    <h2><?php esc_html_e('Support', 'media-wipe'); ?></h2>
                    <p><?php esc_html_e('Need help? We\'re here for you. Find out how to get assistance with Media Wipe.', 'media-wipe'); ?></p>
                </div>

                <div class="mw-support-options">
                    <div class="mw-support-card">
                        <div class="mw-support-icon">
                            <span class="dashicons dashicons-book"></span>
                        </div>
                        <h3><?php esc_html_e('Documentation', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Browse our comprehensive documentation for detailed guides and tutorials.', 'media-wipe'); ?></p>
                        <a href="#" class="mw-button"><?php esc_html_e('View Docs', 'media-wipe'); ?></a>
                    </div>
                    <div class="mw-support-card">
                        <div class="mw-support-icon">
                            <span class="dashicons dashicons-admin-comments"></span>
                        </div>
                        <h3><?php esc_html_e('Contact Support', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Get in touch with our support team for personalized assistance.', 'media-wipe'); ?></p>
                        <a href="mailto:mail.mdnahidhasan@gmail.com" class="mw-button"><?php esc_html_e('Email Support', 'media-wipe'); ?></a>
                    </div>
                </div>

                <div class="mw-plugin-info">
                    <div class="mw-info-item">
                        <span class="dashicons dashicons-info"></span>
                        <span><?php esc_html_e('Plugin Version:', 'media-wipe'); ?></span>
                        <strong><?php echo esc_html(MEDIA_WIPE_VERSION); ?></strong>
                    </div>
                    <div class="mw-info-item">
                        <span class="dashicons dashicons-admin-users"></span>
                        <span><?php esc_html_e('Author:', 'media-wipe'); ?></span>
                        <strong>Md. Nahid Hasan</strong>
                    </div>
                    <div class="mw-info-item">
                        <span class="dashicons dashicons-admin-site"></span>
                        <span><?php esc_html_e('Website:', 'media-wipe'); ?></span>
                        <a href="https://mdnahidhasan.netlify.app" target="_blank">mdnahidhasan.netlify.app</a>
                    </div>
                </div>
            </section>
        </div>

        <!-- JavaScript for interactive elements -->
        <script>
        jQuery(document).ready(function($) {
            // Smooth scrolling for navigation
            $('.mw-nav-item').on('click', function() {
                var target = $(this).data('target');
                $('html, body').animate({
                    scrollTop: $('#' + target).offset().top - 50
                }, 500);

                // Add active class
                $('.mw-nav-item').removeClass('active');
                $(this).addClass('active');
            });

            // FAQ accordion
            $('.mw-faq-question').on('click', function() {
                $(this).parent().toggleClass('active');
                $(this).find('.dashicons').toggleClass('dashicons-plus-alt2 dashicons-minus');
                $(this).next('.mw-faq-answer').slideToggle(200);
            });

            // Set first nav item as active by default
            $('.mw-nav-item:first-child').addClass('active');

            // Hide all FAQ answers initially
            $('.mw-faq-answer').hide();
        });
        </script>
    </div>
    <?php
}
