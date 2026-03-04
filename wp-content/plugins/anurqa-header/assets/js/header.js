/* ═══════════════════════════════════════════════════
   Anurqa Optical — Header JS (Vanilla, No jQuery)
   ═══════════════════════════════════════════════════ */
(function () {
    'use strict';

    /* ─── Globals from wp_localize_script ─── */
    var D = window.anurqaHeaderData || {};

    /* ─── DOM helpers ─── */
    var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
    var $$ = function (sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); };

    /* ═══════════════════════════════════════
       1) DESKTOP MEGA MENU — hover + keyboard
       ═══════════════════════════════════════ */
    function initMegaMenu() {
        var items = $$('.anurqa-nav__item');
        var closeTimer = null;

        items.forEach(function (item) {
            var link = $('a', item);
            var mega = $('.anurqa-mega', item);
            if (!mega) return;

            function open() {
                clearTimeout(closeTimer);
                // Close others
                items.forEach(function (other) {
                    if (other !== item) other.setAttribute('aria-expanded', 'false');
                });
                item.setAttribute('aria-expanded', 'true');
            }

            function close() {
                closeTimer = setTimeout(function () {
                    item.setAttribute('aria-expanded', 'false');
                }, 150);
            }

            // Mouse
            item.addEventListener('mouseenter', open);
            item.addEventListener('mouseleave', close);
            mega.addEventListener('mouseenter', function () { clearTimeout(closeTimer); });
            mega.addEventListener('mouseleave', close);

            // Keyboard
            link.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    open();
                    var firstLink = $('a', mega);
                    if (firstLink) firstLink.focus();
                }
            });

            // ESC closes
            mega.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    item.setAttribute('aria-expanded', 'false');
                    link.focus();
                }
            });
        });

        // Close all on outside click
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.anurqa-nav__item')) {
                items.forEach(function (item) {
                    item.setAttribute('aria-expanded', 'false');
                });
            }
        });
    }

    /* ═══════════════════════════════════════
       2) DESKTOP SEARCH DROPDOWN
       ═══════════════════════════════════════ */
    function initSearch() {
        var toggle = $('.anurqa-search__toggle');
        var dropdown = $('.anurqa-search__dropdown');
        var input = $('.anurqa-search__input');
        var results = $('.anurqa-search__results');

        if (!toggle || !dropdown) return;

        // Mock suggestions (replace with WP REST API later)
        var suggestions = [
            { icon: '👓', text: 'eyeglasses' },
            { icon: '🕶', text: 'sunglasses' },
            { icon: '👁', text: 'contact lens' },
            { icon: '🏅', text: 'gold max membership' },
            { icon: '🏠', text: 'lenskart@home' },
            { icon: '🔄', text: 'lenskart exchange' },
            { icon: '💻', text: 'zero power computer glasses' },
        ];

        function openSearch() {
            dropdown.hidden = false;
            dropdown.setAttribute('aria-expanded', 'true');
            toggle.setAttribute('aria-expanded', 'true');
            requestAnimationFrame(function () {
                input.focus();
            });
            renderSuggestions('');
        }

        function closeSearch() {
            dropdown.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-expanded', 'false');
            setTimeout(function () {
                dropdown.hidden = true;
            }, 200);
        }

        function renderSuggestions(query) {
            var q = query.toLowerCase().trim();
            var filtered = q
                ? suggestions.filter(function (s) { return s.text.includes(q); })
                : suggestions;

            results.innerHTML = filtered.map(function (s) {
                return '<a class="anurqa-search__result-item" href="' + (D.searchUrl || '/?s=') + encodeURIComponent(s.text) + '">' +
                    '<span class="anurqa-search__result-icon">' + s.icon + '</span>' +
                    '<span class="anurqa-search__result-text">' + escapeHtml(s.text) + '</span>' +
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M17 7H7M17 7v10"/></svg>' +
                    '</a>';
            }).join('');
        }

        toggle.addEventListener('click', function () {
            var isOpen = toggle.getAttribute('aria-expanded') === 'true';
            isOpen ? closeSearch() : openSearch();
        });

        if (input) {
            input.addEventListener('input', function () {
                renderSuggestions(this.value);
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeSearch();
                if (e.key === 'Enter') {
                    e.preventDefault();
                    window.location.href = (D.searchUrl || '/?s=') + encodeURIComponent(this.value);
                }
            });
        }

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.anurqa-search')) {
                closeSearch();
            }
        });
    }

    /* ═══════════════════════════════════════
       3) MOBILE DRAWER
       ═══════════════════════════════════════ */
    function initDrawer() {
        var hamburger = $('.anurqa-hamburger');
        var drawer    = $('#anurqa-drawer');
        var closeBtn  = $('.anurqa-drawer__close');
        var overlay   = $('.anurqa-overlay');
        var nav       = $('.anurqa-drawer__nav');
        var greeting  = $('.anurqa-drawer__greeting');

        if (!hamburger || !drawer) return;

        // Set greeting
        if (greeting) {
            if (D.isLoggedIn === '1' && D.userName) {
                greeting.textContent = 'Hi ' + D.userName + '!';
            } else {
                greeting.textContent = 'Hi Specsy!';
            }
        }

        // Build accordion from menu data
        buildDrawerAccordion(nav);

        var focusableEls = [];
        var firstFocusable, lastFocusable;

        function openDrawer() {
            drawer.classList.add('anurqa-drawer--open');
            overlay.classList.add('anurqa-overlay--active');
            overlay.setAttribute('aria-hidden', 'false');
            hamburger.setAttribute('aria-expanded', 'true');
            document.body.classList.add('anurqa-scroll-lock');

            // Focus trap setup
            focusableEls = $$('a[href], button, input, [tabindex]:not([tabindex="-1"])', drawer);
            firstFocusable = focusableEls[0];
            lastFocusable = focusableEls[focusableEls.length - 1];

            requestAnimationFrame(function () {
                if (closeBtn) closeBtn.focus();
            });
        }

        function closeDrawer() {
            drawer.classList.remove('anurqa-drawer--open');
            overlay.classList.remove('anurqa-overlay--active');
            overlay.setAttribute('aria-hidden', 'true');
            hamburger.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('anurqa-scroll-lock');
            hamburger.focus();
        }

        hamburger.addEventListener('click', openDrawer);
        if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
        if (overlay) overlay.addEventListener('click', closeDrawer);

        // ESC to close + focus trap
        document.addEventListener('keydown', function (e) {
            if (!drawer.classList.contains('anurqa-drawer--open')) return;

            if (e.key === 'Escape') {
                closeDrawer();
                return;
            }

            // Focus trap
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstFocusable) {
                        e.preventDefault();
                        lastFocusable.focus();
                    }
                } else {
                    if (document.activeElement === lastFocusable) {
                        e.preventDefault();
                        firstFocusable.focus();
                    }
                }
            }
        });
    }

    /* ═══════════════════════════════════════
       4) BUILD DRAWER ACCORDION FROM MENU DATA
       ═══════════════════════════════════════ */
    function buildDrawerAccordion(navEl) {
        if (!navEl) return;

        var menuItems = getMenuData();
        var html = '';

        menuItems.forEach(function (item, idx) {
            var hasChildren = item.columns && item.columns.length > 0;
            var allSubs = [];

            if (hasChildren) {
                item.columns.forEach(function (col) {
                    if (col.items) {
                        col.items.forEach(function (sub) {
                            allSubs.push(sub);
                        });
                    }
                });
            }

            if (allSubs.length === 0) {
                // Simple link
                html += '<div class="anurqa-accordion">';
                html += '<a href="' + escapeAttr(item.url) + '" class="anurqa-accordion__trigger" style="text-decoration:none">';
                html += escapeHtml(item.title);
                html += '</a></div>';
            } else {
                // Accordion
                var panelId = 'anurqa-panel-' + idx;
                html += '<div class="anurqa-accordion">';
                html += '<button class="anurqa-accordion__trigger" aria-expanded="false" aria-controls="' + panelId + '">';
                html += escapeHtml(item.title);
                html += '<svg class="anurqa-accordion__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>';
                html += '</button>';
                html += '<div id="' + panelId + '" class="anurqa-accordion__panel" role="region">';
                html += '<ul>';
                allSubs.forEach(function (sub) {
                    html += '<li><a href="' + escapeAttr(sub.url) + '">' + escapeHtml(sub.label) + '</a></li>';
                });
                html += '</ul></div></div>';
            }
        });

        navEl.innerHTML = html;

        // Accordion toggle
        $$('.anurqa-accordion__trigger[aria-controls]', navEl).forEach(function (btn) {
            btn.addEventListener('click', function () {
                var expanded = this.getAttribute('aria-expanded') === 'true';
                var panel = document.getElementById(this.getAttribute('aria-controls'));

                // Close others (optional — remove if you want multiple open)
                $$('.anurqa-accordion__trigger[aria-controls]', navEl).forEach(function (other) {
                    if (other !== btn) {
                        other.setAttribute('aria-expanded', 'false');
                        var otherPanel = document.getElementById(other.getAttribute('aria-controls'));
                        if (otherPanel) otherPanel.style.maxHeight = '0';
                    }
                });

                if (expanded) {
                    this.setAttribute('aria-expanded', 'false');
                    panel.style.maxHeight = '0';
                } else {
                    this.setAttribute('aria-expanded', 'true');
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                }
            });
        });
    }

    /* ═══════════════════════════════════════
       5) CART BADGE
       ═══════════════════════════════════════ */
    function initCartBadge() {
        var count = parseInt(D.cartCount, 10) || 0;
        $$('.anurqa-cart-badge').forEach(function (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.setAttribute('data-count', count);
                badge.hidden = false;
            } else {
                badge.hidden = true;
            }
        });
    }

    /* ═══════════════════════════════════════
       6) MOBILE SEARCH OVERLAY
       ═══════════════════════════════════════ */
    function initMobileSearch() {
        // Add a search button to mobile header if not present
        var mobileHeader = $('.anurqa-header--mobile');
        var mobileSearch = $('.anurqa-mobile-search');
        if (!mobileHeader || !mobileSearch) return;

        // Add search button after hamburger
        var existingSearchBtn = $('.anurqa-mobile-search-btn', mobileHeader);
        if (!existingSearchBtn) {
            var searchBtn = document.createElement('button');
            searchBtn.className = 'anurqa-action-btn anurqa-mobile-search-btn';
            searchBtn.setAttribute('aria-label', 'Search');
            searchBtn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>';
            var actions = $('.anurqa-mobile-actions', mobileHeader);
            if (actions) actions.prepend(searchBtn);
            existingSearchBtn = searchBtn;
        }

        var closeBtn = $('.anurqa-mobile-search__close');
        var input = $('input', mobileSearch);

        existingSearchBtn.addEventListener('click', function () {
            mobileSearch.classList.add('anurqa-mobile-search--active');
            document.body.classList.add('anurqa-scroll-lock');
            if (input) {
                requestAnimationFrame(function () { input.focus(); });
            }
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                mobileSearch.classList.remove('anurqa-mobile-search--active');
                document.body.classList.remove('anurqa-scroll-lock');
            });
        }

        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    mobileSearch.classList.remove('anurqa-mobile-search--active');
                    document.body.classList.remove('anurqa-scroll-lock');
                }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    window.location.href = (D.searchUrl || '/?s=') + encodeURIComponent(this.value);
                }
            });
        }
    }

    /* ═══════════════════════════════════════
       HELPERS
       ═══════════════════════════════════════ */
    function getMenuData() {
        // Try parsed JSON from admin
        if (D.menuJson) {
            try {
                var parsed = JSON.parse(D.menuJson);
                if (Array.isArray(parsed)) return parsed;
            } catch (e) { /* fall through */ }
        }
        // Fallback: parse from DOM (server-rendered nav items)
        var items = [];
        $$('.anurqa-nav__item').forEach(function (el) {
            var link = $('a.anurqa-nav__link', el);
            var mega = $('.anurqa-mega', el);
            var item = {
                title: link ? link.textContent.trim() : '',
                url: link ? link.getAttribute('href') : '#',
                columns: [],
                trending: []
            };

            if (mega) {
                $$('.anurqa-mega__col', mega).forEach(function (colEl) {
                    var col = {
                        heading: '',
                        items: []
                    };
                    var titleEl = $('.anurqa-mega__col-title', colEl);
                    if (titleEl) col.heading = titleEl.textContent.trim();

                    $$('.anurqa-mega__items a', colEl).forEach(function (a) {
                        var labelEl = $('.anurqa-mega__item-label', a);
                        col.items.push({
                            label: labelEl ? labelEl.textContent.trim() : a.textContent.trim(),
                            url: a.getAttribute('href') || '#'
                        });
                    });

                    item.columns.push(col);
                });
            }

            items.push(item);
        });
        return items;
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function escapeAttr(str) {
        return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    /* ═══════════════════════════════════════
       INIT
       ═══════════════════════════════════════ */
    function init() {
        initMegaMenu();
        initSearch();
        initDrawer();
        initCartBadge();
        initMobileSearch();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
