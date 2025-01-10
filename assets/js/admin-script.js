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

    // If the user clicks 'Yes', submit the form to delete media
    $('#delete-confirmation-yes').on('click', function () {
        // Set the selected media IDs in a hidden input field
        var mediaIdsString = mediaIds.join(',');
        $('<input>').attr({
            type: 'hidden',
            name: 'media_ids',
            value: mediaIdsString
        }).appendTo('#media-wipe-form');

        // Submit the form to delete the selected media
        $('#media-wipe-form').submit();

        // Hide the modal
        $('#delete-confirmation-modal').fadeOut();
    });

    // If the user clicks 'No', hide the modal
    $('#delete-confirmation-no').on('click', function () {
        $('#delete-confirmation-modal').fadeOut();
    });
});
