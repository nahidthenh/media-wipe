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
        __('Delete All', 'media-wipe'),                    // Menu title
        'manage_options',                                  // Capability
        'media-wipe-delete-all',                          // Menu slug
        'media_wipe_all_media_page'                       // Function
    );

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
        __('Audit Log', 'media-wipe'),                     // Menu title
        'manage_options',                                  // Capability
        'media-wipe-security',                            // Menu slug
        'media_wipe_security_page'                        // Function
    );

    // Add Help & Support submenu
    add_submenu_page(
        'media-wipe',                                      // Parent slug
        __('Help & Support', 'media-wipe'),               // Page title
        __('Support', 'media-wipe'),                       // Menu title
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
                <h1><?php esc_html_e('Media Wipe Dashboard', 'media-wipe'); ?></h1>
                <p class="mw-hero-subtitle"><?php esc_html_e('AI-powered WordPress media management with intelligent unused media detection and enterprise-grade security.', 'media-wipe'); ?></p>
                <div class="mw-hero-stats">
                    <div class="mw-hero-stat">
                        <span class="mw-stat-number" style="color: #fff;"><?php echo esc_html($media_stats['total']); ?></span>
                        <span class="mw-stat-label" style="color: #fff;"><?php esc_html_e('Total Files', 'media-wipe'); ?></span>
                    </div>
                    <div class="mw-hero-stat">
                        <span class="mw-stat-number" style="color: #fff;"><?php echo esc_html(MEDIA_WIPE_VERSION); ?></span>
                        <span class="mw-stat-label" style="color: #fff;"><?php esc_html_e('Version', 'media-wipe'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mw-dashboard-content">
            <!-- Dashboard Navigation Tabs -->
            <div class="mw-quick-nav">
                <div class="mw-nav-item active" data-target="overview">
                    <span class="dashicons dashicons-dashboard"></span>
                    <span><?php esc_html_e('Overview', 'media-wipe'); ?></span>
                </div>
                <div class="mw-nav-item" data-target="actions">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <span><?php esc_html_e('Quick Actions', 'media-wipe'); ?></span>
                </div>
                <div class="mw-nav-item" data-target="statistics">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <span><?php esc_html_e('Statistics', 'media-wipe'); ?></span>
                </div>
                <div class="mw-nav-item" data-target="recent-activity">
                    <span class="dashicons dashicons-clock"></span>
                    <span><?php esc_html_e('Recent Activity', 'media-wipe'); ?></span>
                </div>
                <div class="mw-nav-item" data-target="system-info">
                    <span class="dashicons dashicons-info"></span>
                    <span><?php esc_html_e('System Info', 'media-wipe'); ?></span>
                </div>
            </div>

            <!-- Dashboard Content Sections -->
            <div class="mw-dashboard-sections">
                <!-- Overview Section -->
                <section id="overview" class="mw-dashboard-section mw-help-section active">
                    <div class="mw-section-header">
                        <span class="mw-section-icon dashicons dashicons-dashboard"></span>
                        <h2><?php esc_html_e('Dashboard Overview', 'media-wipe'); ?></h2>
                        <p><?php esc_html_e('Welcome to Media Wipe - your comprehensive WordPress media management solution.', 'media-wipe'); ?></p>
                    </div>

                    <div class="mw-info-cards">
                        <div class="mw-info-card">
                            <div class="mw-info-header">
                                <span class="dashicons dashicons-admin-media"></span>
                                <h3><?php esc_html_e('Media Library Status', 'media-wipe'); ?></h3>
                            </div>
                            <div class="mw-info-content">
                                <p><strong><?php echo esc_html($total_media); ?></strong> <?php esc_html_e('total media files', 'media-wipe'); ?></p>
                                <p><strong><?php echo esc_html(size_format($total_size)); ?></strong> <?php esc_html_e('total storage used', 'media-wipe'); ?></p>
                            </div>
                        </div>

                        <div class="mw-info-card">
                            <div class="mw-info-header">
                                <span class="dashicons dashicons-admin-tools"></span>
                                <h3><?php esc_html_e('Quick Links', 'media-wipe'); ?></h3>
                            </div>
                            <div class="mw-info-content">
                                <div class="mw-quick-links">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=media-wipe-delete-unused')); ?>" class="mw-quick-link">
                                        <span class="dashicons dashicons-search"></span>
                                        <?php esc_html_e('Delete Unused Media', 'media-wipe'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=media-wipe-delete-selected')); ?>" class="mw-quick-link">
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php esc_html_e('Delete Selected Media', 'media-wipe'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=media-wipe-settings')); ?>" class="mw-quick-link">
                                        <span class="dashicons dashicons-admin-settings"></span>
                                        <?php esc_html_e('Plugin Settings', 'media-wipe'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Quick Actions Section -->
                <section id="actions" class="mw-dashboard-section mw-help-section">
                <div class="mw-section-header">
                    <h2><?php esc_html_e('Quick Actions', 'media-wipe'); ?></h2>
                    <p><?php esc_html_e('Choose your preferred media management approach', 'media-wipe'); ?></p>
                </div>

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
                            <a href="<?php echo esc_url(admin_url('admin.php?page=media-wipe-delete-all')); ?>" class="mw-btn mw-btn-danger">
                                <span class="dashicons dashicons-trash"></span>
                                <?php esc_html_e('Delete All', 'media-wipe'); ?>
                            </a>
                        </div>
                        <div class="mw-card-features">
                            <span class="mw-feature-tag"><?php esc_html_e('Multi-Step Security', 'media-wipe'); ?></span>
                            <span class="mw-feature-tag"><?php esc_html_e('Backup Verification', 'media-wipe'); ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Media Library Analytics -->
            <section class="mw-dashboard-section">
                <div class="mw-section-header">
                    <h2><?php esc_html_e('Media Library Analytics', 'media-wipe'); ?></h2>
                    <p><?php esc_html_e('Comprehensive overview of your media library composition and statistics', 'media-wipe'); ?></p>
                </div>

                <div class="mw-stats-grid">
                    <div class="mw-stat-card">
                        <div class="mw-stat-icon">
                            <span class="dashicons dashicons-portfolio"></span>
                        </div>
                        <div class="mw-stat-content">
                            <span class="mw-stat-number"><?php echo esc_html($media_stats['total']); ?></span>
                            <span class="mw-stat-label"><?php esc_html_e('Total Files', 'media-wipe'); ?></span>
                        </div>
                        <span class="mw-stat-trend"><?php esc_html_e('ALL TYPES', 'media-wipe'); ?></span>
                    </div>

                    <div class="mw-stat-card">
                        <div class="mw-stat-icon">
                            <span class="dashicons dashicons-format-image"></span>
                        </div>
                        <div class="mw-stat-content">
                            <span class="mw-stat-number"><?php echo esc_html($media_stats['images']); ?></span>
                            <span class="mw-stat-label"><?php esc_html_e('Images', 'media-wipe'); ?></span>
                            <span class="mw-stat-trend"><?php echo esc_html(round(($media_stats['images'] / max($media_stats['total'], 1)) * 100, 1)); ?>%</span>
                        </div>
                    </div>

                    <div class="mw-stat-card">
                        <div class="mw-stat-icon">
                            <span class="dashicons dashicons-media-document"></span>
                        </div>
                        <div class="mw-stat-content">
                            <span class="mw-stat-number"><?php echo esc_html($media_stats['documents']); ?></span>
                            <span class="mw-stat-label"><?php esc_html_e('Documents', 'media-wipe'); ?></span>
                            <span class="mw-stat-trend"><?php echo esc_html(round(($media_stats['documents'] / max($media_stats['total'], 1)) * 100, 1)); ?>%</span>
                        </div>
                    </div>

                    <div class="mw-stat-card">
                        <div class="mw-stat-icon">
                            <span class="dashicons dashicons-video-alt3"></span>
                        </div>
                        <div class="mw-stat-content">
                            <span class="mw-stat-number"><?php echo esc_html($media_stats['videos']); ?></span>
                            <span class="mw-stat-label"><?php esc_html_e('Videos', 'media-wipe'); ?></span>
                            <span class="mw-stat-trend"><?php echo esc_html(round(($media_stats['videos'] / max($media_stats['total'], 1)) * 100, 1)); ?>%</span>
                        </div>
                    </div>

                    <div class="mw-stat-card">
                        <div class="mw-stat-icon">
                            <span class="dashicons dashicons-format-audio"></span>
                        </div>
                        <div class="mw-stat-content">
                            <span class="mw-stat-number"><?php echo esc_html($media_stats['audio']); ?></span>
                            <span class="mw-stat-label"><?php esc_html_e('Audio', 'media-wipe'); ?></span>
                            <span class="mw-stat-trend"><?php echo esc_html(round(($media_stats['audio'] / max($media_stats['total'], 1)) * 100, 1)); ?>%</span>
                        </div>
                    </div>

                    <div class="mw-stat-card">
                        <div class="mw-stat-icon">
                            <span class="dashicons dashicons-media-archive"></span>
                        </div>
                        <div class="mw-stat-content">
                            <span class="mw-stat-number"><?php echo esc_html($media_stats['other']); ?></span>
                            <span class="mw-stat-label"><?php esc_html_e('Other Files', 'media-wipe'); ?></span>
                            <span class="mw-stat-trend"><?php echo esc_html(round(($media_stats['other'] / max($media_stats['total'], 1)) * 100, 1)); ?>%</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- System Information & Quick Links -->
            <section class="mw-dashboard-section">
                <div class="mw-section-header">
                    <h2><?php esc_html_e('System Information & Quick Links', 'media-wipe'); ?></h2>
                    <p><?php esc_html_e('Plugin status, system compatibility, and helpful resources', 'media-wipe'); ?></p>
                </div>

                <div class="mw-info-grid">
                    <div class="mw-info-card">
                        <div class="mw-info-header">
                            <span class="dashicons dashicons-admin-plugins"></span>
                            <h3><?php esc_html_e('Plugin Status', 'media-wipe'); ?></h3>
                        </div>
                        <div class="mw-info-content">
                            <div class="mw-info-item">
                                <span class="mw-info-label"><?php esc_html_e('Version', 'media-wipe'); ?></span>
                                <span class="mw-info-value mw-version-badge"><?php echo esc_html(MEDIA_WIPE_VERSION); ?></span>
                            </div>
                            <div class="mw-info-item">
                                <span class="mw-info-label"><?php esc_html_e('WordPress', 'media-wipe'); ?></span>
                                <span class="mw-info-value"><?php echo esc_html(get_bloginfo('version')); ?></span>
                            </div>
                            <div class="mw-info-item">
                                <span class="mw-info-label"><?php esc_html_e('PHP Version', 'media-wipe'); ?></span>
                                <span class="mw-info-value"><?php echo esc_html(PHP_VERSION); ?></span>
                            </div>
                            <div class="mw-info-item">
                                <span class="mw-info-label"><?php esc_html_e('Status', 'media-wipe'); ?></span>
                                <span class="mw-info-value mw-status-active"><?php esc_html_e('Active', 'media-wipe'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="mw-info-card">
                        <div class="mw-info-header">
                            <span class="dashicons dashicons-admin-tools"></span>
                            <h3><?php esc_html_e('Quick Settings', 'media-wipe'); ?></h3>
                        </div>
                        <div class="mw-info-content">
                            <div class="mw-quick-links">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=media-wipe-settings')); ?>" class="mw-quick-link">
                                    <span class="dashicons dashicons-admin-settings"></span>
                                    <?php esc_html_e('Plugin Settings', 'media-wipe'); ?>
                                </a>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=media-wipe-audit-log')); ?>" class="mw-quick-link">
                                    <span class="dashicons dashicons-list-view"></span>
                                    <?php esc_html_e('Audit Log', 'media-wipe'); ?>
                                </a>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=media-wipe-help')); ?>" class="mw-quick-link">
                                    <span class="dashicons dashicons-editor-help"></span>
                                    <?php esc_html_e('Help & Support', 'media-wipe'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

                <!-- Statistics Section -->
                <section id="statistics" class="mw-dashboard-section mw-help-section">
                    <div class="mw-section-header">
                        <span class="mw-section-icon dashicons dashicons-chart-bar"></span>
                        <h2><?php esc_html_e('Media Library Statistics', 'media-wipe'); ?></h2>
                        <p><?php esc_html_e('Detailed breakdown of your WordPress media library.', 'media-wipe'); ?></p>
                    </div>

                    <div class="mw-stats-grid">
                        <div class="mw-stat-card">
                            <div class="mw-stat-icon">
                                <span class="dashicons dashicons-format-image"></span>
                            </div>
                            <div class="mw-stat-content">
                                <div class="mw-stat-number"><?php echo esc_html($image_count); ?></div>
                                <div class="mw-stat-label"><?php esc_html_e('Images', 'media-wipe'); ?></div>
                                <div class="mw-stat-trend"><?php echo esc_html(round(($image_count / max($total_media, 1)) * 100, 1)); ?>%</div>
                            </div>
                        </div>

                        <div class="mw-stat-card">
                            <div class="mw-stat-icon">
                                <span class="dashicons dashicons-format-video"></span>
                            </div>
                            <div class="mw-stat-content">
                                <div class="mw-stat-number"><?php echo esc_html($video_count); ?></div>
                                <div class="mw-stat-label"><?php esc_html_e('Videos', 'media-wipe'); ?></div>
                                <div class="mw-stat-trend"><?php echo esc_html(round(($video_count / max($total_media, 1)) * 100, 1)); ?>%</div>
                            </div>
                        </div>

                        <div class="mw-stat-card">
                            <div class="mw-stat-icon">
                                <span class="dashicons dashicons-media-document"></span>
                            </div>
                            <div class="mw-stat-content">
                                <div class="mw-stat-number"><?php echo esc_html($document_count); ?></div>
                                <div class="mw-stat-label"><?php esc_html_e('Documents', 'media-wipe'); ?></div>
                                <div class="mw-stat-trend"><?php echo esc_html(round(($document_count / max($total_media, 1)) * 100, 1)); ?>%</div>
                            </div>
                        </div>

                        <div class="mw-stat-card">
                            <div class="mw-stat-icon">
                                <span class="dashicons dashicons-database"></span>
                            </div>
                            <div class="mw-stat-content">
                                <div class="mw-stat-number"><?php echo esc_html(size_format($total_size)); ?></div>
                                <div class="mw-stat-label"><?php esc_html_e('Total Size', 'media-wipe'); ?></div>
                                <div class="mw-stat-trend"><?php esc_html_e('Storage Used', 'media-wipe'); ?></div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Recent Activity Section -->
                <section id="recent-activity" class="mw-dashboard-section mw-help-section">
                    <div class="mw-section-header">
                        <span class="mw-section-icon dashicons dashicons-clock"></span>
                        <h2><?php esc_html_e('Recent Activity', 'media-wipe'); ?></h2>
                        <p><?php esc_html_e('Latest actions performed with Media Wipe.', 'media-wipe'); ?></p>
                    </div>

                    <?php if (!empty($recent_activity)): ?>
                        <div class="mw-activity-list">
                            <?php foreach (array_slice($recent_activity, 0, 5) as $activity): ?>
                                <?php if (is_array($activity) && isset($activity['action'])): ?>
                                <div class="mw-activity-item">
                                    <div class="mw-activity-icon">
                                        <span class="dashicons dashicons-<?php echo esc_attr(isset($activity['action']) && $activity['action'] === 'delete_all' ? 'trash' : 'yes-alt'); ?>"></span>
                                    </div>
                                    <div class="mw-activity-content">
                                        <div class="mw-activity-title"><?php echo esc_html(ucwords(str_replace('_', ' ', isset($activity['action']) ? $activity['action'] : 'unknown'))); ?></div>
                                        <div class="mw-activity-meta">
                                            <?php echo esc_html(isset($activity['timestamp']) ? $activity['timestamp'] : ''); ?> • <?php echo esc_html(isset($activity['user_login']) ? $activity['user_login'] : 'Unknown User'); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="mw-activity-footer">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=media-wipe-audit-log')); ?>" class="mw-btn mw-btn-secondary">
                                <?php esc_html_e('View Full Activity Log', 'media-wipe'); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mw-empty-state">
                            <span class="dashicons dashicons-clock"></span>
                            <h3><?php esc_html_e('No Recent Activity', 'media-wipe'); ?></h3>
                            <p><?php esc_html_e('Start using Media Wipe to see your activity history here.', 'media-wipe'); ?></p>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- System Info Section -->
                <section id="system-info" class="mw-dashboard-section mw-help-section">
                    <div class="mw-section-header">
                        <span class="mw-section-icon dashicons dashicons-info"></span>
                        <h2><?php esc_html_e('System Information', 'media-wipe'); ?></h2>
                        <p><?php esc_html_e('Plugin and system compatibility information.', 'media-wipe'); ?></p>
                    </div>

                    <div class="mw-info-cards">
                        <div class="mw-info-card">
                            <div class="mw-info-header">
                                <span class="dashicons dashicons-admin-plugins"></span>
                                <h3><?php esc_html_e('Plugin Information', 'media-wipe'); ?></h3>
                            </div>
                            <div class="mw-info-content">
                                <p><strong><?php esc_html_e('Version:', 'media-wipe'); ?></strong> <?php echo esc_html(MEDIA_WIPE_VERSION); ?></p>
                                <p><strong><?php esc_html_e('WordPress:', 'media-wipe'); ?></strong> <?php echo esc_html(get_bloginfo('version')); ?></p>
                                <p><strong><?php esc_html_e('PHP:', 'media-wipe'); ?></strong> <?php echo esc_html(PHP_VERSION); ?></p>
                            </div>
                        </div>

                        <div class="mw-info-card">
                            <div class="mw-info-header">
                                <span class="dashicons dashicons-shield-alt"></span>
                                <h3><?php esc_html_e('Safety Guidelines', 'media-wipe'); ?></h3>
                            </div>
                            <div class="mw-info-content">
                                <div class="mw-safety-tips">
                                    <div class="mw-safety-tip">
                                        <span class="dashicons dashicons-backup"></span>
                                        <span><?php esc_html_e('Always create backups before deletion', 'media-wipe'); ?></span>
                                    </div>
                                    <div class="mw-safety-tip">
                                        <span class="dashicons dashicons-admin-site-alt3"></span>
                                        <span><?php esc_html_e('Test on staging environments first', 'media-wipe'); ?></span>
                                    </div>
                                    <div class="mw-safety-tip">
                                        <span class="dashicons dashicons-search"></span>
                                        <span><?php esc_html_e('Review files carefully before deletion', 'media-wipe'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Dashboard tab navigation
        $('.mw-nav-item').on('click', function() {
            var target = $(this).data('target');

            // Update active nav item
            $('.mw-nav-item').removeClass('active');
            $(this).addClass('active');

            // Show target section
            $('.mw-help-section').removeClass('active');
            $('#' + target).addClass('active');
        });
    });
    </script>
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
    <div class="wrap media-wipe-settings">
        <div class="media-wipe-settings-content">
            <!-- Settings Header -->
            <div class="mw-section-header">
                <h1><?php esc_html_e('Media Wipe Settings', 'media-wipe'); ?></h1>
                <p><?php esc_html_e('Configure plugin behavior and safety options to match your workflow preferences.', 'media-wipe'); ?></p>
            </div>

            <!-- Settings Description -->
            <div class="settings-description">
                <p><?php esc_html_e('These settings control how Media Wipe behaves during deletion operations. Adjust these options based on your security requirements and workflow preferences.', 'media-wipe'); ?></p>
            </div>

            <!-- Modern Safety Section -->
            <section class="mw-page-safety-section collapsible" data-section="settings-safety">
                <div class="mw-page-safety-notice">
                    <div class="mw-page-safety-header">
                        <span class="dashicons dashicons-shield-alt"></span>
                        <h3><?php esc_html_e('Settings Safety Guidelines', 'media-wipe'); ?></h3>
                        <span class="mw-page-safety-toggle dashicons dashicons-arrow-up-alt2"></span>
                    </div>
                    <div class="mw-page-safety-content">
                        <p><?php esc_html_e('These settings affect the safety and behavior of all deletion operations. Review each option carefully to ensure they match your security requirements.', 'media-wipe'); ?></p>
                        <div class="mw-page-safety-grid">
                            <div class="mw-page-safety-item">
                                <span class="dashicons dashicons-admin-network"></span>
                                <h4><?php esc_html_e('Confirmation Requirements', 'media-wipe'); ?></h4>
                                <p><?php esc_html_e('Enable additional confirmation steps to prevent accidental deletions.', 'media-wipe'); ?></p>
                            </div>
                            <div class="mw-page-safety-item">
                                <span class="dashicons dashicons-visibility"></span>
                                <h4><?php esc_html_e('Preview Options', 'media-wipe'); ?></h4>
                                <p><?php esc_html_e('Control how media files are displayed in confirmation dialogs.', 'media-wipe'); ?></p>
                            </div>
                            <div class="mw-page-safety-item">
                                <span class="dashicons dashicons-list-view"></span>
                                <h4><?php esc_html_e('Activity Logging', 'media-wipe'); ?></h4>
                                <p><?php esc_html_e('Track all deletion activities for audit and troubleshooting purposes.', 'media-wipe'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Settings Form -->
            <form method="post" action="" class="media-wipe-settings-form">
                <?php wp_nonce_field('media_wipe_settings_action', 'media_wipe_settings_nonce'); ?>

                <div class="settings-section">
                    <h2><?php esc_html_e('Confirmation Requirements', 'media-wipe'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Backup Confirmation', 'media-wipe'); ?></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" name="require_backup_confirmation" value="1" <?php checked($settings['require_backup_confirmation'], 1); ?>>
                                        <?php esc_html_e('Require backup confirmation for delete all operations', 'media-wipe'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('Forces users to confirm they have created a backup before proceeding with bulk deletions.', 'media-wipe'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Text Confirmation', 'media-wipe'); ?></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" name="require_text_confirmation" value="1" <?php checked($settings['require_text_confirmation'], 1); ?>>
                                        <?php esc_html_e('Require typing confirmation text for delete all operations', 'media-wipe'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('Requires users to type a confirmation phrase before executing bulk deletions.', 'media-wipe'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="settings-section">
                    <h2><?php esc_html_e('Preview & Display Options', 'media-wipe'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Document Preview', 'media-wipe'); ?></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" name="show_document_preview" value="1" <?php checked($settings['show_document_preview'], 1); ?>>
                                        <?php esc_html_e('Show document preview in confirmation dialogs', 'media-wipe'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('Displays document icons and file information in deletion confirmation dialogs.', 'media-wipe'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="settings-section">
                    <h2><?php esc_html_e('Activity Logging', 'media-wipe'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Enable Logging', 'media-wipe'); ?></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="checkbox" name="enable_logging" value="1" <?php checked($settings['enable_logging'], 1); ?>>
                                        <?php esc_html_e('Enable deletion activity logging', 'media-wipe'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('Keep a detailed log of all deletion activities for audit and troubleshooting purposes.', 'media-wipe'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="settings-submit">
                    <?php submit_button(esc_html__('Save Settings', 'media-wipe'), 'primary', 'submit', false); ?>
                    <p class="settings-note">
                        <span class="dashicons dashicons-info"></span>
                        <?php esc_html_e('Settings are saved automatically and apply to all future operations.', 'media-wipe'); ?>
                    </p>
                </div>
            </form>
        </div>
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
                    <div class="mw-feature-card mw-featured-card">
                        <div class="mw-card-icon mw-featured-icon">
                            <span class="dashicons dashicons-search"></span>
                        </div>
                        <div class="mw-featured-badge"><?php esc_html_e('NEW & RECOMMENDED', 'media-wipe'); ?></div>
                        <h3><?php esc_html_e('Delete Unused Media', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('AI-powered detection of truly unused media files with confidence scoring for safe deletion.', 'media-wipe'); ?></p>
                        <div class="mw-steps">
                            <div class="mw-step">
                                <span class="mw-step-number">1</span>
                                <span><?php esc_html_e('Navigate to Media Wipe → Delete Unused', 'media-wipe'); ?></span>
                            </div>
                            <div class="mw-step">
                                <span class="mw-step-number">2</span>
                                <span><?php esc_html_e('Configure scan settings and click "Start Scan"', 'media-wipe'); ?></span>
                            </div>
                            <div class="mw-step">
                                <span class="mw-step-number">3</span>
                                <span><?php esc_html_e('Review results and use "Select High Confidence Only"', 'media-wipe'); ?></span>
                            </div>
                            <div class="mw-step">
                                <span class="mw-step-number">4</span>
                                <span><?php esc_html_e('Click "Delete Selected" and confirm', 'media-wipe'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="mw-feature-card">
                        <div class="mw-card-icon">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <h3><?php esc_html_e('Delete Selected Media', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Choose specific files to delete with our professional DataTable interface.', 'media-wipe'); ?></p>
                        <div class="mw-steps">
                            <div class="mw-step">
                                <span class="mw-step-number">1</span>
                                <span><?php esc_html_e('Navigate to Media Wipe → Delete Selected', 'media-wipe'); ?></span>
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
                    <div class="mw-feature-item mw-featured-feature">
                        <span class="dashicons dashicons-search"></span>
                        <h3><?php esc_html_e('AI-Powered Detection', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Intelligent scanning identifies truly unused media files with confidence scoring.', 'media-wipe'); ?></p>
                    </div>
                    <div class="mw-feature-item mw-featured-feature">
                        <span class="dashicons dashicons-analytics"></span>
                        <h3><?php esc_html_e('Content Analysis', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('Scans posts, pages, widgets, menus, and theme files for media usage.', 'media-wipe'); ?></p>
                    </div>
                    <div class="mw-feature-item mw-featured-feature">
                        <span class="dashicons dashicons-star-filled"></span>
                        <h3><?php esc_html_e('Confidence Scoring', 'media-wipe'); ?></h3>
                        <p><?php esc_html_e('0-100% confidence scores help you make safe deletion decisions.', 'media-wipe'); ?></p>
                    </div>
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
                            <h3><?php esc_html_e('How accurate is the unused media detection?', 'media-wipe'); ?></h3>
                        </div>
                        <div class="mw-faq-answer">
                            <p><?php esc_html_e('The detection system is highly accurate, scanning posts, pages, widgets, menus, and theme files. Files with 90%+ confidence scores are very safe to delete. For maximum safety, always start with "Select High Confidence Only" and review lower-confidence files manually.', 'media-wipe'); ?></p>
                        </div>
                    </div>
                    <div class="mw-faq-item">
                        <div class="mw-faq-question">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <h3><?php esc_html_e('Should I use Basic or Advanced scan?', 'media-wipe'); ?></h3>
                        </div>
                        <div class="mw-faq-answer">
                            <p><?php esc_html_e('Basic scan is recommended for most users as it\'s faster and covers posts, pages, and widgets. Use Advanced scan if you have custom themes with hardcoded media references, but note it\'s slower and may be less accurate due to false positives in theme files.', 'media-wipe'); ?></p>
                        </div>
                    </div>
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
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <span><?php esc_html_e('Release Date:', 'media-wipe'); ?></span>
                        <strong><?php esc_html_e('July 26, 2025', 'media-wipe'); ?></strong>
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

        <!-- Modern Safety Section -->
        <section class="mw-page-safety-section collapsible" data-section="delete-unused-safety">
            <div class="mw-page-safety-notice">
                <div class="mw-page-safety-header">
                    <span class="dashicons dashicons-shield-alt"></span>
                    <h3><?php esc_html_e('Detection Accuracy & Safety Guidelines', 'media-wipe'); ?></h3>
                    <span class="mw-page-safety-toggle dashicons dashicons-arrow-up-alt2"></span>
                </div>
                <div class="mw-page-safety-content">
                    <p><?php esc_html_e('Our AI-powered detection system achieves approximately 85-90% accuracy. Some files marked as "unused" may actually be in use by theme templates, plugins, or external services.', 'media-wipe'); ?></p>
                    <div class="mw-page-safety-grid">
                        <div class="mw-page-safety-item">
                            <span class="dashicons dashicons-backup"></span>
                            <h4><?php esc_html_e('Always Backup First', 'media-wipe'); ?></h4>
                            <p><?php esc_html_e('Create a complete backup before deleting any files. This is your safety net against false positives.', 'media-wipe'); ?></p>
                        </div>
                        <div class="mw-page-safety-item">
                            <span class="dashicons dashicons-admin-tools"></span>
                            <h4><?php esc_html_e('Start with Test Batch', 'media-wipe'); ?></h4>
                            <p><?php esc_html_e('Begin with a small test batch to verify accuracy before processing larger quantities.', 'media-wipe'); ?></p>
                        </div>
                        <div class="mw-page-safety-item">
                            <span class="dashicons dashicons-warning"></span>
                            <h4><?php esc_html_e('Detection Limitations', 'media-wipe'); ?></h4>
                            <p><?php esc_html_e('Files used in theme templates, CSS, plugins, or external services may not be detected as "in use".', 'media-wipe'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

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
                                        <input type="checkbox" name="exclude_recent" value="1" checked>
                                        <?php esc_html_e('Exclude files uploaded in the last 30 days', 'media-wipe'); ?>
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" name="exclude_featured" value="1" checked>
                                        <?php esc_html_e('Exclude featured images (recommended)', 'media-wipe'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Scan Depth', 'media-wipe'); ?></th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="radio" name="scan_depth" value="basic" checked>
                                        <?php esc_html_e('Basic Scan (Recommended) - High accuracy, faster', 'media-wipe'); ?>
                                    </label>
                                    <br>
                                    <label>
                                        <input type="radio" name="scan_depth" value="advanced">
                                        <?php esc_html_e('Advanced Scan - Includes theme files, slower, may have false positives', 'media-wipe'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
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
