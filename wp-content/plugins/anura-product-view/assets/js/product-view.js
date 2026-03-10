/* ═══════════════════════════════════════════════════
   Anura Product View — Frontend JS  v1.7.0
   ═══════════════════════════════════════════════════ */
(function () {
    'use strict';

    var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
    var $$ = function (sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); };

    /* ═══════════════════════════════════════
       1) IMAGE GALLERY (with video support)
       ═══════════════════════════════════════ */
    function initGallery() {
        var mainImg = $('#apv-main-image');
        var thumbs  = $$('.apv-gallery__thumb');
        var prevBtn = $('.apv-gallery__arrow--prev');
        var nextBtn = $('.apv-gallery__arrow--next');
        if (!mainImg || thumbs.length === 0) return;

        var currentIndex = 0;
        var videoEl = null;

        function showImage(index) {
            if (index < 0) index = thumbs.length - 1;
            if (index >= thumbs.length) index = 0;
            currentIndex = index;

            var thumb = thumbs[index];
            var type = thumb.getAttribute('data-type');

            if (type === 'video') {
                var videoUrl = thumb.getAttribute('data-video');
                mainImg.classList.add('apv-gallery__image--hidden');

                if (!videoEl) {
                    videoEl = document.createElement('video');
                    videoEl.className = 'apv-gallery__video';
                    videoEl.controls = true;
                    videoEl.playsInline = true;
                    mainImg.parentNode.insertBefore(videoEl, mainImg.nextSibling);
                }
                videoEl.src = videoUrl;
                videoEl.classList.add('apv-gallery__video--active');
                videoEl.play();
            } else {
                var fullUrl = thumb.getAttribute('data-full');
                mainImg.style.opacity = '0';
                mainImg.classList.remove('apv-gallery__image--hidden');

                if (videoEl) {
                    videoEl.pause();
                    videoEl.classList.remove('apv-gallery__video--active');
                }

                setTimeout(function () {
                    mainImg.src = fullUrl;
                    mainImg.style.opacity = '1';
                }, 150);
            }

            thumbs.forEach(function (t, i) {
                t.classList.toggle('apv-gallery__thumb--active', i === index);
            });
        }

        thumbs.forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                showImage(parseInt(this.getAttribute('data-index'), 10));
            });
        });

        if (prevBtn) prevBtn.addEventListener('click', function () { showImage(currentIndex - 1); });
        if (nextBtn) nextBtn.addEventListener('click', function () { showImage(currentIndex + 1); });

        var gallery = $('.apv-gallery');
        if (gallery) {
            gallery.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowLeft') showImage(currentIndex - 1);
                if (e.key === 'ArrowRight') showImage(currentIndex + 1);
            });
        }

        var startX = 0;
        var mainWrap = $('.apv-gallery__main');
        if (mainWrap) {
            mainWrap.addEventListener('touchstart', function (e) {
                startX = e.touches[0].clientX;
            }, { passive: true });
            mainWrap.addEventListener('touchend', function (e) {
                var diff = startX - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 40) {
                    showImage(diff > 0 ? currentIndex + 1 : currentIndex - 1);
                }
            }, { passive: true });
        }
    }

    /* ═══════════════════════════════════════
       2) COLOR SWATCHES — synced with WC variations
       ═══════════════════════════════════════ */
    function initColorSwatches() {
        var swatches = $$('.apv-color-swatch');
        var label = $('.apv-info__selected-color');
        if (swatches.length === 0) return;

        // Show initial selection
        var initial = $('.apv-color-swatch--active');
        if (initial && label) {
            label.textContent = '— ' + initial.getAttribute('data-color');
        }

        swatches.forEach(function (swatch) {
            swatch.addEventListener('click', function () {
                swatches.forEach(function (s) { s.classList.remove('apv-color-swatch--active'); });
                this.classList.add('apv-color-swatch--active');

                var color = this.getAttribute('data-color');
                if (label) label.textContent = '— ' + color;

                // Sync with WC variation dropdown
                syncVariation('pa_color', color);
                syncVariation('color', color);
            });
        });
    }

    /* ═══════════════════════════════════════
       3) SIZE BUTTONS — synced with WC variations
       ═══════════════════════════════════════ */
    function initSizeButtons() {
        var btns = $$('.apv-size-btn');
        var label = $('.apv-info__selected-size');
        if (btns.length === 0) return;

        // Show initial selection
        var initial = $('.apv-size-btn--active');
        if (initial && label) {
            label.textContent = '— ' + initial.getAttribute('data-size');
        }

        btns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                btns.forEach(function (b) { b.classList.remove('apv-size-btn--active'); });
                this.classList.add('apv-size-btn--active');

                var size = this.getAttribute('data-size');
                if (label) label.textContent = '— ' + size;

                // Sync with WC variation dropdown
                syncVariation('pa_size', size);
                syncVariation('size', size);
            });
        });
    }

    /**
     * Sync custom swatch selection with WooCommerce variation dropdowns.
     * WooCommerce requires jQuery-triggered events for variation logic.
     */
    function syncVariation(attrName, value) {
        var select = $('select[data-attribute_name="attribute_' + attrName + '"]');
        if (!select) return;

        // Find matching option (case-insensitive, slug-aware)
        var valueLower = value.toLowerCase().replace(/\s+/g, '-');
        var options = select.options;
        for (var i = 0; i < options.length; i++) {
            var optVal = options[i].value.toLowerCase();
            if (optVal === valueLower || optVal === value.toLowerCase()) {
                select.value = options[i].value;

                // WooCommerce listens for jQuery events, not native DOM events
                if (window.jQuery) {
                    window.jQuery(select).trigger('change');
                } else {
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
                break;
            }
        }
    }

    /**
     * Hide WC variation rows that our custom swatches replace (fallback for no :has())
     */
    function hideWcVariationDropdowns() {
        var attrs = ['pa_color', 'color', 'pa_size', 'size'];
        attrs.forEach(function (attr) {
            var select = $('select[data-attribute_name="attribute_' + attr + '"]');
            if (select) {
                var row = select.closest('tr');
                if (row) row.classList.add('apv-hidden-variation');
            }
        });
    }

    /* ═══════════════════════════════════════
       4) PINCODE CHECKER
       ═══════════════════════════════════════ */
    function initPincode() {
        var input = $('#apv-pincode');
        var btn   = $('#apv-pincode-btn');
        var note  = $('#apv-delivery-note');
        if (!input || !btn || !note) return;

        btn.addEventListener('click', function () {
            var pin = input.value.trim();
            if (!pin) { input.focus(); return; }

            note.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2e8b57" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>' +
                '<div><strong>Delivery available to ' + pin + '</strong><p>Estimated delivery: 3-5 business days</p></div>';
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                btn.click();
            }
        });
    }

    /* ═══════════════════════════════════════
       5) STICKY CTA BAR
       ═══════════════════════════════════════ */
    function initStickyCTA() {
        var cta = $('#apv-sticky-cta');
        var priceEl = $('.apv-info__price');
        if (!cta || !priceEl) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                cta.classList.toggle('apv-sticky-cta--visible', !entry.isIntersecting);
            });
        }, { threshold: 0 });

        observer.observe(priceEl);
    }

    /* ═══════════════════════════════════════
       6) LOAD MORE REVIEWS
       ═══════════════════════════════════════ */
    function initLoadMore() {
        var btn = $('#apv-load-more');
        var container = $('#apv-reviews-container');
        var list = $('#apv-reviews-list');
        if (!btn || !container || !list) return;

        var page = 1;

        btn.addEventListener('click', function () {
            page++;
            btn.textContent = 'Loading...';
            btn.disabled = true;

            var formData = new FormData();
            formData.append('action', 'apv_load_reviews');
            formData.append('nonce', apvData.nonce);
            formData.append('product_id', list.getAttribute('data-product'));
            formData.append('page', page);

            fetch(apvData.ajaxUrl, {
                method: 'POST',
                body: formData,
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success && res.data.html) {
                    container.insertAdjacentHTML('beforeend', res.data.html);
                }
                if (!res.data.has_more) {
                    btn.remove();
                } else {
                    btn.textContent = 'Load more reviews';
                    btn.disabled = false;
                }
            })
            .catch(function () {
                btn.textContent = 'Load more reviews';
                btn.disabled = false;
            });
        });
    }

    /* ═══════════════════════════════════════
       7) SIMILAR PRODUCTS (scroll to related)
       ═══════════════════════════════════════ */
    function initSimilar() {
        var btn = $('#apv-similar-btn');
        var related = $('.apv-related');
        if (!btn || !related) return;

        btn.addEventListener('click', function () {
            related.scrollIntoView({ behavior: 'smooth' });
        });
    }

    /* ═══════════════════════════════════════
       8) VIRTUAL TRY-ON MODAL
       ═══════════════════════════════════════ */
    function initTryOn() {
        var btn = $('#apv-tryon-btn');
        var modal = $('#apv-tryon-modal');
        if (!btn || !modal) return;

        var overlay = modal.querySelector('.apv-tryon-modal__overlay');
        var closeBtn = modal.querySelector('.apv-tryon-modal__close');
        var iframe = $('#apv-tryon-iframe');

        function openModal() {
            var pngUrl = btn.getAttribute('data-image');
            var tryonBase = apvData.tryOnUrl || '/virtual-tryon/';

            // Build URL: use /?png= format for Vercel app, index.html for local
            var src;
            if (tryonBase.indexOf('http') === 0 && tryonBase.indexOf(window.location.host) === -1) {
                // External Vercel app URL
                src = tryonBase + '/?embedded=1';
            } else {
                // Local plugin URL
                src = tryonBase + 'index.html?embedded=1';
            }
            if (pngUrl) src += '&png=' + encodeURIComponent(pngUrl);

            iframe.src = src;
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.style.display = 'none';
            iframe.src = '';
            document.body.style.overflow = '';
        }

        btn.addEventListener('click', openModal);
        closeBtn.addEventListener('click', closeModal);
        overlay.addEventListener('click', closeModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.style.display === 'block') {
                closeModal();
            }
        });
    }

    /* ═══════════════════════════════════════
       9) WISHLIST HEART
       ═══════════════════════════════════════ */
    function initWishlist() {
        var mainBtn = $('.apv-product > .apv-product__top .apv-wishlist-btn');
        if (!mainBtn) return;

        mainBtn.addEventListener('click', function (e) {
            e.preventDefault();
            this.classList.toggle('apv-wishlist-btn--active');
        });
    }

    /* ═══════════════════════════════════════
       10) SELECT LENSES — open Anura Lens Selector modal
       ═══════════════════════════════════════ */
    function initSelectLenses() {
        var allTriggers = $$('.apv-select-lenses-btn, .apv-select-lenses-trigger');
        if (allTriggers.length === 0) return;

        function handleSelectLenses() {
            // Priority 1: Open the Anura Lens Selector modal (from Lens Category plugin)
            var alsBtn = $('#als-select-lens-btn');
            if (alsBtn) {
                alsBtn.click();
                return;
            }

            // Priority 2: Fallback to WC add-to-cart form
            var wcForm = $('form.cart');
            if (wcForm) {
                if (window.jQuery) {
                    var submitBtn = window.jQuery(wcForm).find('button[type="submit"]');
                    if (submitBtn.length) {
                        submitBtn.trigger('click');
                        return;
                    }
                }
                wcForm.submit();
                return;
            }

            // Priority 3: Direct URL fallback
            var mainBtn = $('#apv-select-lenses-btn');
            var url = mainBtn ? mainBtn.getAttribute('data-add-to-cart-url') : '';
            if (url) {
                window.location.href = url;
            }
        }

        allTriggers.forEach(function (btn) {
            btn.addEventListener('click', handleSelectLenses);
        });
    }

    /* ═══════════════════════════════════════
       11) HOW-TO-BUY CARDS — auto-play + video modal
       ═══════════════════════════════════════ */
    function initHowToCards() {
        var videoCards = $$('.apv-how-card--video');
        var videos = [];
        videoCards.forEach(function (card) {
            var v = card.querySelector('video');
            if (v) videos.push(v);
        });

        if (videos.length === 0) return;

        var currentIndex = 0;
        var autoTimer = null;
        var hoverPaused = false;

        function pauseAllExcept(except) {
            videos.forEach(function (v) {
                if (v !== except && !v.paused) {
                    v.pause();
                    v.currentTime = 0;
                }
            });
        }

        function playIndex(idx) {
            if (hoverPaused) return;
            var video = videos[idx];
            if (!video) return;
            pauseAllExcept(video);
            var p = video.play();
            if (p) p.catch(function () {});
        }

        function scheduleNext(delay) {
            clearTimeout(autoTimer);
            autoTimer = setTimeout(function () {
                if (hoverPaused) return;
                playIndex(currentIndex);
            }, delay);
        }

        // When a video ends, advance to next
        videos.forEach(function (video, idx) {
            video.addEventListener('ended', function () {
                video.currentTime = 0;
                currentIndex = (idx + 1) % videos.length;
                scheduleNext(1500);
            });
        });

        // Hover: play on enter, reset on leave
        videoCards.forEach(function (card, idx) {
            var video = videos[idx];
            if (!video) return;

            card.addEventListener('mouseenter', function () {
                hoverPaused = true;
                clearTimeout(autoTimer);
                pauseAllExcept(video);
                var p = video.play();
                if (p) p.catch(function () {});
            });

            card.addEventListener('mouseleave', function () {
                hoverPaused = false;
                video.pause();
                video.currentTime = 0;
                currentIndex = (idx + 1) % videos.length;
                scheduleNext(2000);
            });
        });

        // Click: open video modal
        videoCards.forEach(function (card) {
            card.addEventListener('click', function (e) {
                e.preventDefault();
                var src = card.getAttribute('data-video-src');
                var title = card.getAttribute('data-video-title');
                if (src) openVideoModal(src, title);
            });
        });

        // Start auto-play after 2s
        scheduleNext(2000);

        // ─── Video Modal ─────────────────────
        var modal = $('#apv-video-modal');
        if (!modal) return;

        var overlay = modal.querySelector('.apv-video-modal__overlay');
        var closeBtn = modal.querySelector('.apv-video-modal__close');
        var modalVideo = $('#apv-video-modal-player');
        var modalTitle = $('#apv-video-modal-title');
        var timeDisplay = $('#apv-video-modal-time');
        var progressBar = $('#apv-video-modal-progress');
        var muteBtn = $('#apv-video-modal-mute');

        function openVideoModal(src, title) {
            videos.forEach(function (v) { v.pause(); v.currentTime = 0; });
            hoverPaused = true;
            clearTimeout(autoTimer);

            modalTitle.textContent = title || '';
            modalVideo.src = src;
            modalVideo.muted = false;
            muteBtn.classList.add('apv-unmuted');

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            var p = modalVideo.play();
            if (p) p.catch(function () {
                modalVideo.muted = true;
                muteBtn.classList.remove('apv-unmuted');
                modalVideo.play();
            });
        }

        function closeVideoModal() {
            modal.style.display = 'none';
            modalVideo.pause();
            modalVideo.src = '';
            document.body.style.overflow = '';
            hoverPaused = false;
            currentIndex = 0;
            scheduleNext(2000);
        }

        closeBtn.addEventListener('click', closeVideoModal);
        overlay.addEventListener('click', closeVideoModal);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                closeVideoModal();
            }
        });

        modalVideo.addEventListener('timeupdate', function () {
            var cur = modalVideo.currentTime;
            var dur = modalVideo.duration || 0;
            var mins = Math.floor(cur / 60);
            var secs = Math.floor(cur % 60);
            timeDisplay.textContent = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
            if (dur > 0) progressBar.value = (cur / dur) * 100;
        });

        progressBar.addEventListener('input', function () {
            var dur = modalVideo.duration || 0;
            if (dur > 0) modalVideo.currentTime = (progressBar.value / 100) * dur;
        });

        muteBtn.addEventListener('click', function () {
            modalVideo.muted = !modalVideo.muted;
            muteBtn.classList.toggle('apv-unmuted', !modalVideo.muted);
        });

        modalVideo.addEventListener('ended', function () {
            modalVideo.currentTime = 0;
            modalVideo.play();
        });
    }

    function init() {
        initGallery();
        initColorSwatches();
        initSizeButtons();
        hideWcVariationDropdowns();
        initPincode();
        initStickyCTA();
        initLoadMore();
        initSimilar();
        initTryOn();
        initWishlist();
        initSelectLenses();
        initHowToCards();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
