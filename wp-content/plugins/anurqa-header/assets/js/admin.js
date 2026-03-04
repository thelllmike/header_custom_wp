/* Anurqa Header — Admin Logo Upload */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var uploadBtn  = document.getElementById('anurqa-upload-logo');
        var removeBtn  = document.getElementById('anurqa-remove-logo');
        var input      = document.getElementById('anurqa_header_logo');
        var preview    = document.getElementById('anurqa-logo-preview');

        if (!uploadBtn) return;

        uploadBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var frame = wp.media({
                title: 'Select Logo',
                button: { text: 'Use this logo' },
                multiple: false,
                library: { type: ['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'] }
            });
            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                input.value = attachment.url;
                preview.innerHTML = '<img src="' + attachment.url + '" style="max-height:60px;">';
                removeBtn.style.display = '';
            });
            frame.open();
        });

        removeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            input.value = '';
            preview.innerHTML = '';
            removeBtn.style.display = 'none';
        });
    });
})();
