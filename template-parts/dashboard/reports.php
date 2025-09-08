<?php
/**
 * Template part for displaying the reports dashboard
 *
 * @package TutiksPOS
 */
?>

<div class="reports-container">
    

    <?php
    // Basic, server-rendered summary so the page is useful without JS yet
    $orders_query = array(
        'post_type'      => 'pos_order',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    $recent_orders   = get_posts($orders_query);
    // Compute today's totals (initial state)
    $today_start = strtotime(date('Y-m-d') . ' 00:00:00');
    $today_end   = strtotime(date('Y-m-d') . ' 23:59:59');
    $today_total_sales  = 0.0;
    $today_total_orders = 0;

    if ($recent_orders) {
        foreach ($recent_orders as $order_post) {
            $order_date = get_post_meta($order_post->ID, '_order_date', true);
            if (!$order_date) {
                $order_date = get_the_date('Y-m-d H:i:s', $order_post);
            }
            $order_ts   = strtotime($order_date);
            if ($order_ts >= $today_start && $order_ts <= $today_end) {
                $today_total_orders += 1;
                $today_total_sales  += (float) get_post_meta($order_post->ID, '_order_total', true);
            }
        }
    }
    ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 id="reports_total_label" class="card-title">Today's Total</h3>
                    <div id="reports_total_value" class="amount" data-today-total="<?php echo esc_attr(number_format($today_total_sales, 2, '.', '')); ?>">₱<?php echo number_format($today_total_sales, 2); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 id="reports_orders_label" class="card-title">Today's Orders</h3>
                    <div id="reports_orders_value" class="amount" data-today-orders="<?php echo esc_attr(intval($today_total_orders)); ?>"><?php echo intval($today_total_orders); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-end gap-3 flex-wrap">
            <h3 class="card-title mb-0">Recent Orders</h3>
            <div class="d-flex align-items-end gap-2">
                <div>
                    <label for="reports_min" class="form-label mb-1">From</label>
                    <input id="reports_min" type="text" class="form-control" placeholder="Select date">
                </div>
                <div>
                    <label for="reports_max" class="form-label mb-1">To</label>
                    <input id="reports_max" type="text" class="form-control" placeholder="Select date">
                </div>
                <div class="pt-3">
                    <button type="button" id="reports_clear_dates" class="btn btn-outline-secondary">Clear</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="reportsTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_orders)) : ?>
                            <?php foreach ($recent_orders as $order_post) :
                                $order_total    = get_post_meta($order_post->ID, '_order_total', true);
                                $payment_method = get_post_meta($order_post->ID, '_payment_method', true);
                                $iso_date_raw   = get_post_meta($order_post->ID, '_order_date', true);
                                if (!$iso_date_raw) {
                                    $iso_date_raw = get_the_date('Y-m-d H:i:s', $order_post);
                                }
                                // Normalize to ISO-8601 with "T" for cross-browser parsing
                                $iso_ts   = strtotime($iso_date_raw);
                                $iso_attr = $iso_ts ? date('c', $iso_ts) : '';
                            ?>
                                <tr>
                                    <td>#<?php echo esc_html($order_post->ID); ?></td>
                                    <td>
                                        <span class="date-display" data-iso="<?php echo esc_attr($iso_attr); ?>">
                                            <?php echo esc_html(get_the_date('M j, Y g:i A', $order_post)); ?>
                                        </span>
                                    </td>
                                    <td>₱<?php echo number_format((float) $order_total, 2); ?></td>
                                    <td><?php echo $payment_method === 'qr' ? 'QR' : esc_html(ucfirst((string) $payment_method)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" class="text-center">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.reports-container {
    max-width: 1200px;
    margin: 0 auto;
}

.amount {
    font-size: 1.75rem;
    font-weight: 700;
}

@media (max-width: 768px) {
    .date-filters { flex-direction: column; }
}
.card-header { overflow: visible; }
.dt-datetime { z-index: 2000; }
</style>


