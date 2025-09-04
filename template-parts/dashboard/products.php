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
            <h3 class="card-title mb-0">Add New Product</h3>
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
                <table class="table table-hover">
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

<style>
.products-container {
    max-width: 1200px;
    margin: 0 auto;
}

.table {
    width: 100%;
    margin-bottom: 1rem;
    background-color: transparent;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 0.75rem;
    vertical-align: middle;
    border-top: 1px solid #dee2e6;
}

.table thead th {
    vertical-align: bottom;
    border-bottom: 2px solid #dee2e6;
    background-color: #f8f9fa;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}

.table-responsive {
    display: block;
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: -15px;
}

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding-right: 15px;
    padding-left: 15px;
}

@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}

.btn-outline-primary {
    color: #007bff;
    border-color: #007bff;
}

.btn-outline-primary:hover {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
}

.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
}

.btn-outline-danger:hover {
    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545;
}

.justify-content-between {
    justify-content: space-between;
}

.align-items-center {
    align-items: center;
}

.d-flex {
    display: flex;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle form visibility
    $('#toggleForm').on('click', function() {
        $('#productForm').slideToggle();
        resetForm();
    });

    // Cancel edit
    $('#cancelEdit').on('click', function() {
        $('#productForm').slideUp();
        resetForm();
    });

    // Edit product
    $('.edit-product').on('click', function() {
        const $btn = $(this);
        const productId = $btn.data('id');
        const productTitle = $btn.data('title');
        const productPrice = $btn.data('price');
        const productCategory = $btn.data('category');

        $('#product_id').val(productId);
        $('#product_title').val(productTitle);
        $('#product_price').val(productPrice);
        $('#product_category').val(productCategory);

        $('#productForm').slideDown();
        $('html, body').animate({
            scrollTop: $('#productForm').offset().top - 100
        }, 500);
    });

    function resetForm() {
        $('#product_id').val('');
        $('#product_title').val('');
        $('#product_price').val('');
        $('#product_category').val('');
    }
});
</script>
