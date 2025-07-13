# Media Wipe Plugin - Technical Documentation

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [File Structure](#file-structure)
3. [Core Components](#core-components)
4. [Security Implementation](#security-implementation)
5. [Database Schema](#database-schema)
6. [API Reference](#api-reference)
7. [Hooks and Filters](#hooks-and-filters)
8. [Performance Considerations](#performance-considerations)
9. [Development Guidelines](#development-guidelines)
10. [Testing](#testing)

## Architecture Overview

Media Wipe follows WordPress plugin best practices with a modular architecture:

```
Media Wipe Plugin
├── Core Plugin Class (Media_Wipe_Plugin)
├── Admin Menu System
├── Security Layer
├── Helper Functions
├── AJAX Handlers
└── Asset Management
```

### Design Patterns
- **Singleton Pattern**: Main plugin class uses singleton pattern
- **Factory Pattern**: Settings and configuration management
- **Observer Pattern**: WordPress hooks and actions
- **Strategy Pattern**: Different deletion strategies (all vs selected)

## File Structure

```
media-wipe/
├── media-wipe.php              # Main plugin file
├── README.txt                  # WordPress.org readme
├── PRODUCT_REQUIREMENTS_DOCUMENT.md
├── TECHNICAL_DOCUMENTATION.md
├── includes/
│   ├── helper-functions.php    # Utility functions
│   ├── admin-menu.php         # Admin interface
│   ├── delete-all-media.php   # Delete all functionality
│   └── delete-selected-media.php # Delete selected functionality
├── assets/
│   ├── css/
│   │   └── admin-style.css    # Admin styles
│   └── js/
│       └── admin-script.js    # Admin JavaScript
└── languages/                 # Translation files (future)
```

## Core Components

### 1. Main Plugin Class (`Media_Wipe_Plugin`)

**Location**: `media-wipe.php`

**Responsibilities**:
- Plugin initialization and lifecycle management
- Asset enqueuing
- Component loading
- Activation/deactivation hooks

**Key Methods**:
```php
public static function get_instance()    // Singleton instance
public function load_textdomain()       // Translation loading
public function init_components()       // Component initialization
public function enqueue_admin_assets()  // Asset management
public function activate()              // Plugin activation
public function deactivate()            // Plugin deactivation
```

### 2. Helper Functions (`helper-functions.php`)

**Core Functions**:
- `media_wipe_get_settings()` - Settings management with caching
- `media_wipe_get_media_info()` - Media file information retrieval
- `media_wipe_validate_media_ids()` - Input validation
- `media_wipe_check_rate_limit()` - Rate limiting implementation
- `media_wipe_log_activity()` - Activity logging
- `media_wipe_log_security_event()` - Security event logging

### 3. Admin Menu System (`admin-menu.php`)

**Menu Structure**:
```
Media Wipe (toplevel_page_media-wipe)
├── Dashboard (media-wipe)
├── Delete All Media (media-wipe-delete-all)
├── Delete Selected Media (media-wipe-delete-selected)
├── Settings (media-wipe-settings)
├── Security Audit (media-wipe-security)
└── Help & Support (media-wipe-help)
```

### 4. Deletion Components

**Delete All Media** (`delete-all-media.php`):
- Multi-step confirmation system
- Document preview functionality
- Batch processing for performance
- Progress tracking

**Delete Selected Media** (`delete-selected-media.php`):
- Individual file selection
- Enhanced confirmation modal
- File type detection and preview
- Bulk operations with validation

## Security Implementation

### 1. Authentication and Authorization

```php
// Capability checks
if (!current_user_can('manage_options')) {
    wp_die(__('Insufficient permissions.', 'media-wipe'));
}

// Nonce verification
if (!wp_verify_nonce($_POST['nonce'], 'media_wipe_action')) {
    wp_send_json_error(['message' => 'Security check failed']);
}
```

### 2. Input Validation and Sanitization

```php
// Media ID validation
function media_wipe_validate_media_ids($media_ids) {
    $validated_ids = array();
    foreach ($media_ids as $id) {
        $id = absint($id);
        if ($id > 0 && get_post_type($id) === 'attachment') {
            $validated_ids[] = $id;
        }
    }
    return $validated_ids;
}
```

### 3. Rate Limiting

```php
function media_wipe_check_rate_limit($action, $count = 1) {
    $limits = [
        'delete_selected' => 500, // Max 500 files per hour
        'delete_all' => 5,        // Max 5 operations per hour
    ];
    // Implementation details...
}
```

### 4. Security Headers

```php
function media_wipe_set_security_headers() {
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
    }
}
```

## Database Schema

### WordPress Options Used

| Option Name | Description | Data Type |
|-------------|-------------|-----------|
| `media_wipe_settings` | Plugin configuration | Array |
| `media_wipe_activity_log` | Activity logging | Array |
| `media_wipe_security_log` | Security events | Array |
| `media_wipe_version` | Plugin version | String |

### Settings Structure

```php
$settings = [
    'require_backup_confirmation' => 1,
    'require_text_confirmation' => 1,
    'show_document_preview' => 1,
    'enable_logging' => 1
];
```

### Log Entry Structure

```php
$log_entry = [
    'timestamp' => '2025-01-13 10:30:00',
    'user_id' => 1,
    'user_login' => 'admin',
    'action' => 'delete_all_success',
    'data' => ['deleted_count' => 150],
    'ip_address' => '192.168.1.1'
];
```

## API Reference

### AJAX Endpoints

#### Delete All Media
- **Action**: `media_wipe_delete_all_media`
- **Method**: POST
- **Parameters**:
  - `nonce`: Security nonce
  - `confirmation`: Confirmation text
- **Response**: JSON with success/error status

#### Delete Selected Media
- **Action**: `media_wipe_delete_unused_media`
- **Method**: POST
- **Parameters**:
  - `nonce`: Security nonce
  - `media_ids`: Array of attachment IDs
- **Response**: JSON with success/error status

### JavaScript API

#### Global Object: `mediaWipeAjax`
```javascript
{
    ajaxurl: '/wp-admin/admin-ajax.php',
    nonce: 'abc123...',
    strings: {
        confirmDelete: 'Are you sure...',
        // ... other localized strings
    }
}
```

## Hooks and Filters

### Actions
- `media_wipe_before_delete_all` - Fired before deleting all media
- `media_wipe_after_delete_all` - Fired after deleting all media
- `media_wipe_before_delete_selected` - Fired before deleting selected media
- `media_wipe_after_delete_selected` - Fired after deleting selected media

### Filters
- `media_wipe_delete_batch_size` - Modify batch size for deletions
- `media_wipe_rate_limits` - Customize rate limiting rules
- `media_wipe_document_types` - Add/remove supported document types
- `media_wipe_settings_defaults` - Modify default settings

### Usage Examples

```php
// Modify batch size
add_filter('media_wipe_delete_batch_size', function($size) {
    return 25; // Reduce batch size for slower servers
});

// Add custom logging
add_action('media_wipe_after_delete_all', function($result) {
    error_log('Media Wipe: Deleted ' . $result['deleted_count'] . ' files');
});
```

## Performance Considerations

### 1. Batch Processing
Large deletion operations are processed in batches to prevent:
- Memory exhaustion
- Script timeouts
- Server overload

### 2. Caching Strategy
- Settings cached in static variables
- Media info cached using WordPress transients
- Database query optimization

### 3. Memory Management
```php
// Increase time limit for large operations
if (!ini_get('safe_mode')) {
    set_time_limit(300);
}

// Process in batches
$batch_size = apply_filters('media_wipe_delete_batch_size', 50);
$batches = array_chunk($attachments, $batch_size);
```

## Development Guidelines

### 1. Coding Standards
- Follow WordPress Coding Standards
- Use proper escaping and sanitization
- Implement proper error handling
- Add comprehensive PHPDoc comments

### 2. Security Best Practices
- Always verify nonces
- Check user capabilities
- Validate and sanitize input
- Use prepared statements for database queries

### 3. Performance Best Practices
- Use WordPress caching mechanisms
- Optimize database queries
- Implement proper pagination
- Use transients for expensive operations

## Testing

### 1. Unit Testing
Test individual functions and methods:
```php
// Test media ID validation
$valid_ids = media_wipe_validate_media_ids([1, 2, 'invalid', 3]);
$this->assertEquals([1, 2, 3], $valid_ids);
```

### 2. Integration Testing
Test component interactions:
- AJAX endpoint functionality
- Database operations
- File system operations

### 3. Security Testing
- Test nonce verification
- Test capability checks
- Test input validation
- Test rate limiting

### 4. Performance Testing
- Test with large media libraries
- Memory usage monitoring
- Execution time analysis
- Database query optimization

### 5. Browser Testing
- Cross-browser compatibility
- Mobile responsiveness
- JavaScript functionality
- CSS rendering

## Troubleshooting

### Common Issues

1. **Memory Limit Exceeded**
   - Reduce batch size
   - Increase PHP memory limit
   - Process in smaller chunks

2. **Script Timeout**
   - Increase max execution time
   - Use AJAX for long operations
   - Implement progress tracking

3. **Permission Errors**
   - Check file permissions
   - Verify user capabilities
   - Review server configuration

### Debug Mode
Enable WordPress debug mode for detailed error logging:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Future Enhancements

### Planned Features
- Multisite support
- Advanced filtering options
- Scheduled cleanup operations
- Integration with backup plugins
- REST API endpoints
- CLI commands

### Extension Points
The plugin is designed to be extensible through:
- WordPress hooks and filters
- Modular architecture
- Configuration options
- Custom post types support
