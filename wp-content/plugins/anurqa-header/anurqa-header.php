<?php
/**
 * Plugin Name: Anurqa Optical - Custom Header
 * Plugin URI: https://anurqaoptical.com
 * Description: Lenskart-style custom navigation header with mega menu, mobile drawer, and admin settings for logo upload.
 * Version: 1.0.0
 * Author: Anurqa Optical
 * License: GPL v2 or later
 * Text Domain: anurqa-header
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'ANURQA_HEADER_VERSION', '1.0.0' );
define( 'ANURQA_HEADER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ANURQA_HEADER_URL', plugin_dir_url( __FILE__ ) );

/* ─── Admin Settings ─── */
class Anurqa_Header_Settings {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
    }

    public function add_menu() {
        add_menu_page(
            'Anurqa Header Settings',
            'Anurqa Header',
            'manage_options',
            'anurqa-header',
            [ $this, 'settings_page' ],
            'dashicons-menu-alt3',
            59
        );
    }

    public function register_settings() {
        register_setting( 'anurqa_header_group', 'anurqa_header_logo' );
        register_setting( 'anurqa_header_group', 'anurqa_header_phone' );
        register_setting( 'anurqa_header_group', 'anurqa_header_login_url' );
        register_setting( 'anurqa_header_group', 'anurqa_header_cart_url' );
        register_setting( 'anurqa_header_group', 'anurqa_header_wishlist_url' );
        register_setting( 'anurqa_header_group', 'anurqa_header_hide_theme_header' );
        register_setting( 'anurqa_header_group', 'anurqa_header_sticky' );
        register_setting( 'anurqa_header_group', 'anurqa_header_primary_color' );
        register_setting( 'anurqa_header_group', 'anurqa_header_menu_data', [
            'type' => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default' => '',
        ]);
    }

    public function admin_scripts( $hook ) {
        if ( $hook !== 'toplevel_page_anurqa-header' ) return;
        wp_enqueue_media();
        wp_enqueue_style( 'anurqa-admin', ANURQA_HEADER_URL . 'assets/css/admin.css', [], ANURQA_HEADER_VERSION );
        wp_enqueue_script( 'anurqa-admin', ANURQA_HEADER_URL . 'assets/js/admin.js', [], ANURQA_HEADER_VERSION, true );
    }

    public function settings_page() {
        $logo          = get_option( 'anurqa_header_logo', '' );
        $phone         = get_option( 'anurqa_header_phone', '9999899998' );
        $login_url     = get_option( 'anurqa_header_login_url', '/my-account/' );
        $cart_url      = get_option( 'anurqa_header_cart_url', '/cart/' );
        $wishlist_url  = get_option( 'anurqa_header_wishlist_url', '/wishlist/' );
        $hide_theme    = get_option( 'anurqa_header_hide_theme_header', '1' );
        $sticky        = get_option( 'anurqa_header_sticky', '1' );
        $primary_color = get_option( 'anurqa_header_primary_color', '#000b3d' );
        ?>
        <div class="wrap anurqa-admin-wrap">
            <h1>Anurqa Header Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'anurqa_header_group' ); ?>

                <table class="form-table">
                    <!-- Logo Upload -->
                    <tr>
                        <th>Logo</th>
                        <td>
                            <div id="anurqa-logo-preview">
                                <?php if ( $logo ) : ?>
                                    <img src="<?php echo esc_url( $logo ); ?>" style="max-height:60px;">
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="anurqa_header_logo" id="anurqa_header_logo" value="<?php echo esc_attr( $logo ); ?>">
                            <button type="button" class="button" id="anurqa-upload-logo">Upload Logo</button>
                            <button type="button" class="button" id="anurqa-remove-logo" <?php echo $logo ? '' : 'style="display:none"'; ?>>Remove</button>
                            <p class="description">Recommended: PNG or SVG, max height 50px.</p>
                        </td>
                    </tr>

                    <!-- Primary Color -->
                    <tr>
                        <th>Primary Color</th>
                        <td>
                            <input type="color" name="anurqa_header_primary_color" value="<?php echo esc_attr( $primary_color ); ?>">
                            <code><?php echo esc_html( $primary_color ); ?></code>
                        </td>
                    </tr>

                    <!-- Phone -->
                    <tr>
                        <th>Phone Number</th>
                        <td><input type="text" name="anurqa_header_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text"></td>
                    </tr>

                    <!-- URLs -->
                    <tr>
                        <th>Login / My Account URL</th>
                        <td><input type="text" name="anurqa_header_login_url" value="<?php echo esc_attr( $login_url ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th>Cart URL</th>
                        <td><input type="text" name="anurqa_header_cart_url" value="<?php echo esc_attr( $cart_url ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th>Wishlist URL</th>
                        <td><input type="text" name="anurqa_header_wishlist_url" value="<?php echo esc_attr( $wishlist_url ); ?>" class="regular-text"></td>
                    </tr>

                    <!-- Toggles -->
                    <tr>
                        <th>Hide Theme Header</th>
                        <td>
                            <label>
                                <input type="checkbox" name="anurqa_header_hide_theme_header" value="1" <?php checked( $hide_theme, '1' ); ?>>
                                Hide the default theme header/nav
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>Sticky Header</th>
                        <td>
                            <label>
                                <input type="checkbox" name="anurqa_header_sticky" value="1" <?php checked( $sticky, '1' ); ?>>
                                Make header stick on scroll
                            </label>
                        </td>
                    </tr>
                </table>

                <hr>
                <h2>Menu Items</h2>
                <p class="description">Configure your mega-menu categories and their dropdown items below. Use the WordPress <strong>Appearance → Menus</strong> for dynamic menus, or configure JSON below for static menus.</p>
                <p><strong>Option A:</strong> Create a WordPress menu named <code>anurqa-header-menu</code> under Appearance → Menus (2-level: top = category, children = sub-items).</p>
                <p><strong>Option B:</strong> Paste custom JSON below (overrides WP menu if filled):</p>
                <textarea name="anurqa_header_menu_data" rows="12" class="large-text code" placeholder='[
  {
    "title": "Eyeglasses",
    "url": "/eyeglasses/",
    "columns": [
      {
        "heading": "MEN Eyeglasses",
        "image": "",
        "badge": "with FREE lenses",
        "items": [
          {"label": "John Jacobs | Owndays", "price": "Starts at ₹3100", "url": "/brand/john-jacobs/"},
          {"label": "Vincent Chase", "price": "Starts at ₹1400", "url": "/brand/vincent-chase/"}
        ]
      }
    ],
    "trending": [
      {"label": "eyeglasses", "icon": "👓", "url": "/eyeglasses/"},
      {"label": "sunglasses", "icon": "🕶", "url": "/sunglasses/"}
    ]
  }
]'><?php echo esc_textarea( get_option( 'anurqa_header_menu_data', '' ) ); ?></textarea>

                <?php submit_button( 'Save Settings' ); ?>
            </form>
        </div>
        <?php
    }
}
new Anurqa_Header_Settings();

/* ─── Frontend ─── */
class Anurqa_Header_Frontend {

    private $settings = [];

    public function __construct() {
        $this->settings = [
            'logo'         => get_option( 'anurqa_header_logo', '' ),
            'phone'        => get_option( 'anurqa_header_phone', '9999899998' ),
            'login_url'    => get_option( 'anurqa_header_login_url', '/my-account/' ),
            'cart_url'     => get_option( 'anurqa_header_cart_url', '/cart/' ),
            'wishlist_url' => get_option( 'anurqa_header_wishlist_url', '/wishlist/' ),
            'hide_theme'   => get_option( 'anurqa_header_hide_theme_header', '1' ),
            'sticky'       => get_option( 'anurqa_header_sticky', '1' ),
            'primary'      => get_option( 'anurqa_header_primary_color', '#000b3d' ),
            'menu_json'    => get_option( 'anurqa_header_menu_data', '' ),
        ];

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
        add_action( 'wp_body_open', [ $this, 'render_header' ], 1 );

        if ( $this->settings['hide_theme'] === '1' ) {
            add_action( 'wp_head', [ $this, 'hide_theme_header_css' ], 999 );
        }
    }

    public function enqueue() {
        wp_enqueue_style( 'anurqa-header', ANURQA_HEADER_URL . 'assets/css/header.css', [], ANURQA_HEADER_VERSION );
        wp_enqueue_script( 'anurqa-header', ANURQA_HEADER_URL . 'assets/js/header.js', [], ANURQA_HEADER_VERSION, true );

        // Pass settings to JS
        wp_localize_script( 'anurqa-header', 'anurqaHeaderData', [
            'menuJson'    => $this->settings['menu_json'],
            'phone'       => $this->settings['phone'],
            'loginUrl'    => $this->settings['login_url'],
            'cartUrl'     => $this->settings['cart_url'],
            'wishlistUrl' => $this->settings['wishlist_url'],
            'isLoggedIn'  => is_user_logged_in() ? '1' : '0',
            'userName'    => is_user_logged_in() ? wp_get_current_user()->display_name : '',
            'cartCount'   => $this->get_cart_count(),
            'searchUrl'   => home_url( '/?s=' ),
        ]);

        // CSS custom properties
        $primary = sanitize_hex_color( $this->settings['primary'] );
        wp_add_inline_style( 'anurqa-header', ":root{--anurqa-primary:{$primary};}" );
    }

    private function get_cart_count() {
        if ( function_exists( 'WC' ) && WC()->cart ) {
            return WC()->cart->get_cart_contents_count();
        }
        return 0;
    }

    public function hide_theme_header_css() {
        echo '<style>
            header:not(.anurqa-header),
            .site-header:not(.anurqa-header),
            #masthead:not(.anurqa-header),
            .elementor-location-header:not(.anurqa-header-wrap) {
                display: none !important;
            }
        </style>';
    }

    public function render_header() {
        $s = $this->settings;
        $sticky_class = $s['sticky'] === '1' ? ' anurqa-header--sticky' : '';
        $logo_html = $s['logo']
            ? '<img src="' . esc_url( $s['logo'] ) . '" alt="Anurqa Optical" class="anurqa-logo-img">'
            : '<span class="anurqa-logo-text">Anurqa Optical</span>';

        // Build menu from JSON or WP Menu
        $menu_items = $this->get_menu_items();
        ?>

        <!-- Anurqa Header Plugin -->
        <div class="anurqa-header-wrap<?php echo esc_attr( $sticky_class ); ?>">

            <!-- ══ DESKTOP HEADER ══ -->
            <header class="anurqa-header anurqa-header--desktop" role="banner">
                <div class="anurqa-container">

                    <!-- Logo -->
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="anurqa-logo" aria-label="Anurqa Optical Home">
                        <?php echo $logo_html; ?>
                    </a>

                    <!-- Main Nav -->
                    <nav class="anurqa-nav" role="navigation" aria-label="Main Navigation">
                        <ul class="anurqa-nav__list" role="menubar">
                            <?php foreach ( $menu_items as $i => $item ) : ?>
                            <li class="anurqa-nav__item" role="none"
                                aria-haspopup="<?php echo ! empty( $item['columns'] ) ? 'true' : 'false'; ?>"
                                aria-expanded="false">
                                <a href="<?php echo esc_url( $item['url'] ); ?>"
                                   class="anurqa-nav__link"
                                   role="menuitem"
                                   data-index="<?php echo $i; ?>">
                                    <?php echo esc_html( $item['title'] ); ?>
                                </a>
                                <?php if ( ! empty( $item['columns'] ) ) : ?>
                                <!-- Mega Menu -->
                                <div class="anurqa-mega" role="menu" aria-label="<?php echo esc_attr( $item['title'] ); ?> submenu">
                                    <div class="anurqa-mega__inner">
                                        <div class="anurqa-mega__columns">
                                            <?php foreach ( $item['columns'] as $col ) : ?>
                                            <div class="anurqa-mega__col">
                                                <div class="anurqa-mega__col-header">
                                                    <?php if ( ! empty( $col['image'] ) ) : ?>
                                                        <img src="<?php echo esc_url( $col['image'] ); ?>" alt="" class="anurqa-mega__col-img">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong class="anurqa-mega__col-title"><?php echo esc_html( $col['heading'] ); ?></strong>
                                                        <?php if ( ! empty( $col['badge'] ) ) : ?>
                                                            <span class="anurqa-mega__badge"><?php echo esc_html( $col['badge'] ); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php if ( ! empty( $col['items'] ) ) : ?>
                                                <ul class="anurqa-mega__items">
                                                    <?php foreach ( $col['items'] as $sub ) : ?>
                                                    <li>
                                                        <a href="<?php echo esc_url( $sub['url'] ); ?>">
                                                            <span class="anurqa-mega__item-label"><?php echo esc_html( $sub['label'] ); ?></span>
                                                            <span class="anurqa-mega__item-price"><?php echo esc_html( $sub['price'] ?? '' ); ?></span>
                                                            <svg class="anurqa-mega__arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                                                        </a>
                                                    </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                <?php endif; ?>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if ( ! empty( $item['trending'] ) ) : ?>
                                        <div class="anurqa-mega__trending">
                                            <span class="anurqa-mega__trending-title">TRENDING AT ANURQA</span>
                                            <ul>
                                                <?php foreach ( $item['trending'] as $t ) : ?>
                                                <li>
                                                    <a href="<?php echo esc_url( $t['url'] ); ?>">
                                                        <span class="anurqa-mega__trending-icon"><?php echo esc_html( $t['icon'] ?? '' ); ?></span>
                                                        <span><?php echo esc_html( $t['label'] ); ?></span>
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                                                    </a>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>

                    <!-- Right Actions -->
                    <div class="anurqa-actions">
                        <!-- Search -->
                        <div class="anurqa-search" role="search">
                            <button class="anurqa-search__toggle" aria-label="Search" aria-expanded="false">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                            </button>
                            <div class="anurqa-search__dropdown" role="combobox" aria-expanded="false" hidden>
                                <input type="search" class="anurqa-search__input" placeholder="What are you looking for?" autocomplete="off" aria-label="Search products">
                                <div class="anurqa-search__results"></div>
                            </div>
                        </div>

                        <!-- Wishlist -->
                        <a href="<?php echo esc_url( $s['wishlist_url'] ); ?>" class="anurqa-action-btn" aria-label="Wishlist">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        </a>

                        <!-- Cart -->
                        <a href="<?php echo esc_url( $s['cart_url'] ); ?>" class="anurqa-action-btn anurqa-cart-btn" aria-label="Cart">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            <span class="anurqa-cart-badge" data-count="0" hidden>0</span>
                        </a>

                        <!-- Account -->
                        <a href="<?php echo esc_url( $s['login_url'] ); ?>" class="anurqa-action-btn" aria-label="My Account">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </a>
                    </div>
                </div>
            </header>

            <!-- ══ MOBILE HEADER ══ -->
            <header class="anurqa-header anurqa-header--mobile" role="banner">
                <button class="anurqa-hamburger" aria-label="Open menu" aria-expanded="false" aria-controls="anurqa-drawer">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="anurqa-logo" aria-label="Anurqa Optical Home">
                    <?php echo $logo_html; ?>
                </a>
                <div class="anurqa-mobile-actions">
                    <a href="<?php echo esc_url( $s['cart_url'] ); ?>" class="anurqa-action-btn anurqa-cart-btn" aria-label="Cart">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <span class="anurqa-cart-badge" data-count="0" hidden>0</span>
                    </a>
                    <a href="<?php echo esc_url( $s['login_url'] ); ?>" class="anurqa-action-btn" aria-label="My Account">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </a>
                </div>
            </header>

            <!-- ══ MOBILE DRAWER ══ -->
            <div class="anurqa-overlay" aria-hidden="true"></div>
            <aside id="anurqa-drawer" class="anurqa-drawer" role="dialog" aria-modal="true" aria-label="Navigation menu" hidden>
                <div class="anurqa-drawer__header">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="anurqa-logo">
                        <?php echo $logo_html; ?>
                    </a>
                    <button class="anurqa-drawer__close" aria-label="Close menu">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <!-- User Card -->
                <div class="anurqa-drawer__user">
                    <div class="anurqa-drawer__avatar">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--anurqa-primary)" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div class="anurqa-drawer__user-info">
                        <strong class="anurqa-drawer__greeting">Hi Specsy!</strong>
                        <p>Login or Signup to track your orders and get access to exclusive deals.</p>
                    </div>
                    <a href="<?php echo esc_url( $s['login_url'] ); ?>" class="anurqa-drawer__login-btn">Login/Signup</a>
                </div>

                <!-- Phone -->
                <div class="anurqa-drawer__phone">
                    <span>Talk to us</span>
                    <a href="tel:<?php echo esc_attr( $s['phone'] ); ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <?php echo esc_html( $s['phone'] ); ?>
                    </a>
                </div>

                <!-- Accordion Menu -->
                <nav class="anurqa-drawer__nav" role="navigation" aria-label="Mobile Navigation">
                    <!-- Populated by JS from same menu data -->
                </nav>
            </aside>

            <!-- ══ SEARCH OVERLAY (mobile) ══ -->
            <div class="anurqa-mobile-search" hidden>
                <div class="anurqa-mobile-search__bar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    <input type="search" placeholder="What are you looking for?" aria-label="Search products">
                    <button class="anurqa-mobile-search__close" aria-label="Close search">Cancel</button>
                </div>
                <div class="anurqa-mobile-search__results"></div>
            </div>

        </div>
        <!-- /Anurqa Header Plugin -->
        <?php
    }

    private function get_menu_items() {
        // Priority 1: JSON from admin
        $json = $this->settings['menu_json'];
        if ( ! empty( $json ) ) {
            $parsed = json_decode( $json, true );
            if ( is_array( $parsed ) ) return $parsed;
        }

        // Priority 2: WordPress menu named 'anurqa-header-menu'
        $locations = get_nav_menu_locations();
        $menu_obj  = false;

        // Try registered location first
        if ( isset( $locations['anurqa-header'] ) ) {
            $menu_obj = wp_get_nav_menu_object( $locations['anurqa-header'] );
        }
        // Try by slug
        if ( ! $menu_obj ) {
            $menu_obj = wp_get_nav_menu_object( 'anurqa-header-menu' );
        }

        if ( $menu_obj ) {
            $items = wp_get_nav_menu_items( $menu_obj->term_id );
            if ( $items ) return $this->parse_wp_menu( $items );
        }

        // Priority 3: Default demo data
        return $this->default_menu();
    }

    private function parse_wp_menu( $items ) {
        $top    = [];
        $children = [];

        foreach ( $items as $item ) {
            if ( $item->menu_item_parent == 0 ) {
                $top[ $item->ID ] = [
                    'title'   => $item->title,
                    'url'     => $item->url,
                    'columns' => [],
                    'trending' => [],
                ];
            } else {
                $children[ $item->menu_item_parent ][] = [
                    'label' => $item->title,
                    'url'   => $item->url,
                    'price' => $item->description ?? '',
                ];
            }
        }

        foreach ( $children as $parent_id => $subs ) {
            if ( isset( $top[ $parent_id ] ) ) {
                $top[ $parent_id ]['columns'][] = [
                    'heading' => $top[ $parent_id ]['title'],
                    'image'   => '',
                    'badge'   => '',
                    'items'   => $subs,
                ];
            }
        }

        return array_values( $top );
    }

    private function default_menu() {
        return [
            [
                'title' => 'Eyeglasses',
                'url'   => '/eyeglasses/',
                'columns' => [
                    [
                        'heading' => 'MEN Eyeglasses',
                        'image'   => '',
                        'badge'   => 'with FREE lenses',
                        'items'   => [
                            [ 'label' => 'John Jacobs | Owndays | Le Petit', 'price' => 'Starts at ₹3100', 'url' => '#' ],
                            [ 'label' => 'Vincent Chase | Lenskart Air', 'price' => 'Starts at ₹1400', 'url' => '#' ],
                            [ 'label' => 'Hustlr', 'price' => 'Starts at ₹500', 'url' => '#' ],
                            [ 'label' => 'Essentials', 'price' => 'Starts at ₹500', 'url' => '#' ],
                            [ 'label' => 'All Brands', 'price' => 'Starts at ₹1400', 'url' => '#' ],
                        ],
                    ],
                    [
                        'heading' => 'WOMEN Eyeglasses',
                        'image'   => '',
                        'badge'   => 'with FREE lenses',
                        'items'   => [
                            [ 'label' => 'John Jacobs | Owndays | Le Petit', 'price' => 'Starts at ₹3100', 'url' => '#' ],
                            [ 'label' => 'Vincent Chase | Lenskart Air', 'price' => 'Starts at ₹1400', 'url' => '#' ],
                            [ 'label' => 'Hustlr', 'price' => 'Starts at ₹500', 'url' => '#' ],
                            [ 'label' => 'Essentials', 'price' => 'Starts at ₹500', 'url' => '#' ],
                            [ 'label' => 'All Brands', 'price' => 'Starts at ₹1400', 'url' => '#' ],
                        ],
                    ],
                ],
                'trending' => [
                    [ 'label' => 'eyeglasses', 'icon' => '👓', 'url' => '#' ],
                    [ 'label' => 'sunglasses', 'icon' => '🕶', 'url' => '#' ],
                    [ 'label' => 'contact lens', 'icon' => '👁', 'url' => '#' ],
                    [ 'label' => 'gold max membership', 'icon' => '🏅', 'url' => '#' ],
                    [ 'label' => 'zero power glasses', 'icon' => '💎', 'url' => '#' ],
                ],
            ],
            [
                'title' => 'Sunglasses',
                'url'   => '/sunglasses/',
                'columns' => [
                    [
                        'heading' => 'Shop Sunglasses',
                        'image'   => '',
                        'badge'   => '',
                        'items'   => [
                            [ 'label' => 'All Sunglasses', 'price' => '', 'url' => '#' ],
                            [ 'label' => 'Power Sunglasses', 'price' => '', 'url' => '#' ],
                            [ 'label' => 'Vincent Chase', 'price' => '', 'url' => '#' ],
                            [ 'label' => 'Polarized Sunglasses', 'price' => '', 'url' => '#' ],
                            [ 'label' => 'Aviator', 'price' => '', 'url' => '#' ],
                        ],
                    ],
                ],
                'trending' => [],
            ],
            [
                'title' => 'Contacts',
                'url'   => '/contacts/',
                'columns' => [
                    [
                        'heading' => 'Shop Contact Lens',
                        'image'   => '',
                        'badge'   => '',
                        'items'   => [
                            [ 'label' => 'Colored Contact Lens', 'price' => '', 'url' => '#' ],
                            [ 'label' => 'Yearly', 'price' => '', 'url' => '#' ],
                            [ 'label' => 'Daily', 'price' => '', 'url' => '#' ],
                            [ 'label' => 'Monthly', 'price' => '', 'url' => '#' ],
                            [ 'label' => 'Day & Night', 'price' => '', 'url' => '#' ],
                        ],
                    ],
                ],
                'trending' => [],
            ],
            [ 'title' => 'Special Power', 'url' => '/special-power/', 'columns' => [], 'trending' => [] ],
            [ 'title' => 'Stores', 'url' => '/stores/', 'columns' => [], 'trending' => [] ],
            [ 'title' => 'Try @ Home', 'url' => '/try-at-home/', 'columns' => [], 'trending' => [] ],
        ];
    }
}

// Only load frontend on non-admin pages
if ( ! is_admin() ) {
    add_action( 'init', function() {
        new Anurqa_Header_Frontend();
    });
}

/* ─── Register menu location ─── */
add_action( 'init', function() {
    register_nav_menus([
        'anurqa-header' => 'Anurqa Header Menu',
    ]);
});

/* ─── WooCommerce cart fragment support ─── */
add_filter( 'woocommerce_add_to_cart_fragments', function( $fragments ) {
    $count = WC()->cart->get_cart_contents_count();
    $fragments['.anurqa-cart-badge'] = '<span class="anurqa-cart-badge" data-count="' . $count . '"' . ( $count > 0 ? '' : ' hidden' ) . '>' . $count . '</span>';
    return $fragments;
});
