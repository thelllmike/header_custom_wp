<?php
/**
 * Plugin Name: Anura Optical - Custom Header
 * Plugin URI: https://anuraoptical.com
 * Description: Lenskart-style custom navigation header with mega menu, mobile drawer, and admin settings for logo upload. Built for Sri Lanka.
 * Version: 2.0.7
 * Author: Anura Optical
 * License: GPL v2 or later
 * Text Domain: anurqa-header
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'ANURQA_HEADER_VERSION', '2.0.7' );
define( 'ANURQA_HEADER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ANURQA_HEADER_URL', plugin_dir_url( __FILE__ ) );

/* ═══════════════════════════════════════
   ADMIN SETTINGS
   ═══════════════════════════════════════ */
class Anurqa_Header_Settings {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
    }

    public function add_menu() {
        add_menu_page(
            'Anura Header Settings',
            'Anura Header',
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
        register_setting( 'anurqa_header_group', 'anurqa_header_store_image' );
        register_setting( 'anurqa_header_group', 'anurqa_header_home_image' );
        register_setting( 'anurqa_header_group', 'anurqa_header_menu_data', [
            'type' => 'string',
            'sanitize_callback' => function( $val ) {
                return wp_unslash( $val ); // preserve JSON
            },
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
        $phone         = get_option( 'anurqa_header_phone', '+94 77 123 4567' );
        $login_url     = get_option( 'anurqa_header_login_url', '/my-account/' );
        $cart_url      = get_option( 'anurqa_header_cart_url', '/cart/' );
        $wishlist_url  = get_option( 'anurqa_header_wishlist_url', '/wishlist/' );
        $hide_theme    = get_option( 'anurqa_header_hide_theme_header', '1' );
        $sticky        = get_option( 'anurqa_header_sticky', '1' );
        $primary_color = get_option( 'anurqa_header_primary_color', '#000b3d' );
        $store_image   = get_option( 'anurqa_header_store_image', '' );
        $home_image    = get_option( 'anurqa_header_home_image', '' );
        ?>
        <div class="wrap anurqa-admin-wrap">
            <h1>Anura Header Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'anurqa_header_group' ); ?>

                <table class="form-table">
                    <!-- Logo Upload -->
                    <tr>
                        <th>Logo</th>
                        <td>
                            <div class="anurqa-img-preview" id="anurqa-logo-preview">
                                <?php if ( $logo ) : ?>
                                    <img src="<?php echo esc_url( $logo ); ?>" style="max-height:60px;">
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="anurqa_header_logo" id="anurqa_header_logo" value="<?php echo esc_attr( $logo ); ?>">
                            <button type="button" class="button anurqa-upload-btn" data-target="anurqa_header_logo" data-preview="anurqa-logo-preview">Upload Logo</button>
                            <button type="button" class="button anurqa-remove-btn" data-target="anurqa_header_logo" data-preview="anurqa-logo-preview" <?php echo $logo ? '' : 'style="display:none"'; ?>>Remove</button>
                            <p class="description">Recommended: PNG or SVG, max height 50px.</p>
                        </td>
                    </tr>

                    <!-- Store Image -->
                    <tr>
                        <th>Store Page Image (Mega Menu)</th>
                        <td>
                            <div class="anurqa-img-preview" id="anurqa-store-preview">
                                <?php if ( $store_image ) : ?>
                                    <img src="<?php echo esc_url( $store_image ); ?>" style="max-height:100px;">
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="anurqa_header_store_image" id="anurqa_header_store_image" value="<?php echo esc_attr( $store_image ); ?>">
                            <button type="button" class="button anurqa-upload-btn" data-target="anurqa_header_store_image" data-preview="anurqa-store-preview">Upload Image</button>
                            <button type="button" class="button anurqa-remove-btn" data-target="anurqa_header_store_image" data-preview="anurqa-store-preview" <?php echo $store_image ? '' : 'style="display:none"'; ?>>Remove</button>
                            <p class="description">Image shown in the "Stores" mega menu dropdown. ~600x400px recommended.</p>
                        </td>
                    </tr>

                    <!-- Try@Home Image -->
                    <tr>
                        <th>Try @ Home Image (Mega Menu)</th>
                        <td>
                            <div class="anurqa-img-preview" id="anurqa-home-preview">
                                <?php if ( $home_image ) : ?>
                                    <img src="<?php echo esc_url( $home_image ); ?>" style="max-height:100px;">
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="anurqa_header_home_image" id="anurqa_header_home_image" value="<?php echo esc_attr( $home_image ); ?>">
                            <button type="button" class="button anurqa-upload-btn" data-target="anurqa_header_home_image" data-preview="anurqa-home-preview">Upload Image</button>
                            <button type="button" class="button anurqa-remove-btn" data-target="anurqa_header_home_image" data-preview="anurqa-home-preview" <?php echo $home_image ? '' : 'style="display:none"'; ?>>Remove</button>
                            <p class="description">Image shown in the "Try @ Home" mega menu dropdown. ~600x400px recommended.</p>
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
                <h2>Menu Items (JSON)</h2>
                <p class="description">Configure your mega-menu categories below. Each category can have <code>columns</code> with items (each item can have a <code>thumb</code> image URL). Special types: <code>"type":"store"</code> and <code>"type":"home"</code> for Stores/Try@Home layouts.</p>
                <p><strong>Or:</strong> Create a WordPress menu named <code>anurqa-header-menu</code> under Appearance → Menus. JSON overrides WP menu if filled.</p>
                <textarea name="anurqa_header_menu_data" rows="20" class="large-text code" placeholder="Paste JSON here..."><?php echo esc_textarea( get_option( 'anurqa_header_menu_data', '' ) ); ?></textarea>
                <p class="description">Leave empty to use built-in Sri Lanka demo data. <a href="#" id="anurqa-load-demo">Load demo JSON</a></p>

                <?php submit_button( 'Save Settings' ); ?>
            </form>
        </div>
        <?php
    }
}
new Anurqa_Header_Settings();

/* ═══════════════════════════════════════
   FRONTEND
   ═══════════════════════════════════════ */
class Anurqa_Header_Frontend {

    private $settings = [];

    public function __construct() {
        $this->settings = [
            'logo'         => get_option( 'anurqa_header_logo', '' ),
            'phone'        => get_option( 'anurqa_header_phone', '+94 77 123 4567' ),
            'login_url'    => get_option( 'anurqa_header_login_url', '/my-account/' ),
            'cart_url'     => get_option( 'anurqa_header_cart_url', '/cart/' ),
            'wishlist_url' => get_option( 'anurqa_header_wishlist_url', '/wishlist/' ),
            'hide_theme'   => get_option( 'anurqa_header_hide_theme_header', '1' ),
            'sticky'       => get_option( 'anurqa_header_sticky', '1' ),
            'primary'      => get_option( 'anurqa_header_primary_color', '#000b3d' ),
            'menu_json'    => get_option( 'anurqa_header_menu_data', '' ),
            'store_image'  => get_option( 'anurqa_header_store_image', '' ),
            'home_image'   => get_option( 'anurqa_header_home_image', '' ),
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

        wp_localize_script( 'anurqa-header', 'anurqaHeaderData', [
            'menuJson'    => $this->settings['menu_json'],
            'phone'       => $this->settings['phone'],
            'loginUrl'    => $this->settings['login_url'],
            'cartUrl'     => $this->settings['cart_url'],
            'wishlistUrl' => $this->settings['wishlist_url'],
            'storeImage'  => $this->settings['store_image'],
            'homeImage'   => $this->settings['home_image'],
            'isLoggedIn'  => is_user_logged_in() ? '1' : '0',
            'userName'    => is_user_logged_in() ? wp_get_current_user()->display_name : '',
            'cartCount'   => $this->get_cart_count(),
            'searchUrl'   => home_url( '/?s=' ),
        ]);

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
            ? '<img src="' . esc_url( $s['logo'] ) . '" alt="Anura Optical" class="anurqa-logo-img">'
            : '<span class="anurqa-logo-text">Anura Optical</span>';

        $menu_items = $this->get_menu_items();
        ?>

        <!-- Anura Header Plugin v2 -->
        <div class="anurqa-header-wrap<?php echo esc_attr( $sticky_class ); ?>">

            <!-- ══ DESKTOP HEADER ══ -->
            <header class="anurqa-header anurqa-header--desktop" role="banner">
                <div class="anurqa-container">

                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="anurqa-logo" aria-label="Anura Optical Home">
                        <?php echo $logo_html; ?>
                    </a>

                    <nav class="anurqa-nav" role="navigation" aria-label="Main Navigation">
                        <ul class="anurqa-nav__list" role="menubar">
                            <?php foreach ( $menu_items as $i => $item ) :
                                $has_mega = ! empty( $item['columns'] ) || ! empty( $item['type'] );
                                $type = $item['type'] ?? 'default';
                            ?>
                            <li class="anurqa-nav__item<?php echo $has_mega ? ' anurqa-nav__item--has-mega' : ''; ?>"
                                role="none"
                                aria-haspopup="<?php echo $has_mega ? 'true' : 'false'; ?>"
                                aria-expanded="false"
                                data-type="<?php echo esc_attr( $type ); ?>">
                                <a href="<?php echo esc_url( $item['url'] ); ?>"
                                   class="anurqa-nav__link"
                                   role="menuitem"
                                   data-index="<?php echo $i; ?>">
                                    <?php echo esc_html( $item['title'] ); ?>
                                </a>

                                <?php if ( $type === 'store' ) : ?>
                                <!-- STORES Mega Menu -->
                                <div class="anurqa-mega anurqa-mega--store" role="menu" aria-label="Stores">
                                    <div class="anurqa-mega__inner anurqa-mega--split">
                                        <div class="anurqa-mega--split-img">
                                            <?php if ( $s['store_image'] ) : ?>
                                                <img src="<?php echo esc_url( $s['store_image'] ); ?>" alt="Our Store">
                                            <?php else : ?>
                                                <div class="anurqa-mega__placeholder">Upload store image in settings</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="anurqa-mega--split-content">
                                            <h3><?php echo esc_html( $item['heading'] ?? 'Find your nearest Anura Optical Store' ); ?></h3>
                                            <?php if ( ! empty( $item['locations'] ) ) : ?>
                                            <div class="anurqa-mega__locations">
                                                <?php foreach ( $item['locations'] as $loc ) : ?>
                                                <a href="<?php echo esc_url( $loc['url'] ?? '#' ); ?>" class="anurqa-mega__location">
                                                    <?php if ( ! empty( $loc['icon'] ) ) : ?>
                                                        <img src="<?php echo esc_url( $loc['icon'] ); ?>" alt="" class="anurqa-mega__loc-icon">
                                                    <?php else : ?>
                                                        <span class="anurqa-mega__loc-emoji"><?php echo esc_html( $loc['emoji'] ?? '📍' ); ?></span>
                                                    <?php endif; ?>
                                                    <span><?php echo esc_html( $loc['name'] ); ?></span>
                                                </a>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ( ! empty( $item['cta_label'] ) ) : ?>
                                            <a href="<?php echo esc_url( $item['cta_url'] ?? $item['url'] ); ?>" class="anurqa-mega__cta">
                                                <?php echo esc_html( $item['cta_label'] ); ?>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <?php elseif ( $type === 'home' ) : ?>
                                <!-- TRY@HOME Mega Menu -->
                                <div class="anurqa-mega anurqa-mega--home" role="menu" aria-label="Try at Home">
                                    <div class="anurqa-mega__inner anurqa-mega--split">
                                        <div class="anurqa-mega--split-img">
                                            <?php if ( $s['home_image'] ) : ?>
                                                <img src="<?php echo esc_url( $s['home_image'] ); ?>" alt="Try at Home">
                                            <?php else : ?>
                                                <div class="anurqa-mega__placeholder">Upload image in settings</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="anurqa-mega--split-content">
                                            <h3><?php echo esc_html( $item['heading'] ?? 'Get your eyes checked at home' ); ?></h3>
                                            <?php if ( ! empty( $item['features'] ) ) : ?>
                                            <div class="anurqa-mega__features">
                                                <?php foreach ( $item['features'] as $feat ) : ?>
                                                <div class="anurqa-mega__feature">
                                                    <span class="anurqa-mega__feature-icon"><?php echo esc_html( $feat['icon'] ?? '✓' ); ?></span>
                                                    <span><?php echo esc_html( $feat['text'] ); ?></span>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ( ! empty( $item['cta_label'] ) ) : ?>
                                            <a href="<?php echo esc_url( $item['cta_url'] ?? $item['url'] ); ?>" class="anurqa-mega__cta">
                                                <?php echo esc_html( $item['cta_label'] ); ?>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <?php elseif ( ! empty( $item['columns'] ) ) : ?>
                                <!-- STANDARD Mega Menu (Eyeglasses / Sunglasses / Contacts / Special Power) -->
                                <div class="anurqa-mega anurqa-mega--cols-<?php echo count( $item['columns'] ); ?>" role="menu" aria-label="<?php echo esc_attr( $item['title'] ); ?> submenu">
                                    <div class="anurqa-mega__inner">
                                        <div class="anurqa-mega__columns">
                                            <?php foreach ( $item['columns'] as $col ) : ?>
                                            <div class="anurqa-mega__col">
                                                <!-- Column Header Card -->
                                                <a href="<?php echo esc_url( $col['url'] ?? '#' ); ?>" class="anurqa-mega__col-header">
                                                    <div class="anurqa-mega__col-text">
                                                        <strong class="anurqa-mega__col-title"><?php echo wp_kses_post( $col['heading'] ); ?></strong>
                                                        <?php if ( ! empty( $col['badge'] ) ) : ?>
                                                            <span class="anurqa-mega__badge"><?php echo esc_html( $col['badge'] ); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ( ! empty( $col['image'] ) ) : ?>
                                                        <img src="<?php echo esc_url( $col['image'] ); ?>" alt="" class="anurqa-mega__col-img">
                                                    <?php endif; ?>
                                                </a>

                                                <?php if ( ! empty( $col['items'] ) ) : ?>
                                                <ul class="anurqa-mega__items">
                                                    <?php foreach ( $col['items'] as $sub ) : ?>
                                                    <li>
                                                        <a href="<?php echo esc_url( $sub['url'] ); ?>">
                                                            <?php if ( ! empty( $sub['thumb'] ) ) : ?>
                                                                <img src="<?php echo esc_url( $sub['thumb'] ); ?>" alt="" class="anurqa-mega__item-thumb">
                                                            <?php endif; ?>
                                                            <div class="anurqa-mega__item-info">
                                                                <span class="anurqa-mega__item-label"><?php echo esc_html( $sub['label'] ); ?></span>
                                                                <?php if ( ! empty( $sub['price'] ) ) : ?>
                                                                    <span class="anurqa-mega__item-price"><?php echo esc_html( $sub['price'] ); ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <svg class="anurqa-mega__arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                                                        </a>
                                                    </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                                <?php endif; ?>

                                                <?php // Reading power badges
                                                if ( ! empty( $col['powers'] ) ) : ?>
                                                <div class="anurqa-mega__powers">
                                                    <?php foreach ( $col['powers'] as $pwr ) : ?>
                                                    <a href="<?php echo esc_url( $pwr['url'] ?? '#' ); ?>" class="anurqa-mega__power-badge">
                                                        <?php echo esc_html( $pwr['label'] ); ?>
                                                    </a>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>

                    <!-- Right Actions -->
                    <div class="anurqa-actions">
                        <div class="anurqa-search-inline" role="search">
                            <svg class="anurqa-search-inline__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                            <input type="text" class="anurqa-search-inline__input" placeholder="What are you looking for?" aria-label="Search products" autocomplete="off">
                            <div class="anurqa-search-inline__dropdown" hidden></div>
                        </div>

                        <a href="<?php echo esc_url( $s['wishlist_url'] ); ?>" class="anurqa-action-btn" aria-label="Wishlist">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        </a>

                        <a href="<?php echo esc_url( $s['cart_url'] ); ?>" class="anurqa-action-btn anurqa-cart-btn" aria-label="Cart">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            <span class="anurqa-cart-badge" data-count="0" hidden>0</span>
                        </a>

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
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="anurqa-logo" aria-label="Anura Optical Home">
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

                <div class="anurqa-drawer__user">
                    <div class="anurqa-drawer__avatar">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--anurqa-primary)" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div class="anurqa-drawer__user-info">
                        <strong class="anurqa-drawer__greeting">Hi there!</strong>
                        <p>Login or Signup to track your orders and get access to exclusive deals.</p>
                    </div>
                    <a href="<?php echo esc_url( $s['login_url'] ); ?>" class="anurqa-drawer__login-btn">Login/Signup</a>
                </div>

                <div class="anurqa-drawer__phone">
                    <span>Talk to us</span>
                    <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $s['phone'] ) ); ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <?php echo esc_html( $s['phone'] ); ?>
                    </a>
                </div>

                <nav class="anurqa-drawer__nav" role="navigation" aria-label="Mobile Navigation">
                    <!-- Populated by JS -->
                </nav>
            </aside>

            <!-- ══ MOBILE SEARCH ══ -->
            <div class="anurqa-mobile-search" hidden>
                <div class="anurqa-mobile-search__bar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    <input type="search" placeholder="What are you looking for?" aria-label="Search products">
                    <button class="anurqa-mobile-search__close" aria-label="Close search">Cancel</button>
                </div>
                <div class="anurqa-mobile-search__results"></div>
            </div>

        </div>
        <!-- /Anura Header Plugin v2 -->
        <?php
    }

    private function get_menu_items() {
        $json = $this->settings['menu_json'];
        if ( ! empty( $json ) ) {
            $parsed = json_decode( $json, true );
            if ( is_array( $parsed ) ) return $parsed;
        }

        $locations = get_nav_menu_locations();
        $menu_obj  = false;
        if ( isset( $locations['anurqa-header'] ) ) {
            $menu_obj = wp_get_nav_menu_object( $locations['anurqa-header'] );
        }
        if ( ! $menu_obj ) {
            $menu_obj = wp_get_nav_menu_object( 'anurqa-header-menu' );
        }
        if ( $menu_obj ) {
            $items = wp_get_nav_menu_items( $menu_obj->term_id );
            if ( $items ) return $this->parse_wp_menu( $items );
        }

        return $this->default_menu();
    }

    private function parse_wp_menu( $items ) {
        $top = [];
        $children = [];
        foreach ( $items as $item ) {
            if ( $item->menu_item_parent == 0 ) {
                $top[ $item->ID ] = [
                    'title'   => $item->title,
                    'url'     => $item->url,
                    'columns' => [],
                ];
            } else {
                $children[ $item->menu_item_parent ][] = [
                    'label' => $item->title,
                    'url'   => $item->url,
                    'price' => $item->description ?? '',
                    'thumb' => '',
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

    /* ─── Default Sri Lanka menu data ─── */
    private function default_menu() {
        return [
            // ── EYEGLASSES ──
            [
                'title' => 'Eyeglasses',
                'url'   => '/eyeglasses/',
                'columns' => [
                    [
                        'heading' => '<b>MEN</b> Eyeglasses',
                        'image'   => '',
                        'url'     => '/eyeglasses/men/',
                        'badge'   => 'with FREE lenses',
                        'items'   => [
                            [ 'label' => 'Premium Frames', 'price' => 'Starts at LKR 8,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Budget Frames', 'price' => 'Starts at LKR 3,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Titanium', 'price' => 'Starts at LKR 12,000', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Essentials', 'price' => 'Starts at LKR 2,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'All Brands', 'price' => 'Starts at LKR 2,500', 'url' => '#', 'thumb' => '' ],
                        ],
                    ],
                    [
                        'heading' => '<b>WOMEN</b> Eyeglasses',
                        'image'   => '',
                        'url'     => '/eyeglasses/women/',
                        'badge'   => 'with FREE lenses',
                        'items'   => [
                            [ 'label' => 'Premium Frames', 'price' => 'Starts at LKR 8,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Budget Frames', 'price' => 'Starts at LKR 3,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Cat Eye', 'price' => 'Starts at LKR 4,000', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Essentials', 'price' => 'Starts at LKR 2,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'All Brands', 'price' => 'Starts at LKR 2,500', 'url' => '#', 'thumb' => '' ],
                        ],
                    ],
                    [
                        'heading' => '<b>KIDS</b> Eyeglasses',
                        'image'   => '',
                        'url'     => '/eyeglasses/kids/',
                        'badge'   => 'with FREE lenses',
                        'items'   => [
                            [ 'label' => 'Juniors | 5 to 8 years', 'price' => 'Starts at LKR 2,000', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Tweens | 8 to 12 years', 'price' => 'Starts at LKR 2,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Teens | 12 to 17 years', 'price' => 'Starts at LKR 3,500', 'url' => '#', 'thumb' => '' ],
                        ],
                    ],
                ],
            ],
            // ── SUNGLASSES ──
            [
                'title' => 'Sunglasses',
                'url'   => '/sunglasses/',
                'columns' => [
                    [
                        'heading' => '<b>MEN</b> Sunglasses',
                        'image'   => '',
                        'url'     => '/sunglasses/men/',
                        'badge'   => 'Polarized with UV Protection',
                        'items'   => [
                            [ 'label' => 'Premium Brands', 'price' => 'Starts at LKR 8,000', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Aviator', 'price' => 'Starts at LKR 3,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Sports', 'price' => 'Starts at LKR 2,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Essentials', 'price' => 'Starts at LKR 1,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'All Brands', 'price' => 'Starts at LKR 1,500', 'url' => '#', 'thumb' => '' ],
                        ],
                    ],
                    [
                        'heading' => '<b>WOMEN</b> Sunglasses',
                        'image'   => '',
                        'url'     => '/sunglasses/women/',
                        'badge'   => 'Polarized with UV Protection',
                        'items'   => [
                            [ 'label' => 'Premium Brands', 'price' => 'Starts at LKR 8,000', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Cat Eye', 'price' => 'Starts at LKR 3,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Oversized', 'price' => 'Starts at LKR 2,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Essentials', 'price' => 'Starts at LKR 1,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'All Brands', 'price' => 'Starts at LKR 1,500', 'url' => '#', 'thumb' => '' ],
                        ],
                    ],
                    [
                        'heading' => '<b>KIDS</b> Sunglasses',
                        'image'   => '',
                        'url'     => '/sunglasses/kids/',
                        'badge'   => 'Polarized with UV Protection',
                        'items'   => [
                            [ 'label' => 'Juniors | 5 to 8 years', 'price' => 'Starts at LKR 1,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Tweens | 8 to 12 years', 'price' => 'Starts at LKR 1,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Teens | 12 to 17 years', 'price' => 'Starts at LKR 2,500', 'url' => '#', 'thumb' => '' ],
                        ],
                    ],
                ],
            ],
            // ── CONTACTS ──
            [
                'title' => 'Contacts',
                'url'   => '/contacts/',
                'columns' => [
                    [
                        'heading' => '<b>CLEAR</b> Contacts',
                        'image'   => '',
                        'url'     => '/contacts/clear/',
                        'badge'   => '10% OFF with Gold',
                        'items'   => [
                            [ 'label' => 'Distance power (-ve)', 'price' => 'Starts at LKR 1,200', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Toric/Cylindrical', 'price' => 'Starts at LKR 1,800', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Multi-Focal', 'price' => 'Starts at LKR 8,000', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'All Powers', 'price' => 'Starts at LKR 1,200', 'url' => '#', 'thumb' => '' ],
                        ],
                    ],
                    [
                        'heading' => '<b>COLOR</b> Contacts',
                        'image'   => '',
                        'url'     => '/contacts/color/',
                        'badge'   => '10% OFF with Gold',
                        'items'   => [
                            [ 'label' => 'Zero Power', 'price' => 'Starts at LKR 900', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'With Power', 'price' => 'Starts at LKR 1,000', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Color Combos', 'price' => 'Buy 4 at the price of 3!', 'url' => '#', 'thumb' => '' ],
                        ],
                    ],
                    [
                        'heading' => '<b>Solution</b> & Accessories',
                        'image'   => '',
                        'url'     => '/contacts/accessories/',
                        'badge'   => '10% OFF with Gold',
                        'items'   => [
                            [ 'label' => 'Solution', 'price' => 'Starts at LKR 750', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Accessories', 'price' => 'Starts at LKR 500', 'url' => '#', 'thumb' => '' ],
                        ],
                    ],
                ],
            ],
            // ── SPECIAL POWER ──
            [
                'title' => 'Special Power',
                'url'   => '/special-power/',
                'columns' => [
                    [
                        'heading' => '<b>PRE-FIT</b> ZERO POWER',
                        'image'   => '',
                        'url'     => '/special-power/zero-power/',
                        'badge'   => '',
                        'items'   => [
                            [ 'label' => 'Computer Glasses', 'price' => 'Starts at LKR 2,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Blue Light Blocking', 'price' => 'Starts at LKR 1,500', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'All Brands', 'price' => 'Starts at LKR 1,500', 'url' => '#', 'thumb' => '' ],
                        ],
                    ],
                    [
                        'heading' => '<b>PROGRESSIVE</b> Lenses',
                        'image'   => '',
                        'url'     => '/special-power/progressive/',
                        'badge'   => '',
                        'items'   => [
                            [ 'label' => 'Men', 'price' => 'Starts at LKR 12,000', 'url' => '#', 'thumb' => '' ],
                            [ 'label' => 'Women', 'price' => 'Starts at LKR 12,000', 'url' => '#', 'thumb' => '' ],
                        ],
                    ],
                    [
                        'heading' => '<b>READING</b>',
                        'image'   => '',
                        'url'     => '/special-power/reading/',
                        'badge'   => '',
                        'items'   => [],
                        'powers'  => [
                            [ 'label' => '+1.0', 'url' => '#' ],
                            [ 'label' => '+1.25', 'url' => '#' ],
                            [ 'label' => '+1.5', 'url' => '#' ],
                            [ 'label' => '+1.75', 'url' => '#' ],
                            [ 'label' => '+2.0', 'url' => '#' ],
                            [ 'label' => '+2.25', 'url' => '#' ],
                            [ 'label' => '+2.5', 'url' => '#' ],
                            [ 'label' => 'View All', 'url' => '#' ],
                        ],
                    ],
                ],
            ],
            // ── STORES ──
            [
                'title'     => 'Stores',
                'url'       => '/stores/',
                'type'      => 'store',
                'heading'   => 'Find our Anura Optical Store',
                'locations' => [
                    [ 'name' => 'Pettah', 'emoji' => '🏛', 'url' => '/stores/pettah/' ],
                ],
                'cta_label' => 'Locate a Store',
                'cta_url'   => '/stores/',
            ],
            // ── TRY @ HOME ──
            [
                'title'     => 'Try @ Home',
                'url'       => '/try-at-home/',
                'type'      => 'home',
                'heading'   => 'Get your eyes checked at home',
                'features'  => [
                    [ 'icon' => '👁', 'text' => 'Professional Eye Checkup' ],
                    [ 'icon' => '🔬', 'text' => 'Latest Eye Test Equipment' ],
                    [ 'icon' => '👓', 'text' => 'Try 50+ frames at home' ],
                ],
                'cta_label' => 'Book appointment',
                'cta_url'   => '/try-at-home/book/',
            ],
        ];
    }
}

if ( ! is_admin() ) {
    add_action( 'init', function() {
        new Anurqa_Header_Frontend();
    });
}

add_action( 'init', function() {
    register_nav_menus([
        'anurqa-header' => 'Anura Header Menu',
    ]);
});

add_filter( 'woocommerce_add_to_cart_fragments', function( $fragments ) {
    $count = WC()->cart->get_cart_contents_count();
    $fragments['.anurqa-cart-badge'] = '<span class="anurqa-cart-badge" data-count="' . $count . '"' . ( $count > 0 ? '' : ' hidden' ) . '>' . $count . '</span>';
    return $fragments;
});
