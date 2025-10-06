jQuery(document).ready(function ($) {
    // Debug flag - set to false for production
    var MEDIA_WIPE_DEBUG = false;

    // Debug logging function
    function debugLog(message, data) {
        if (MEDIA_WIPE_DEBUG) {
            if (data) {
                console.log('Media Wipe Debug:', message, data);
            } else {
                console.log('Media Wipe Debug:', message);
            }
        }
    }

    var mediaIds = [];

    // ================================
    // Collapsible Safety Sections
    // ================================

    // Initialize collapsible sections
    function initCollapsibleSections() {
        // Load saved states from localStorage
        $('.mw-page-safety-section.collapsible').each(function () {
            var $section = $(this);
            var sectionId = $section.data('section');
            var isCollapsed = localStorage.getItem('mw-safety-' + sectionId) === 'collapsed';

            if (isCollapsed) {
                $section.addClass('collapsed');
            }
        });

        // Handle click events
        $('.mw-page-safety-section.collapsible .mw-page-safety-header').on('click', function () {
            var $section = $(this).closest('.mw-page-safety-section');
            var sectionId = $section.data('section');

            $section.toggleClass('collapsed');

            // Save state to localStorage
            var isCollapsed = $section.hasClass('collapsed');
            localStorage.setItem('mw-safety-' + sectionId, isCollapsed ? 'collapsed' : 'expanded');
        });
    }

    // Initialize on page load
    initCollapsibleSections();

    // ================================
    // Selected Media Deletion (Existing)
    // ================================

    // When the delete button is clicked
    $('#open-delete-modal').on('click', function (e) {
        // Prevent default form submission
        e.preventDefault();

        // Get the selected media IDs and their information
        mediaIds = [];
        var selectedFiles = [];

        $('input[name="delete_media[]"]:checked').each(function () {
            var mediaId = $(this).val();
            var $row = $(this).closest('tr');
            var mediaType = $row.data('media-type');
            var mediaTitle = $row.find('.media-title').text();
            var mediaDetails = $row.find('.media-details').text();
            var $previewCell = $row.find('.media-preview-cell');

            mediaIds.push(mediaId);
            selectedFiles.push({
                id: mediaId,
                title: mediaTitle,
                details: mediaDetails,
                type: mediaType,
                isDocument: isDocumentType(mediaType),
                previewHtml: $previewCell.html()
            });
        });

        // If no media is selected, return
        if (mediaIds.length === 0) {
            alert('Please select media to delete.');
            return;
        }

        // Populate the selected files list in modal
        populateSelectedFilesList(selectedFiles);

        // Show the confirmation modal
        $('#delete-confirmation-modal').fadeIn(300);
    });

    // If the user clicks 'Yes', submit the form to delete media via AJAX
    $('#delete-confirmation-yes').on('click', function () {
        // Make sure there are media IDs to delete
        if (mediaIds.length === 0) {
            showEnhancedNotification('error', {
                title: 'No Selection',
                message: 'No media files selected for deletion.',
                icon: '⚠️'
            });
            return;
        }

        var $button = $(this);
        var $modal = $('#delete-confirmation-modal');

        // Show enhanced loading state
        showDeletionProgress($button, $modal, 'selected');

        // Make an AJAX request to delete the selected media
        $.ajax({
            url: mediaWipeAjax.ajaxurl,
            type: 'POST',
            timeout: 120000, // 2 minutes timeout for selected files
            data: {
                action: 'media_wipe_delete_unused_media',
                media_ids: mediaIds,
                nonce: mediaWipeAjax.nonce
            },
            success: function (response) {
                hideDeletionProgress($modal);

                if (response.success) {
                    // Show enhanced success message
                    showEnhancedNotification('success', {
                        title: 'Deletion Completed',
                        message: response.data.message,
                        icon: '✅'
                    });

                    // Update UI to reflect changes
                    updateStatsAfterDeletion('selected', mediaIds.length);

                    // Close modal and reload page after delay
                    setTimeout(function () {
                        $('#delete-confirmation-modal').fadeOut(300);
                        location.reload();
                    }, 2000);
                } else {
                    // Show enhanced error message
                    showEnhancedNotification('error', {
                        title: 'Deletion Failed',
                        message: response.data.message || 'An error occurred during deletion.',
                        icon: '❌'
                    });
                    resetSelectedDeleteButton($button);
                }
            },
            error: function (xhr, status, error) {
                hideDeletionProgress($modal);

                var errorMessage = 'Network error occurred.';
                if (status === 'timeout') {
                    errorMessage = 'The operation timed out. Some files may have been deleted.';
                } else if (xhr.responseJSON && xhr.responseJSON.data) {
                    errorMessage = xhr.responseJSON.data.message;
                }

                showEnhancedNotification('error', {
                    title: 'Connection Error',
                    message: errorMessage,
                    icon: '⚠️'
                });
                resetSelectedDeleteButton($button);
            }
        });
    });

    // If the user clicks 'No', hide the modal
    $('#delete-confirmation-no, #close-selected-modal').on('click', function () {
        $('#delete-confirmation-modal').fadeOut(300);
    });

    // Close selected media modal when clicking overlay
    $('#delete-confirmation-modal .modal-overlay').on('click', function () {
        $('#delete-confirmation-modal').fadeOut(300);
    });

    // Prevent modal close when clicking inside modal content
    $('#delete-confirmation-modal .modal-content-medium').on('click', function (e) {
        e.stopPropagation();
    });

    // ================================
    // Enhanced Delete All Media Functionality
    // ================================

    // Open delete all confirmation modal
    $('#open-delete-all-modal').on('click', function (e) {
        e.preventDefault();
        $('#delete-all-confirmation-modal').fadeIn(300);
        resetDeleteAllModal();
    });

    // Close modal handlers
    $('#close-delete-all-modal, #cancel-delete-all, .mw-modal-overlay').on('click', function (e) {
        if (e.target === this) {
            $('#delete-all-confirmation-modal').fadeOut(300);
            resetDeleteAllModal();
        }
    });

    // Prevent modal close when clicking inside modal content
    $('.mw-modal-content').on('click', function (e) {
        e.stopPropagation();
    });

    // Handle checkbox changes - simplified validation
    function validateDeleteAllForm() {
        var isValid = $('#final-confirm').is(':checked');
        $('#confirm-delete-all').prop('disabled', !isValid);
        return isValid;
    }

    // Bind validation to checkbox
    $(document).on('change', '#final-confirm', validateDeleteAllForm);

    // Initial validation on page load
    if ($('#delete-all-confirmation-modal').length) {
        validateDeleteAllForm();
    }

    // Handle delete all confirmation
    $('#confirm-delete-all').on('click', function () {
        if (!validateDeleteAllForm()) {
            return;
        }

        var $button = $(this);
        var $modal = $('#delete-all-confirmation-modal');

        // Show enhanced loading state
        showDeletionProgress($button, $modal, 'all');

        // Get nonce from the form
        var nonce = $('input[name="media_wipe_all_nonce"]').val();
        var confirmationText = $('#confirmation-text').length ? $('#confirmation-text').val().trim() : '';

        // Make AJAX request with timeout
        $.ajax({
            url: mediaWipeAjax.ajaxurl,
            type: 'POST',
            timeout: 300000, // 5 minutes timeout
            data: {
                action: 'media_wipe_delete_all_media',
                nonce: nonce,
                confirmation: confirmationText
            },
            success: function (response) {
                hideDeletionProgress($modal);

                if (response.success) {
                    // Show enhanced success message
                    showEnhancedNotification('success', {
                        title: 'Deletion Completed',
                        message: response.data.message,
                        icon: '✅'
                    });

                    // Update UI to reflect changes
                    updateStatsAfterDeletion('all');

                    // Close modal and reload page after delay
                    setTimeout(function () {
                        $('#delete-all-confirmation-modal').fadeOut(300);
                        location.reload();
                    }, 3000);
                } else {
                    // Show enhanced error message
                    showEnhancedNotification('error', {
                        title: 'Deletion Failed',
                        message: response.data.message || 'An error occurred during deletion.',
                        icon: '❌'
                    });
                    resetDeleteAllButton($button);
                }
            },
            error: function (xhr, status, error) {
                hideDeletionProgress($modal);

                var errorMessage = 'Network error occurred.';
                if (status === 'timeout') {
                    errorMessage = 'The operation timed out. Some files may have been deleted.';
                } else if (xhr.responseJSON && xhr.responseJSON.data) {
                    errorMessage = xhr.responseJSON.data.message;
                }

                showEnhancedNotification('error', {
                    title: 'Connection Error',
                    message: errorMessage,
                    icon: '⚠️'
                });
                resetDeleteAllButton($button);
            }
        });
    });

    // Reset modal to initial state
    function resetDeleteAllModal() {
        // Reset checkbox
        $('#final-confirm').prop('checked', false);

        // Reset button state
        $('#confirm-delete-all').text('Delete All Files');

        // Remove any notifications
        $('.notification').remove();

        // Validate form to set correct button state
        validateDeleteAllForm();
    }

    // Enhanced notification system
    function showNotification(type, message) {
        // Remove existing notifications
        $('.notification').remove();

        var notificationClass = type === 'success' ? 'notice-success' : 'notice-error';
        var notification = $('<div class="notice ' + notificationClass + ' is-dismissible notification"><p>' + message + '</p></div>');

        // Insert notification at the top of modal body
        $('.modal-body').prepend(notification);

        // Auto-remove error notifications after 5 seconds
        if (type === 'error') {
            setTimeout(function () {
                notification.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 5000);
        }
    }

    // Enhanced notification with better styling and animations
    function showEnhancedNotification(type, options) {
        // Remove existing notifications
        $('.enhanced-notification').remove();

        var icon = options.icon || (type === 'success' ? '✅' : '❌');
        var title = options.title || (type === 'success' ? 'Success' : 'Error');
        var message = options.message || '';

        var notification = $(`
            <div class="enhanced-notification ${type}">
                <div class="notification-content">
                    <div class="notification-icon">${icon}</div>
                    <div class="notification-text">
                        <h4>${title}</h4>
                        <p>${message}</p>
                    </div>
                    <button class="notification-close">&times;</button>
                </div>
                <div class="notification-progress"></div>
            </div>
        `);

        // Insert notification
        if ($('.modal-body').length) {
            $('.modal-body').prepend(notification);
        } else {
            $('body').append(notification);
        }

        // Animate in
        notification.addClass('show');

        // Handle close button
        notification.find('.notification-close').on('click', function () {
            notification.removeClass('show');
            setTimeout(() => notification.remove(), 300);
        });

        // Auto-remove after delay
        var delay = type === 'success' ? 3000 : 8000;
        setTimeout(function () {
            if (notification.length) {
                notification.removeClass('show');
                setTimeout(() => notification.remove(), 300);
            }
        }, delay);
    }

    // Show deletion progress with enhanced UI
    function showDeletionProgress($button, $modal, type) {
        // Update button state
        $button.prop('disabled', true).html(`
            <span class="spinner"></span>
            <span class="button-text">Deleting files...</span>
        `);

        // Add loading overlay to modal
        $modal.addClass('modal-loading');

        // Add progress indicator
        var progressHtml = `
            <div class="deletion-progress">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-text">
                    <span class="progress-status">Preparing deletion...</span>
                    <span class="progress-details">This may take a few moments</span>
                </div>
            </div>
        `;

        if (!$('.deletion-progress').length) {
            $('.modal-body').append(progressHtml);
        }

        // Animate progress bar
        setTimeout(() => {
            $('.progress-fill').css('width', '30%');
            $('.progress-status').text('Deleting files...');
        }, 500);
    }

    // Hide deletion progress
    function hideDeletionProgress($modal) {
        $modal.removeClass('modal-loading');
        $('.deletion-progress').fadeOut(300, function () {
            $(this).remove();
        });
    }

    // Reset delete all button
    function resetDeleteAllButton($button) {
        $button.prop('disabled', false).text('Delete All Media Files');
    }

    // Reset selected delete button
    function resetSelectedDeleteButton($button) {
        $button.prop('disabled', false).text('Delete Selected Files');
    }

    // Helper function to normalize text for comparison
    function normalizeText(text) {
        return text.trim().toUpperCase().replace(/\s+/g, ' ');
    }

    // Update statistics after deletion
    function updateStatsAfterDeletion(type, count) {
        if (type === 'all') {
            // Update all stats to 0
            $('.stat-number').text('0');
        } else if (type === 'selected' && count) {
            // Update total count by subtracting deleted files
            var $totalStat = $('.stat-number').first();
            var currentTotal = parseInt($totalStat.text()) || 0;
            var newTotal = Math.max(0, currentTotal - count);
            $totalStat.text(newTotal);
        }
        // This could be enhanced with real-time updates for specific file types
    }

    // Handle escape key to close modal
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $('#delete-all-confirmation-modal').is(':visible')) {
            $('#delete-all-confirmation-modal').fadeOut(300);
            resetDeleteAllModal();
        }
    });

    // Enhance confirmation text input
    $('#confirmation-text').on('input', function () {
        var $input = $(this);
        var value = normalizeText($input.val());
        var target = 'DELETE ALL MEDIA';

        // Debug logging (remove in production)
        // console.log('Input value:', '"' + $input.val() + '"');
        // console.log('Normalized value:', '"' + value + '"');
        // console.log('Target:', '"' + target + '"');
        // console.log('Match:', value === target);

        // Visual feedback for typing progress
        if (value === target) {
            $input.css('border-color', '#28a745');
        } else if (target.startsWith(value) && value.length > 0) {
            $input.css('border-color', '#ffc107');
        } else if (value.length > 0) {
            $input.css('border-color', '#dc3545');
        } else {
            $input.css('border-color', '#ffeaa7');
        }

        // Trigger validation check
        validateDeleteAllForm();
    });

    // ================================
    // Helper Functions
    // ================================

    // Check if MIME type is a document type
    function isDocumentType(mimeType) {
        var documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
            'application/rtf'
        ];

        return documentTypes.indexOf(mimeType) !== -1;
    }

    // Populate selected files list in confirmation modal
    function populateSelectedFilesList(selectedFiles) {
        var $container = $('#selected-files-list');
        $container.empty();

        if (selectedFiles.length === 0) {
            $container.html('<p class="no-files-selected">No files selected.</p>');
            return;
        }

        var html = '<div class="selected-files-grid">';

        selectedFiles.forEach(function (file) {
            html += '<div class="selected-file-item" data-file-id="' + file.id + '">';

            if (file.isDocument) {
                html += '<div class="file-preview document-preview">' + file.previewHtml + '</div>';
            } else {
                html += '<div class="file-preview image-preview">' + file.previewHtml + '</div>';
            }

            html += '<div class="file-info">';
            html += '<span class="file-title">' + file.title + '</span>';
            html += '<span class="file-details">' + file.details + '</span>';
            html += '</div>';
            html += '</div>';
        });

        html += '</div>';

        // Add summary
        html += '<div class="selection-summary">';
        html += '<p><strong>' + selectedFiles.length + ' file(s) selected for deletion</strong></p>';

        var documentCount = selectedFiles.filter(function (file) {
            return file.isDocument;
        }).length;

        if (documentCount > 0) {
            html += '<p class="document-warning">⚠️ ' + documentCount + ' document file(s) will be permanently deleted</p>';
        }

        html += '</div>';

        $container.html(html);
    }

    // ================================
    // Settings Page Enhancement
    // ================================

    // Settings form validation and enhancement
    if ($('.media-wipe-settings-form').length) {
        // Add visual feedback for checkbox changes
        $('.form-table input[type="checkbox"]').on('change', function () {
            const $label = $(this).closest('label');
            const $row = $(this).closest('tr');

            if ($(this).is(':checked')) {
                $label.addClass('setting-enabled');
                $row.addClass('setting-active');
            } else {
                $label.removeClass('setting-enabled');
                $row.removeClass('setting-active');
            }
        });

        // Initialize checkbox states
        $('.form-table input[type="checkbox"]:checked').each(function () {
            $(this).closest('label').addClass('setting-enabled');
            $(this).closest('tr').addClass('setting-active');
        });

        // Form submission enhancement
        $('.media-wipe-settings-form').on('submit', function () {
            const $submitBtn = $(this).find('.button-primary');
            const originalText = $submitBtn.text();

            $submitBtn.prop('disabled', true)
                .text('Saving Settings...')
                .addClass('updating-message');

            // Re-enable after a delay (in case of errors)
            setTimeout(function () {
                $submitBtn.prop('disabled', false)
                    .text(originalText)
                    .removeClass('updating-message');
            }, 3000);
        });
    }

    // ================================
    // Remove Unused Media Functionality
    // ================================

    // Start unused media scan
    $('#start-unused-scan').on('click', function (e) {
        e.preventDefault();

        var $button = $(this);
        var $form = $('#unused-media-scan-form');

        // Get form data
        var formData = {
            action: 'media_wipe_start_unused_scan',
            nonce: $form.find('input[name="media_wipe_unused_scan_nonce"]').val(),
            exclude_recent: $form.find('input[name="exclude_recent"]:checked').length > 0 ? 1 : 0,
            exclude_featured: $form.find('input[name="exclude_featured"]:checked').length > 0 ? 1 : 0,
            scan_depth: $form.find('input[name="scan_depth"]:checked').val()
        };

        // Show progress container
        $('#scan-progress-container').show();
        $('#scan-results-container').hide();

        // Disable scan button
        $button.prop('disabled', true).text('Scanning...');
        $('#cancel-unused-scan').show();

        // Start scan
        $.ajax({
            url: mediaWipeAjax.ajaxurl,
            type: 'POST',
            data: formData,
            timeout: 300000, // 5 minutes
            success: function (response) {
                console.log('Scan response received:', response);
                if (response.success) {
                    displayUnusedMediaResults(response.data.results);
                } else {
                    showNotification('error', response.data.message || 'Scan failed');
                }
            },
            error: function (xhr, status, error) {
                showNotification('error', 'Scan failed: ' + error);
            },
            complete: function () {
                // Re-enable scan button
                $button.prop('disabled', false).text('Start Scan');
                $('#cancel-unused-scan').hide();
                $('#scan-progress-container').hide();
            }
        });
    });

    // Display unused media results
    function displayUnusedMediaResults(results) {
        console.log('Display results called with:', results);
        debugLog('Display results called with:', results);

        // Handle different "no results" scenarios
        var noResults = false;
        var message = '';
        var description = '';

        if (!results) {
            console.log('No results object received');
            noResults = true;
            message = 'No Media Files Found';
            description = 'Your media library appears to be empty. Upload some media files to scan for unused items.';
        } else if (!results.files || results.files.length === 0) {
            console.log('Results object exists but no files found');

            // Check if there were any files scanned at all
            if (results.total_scanned === 0) {
                noResults = true;
                message = 'No Media Files Found';
                description = 'Your media library appears to be empty. Upload some media files to scan for unused items.';
            } else {
                noResults = true;
                message = 'Great News! No Unused Files Found';
                description = 'Your media library is clean and optimized. All files appear to be in use on your website.';
            }
        }

        if (noResults) {
            console.log('Showing no results message:', message);

            // Show the results container with a "nothing found" message
            $('#scan-results-container').show();
            $('#results-summary-text').text(results && results.total_scanned ?
                `No unused files found out of ${results.total_scanned} scanned` :
                'No media files found to scan');

            // Hide the table and controls
            $('.results-table-container').hide();
            $('.results-controls').hide();

            // Remove existing no-results message if it exists
            $('#no-results-message').remove();

            // Add the "nothing found" message
            $('.results-card').append(`
                <div id="no-results-message" class="no-results-found">
                    <div class="no-results-icon">
                        <span class="dashicons dashicons-${results && results.total_scanned > 0 ? 'yes-alt' : 'info'}"></span>
                    </div>
                    <h3>${message}</h3>
                    <p>${description}</p>
                    <div class="no-results-actions">
                        <button type="button" id="scan-again-btn" class="button button-primary">
                            <span class="dashicons dashicons-update"></span>
                            Scan Again
                        </button>
                    </div>
                </div>
            `);

            showNotification(results && results.total_scanned > 0 ? 'success' : 'info',
                results && results.total_scanned > 0 ? 'Scan completed - no unused files found!' : 'No media files found to scan');
            return;
        }

        // Hide the "no results" message if it exists and show table/controls
        $('#no-results-message').remove();
        $('.results-table-container').show();
        $('.results-controls').show();

        // Debug first few files to check data structure
        debugLog('First file data:', results.files[0]);
        if (results.files.length > 1) {
            debugLog('Second file data:', results.files[1]);
        }

        // Update results summary
        $('#results-summary-text').text(
            results.unused_found + ' unused files found out of ' + results.total_scanned + ' scanned'
        );

        // Show results container
        $('#scan-results-container').show();

        // Check if DataTables is available
        if (typeof $.fn.DataTable === 'undefined') {
            debugLog('DataTables library is not loaded');
            showNotification('warning', 'DataTables library is not loaded. Displaying basic table.');
            displayBasicUnusedTable(results.files);
            return;
        }

        // Initialize or update DataTable
        if ($.fn.DataTable.isDataTable('#unused-media-datatable')) {
            $('#unused-media-datatable').DataTable().destroy();
        }

        // Prepare data for DataTable
        var tableData = results.files.map(function (file, index) {
            // Debug file ID for first few files
            if (index < 3) {
                debugLog('File ' + index + ' ID:', file.id, 'Type:', typeof file.id);
            }

            var confidenceClass = file.confidence_score >= 90 ? 'high' :
                file.confidence_score >= 75 ? 'medium' : 'low';
            var confidenceBadge = '<span class="confidence-badge ' + confidenceClass + '">' +
                file.confidence_score + '%</span>';

            var thumbnail = file.thumbnail ?
                '<img src="' + file.thumbnail + '" alt="' + file.filename + '" style="max-width: 50px; max-height: 50px;">' :
                '<span class="dashicons dashicons-media-default"></span>';

            var checkboxHtml = '<input type="checkbox" class="unused-file-checkbox" data-id="' + file.id + '" data-confidence="' + file.confidence_score + '">';

            // Debug checkbox HTML for first file
            if (index === 0) {
                debugLog('First checkbox HTML:', checkboxHtml);
            }

            return [
                checkboxHtml,
                thumbnail,
                '<strong>' + file.filename + '</strong><br><small>' + file.title + '</small>',
                file.file_type,
                file.file_size_formatted,
                file.upload_date,
                confidenceBadge,
                '<button class="button button-small view-usage-btn" data-id="' + file.id + '">View Details</button>'
            ];
        });

        // Initialize DataTable - match selected media table configuration
        $('#unused-media-datatable').DataTable({
            data: tableData,
            responsive: true,
            pageLength: 25,
            order: [[6, 'desc']], // Sort by confidence score (column 6)
            columnDefs: [
                { orderable: false, targets: [0, 1, 7] } // Disable sorting for Select, Preview, and Actions columns
            ]
        });

        // Update selection controls
        updateUnusedSelectionControls();
    }

    // Fallback function to display basic table without DataTables
    function displayBasicUnusedTable(files) {
        var $tbody = $('#unused-media-datatable tbody');
        $tbody.empty();

        files.forEach(function (file) {
            var confidenceClass = file.confidence_score >= 90 ? 'high' :
                file.confidence_score >= 75 ? 'medium' : 'low';
            var confidenceBadge = '<span class="confidence-badge ' + confidenceClass + '">' +
                file.confidence_score + '%</span>';

            var thumbnail = file.thumbnail ?
                '<img src="' + file.thumbnail + '" alt="' + file.filename + '" style="max-width: 50px; max-height: 50px;">' :
                '<span class="dashicons dashicons-media-default"></span>';

            var row = '<tr>' +
                '<td><input type="checkbox" class="unused-file-checkbox" data-id="' + file.id + '" data-confidence="' + file.confidence_score + '"></td>' +
                '<td>' + thumbnail + '</td>' +
                '<td><strong>' + file.filename + '</strong><br><small>' + file.title + '</small></td>' +
                '<td>' + file.file_type + '</td>' +
                '<td>' + file.file_size_formatted + '</td>' +
                '<td>' + file.upload_date + '</td>' +
                '<td>' + confidenceBadge + '</td>' +
                '<td><button class="button button-small view-usage-btn" data-id="' + file.id + '">View Details</button></td>' +
                '</tr>';

            $tbody.append(row);
        });

        // Update selection controls
        updateUnusedSelectionControls();
    }

    // Scan Again button functionality
    $(document).on('click', '#scan-again-btn', function () {
        // Hide the no results message
        $('#no-results-message').hide();
        $('#scan-results-container').hide();

        // Trigger the scan again
        $('#start-unused-scan').trigger('click');
    });

    // Selection controls for unused media
    $('#select-all-unused').on('click', function () {
        // Handle both DataTable and regular table
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#unused-media-datatable')) {
            var table = $('#unused-media-datatable').DataTable();
            table.$('.unused-file-checkbox').prop('checked', true);
        } else {
            $('.unused-file-checkbox').prop('checked', true);
        }
        updateUnusedSelectionControls();
    });

    $('#select-none-unused').on('click', function () {
        // Handle both DataTable and regular table
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#unused-media-datatable')) {
            var table = $('#unused-media-datatable').DataTable();
            table.$('.unused-file-checkbox').prop('checked', false);
        } else {
            $('.unused-file-checkbox').prop('checked', false);
        }
        updateUnusedSelectionControls();
    });

    $('#select-high-confidence').on('click', function () {
        // Handle both DataTable and regular table
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#unused-media-datatable')) {
            var table = $('#unused-media-datatable').DataTable();
            table.$('.unused-file-checkbox').each(function () {
                var confidence = parseInt($(this).data('confidence'));
                $(this).prop('checked', confidence >= 90);
            });
        } else {
            $('.unused-file-checkbox').each(function () {
                var confidence = parseInt($(this).data('confidence'));
                $(this).prop('checked', confidence >= 90);
            });
        }
        updateUnusedSelectionControls();
    });

    // Update selection controls
    $(document).on('change', '.unused-file-checkbox', updateUnusedSelectionControls);

    function updateUnusedSelectionControls() {
        var selectedCount = 0;

        // Count selected checkboxes (handle both DataTable and regular table)
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#unused-media-datatable')) {
            var table = $('#unused-media-datatable').DataTable();
            selectedCount = table.$('.unused-file-checkbox:checked').length;
        } else {
            selectedCount = $('.unused-file-checkbox:checked').length;
        }

        var $deleteButton = $('#delete-selected-unused');
        $deleteButton.prop('disabled', selectedCount === 0);

        // Update button text properly
        var buttonText = 'Delete Selected (' + selectedCount + ')';
        if ($deleteButton.find('.dashicons').length > 0) {
            // If button has icon, preserve it
            $deleteButton.html('<span class="dashicons dashicons-trash"></span>' + buttonText);
        } else {
            $deleteButton.text(buttonText);
        }
    }

    // Add test button for debugging (temporary)
    if (MEDIA_WIPE_DEBUG) {
        $('body').append('<button id="debug-test-delete" style="position: fixed; top: 50px; right: 50px; z-index: 9999; background: red; color: white; padding: 10px;">DEBUG: Test Delete</button>');

        $(document).on('click', '#debug-test-delete', function () {
            debugLog('Debug test button clicked');
            debugLog('Checkboxes found:', $('.unused-file-checkbox').length);
            debugLog('Checked checkboxes:', $('.unused-file-checkbox:checked').length);

            $('.unused-file-checkbox:checked').each(function (index) {
                debugLog('Checkbox ' + index + ' ID:', $(this).data('id'));
            });

            // Test AJAX call
            debugLog('Testing AJAX call...');
            $.ajax({
                url: mediaWipeAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'media_wipe_delete_unused_files',
                    nonce: $('#media_wipe_delete_unused_nonce').val(),
                    selected_ids: '123,456' // Test IDs
                },
                success: function (response) {
                    debugLog('Test AJAX success:', response);
                },
                error: function (xhr, status, error) {
                    debugLog('Test AJAX error:', error);
                }
            });
        });
    }

    // Delete selected unused media (using event delegation)
    debugLog('Binding delete button event handler with delegation');

    // Debug nonce field availability on page load
    debugLog('Page load - Current page:', window.location.href);
    debugLog('Page load - Nonce field exists:', $('#media_wipe_delete_unused_nonce').length > 0);
    debugLog('Page load - Nonce field value:', $('#media_wipe_delete_unused_nonce').val());
    debugLog('Page load - Delete unused page?', window.location.href.indexOf('delete-unused') !== -1);

    $(document).on('click', '#delete-selected-unused', function (e) {
        e.preventDefault();

        debugLog('Delete button clicked');
        debugLog('Button disabled state:', $(this).prop('disabled'));

        // If button is disabled, don't proceed (unless debugging)
        if ($(this).prop('disabled') && !MEDIA_WIPE_DEBUG) {
            debugLog('Button is disabled, not proceeding');
            return;
        }

        var selectedIds = [];

        // Debug checkbox availability
        var totalCheckboxes = $('.unused-file-checkbox').length;
        var checkedCheckboxes = $('.unused-file-checkbox:checked').length;
        debugLog('Total checkboxes found:', totalCheckboxes);
        debugLog('Checked checkboxes found:', checkedCheckboxes);

        // Collect selected IDs (handle both DataTable and regular table)
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#unused-media-datatable')) {
            var table = $('#unused-media-datatable').DataTable();
            debugLog('Using DataTable to collect IDs');

            var dtTotalCheckboxes = table.$('.unused-file-checkbox').length;
            var dtCheckedCheckboxes = table.$('.unused-file-checkbox:checked').length;
            debugLog('DataTable - Total checkboxes:', dtTotalCheckboxes);
            debugLog('DataTable - Checked checkboxes:', dtCheckedCheckboxes);

            table.$('.unused-file-checkbox:checked').each(function (index) {
                var id = $(this).data('id');
                debugLog('DataTable checkbox ' + index + ' ID:', id, 'Type:', typeof id);
                selectedIds.push(id);
            });
        } else {
            debugLog('Using regular table to collect IDs');
            $('.unused-file-checkbox:checked').each(function (index) {
                var id = $(this).data('id');
                debugLog('Regular checkbox ' + index + ' ID:', id, 'Type:', typeof id);
                selectedIds.push(id);
            });
        }

        debugLog('Selected IDs collected:', selectedIds);

        if (selectedIds.length === 0) {
            showNotification('warning', 'Please select files to delete.');
            return;
        }

        // Show confirmation dialog
        if (!confirm('Are you sure you want to delete ' + selectedIds.length + ' unused media files? This action cannot be undone.')) {
            return;
        }

        var $button = $(this);
        $button.prop('disabled', true).text('Deleting...');

        // Use form-specific nonce for delete unused operation, fallback to global nonce
        var nonceValue = $('#media_wipe_delete_unused_nonce').val();
        var useGlobalNonce = false;

        debugLog('Form nonce exists:', $('#media_wipe_delete_unused_nonce').length > 0);
        debugLog('Form nonce value:', nonceValue);
        debugLog('Global nonce available:', typeof mediaWipeAjax !== 'undefined' && mediaWipeAjax.nonce);
        debugLog('Global nonce value:', typeof mediaWipeAjax !== 'undefined' ? mediaWipeAjax.nonce : 'N/A');

        // Fallback to global nonce if form nonce is not available
        if (!nonceValue && typeof mediaWipeAjax !== 'undefined' && mediaWipeAjax.nonce) {
            nonceValue = mediaWipeAjax.nonce;
            useGlobalNonce = true;
            debugLog('Using global nonce as fallback');
        }

        if (!nonceValue) {
            showNotification('error', 'Security nonce not found. Please refresh the page.');
            return;
        }

        debugLog('Final nonce to use:', nonceValue);
        debugLog('Using global nonce:', useGlobalNonce);

        // Debug logging
        debugLog('Delete request data:', {
            action: 'media_wipe_delete_unused_files',
            nonce: nonceValue,
            selected_ids: selectedIds.join(','),
            selectedIds: selectedIds
        });

        // Check AJAX object availability
        if (typeof mediaWipeAjax === 'undefined') {
            showNotification('error', 'AJAX configuration not found. Please refresh the page.');
            debugLog('mediaWipeAjax object is undefined');
            return;
        }

        debugLog('AJAX URL:', mediaWipeAjax.ajaxurl);

        // Delete files
        $.ajax({
            url: mediaWipeAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'media_wipe_delete_unused_files',
                nonce: $('#media_wipe_delete_unused_nonce').val(),
                selected_ids: selectedIds.join(',')
            },
            success: function (response) {
                debugLog('Delete response:', response);

                // Show detailed error information
                if (!response.success) {
                    debugLog('Delete failed - Error details:', response.data);
                    if (response.data && response.data.message) {
                        debugLog('Error message:', response.data.message);
                    }
                }

                if (response.success) {
                    showNotification('success', response.data.message);

                    // Show debug info if available
                    if (response.data.debug_info) {
                        debugLog('Debug info:', response.data.debug_info);
                    }

                    // Show errors if any
                    if (response.data.errors && response.data.errors.length > 0) {
                        debugLog('Deletion errors:', response.data.errors);
                        response.data.errors.forEach(function (error) {
                            showNotification('warning', error);
                        });
                    }

                    // Remove deleted rows from table
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#unused-media-datatable')) {
                        var table = $('#unused-media-datatable').DataTable();
                        selectedIds.forEach(function (id) {
                            var row = table.$('.unused-file-checkbox[data-id="' + id + '"]').closest('tr');
                            table.row(row).remove();
                        });
                        table.draw();
                    } else {
                        selectedIds.forEach(function (id) {
                            $('.unused-file-checkbox[data-id="' + id + '"]').closest('tr').remove();
                        });
                    }
                    updateUnusedSelectionControls();
                } else {
                    showNotification('error', response.data.message || 'Deletion failed');
                }
            },
            error: function (xhr, status, error) {
                showNotification('error', 'Deletion failed: ' + error);
            },
            complete: function () {
                $button.prop('disabled', false);
                updateUnusedSelectionControls();
            }
        });
    });

    // Initialize Deletion History DataTable
    if ($('#deletion-history-datatable').length) {
        $('#deletion-history-datatable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[1, 'desc']], // Sort by date/time descending (newest first)
            columnDefs: [
                { width: "15%", targets: 0 }, // Type column
                { width: "25%", targets: 1 }, // Date column
                { width: "20%", targets: 2 }, // User column
                { width: "20%", targets: 3 }, // Action column
                { width: "20%", targets: 4 }  // IP column
            ],
            language: {
                search: "Search logs:",
                lengthMenu: "Show _MENU_ logs per page",
                info: "Showing _START_ to _END_ of _TOTAL_ log entries",
                infoEmpty: "No log entries found",
                infoFiltered: "(filtered from _MAX_ total entries)",
                emptyTable: "No deletion history available",
                zeroRecords: "No matching log entries found"
            }
        });
    }

    // Details modal functionality removed - simplified table without details column

});
