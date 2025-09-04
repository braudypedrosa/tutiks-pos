jQuery(document).ready(function($) {
    // Initialize SweetAlert Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // Cart data structure
    let cart = {
        items: {},
        total: 0
    };
    
    // Get QR code URL from WordPress data
    const qrCodeUrl = tutiksPOS.qrCodeUrl;
    
    // Debug logging
    if (tutiksPOS.debug) {
        console.log('tutiksPOS object:', tutiksPOS);
        console.log('QR Code URL:', qrCodeUrl);
        console.log('QR Code URL type:', typeof qrCodeUrl);
        console.log('QR Code URL length:', qrCodeUrl ? qrCodeUrl.length : 0);
    }

    // Category and search functionality
    let currentCategory = 'all';
    let currentSearch = '';

    function filterProducts() {
        $('.product-item').each(function() {
            const $product = $(this);
            const $container = $product.closest('.col-sm-6');
            const productName = $product.find('.card-title').text().toLowerCase();
            const productCategory = $product.data('category');
            
            const matchesSearch = productName.includes(currentSearch);
            const matchesCategory = currentCategory === 'all' || productCategory === currentCategory;
            
            $container.toggle(matchesSearch && matchesCategory);
        });
    }

    // Search functionality
    $('#pos-search').on('input', function() {
        currentSearch = $(this).val().toLowerCase();
        filterProducts();
    });

    // Category filtering
    $('.category-pills .btn').on('click', function() {
        $('.category-pills .btn').removeClass('active');
        $(this).addClass('active');
        
        currentCategory = $(this).data('category');
        filterProducts();
    });

    // Add product to cart
    $('.product-item').on('click', function(e) {
        // Don't trigger if clicking on a button
        if ($(e.target).is('button') || $(e.target).closest('button').length) {
            return;
        }
        addToCart($(this));
    });

    // Add to cart button click
    $('.product-item button').on('click', function(e) {
        e.preventDefault();
        addToCart($(this).closest('.product-item'));
    });

    function addToCart($product) {
        const productId = $product.data('id');
        const productName = $product.find('.card-title').text();
        const productPrice = parseFloat($product.data('price')).toFixed(2);
        const isNewItem = !cart.items[productId];
        
        if (cart.items[productId]) {
            cart.items[productId].quantity = parseInt(cart.items[productId].quantity) + 1;
        } else {
            cart.items[productId] = {
                name: productName,
                price: productPrice,
                quantity: 1
            };
        }
        updateCartTotals();
        updateCartDisplay();

        // Show toast notification
        Toast.fire({
            icon: 'success',
            title: isNewItem ? `Added ${productName} to cart` : `Increased ${productName} quantity`
        });
    }

    // Update cart total
    function updateCartTotals() {
        // Calculate total from items
        const newTotal = Object.values(cart.items).reduce((total, item) => {
            return total + (parseFloat(item.price) * parseInt(item.quantity));
        }, 0);
        
        // Update cart total
        cart.total = parseFloat(newTotal.toFixed(2));
    }

    // Update cart display
    function updateCartDisplay() {
        const $cartItems = $('.cart-items');
        $cartItems.empty();

        // Add each item to the cart display
        Object.entries(cart.items).forEach(([productId, item]) => {
            const $cartItem = $(`
                <div class="cart-item mb-3" data-id="${productId}">
                    <div class="card">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">${item.name}</h6>
                                    <span class="text-primary">₱${(item.price * item.quantity).toFixed(2)}</span>
                                </div>
                                <div class="quantity-controls d-flex align-items-center">
                                    <div class="btn-group btn-group-sm me-2">
                                        <button class="quantity-decrease btn btn-outline-secondary">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" disabled>${item.quantity}</button>
                                        <button class="quantity-increase btn btn-outline-secondary">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <button class="remove-item btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);

            $cartItems.append($cartItem);
        });

        // Update total display
        $('.total .amount').text('₱' + cart.total.toFixed(2));

        // Update cart buttons state
        $('.clear-cart, .checkout').prop('disabled', Object.keys(cart.items).length === 0);
    }

    // Quantity controls
    $(document).on('click', '.quantity-increase', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $item = $(this).closest('.cart-item');
        const productId = $item.data('id');
        cart.items[productId].quantity = parseInt(cart.items[productId].quantity) + 1;
        updateCartTotals();
        updateCartDisplay();
    });

    $(document).on('click', '.quantity-decrease', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $item = $(this).closest('.cart-item');
        const productId = $item.data('id');
        const currentQty = parseInt(cart.items[productId].quantity);
        
        if (currentQty > 1) {
            cart.items[productId].quantity = currentQty - 1;
        } else {
            delete cart.items[productId];
        }
        updateCartTotals();
        updateCartDisplay();
    });

    $(document).on('click', '.remove-item', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $item = $(this).closest('.cart-item');
        const productId = $item.data('id');
        delete cart.items[productId];
        updateCartTotals();
        updateCartDisplay();
    });

    // Clear cart
    $('.clear-cart').on('click', function() {
        if (Object.keys(cart.items).length === 0) {
            Toast.fire({
                icon: 'info',
                title: 'Cart is already empty'
            });
            return;
        }

        Swal.fire({
            title: 'Clear Cart?',
            text: 'Are you sure you want to remove all items?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, clear it!'
        }).then((result) => {
            if (result.isConfirmed) {
                cart.items = {};
                updateCartTotals();
                updateCartDisplay();
                Toast.fire({
                    icon: 'success',
                    title: 'Cart has been cleared'
                });
            }
        });
    });

    // Checkout
    $('.checkout').on('click', function() {
        if (Object.keys(cart.items).length === 0) {
            Toast.fire({
                icon: 'error',
                title: 'Cart is empty!'
            });
            return;
        }

        // Create order summary HTML
        const orderSummaryHtml = Object.values(cart.items).map(item => {
            const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
            return `
                <div class="d-flex justify-content-between mb-2">
                    <span>${item.name} × ${item.quantity}</span>
                    <span>₱${itemTotal.toFixed(2)}</span>
                </div>
            `;
        }).join('') + `
            <hr>
            <div class="d-flex justify-content-between fw-bold">
                <span>Total:</span>
                <span>₱${cart.total.toFixed(2)}</span>
            </div>
        `;

        // First step: Show order summary and select payment method
        Swal.fire({
            title: 'Order Summary',
            html: `
                <div class="text-start mb-4">${orderSummaryHtml}</div>
                <div class="payment-method-selection">
                    <h6 class="mb-3">Select Payment Method</h6>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-primary payment-method-btn" data-method="cash">
                            <i class="fas fa-money-bill-wave me-2"></i>Cash
                        </button>
                        <button type="button" class="btn btn-outline-primary payment-method-btn" data-method="qr">
                            <i class="fas fa-qrcode me-2"></i>QR Code
                        </button>
                    </div>
                </div>
            `,
            showCancelButton: true,
            showConfirmButton: false,
            cancelButtonColor: '#6c757d',
            didOpen: () => {
                // Add click handlers for payment method buttons
                $('.payment-method-btn').on('click', function() {
                    const paymentMethod = $(this).data('method');
                    
                    // Update button states
                    $('.payment-method-btn').removeClass('active');
                    $(this).addClass('active');
                    
                    // Show confirmation dialog based on payment method
                    Swal.fire({
                        title: 'Confirm Payment',
                        html: `
                            <div class="text-start mb-4">
                                <p class="mb-2">Total Amount: <strong>₱${cart.total.toFixed(2)}</strong></p>
                                <p class="mb-0">Payment Method: <strong>${paymentMethod === 'cash' ? 'Cash' : 'QR Code'}</strong></p>
                            </div>
                            ${paymentMethod === 'qr' ? `
                                <div class="qr-code-display text-center mb-4">
                                    ${qrCodeUrl ? `
                                        <img src="${qrCodeUrl}" alt="Payment QR Code" style="max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    ` : `
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            QR code not found. Please set up QR code in settings.
                                        </div>
                                    `}
                                </div>
                                <div class="qr-instructions alert alert-info text-start">
                                    <i class="fas fa-info-circle me-2"></i>
                                    ${qrCodeUrl ? 'Please scan the QR code and confirm once payment is complete.' : 'Please set up your QR code in the settings page.'}
                                </div>
                            ` : ''}
                        `,
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: paymentMethod === 'cash' ? 'Payment Received' : 'Payment Confirmed'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Process the order
                            const orderData = {
                                items: cart.items,
                                total: cart.total,
                                paymentMethod: paymentMethod,
                                timestamp: new Date().toISOString()
                            };
                            
                            // Save order to database
                            $.ajax({
                                url: tutiksPOS.ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'tutiks_pos_save_order',
                                    nonce: tutiksPOS.nonce,
                                    order_data: JSON.stringify(orderData)
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Clear cart after successful checkout
                                        cart.items = {};
                                        updateCartTotals();
                                        updateCartDisplay();
                                        
                                        // Show success message with order number
                                        Swal.fire({
                                            title: 'Success!',
                                            html: `
                                                <div class="text-center">
                                                    <p class="mb-2">Order #${response.data.order_id} has been processed successfully.</p>
                                                    <div class="alert alert-success">
                                                        <i class="fas fa-check-circle me-2"></i>
                                                        Payment received: ₱${orderData.total.toFixed(2)}
                                                    </div>
                                                </div>
                                            `,
                                            icon: 'success',
                                            confirmButtonColor: '#198754'
                                        });
                                    } else {
                                        // Show error message
                                        Swal.fire({
                                            title: 'Error!',
                                            text: response.data || 'Failed to save order. Please try again.',
                                            icon: 'error',
                                            confirmButtonColor: '#dc3545'
                                        });
                                    }
                                },
                                error: function() {
                                    // Show error message
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'Failed to save order. Please check your connection and try again.',
                                        icon: 'error',
                                        confirmButtonColor: '#dc3545'
                                    });
                                }
                            });
                        }
                    });
                });
            }
        });
    });

    // Initialize cart display
    updateCartDisplay();
});
