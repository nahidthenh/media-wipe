# Product Requirements Document: Remove Unused Media Feature

**Plugin:** Media Wipe  
**Feature:** Remove Unused Media  
**Version:** 1.2.0  
**Date:** July 15, 2025  
**Status:** Planning Phase  

## 1. Executive Summary

### 1.1 Feature Overview
The "Remove Unused Media" feature will enable WordPress users to identify and safely remove media files that are not being used anywhere on their website. This addresses a common pain point for users who import themes with demo content or accumulate unused media over time.

### 1.2 Business Objectives
- **Primary**: Provide users with a safe, reliable method to clean up unused media files
- **Secondary**: Reduce website storage usage and improve media library organization
- **Tertiary**: Enhance the Media Wipe plugin's value proposition and user satisfaction

### 1.3 Success Metrics
- **User Adoption**: 60% of existing users try the feature within 3 months
- **Safety Record**: <1% of users report accidentally deleted media
- **Performance**: Scan completion within 5 minutes for libraries up to 1,000 files
- **Accuracy**: 85%+ accuracy in unused media detection

## 2. User Stories & Requirements

### 2.1 Primary User Stories

#### US-001: Theme Developer/Designer
**As a** WordPress developer who frequently imports demo themes  
**I want to** identify and remove demo media files that I'm not using  
**So that** my media library only contains relevant content for my project  

**Acceptance Criteria:**
- Can scan entire media library for unused files
- Can preview list of unused media before deletion
- Can selectively choose which files to delete
- Receives clear warnings about detection accuracy

#### US-002: Content Manager
**As a** content manager with a large media library  
**I want to** clean up old, unused media files  
**So that** I can free up storage space and organize my media library  

**Acceptance Criteria:**
- Can filter unused media by date, file type, or size
- Can exclude recently uploaded files from deletion
- Can create whitelist of protected files
- Receives detailed scan results with usage context

#### US-003: Website Maintainer
**As a** website maintainer concerned about storage costs  
**I want to** safely remove unused media without breaking my site  
**So that** I can optimize storage usage while maintaining site functionality  

**Acceptance Criteria:**
- Receives comprehensive safety warnings and recommendations
- Can perform test runs on small batches
- Has access to detailed scan methodology explanation
- Can undo deletions where possible

### 2.2 Functional Requirements

#### FR-001: Media Usage Scanning
- **Requirement**: System must scan multiple contexts to detect media usage
- **Priority**: High
- **Contexts to Scan**:
  - Post content (posts, pages, custom post types)
  - Featured images and thumbnails
  - Gallery attachments and image metadata
  - Widget content and sidebar elements
  - Navigation menu items
  - WordPress Customizer settings
  - Theme template files (optional, with warnings)
  - Basic CSS file references (optional, with warnings)

#### FR-002: Unused Media Detection
- **Requirement**: System must accurately identify unused media files
- **Priority**: High
- **Detection Criteria**:
  - No references found in scanned contexts
  - Not attached to any posts (orphaned files)
  - Not used as featured images
  - Not referenced in active widgets
  - Not used in navigation menus
  - Exclude system-generated thumbnails of used images

#### FR-003: User Interface & Experience
- **Requirement**: Provide intuitive interface for reviewing and managing unused media
- **Priority**: High
- **Interface Elements**:
  - Scan initiation with progress indicator
  - Tabular display of unused media (reuse DataTable component)
  - Thumbnail previews and file information
  - Bulk selection controls (Select All, Select None, Select by Criteria)
  - Individual file selection checkboxes
  - Detailed usage information for each file
  - Clear accuracy warnings and limitations

#### FR-004: Safety & Confirmation Features
- **Requirement**: Implement comprehensive safety measures to prevent accidental deletions
- **Priority**: Critical
- **Safety Features**:
  - Multi-step confirmation process
  - Detailed preview of files to be deleted
  - Backup creation recommendations
  - Exclusion of recently uploaded files (configurable threshold)
  - User-defined whitelist functionality
  - Batch size limitations for testing
  - Clear warnings about detection limitations

#### FR-005: Performance & Scalability
- **Requirement**: Handle large media libraries efficiently
- **Priority**: High
- **Performance Features**:
  - Chunked processing for large libraries
  - Progress indicators with estimated completion time
  - Background processing for extensive scans
  - Result caching to avoid repeated scans
  - Memory-efficient processing
  - Configurable batch sizes

### 2.3 Non-Functional Requirements

#### NFR-001: Performance
- Scan completion within 5 minutes for 1,000 media files
- Memory usage under 256MB during scanning
- No PHP timeout errors during normal operations
- Responsive UI during background processing

#### NFR-002: Reliability
- 85%+ accuracy in unused media detection
- Zero false positives for critical media (featured images, recent uploads)
- Graceful handling of scan interruptions
- Consistent results across multiple scans

#### NFR-003: Usability
- Intuitive interface requiring minimal learning curve
- Clear progress indicators and status messages
- Comprehensive help documentation
- Mobile-responsive design

#### NFR-004: Security
- Proper capability checks (manage_options)
- Nonce verification for all operations
- Input sanitization and validation
- Audit logging for all deletions

## 3. Technical Specifications

### 3.1 Architecture Overview

#### Core Components
1. **Media Scanner Engine** (`MediaWipeScanner` class)
   - Orchestrates scanning across different contexts
   - Manages scan progress and caching
   - Handles batch processing and memory management

2. **Usage Detection Library** (`MediaUsageDetector` class)
   - Context-specific detection methods
   - Extensible architecture for adding new detection types
   - Configurable accuracy levels

3. **Unused Media Manager** (`UnusedMediaManager` class)
   - Manages unused media data and operations
   - Handles whitelist and exclusion functionality
   - Provides data for UI components

4. **User Interface Components**
   - Scan initiation page
   - Results display (DataTable integration)
   - Confirmation and deletion interface

### 3.2 Database Schema

#### New Tables
```sql
-- Cache scan results
CREATE TABLE wp_media_wipe_scan_cache (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    attachment_id bigint(20) NOT NULL,
    scan_date datetime NOT NULL,
    usage_contexts text,
    is_unused tinyint(1) DEFAULT 0,
    confidence_score int(3) DEFAULT 0,
    PRIMARY KEY (id),
    KEY attachment_id (attachment_id),
    KEY scan_date (scan_date)
);

-- User exclusions and whitelist
CREATE TABLE wp_media_wipe_exclusions (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    attachment_id bigint(20) NOT NULL,
    exclusion_type varchar(50) NOT NULL,
    created_date datetime NOT NULL,
    created_by bigint(20) NOT NULL,
    PRIMARY KEY (id),
    KEY attachment_id (attachment_id)
);
```

#### New Options
- `media_wipe_unused_settings`: Feature configuration
- `media_wipe_scan_progress`: Current scan status
- `media_wipe_last_scan`: Last scan completion time

### 3.3 API Endpoints

#### AJAX Endpoints
- `media_wipe_start_unused_scan`: Initiate unused media scan
- `media_wipe_get_scan_progress`: Get current scan progress
- `media_wipe_get_unused_media`: Retrieve unused media results
- `media_wipe_delete_unused_media`: Delete selected unused media
- `media_wipe_exclude_media`: Add media to exclusion list

### 3.4 Integration Points

#### Existing Components
- **DataTable Component**: Reuse for unused media display
- **Notification System**: Extend for scan progress and results
- **Safety Protocols**: Apply existing confirmation mechanisms
- **Audit Logging**: Track unused media operations

#### WordPress Integration
- **Media Library**: Deep integration with attachment queries
- **Customizer API**: Scan customizer settings
- **Widget API**: Scan widget content
- **Menu API**: Scan navigation menu items

## 4. User Experience Design

### 4.1 User Flow

1. **Scan Initiation**
   - User navigates to "Media Wipe → Remove Unused"
   - System displays scan options and warnings
   - User configures scan parameters (optional)
   - User initiates scan with confirmation

2. **Scanning Process**
   - Progress indicator shows scan status
   - Real-time updates on files processed
   - Estimated completion time
   - Option to cancel scan

3. **Results Review**
   - Tabular display of unused media
   - Thumbnail previews and file details
   - Usage context information
   - Confidence scores for each file

4. **Selection & Deletion**
   - Bulk selection tools
   - Individual file selection
   - Preview of deletion impact
   - Multi-step confirmation
   - Deletion execution with progress

### 4.2 Interface Mockups

#### Scan Initiation Page
```
┌─────────────────────────────────────────────────────────┐
│ Remove Unused Media                                     │
├─────────────────────────────────────────────────────────┤
│ ⚠️ Important: This feature scans your media library    │
│ to identify unused files. Detection accuracy is        │
│ approximately 85%. Always backup before deletion.      │
│                                                         │
│ Scan Options:                                          │
│ ☑️ Exclude files uploaded in last 30 days             │
│ ☑️ Exclude featured images                             │
│ ☐ Include theme file scanning (slower, less accurate)  │
│                                                         │
│ [Start Scan] [Learn More]                             │
└─────────────────────────────────────────────────────────┘
```

#### Results Display
```
┌─────────────────────────────────────────────────────────┐
│ Unused Media Results (127 files found)                 │
├─────────────────────────────────────────────────────────┤
│ [Select All] [Select None] [Delete Selected (0)]       │
│                                                         │
│ ┌─┬────────┬──────────┬──────┬──────────┬─────────────┐ │
│ │☐│Preview │ Filename │ Size │ Uploaded │ Confidence  │ │
│ ├─┼────────┼──────────┼──────┼──────────┼─────────────┤ │
│ │☐│[img]   │demo1.jpg │ 2MB  │ 2024-01  │ High (95%)  │ │
│ │☐│[img]   │temp2.png │ 500K │ 2024-02  │ Medium(75%) │ │
│ └─┴────────┴──────────┴──────┴──────────┴─────────────┘ │
└─────────────────────────────────────────────────────────┘
```

## 5. Implementation Plan

### 5.1 Development Phases

#### Phase 1: Core Implementation (3 weeks)
- **Week 1**: Scanner engine and basic detection
- **Week 2**: User interface and DataTable integration
- **Week 3**: Safety features and testing

#### Phase 2: Enhanced Features (2 weeks)
- **Week 4**: Advanced detection methods
- **Week 5**: Performance optimization and caching

#### Phase 3: Polish & Launch (1 week)
- **Week 6**: Documentation, testing, and release preparation

### 5.2 Testing Strategy
- **Unit Testing**: Core detection algorithms
- **Integration Testing**: WordPress compatibility
- **Performance Testing**: Large media libraries
- **User Acceptance Testing**: Real-world scenarios
- **Security Testing**: Permission and validation checks

### 5.3 Launch Criteria
- 85%+ detection accuracy in testing
- Performance benchmarks met
- Comprehensive safety warnings implemented
- Documentation complete
- Security review passed

## 6. Risk Assessment & Mitigation

### 6.1 Technical Risks
- **Detection Accuracy**: Mitigate with conservative defaults and clear warnings
- **Performance Impact**: Mitigate with chunked processing and optimization
- **False Positives**: Mitigate with confidence scoring and manual review

### 6.2 User Experience Risks
- **Accidental Deletions**: Mitigate with multi-step confirmations and backups
- **User Confusion**: Mitigate with clear documentation and warnings
- **Feature Complexity**: Mitigate with intuitive UI and progressive disclosure

### 6.3 Business Risks
- **Support Burden**: Mitigate with comprehensive documentation and testing
- **Reputation Risk**: Mitigate with conservative approach and clear limitations
- **Development Delays**: Mitigate with phased approach and realistic timelines

## 7. Success Metrics & KPIs

### 7.1 Adoption Metrics
- Feature usage rate among existing users
- New user acquisition attributed to feature
- User retention after feature usage

### 7.2 Quality Metrics
- Detection accuracy rate
- False positive rate
- User satisfaction scores
- Support ticket volume

### 7.3 Performance Metrics
- Average scan completion time
- Memory usage during operations
- Error rates and timeout incidents

## 8. Future Enhancements

### 8.1 Advanced Detection
- Page builder integration (Elementor, Gutenberg blocks)
- E-commerce product image detection
- Social media integration scanning

### 8.2 Automation Features
- Scheduled automatic scans
- Auto-deletion of unused files (with strict safeguards)
- Integration with backup plugins

### 8.3 Reporting & Analytics
- Detailed usage reports
- Storage savings tracking
- Historical scan data analysis

## 9. Technical Implementation Details

### 9.1 Core Detection Algorithms

#### High-Accuracy Detection Methods
```php
class MediaUsageDetector {

    // Scan post content for media references
    public function scanPostContent($attachment_id) {
        global $wpdb;
        $attachment_url = wp_get_attachment_url($attachment_id);
        $filename = basename($attachment_url);

        // Search in post content
        $posts = $wpdb->get_results($wpdb->prepare("
            SELECT ID, post_title, post_type
            FROM {$wpdb->posts}
            WHERE post_content LIKE %s
            AND post_status = 'publish'
        ", '%' . $filename . '%'));

        return $posts;
    }

    // Check featured image usage
    public function scanFeaturedImages($attachment_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("
            SELECT post_id
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_thumbnail_id'
            AND meta_value = %d
        ", $attachment_id));
    }

    // Scan widget content
    public function scanWidgetContent($attachment_id) {
        $widgets = get_option('widget_media_image', array());
        $usage = array();

        foreach ($widgets as $widget) {
            if (isset($widget['attachment_id']) &&
                $widget['attachment_id'] == $attachment_id) {
                $usage[] = $widget;
            }
        }

        return $usage;
    }
}
```

#### Medium-Accuracy Detection Methods
```php
// Scan theme files for media references
public function scanThemeFiles($attachment_id) {
    $theme_dir = get_template_directory();
    $attachment_url = wp_get_attachment_url($attachment_id);
    $filename = basename($attachment_url);

    $files_to_scan = array(
        $theme_dir . '/style.css',
        $theme_dir . '/functions.php',
        // Add more theme files as needed
    );

    $usage = array();
    foreach ($files_to_scan as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if (strpos($content, $filename) !== false) {
                $usage[] = $file;
            }
        }
    }

    return $usage;
}
```

### 9.2 Performance Optimization Strategies

#### Chunked Processing
```php
class MediaWipeScanner {
    private $chunk_size = 50;

    public function scanInChunks($attachment_ids) {
        $chunks = array_chunk($attachment_ids, $this->chunk_size);
        $results = array();

        foreach ($chunks as $chunk_index => $chunk) {
            // Update progress
            $this->updateProgress($chunk_index, count($chunks));

            // Process chunk
            foreach ($chunk as $attachment_id) {
                $results[$attachment_id] = $this->scanSingleMedia($attachment_id);
            }

            // Prevent memory leaks
            if ($chunk_index % 10 === 0) {
                wp_cache_flush();
            }
        }

        return $results;
    }
}
```

#### Caching Strategy
```php
public function getCachedScanResult($attachment_id) {
    global $wpdb;

    $cache_result = $wpdb->get_row($wpdb->prepare("
        SELECT usage_contexts, is_unused, confidence_score
        FROM {$wpdb->prefix}media_wipe_scan_cache
        WHERE attachment_id = %d
        AND scan_date > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ", $attachment_id));

    return $cache_result;
}

public function cacheScanResult($attachment_id, $usage_contexts, $is_unused, $confidence) {
    global $wpdb;

    $wpdb->replace(
        $wpdb->prefix . 'media_wipe_scan_cache',
        array(
            'attachment_id' => $attachment_id,
            'scan_date' => current_time('mysql'),
            'usage_contexts' => json_encode($usage_contexts),
            'is_unused' => $is_unused,
            'confidence_score' => $confidence
        ),
        array('%d', '%s', '%s', '%d', '%d')
    );
}
```

### 9.3 Safety Implementation

#### Confidence Scoring System
```php
public function calculateConfidenceScore($usage_contexts) {
    $base_confidence = 100;
    $penalties = array(
        'theme_files_scanned' => -15,  // Theme scanning is less reliable
        'recent_upload' => -20,        // Recent files might not be used yet
        'no_post_content' => -10,      // Not found in posts
        'orphaned_file' => +10,        // Orphaned files likely unused
    );

    foreach ($penalties as $condition => $penalty) {
        if ($this->checkCondition($condition, $usage_contexts)) {
            $base_confidence += $penalty;
        }
    }

    return max(0, min(100, $base_confidence));
}
```

#### Exclusion System
```php
public function addToExclusionList($attachment_id, $exclusion_type = 'manual') {
    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'media_wipe_exclusions',
        array(
            'attachment_id' => $attachment_id,
            'exclusion_type' => $exclusion_type,
            'created_date' => current_time('mysql'),
            'created_by' => get_current_user_id()
        ),
        array('%d', '%s', '%s', '%d')
    );
}

public function isExcluded($attachment_id) {
    global $wpdb;

    $excluded = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM {$wpdb->prefix}media_wipe_exclusions
        WHERE attachment_id = %d
    ", $attachment_id));

    return $excluded > 0;
}
```

---

**Document Version:** 1.0
**Last Updated:** July 15, 2025
**Next Review:** August 15, 2025
