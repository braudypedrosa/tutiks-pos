<?php
/**
 * The main template file
 *
 * @package TutiksPOS
 * @since 1.0.0
 */

get_header();
?>

<main class="site-main">
    <div class="container">
        <?php
        if (is_user_logged_in()) {
            // POS system content will go here in future updates
            ?>
            <div class="pos-container">
                <h1><?php esc_html_e('Welcome to Tutiks POS', 'tutiks-pos'); ?></h1>
                <p><?php esc_html_e('POS system interface coming soon.', 'tutiks-pos'); ?></p>
            </div>
            <?php
        } else {
            ?>
            <div class="login-required">
                <h1><?php esc_html_e('Login Required', 'tutiks-pos'); ?></h1>
                <p><?php esc_html_e('Please log in to access the POS system.', 'tutiks-pos'); ?></p>
                <?php wp_login_form(); ?>
            </div>
            <?php
        }
        ?>
    </div>
</main>

<?php
get_footer();
