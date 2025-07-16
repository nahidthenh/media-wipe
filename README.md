# Media Wipe - WordPress Media Management Plugin

![Media Wipe Banner](.wordpress-org/banner-1544x500.png)

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-6.8%20tested-blue.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Plugin Version](https://img.shields.io/badge/Version-1.1.2-orange.svg)](https://github.com/mdnahidhasan/media-wipe)

**Media Wipe** is a powerful WordPress plugin designed for efficient media library management. Clean up your WordPress media library with confidence using advanced safety features, professional DataTable interface, and comprehensive audit logging.

## Key Features

### **Selective Media Deletion**
- **Professional DataTable Interface** - Browse, search, and filter media files with ease
- **Bulk Selection** - Select multiple files for deletion with checkboxes
- **Real-time Search** - Find specific files instantly
- **Advanced Filtering** - Filter by file type, date, and size
- **Responsive Design** - Works perfectly on all devices

### **Complete Media Wipe**
- **Delete All Media** - Remove all media files with multi-step confirmation
- **Safety Protocols** - Multiple confirmation layers prevent accidental deletions
- **Backup Verification** - Ensures users have backups before proceeding
- **Progress Tracking** - Real-time progress indicators during operations

### **Security & Safety**
- **Multi-step Confirmation** - Configurable safety requirements
- **Backup Verification** - Mandatory backup confirmation (configurable)
- **Text Confirmation** - Type "DELETE ALL MEDIA" to confirm (configurable)
- **Rate Limiting** - Prevents abuse with 1-hour cooldown periods
- **Audit Logging** - Complete activity logs for compliance

### **Professional Interface**
- **WordPress Admin Integration** - Seamless integration with WordPress admin
- **Modern Design** - Clean, professional interface matching WordPress standards
- **Mobile Responsive** - Full functionality on mobile devices
- **Accessibility** - WCAG 2.1 AA compliant design
- **Help & Support** - Built-in documentation and support system

## Dashboard Features

- **Real-time Statistics** - View media library metrics at a glance
- **Plugin Information** - Current version, WordPress compatibility, PHP version
- **Quick Actions** - Direct access to all plugin features
- **Safety Notices** - Important warnings and best practices

## Security Features

- **Nonce Verification** - CSRF protection on all forms
- **Capability Checks** - Only administrators can access features
- **Input Sanitization** - All user inputs are properly sanitized
- **Rate Limiting** - Prevents rapid successive deletion attempts
- **Activity Logging** - Comprehensive audit trail

## Configurable Settings

- **Backup Confirmation** - Require backup verification (enable/disable)
- **Text Confirmation** - Require typing confirmation text (enable/disable)
- **Document Preview** - Show file previews in dialogs (enable/disable)
- **Activity Logging** - Enable/disable deletion audit logs

## WordPress Compatibility

- **WordPress Version**: 5.0+ (Tested up to 6.8)
- **PHP Version**: 7.4 to 8.3
- **Multisite**: Fully compatible
- **Themes**: Works with all standard WordPress themes

## Installation

### From WordPress Admin
1. Go to **Plugins > Add New**
2. Search for **"Media Wipe"**
3. Click **Install Now**
4. **Activate** the plugin

### Manual Installation
1. Download the plugin ZIP file
2. Upload to `/wp-content/plugins/` directory
3. Extract the files
4. Activate through WordPress admin

### From GitHub
```bash
cd wp-content/plugins/
git clone https://github.com/mdnahidhasan/media-wipe.git
```

## Usage Guide

### Delete Selected Media
1. Navigate to **Media Wipe → Delete Selected**
2. Browse your media files using the DataTable
3. Use search and filters to find specific files
4. Select files using checkboxes or "Select All"
5. Click **"Delete Selected"** and confirm

### Delete All Media
1. Navigate to **Media Wipe → Delete All**
2. Review media library statistics
3. Complete required confirmations (based on settings)
4. Type confirmation text if required
5. Confirm deletion

### Configure Settings
1. Navigate to **Media Wipe → Settings**
2. Configure safety and confirmation requirements
3. Enable/disable features as needed
4. Save settings

## Configuration

### Safety Settings
- **Backup Confirmation**: Require users to confirm they have backups
- **Text Confirmation**: Require typing "DELETE ALL MEDIA" to confirm
- **Both Disabled**: Simple single-checkbox confirmation

### Interface Settings
- **Document Preview**: Show file information in confirmation dialogs
- **Activity Logging**: Keep detailed logs of all deletion activities

## Changelog

### Version 1.1.2 (2025-07-15)
- **IMPROVED**: Simplified sidebar menu item names
- **IMPROVED**: Enhanced rate limiting with specific wait times
- **IMPROVED**: Better settings integration with delete operations
- **ENHANCED**: Plugin information display on dashboard
- **FIXED**: Settings now properly control confirmation requirements

## Contributing

We welcome contributions! Please feel free to submit issues, feature requests, or pull requests.

### Development Setup
1. Clone the repository
2. Install WordPress development environment
3. Activate the plugin
4. Make your changes
5. Test thoroughly
6. Submit a pull request

## Support

- **Documentation**: Built-in help system in WordPress admin
- **Issues**: [GitHub Issues](https://github.com/mdnahidhasan/media-wipe/issues)
- **Email**: [mail.mdnahidhasan@gmail.com](mailto:mail.mdnahidhasan@gmail.com)
- **Website**: [mdnahidhasan.netlify.app](https://mdnahidhasan.netlify.app)

## License

This plugin is licensed under the [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

## Important Notice

**Media Wipe performs permanent deletions that cannot be undone.** Always create complete backups of your website before using any deletion features. Test on a staging site first.

## Author

**Md. Nahid Hasan**
- Website: [mdnahidhasan.netlify.app](https://mdnahidhasan.netlify.app)
- Email: [mail.mdnahidhasan@gmail.com](mailto:mail.mdnahidhasan@gmail.com)
- GitHub: [@mdnahidhasan](https://github.com/mdnahidhasan)

---

**If you find this plugin helpful, please consider giving it a star on GitHub!**
