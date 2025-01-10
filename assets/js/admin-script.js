(function ($) {
    $(document).ready(function () {
        // Add your JavaScript if needed

        // Show the confirmation modal
        document.getElementById('delete-media-btn').addEventListener('click', function () {
            var selectedMedia = document.querySelectorAll('.media-checkbox:checked');
            if (selectedMedia.length > 0) {
                document.getElementById('deleteModal').style.display = 'block';
            } else {
                alert("Please select media to delete.");
            }
        });

        // Close modal on cancel
        document.getElementById('cancel-delete').addEventListener('click', function () {
            document.getElementById('deleteModal').style.display = 'none';
        });

        // Confirm deletion and submit the form
        document.getElementById('confirm-delete').addEventListener('click', function () {
            document.getElementById('media-wipe-form').submit();
        });

        // Close modal when clicking on the close button
        document.querySelector('.media-wipe-close-btn').addEventListener('click', function () {
            document.getElementById('deleteModal').style.display = 'none';
        });

    });
})(jQuery);
