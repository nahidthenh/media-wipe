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
        11                                                 // Position (right after Media menu at 10)
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

    // Delete All Media functionality is now integrated into the dashboard
    // No separate submenu needed

    // Add Delete Selected Media submenu
    add_submenu_page(
        'media-wipe',                                      // Parent slug
        __('Delete Selected Media', 'media-wipe'),         // Page title
        __('Delete Selected', 'media-wipe'),               // Menu title
        'manage_options',                                  // Capability
        'media-wipe-delete-selected',                     // Menu slug
        'media_wipe_unused_media_page'                    // Function
    );

    // Add Delete Unused Media submenu
    add_submenu_page(
        'media-wipe',                                      // Parent slug
        __('Delete Unused Media', 'media-wipe'),           // Page title
        __('Delete Unused', 'media-wipe'),                 // Menu title
        'manage_options',                                  // Capability
        'media-wipe-delete-unused',                       // Menu slug
        'media_wipe_delete_unused_page'                   // Function
    );

    // Settings functionality is now integrated into Deletion History page

    // Add Deletion History submenu
    add_submenu_page(
        'media-wipe',                                      // Parent slug
        __('Deletion History', 'media-wipe'),             // Page title
        __('Deletion History', 'media-wipe'),             // Menu title
        'manage_options',                                  // Capability
        'media-wipe-security',                            // Menu slug
        'media_wipe_deletion_history_page'                // Function
    );

    // Support page removed for cleaner, more focused interface
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

    // Extract individual statistics for dashboard display
    $total_media = $media_stats['total'];
    $image_count = $media_stats['images'];
    $video_count = $media_stats['videos'];
    $document_count = $media_stats['documents'];

    // Calculate total media library size
    $total_size = 0;
    $upload_dir = wp_upload_dir();
    if (function_exists('media_wipe_calculate_media_size')) {
        $total_size = media_wipe_calculate_media_size();
    } else {
        // Fallback calculation
        $attachments = get_posts(array(
            'post_type' => 'attachment',
            'numberposts' => -1,
            'post_status' => 'inherit'
        ));

        foreach ($attachments as $attachment) {
            $file_path = get_attached_file($attachment->ID);
            if ($file_path && file_exists($file_path)) {
                $total_size += filesize($file_path);
            }
        }
    }
    ?>
    <div class="mw-wrap media-wipe-dashboard-page">
        <!-- Modern Hero Section -->
        <div class="mw-dashboard-hero">
            <div class="mw-hero-content">
                <div class="mw-hero-icon">
                    <span class="dashicons dashicons-trash"></span>
                </div>
                <div class="mw-hero-text">
                    <h1><?php esc_html_e('Media Wipe Dashboard', 'media-wipe'); ?></h1>
                    <p class="mw-hero-subtitle"><?php esc_html_e('AI-powered WordPress media management with intelligent unused media detection and enterprise-grade security.', 'media-wipe'); ?></p>
                </div>
                <div class="mw-hero-stats">
                    <div class="mw-hero-stat">
                        <span class="mw-stat-number"><?php echo esc_html($media_stats['total']); ?></span>
                        <span class="mw-stat-label"><?php esc_html_e('Total Files', 'media-wipe'); ?></span>
                    </div>
                    <div class="mw-hero-stat">
                        <span class="mw-stat-number"><?php echo esc_html(size_format($total_size)); ?></span>
                        <span class="mw-stat-label"><?php esc_html_e('Storage Used', 'media-wipe'); ?></span>
                    </div>
                    <div class="mw-hero-stat">
                        <span class="mw-stat-number"><?php echo esc_html(MEDIA_WIPE_VERSION); ?></span>
                        <span class="mw-stat-label"><?php esc_html_e('Version', 'media-wipe'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mw-dashboard-content">

            <!-- 1. Quick Action Cards Section -->
            <section class="mw-help-section">
                <!-- Stats  -->
                <div class="mw-stats-grid">
                    <div class="mw-stat-card">
                        <div class="mw-stat-icon">
                            <span class="dashicons dashicons-format-image"></span>
                        </div>
                        <div class="mw-stat-content">
                            <span class="mw-stat-number"><?php echo esc_html($media_stats['images']); ?></span>
                            <span class="mw-stat-label"><?php esc_html_e('Images', 'media-wipe'); ?></span>
                        </div>
                        <span class="mw-stat-trend"><?php echo esc_html(round(($media_stats['images'] / max($media_stats['total'], 1)) * 100, 1)); ?>%</span>
                    </div>

                    <div class="mw-stat-card">
                        <div class="mw-stat-icon">
                            <span class="dashicons dashicons-video-alt3"></span>
                        </div>
                        <div class="mw-stat-content">
                            <span class="mw-stat-number"><?php echo esc_html($media_stats['videos']); ?></span>
                            <span class="mw-stat-label"><?php esc_html_e('Videos', 'media-wipe'); ?></span>
                        </div>
                        <span class="mw-stat-trend"><?php echo esc_html(round(($media_stats['videos'] / max($media_stats['total'], 1)) * 100, 1)); ?>%</span>
                    </div>

                    <div class="mw-stat-card">
                        <div class="mw-stat-icon">
                            <span class="dashicons dashicons-media-document"></span>
                        </div>
                        <div class="mw-stat-content">
                            <span class="mw-stat-number"><?php echo esc_html($media_stats['documents']); ?></span>
                            <span class="mw-stat-label"><?php esc_html_e('Documents', 'media-wipe'); ?></span>
                        </div>
                        <span class="mw-stat-trend"><?php echo esc_html(round(($media_stats['documents'] / max($media_stats['total'], 1)) * 100, 1)); ?>%</span>
                    </div>

                    <div class="mw-stat-card">
                        <div class="mw-stat-icon">
                            <span class="dashicons dashicons-format-audio"></span>
                        </div>
                        <div class="mw-stat-content">
                            <span class="mw-stat-number"><?php echo esc_html($media_stats['audio']); ?></span>
                            <span class="mw-stat-label"><?php esc_html_e('Audio', 'media-wipe'); ?></span>
                        </div>
                        <span class="mw-stat-trend"><?php echo esc_html(round(($media_stats['audio'] / max($media_stats['total'], 1)) * 100, 1)); ?>%</span>
                    </div>

                    <div class="mw-stat-card">
                        <div class="mw-stat-icon">
                            <span class="dashicons dashicons-media-archive"></span>
                        </div>
                        <div class="mw-stat-content">
                            <span class="mw-stat-number"><?php echo esc_html($media_stats['other']); ?></span>
                            <span class="mw-stat-label"><?php esc_html_e('Other Files', 'media-wipe'); ?></span>
                        </div>
                        <span class="mw-stat-trend"><?php echo esc_html(round(($media_stats['other'] / max($media_stats['total'], 1)) * 100, 1)); ?>%</span>
                    </div>
                </div>
                <!-- Quick Action  -->
                <div class="mw-action-cards">
                    <div class="mw-action-card mw-featured-card">
                        <div class="mw-card-icon mw-featured-icon">
                            <span class="dashicons dashicons-search"></span>
                        </div>
                        <div class="mw-featured-badge"><?php esc_html_e('AI-POWERED', 'media-wipe'); ?></div>
                        <h3><?php esc_html_e('Delete Unused Media', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Revolutionary AI detection identifies truly unused files with confidence scoring for safe deletion.', 'media-wipe'); ?></p>
                        <div class="mw-card-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=media-wipe-delete-unused')); ?>" class="mw-btn mw-btn-primary">
                                <span class="dashicons dashicons-search"></span>
                                <?php esc_html_e('Start Smart Scan', 'media-wipe'); ?>
                            </a>
                        </div>
                        <div class="mw-card-features">
                            <span class="mw-feature-tag"><?php esc_html_e('Confidence Scoring', 'media-wipe'); ?></span>
                            <span class="mw-feature-tag"><?php esc_html_e('Content Analysis', 'media-wipe'); ?></span>
                            <span class="mw-feature-tag"><?php esc_html_e('Safe Deletion', 'media-wipe'); ?></span>
                        </div>
                    </div>

                    <div class="mw-action-card">
                        <div class="mw-card-icon">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <h3><?php esc_html_e('Delete Selected Media', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Professional DataTable interface for precise file selection and deletion with advanced filtering.', 'media-wipe'); ?></p>
                        <div class="mw-card-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=media-wipe-delete-selected')); ?>" class="mw-btn mw-btn-secondary">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e('Select Files', 'media-wipe'); ?>
                            </a>
                        </div>
                        <div class="mw-card-features">
                            <span class="mw-feature-tag"><?php esc_html_e('DataTables', 'media-wipe'); ?></span>
                            <span class="mw-feature-tag"><?php esc_html_e('Bulk Selection', 'media-wipe'); ?></span>
                        </div>
                    </div>

                    <div class="mw-action-card">
                        <div class="mw-card-icon">
                            <span class="dashicons dashicons-trash"></span>
                        </div>
                        <h3><?php esc_html_e('Delete All Media', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Complete media library cleanup with multi-step security confirmation and backup verification.', 'media-wipe'); ?></p>
                        <div class="mw-card-actions">
                            <button type="button" id="open-delete-all-modal" class="mw-btn mw-btn-danger">
                                <span class="dashicons dashicons-trash"></span>
                                <?php esc_html_e('Delete All', 'media-wipe'); ?>
                            </button>
                        </div>
                        <div class="mw-card-features">
                            <span class="mw-feature-tag"><?php esc_html_e('Multi-Step Security', 'media-wipe'); ?></span>
                            <span class="mw-feature-tag"><?php esc_html_e('Backup Verification', 'media-wipe'); ?></span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Delete All Media Modal -->
    <?php media_wipe_render_delete_all_modal($media_stats); ?>

    <!-- Hidden form for nonce -->
    <form style="display: none;">
        <?php wp_nonce_field('media_wipe_all_action', 'media_wipe_all_nonce'); ?>
    </form>

    <?php
}

// Settings page removed - logging settings moved to Deletion History page

/**
 * Deletion History page content
 */
function media_wipe_deletion_history_page() {
    // Set security headers
    media_wipe_set_security_headers();

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'media-wipe'));
    }

    // Handle logging settings save
    if (isset($_POST['save_logging']) && check_admin_referer('media_wipe_logging_action', 'media_wipe_logging_nonce')) {
        $settings = array(
            'enable_logging' => isset($_POST['enable_logging']) ? 1 : 0,
        );
        update_option('media_wipe_settings', $settings);
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Logging settings saved successfully.', 'media-wipe') . '</p></div>';
    }

    // Handle log clearing
    if (isset($_POST['clear_logs']) && check_admin_referer('media_wipe_clear_logs', 'clear_logs_nonce')) {
        delete_option('media_wipe_activity_log');
        delete_option('media_wipe_security_log');
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Deletion history cleared successfully.', 'media-wipe') . '</p></div>';
    }

    $settings = media_wipe_get_settings();

    $activity_logs = get_option('media_wipe_activity_log', array());
    $security_logs = get_option('media_wipe_security_log', array());

    // Combine and sort logs by timestamp
    $all_logs = array_merge($activity_logs, $security_logs);
    usort($all_logs, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });

    // Reverse individual arrays to show most recent first (for backward compatibility)
    $activity_logs = array_reverse($activity_logs);
    $security_logs = array_reverse($security_logs);
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Deletion History', 'media-wipe'); ?></h1>

        <!-- Logging Settings -->
        <div class="mw-logging-settings">
            <form method="post" action="">
                <?php wp_nonce_field('media_wipe_logging_action', 'media_wipe_logging_nonce'); ?>
                <div class="mw-logging-card">
                    <div class="mw-logging-header">
                        <div class="mw-logging-icon">
                            <span class="dashicons dashicons-admin-tools"></span>
                        </div>
                        <div class="mw-logging-title">
                            <h3><?php esc_html_e('Activity Logging', 'media-wipe'); ?></h3>
                            <p><?php esc_html_e('Keep detailed logs of all deletion activities for audit and troubleshooting purposes', 'media-wipe'); ?></p>
                        </div>
                    </div>
                    <div class="mw-logging-control">
                        <label class="mw-toggle">
                            <input type="checkbox" name="enable_logging" value="1" <?php checked($settings['enable_logging'], 1); ?>>
                            <span class="mw-toggle-slider"></span>
                        </label>
                        <?php submit_button(__('Save', 'media-wipe'), 'primary', 'save_logging', false, array('class' => 'mw-save-logging-btn')); ?>
                    </div>
                </div>
            </form>
        </div>

        <div class="mw-deletion-history-overview">
            <div class="mw-history-stats">
                <div class="mw-stat-card">
                    <div class="mw-stat-icon">
                        <span class="dashicons dashicons-admin-tools"></span>
                    </div>
                    <div class="mw-stat-content">
                        <h3><?php esc_html_e('Activity Logs', 'media-wipe'); ?></h3>
                        <span class="mw-stat-number"><?php echo count($activity_logs); ?></span>
                    </div>
                </div>
                <div class="mw-stat-card">
                    <div class="mw-stat-icon">
                        <span class="dashicons dashicons-shield-alt"></span>
                    </div>
                    <div class="mw-stat-content">
                        <h3><?php esc_html_e('Security Events', 'media-wipe'); ?></h3>
                        <span class="mw-stat-number"><?php echo count($security_logs); ?></span>
                    </div>
                </div>
                <div class="mw-stat-card">
                    <div class="mw-stat-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <div class="mw-stat-content">
                        <h3><?php esc_html_e('Total Events', 'media-wipe'); ?></h3>
                        <span class="mw-stat-number"><?php echo count($all_logs); ?></span>
                    </div>
                </div>
            </div>

            <div class="mw-history-actions">
                <form method="post" style="display: inline-block;">
                    <?php wp_nonce_field('media_wipe_clear_logs', 'clear_logs_nonce'); ?>
                    <button type="submit" name="clear_logs" class="button button-secondary mw-clear-logs-btn"
                           onclick="return confirm('<?php esc_attr_e('Are you sure you want to clear all logs? This action cannot be undone.', 'media-wipe'); ?>');">
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e('Clear All Logs', 'media-wipe'); ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Modern Deletion History Table -->
        <div class="mw-history-table-container">
            <h2><?php esc_html_e('Deletion History', 'media-wipe'); ?></h2>

            <?php if (!empty($all_logs)): ?>
                <div class="datatable-container">
                    <table id="deletion-history-datatable" class="display nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Type', 'media-wipe'); ?></th>
                                <th><?php esc_html_e('Date & Time', 'media-wipe'); ?></th>
                                <th><?php esc_html_e('User', 'media-wipe'); ?></th>
                                <th><?php esc_html_e('Action', 'media-wipe'); ?></th>
                                <th><?php esc_html_e('IP Address', 'media-wipe'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_logs as $log):
                                $log_type = isset($log['event']) ? 'security' : 'activity';
                                $action = isset($log['event']) ? $log['event'] : $log['action'];
                                $formatted_date = date('M j, Y g:i A', strtotime($log['timestamp']));
                                $relative_time = human_time_diff(strtotime($log['timestamp']), current_time('timestamp')) . ' ago';
                            ?>
                                <tr>
                                    <td>
                                        <span class="mw-log-type mw-log-<?php echo esc_attr($log_type); ?>">
                                            <span class="dashicons dashicons-<?php echo $log_type === 'security' ? 'shield-alt' : 'admin-tools'; ?>"></span>
                                            <?php echo esc_html(ucfirst($log_type)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="mw-timestamp">
                                            <strong><?php echo esc_html($formatted_date); ?></strong>
                                            <small><?php echo esc_html($relative_time); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mw-user-info">
                                            <span class="dashicons dashicons-admin-users"></span>
                                            <?php echo esc_html($log['user_login']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="mw-action-badge mw-action-<?php echo esc_attr($action); ?>">
                                            <?php echo esc_html(ucwords(str_replace('_', ' ', $action))); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code class="mw-ip-address"><?php echo esc_html($log['ip_address']); ?></code>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="mw-no-logs">
                    <div class="mw-no-logs-icon">
                        <span class="dashicons dashicons-admin-tools"></span>
                    </div>
                    <h3><?php esc_html_e('No Deletion History', 'media-wipe'); ?></h3>
                    <p><?php esc_html_e('No deletion activities have been recorded yet. Activity will appear here when you delete media files.', 'media-wipe'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// Support page removed - plugin interface is self-explanatory

/**
 * Delete Unused Media page
 */
function media_wipe_delete_unused_page() {
    // Set security headers
    media_wipe_set_security_headers();

    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'media-wipe'));
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Delete Unused Media', 'media-wipe'); ?></h1>

        <!-- Feature Introduction -->
        <div class="media-wipe-unused-intro">
            <div class="intro-content">
                <h2><?php esc_html_e('Identify and Delete Unused Media Files', 'media-wipe'); ?></h2>
                <p><?php esc_html_e('This feature scans your media library to identify files that are not being used anywhere on your website. This is particularly useful for cleaning up demo content from imported themes or removing old, forgotten media files.', 'media-wipe'); ?></p>
            </div>
        </div>

        <!-- Warning section removed for cleaner interface -->

        <!-- Scan Configuration -->
        <div class="media-wipe-scan-config">
            <div class="config-card">
                <h3><?php esc_html_e('Scan Configuration', 'media-wipe'); ?></h3>
                <form id="unused-media-scan-form">
                    <?php wp_nonce_field('media_wipe_unused_scan', 'media_wipe_unused_scan_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Safety Options', 'media-wipe'); ?></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" name="exclude_recent" value="1">
                                        <?php esc_html_e('Exclude files uploaded in the last 30 days', 'media-wipe'); ?>
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" name="exclude_featured" value="1">
                                        <?php esc_html_e('Exclude featured images (recommended)', 'media-wipe'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <!-- Advanced scan option removed - Basic scan is optimal for most users -->
                    </table>

                    <div class="scan-actions">
                        <button type="button" id="start-unused-scan" class="button button-primary">
                            <span class="dashicons dashicons-search"></span>
                            <?php esc_html_e('Start Scan', 'media-wipe'); ?>
                        </button>
                        <button type="button" id="cancel-unused-scan" class="button" style="display: none;">
                            <?php esc_html_e('Cancel Scan', 'media-wipe'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Scan Progress -->
        <div id="scan-progress-container" style="display: none;">
            <div class="progress-card">
                <h3><?php esc_html_e('Scanning Media Library...', 'media-wipe'); ?></h3>
                <div class="progress-bar-container">
                    <div class="progress-bar">
                        <div id="progress-bar-fill" style="width: 0%;"></div>
                    </div>
                    <div class="progress-text">
                        <span id="progress-percentage">0%</span>
                        <span id="progress-details"><?php esc_html_e('Initializing scan...', 'media-wipe'); ?></span>
                    </div>
                </div>
                <div class="progress-stats">
                    <span id="files-processed">0</span> / <span id="total-files">0</span> <?php esc_html_e('files processed', 'media-wipe'); ?>
                    <span class="separator">•</span>
                    <span id="unused-found">0</span> <?php esc_html_e('unused files found', 'media-wipe'); ?>
                </div>
            </div>
        </div>

        <!-- Scan Results -->
        <div id="scan-results-container" style="display: none;">
            <div class="results-card">
                <div class="results-header">
                    <h3><?php esc_html_e('Scan Results', 'media-wipe'); ?></h3>
                    <div class="results-summary">
                        <span id="results-summary-text"></span>
                    </div>
                </div>

                <div class="results-controls">
                    <button type="button" id="select-all-unused" class="button">
                        <?php esc_html_e('Select All', 'media-wipe'); ?>
                    </button>
                    <button type="button" id="select-none-unused" class="button">
                        <?php esc_html_e('Select None', 'media-wipe'); ?>
                    </button>
                    <button type="button" id="select-high-confidence" class="button">
                        <?php esc_html_e('Select High Confidence Only', 'media-wipe'); ?>
                    </button>
                    <button type="button" id="delete-selected-unused" class="button button-primary" disabled>
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e('Delete Selected (0)', 'media-wipe'); ?>
                    </button>
                </div>

                <div class="results-table-container">
                    <table id="unused-media-datatable" class="display nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Select', 'media-wipe'); ?></th>
                                <th><?php esc_html_e('Preview', 'media-wipe'); ?></th>
                                <th><?php esc_html_e('Filename', 'media-wipe'); ?></th>
                                <th><?php esc_html_e('Type', 'media-wipe'); ?></th>
                                <th><?php esc_html_e('Size', 'media-wipe'); ?></th>
                                <th><?php esc_html_e('Upload Date', 'media-wipe'); ?></th>
                                <th><?php esc_html_e('Confidence', 'media-wipe'); ?></th>
                                <th><?php esc_html_e('Actions', 'media-wipe'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Results will be populated via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Hidden form for deletion -->
        <form id="delete-unused-form" method="post" style="display: none;">
            <?php wp_nonce_field('media_wipe_delete_unused', 'media_wipe_delete_unused_nonce'); ?>
            <input type="hidden" id="selected-unused-ids" name="selected_unused_ids" value="">
        </form>
    </div>
    <?php
}
