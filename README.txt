=== Media Wipe ===
Contributors: mdnahidhasan
Author URI: https://mdnahidhasan.netlify.app
Tags: media, delete, cleanup, unused, bulk
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Comprehensive WordPress media management with enterprise security, document preview, and professional dashboard. Transform your cleanup workflow!

== Description ==

Media Wipe is a powerful and secure WordPress plugin that provides comprehensive media library management capabilities. With advanced confirmation systems, document preview functionality, and detailed security audit logging, it's the safest way to clean up your WordPress media library.

**Key Features:**

* **Enhanced Delete All Media System** - Multi-step confirmation process with backup verification
* **Document Preview** - Visual preview of PDF, DOC, and other document files before deletion
* **WordPress Admin Menu Integration** - Dedicated admin menu with dashboard and statistics
* **Security Audit Logging** - Comprehensive tracking of all operations and security events
* **Advanced Confirmation System** - Multiple safety layers prevent accidental deletions
* **Real-time Progress Tracking** - Visual feedback during deletion operations
* **Rate Limiting** - Prevents abuse and ensures system stability
* **Mobile-Friendly Interface** - Responsive design works on all devices

**Security Features:**

* Nonce verification for all operations
* Capability-based access control
* CSRF protection
* Input validation and sanitization
* Security headers implementation
* Comprehensive audit trails
* Rate limiting for deletion operations

**Dashboard Features:**

* Media library statistics overview
* Recent activity tracking
* Quick action buttons
* Safety guidelines and best practices
* Settings management interface

**Document Preview System:**

* Support for PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX files
* Text files (TXT, CSV) preview
* File size and type information
* Thumbnail generation when available
* Batch preview for multiple documents

**Use Cases:**

* Clean up development sites before going live
* Remove unused media files to optimize storage
* Bulk delete media files during website redesigns
* Maintain organized and efficient media libraries
* Audit media deletion activities for compliance
* Safely manage large media collections

**Performance Optimized:**

* Batch processing for large operations
* Memory management for bulk deletions
* Efficient database queries
* Caching for improved performance
* Configurable operation limits

**WordPress Compatibility:**

* Fully tested with WordPress 6.7.1 (latest version)
* Compatible with WordPress 5.0 and above
* Works with PHP 7.4 to 8.3
* Multisite compatible

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/media-wipe` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to **Media Wipe** in your WordPress admin menu to access all features.
4. Review the settings and configure according to your needs.
5. Always create a backup before performing any deletion operations.

== Frequently Asked Questions ==

= Is this plugin compatible with my WordPress version? =

Yes, Media Wipe is fully tested and compatible with WordPress 6.7.1 (the latest version) and works with WordPress 5.0 and above. It also supports PHP 7.4 to 8.3 and is multisite compatible.

= Is it safe to use this plugin? =

Yes, Media Wipe includes multiple safety layers including backup verification requirements, multi-step confirmation processes, and comprehensive audit logging. However, always create a complete backup before using any deletion features.

= Can I recover deleted files? =

No, once files are deleted using this plugin, they cannot be recovered. This is why the plugin requires explicit confirmation that you have created a backup before proceeding.

= What file types are supported for document preview? =

The plugin supports preview for PDF, Microsoft Office files (DOC, DOCX, XLS, XLSX, PPT, PPTX), text files (TXT, CSV), and OpenDocument formats.

= Does this plugin delete files from the server? =

Yes, this plugin deletes both the database entries and the actual files from your server. All deletion operations are permanent and irreversible.

= How does the security audit system work? =

The plugin logs all activities including deletion attempts, security events, user information, IP addresses, and timestamps. You can view these logs in the Security Audit section of the admin menu.

= Can I configure the confirmation requirements? =

Yes, you can customize confirmation requirements in the Settings page, including backup confirmation requirements, text confirmation, and document preview settings.

= Is the plugin compatible with multisite installations? =

Currently, the plugin is designed for single-site installations. Multisite support is planned for future versions.

= How does rate limiting work? =

The plugin implements rate limiting to prevent abuse, allowing a maximum number of operations per hour. This helps maintain system stability and security.

== Screenshots ==

1. Media Wipe Dashboard with statistics and quick actions
2. Enhanced Delete All Media page with confirmation system
3. Delete Selected Media with enhanced interface
4. Security Audit log viewer
5. Settings configuration page
6. Help and support documentation

== Changelog ==

= 1.1.2 - 2025-07-15 =
* **IMPROVED**: Simplified sidebar menu item names for better navigation
* **IMPROVED**: Enhanced rate limiting message with specific 1-hour wait time
* **IMPROVED**: Removed text-transform uppercase from confirmation input for better UX
* **IMPROVED**: Fixed CSS styling issues for better visual consistency
* **IMPROVED**: Updated DataTable class names for proper functionality
* **ENHANCED**: Plugin information card now displays on dashboard
* **ENHANCED**: Better user experience with cleaner menu structure

= 1.1.1 - 2025-07-15 =
* **COMPLIANCE**: Fixed WordPress.org plugin directory compliance issues
* **FIXED**: Updated WordPress compatibility to 6.7.1 (latest version)
* **FIXED**: Reduced tags from 8 to 5 (removed: security, audit, preview)
* **FIXED**: Shortened description to meet 150 character limit
* **FIXED**: Resolved fatal error from duplicate function declarations (media_wipe_get_all_media, media_wipe_get_unused_media)
* **FIXED**: Removed all emoji icons from README.txt for cleaner appearance
* **NEW**: Professional DataTables.net integration for Delete Selected Media
* **NEW**: Full-screen layout across all plugin pages
* **NEW**: Real-time search with instant filtering
* **NEW**: Multi-column sorting with visual indicators
* **NEW**: Advanced pagination controls (10, 25, 50, 100 items per page)
* **NEW**: Bulk selection with working checkboxes and state persistence
* **NEW**: Enhanced toast notification system with 4 types
* **NEW**: Auto-dismiss notifications with configurable timing
* **NEW**: Progress bars for long-running operations
* **NEW**: Notification queuing system for multiple notifications
* **NEW**: ARIA live regions for screen reader compatibility
* **ENHANCED**: Mobile-responsive design with horizontal scrolling
* **ENHANCED**: Modern UI with WordPress 6.7.1 admin design consistency
* **ENHANCED**: Performance optimizations for large media libraries
* **ENHANCED**: Better error handling with user-friendly messages
* **ENHANCED**: Clean notice styling with proper heading/paragraph structure
* **ENHANCED**: Professional file type badges and media thumbnails

= 1.1.0 - 2025-07-13 =
* **MAJOR UPDATE**: Complete plugin overhaul with enterprise-grade features
* **NEW**: Dedicated WordPress admin menu with professional dashboard
* **NEW**: Advanced multi-step confirmation system for delete operations
* **NEW**: Document preview functionality for PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX files
* **NEW**: Real-time media library statistics and analytics dashboard
* **NEW**: Comprehensive security audit logging and activity tracking
* **NEW**: Settings page for complete plugin configuration
* **NEW**: Built-in help system with comprehensive documentation
* **ENHANCED**: User interface with progress indicators and loading animations
* **ENHANCED**: Security measures with nonce verification, CSRF protection, and rate limiting
* **ENHANCED**: Mobile-responsive design for all devices
* **ENHANCED**: Performance optimizations for large media libraries (1000+ files)
* **ENHANCED**: WordPress admin design consistency and accessibility (WCAG 2.1 AA)
* **ENHANCED**: Error handling with user-friendly messages and graceful degradation
* **ENHANCED**: Code quality following WordPress coding standards
* **ENHANCED**: Translation-ready with proper internationalization
* **ENHANCED**: Batch processing for memory-efficient operations
* **ENHANCED**: Comprehensive logging for compliance and audit purposes
* **ENHANCED**: Toast-style notifications with better user feedback
* **ENHANCED**: Input validation and sanitization for security

= 1.0.3 - 2025-01-12 =
- Added: Assets Updated
- Few minor bug fixes & improvements.

= 1.0.2 - 2025-01-11 =
- Added: Fetch All Media Option.
- Improved: Delete Selected Media
- Few minor bug fixes & improvements.

= 1.0.1 - 2025-01-10 =
- Added: Option for users to delete selected media files.
- Few minor bug fixes & improvements.

= 1.0.0 =
- Initial release.

== Upgrade Notice ==

= 1.0.4 =
MAJOR UPDATE: Complete plugin transformation with enterprise features! New admin menu location, enhanced security, document preview, and comprehensive audit logging. **BACKUP YOUR SITE** before upgrading. Menu moved from Media to dedicated "Media Wipe" section. Review new settings after upgrade.

= 1.0.2 =
Added new features to delete selected media files. Back up your site before upgrading.

== Support ==

For support, documentation, and feature requests, please visit:
* Plugin documentation: Available in the Help & Support section
* Author website: https://mdnahidhasan.netlify.app
* WordPress.org support forums

== Privacy Policy ==

Media Wipe respects your privacy and follows WordPress privacy best practices:
* The plugin only logs activities when logging is enabled in settings
* All logs are stored locally in your WordPress database
* No data is transmitted to external servers
* User information is only logged for audit purposes
* You can clear all logs at any time from the Security Audit page

== Credits ==

Developed by Md. Nahid Hasan with a focus on security, usability, and WordPress best practices.

== License ==

This plugin is licensed under the GPLv2 or later. For details, visit [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html).
