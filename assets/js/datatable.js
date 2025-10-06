/**
 * Media Wipe DataTable JavaScript using DataTables.net
 *
 * Professional DataTable implementation for Delete Selected Media page
 *
 * @package MediaWipe
 * @since 1.1.1
 */

(function ($) {
    'use strict';

    /**
     * MediaWipe DataTable Class
     */
    class MediaWipeDataTable {
        constructor() {
            this.table = null;
            this.selectedItems = new Set();

            this.init();
        }

        /**
         * Initialize the DataTable
         */
        init() {
            this.addSpinnerCSS();
            this.initializeDataTable();
            this.bindEvents();
        }

        /**
         * Initialize DataTables.net
         */
        initializeDataTable() {
            if (!$.fn.DataTable) {
                console.error('DataTables library not loaded');
                return;
            }

            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('#media-datatable')) {
                $('#media-datatable').DataTable().destroy();
            }

            this.table = $('#media-datatable').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[5, 'desc']], // Sort by date column
                columnDefs: [
                    {
                        targets: [0, 6], // Select and Actions columns
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: 1, // Preview column
                        orderable: false,
                        width: '80px'
                    },
                    {
                        targets: 4, // Size column
                        type: 'num'
                    }
                ],
                language: {
                    search: 'Search media files:',
                    lengthMenu: 'Show _MENU_ files per page',
                    info: 'Showing _START_ to _END_ of _TOTAL_ files',
                    infoEmpty: 'No files found',
                    infoFiltered: '(filtered from _MAX_ total files)',
                    paginate: {
                        first: 'First',
                        last: 'Last',
                        next: 'Next',
                        previous: 'Previous'
                    }
                },
                dom: '<"datatable-top"<"datatable-length"l><"datatable-filter"f>>rt<"datatable-bottom"<"datatable-info"i><"datatable-pagination"p>>',
                drawCallback: () => {
                    this.updateSelectionState();
                },
                initComplete: () => {
                    console.log('DataTable initialized successfully');
                    this.updateDeleteButton();
                }
            });
        }

        /**
         * Bind event handlers
         */
        bindEvents() {
            console.log('Media Wipe DataTable: Binding events...');

            // Bulk selection controls
            $('#select-all-btn').on('click', () => {
                console.log('Select All clicked');
                this.selectAll();
            });

            $('#select-none-btn').on('click', () => {
                console.log('Select None clicked');
                this.selectNone();
            });

            // Delete selected button
            $('#delete-selected-btn').on('click', () => {
                console.log('Delete Selected clicked');
                this.deleteSelected();
            });

            // Individual checkbox selection
            $(document).on('change', '.media-checkbox', (e) => {
                console.log('Checkbox changed:', e.target.value);
                this.handleCheckboxChange(e.target);
            });

            // Single delete buttons
            $(document).on('click', '.delete-single', (e) => {
                e.stopPropagation(); // Prevent row click when clicking delete button
                const mediaId = $(e.target).data('media-id');
                console.log('Delete single clicked:', mediaId);
                this.deleteSingle(mediaId);
            });

            // Row click functionality for easier selection
            $(document).on('click', '#media-datatable tbody tr', (e) => {
                // Don't trigger row selection if clicking on buttons, links, or checkboxes
                if ($(e.target).is('button, a, input[type="checkbox"], .button, .delete-single') ||
                    $(e.target).closest('button, a, .button, .delete-single').length > 0) {
                    return;
                }

                const $row = $(e.currentTarget);
                const $checkbox = $row.find('.media-checkbox');

                if ($checkbox.length > 0) {
                    // Toggle the checkbox
                    $checkbox.prop('checked', !$checkbox.prop('checked'));
                    // Trigger the change event to update selection state
                    $checkbox.trigger('change');
                    console.log('Row clicked - toggled checkbox for media ID:', $checkbox.val());
                }
            });

            // Prevent checkbox clicks from bubbling to row click
            $(document).on('click', '.media-checkbox', (e) => {
                e.stopPropagation();
            });

            // Keyboard accessibility - Space key to toggle selection
            $(document).on('keydown', '#media-datatable tbody tr', (e) => {
                if (e.key === ' ' || e.keyCode === 32) {
                    e.preventDefault();
                    const $row = $(e.currentTarget);
                    const $checkbox = $row.find('.media-checkbox');

                    if ($checkbox.length > 0) {
                        $checkbox.prop('checked', !$checkbox.prop('checked'));
                        $checkbox.trigger('change');
                        console.log('Space key pressed - toggled checkbox for media ID:', $checkbox.val());
                    }
                }
            });

            // Make rows focusable for keyboard navigation
            $(document).on('focus', '#media-datatable tbody tr', function () {
                $(this).attr('tabindex', '0');
            });

            console.log('Media Wipe DataTable: Events bound successfully');
        }

        /**
         * Select all visible items
         */
        selectAll() {
            $('.media-checkbox:visible').each((_, checkbox) => {
                const id = parseInt(checkbox.value);
                this.selectedItems.add(id);
                checkbox.checked = true;
                $(checkbox).closest('tr').addClass('selected');
            });
            this.updateDeleteButton();
        }

        /**
         * Select none
         */
        selectNone() {
            $('.media-checkbox').each((_, checkbox) => {
                const id = parseInt(checkbox.value);
                this.selectedItems.delete(id);
                checkbox.checked = false;
                $(checkbox).closest('tr').removeClass('selected');
            });
            this.updateDeleteButton();
        }

        /**
         * Handle individual checkbox change
         */
        handleCheckboxChange(checkbox) {
            const id = parseInt(checkbox.value);
            const $row = $(checkbox).closest('tr');

            if (checkbox.checked) {
                this.selectedItems.add(id);
                $row.addClass('selected');
            } else {
                this.selectedItems.delete(id);
                $row.removeClass('selected');
            }

            this.updateDeleteButton();
        }

        /**
         * Update delete button state
         */
        updateDeleteButton() {
            const count = this.selectedItems.size;
            const $deleteBtn = $('#delete-selected-btn');

            if (count > 0) {
                $deleteBtn.prop('disabled', false);
                $deleteBtn.html(`<span class="dashicons dashicons-trash"></span> Delete Selected (${count})`);
            } else {
                $deleteBtn.prop('disabled', true);
                $deleteBtn.html('<span class="dashicons dashicons-trash"></span> Delete Selected');
            }
        }

        /**
         * Update selection state after DataTable redraw
         */
        updateSelectionState() {
            $('.media-checkbox').each((_, checkbox) => {
                const id = parseInt(checkbox.value);
                if (this.selectedItems.has(id)) {
                    checkbox.checked = true;
                    $(checkbox).closest('tr').addClass('selected');
                } else {
                    checkbox.checked = false;
                    $(checkbox).closest('tr').removeClass('selected');
                }
            });
            this.updateDeleteButton();
        }

        /**
         * Delete selected media
         */
        deleteSelected() {
            if (this.selectedItems.size === 0) {
                alert('Please select media files to delete.');
                return;
            }

            const selectedIds = Array.from(this.selectedItems);
            const confirmMessage = `Are you sure you want to delete ${selectedIds.length} selected media files? This action cannot be undone.`;

            if (confirm(confirmMessage)) {
                this.performDeletion(selectedIds);
            }
        }

        /**
         * Delete single media item
         */
        deleteSingle(mediaId) {
            const confirmMessage = 'Are you sure you want to delete this media file? This action cannot be undone.';

            if (confirm(confirmMessage)) {
                this.performDeletion([mediaId]);
            }
        }

        /**
         * Perform actual deletion
         */
        performDeletion(mediaIds) {
            // Show loading state
            const $deleteBtn = $('#delete-selected-btn');
            $deleteBtn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Deleting...');

            // Add spinner CSS
            this.addSpinnerCSS();

            // Set the selected media IDs in the hidden form
            $('#selected-media-ids').val(mediaIds.join(','));

            // Submit the form
            $('#delete-selected-form').submit();
        }

        /**
         * Add CSS for spinning icon
         */
        addSpinnerCSS() {
            if (!$('#media-wipe-spinner-css').length) {
                $('<style id="media-wipe-spinner-css">.spin { animation: spin 1s linear infinite; } @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>').appendTo('head');
            }
        }

        /**
         * Get selected media IDs
         */
        getSelectedIds() {
            return Array.from(this.selectedItems);
        }
    }

    // Initialize DataTable when document is ready
    $(document).ready(function () {
        console.log('Media Wipe DataTable: Document ready');

        if ($('#media-datatable').length) {
            console.log('Media Wipe DataTable: Table found, initializing...');
            window.mediaWipeDataTable = new MediaWipeDataTable();
        } else {
            console.log('Media Wipe DataTable: Table not found');
        }
    });

})(jQuery);
