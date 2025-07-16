jQuery(document).ready(function ($) {
    var mediaIds = [];

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
    $('#close-delete-all-modal, #cancel-delete-all, .modal-overlay').on('click', function (e) {
        if (e.target === this) {
            $('#delete-all-confirmation-modal').fadeOut(300);
            resetDeleteAllModal();
        }
    });

    // Prevent modal close when clicking inside modal content
    $('.modal-content-large').on('click', function (e) {
        e.stopPropagation();
    });

    // Handle checkbox changes and confirmation text
    function validateDeleteAllForm() {
        var hasAnyValidation = false;
        var isValid = true;

        // Check backup confirmation checkboxes if they exist
        if ($('#backup-confirmed').length) {
            hasAnyValidation = true;
            var allChecked = $('#backup-confirmed').is(':checked') &&
                $('#understand-permanent').is(':checked') &&
                $('#accept-responsibility').is(':checked');
            isValid = isValid && allChecked;
        }

        // Check text confirmation if it exists
        if ($('#confirmation-text').length) {
            hasAnyValidation = true;
            var confirmationText = normalizeText($('#confirmation-text').val());
            var textMatches = confirmationText === 'DELETE ALL MEDIA';
            isValid = isValid && textMatches;
        }

        // Check simple confirmation if it exists
        if ($('#final-confirm').length) {
            hasAnyValidation = true;
            var finalConfirmed = $('#final-confirm').is(':checked');
            isValid = isValid && finalConfirmed;
        }

        // If no validation elements exist, allow deletion
        if (!hasAnyValidation) {
            isValid = true;
        }

        // Debug logging (remove in production)
        // console.log('Validation result:', {
        //     hasAnyValidation: hasAnyValidation,
        //     isValid: isValid,
        //     backupExists: $('#backup-confirmed').length > 0,
        //     textExists: $('#confirmation-text').length > 0,
        //     simpleExists: $('#final-confirm').length > 0
        // });

        $('#confirm-delete-all').prop('disabled', !isValid);
        return isValid;
    }

    // Bind validation to form elements (only if they exist)
    $(document).on('change', '#backup-confirmed, #understand-permanent, #accept-responsibility, #final-confirm', validateDeleteAllForm);
    $(document).on('input', '#confirmation-text', validateDeleteAllForm);

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
        // Reset backup confirmation checkboxes if they exist
        $('#backup-confirmed, #understand-permanent, #accept-responsibility').prop('checked', false);

        // Reset text confirmation if it exists
        if ($('#confirmation-text').length) {
            $('#confirmation-text').val('');
        }

        // Reset simple confirmation if it exists
        $('#final-confirm').prop('checked', false);

        // Reset button state
        $('#confirm-delete-all').text('Delete All Media Files');

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
});
