/* Anura Header — Admin (Logo + Image Uploads) */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        /* ─── Generic upload handler for all image upload buttons ─── */
        document.querySelectorAll('.anurqa-upload-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var targetId = this.getAttribute('data-target');
                var previewId = this.getAttribute('data-preview');
                var input = document.getElementById(targetId);
                var preview = document.getElementById(previewId);
                var removeBtn = this.nextElementSibling;

                var frame = wp.media({
                    title: 'Select Image',
                    button: { text: 'Use this image' },
                    multiple: false,
                    library: { type: ['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'] }
                });

                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    input.value = attachment.url;
                    preview.innerHTML = '<img src="' + attachment.url + '" style="max-height:100px;">';
                    if (removeBtn && removeBtn.classList.contains('anurqa-remove-btn')) {
                        removeBtn.style.display = '';
                    }
                });

                frame.open();
            });
        });

        /* ─── Generic remove handler ─── */
        document.querySelectorAll('.anurqa-remove-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var targetId = this.getAttribute('data-target');
                var previewId = this.getAttribute('data-preview');
                var input = document.getElementById(targetId);
                var preview = document.getElementById(previewId);
                input.value = '';
                preview.innerHTML = '';
                this.style.display = 'none';
            });
        });

        /* ─── Load demo JSON ─── */
        var demoLink = document.getElementById('anurqa-load-demo');
        if (demoLink) {
            demoLink.addEventListener('click', function (e) {
                e.preventDefault();
                var textarea = document.querySelector('textarea[name="anurqa_header_menu_data"]');
                if (!textarea) return;
                if (textarea.value.trim() && !confirm('This will replace your current JSON. Continue?')) return;

                var demo = [
                    {
                        "title": "Eyeglasses",
                        "url": "/eyeglasses/",
                        "columns": [
                            {
                                "heading": "<b>MEN</b> Eyeglasses",
                                "image": "",
                                "url": "/eyeglasses/men/",
                                "badge": "with FREE lenses",
                                "items": [
                                    {"label": "Premium Frames", "price": "Starts at LKR 8,500", "url": "#", "thumb": ""},
                                    {"label": "Budget Frames", "price": "Starts at LKR 3,500", "url": "#", "thumb": ""},
                                    {"label": "Titanium", "price": "Starts at LKR 12,000", "url": "#", "thumb": ""},
                                    {"label": "Essentials", "price": "Starts at LKR 2,500", "url": "#", "thumb": ""},
                                    {"label": "All Brands", "price": "Starts at LKR 2,500", "url": "#", "thumb": ""}
                                ]
                            },
                            {
                                "heading": "<b>WOMEN</b> Eyeglasses",
                                "image": "",
                                "url": "/eyeglasses/women/",
                                "badge": "with FREE lenses",
                                "items": [
                                    {"label": "Premium Frames", "price": "Starts at LKR 8,500", "url": "#", "thumb": ""},
                                    {"label": "Budget Frames", "price": "Starts at LKR 3,500", "url": "#", "thumb": ""},
                                    {"label": "Cat Eye", "price": "Starts at LKR 4,000", "url": "#", "thumb": ""},
                                    {"label": "Essentials", "price": "Starts at LKR 2,500", "url": "#", "thumb": ""},
                                    {"label": "All Brands", "price": "Starts at LKR 2,500", "url": "#", "thumb": ""}
                                ]
                            },
                            {
                                "heading": "<b>KIDS</b> Eyeglasses",
                                "image": "",
                                "url": "/eyeglasses/kids/",
                                "badge": "with FREE lenses",
                                "items": [
                                    {"label": "Juniors | 5 to 8 years", "price": "Starts at LKR 2,000", "url": "#", "thumb": ""},
                                    {"label": "Tweens | 8 to 12 years", "price": "Starts at LKR 2,500", "url": "#", "thumb": ""},
                                    {"label": "Teens | 12 to 17 years", "price": "Starts at LKR 3,500", "url": "#", "thumb": ""}
                                ]
                            }
                        ]
                    },
                    {
                        "title": "Sunglasses",
                        "url": "/sunglasses/",
                        "columns": [
                            {
                                "heading": "<b>MEN</b> Sunglasses",
                                "image": "",
                                "url": "/sunglasses/men/",
                                "badge": "Polarized with UV Protection",
                                "items": [
                                    {"label": "Premium Brands", "price": "Starts at LKR 8,000", "url": "#", "thumb": ""},
                                    {"label": "Aviator", "price": "Starts at LKR 3,500", "url": "#", "thumb": ""},
                                    {"label": "Sports", "price": "Starts at LKR 2,500", "url": "#", "thumb": ""},
                                    {"label": "All Brands", "price": "Starts at LKR 1,500", "url": "#", "thumb": ""}
                                ]
                            },
                            {
                                "heading": "<b>WOMEN</b> Sunglasses",
                                "image": "",
                                "url": "/sunglasses/women/",
                                "badge": "Polarized with UV Protection",
                                "items": [
                                    {"label": "Premium Brands", "price": "Starts at LKR 8,000", "url": "#", "thumb": ""},
                                    {"label": "Cat Eye", "price": "Starts at LKR 3,500", "url": "#", "thumb": ""},
                                    {"label": "Oversized", "price": "Starts at LKR 2,500", "url": "#", "thumb": ""},
                                    {"label": "All Brands", "price": "Starts at LKR 1,500", "url": "#", "thumb": ""}
                                ]
                            },
                            {
                                "heading": "<b>KIDS</b> Sunglasses",
                                "image": "",
                                "url": "/sunglasses/kids/",
                                "badge": "Polarized with UV Protection",
                                "items": [
                                    {"label": "Juniors | 5 to 8 years", "price": "Starts at LKR 1,500", "url": "#", "thumb": ""},
                                    {"label": "Tweens | 8 to 12 years", "price": "Starts at LKR 1,500", "url": "#", "thumb": ""},
                                    {"label": "Teens | 12 to 17 years", "price": "Starts at LKR 2,500", "url": "#", "thumb": ""}
                                ]
                            }
                        ]
                    },
                    {
                        "title": "Contacts",
                        "url": "/contacts/",
                        "columns": [
                            {
                                "heading": "<b>CLEAR</b> Contacts",
                                "image": "",
                                "url": "/contacts/clear/",
                                "badge": "10% OFF with Gold",
                                "items": [
                                    {"label": "Distance power (-ve)", "price": "Starts at LKR 1,200", "url": "#", "thumb": ""},
                                    {"label": "Toric/Cylindrical", "price": "Starts at LKR 1,800", "url": "#", "thumb": ""},
                                    {"label": "Multi-Focal", "price": "Starts at LKR 8,000", "url": "#", "thumb": ""},
                                    {"label": "All Powers", "price": "Starts at LKR 1,200", "url": "#", "thumb": ""}
                                ]
                            },
                            {
                                "heading": "<b>COLOR</b> Contacts",
                                "image": "",
                                "url": "/contacts/color/",
                                "badge": "10% OFF with Gold",
                                "items": [
                                    {"label": "Zero Power", "price": "Starts at LKR 900", "url": "#", "thumb": ""},
                                    {"label": "With Power", "price": "Starts at LKR 1,000", "url": "#", "thumb": ""},
                                    {"label": "Color Combos", "price": "Buy 4 at the price of 3!", "url": "#", "thumb": ""}
                                ]
                            },
                            {
                                "heading": "<b>Solution</b> & Accessories",
                                "image": "",
                                "url": "/contacts/accessories/",
                                "badge": "10% OFF with Gold",
                                "items": [
                                    {"label": "Solution", "price": "Starts at LKR 750", "url": "#", "thumb": ""},
                                    {"label": "Accessories", "price": "Starts at LKR 500", "url": "#", "thumb": ""}
                                ]
                            }
                        ]
                    },
                    {
                        "title": "Special Power",
                        "url": "/special-power/",
                        "columns": [
                            {
                                "heading": "<b>PRE-FIT</b> ZERO POWER",
                                "image": "",
                                "url": "/special-power/zero-power/",
                                "items": [
                                    {"label": "Computer Glasses", "price": "Starts at LKR 2,500", "url": "#", "thumb": ""},
                                    {"label": "Blue Light Blocking", "price": "Starts at LKR 1,500", "url": "#", "thumb": ""},
                                    {"label": "All Brands", "price": "Starts at LKR 1,500", "url": "#", "thumb": ""}
                                ]
                            },
                            {
                                "heading": "<b>PROGRESSIVE</b> Lenses",
                                "image": "",
                                "url": "/special-power/progressive/",
                                "items": [
                                    {"label": "Men", "price": "Starts at LKR 12,000", "url": "#", "thumb": ""},
                                    {"label": "Women", "price": "Starts at LKR 12,000", "url": "#", "thumb": ""}
                                ]
                            },
                            {
                                "heading": "<b>READING</b>",
                                "image": "",
                                "url": "/special-power/reading/",
                                "items": [],
                                "powers": [
                                    {"label": "+1.0", "url": "#"},
                                    {"label": "+1.25", "url": "#"},
                                    {"label": "+1.5", "url": "#"},
                                    {"label": "+1.75", "url": "#"},
                                    {"label": "+2.0", "url": "#"},
                                    {"label": "+2.25", "url": "#"},
                                    {"label": "+2.5", "url": "#"},
                                    {"label": "View All", "url": "#"}
                                ]
                            }
                        ]
                    },
                    {
                        "title": "Stores",
                        "url": "/stores/",
                        "type": "store",
                        "heading": "Find our Anura Optical Store",
                        "locations": [
                            {"name": "Pettah", "emoji": "\ud83c\udfdb", "url": "/stores/pettah/"}
                        ],
                        "cta_label": "Locate a Store",
                        "cta_url": "/stores/"
                    },
                    {
                        "title": "Try @ Home",
                        "url": "/try-at-home/",
                        "type": "home",
                        "heading": "Get your eyes checked at home",
                        "features": [
                            {"icon": "\ud83d\udc41", "text": "Professional Eye Checkup"},
                            {"icon": "\ud83d\udd2c", "text": "Latest Eye Test Equipment"},
                            {"icon": "\ud83d\udc53", "text": "Try 50+ frames at home"}
                        ],
                        "cta_label": "Book appointment",
                        "cta_url": "/try-at-home/book/"
                    }
                ];

                textarea.value = JSON.stringify(demo, null, 2);
            });
        }
    });
})();
