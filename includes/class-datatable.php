<?php
/**
 * Advanced DataTable Component for Media Wipe Plugin
 * 
 * Handles server-side pagination, sorting, filtering, and search functionality
 * for large media libraries with optimized performance.
 * 
 * @package MediaWipe
 * @since 1.1.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

/**
 * Media Wipe DataTable Class
 */
class Media_Wipe_DataTable {
    
    /**
     * Default items per page
     */
    const DEFAULT_PER_PAGE = 25;
    
    /**
     * Maximum items per page
     */
    const MAX_PER_PAGE = 100;
    
    /**
     * Search debounce delay in milliseconds
     */
    const SEARCH_DEBOUNCE = 300;
    
    /**
     * Initialize the DataTable component
     */
    public function __construct() {
        add_action('wp_ajax_media_wipe_get_media_page', array($this, 'ajax_get_media_page'));
        add_action('wp_ajax_media_wipe_search_media', array($this, 'ajax_search_media'));
        add_action('wp_ajax_media_wipe_filter_media', array($this, 'ajax_filter_media'));
    }
    
    /**
     * Get paginated media data via AJAX
     */
    public function ajax_get_media_page() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'media_wipe_ajax_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'media-wipe')));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'media-wipe')));
        }
        
        // Get and validate parameters
        $page = max(1, intval($_POST['page'] ?? 1));
        $per_page = min(self::MAX_PER_PAGE, max(10, intval($_POST['per_page'] ?? self::DEFAULT_PER_PAGE)));
        $orderby = sanitize_text_field($_POST['orderby'] ?? 'date');
        $order = sanitize_text_field($_POST['order'] ?? 'desc');
        $search = sanitize_text_field($_POST['search'] ?? '');
        $filters = $this->sanitize_filters($_POST['filters'] ?? array());
        
        // Get media data
        $result = $this->get_media_data($page, $per_page, $orderby, $order, $search, $filters);
        
        wp_send_json_success($result);
    }
    
    /**
     * Search media via AJAX
     */
    public function ajax_search_media() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'media_wipe_ajax_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'media-wipe')));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'media-wipe')));
        }
        
        $search = sanitize_text_field($_POST['search'] ?? '');
        $page = max(1, intval($_POST['page'] ?? 1));
        $per_page = min(self::MAX_PER_PAGE, max(10, intval($_POST['per_page'] ?? self::DEFAULT_PER_PAGE)));
        
        $result = $this->search_media($search, $page, $per_page);
        
        wp_send_json_success($result);
    }
    
    /**
     * Filter media via AJAX
     */
    public function ajax_filter_media() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'media_wipe_ajax_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'media-wipe')));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'media-wipe')));
        }
        
        $filters = $this->sanitize_filters($_POST['filters'] ?? array());
        $page = max(1, intval($_POST['page'] ?? 1));
        $per_page = min(self::MAX_PER_PAGE, max(10, intval($_POST['per_page'] ?? self::DEFAULT_PER_PAGE)));
        
        $result = $this->filter_media($filters, $page, $per_page);
        
        wp_send_json_success($result);
    }
    
    /**
     * Get media data with pagination, sorting, and filtering
     * 
     * @param int $page Current page number
     * @param int $per_page Items per page
     * @param string $orderby Column to sort by
     * @param string $order Sort direction (asc/desc)
     * @param string $search Search term
     * @param array $filters Filter parameters
     * @return array Formatted media data
     */
    public function get_media_data($page = 1, $per_page = 25, $orderby = 'date', $order = 'desc', $search = '', $filters = array()) {
        global $wpdb;
        
        // Calculate offset
        $offset = ($page - 1) * $per_page;
        
        // Build base query
        $select = "SELECT p.ID, p.post_title, p.post_date, p.post_mime_type, p.guid";
        $from = "FROM {$wpdb->posts} p";
        $where = "WHERE p.post_type = 'attachment'";
        $orderby_clause = $this->build_orderby_clause($orderby, $order);
        $limit = $wpdb->prepare("LIMIT %d OFFSET %d", $per_page, $offset);
        
        // Add search conditions
        if (!empty($search)) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where .= $wpdb->prepare(" AND p.post_title LIKE %s", $search_term);
        }
        
        // Add filter conditions
        $where .= $this->build_filter_conditions($filters);
        
        // Execute main query
        $query = "{$select} {$from} {$where} {$orderby_clause} {$limit}";
        $media_items = $wpdb->get_results($query);
        
        // Get total count for pagination
        $count_query = "SELECT COUNT(*) {$from} {$where}";
        $total_items = $wpdb->get_var($count_query);
        
        // Format media items
        $formatted_items = array();
        foreach ($media_items as $item) {
            $formatted_items[] = $this->format_media_item($item);
        }
        
        return array(
            'items' => $formatted_items,
            'pagination' => array(
                'current_page' => $page,
                'per_page' => $per_page,
                'total_items' => intval($total_items),
                'total_pages' => ceil($total_items / $per_page),
                'has_prev' => $page > 1,
                'has_next' => $page < ceil($total_items / $per_page)
            ),
            'sorting' => array(
                'orderby' => $orderby,
                'order' => $order
            ),
            'search' => $search,
            'filters' => $filters
        );
    }
    
    /**
     * Search media items
     * 
     * @param string $search Search term
     * @param int $page Current page
     * @param int $per_page Items per page
     * @return array Search results
     */
    public function search_media($search, $page = 1, $per_page = 25) {
        return $this->get_media_data($page, $per_page, 'date', 'desc', $search);
    }
    
    /**
     * Filter media items
     * 
     * @param array $filters Filter parameters
     * @param int $page Current page
     * @param int $per_page Items per page
     * @return array Filtered results
     */
    public function filter_media($filters, $page = 1, $per_page = 25) {
        return $this->get_media_data($page, $per_page, 'date', 'desc', '', $filters);
    }
    
    /**
     * Build ORDER BY clause
     * 
     * @param string $orderby Column to sort by
     * @param string $order Sort direction
     * @return string ORDER BY clause
     */
    private function build_orderby_clause($orderby, $order) {
        $valid_orderby = array('title', 'date', 'type', 'size');
        $valid_order = array('asc', 'desc');
        
        if (!in_array($orderby, $valid_orderby)) {
            $orderby = 'date';
        }
        
        if (!in_array($order, $valid_order)) {
            $order = 'desc';
        }
        
        $column_map = array(
            'title' => 'p.post_title',
            'date' => 'p.post_date',
            'type' => 'p.post_mime_type',
            'size' => 'p.ID' // We'll handle size sorting differently
        );
        
        return "ORDER BY {$column_map[$orderby]} {$order}";
    }
    
    /**
     * Build filter conditions for WHERE clause
     * 
     * @param array $filters Filter parameters
     * @return string WHERE conditions
     */
    private function build_filter_conditions($filters) {
        global $wpdb;
        $conditions = '';
        
        // File type filter
        if (!empty($filters['file_type'])) {
            $file_type = sanitize_text_field($filters['file_type']);
            if ($file_type !== 'all') {
                $conditions .= $wpdb->prepare(" AND p.post_mime_type LIKE %s", $file_type . '%');
            }
        }
        
        // Date range filter
        if (!empty($filters['date_from'])) {
            $date_from = sanitize_text_field($filters['date_from']);
            $conditions .= $wpdb->prepare(" AND p.post_date >= %s", $date_from);
        }
        
        if (!empty($filters['date_to'])) {
            $date_to = sanitize_text_field($filters['date_to']);
            $conditions .= $wpdb->prepare(" AND p.post_date <= %s", $date_to . ' 23:59:59');
        }
        
        return $conditions;
    }
    
    /**
     * Format media item for frontend display
     * 
     * @param object $item Raw media item from database
     * @return array Formatted media item
     */
    private function format_media_item($item) {
        $file_path = get_attached_file($item->ID);
        $file_size = $file_path && file_exists($file_path) ? filesize($file_path) : 0;
        
        return array(
            'id' => intval($item->ID),
            'title' => sanitize_text_field($item->post_title),
            'date' => $item->post_date,
            'date_formatted' => date_i18n(get_option('date_format'), strtotime($item->post_date)),
            'mime_type' => sanitize_mime_type($item->post_mime_type),
            'file_extension' => $this->get_file_extension($item->post_mime_type),
            'file_size' => $file_size,
            'file_size_formatted' => size_format($file_size),
            'url' => esc_url($item->guid),
            'thumbnail' => wp_get_attachment_image_url($item->ID, 'thumbnail'),
            'is_document' => $this->is_document_type($item->post_mime_type)
        );
    }
    
    /**
     * Sanitize filter parameters
     * 
     * @param array $filters Raw filter data
     * @return array Sanitized filters
     */
    private function sanitize_filters($filters) {
        if (!is_array($filters)) {
            return array();
        }
        
        $sanitized = array();
        
        if (isset($filters['file_type'])) {
            $sanitized['file_type'] = sanitize_text_field($filters['file_type']);
        }
        
        if (isset($filters['date_from'])) {
            $sanitized['date_from'] = sanitize_text_field($filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $sanitized['date_to'] = sanitize_text_field($filters['date_to']);
        }
        
        return $sanitized;
    }
    
    /**
     * Get file extension from MIME type
     * 
     * @param string $mime_type MIME type
     * @return string File extension
     */
    private function get_file_extension($mime_type) {
        $mime_to_ext = array(
            'image/jpeg' => 'JPG',
            'image/png' => 'PNG',
            'image/gif' => 'GIF',
            'image/webp' => 'WEBP',
            'application/pdf' => 'PDF',
            'application/msword' => 'DOC',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
            'video/mp4' => 'MP4',
            'audio/mpeg' => 'MP3',
            'text/plain' => 'TXT'
        );
        
        return $mime_to_ext[$mime_type] ?? strtoupper(substr($mime_type, strpos($mime_type, '/') + 1));
    }
    
    /**
     * Check if MIME type is a document
     * 
     * @param string $mime_type MIME type
     * @return bool True if document type
     */
    private function is_document_type($mime_type) {
        $document_types = array(
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv'
        );
        
        return in_array($mime_type, $document_types);
    }
    
    /**
     * Render DataTable HTML structure
     * 
     * @return string HTML for DataTable
     */
    public function render_datatable() {
        ob_start();
        ?>
        <div id="media-wipe-datatable-container" class="media-wipe-datatable-wrapper">
            <!-- Search and Filters -->
            <div class="datatable-controls">
                <div class="datatable-search">
                    <input type="text" id="media-search" placeholder="<?php esc_attr_e('Search media files...', 'media-wipe'); ?>" />
                    <span class="search-loading" style="display: none;">
                        <span class="spinner"></span>
                    </span>
                </div>
                
                <div class="datatable-filters">
                    <select id="file-type-filter">
                        <option value="all"><?php esc_html_e('All File Types', 'media-wipe'); ?></option>
                        <option value="image"><?php esc_html_e('Images', 'media-wipe'); ?></option>
                        <option value="video"><?php esc_html_e('Videos', 'media-wipe'); ?></option>
                        <option value="audio"><?php esc_html_e('Audio', 'media-wipe'); ?></option>
                        <option value="application"><?php esc_html_e('Documents', 'media-wipe'); ?></option>
                    </select>
                    
                    <input type="date" id="date-from-filter" placeholder="<?php esc_attr_e('From Date', 'media-wipe'); ?>" />
                    <input type="date" id="date-to-filter" placeholder="<?php esc_attr_e('To Date', 'media-wipe'); ?>" />
                    
                    <button type="button" id="clear-filters" class="button">
                        <?php esc_html_e('Clear Filters', 'media-wipe'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Bulk Actions -->
            <div class="datatable-bulk-actions">
                <div class="bulk-selection">
                    <input type="checkbox" id="select-all-media" />
                    <label for="select-all-media"><?php esc_html_e('Select All', 'media-wipe'); ?></label>
                    
                    <button type="button" id="select-none" class="button-link">
                        <?php esc_html_e('Select None', 'media-wipe'); ?>
                    </button>
                    
                    <button type="button" id="select-filtered" class="button-link">
                        <?php esc_html_e('Select Filtered', 'media-wipe'); ?>
                    </button>
                </div>
                
                <div class="selected-count">
                    <span id="selected-count-text">0 <?php esc_html_e('selected', 'media-wipe'); ?></span>
                </div>
            </div>
            
            <!-- DataTable -->
            <div class="datatable-wrapper">
                <table id="media-datatable" class="widefat striped">
                    <thead>
                        <tr>
                            <th class="check-column">
                                <input type="checkbox" id="table-select-all" />
                            </th>
                            <th class="sortable" data-column="title">
                                <?php esc_html_e('Title', 'media-wipe'); ?>
                                <span class="sort-indicator"></span>
                            </th>
                            <th class="sortable" data-column="type">
                                <?php esc_html_e('Type', 'media-wipe'); ?>
                                <span class="sort-indicator"></span>
                            </th>
                            <th class="sortable" data-column="size">
                                <?php esc_html_e('Size', 'media-wipe'); ?>
                                <span class="sort-indicator"></span>
                            </th>
                            <th class="sortable" data-column="date">
                                <?php esc_html_e('Date', 'media-wipe'); ?>
                                <span class="sort-indicator"></span>
                            </th>
                            <th><?php esc_html_e('Preview', 'media-wipe'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="media-table-body">
                        <!-- Content loaded via AJAX -->
                    </tbody>
                </table>
                
                <!-- Loading State -->
                <div id="datatable-loading" class="datatable-loading" style="display: none;">
                    <div class="loading-skeleton">
                        <?php for ($i = 0; $i < 10; $i++): ?>
                            <div class="skeleton-row">
                                <div class="skeleton-cell skeleton-checkbox"></div>
                                <div class="skeleton-cell skeleton-title"></div>
                                <div class="skeleton-cell skeleton-type"></div>
                                <div class="skeleton-cell skeleton-size"></div>
                                <div class="skeleton-cell skeleton-date"></div>
                                <div class="skeleton-cell skeleton-preview"></div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="datatable-pagination">
                <div class="pagination-info">
                    <span id="pagination-info-text"></span>
                </div>
                
                <div class="pagination-controls">
                    <button type="button" id="first-page" class="button" disabled>
                        <?php esc_html_e('First', 'media-wipe'); ?>
                    </button>
                    <button type="button" id="prev-page" class="button" disabled>
                        <?php esc_html_e('Previous', 'media-wipe'); ?>
                    </button>
                    
                    <div class="page-numbers" id="page-numbers">
                        <!-- Page numbers loaded dynamically -->
                    </div>
                    
                    <button type="button" id="next-page" class="button" disabled>
                        <?php esc_html_e('Next', 'media-wipe'); ?>
                    </button>
                    <button type="button" id="last-page" class="button" disabled>
                        <?php esc_html_e('Last', 'media-wipe'); ?>
                    </button>
                </div>
                
                <div class="per-page-selector">
                    <label for="per-page-select"><?php esc_html_e('Items per page:', 'media-wipe'); ?></label>
                    <select id="per-page-select">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the DataTable component
new Media_Wipe_DataTable();
