jQuery(document).ready(function ($) {
    var mediaIds = [];

    // When the delete button is clicked
    $('#open-delete-modal').on('click', function (e) {
        // Prevent default form submission
        e.preventDefault();

        // Get the selected media IDs
        mediaIds = [];
        $('input[name="delete_media[]"]:checked').each(function () {
            mediaIds.push($(this).val());
        });

        // If no media is selected, return
        if (mediaIds.length === 0) {
            alert('Please select media to delete.');
            return;
        }

        // Show the confirmation modal
        $('#delete-confirmation-modal').fadeIn();
    });

    // If the user clicks 'Yes', submit the form to delete media via AJAX
    $('#delete-confirmation-yes').on('click', function () {
        // Make sure there are media IDs to delete
        if (mediaIds.length === 0) {
            alert('No media selected.');
            return;
        }

        // Make an AJAX request to delete the selected media
        $.ajax({
            url: ajaxurl, // WordPress AJAX handler
            type: 'POST',
            data: {
                action: 'media_wipe_delete_unused_media', // Your custom action
                media_ids: mediaIds // Pass the selected media IDs
            },
            success: function (response) {
                // Show success message on the page
                // alert('Selected media files have been deleted.');

                // Reload the page or update the UI to reflect the change
                location.reload(); // Reload page to reflect changes

                // Close the modal
                $('#delete-confirmation-modal').fadeOut();
            },
            error: function (xhr, status, error) {
                // Show error message
                alert('An error occurred: ' + error);
            }
        });
    });

    // If the user clicks 'No', hide the modal
    $('#delete-confirmation-no').on('click', function () {
        $('#delete-confirmation-modal').fadeOut();
    });
});
