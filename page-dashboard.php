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
            } else {
                // For all other pages, display the default page content
                the_content();
            }
            ?>
        </div>
    </div>
</div>

<style>
/* Dashboard Layout */
.dashboard-container {
    display: flex;
    min-height: calc(100vh - var(--header-height));
    background-color: #f8f9fa;
}

/* Sidebar Styles */
.dashboard-sidebar {
    width: 250px;
    background-color: #2c3e50;
    color: #fff;
    flex-shrink: 0;
}

.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-header h3 {
    margin: 0;
    color: #fff;
}

.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-nav li {
    margin: 0;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s ease;
}

.sidebar-nav a:hover,
.sidebar-nav li.active a {
    background-color: rgba(255,255,255,0.1);
    color: #fff;
}

.sidebar-nav i {
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
}

/* Main Content Area */
.dashboard-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.dashboard-header {
    background-color: #fff;
    padding: 1rem 2rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dashboard-header h1 {
    margin: 0;
    font-size: 1.5rem;
}

.user-info {
    color: #6c757d;
}

.dashboard-content {
    padding: 2rem;
    overflow-y: auto;
    flex: 1;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-container {
        flex-direction: column;
    }

    .dashboard-sidebar {
        width: 100%;
    }

    .dashboard-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
}
</style>

<?php
// Don't include the regular footer
?>
