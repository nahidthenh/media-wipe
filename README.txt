=== Media Wipe ===
Contributors: mdnahidhasan
Author URI: https://mdnahidhasan.netlify.app
Tags: media, delete, cleanup, unused, bulk
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.3.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered WordPress media management with intelligent unused media detection and enterprise security. Transform your cleanup workflow!

== Description ==

Media Wipe is a powerful and secure WordPress plugin that provides comprehensive media library management capabilities. With revolutionary AI-powered unused media detection, professional DataTable interfaces, advanced confirmation systems, and detailed audit logging, it's the smartest and safest way to clean up your WordPress media library.

**Key Features:**

* **AI-Powered Unused Media Detection** - Revolutionary intelligent scanning identifies truly unused media files with confidence scoring (High/Medium/Low)
* **Professional DataTable Interface** - Modern, responsive tables with search, sorting, pagination, and bulk selection for both Delete Selected and Delete Unused features
* **Smart Content Analysis** - Scans posts, pages, widgets, menus, and theme files for comprehensive media usage detection
* **Confidence Scoring System** - 0-100% confidence scores with visual badges help you make safe deletion decisions
* **Delete Selected Media** - Browse and select specific media files using advanced DataTable with thumbnails, file information, and individual delete options
* **Delete Unused Media** - AI-powered detection with "Select High Confidence Only" for safest automated cleanup
* **Delete All Media** - Complete media library cleanup with multi-step confirmation process and backup verification
* **Deletion History & Audit Logging** - Professional DataTable showing all deletion activities with timestamps, user information, and detailed logs
* **Dashboard Overview** - Comprehensive statistics, recent activity, and quick action buttons
* **Advanced Safety Features** - Multiple confirmation layers, backup verification, and rate limiting prevent accidental deletions
* **Mobile-Optimized Interface** - Fully responsive design with touch-friendly controls and horizontal scrolling tables

**DataTable Features:**

* Professional responsive tables with search, sort, and pagination
* Bulk selection with "Select All" and "Select None" controls
* Individual file actions (delete single files)
* Thumbnail previews with hover effects
* File type badges and size information
* Cross-page selection support for large media libraries
* Mobile-optimized with horizontal scrolling

**Delete Selected Media:**

* Browse all media files in professional DataTable format
* Search and filter by filename, type, or upload date
* Select individual files or use bulk selection
* Thumbnail previews for images, icons for documents
* Individual delete buttons for single file removal
* Real-time selection counter and delete button state

**Delete Unused Media (AI-Powered):**

* Intelligent scanning with confidence scoring (0-100%)
* Visual confidence badges: High (90-100%), Medium (75-89%), Low (0-74%)
* "Select High Confidence Only" for safest automated cleanup
* Scans posts, pages, widgets, menus, and theme files
* Real-time scan progress with file counts and status
* Advanced filtering by confidence level

**Dashboard Overview:**

* Media library statistics with file counts by type
* Total storage usage calculation
* Recent deletion activity timeline
* Quick action buttons for all features
* Modern hero section with key metrics
* System information and plugin status

**Deletion History & Audit Logging:**

* Professional DataTable showing all deletion activities
* Detailed logs with timestamps, user info, and IP addresses
* Activity and security event tracking
* Configurable logging (enable/disable)
* Log clearing functionality
* Export capabilities for compliance

**Security & Safety Features:**

* Nonce verification for all operations
* Capability-based access control (manage_options required)
* CSRF protection and input validation
* Rate limiting to prevent abuse (configurable limits per hour)
* Multi-step confirmation processes with backup verification
* Comprehensive audit trails and activity logging
* Security headers implementation

**Use Cases:**

* **Site Cleanup**: Intelligently identify and remove unused media files from multiple site imports or theme changes
* **Development to Production**: Clean up development sites before going live with AI-powered unused media detection
* **Storage Optimization**: Reduce hosting costs by removing truly unused media files with confidence scoring
* **Website Redesigns**: Safely bulk delete old media files during complete website overhauls
* **Media Library Maintenance**: Keep organized and efficient media libraries with automated unused file detection
* **Compliance & Auditing**: Track all deletion activities with detailed logs for business compliance requirements
* **Large Media Management**: Efficiently manage media libraries with 1000+ files using professional DataTable interfaces

**Performance & Technical:**

* Memory-efficient batch processing for large operations (1000+ files)
* Optimized database queries with early exit scanning
* Professional DataTables.net integration for responsive interfaces
* Caching and performance optimizations
* AJAX-powered operations with progress tracking
* Mobile-optimized responsive design

**WordPress Compatibility:**

* Fully tested with WordPress 6.8 (latest version)
* Compatible with WordPress 5.0 and above
* Works with PHP 7.4 to 8.3
* Single-site installations (multisite support planned)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/media-wipe` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to **Media Wipe** in your WordPress admin menu (located after the Media menu).
4. Explore the Dashboard to see your media library statistics and recent activity.
5. **Always create a complete backup** before performing any deletion operations.

**Quick Start Guide:**
1. **Dashboard**: View media statistics and recent activity
2. **Delete Selected**: Browse and select specific files to delete using the professional DataTable
3. **Delete Unused**: Run AI-powered scan to find unused media files with confidence scoring
4. **Deletion History**: Review all deletion activities and manage audit logging

== Frequently Asked Questions ==

= Is this plugin compatible with my WordPress version? =

Yes, Media Wipe is fully tested and compatible with WordPress 6.8 (the latest version) and works with WordPress 5.0 and above. It also supports PHP 7.4 to 8.3 and is designed for single-site installations.

= Is it safe to use this plugin? =

Yes, Media Wipe includes multiple safety layers including AI-powered confidence scoring, backup verification requirements, multi-step confirmation processes, and comprehensive audit logging. However, always create a complete backup before using any deletion features.

= Can I recover deleted files? =

No, once files are deleted using this plugin, they cannot be recovered. This is why the plugin requires explicit confirmation that you have created a backup before proceeding. All deletions are permanent and irreversible.

= How does the DataTable interface work? =

The plugin uses professional DataTables with search, sorting, pagination, and bulk selection. You can search for files, sort by any column, select multiple files across pages, and perform bulk operations. The interface is fully responsive and works on mobile devices.

= What's the difference between Delete Selected and Delete Unused? =

**Delete Selected** lets you manually browse and select specific media files to delete using a professional DataTable interface. **Delete Unused** uses AI-powered scanning to automatically identify media files that aren't being used anywhere on your site, with confidence scoring to help you make safe decisions.

= How does the AI-powered unused media detection work? =

The system scans your posts, pages, widgets, menus, and theme files to identify media usage. It assigns confidence scores (0-100%) with visual badges: High (90-100%) = very safe to delete, Medium (75-89%) = review recommended, Low (0-74%) = manual review required.

= What does the confidence score mean? =

The confidence score indicates how certain the system is that a file is unused. **High confidence (90-100%)** files are very safe to delete. **Medium confidence (75-89%)** files should be reviewed. **Low confidence (0-74%)** files require manual verification before deletion.

= How does the Deletion History feature work? =

The Deletion History page shows all deletion activities in a professional DataTable with timestamps, user information, IP addresses, and detailed logs. You can enable/disable logging, clear logs, and export data for compliance purposes.

= Can I delete individual files or only in bulk? =

Both! You can delete individual files using the "Delete" button in each table row, or select multiple files and use "Delete Selected" for bulk operations. The interface supports both single and bulk deletion workflows.

= How does rate limiting work? =

The plugin implements rate limiting to prevent abuse, allowing a maximum number of deletion operations per hour. This helps maintain system stability and prevents accidental mass deletions.

= How accurate is the unused media detection? =

The AI-powered detection system is highly accurate, scanning posts, pages, widgets, menus, and theme files. Files with 90%+ confidence scores are very safe to delete. For maximum safety, always start with "Select High Confidence Only" and review lower-confidence files manually.

= Is the plugin compatible with multisite installations? =

Currently, the plugin is designed for single-site installations. Multisite support is planned for future versions.

= How do I get started with the plugin? =

After activation, visit the **Media Wipe Dashboard** to see your media library statistics. Use **Delete Selected** to manually choose files, or **Delete Unused** for AI-powered detection. Always start with "Select High Confidence Only" for the safest automated cleanup.

== Screenshots ==

1. Media Wipe Dashboard with statistics, recent activity, and quick action buttons
2. Delete Selected Media with professional DataTable interface, search, and bulk selection
3. Delete Unused Media with AI-powered scanning, confidence scoring, and "Select High Confidence Only" feature
4. Deletion History with comprehensive audit logging and professional DataTable
5. AI scan results showing confidence badges (High/Medium/Low) and detailed file information
6. Mobile-responsive interface with touch-friendly controls and horizontal scrolling tables

== Changelog ==

= 1.3.2 - 2025-10-08 =
**Enhanced DataTable Interface & User Experience**
- Few minor bug fixes and improvements.

= 1.3.1 - 2025-10-06 =
**Enhanced DataTable Interface & User Experience**
- **ENHANCED**: Professional DataTable styling consistency between Delete Selected and Delete Unused tables
- **IMPROVED**: Delete Unused table now matches Delete Selected table design while preserving unique Confidence column
- **ENHANCED**: Unified button styling and spacing across all DataTable interfaces
- **IMPROVED**: Consistent hover effects, transitions, and responsive behavior
- **ENHANCED**: Mobile optimization with horizontal scrolling and touch-friendly controls
- **IMPROVED**: DataTable configuration standardization (25 items per page, consistent sorting)
- **ENHANCED**: Visual consistency with matching table headers, row styling, and image thumbnails
- **IMPROVED**: Professional confidence badge styling (High/Medium/Low indicators)
- **ENHANCED**: Streamlined user experience with identical interaction patterns across features
- **PERFORMANCE**: Optimized CSS and JavaScript for better loading performance

= 1.3.0 - 2025-07-30 =
**🎉 MAJOR RELEASE: Complete Interface Transformation**
- **REVOLUTIONARY**: Complete plugin simplification - removed Settings and Support pages
- **STREAMLINED**: Clean 4-page structure: Dashboard → Delete Selected → Delete Unused → Deletion History
- **ENHANCED**: Delete Unused with beautiful "No Files Found" messages and smarter defaults
- **PROFESSIONAL**: Modern Deletion History with DataTable, search, sort, and pagination
- **OPTIMIZED**: Desktop experience with max-width constraints for large screens
- **PERFORMANCE**: Removed 500+ lines of unused code for 20% faster loading
- **DESIGN**: Beautiful gradient icons, modern styling, and WordPress integration
- **POSITIONING**: Logical menu placement right after Media menu (position 11)
- **FIXED**: Critical undefined variable errors and broken functionality
- **UX**: Simplified interface with reduced cognitive load and better feedback

= 1.2.4 - 2025-07-30 =
**Major Interface Improvements & Bug Fixes**
- **FIXED**: Critical undefined variable error in Deletion History page
- **ENHANCED**: Professional DataTable interface for Deletion History with search, sort, and pagination
- **ENHANCED**: Beautiful statistics overview with visual activity counts
- **SIMPLIFIED**: Removed broken "View Details" functionality for cleaner interface
- **IMPROVED**: Desktop optimization with max-width constraints for large screens (1920px+)
- **IMPROVED**: Menu positioning - moved to position 11 (right after Media menu)
- **PERFORMANCE**: Removed 500+ lines of unused JavaScript and CSS code
- **DESIGN**: Modern gradient icons and professional button styling
- **UX**: Enhanced mobile responsiveness across all pages

= 1.2.3 - 2025-07-28 =
**Streamlined Menu Structure**
- **Removed Settings page** - moved logging toggle directly to Deletion History page
- **Removed Support page** - plugin interface is now self-explanatory
- **Removed warning sections** from Delete Selected and Delete Unused pages
- **Clean 4-page structure**: Dashboard → Delete Selected → Delete Unused → Deletion History
- **Removed Advanced Scan option** - Basic scan is now the optimal default
- **Added beautiful "No Files Found" message** for empty media libraries with encouraging feedback
- **Improved scan result handling** with better user notifications
- **Safety options unchecked by default** for more comprehensive scanning

= 1.2.2 - 2025-07-26 =
* **NEW**: Dashboard Tabbed Navigation - Modern tabbed interface with Overview, Quick Actions, Statistics, Recent Activity, and System Info sections
* **NEW**: Plugin Notice Suppression - Clean admin interface by hiding notices from other plugins on Media Wipe pages
* **NEW**: Release Date Display - Added release date information to Support page for better transparency
* **ENHANCED**: Dashboard User Experience - Complete overhaul of dashboard navigation and content organization
* **ENHANCED**: Settings Page Design - Modern aesthetic with improved form layout and styling consistency
* **FIXED**: Dashboard URL Routing - Resolved URL mismatch between main dashboard and redirect targets
* **FIXED**: Support Page Styling - Removed left border color from featured feature cards for cleaner appearance
* **IMPROVED**: Code Organization - Better plugin structure and maintainability
* **IMPROVED**: CSS Consistency - Standardized styling across all plugin pages
* **IMPROVED**: JavaScript Enhancement - Added interactive tab functionality for better user experience

= 1.2.1 - 2025-07-21 =
* **MAJOR FEATURE**: Revolutionary AI-Powered Unused Media Detection system
* **NEW**: Smart Content Analysis - Scans posts, pages, widgets, menus, and theme files
* **NEW**: Confidence Scoring System - 0-100% confidence scores for safe deletion decisions
* **NEW**: Advanced Scan Options - Basic (fast) vs Advanced (thorough) scanning modes
* **NEW**: "Select High Confidence Only" button for safest automated cleanup
* **NEW**: Real-time scan progress tracking with file counts and status updates
* **NEW**: Professional results interface with DataTables integration
* **NEW**: Multiple image size detection - Finds usage of thumbnails, medium, large sizes
* **NEW**: WordPress Blocks scanning - Gutenberg image, gallery, media-text blocks
* **NEW**: Enhanced widget scanning - All widget types including text and custom HTML
* **NEW**: Theme file scanning - Advanced mode checks theme files for hardcoded references
* **NEW**: Gallery shortcode detection - WordPress gallery usage patterns
* **NEW**: Serialized data scanning - Complex field and plugin data structures
* **ENHANCED**: Bulk selection now works across DataTables pagination
* **ENHANCED**: Improved AJAX handling with proper error management and nonce security
* **ENHANCED**: Production-ready logging system with debug mode controls
* **ENHANCED**: Cross-page selection support for large result sets
* **ENHANCED**: Memory-efficient scanning for large media libraries (1000+ files)
* **ENHANCED**: Smart filtering by confidence level for targeted cleanup
* **FIXED**: Resolved AJAX action conflicts between different deletion methods
* **FIXED**: Improved nonce handling for enhanced security across all operations
* **FIXED**: DataTables integration issues affecting checkbox selection and deletion
* **FIXED**: Console logging cleanup for production environments
* **PERFORMANCE**: Optimized database queries for faster scanning
* **PERFORMANCE**: Early exit scanning when usage is detected for speed improvement
* **PERFORMANCE**: Efficient batch processing for large media collections

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

= 1.3.1 =
Enhanced DataTable interface with unified styling! Delete Selected and Delete Unused tables now have consistent professional appearance while preserving unique Confidence column functionality. Improved mobile responsiveness and performance optimizations. Safe upgrade with no breaking changes.

= 1.3.0 =
🎉 MAJOR RELEASE: Complete interface transformation! Simplified 4-page structure, professional Deletion History with DataTable, beautiful empty states, and 20% performance improvement. Settings/Support pages removed - everything is now self-explanatory. **BACKUP RECOMMENDED** before this major upgrade.

= 1.2.1 =
REVOLUTIONARY UPDATE: AI-Powered Unused Media Detection! Intelligently identifies truly unused media files with confidence scoring. Perfect for cleaning up sites with multiple imports. **BACKUP YOUR SITE** before using new detection features. New "Delete Unused" menu item available.

= 1.0.4 =
MAJOR UPDATE: Complete plugin transformation with enterprise features! New admin menu location, enhanced security, document preview, and comprehensive audit logging. **BACKUP YOUR SITE** before upgrading. Menu moved from Media to dedicated "Media Wipe" section. Review new settings after upgrade.

= 1.0.2 =
Added new features to delete selected media files. Back up your site before upgrading.

== Support ==

For support, documentation, and feature requests, please visit:
* **Plugin Dashboard**: Access comprehensive statistics and quick actions within WordPress admin
* **Deletion History**: View detailed audit logs and manage logging settings
* **Author website**: https://mdnahidhasan.netlify.app
* **WordPress.org support forums**: Community support and discussions

== Privacy Policy ==

Media Wipe respects your privacy and follows WordPress privacy best practices:
* The plugin only logs activities when logging is enabled in Deletion History settings
* All logs are stored locally in your WordPress database
* No data is transmitted to external servers or third parties
* User information is only logged for audit and security purposes
* You can enable/disable logging and clear all logs at any time from the Deletion History page
* IP addresses and user information are logged only for security audit purposes

== Credits ==

Developed by Md. Nahid Hasan with a focus on security, usability, and WordPress best practices.

== License ==

This plugin is licensed under the GPLv2 or later. For details, visit [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html).
