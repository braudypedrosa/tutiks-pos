<?php
/**
 * Template Name: POS Dashboard
 *
 * @package TutiksPOS
 * @since 1.0.0
 */

// Redirect non-logged in users
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();
?>

<div class="pos-dashboard py-4">
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Main Content Area -->
            <div class="col-lg-9">
                <!-- Search and Categories -->
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Search Bar -->
                        <div class="input-group input-group-lg mb-4">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" id="pos-search" class="form-control" 
                                   placeholder="<?php esc_attr_e('Search products...', 'tutiks-pos'); ?>">
                        </div>

                        <!-- Category Pills -->
                        <div class="category-pills">
                            <button class="btn btn-outline-primary me-2 mb-2 active" data-category="all">
                                <i class="fas fa-th-large me-2"></i>All Items
                            </button>
                            <?php
                            $categories = get_terms(array(
                                'taxonomy' => 'menu_category',
                                'hide_empty' => true,
                            ));

                            if (!is_wp_error($categories)) {
                                foreach ($categories as $category) {
                                    $icon_class = 'fa-utensils'; // default icon
                                    
                                    // Set specific icons for categories
                                    switch ($category->slug) {
                                        case 'brunch':
                                            $icon_class = 'fa-coffee';
                                            break;
                                        case 'veggies':
                                            $icon_class = 'fa-carrot';
                                            break;
                                        case 'chicken':
                                            $icon_class = 'fa-drumstick-bite';
                                            break;
                                        case 'rice-noodles':
                                            $icon_class = 'fa-bowl-rice';
                                            break;
                                        case 'seafood':
                                            $icon_class = 'fa-fish';
                                            break;
                                        case 'pork':
                                            $icon_class = 'fa-bacon';
                                            break;
                                        case 'beef':
                                            $icon_class = 'fa-cow';
                                            break;
                                        case 'snacks':
                                            $icon_class = 'fa-cookie-bite';
                                            break;
                                    }
                                    ?>
                                    <button class="btn btn-outline-primary me-2 mb-2" 
                                            data-category="<?php echo esc_attr($category->slug); ?>">
                                        <i class="fas <?php echo esc_attr($icon_class); ?> me-2"></i>
                                        <?php echo esc_html($category->name); ?>
                                    </button>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="row g-4">
                    <?php
                    $products = new WP_Query(array(
                        'post_type' => 'product',
                        'posts_per_page' => -1,
                    ));

                    if ($products->have_posts()) :
                        while ($products->have_posts()) : $products->the_post();
                            $price = get_post_meta(get_the_ID(), '_product_price', true);
                            ?>
                            <?php
                            $categories = get_the_terms(get_the_ID(), 'menu_category');
                            $category_slug = $categories ? $categories[0]->slug : '';
                            ?>
                            <div class="col-sm-6 col-md-4 col-xl-3">
                                <div class="product-item card h-100" 
                                     data-id="<?php echo esc_attr(get_the_ID()); ?>"
                                     data-price="<?php echo esc_attr($price); ?>"
                                     data-category="<?php echo esc_attr($category_slug); ?>">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="product-image">
                                            <?php the_post_thumbnail('medium', ['class' => 'card-img-top']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php the_title(); ?></h5>
                                        <p class="card-text text-primary h4 mb-3">₱<?php echo number_format($price, 2); ?></p>
                                        <button class="btn btn-primary mt-auto">
                                            <i class="bi bi-plus-circle me-2"></i>Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>
            </div>

            <!-- Cart Section -->
            <div class="col-lg-3">
                <div class="pos-cart card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Current Order</h5>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="cart-items px-3 py-2">
                            <!-- Cart items will be dynamically added here -->
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top">
                        <div class="cart-summary mb-3">
                            <div class="d-flex justify-content-between total">
                                <span class="fw-bold">Total:</span>
                                <span class="amount fw-bold text-primary">₱0.00</span>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="clear-cart btn btn-outline-danger">
                                <i class="bi bi-trash me-2"></i>Clear Cart
                            </button>
                            <button class="checkout btn btn-success btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();