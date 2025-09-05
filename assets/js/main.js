/**
 * Main JavaScript file for Tutiks POS theme
 */

jQuery(document).ready(function($) {
    // Menu Toggle Functionality
    const $menuToggle = $('#menu-toggle');
    const $slideMenu = $('.slide-menu');
    const $menuOverlay = $('.menu-overlay');
    const $closeBtn = $('.slide-menu .btn-close');
    
    function toggleMenu() {
        $menuToggle.toggleClass('active');
        $slideMenu.toggleClass('active');
        $menuOverlay.toggleClass('active');
        $('body').toggleClass('menu-open');
    }

    // Toggle menu on hamburger click
    $menuToggle.on('click', toggleMenu);

    // Close menu on overlay click
    $menuOverlay.on('click', toggleMenu);

    // Close menu on close button click
    $closeBtn.on('click', toggleMenu);

    // Close menu on ESC key press
    $(document).on('keyup', function(e) {
        if (e.key === 'Escape' && $slideMenu.hasClass('active')) {
            toggleMenu();
        }
    });

    // Handle menu item clicks on mobile
    $('.slide-menu-nav a').on('click', function() {
        if (window.innerWidth < 992) { // Bootstrap's lg breakpoint
            toggleMenu();
        }
    });

    // Close menu on window resize if it goes above lg breakpoint
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth >= 992 && $slideMenu.hasClass('active')) {
                toggleMenu();
            }
        }, 250);
    });

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
