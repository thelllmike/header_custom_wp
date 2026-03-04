/* ═══════════════════════════════════════════════════
   Anurqa Optical — Header JS v2 (Vanilla, No jQuery)
   ═══════════════════════════════════════════════════ */
(function () {
    'use strict';

    var D = window.anurqaHeaderData || {};
    var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
    var $$ = function (sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); };

    /* ═══════════════════════════════════════
       1) MEGA MENU — hover + keyboard
       ═══════════════════════════════════════ */
    function initMegaMenu() {
        var items = $$('.anurqa-nav__item--has-mega');
        var closeTimer = null;

        items.forEach(function (item) {
            var link = $('a.anurqa-nav__link', item);
            var mega = $('.anurqa-mega', item);
            if (!mega) return;

            function open() {
                clearTimeout(closeTimer);
                items.forEach(function (o) { if (o !== item) o.setAttribute('aria-expanded', 'false'); });
                item.setAttribute('aria-expanded', 'true');
            }
            function close() {
                closeTimer = setTimeout(function () { item.setAttribute('aria-expanded', 'false'); }, 150);
            }

            item.addEventListener('mouseenter', open);
            item.addEventListener('mouseleave', close);
            mega.addEventListener('mouseenter', function () { clearTimeout(closeTimer); });
            mega.addEventListener('mouseleave', close);

            link.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                    e.preventDefault(); open();
                    var first = $('a', mega);
                    if (first) first.focus();
                }
            });
            mega.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { item.setAttribute('aria-expanded', 'false'); link.focus(); }
            });
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.anurqa-nav__item')) {
                items.forEach(function (i) { i.setAttribute('aria-expanded', 'false'); });
            }
        });
    }

    /* ═══════════════════════════════════════
       2) INLINE SEARCH (desktop)
       ═══════════════════════════════════════ */
    function initInlineSearch() {
        var wrap = $('.anurqa-search-inline');
        if (!wrap) return;
        var input = $('.anurqa-search-inline__input', wrap);
        var dropdown = $('.anurqa-search-inline__dropdown', wrap);

        var suggestions = [
            { icon: '👓', text: 'eyeglasses' },
            { icon: '🕶', text: 'sunglasses' },
            { icon: '👁', text: 'contact lens' },
            { icon: '💻', text: 'computer glasses' },
            { icon: '📖', text: 'reading glasses' },
            { icon: '🧒', text: 'kids eyeglasses' },
        ];

        function render(q) {
            var query = (q || '').toLowerCase().trim();
            var filtered = query ? suggestions.filter(function (s) { return s.text.includes(query); }) : suggestions;
            if (filtered.length === 0) { dropdown.hidden = true; return; }
            dropdown.innerHTML = filtered.map(function (s) {
                return '<a class="anurqa-search-result" href="' + (D.searchUrl || '/?s=') + encodeURIComponent(s.text) + '">' +
                    '<span class="anurqa-search-result__icon">' + s.icon + '</span>' +
                    '<span class="anurqa-search-result__text">' + esc(s.text) + '</span>' +
                    '<svg class="anurqa-search-result__arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M17 7H7M17 7v10"/></svg>' +
                    '</a>';
            }).join('');
            dropdown.hidden = false;
        }

        input.addEventListener('focus', function () { render(this.value); });
        input.addEventListener('input', function () { render(this.value); });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { dropdown.hidden = true; this.blur(); }
            if (e.key === 'Enter') {
                e.preventDefault();
                window.location.href = (D.searchUrl || '/?s=') + encodeURIComponent(this.value);
            }
        });
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.anurqa-search-inline')) dropdown.hidden = true;
        });
    }

    /* ═══════════════════════════════════════
       3) MOBILE DRAWER
       ═══════════════════════════════════════ */
    function initDrawer() {
        var hamburger = $('.anurqa-hamburger');
        var drawer = $('#anurqa-drawer');
        var closeBtn = $('.anurqa-drawer__close');
        var overlay = $('.anurqa-overlay');
        var nav = $('.anurqa-drawer__nav');
        var greeting = $('.anurqa-drawer__greeting');

        if (!hamburger || !drawer) return;

        if (greeting) {
            greeting.textContent = (D.isLoggedIn === '1' && D.userName) ? 'Hi ' + D.userName + '!' : 'Hi there!';
        }

        buildDrawerAccordion(nav);

        var focusableEls, firstF, lastF;

        function openDrawer() {
            drawer.classList.add('anurqa-drawer--open');
            overlay.classList.add('anurqa-overlay--active');
            overlay.setAttribute('aria-hidden', 'false');
            hamburger.setAttribute('aria-expanded', 'true');
            document.body.classList.add('anurqa-scroll-lock');
            focusableEls = $$('a[href], button, input, [tabindex]:not([tabindex="-1"])', drawer);
            firstF = focusableEls[0]; lastF = focusableEls[focusableEls.length - 1];
            requestAnimationFrame(function () { if (closeBtn) closeBtn.focus(); });
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

        document.addEventListener('keydown', function (e) {
            if (!drawer.classList.contains('anurqa-drawer--open')) return;
            if (e.key === 'Escape') { closeDrawer(); return; }
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstF) { e.preventDefault(); lastF.focus(); }
                } else {
                    if (document.activeElement === lastF) { e.preventDefault(); firstF.focus(); }
                }
            }
        });
    }

    /* ═══════════════════════════════════════
       4) DRAWER ACCORDION
       ═══════════════════════════════════════ */
    function buildDrawerAccordion(navEl) {
        if (!navEl) return;
        var menuItems = getMenuData();
        var html = '';

        menuItems.forEach(function (item, idx) {
            var allSubs = [];
            if (item.columns) {
                item.columns.forEach(function (col) {
                    // Add column heading as a sub-section
                    if (col.heading) {
                        allSubs.push({ label: col.heading.replace(/<[^>]*>/g, ''), url: col.url || '#', isHeading: true });
                    }
                    if (col.items) {
                        col.items.forEach(function (sub) { allSubs.push(sub); });
                    }
                    if (col.powers) {
                        col.powers.forEach(function (p) { allSubs.push({ label: p.label, url: p.url || '#' }); });
                    }
                });
            }

            if (allSubs.length === 0) {
                html += '<div class="anurqa-accordion"><a href="' + escAttr(item.url) + '" class="anurqa-accordion__trigger">' + esc(item.title) + '</a></div>';
            } else {
                var pid = 'anurqa-panel-' + idx;
                html += '<div class="anurqa-accordion">';
                html += '<button class="anurqa-accordion__trigger" aria-expanded="false" aria-controls="' + pid + '">';
                html += esc(item.title);
                html += '<svg class="anurqa-accordion__chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>';
                html += '</button>';
                html += '<div id="' + pid + '" class="anurqa-accordion__panel" role="region"><ul>';
                allSubs.forEach(function (sub) {
                    if (sub.isHeading) {
                        html += '<li style="font-weight:600;color:var(--anurqa-primary);font-size:13px;padding:10px 8px 4px;border:none;text-transform:uppercase;letter-spacing:0.5px">' + esc(sub.label) + '</li>';
                    } else {
                        html += '<li><a href="' + escAttr(sub.url) + '">' + esc(sub.label) + '</a></li>';
                    }
                });
                html += '</ul></div></div>';
            }
        });

        navEl.innerHTML = html;

        $$('.anurqa-accordion__trigger[aria-controls]', navEl).forEach(function (btn) {
            btn.addEventListener('click', function () {
                var expanded = this.getAttribute('aria-expanded') === 'true';
                var panel = document.getElementById(this.getAttribute('aria-controls'));
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
        $$('.anurqa-cart-badge').forEach(function (b) {
            if (count > 0) { b.textContent = count; b.setAttribute('data-count', count); b.hidden = false; }
            else { b.hidden = true; }
        });
    }

    /* ═══════════════════════════════════════
       6) MOBILE SEARCH
       ═══════════════════════════════════════ */
    function initMobileSearch() {
        var mobileHeader = $('.anurqa-header--mobile');
        var mobileSearch = $('.anurqa-mobile-search');
        if (!mobileHeader || !mobileSearch) return;

        var existing = $('.anurqa-mobile-search-btn', mobileHeader);
        if (!existing) {
            var btn = document.createElement('button');
            btn.className = 'anurqa-action-btn anurqa-mobile-search-btn';
            btn.setAttribute('aria-label', 'Search');
            btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>';
            var actions = $('.anurqa-mobile-actions', mobileHeader);
            if (actions) actions.prepend(btn);
            existing = btn;
        }

        var closeBtn = $('.anurqa-mobile-search__close');
        var input = $('input', mobileSearch);

        existing.addEventListener('click', function () {
            mobileSearch.classList.add('anurqa-mobile-search--active');
            document.body.classList.add('anurqa-scroll-lock');
            if (input) requestAnimationFrame(function () { input.focus(); });
        });
        if (closeBtn) closeBtn.addEventListener('click', function () {
            mobileSearch.classList.remove('anurqa-mobile-search--active');
            document.body.classList.remove('anurqa-scroll-lock');
        });
        if (input) input.addEventListener('keydown', function (e) {
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

    /* ═══════════════════════════════════════
       HELPERS
       ═══════════════════════════════════════ */
    function getMenuData() {
        if (D.menuJson) {
            try { var p = JSON.parse(D.menuJson); if (Array.isArray(p)) return p; } catch (e) {}
        }
        // Parse from server-rendered DOM
        var items = [];
        $$('.anurqa-nav__item').forEach(function (el) {
            var link = $('a.anurqa-nav__link', el);
            var type = el.getAttribute('data-type') || 'default';
            var item = { title: link ? link.textContent.trim() : '', url: link ? link.href : '#', type: type, columns: [] };

            if (type === 'store' || type === 'home') {
                items.push(item);
                return;
            }

            $$('.anurqa-mega__col', el).forEach(function (colEl) {
                var col = { heading: '', items: [], powers: [] };
                var t = $('.anurqa-mega__col-title', colEl);
                if (t) col.heading = t.textContent.trim();
                col.url = colEl.closest('a') ? colEl.closest('a').href : '#';

                $$('.anurqa-mega__items a', colEl).forEach(function (a) {
                    var lbl = $('.anurqa-mega__item-label', a);
                    col.items.push({ label: lbl ? lbl.textContent.trim() : a.textContent.trim(), url: a.href || '#' });
                });
                $$('.anurqa-mega__power-badge', colEl).forEach(function (a) {
                    col.powers.push({ label: a.textContent.trim(), url: a.href || '#' });
                });

                item.columns.push(col);
            });
            items.push(item);
        });
        return items;
    }

    function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    function escAttr(s) { return s.replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    /* ═══════════════════════════════════════
       INIT
       ═══════════════════════════════════════ */
    function init() {
        initMegaMenu();
        initInlineSearch();
        initDrawer();
        initCartBadge();
        initMobileSearch();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
