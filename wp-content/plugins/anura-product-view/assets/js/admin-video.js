/* Anura Product View — Admin Video Upload with file size validation */
(function ($) {
    'use strict';

    $(document).ready(function () {
        // Product video upload (product edit page)
        var $input = $('#apv-video-url');
        var $upload = $('#apv-upload-video');
        var $remove = $('#apv-remove-video');

        if ($upload.length) {
            $upload.on('click', function (e) {
                e.preventDefault();

                var frame = wp.media({
                    title: 'Select Product Video',
                    button: { text: 'Use this video' },
                    library: { type: 'video' },
                    multiple: false,
                });

                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var ext = attachment.filename.split('.').pop().toLowerCase();
                    var maxSize = apvAdmin.maxVideoSize;
                    var allowed = apvAdmin.allowedVideo;

                    if (allowed.indexOf(ext) === -1) {
                        alert('Invalid format. Accepted: ' + allowed.join(', ').toUpperCase());
                        return;
                    }

                    if (attachment.filesizeInBytes && attachment.filesizeInBytes > maxSize) {
                        alert('Video too large. Maximum size: ' + Math.round(maxSize / 1024 / 1024) + ' MB');
                        return;
                    }

                    $input.val(attachment.url);
                });

                frame.open();
            });
        }

        if ($remove.length) {
            $remove.on('click', function (e) {
                e.preventDefault();
                $input.val('');
                $(this).hide();
                $('#apv-video-meta video').remove();
            });
        }

        // How to Buy card upload buttons (settings page)
        $('.apv-htb-upload').on('click', function (e) {
            e.preventDefault();
            var targetId = $(this).data('target');
            var $target = $('#' + targetId);

            var frame = wp.media({
                title: 'Select Image or Video',
                button: { text: 'Use this media' },
                multiple: false,
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                $target.val(attachment.url);
            });

            frame.open();
        });
    });
})(jQuery);
