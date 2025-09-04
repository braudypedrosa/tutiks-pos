<?php
/**
 * Tutiks POS Theme functions and definitions
 *
 * @package TutiksPOS
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @since 1.0.0
 * @return void
 */
function tutiks_pos_setup() {
    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Enable title tag support
    add_theme_support('title-tag');

    // Add custom logo support
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    // Enable post thumbnails
    add_theme_support('post-thumbnails');

    // Enable HTML5 markup
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // Register navigation menus
    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'tutiks-pos'),
        'pos-menu' => esc_html__('POS Menu', 'tutiks-pos'),
    ));
}
add_action('after_setup_theme', 'tutiks_pos_setup');

/**
 * Enqueue scripts and styles.
 *
 * @since 1.0.0
 * @return void
 */
function tutiks_pos_scripts() {
    global $pagenow;
    $is_pos_page = is_page('pos') || (isset($_GET['page']) && $_GET['page'] === 'pos');
    // Third-party CSS
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', array(), '5.3.0');
    wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', array(), '6.4.2');

    // Theme CSS
    wp_enqueue_style('tutiks-pos-style', get_stylesheet_uri(), array('bootstrap'), wp_get_theme()->get('Version'));
    wp_enqueue_style('tutiks-pos-pos', get_template_directory_uri() . '/assets/css/pos.css', array('bootstrap'), wp_get_theme()->get('Version'));
    wp_enqueue_style('tutiks-pos-admin', get_template_directory_uri() . '/assets/css/admin.css', array(), wp_get_theme()->get('Version'));

    // Third-party JavaScript
    wp_enqueue_script('jquery');
    wp_enqueue_script('bootstrap-bundle', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
    wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), '11.0.0', true);
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);

    // Theme JavaScript
    wp_enqueue_script('tutiks-pos-script', get_template_directory_uri() . '/assets/js/main.js', array('jquery', 'bootstrap-bundle', 'sweetalert2'), wp_get_theme()->get('Version'), true);
    
    // Only load POS script on POS page or when needed
    if (is_page('pos') || (isset($_GET['page']) && $_GET['page'] === 'pos')) {
        wp_enqueue_script('tutiks-pos-pos', get_template_directory_uri() . '/assets/js/pos.js', array('jquery', 'bootstrap-bundle', 'sweetalert2', 'tutiks-pos-script'), wp_get_theme()->get('Version'), true);
        
        // Localize POS script
        wp_localize_script('tutiks-pos-pos', 'tutiksPOS', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tutiks_pos_nonce'),
            'qrCodeUrl' => get_option('tutiks_pos_qr_code_url', ''),
            'debug' => WP_DEBUG
        ));
    }
    

    // No need for additional localization here as it's handled in the conditional blocks above
}
add_action('wp_enqueue_scripts', 'tutiks_pos_scripts');
add_action('admin_enqueue_scripts', 'tutiks_pos_scripts');

/**
 * Register Custom Post Type for Products
 *
 * @since 1.0.0
 * @return void
 */
function tutiks_pos_register_post_types() {
    // Register Orders Post Type
    $labels = array(
        'name'               => _x('Sales', 'post type general name', 'tutiks-pos'),
        'singular_name'      => _x('Sale', 'post type singular name', 'tutiks-pos'),
        'menu_name'          => _x('Sales', 'admin menu', 'tutiks-pos'),
        'name_admin_bar'     => _x('Sale', 'add new on admin bar', 'tutiks-pos'),
        'add_new'            => _x('Add New', 'sale', 'tutiks-pos'),
        'add_new_item'       => __('Add New Sale', 'tutiks-pos'),
        'new_item'           => __('New Sale', 'tutiks-pos'),
        'edit_item'          => __('Edit Sale', 'tutiks-pos'),
        'view_item'          => __('View Sale', 'tutiks-pos'),
        'all_items'          => __('Sales', 'tutiks-pos'),
        'search_items'       => __('Search Sales', 'tutiks-pos'),
        'not_found'          => __('No sales found.', 'tutiks-pos'),
        'not_found_in_trash' => __('No sales found in Trash.', 'tutiks-pos')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=product',
        'capability_type'    => 'post',
        'supports'           => array('title'),
    );

    register_post_type('pos_order', $args);
    $labels = array(
        'name'               => _x('Products', 'post type general name', 'tutiks-pos'),
        'singular_name'      => _x('Product', 'post type singular name', 'tutiks-pos'),
        'menu_name'          => _x('Products', 'admin menu', 'tutiks-pos'),
        'name_admin_bar'     => _x('Product', 'add new on admin bar', 'tutiks-pos'),
        'add_new'            => _x('Add New', 'product', 'tutiks-pos'),
        'add_new_item'       => __('Add New Product', 'tutiks-pos'),
        'new_item'           => __('New Product', 'tutiks-pos'),
        'edit_item'          => __('Edit Product', 'tutiks-pos'),
        'view_item'          => __('View Product', 'tutiks-pos'),
        'all_items'          => __('All Products', 'tutiks-pos'),
        'search_items'       => __('Search Products', 'tutiks-pos'),
        'not_found'          => __('No products found.', 'tutiks-pos'),
        'not_found_in_trash' => __('No products found in Trash.', 'tutiks-pos')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'product'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-cart',
        'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields'),
    );

    register_post_type('product', $args);

    // Register Menu Category Taxonomy
    $labels = array(
        'name'              => _x('Menu Categories', 'taxonomy general name', 'tutiks-pos'),
        'singular_name'     => _x('Menu Category', 'taxonomy singular name', 'tutiks-pos'),
        'search_items'      => __('Search Menu Categories', 'tutiks-pos'),
        'all_items'         => __('All Menu Categories', 'tutiks-pos'),
        'parent_item'       => __('Parent Menu Category', 'tutiks-pos'),
        'parent_item_colon' => __('Parent Menu Category:', 'tutiks-pos'),
        'edit_item'         => __('Edit Menu Category', 'tutiks-pos'),
        'update_item'       => __('Update Menu Category', 'tutiks-pos'),
        'add_new_item'      => __('Add New Menu Category', 'tutiks-pos'),
        'new_item_name'     => __('New Menu Category Name', 'tutiks-pos'),
        'menu_name'         => __('Menu Categories', 'tutiks-pos'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'          => true,
        'show_admin_column' => true,
        'query_var'        => true,
        'rewrite'          => array('slug' => 'menu-category'),
    );

    register_taxonomy('menu_category', array('product'), $args);
}
add_action('init', 'tutiks_pos_register_post_types');

/**
 * Add custom meta box for product price
 *
 * @since 1.0.0
 * @return void
 */
function tutiks_pos_add_product_meta_boxes() {
    add_meta_box(
        'product_price',
        __('Product Price', 'tutiks-pos'),
        'tutiks_pos_product_price_callback',
        'product',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'tutiks_pos_add_product_meta_boxes');

/**
 * Product price meta box callback
 *
 * @param WP_Post $post Current post object
 * @return void
 */
function tutiks_pos_product_price_callback($post) {
    wp_nonce_field('tutiks_pos_product_price', 'tutiks_pos_product_price_nonce');
    $price = get_post_meta($post->ID, '_product_price', true);
    ?>
    <p>
        <label for="tutiks_pos_product_price"><?php _e('Price ($):', 'tutiks-pos'); ?></label>
        <input type="number" id="tutiks_pos_product_price" name="tutiks_pos_product_price" 
               value="<?php echo esc_attr($price); ?>" step="0.01" min="0" style="width: 100%">
    </p>
    <?php
}

/**
 * Save product price meta box data
 *
 * @param int $post_id The ID of the post being saved
 * @return void
 */
function tutiks_pos_save_product_price($post_id) {
    if (!isset($_POST['tutiks_pos_product_price_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['tutiks_pos_product_price_nonce'], 'tutiks_pos_product_price')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['tutiks_pos_product_price'])) {
        update_post_meta(
            $post_id,
            '_product_price',
            floatval($_POST['tutiks_pos_product_price'])
        );
    }
}
add_action('save_post_product', 'tutiks_pos_save_product_price');

/**
 * Initialize menu categories and products
 */
function tutiks_pos_initialize_menu() {
    // Define categories
    $categories = array(
        'brunch' => 'BRUNCH',
        'veggies' => 'VEGGIES',
        'chicken' => 'CHICKEN',
        'rice-noodles' => 'RICE, NOODLES',
        'seafood' => 'SEAFOOD',
        'pork' => 'PORK',
        'beef' => 'BEEF',
        'snacks' => 'SNACKS'
    );

    // Create categories
    $created_categories = array();
    foreach ($categories as $slug => $name) {
        $term = term_exists($slug, 'menu_category');
        if (!$term) {
            $term = wp_insert_term($name, 'menu_category', array('slug' => $slug));
        }
        if (!is_wp_error($term)) {
            $created_categories[$slug] = $term['term_id'];
        }
    }

    // Define menu items
    $menu_items = array(
        // BRUNCH
        array('title' => 'Egg', 'price' => 15.00, 'category' => 'brunch', 
              'description' => 'scrambled/sunny-side up/hard-boiled'),
        array('title' => 'Longaniza', 'price' => 20.00, 'category' => 'brunch',
              'description' => '2pcs pork longaniza'),
        array('title' => 'Corned Beef', 'price' => 45.00, 'category' => 'brunch',
              'description' => 'served with mixed veggies'),
        array('title' => 'Tocino', 'price' => 45.00, 'category' => 'brunch',
              'description' => 'homemade chicken tocino'),
        array('title' => 'Hotdog', 'price' => 20.00, 'category' => 'brunch'),

        // VEGGIES
        array('title' => 'Ampalaya with Egg', 'price' => 30.00, 'category' => 'veggies'),
        array('title' => 'Tortang Talong', 'price' => 30.00, 'category' => 'veggies'),
        array('title' => 'Sitao Adobo', 'price' => 30.00, 'category' => 'veggies'),
        array('title' => 'Pinakbet', 'price' => 30.00, 'category' => 'veggies'),
        array('title' => 'Chopseuy', 'price' => 50.00, 'category' => 'veggies'),
        array('title' => 'Camote Tops', 'price' => 30.00, 'category' => 'veggies'),
        array('title' => 'Langka Salad', 'price' => 30.00, 'category' => 'veggies'),
        array('title' => 'Ginataang Kalabasa', 'price' => 30.00, 'category' => 'veggies'),
        array('title' => 'Lumpia Veggies', 'price' => 20.00, 'category' => 'veggies'),
        array('title' => 'Caesar Salad', 'price' => 50.00, 'category' => 'veggies'),

        // CHICKEN
        array('title' => 'Fried Chicken', 'price' => 50.00, 'category' => 'chicken'),
        array('title' => 'Chicken Curry', 'price' => 65.00, 'category' => 'chicken'),
        array('title' => 'Chicken Teriyaki', 'price' => 50.00, 'category' => 'chicken'),
        array('title' => 'Chicken Adobo', 'price' => 50.00, 'category' => 'chicken'),
        array('title' => 'Chicken Liver', 'price' => 25.00, 'category' => 'chicken'),
        array('title' => 'Chicken Feet', 'price' => 60.00, 'category' => 'chicken'),

        // RICE & NOODLES
        array('title' => 'Plain Rice', 'price' => 15.00, 'category' => 'rice-noodles'),
        array('title' => 'Fried Rice', 'price' => 25.00, 'category' => 'rice-noodles'),
        array('title' => 'Pancit / Bam-i', 'price' => 30.00, 'category' => 'rice-noodles'),
        array('title' => 'Sotanghon', 'price' => 50.00, 'category' => 'rice-noodles'),
        array('title' => 'Batchoy', 'price' => 50.00, 'category' => 'rice-noodles'),

        // SEAFOOD
        array('title' => 'Fried Fish (Kutob)', 'price' => 30.00, 'category' => 'seafood'),
        array('title' => 'Fish Fillet', 'price' => 50.00, 'category' => 'seafood'),
        array('title' => 'Bangus Daing', 'price' => 35.00, 'category' => 'seafood'),
        array('title' => 'Buttered Shrimps', 'price' => 75.00, 'category' => 'seafood'),
        array('title' => 'Kinilaw Malasugue', 'price' => 80.00, 'category' => 'seafood'),
        array('title' => 'Tinolang Bariles', 'price' => 80.00, 'category' => 'seafood'),
        array('title' => 'Guso', 'price' => 25.00, 'category' => 'seafood'),

        // PORK
        array('title' => 'Breaded Pork Chop', 'price' => 60.00, 'category' => 'pork'),
        array('title' => 'Pork Adobo', 'price' => 60.00, 'category' => 'pork'),
        array('title' => 'Humba', 'price' => 60.00, 'category' => 'pork'),
        array('title' => 'Ginaling with Patatas', 'price' => 50.00, 'category' => 'pork'),
        array('title' => 'Bicol Express', 'price' => 65.00, 'category' => 'pork'),
        array('title' => 'Menudo', 'price' => 60.00, 'category' => 'pork'),
        array('title' => 'Meatball', 'price' => 25.00, 'category' => 'pork'),
        array('title' => 'Pork BBQ', 'price' => 45.00, 'category' => 'pork'),
        array('title' => 'Sinugba', 'price' => 75.00, 'category' => 'pork'),
        array('title' => 'Sinuglaw', 'price' => 120.00, 'category' => 'pork'),

        // BEEF
        array('title' => 'Beef with Mushroom', 'price' => 75.00, 'category' => 'beef'),
        array('title' => 'Beef with Brocolli', 'price' => 75.00, 'category' => 'beef'),
        array('title' => 'Beef Steak', 'price' => 75.00, 'category' => 'beef'),
        array('title' => 'Burger Steak', 'price' => 50.00, 'category' => 'beef'),
        array('title' => 'Kare-Kare', 'price' => 80.00, 'category' => 'beef'),
        array('title' => 'Lauyang Baka', 'price' => 85.00, 'category' => 'beef'),

        // SNACKS
        array('title' => 'Binignit', 'price' => 35.00, 'category' => 'snacks'),
        array('title' => 'Maja Blanca', 'price' => 15.00, 'category' => 'snacks'),
        array('title' => 'Mango Crepe Rolls', 'price' => 250.00, 'category' => 'snacks',
              'description' => '(pre-order 14 pcs.)')
    );

    // Create products
    foreach ($menu_items as $item) {
        // Check if product already exists
        $existing_product = get_page_by_title($item['title'], OBJECT, 'product');
        
        if (!$existing_product) {
            // Create post object
            $product = array(
                'post_title'   => $item['title'],
                'post_content' => isset($item['description']) ? $item['description'] : '',
                'post_status'  => 'publish',
                'post_type'    => 'product'
            );

            // Insert the post into the database
            $product_id = wp_insert_post($product);

            if ($product_id) {
                // Set the category
                if (isset($created_categories[$item['category']])) {
                    wp_set_object_terms($product_id, $created_categories[$item['category']], 'menu_category');
                }

                // Set the price
                update_post_meta($product_id, '_product_price', $item['price']);
            }
        }
    }
}

// Hook for activation
function tutiks_pos_activate() {
    tutiks_pos_initialize_menu();
}
register_activation_hook(__FILE__, 'tutiks_pos_activate');

// Add menu item to manually run initialization
function tutiks_pos_admin_menu() {
    // Add Initialize Menu as submenu under Products
    add_submenu_page(
        'edit.php?post_type=product',
        'Initialize Menu',
        'Initialize Menu',
        'manage_options',
        'initialize-menu',
        'tutiks_pos_initialize_menu_page'
    );
}

add_action('admin_menu', 'tutiks_pos_admin_menu');

/**
 * AJAX handler for saving orders
 */
function tutiks_pos_save_order() {
    // Verify nonce
    check_ajax_referer('tutiks_pos_nonce', 'nonce');

    // Get order data
    $order_data = json_decode(stripslashes($_POST['order_data']), true);
    
    if (!$order_data) {
        wp_send_json_error('Invalid order data');
        return;
    }

    // Create order title with date and time
    $order_title = 'Order #' . date('YmdHis');

    // Create post object
    $order_post = array(
        'post_title'   => $order_title,
        'post_status'  => 'publish',
        'post_type'    => 'pos_order'
    );

    // Insert the post into the database
    $order_id = wp_insert_post($order_post);

    if ($order_id) {
        // Save order details as post meta
        update_post_meta($order_id, '_order_items', $order_data['items']);
        update_post_meta($order_id, '_order_total', $order_data['total']);
        update_post_meta($order_id, '_payment_method', $order_data['paymentMethod']);
        update_post_meta($order_id, '_order_date', $order_data['timestamp']);

        // Create order details for display
        $items_list = array();
        foreach ($order_data['items'] as $item) {
            $items_list[] = $item['name'] . ' × ' . $item['quantity'];
        }

        // Update post content with order summary
        $order_summary = sprintf(
            'Order Total: ₱%s\nPayment Method: %s\nItems:\n- %s',
            number_format($order_data['total'], 2),
            $order_data['paymentMethod'],
            implode("\n- ", $items_list)
        );

        wp_update_post(array(
            'ID' => $order_id,
            'post_content' => $order_summary
        ));

        wp_send_json_success(array(
            'order_id' => $order_id,
            'message' => 'Order saved successfully'
        ));
    } else {
        wp_send_json_error('Failed to create order');
    }
}
add_action('wp_ajax_tutiks_pos_save_order', 'tutiks_pos_save_order');
add_action('wp_ajax_nopriv_tutiks_pos_save_order', 'tutiks_pos_save_order');

/**
 * Add custom columns to Sales listing
 */
function tutiks_pos_order_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        if ($key === 'title') {
            $new_columns[$key] = $value;
            $new_columns['order_total'] = 'Total';
            $new_columns['payment_method'] = 'Payment Method';
            $new_columns['order_items'] = 'Items';
            $new_columns['order_date'] = 'Order Date';
        } else if ($key !== 'date') { // Skip the default date column
            $new_columns[$key] = $value;
        }
    }
    return $new_columns;
}
add_filter('manage_pos_order_posts_columns', 'tutiks_pos_order_columns');

/**
 * Display custom column content
 */
function tutiks_pos_order_column_content($column, $post_id) {
    switch ($column) {
        case 'order_total':
            $total = get_post_meta($post_id, '_order_total', true);
            echo '₱' . number_format((float)$total, 2);
            break;
        
        case 'payment_method':
            $method = get_post_meta($post_id, '_payment_method', true);
            echo $method === 'qr' ? 'QR' : ucfirst($method);
            break;
        
        case 'order_items':
            $items = get_post_meta($post_id, '_order_items', true);
            if ($items) {
                $items_list = array();
                foreach ($items as $item) {
                    $items_list[] = sprintf('%s × %d', $item['name'], $item['quantity']);
                }
                echo implode(', ', array_slice($items_list, 0, 3));
                if (count($items_list) > 3) {
                    echo ', ...';
                }
            }
            break;
        
        case 'order_date':
            $date = get_post_meta($post_id, '_order_date', true);
            if ($date) {
                echo date('Y-m-d H:i:s', strtotime($date));
            }
            break;
    }
}
add_action('manage_pos_order_posts_custom_column', 'tutiks_pos_order_column_content', 10, 2);

/**
 * Make custom columns sortable
 */
function tutiks_pos_order_sortable_columns($columns) {
    $columns['order_total'] = 'order_total';
    $columns['payment_method'] = 'payment_method';
    $columns['order_date'] = 'order_date';
    return $columns;
}
add_filter('manage_edit-pos_order_sortable_columns', 'tutiks_pos_order_sortable_columns');

/**
 * Add meta box for order details
 */
function tutiks_pos_add_order_meta_box() {
    add_meta_box(
        'pos_order_details',
        'Order Details',
        'tutiks_pos_order_details_callback',
        'pos_order',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tutiks_pos_add_order_meta_box');

/**
 * Order details meta box callback
 */
function tutiks_pos_order_details_callback($post) {
    $order_total = get_post_meta($post->ID, '_order_total', true);
    $payment_method = get_post_meta($post->ID, '_payment_method', true);
    $order_date = get_post_meta($post->ID, '_order_date', true);
    $items = get_post_meta($post->ID, '_order_items', true);
    ?>
    <style>
        .order-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .order-detail-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .order-detail-box h4 {
            margin: 0 0 10px;
            color: #666;
            font-size: 13px;
        }
        .order-detail-box .value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .order-items-table th,
        .order-items-table td {
            padding: 10px;
            border: 1px solid #dee2e6;
            text-align: left;
        }
        .order-items-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .order-items-table tr:nth-child(even) {
            background: #f8f9fa;
        }
    </style>

    <div class="order-details-grid">
        <div class="order-detail-box">
            <h4>Order Total</h4>
            <div class="value">₱<?php echo number_format((float)$order_total, 2); ?></div>
        </div>
        <div class="order-detail-box">
            <h4>Payment Method</h4>
            <div class="value"><?php echo $payment_method === 'qr' ? 'QR' : ucfirst($payment_method); ?></div>
        </div>
        <div class="order-detail-box">
            <h4>Order Date</h4>
            <div class="value"><?php echo date('Y-m-d H:i:s', strtotime($order_date)); ?></div>
        </div>
    </div>

    <table class="order-items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo esc_html($item['name']); ?></td>
                        <td><?php echo intval($item['quantity']); ?></td>
                        <td>₱<?php echo number_format((float)$item['price'], 2); ?></td>
                        <td>₱<?php echo number_format((float)$item['price'] * intval($item['quantity']), 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
}


function tutiks_pos_initialize_menu_page() {
    if (isset($_POST['initialize_menu']) && check_admin_referer('initialize_menu_nonce')) {
        tutiks_pos_initialize_menu();
        echo '<div class="notice notice-success"><p>Menu has been initialized!</p></div>';
    }
    
    if (isset($_POST['create_sample_orders']) && check_admin_referer('initialize_menu_nonce')) {
        tutiks_pos_create_sample_orders();
        echo '<div class="notice notice-success"><p>Sample orders have been created!</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Initialize Menu</h1>
        <p>Click the button below to initialize or update the menu items.</p>
        <form method="post">
            <?php wp_nonce_field('initialize_menu_nonce'); ?>
            <button type="submit" name="initialize_menu" class="button button-primary">Initialize Menu</button>
        </form>
        
        <hr>
        <h2>Sample Data</h2>
        <p>Create sample orders for testing purposes.</p>
        <form method="post">
            <?php wp_nonce_field('initialize_menu_nonce'); ?>
            <button type="submit" name="create_sample_orders" class="button button-secondary">Create Sample Orders</button>
        </form>
    </div>
    <?php
}

/**
 * Create sample orders for testing
 */
function tutiks_pos_create_sample_orders() {
    // Sample order data
    $sample_orders = array(
        array(
            'items' => array(
                array('name' => 'Fried Chicken', 'price' => 50.00, 'quantity' => 2),
                array('name' => 'Plain Rice', 'price' => 15.00, 'quantity' => 2),
            ),
            'total' => 130.00,
            'payment_method' => 'cash',
            'date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ),
        array(
            'items' => array(
                array('name' => 'Pork Adobo', 'price' => 60.00, 'quantity' => 1),
                array('name' => 'Fried Rice', 'price' => 25.00, 'quantity' => 1),
            ),
            'total' => 85.00,
            'payment_method' => 'qr',
            'date' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ),
        array(
            'items' => array(
                array('name' => 'Beef Steak', 'price' => 75.00, 'quantity' => 1),
                array('name' => 'Plain Rice', 'price' => 15.00, 'quantity' => 1),
            ),
            'total' => 90.00,
            'payment_method' => 'cash',
            'date' => date('Y-m-d H:i:s', strtotime('-3 days'))
        ),
        array(
            'items' => array(
                array('name' => 'Chicken Curry', 'price' => 65.00, 'quantity' => 1),
                array('name' => 'Plain Rice', 'price' => 15.00, 'quantity' => 1),
            ),
            'total' => 80.00,
            'payment_method' => 'qr',
            'date' => date('Y-m-d H:i:s', strtotime('-4 days'))
        ),
        array(
            'items' => array(
                array('name' => 'Sinuglaw', 'price' => 120.00, 'quantity' => 1),
                array('name' => 'Plain Rice', 'price' => 15.00, 'quantity' => 1),
            ),
            'total' => 135.00,
            'payment_method' => 'cash',
            'date' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ),
    );
    
    foreach ($sample_orders as $order_data) {
        // Create order title with date and time
        $order_title = 'Order #' . date('YmdHis', strtotime($order_data['date']));
        
        // Create post object
        $order_post = array(
            'post_title'   => $order_title,
            'post_status'  => 'publish',
            'post_type'    => 'pos_order',
            'post_date'    => $order_data['date']
        );
        
        // Insert the post into the database
        $order_id = wp_insert_post($order_post);
        
        if ($order_id) {
            // Save order details as post meta
            update_post_meta($order_id, '_order_items', $order_data['items']);
            update_post_meta($order_id, '_order_total', $order_data['total']);
            update_post_meta($order_id, '_payment_method', $order_data['payment_method']);
            update_post_meta($order_id, '_order_date', $order_data['date']);
            
            // Create order details for display
            $items_list = array();
            foreach ($order_data['items'] as $item) {
                $items_list[] = $item['name'] . ' × ' . $item['quantity'];
            }
            
            // Update post content with order summary
            $order_summary = sprintf(
                'Order Total: ₱%s\nPayment Method: %s\nItems:\n- %s',
                number_format($order_data['total'], 2),
                $order_data['payment_method'],
                implode("\n- ", $items_list)
            );
            
            wp_update_post(array(
                'ID' => $order_id,
                'post_content' => $order_summary
            ));
        }
    }
}
