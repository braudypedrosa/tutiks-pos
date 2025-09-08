<?php
/**
 * Template part for displaying the products management page
 *
 * @package TutiksPOS
 */

// Handle form submission
if (isset($_POST['tutiks_product_nonce']) && wp_verify_nonce($_POST['tutiks_product_nonce'], 'tutiks_product')) {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $product_title = sanitize_text_field($_POST['product_title']);
    $product_price = floatval($_POST['product_price']);
    $product_category = sanitize_text_field($_POST['product_category']);
    
    // Create or update product
    $product_data = array(
        'post_title' => $product_title,
        'post_status' => 'publish',
        'post_type' => 'product'
    );

    if ($product_id) {
        $product_data['ID'] = $product_id;
        $product_id = wp_update_post($product_data);
    } else {
        $product_id = wp_insert_post($product_data);
    }

    if (!is_wp_error($product_id)) {
        // Update price
        update_post_meta($product_id, '_product_price', $product_price);
        
        // Update category
        wp_set_object_terms($product_id, $product_category, 'menu_category');
        
        $message = 'Product saved successfully.';
        $message_type = 'success';
    } else {
        $message = 'Error saving product.';
        $message_type = 'danger';
    }
}

// Handle delete request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    if (wp_delete_post($product_id, true)) {
        $message = 'Product deleted successfully.';
        $message_type = 'success';
    } else {
        $message = 'Error deleting product.';
        $message_type = 'danger';
    }
}

// Get all categories
$categories = get_terms(array(
    'taxonomy' => 'menu_category',
    'hide_empty' => false,
));

// Get all products
$products = get_posts(array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
));
?>

<div class="products-container">
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo esc_attr($message_type); ?>">
            <?php echo esc_html($message); ?>
        </div>
    <?php endif; ?>

    <!-- Add/Edit Product Form -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 id="productFormTitle" class="card-title mb-0">Add New Product</h3>
            <button type="button" class="btn btn-outline-primary btn-sm" id="toggleForm">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <div class="card-body" id="productForm" style="display: none;">
            <form method="post" class="product-form">
                <?php wp_nonce_field('tutiks_product', 'tutiks_product_nonce'); ?>
                <input type="hidden" name="product_id" id="product_id" value="">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="product_title">Product Name</label>
                            <input type="text" id="product_title" name="product_title" 
                                   class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="product_price">Price</label>
                            <input type="number" id="product_price" name="product_price" 
                                   class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="product_category">Category</label>
                    <select id="product_category" name="product_category" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo esc_attr($category->slug); ?>">
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary me-2" id="cancelEdit">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Products</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="productsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product):
                            $price = get_post_meta($product->ID, '_product_price', true);
                            $categories = wp_get_post_terms($product->ID, 'menu_category');
                            $category = !empty($categories) ? $categories[0]->name : '';
                        ?>
                            <tr>
                                <td><?php echo esc_html($product->post_title); ?></td>
                                <td><?php echo esc_html($category); ?></td>
                                <td>₱<?php echo number_format($price, 2); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary me-2 edit-product"
                                            data-id="<?php echo esc_attr($product->ID); ?>"
                                            data-title="<?php echo esc_attr($product->post_title); ?>"
                                            data-price="<?php echo esc_attr($price); ?>"
                                            data-category="<?php echo esc_attr(!empty($categories) ? $categories[0]->slug : ''); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?action=delete&product_id=<?php echo esc_attr($product->ID); ?>" 
                                       class="btn btn-sm btn-outline-danger delete-product"
                                       onclick="return confirm('Are you sure you want to delete this product?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(function($) {
    $('#productsTable').DataTable({
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100],
        order: [[0, 'asc']],
        language: { search: 'Search:' },
        columnDefs: [
            { targets: -1, orderable: false, searchable: false } // Actions column
        ],
        dom: "<'row'<'col-sm-12 col-md-8 d-flex align-items-center gap-2'l f><'col-sm-12 col-md-4 text-md-end'>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });

    // Form toggle and edit mode logic
    const $formCardBody = $('#productForm');
    const $title = $('#productFormTitle');
    const $toggle = $('#toggleForm');
    const $inputId = $('#product_id');
    const $inputTitle = $('#product_title');
    const $inputPrice = $('#product_price');
    const $inputCategory = $('#product_category');
    const defaultTitle = 'Add New Product';

    function openForm() {
        if (!$formCardBody.is(':visible')) {
            $formCardBody.slideDown(150);
        }
    }

    function resetFormToAddMode() {
        $inputId.val('');
        $inputTitle.val('');
        $inputPrice.val('');
        $inputCategory.val('');
        $title.text(defaultTitle);
    }

    function setEditMode(data) {
        $title.text('Edit Product');
        $inputId.val(data.id || '');
        $inputTitle.val(data.title || '');
        $inputPrice.val(data.price || '');
        if (data.category) { $inputCategory.val(data.category); }
        openForm();
    }

    // Toggle button shows/hides form. Hiding resets to add mode.
    $toggle.on('click', function() {
        if ($formCardBody.is(':visible')) {
            $formCardBody.slideUp(150, resetFormToAddMode);
        } else {
            resetFormToAddMode();
            openForm();
        }
    });

    // Edit buttons populate the form and switch title to Edit Product
    $(document).on('click', '.edit-product', function() {
        const $btn = $(this);
        setEditMode({
            id: $btn.data('id'),
            title: $btn.data('title'),
            price: $btn.data('price'),
            category: $btn.data('category')
        });
    });

    // Cancel returns to Add New Product and hides the form
    $('#cancelEdit').on('click', function() {
        resetFormToAddMode();
        $formCardBody.slideUp(150);
    });
});
</script>