<?php
/**
 * Template Name: Dashboard
 *
 * @package TutiksPOS
 */

// Redirect non-logged in users
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();
?>

<div class="dashboard-container">
    <!-- Sidebar Navigation -->
    <div class="dashboard-sidebar">
        <div class="sidebar-header">
            <h3>Tutik's POS</h3>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li class="<?php echo is_page('pos') ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url(home_url('/pos')); ?>">
                        <i class="fas fa-cash-register"></i> POS
                    </a>
                </li>
                <li class="<?php echo is_page('products') ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url(home_url('/products')); ?>">
                        <i class="fas fa-box"></i> Products
                    </a>
                </li>
                <li class="<?php echo is_page('sales-report') ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url(home_url('/sales-report')); ?>">
                        <i class="fas fa-chart-bar"></i> Sales Report
                    </a>
                </li>
                <li class="<?php echo is_page('settings') ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url(home_url('/settings')); ?>">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content Area -->
    <div class="dashboard-main">
        <div class="dashboard-header">
            <div class="current-page">
                <h1><?php the_title(); ?></h1>
            </div>
            <div class="user-info">
                <?php $current_user = wp_get_current_user(); ?>
                <span>Welcome, <?php echo esc_html($current_user->display_name); ?></span>
            </div>
        </div>

        <div class="dashboard-content">
            <?php
            // Use is_page() to determine which dashboard section to load
            if ( is_page('products') ) {
                // If the current page is 'products', load the products dashboard template part
                get_template_part('template-parts/dashboard/products');
            } elseif ( is_page('settings') ) {
                // If the current page is 'settings', load the settings dashboard template part
                get_template_part('template-parts/dashboard/settings');
            } elseif ( is_page('sales-report') ) {
                // If the current page is 'sales-report', load the reports dashboard template part
                get_template_part('template-parts/dashboard/reports');
            } else {
                // For all other pages, display the default page content
                the_content();
            }
            ?>
        </div>
    </div>
</div>

<?php
get_footer();
?>
