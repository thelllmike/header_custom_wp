<?php
/**
 * Plugin Name: Anura Product View
 * Description: Lenskart-style single product page for WooCommerce — image gallery, color/size selectors, delivery checker, trust badges, reviews, virtual try-on, wishlist.
 * Version:     1.6.0
 * Author:      Anura Optical
 * Text Domain: anura-product-view
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'APV_VERSION', '1.6.0' );
define( 'APV_PATH',    plugin_dir_path( __FILE__ ) );
define( 'APV_URL',     plugin_dir_url( __FILE__ ) );

class Anura_Product_View {

    public function __construct() {
        add_action( 'wp_enqueue_scripts',   [ $this, 'enqueue' ] );

        // Force override the entire single-product page template
        add_filter( 'template_include',     [ $this, 'force_template' ], 999 );

        // AJAX for reviews pagination
        add_action( 'wp_ajax_apv_load_reviews',        [ $this, 'ajax_load_reviews' ] );
        add_action( 'wp_ajax_nopriv_apv_load_reviews', [ $this, 'ajax_load_reviews' ] );

        // Admin settings
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );

        // Product video meta box
        add_action( 'add_meta_boxes', [ $this, 'add_video_meta_box' ] );
        add_action( 'save_post_product', [ $this, 'save_video_meta' ] );

        // Admin scripts for video upload
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue' ] );
    }

    /* ─── Frontend Assets ─── */
    public function enqueue(): void {
        if ( ! is_product() ) return;

        wp_enqueue_style( 'apv-style', APV_URL . 'assets/css/product-view.css', [], APV_VERSION );
        wp_enqueue_script( 'apv-script', APV_URL . 'assets/js/product-view.js', [ 'jquery' ], APV_VERSION, true );

        // Determine try-on URL — prefer Virtual Try-On plugin's Vercel app URL
        $tryon_url = get_option( 'vtryon_app_url', '' );
        if ( ! $tryon_url ) {
            // Fallback: local virtual_tryon folder
            if ( file_exists( WP_PLUGIN_DIR . '/virtual_tryon/index.html' ) ) {
                $tryon_url = plugins_url( 'virtual_tryon/' );
            } else {
                $tryon_url = '/virtual-tryon/';
            }
        }

        wp_localize_script( 'apv-script', 'apvData', [
            'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'apv_nonce' ),
            'tryOnUrl' => $tryon_url,
        ]);
    }

    /* ─── Admin Assets ─── */
    public function admin_enqueue( $hook ): void {
        $screen = get_current_screen();
        $is_product = $screen && $screen->post_type === 'product' && in_array( $hook, [ 'post.php', 'post-new.php' ] );
        $is_settings = $hook === 'settings_page_apv-settings';

        if ( $is_product || $is_settings ) {
            wp_enqueue_media();
            wp_enqueue_script( 'apv-admin', APV_URL . 'assets/js/admin-video.js', [ 'jquery' ], APV_VERSION, true );
            wp_localize_script( 'apv-admin', 'apvAdmin', [
                'maxImageSize' => 5 * 1024 * 1024,
                'maxVideoSize' => 25 * 1024 * 1024,
                'allowedVideo' => [ 'mp4', 'webm', 'mov' ],
            ]);
        }
    }

    /* ─── Force template override at highest priority ─── */
    public function force_template( $template ) {
        if ( is_product() ) {
            $custom = APV_PATH . 'templates/single-product.php';
            if ( file_exists( $custom ) ) {
                return $custom;
            }
        }
        return $template;
    }

    /* ─── Video meta box ─── */
    public function add_video_meta_box(): void {
        add_meta_box(
            'apv_product_video',
            'Product Video',
            [ $this, 'render_video_meta_box' ],
            'product',
            'side',
            'default'
        );
    }

    public function render_video_meta_box( $post ): void {
        wp_nonce_field( 'apv_video_meta', 'apv_video_nonce' );
        $video_url = get_post_meta( $post->ID, '_apv_product_video', true );
        ?>
        <div id="apv-video-meta">
            <p>
                <label for="apv-video-url"><strong>Video URL</strong></label><br>
                <input type="url" id="apv-video-url" name="_apv_product_video" value="<?php echo esc_url( $video_url ); ?>" class="widefat" placeholder="Upload or paste video URL">
            </p>
            <p>
                <button type="button" class="button" id="apv-upload-video">Upload Video</button>
                <?php if ( $video_url ) : ?>
                <button type="button" class="button" id="apv-remove-video" style="color:#d63638;">Remove</button>
                <?php endif; ?>
            </p>
            <p class="description">Accepted: MP4, WebM, MOV. Max size: 25 MB.</p>
            <?php if ( $video_url ) : ?>
            <video src="<?php echo esc_url( $video_url ); ?>" controls style="width:100%; margin-top:8px; border-radius:4px;"></video>
            <?php endif; ?>
        </div>
        <?php
    }

    public function save_video_meta( $post_id ): void {
        if ( ! isset( $_POST['apv_video_nonce'] ) || ! wp_verify_nonce( $_POST['apv_video_nonce'], 'apv_video_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $video = isset( $_POST['_apv_product_video'] ) ? esc_url_raw( $_POST['_apv_product_video'] ) : '';
        update_post_meta( $post_id, '_apv_product_video', $video );
    }

    /* ─── AJAX: Load reviews ─── */
    public function ajax_load_reviews(): void {
        check_ajax_referer( 'apv_nonce', 'nonce' );

        $product_id = absint( $_POST['product_id'] ?? 0 );
        $page       = absint( $_POST['page'] ?? 1 );
        $per_page   = 5;

        $comments = get_comments([
            'post_id'  => $product_id,
            'status'   => 'approve',
            'type'     => 'review',
            'number'   => $per_page,
            'offset'   => ( $page - 1 ) * $per_page,
            'orderby'  => 'comment_date_gmt',
            'order'    => 'DESC',
        ]);

        $total = get_comments([
            'post_id' => $product_id,
            'status'  => 'approve',
            'type'    => 'review',
            'count'   => true,
        ]);

        $html = '';
        foreach ( $comments as $c ) {
            $rating = (int) get_comment_meta( $c->comment_ID, 'rating', true );
            $date   = date( 'j M Y', strtotime( $c->comment_date ) );
            $name   = esc_html( $c->comment_author );
            $text   = esc_html( $c->comment_content );
            $avatar = get_avatar_url( $c->comment_author_email, [ 'size' => 40 ] );

            $html .= '<div class="apv-review">';
            $html .= '<div class="apv-review__header">';
            $html .= '<img src="' . esc_url( $avatar ) . '" alt="" class="apv-review__avatar">';
            $html .= '<div class="apv-review__meta">';
            $html .= '<span class="apv-review__author">' . $name . '</span>';
            $html .= '<span class="apv-review__date">' . esc_html( $date ) . '</span>';
            $html .= '</div>';
            $html .= '<span class="apv-review__rating">' . $rating . ' <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></span>';
            $html .= '</div>';
            $html .= '<p class="apv-review__text">' . $text . '</p>';
            $html .= '</div>';
        }

        wp_send_json_success([
            'html'     => $html,
            'has_more' => ( $page * $per_page ) < $total,
        ]);
    }

    /* ─── Admin settings ─── */
    public function admin_menu(): void {
        add_options_page( 'Anura Product View', 'Anura Product View', 'manage_options', 'apv-settings', [ $this, 'settings_page' ] );
    }

    public function register_settings(): void {
        register_setting( 'apv_group', 'apv_settings', [
            'sanitize_callback' => [ $this, 'sanitize_settings' ],
        ]);
    }

    public function sanitize_settings( $input ): array {
        $clean = [];
        $clean['cta_text']        = sanitize_text_field( $input['cta_text'] ?? 'Select Lenses' );
        $clean['cta_color']       = sanitize_hex_color( $input['cta_color'] ?? '#1b3c6b' );
        $clean['delivery_text']   = sanitize_text_field( $input['delivery_text'] ?? 'Express delivery might be applicable' );
        $clean['trust_1_title']   = sanitize_text_field( $input['trust_1_title'] ?? 'No Questions Asked Returns' );
        $clean['trust_2_title']   = sanitize_text_field( $input['trust_2_title'] ?? 'Easy 14 day Exchange' );
        $clean['trust_3_title']   = sanitize_text_field( $input['trust_3_title'] ?? '365 days Warranty' );
        $clean['show_try_on']     = ! empty( $input['show_try_on'] ) ? '1' : '0';
        $clean['show_nearby']     = ! empty( $input['show_nearby'] ) ? '1' : '0';
        $clean['show_similar']    = ! empty( $input['show_similar'] ) ? '1' : '0';
        $clean['show_how_to_buy'] = ! empty( $input['show_how_to_buy'] ) ? '1' : '0';
        $clean['show_styling']    = ! empty( $input['show_styling'] ) ? '1' : '0';

        // How to Buy cards (up to 5)
        for ( $i = 1; $i <= 5; $i++ ) {
            $clean["htb_{$i}_title"]    = sanitize_text_field( $input["htb_{$i}_title"] ?? '' );
            $clean["htb_{$i}_subtitle"] = sanitize_text_field( $input["htb_{$i}_subtitle"] ?? '' );
            $clean["htb_{$i}_image"]    = esc_url_raw( $input["htb_{$i}_image"] ?? '' );
        }

        return $clean;
    }

    public function settings_page(): void {
        $s = wp_parse_args( get_option( 'apv_settings', [] ), $this->defaults() );
        ?>
        <div class="wrap">
            <h1>Anura Product View</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'apv_group' ); ?>
                <table class="form-table">
                    <tr><th>CTA Button Text</th><td><input type="text" name="apv_settings[cta_text]" value="<?php echo esc_attr( $s['cta_text'] ); ?>" class="regular-text"></td></tr>
                    <tr><th>CTA Button Color</th><td><input type="color" name="apv_settings[cta_color]" value="<?php echo esc_attr( $s['cta_color'] ); ?>"></td></tr>
                    <tr><th>Delivery Note</th><td><input type="text" name="apv_settings[delivery_text]" value="<?php echo esc_attr( $s['delivery_text'] ); ?>" class="large-text"></td></tr>
                    <tr><th>Trust Badge 1</th><td><input type="text" name="apv_settings[trust_1_title]" value="<?php echo esc_attr( $s['trust_1_title'] ); ?>" class="regular-text"></td></tr>
                    <tr><th>Trust Badge 2</th><td><input type="text" name="apv_settings[trust_2_title]" value="<?php echo esc_attr( $s['trust_2_title'] ); ?>" class="regular-text"></td></tr>
                    <tr><th>Trust Badge 3</th><td><input type="text" name="apv_settings[trust_3_title]" value="<?php echo esc_attr( $s['trust_3_title'] ); ?>" class="regular-text"></td></tr>
                    <tr><th>Show Sections</th><td>
                        <label><input type="checkbox" name="apv_settings[show_try_on]" value="1" <?php checked( $s['show_try_on'], '1' ); ?>> Try On button</label><br>
                        <label><input type="checkbox" name="apv_settings[show_nearby]" value="1" <?php checked( $s['show_nearby'], '1' ); ?>> Nearby Stores button</label><br>
                        <label><input type="checkbox" name="apv_settings[show_similar]" value="1" <?php checked( $s['show_similar'], '1' ); ?>> Similar button</label><br>
                        <label><input type="checkbox" name="apv_settings[show_how_to_buy]" value="1" <?php checked( $s['show_how_to_buy'], '1' ); ?>> How to Buy section</label><br>
                        <label><input type="checkbox" name="apv_settings[show_styling]" value="1" <?php checked( $s['show_styling'], '1' ); ?>> Related Products section</label>
                    </td></tr>
                </table>

                <h2>How to Buy Cards</h2>
                <p class="description">Upload images or video thumbnails for "How to Buy Your Glasses" cards (up to 5). Leave empty to hide a card.</p>
                <table class="form-table">
                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                    <tr>
                        <th>Card <?php echo $i; ?></th>
                        <td>
                            <p><label>Title: <input type="text" name="apv_settings[htb_<?php echo $i; ?>_title]" value="<?php echo esc_attr( $s["htb_{$i}_title"] ?? '' ); ?>" class="regular-text"></label></p>
                            <p><label>Subtitle: <input type="text" name="apv_settings[htb_<?php echo $i; ?>_subtitle]" value="<?php echo esc_attr( $s["htb_{$i}_subtitle"] ?? '' ); ?>" class="regular-text"></label></p>
                            <p>
                                <label>Image/Video URL:</label><br>
                                <input type="url" name="apv_settings[htb_<?php echo $i; ?>_image]" value="<?php echo esc_url( $s["htb_{$i}_image"] ?? '' ); ?>" class="regular-text apv-htb-url" id="apv-htb-url-<?php echo $i; ?>">
                                <button type="button" class="button apv-htb-upload" data-target="apv-htb-url-<?php echo $i; ?>">Upload</button>
                            </p>
                            <?php if ( ! empty( $s["htb_{$i}_image"] ) ) : ?>
                            <p><img src="<?php echo esc_url( $s["htb_{$i}_image"] ); ?>" style="max-width:200px;max-height:120px;border-radius:8px;"></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endfor; ?>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public static function defaults(): array {
        return [
            'cta_text'        => 'Select Lenses',
            'cta_color'       => '#1b3c6b',
            'delivery_text'   => 'Express delivery might be applicable',
            'trust_1_title'   => 'No Questions Asked Returns',
            'trust_2_title'   => 'Easy 14 day Exchange',
            'trust_3_title'   => '365 days Warranty',
            'show_try_on'     => '1',
            'show_nearby'     => '1',
            'show_similar'    => '1',
            'show_how_to_buy' => '1',
            'show_styling'    => '1',
            'htb_1_title'     => 'Select the right frame',
            'htb_1_subtitle'  => 'Choose the right frame',
            'htb_1_image'     => '',
            'htb_2_title'     => 'Find your frame size',
            'htb_2_subtitle'  => 'Find your frame size',
            'htb_2_image'     => '',
            'htb_3_title'     => 'Lens & power coverage',
            'htb_3_subtitle'  => 'Lens coverage details',
            'htb_3_image'     => '',
            'htb_4_title'     => 'Return policy',
            'htb_4_subtitle'  => 'Return policy',
            'htb_4_image'     => '',
            'htb_5_title'     => 'Gold membership',
            'htb_5_subtitle'  => 'Gold membership',
            'htb_5_image'     => '',
        ];
    }
}

new Anura_Product_View();

/* ─── Helper: Get product gallery data ─── */
function apv_get_gallery_images( $product ) {
    $images = [];
    $main_id = $product->get_image_id();
    if ( $main_id ) {
        $images[] = [
            'full'  => wp_get_attachment_image_url( $main_id, 'large' ),
            'thumb' => wp_get_attachment_image_url( $main_id, 'thumbnail' ),
        ];
    }
    foreach ( $product->get_gallery_image_ids() as $id ) {
        $images[] = [
            'full'  => wp_get_attachment_image_url( $id, 'large' ),
            'thumb' => wp_get_attachment_image_url( $id, 'thumbnail' ),
        ];
    }
    return $images;
}

/* ─── Helper: Get review stats ─── */
function apv_get_review_stats( $product_id ) {
    $comments = get_comments([
        'post_id' => $product_id,
        'status'  => 'approve',
        'type'    => 'review',
    ]);

    $total = count( $comments );
    $stars = [ 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 ];
    $sum   = 0;
    $photos = [];

    foreach ( $comments as $c ) {
        $r = (int) get_comment_meta( $c->comment_ID, 'rating', true );
        if ( $r >= 1 && $r <= 5 ) {
            $stars[ $r ]++;
            $sum += $r;
        }
        $attachments = get_comment_meta( $c->comment_ID, 'apv_review_photos', true );
        if ( $attachments && is_array( $attachments ) ) {
            foreach ( $attachments as $url ) {
                $photos[] = $url;
            }
        }
    }

    $avg = $total > 0 ? round( $sum / $total, 1 ) : 0;

    return [
        'total'  => $total,
        'avg'    => $avg,
        'stars'  => $stars,
        'photos' => $photos,
    ];
}
