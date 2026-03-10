<?php
/**
 * Anura Product View — Full Single Product Page Template
 * Overrides the entire single-product.php via template_include filter
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header( 'shop' );

while ( have_posts() ) :
    the_post();

    global $product;
    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        $product = wc_get_product( get_the_ID() );
    }
    if ( ! $product ) continue;

    $settings = wp_parse_args( get_option( 'apv_settings', [] ), Anura_Product_View::defaults() );
    $images   = apv_get_gallery_images( $product );
    $stats    = apv_get_review_stats( $product->get_id() );

    // Product attributes for color/size
    $colors = $product->get_attribute( 'pa_color' ) ?: $product->get_attribute( 'color' );
    $sizes  = $product->get_attribute( 'pa_size' ) ?: $product->get_attribute( 'size' );
    $color_list = $colors ? array_map( 'trim', explode( ',', $colors ) ) : [];
    $size_list  = $sizes ? array_map( 'trim', explode( ',', $sizes ) ) : [];

    // Stock
    $stock_qty = $product->get_stock_quantity();
    $low_stock = $stock_qty !== null && $stock_qty > 0 && $stock_qty <= 5;

    // Sale
    $on_sale    = $product->is_on_sale();
    $reg_price  = $product->get_regular_price();
    $sale_price = $product->get_sale_price();
    $discount   = ( $reg_price && $sale_price && $reg_price > 0 ) ? round( ( ( $reg_price - $sale_price ) / $reg_price ) * 100 ) : 0;

    // Main product image URL for virtual try-on
    $main_image_url = ! empty( $images ) ? $images[0]['full'] : '';

    // Virtual Try-On plugin integration: get transparent PNG and app URL
    $tryon_png_url = get_post_meta( $product->get_id(), '_tryon_png_url', true );
    $tryon_app_url = get_option( 'vtryon_app_url', '' );

    // Video (product meta)
    $product_video = get_post_meta( $product->get_id(), '_apv_product_video', true );
    ?>

    <div class="apv-product" id="apv-product-<?php echo esc_attr( $product->get_id() ); ?>">

        <!-- ════════════════════════════════════════════
             TOP SECTION: Gallery + Info (2 columns)
             ════════════════════════════════════════════ -->
        <div class="apv-product__top">

            <!-- LEFT: Image Gallery -->
            <div class="apv-gallery">
                <div class="apv-gallery__main">
                    <?php if ( $on_sale && $discount > 0 ) : ?>
                        <span class="apv-gallery__badge"><?php echo $discount; ?>% OFF</span>
                    <?php endif; ?>

                    <!-- Wishlist heart -->
                    <button class="apv-wishlist-btn" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" aria-label="Add to wishlist">
                        <svg class="apv-wishlist-btn__icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                    </button>

                    <?php if ( ! empty( $images ) ) : ?>
                        <img src="<?php echo esc_url( $images[0]['full'] ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" class="apv-gallery__image" id="apv-main-image">
                    <?php else : ?>
                        <img src="<?php echo esc_url( wc_placeholder_img_src( 'large' ) ); ?>" alt="Placeholder" class="apv-gallery__image" id="apv-main-image">
                    <?php endif; ?>

                    <!-- Nav arrows -->
                    <?php if ( count( $images ) > 1 ) : ?>
                    <button class="apv-gallery__arrow apv-gallery__arrow--prev" aria-label="Previous image">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <button class="apv-gallery__arrow apv-gallery__arrow--next" aria-label="Next image">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Thumbnail strip -->
                <?php if ( count( $images ) > 1 || $product_video ) : ?>
                <div class="apv-gallery__thumbs">
                    <?php foreach ( $images as $i => $img ) : ?>
                    <button class="apv-gallery__thumb <?php echo $i === 0 ? 'apv-gallery__thumb--active' : ''; ?>" data-full="<?php echo esc_url( $img['full'] ); ?>" data-index="<?php echo $i; ?>" data-type="image">
                        <img src="<?php echo esc_url( $img['thumb'] ); ?>" alt="">
                    </button>
                    <?php endforeach; ?>

                    <?php if ( $product_video ) : ?>
                    <button class="apv-gallery__thumb apv-gallery__thumb--video" data-video="<?php echo esc_url( $product_video ); ?>" data-index="<?php echo count( $images ); ?>" data-type="video">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Action buttons -->
                <div class="apv-gallery__actions">
                    <?php if ( $settings['show_nearby'] === '1' ) : ?>
                    <a href="<?php echo esc_url( home_url( '/stores/' ) ); ?>" class="apv-action-pill">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path d="M9 22V12h6v10"/></svg>
                        Nearby Stores
                    </a>
                    <?php endif; ?>

                    <?php if ( $settings['show_try_on'] === '1' && $tryon_png_url ) : ?>
                    <button class="apv-action-pill apv-action-pill--primary" id="apv-tryon-btn" data-image="<?php echo esc_url( $tryon_png_url ); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M20.188 10.934c.388.472.582.707.582 1.066s-.194.594-.582 1.066C18.768 14.79 15.636 18 12 18c-3.636 0-6.768-3.21-8.188-4.934C3.424 12.594 3.23 12.36 3.23 12s.194-.594.582-1.066C5.232 9.21 8.364 6 12 6c3.636 0 6.768 3.21 8.188 4.934z"/></svg>
                        Try on
                    </button>
                    <?php endif; ?>

                    <?php if ( $settings['show_similar'] === '1' ) : ?>
                    <button class="apv-action-pill" id="apv-similar-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                        Similar
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT: Product Info -->
            <div class="apv-info">
                <!-- Breadcrumb -->
                <nav class="apv-breadcrumb">
                    <?php woocommerce_breadcrumb([ 'delimiter' => ' <span class="apv-breadcrumb__sep">›</span> ', 'wrap_before' => '', 'wrap_after' => '' ]); ?>
                </nav>

                <h1 class="apv-info__title"><?php echo esc_html( $product->get_name() ); ?></h1>

                <!-- Rating summary (inline) -->
                <?php if ( $stats['total'] > 0 ) : ?>
                <div class="apv-info__rating-inline">
                    <span class="apv-info__rating-badge"><?php echo esc_html( $stats['avg'] ); ?> <svg width="12" height="12" viewBox="0 0 24 24" fill="#fff"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></span>
                    <a href="#apv-reviews-section" class="apv-info__rating-count">(<?php echo esc_html( $stats['total'] ); ?> customer reviews)</a>
                </div>
                <?php endif; ?>

                <!-- Price -->
                <div class="apv-info__price">
                    <?php echo $product->get_price_html(); ?>
                </div>

                <!-- Short description -->
                <?php if ( $product->get_short_description() ) : ?>
                    <div class="apv-info__desc"><?php echo wp_kses_post( $product->get_short_description() ); ?></div>
                <?php endif; ?>

                <!-- Frame Color (custom swatches — replaces WC dropdown) -->
                <?php if ( ! empty( $color_list ) ) : ?>
                <div class="apv-info__section">
                    <h3 class="apv-info__label">Frame Color <span class="apv-info__selected-color"></span></h3>
                    <div class="apv-color-swatches">
                        <?php foreach ( $color_list as $idx => $color ) : ?>
                        <button class="apv-color-swatch <?php echo $idx === 0 ? 'apv-color-swatch--active' : ''; ?>" title="<?php echo esc_attr( $color ); ?>" data-color="<?php echo esc_attr( $color ); ?>">
                            <span class="apv-color-swatch__circle" style="background: <?php echo esc_attr( $color ); ?>;"></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php if ( $low_stock ) : ?>
                        <span class="apv-info__stock-low">Few Left</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Frame Size (custom buttons — replaces WC dropdown) -->
                <?php if ( ! empty( $size_list ) ) : ?>
                <div class="apv-info__section">
                    <h3 class="apv-info__label">Frame Size <span class="apv-info__selected-size"></span></h3>
                    <div class="apv-size-options">
                        <?php foreach ( $size_list as $idx => $size ) : ?>
                        <button class="apv-size-btn <?php echo $idx === 0 ? 'apv-size-btn--active' : ''; ?>" data-size="<?php echo esc_attr( $size ); ?>"><?php echo esc_html( $size ); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- WooCommerce Add to Cart (variable/simple) — hidden, synced with swatches -->
                <div class="apv-info__section apv-info__add-to-cart" style="display:none !important;">
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>

                <!-- Select Lenses CTA -->
                <div class="apv-info__section">
                    <?php
                    $product_cats = wp_get_post_terms( $product->get_id(), 'product_cat', [ 'fields' => 'slugs' ] );
                    $is_sunglasses = is_array( $product_cats ) && ( in_array( 'sunglasses', $product_cats ) || in_array( 'sunglass', $product_cats ) );
                    $optical_group = $is_sunglasses ? 'sunglasses' : 'eyeglasses';

                    // Build the WooCommerce add-to-cart URL
                    $add_to_cart_url = $product->add_to_cart_url();

                    // Determine category page URL from optical-shop-ui plugin (for post-cart redirect)
                    $category_url = '';
                    if ( function_exists( 'osui_get_shapes' ) ) {
                        $shapes = osui_get_shapes( $optical_group );
                        if ( ! empty( $shapes ) && ! empty( $shapes[0]['url'] ) ) {
                            $category_url = $shapes[0]['url'];
                        }
                    }
                    ?>
                    <button type="button" class="apv-select-lenses-btn" id="apv-select-lenses-btn"
                        data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
                        data-product-type="<?php echo esc_attr( $product->get_type() ); ?>"
                        data-add-to-cart-url="<?php echo esc_url( $add_to_cart_url ); ?>"
                        data-category-url="<?php echo esc_url( $category_url ); ?>"
                        style="background: <?php echo esc_attr( $settings['cta_color'] ); ?>;">
                        <?php echo esc_html( $settings['cta_text'] ); ?>
                    </button>
                </div>

                <!-- Delivery Details -->
                <div class="apv-info__section">
                    <h3 class="apv-info__label apv-info__label--large">Delivery Details</h3>
                    <div class="apv-delivery">
                        <div class="apv-delivery__input-wrap">
                            <input type="text" class="apv-delivery__input" id="apv-pincode" placeholder="Enter pincode" maxlength="10">
                            <button class="apv-delivery__check" id="apv-pincode-btn">Check</button>
                        </div>
                        <div class="apv-delivery__note" id="apv-delivery-note">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                            <div>
                                <strong>Check delivery details</strong>
                                <p><?php echo esc_html( $settings['delivery_text'] ); ?></p>
                            </div>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2e8b57" stroke-width="2" class="apv-delivery__gps"><circle cx="12" cy="12" r="3"/><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        </div>
                    </div>
                </div>

                <!-- We Assure You -->
                <div class="apv-info__section">
                    <h3 class="apv-info__label apv-info__label--large">We Assure you</h3>
                    <div class="apv-trust">
                        <div class="apv-trust__item">
                            <div class="apv-trust__icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2e8b57" stroke-width="1.5"><path d="M3 10h4l3-7 4 14 3-7h4"/></svg>
                            </div>
                            <span class="apv-trust__title"><?php echo esc_html( $settings['trust_1_title'] ); ?></span>
                            <a href="#" class="apv-trust__link">Learn More &#9656;</a>
                        </div>
                        <div class="apv-trust__item">
                            <div class="apv-trust__icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2e8b57" stroke-width="1.5"><path d="M23 4l-6 6-3-3M17 14v4a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h4"/></svg>
                            </div>
                            <span class="apv-trust__title"><?php echo esc_html( $settings['trust_2_title'] ); ?></span>
                            <a href="#" class="apv-trust__link">Learn More &#9656;</a>
                        </div>
                        <div class="apv-trust__item">
                            <div class="apv-trust__icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2e8b57" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <span class="apv-trust__title"><?php echo esc_html( $settings['trust_3_title'] ); ?></span>
                            <a href="#" class="apv-trust__link">Learn More &#9656;</a>
                        </div>
                    </div>
                </div>


            </div><!-- .apv-info -->
        </div><!-- .apv-product__top -->

        <!-- ════════════════════════════════════════════
             HOW TO BUY (full width below product)
             ════════════════════════════════════════════ -->
        <?php if ( $settings['show_how_to_buy'] === '1' ) :
            $htb_cards = [];
            for ( $ci = 1; $ci <= 5; $ci++ ) {
                $t = $settings["htb_{$ci}_title"] ?? '';
                if ( $t ) {
                    $htb_cards[] = [
                        'title'    => $t,
                        'subtitle' => $settings["htb_{$ci}_subtitle"] ?? '',
                        'image'    => $settings["htb_{$ci}_image"] ?? '',
                    ];
                }
            }
        ?>
        <?php if ( ! empty( $htb_cards ) ) : ?>
        <div class="apv-how-section">
            <div class="apv-how-section__inner">
                <h3 class="apv-info__label apv-info__label--large">How to Buy Your Glasses</h3>
                <div class="apv-how-to-buy">
                    <?php foreach ( $htb_cards as $card ) : ?>
                    <div class="apv-how-card">
                        <?php if ( $card['image'] ) : ?>
                        <div class="apv-how-card__media">
                            <?php
                            $ext = strtolower( pathinfo( $card['image'], PATHINFO_EXTENSION ) );
                            if ( in_array( $ext, [ 'mp4', 'webm', 'mov' ] ) ) : ?>
                                <video src="<?php echo esc_url( $card['image'] ); ?>" muted loop playsinline preload="metadata"></video>
                            <?php else : ?>
                                <img src="<?php echo esc_url( $card['image'] ); ?>" alt="<?php echo esc_attr( $card['title'] ); ?>">
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <div class="apv-how-card__inner">
                            <span><?php echo esc_html( $card['title'] ); ?></span>
                            <small><?php echo esc_html( $card['subtitle'] ); ?></small>
                            <a href="#" class="apv-how-card__link">Learn More &#9656;</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- ════════════════════════════════════════════
             REVIEWS SECTION (full width)
             ════════════════════════════════════════════ -->
        <?php if ( $stats['total'] > 0 || comments_open( $product->get_id() ) ) : ?>
        <div class="apv-section-full" id="apv-reviews-section">
            <div class="apv-section-full__inner">
                <h3 class="apv-info__label apv-info__label--large">Rating &amp; Reviews</h3>

                <?php if ( $stats['total'] > 0 ) : ?>
                <div class="apv-reviews-summary">
                    <div class="apv-reviews-summary__avg">
                        <span class="apv-reviews-summary__number"><?php echo esc_html( $stats['avg'] ); ?></span>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="#2e8b57"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span class="apv-reviews-summary__total"><?php echo esc_html( $stats['total'] ); ?> Reviews</span>
                    </div>
                    <div class="apv-reviews-summary__bars">
                        <?php for ( $s2 = 5; $s2 >= 1; $s2-- ) :
                            $pct = $stats['total'] > 0 ? round( ( $stats['stars'][ $s2 ] / $stats['total'] ) * 100 ) : 0;
                        ?>
                        <div class="apv-bar-row">
                            <span class="apv-bar-row__label"><?php echo $s2; ?> <svg width="10" height="10" viewBox="0 0 24 24" fill="#2e8b57"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></span>
                            <div class="apv-bar-row__track"><div class="apv-bar-row__fill" style="width: <?php echo $pct; ?>%; background: <?php echo $s2 >= 4 ? '#2e8b57' : ( $s2 === 3 ? '#e6a817' : '#d63638' ); ?>;"></div></div>
                            <span class="apv-bar-row__pct"><?php echo $pct; ?>%</span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- User Reviews list -->
                <div class="apv-reviews-list" id="apv-reviews-list" data-product="<?php echo esc_attr( $product->get_id() ); ?>" data-page="1">
                    <h4>User Reviews</h4>
                    <?php
                    $first_reviews = get_comments([
                        'post_id' => $product->get_id(),
                        'status'  => 'approve',
                        'type'    => 'review',
                        'number'  => 5,
                        'orderby' => 'comment_date_gmt',
                        'order'   => 'DESC',
                    ]);
                    ?>
                    <div id="apv-reviews-container">
                    <?php foreach ( $first_reviews as $c ) :
                        $rating = (int) get_comment_meta( $c->comment_ID, 'rating', true );
                        $date   = date( 'j M Y', strtotime( $c->comment_date ) );
                    ?>
                    <div class="apv-review">
                        <div class="apv-review__header">
                            <img src="<?php echo esc_url( get_avatar_url( $c->comment_author_email, [ 'size' => 40 ] ) ); ?>" alt="" class="apv-review__avatar">
                            <div class="apv-review__meta">
                                <span class="apv-review__author"><?php echo esc_html( $c->comment_author ); ?></span>
                                <span class="apv-review__date"><?php echo esc_html( $date ); ?></span>
                            </div>
                            <span class="apv-review__rating"><?php echo $rating; ?> <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></span>
                        </div>
                        <p class="apv-review__text"><?php echo esc_html( $c->comment_content ); ?></p>
                    </div>
                    <?php endforeach; ?>
                    </div>

                    <?php if ( $stats['total'] > 5 ) : ?>
                    <button class="apv-load-more" id="apv-load-more">Load more reviews</button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Write a review -->
                <?php if ( comments_open( $product->get_id() ) ) : ?>
                <div class="apv-write-review">
                    <?php comments_template(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Related / Similar products -->
        <?php if ( $settings['show_styling'] === '1' ) : ?>
        <div class="apv-related">
            <?php
            $related_ids = wc_get_related_products( $product->get_id(), 6 );
            if ( ! empty( $related_ids ) ) :
                $related = array_filter( array_map( 'wc_get_product', $related_ids ) );
            ?>
            <h3 class="apv-info__label apv-info__label--large">You might also like</h3>
            <div class="apv-related__grid">
                <?php foreach ( $related as $rp ) : ?>
                <a href="<?php echo esc_url( $rp->get_permalink() ); ?>" class="apv-related__item">
                    <div class="apv-related__img-wrap">
                        <img src="<?php echo esc_url( wp_get_attachment_image_url( $rp->get_image_id(), 'medium' ) ?: wc_placeholder_img_src( 'medium' ) ); ?>" alt="<?php echo esc_attr( $rp->get_name() ); ?>" loading="lazy">
                        <button class="apv-wishlist-btn apv-wishlist-btn--small" data-product-id="<?php echo esc_attr( $rp->get_id() ); ?>" aria-label="Add to wishlist" onclick="event.preventDefault(); event.stopPropagation(); this.classList.toggle('apv-wishlist-btn--active');">
                            <svg class="apv-wishlist-btn__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                        </button>
                    </div>
                    <span class="apv-related__name"><?php echo esc_html( $rp->get_name() ); ?></span>
                    <span class="apv-related__price"><?php echo $rp->get_price_html(); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Sticky CTA bar -->
        <div class="apv-sticky-cta" id="apv-sticky-cta">
            <div class="apv-sticky-cta__inner">
                <div class="apv-sticky-cta__info">
                    <span class="apv-sticky-cta__name"><?php echo esc_html( $product->get_name() ); ?></span>
                    <span class="apv-sticky-cta__price"><?php echo $product->get_price_html(); ?></span>
                </div>
                <button type="button" class="apv-sticky-cta__btn apv-select-lenses-trigger" style="background: <?php echo esc_attr( $settings['cta_color'] ); ?>;">
                    <?php echo esc_html( $settings['cta_text'] ); ?>
                </button>
            </div>
        </div>

        <!-- Virtual Try-On Modal -->
        <div class="apv-tryon-modal" id="apv-tryon-modal" style="display:none;">
            <div class="apv-tryon-modal__overlay"></div>
            <div class="apv-tryon-modal__content">
                <button class="apv-tryon-modal__close" aria-label="Close try-on">&times;</button>
                <iframe id="apv-tryon-iframe" class="apv-tryon-modal__iframe" allow="camera" allowfullscreen></iframe>
            </div>
        </div>

    </div><!-- .apv-product -->

<?php endwhile;

get_footer( 'shop' );
