<?php
/**
 * Template part for displaying the dashboard home page
 *
 * @package TutiksPOS
 */

// Get quick stats
$total_products = wp_count_posts('product')->publish;
$total_orders = wp_count_posts('pos_order')->publish;

// Get today's sales
$today_orders = get_posts(array(
    'post_type' => 'pos_order',
    'posts_per_page' => -1,
    'date_query' => array(
        array(
            'year' => date('Y'),
            'month' => date('m'),
            'day' => date('d'),
        ),
    ),
));

$today_sales = 0;
foreach ($today_orders as $order) {
    $today_sales += floatval(get_post_meta($order->ID, '_order_total', true));
}

// Get recent orders
$recent_orders = get_posts(array(
    'post_type' => 'pos_order',
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC',
));
?>

<div class="dashboard-home">
    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-cash-register text-primary"></i>
                    </div>
                    <h3 class="card-title">Today's Sales</h3>
                    <div class="stat-value">₱<?php echo number_format($today_sales, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-box text-success"></i>
                    </div>
                    <h3 class="card-title">Total Products</h3>
                    <div class="stat-value"><?php echo $total_products; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart text-info"></i>
                    </div>
                    <h3 class="card-title">Total Orders</h3>
                    <div class="stat-value"><?php echo $total_orders; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="<?php echo esc_url(home_url('/pos')); ?>" class="btn btn-lg btn-primary me-2">
                            <i class="fas fa-cash-register me-2"></i>Open POS
                        </a>
                        <a href="<?php echo esc_url(home_url('/products')); ?>" class="btn btn-lg btn-success me-2">
                            <i class="fas fa-plus me-2"></i>Add Product
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Orders</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order):
                                    $total = get_post_meta($order->ID, '_order_total', true);
                                ?>
                                    <tr>
                                        <td>#<?php echo $order->ID; ?></td>
                                        <td><?php echo get_the_date('M j, Y g:i A', $order); ?></td>
                                        <td>₱<?php echo number_format($total, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-home {
    max-width: 1200px;
    margin: 0 auto;
}

.stat-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    margin-top: 0.5rem;
}

.quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.quick-actions .btn {
    flex: 1;
    min-width: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

@media (max-width: 768px) {
    .quick-actions {
        flex-direction: column;
    }
    
    .quick-actions .btn {
        width: 100%;
    }
}
</style>
