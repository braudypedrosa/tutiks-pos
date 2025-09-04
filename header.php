<?php
/**
 * The header for our theme
 *
 * @package TutiksPOS
 * @since 1.0.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="site-wrapper">
    <!-- Slide Menu Overlay -->
    <div class="menu-overlay"></div>

    <!-- Main Header -->
    <header class="site-header bg-white shadow-sm">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <div class="site-branding">
                    <?php
                    if (has_custom_logo()) {
                        the_custom_logo();
                    } else {
                        ?>
                        <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
                            <?php bloginfo('name'); ?>
                        </a>
                        <?php
                    }
                    ?>
                </div>

                <!-- Hamburger Button -->
                <button id="menu-toggle" class="navbar-toggler" type="button">
                    <i class="fas fa-bars"></i>
                    <span class="toggler-text ms-2">Menu</span>
                </button>
            </div>
        </nav>
    </header>

    <!-- Slide Menu -->
    <div class="slide-menu">
        <div class="slide-menu-header">
            <h5 class="mb-0">Menu</h5>
            <button class="btn-close"></button>
        </div>
        <div class="slide-menu-content">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_class'     => 'slide-menu-nav',
                'container'      => false,
                'fallback_cb'    => function() {
                    echo '<ul class="slide-menu-nav">';
                    echo '<li><a href="' . esc_url(admin_url('nav-menus.php')) . '">Set up menu</a></li>';
                    echo '</ul>';
                },
            ));
            ?>
        </div>
        <?php if (is_user_logged_in()) : ?>
            <div class="slide-menu-footer">
                <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="btn btn-outline-danger w-100">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="site-content"><!-- Closed in footer.php -->