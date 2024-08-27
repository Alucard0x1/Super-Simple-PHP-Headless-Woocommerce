document.addEventListener('DOMContentLoaded', () => {
    // JavaScript version of format_rupiah to format prices in Indonesian style
    function formatRupiah(number) {
        return 'Rp. ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ',-';
    }

    function updateCart() {
        fetch('cart-handler.php?action=view')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }

                if (!data.cart || !data.products) {
                    console.error('Cart or Product data is missing');
                    document.getElementById('cart-items').innerHTML = '<li>Cart is empty</li>';
                    document.getElementById('cart-total').textContent = formatRupiah(0);
                    return;
                }

                let totalQuantity = 0;
                let cartDetails = '';

                for (const [productId, details] of Object.entries(data.products)) {
                    const quantity = data.cart[productId] || 0;
                    totalQuantity += quantity;

                    cartDetails += `
                        <li>
                            <img src="${details.image}" alt="${details.name}">
                            <div>
                                <strong>${details.name}</strong><br>
                                <small>${formatRupiah(details.price)}</small>
                                <div>
                                    <button class="decrease-quantity" data-id="${productId}">-</button>
                                    <span>${quantity}</span>
                                    <button class="increase-quantity" data-id="${productId}">+</button>
                                </div>
                            </div>
                        </li>
                    `;
                }

                const totalPrice = Object.keys(data.cart).reduce((total, productId) => {
                    return total + (data.products[productId] ? data.products[productId].price : 0) * (data.cart[productId] || 0);
                }, 0);

                document.getElementById('cart-items').innerHTML = cartDetails;
                document.getElementById('cart-total').textContent = formatRupiah(totalPrice);

                // Attach event listeners for the + and - buttons
                document.querySelectorAll('.increase-quantity').forEach(button => {
                    button.addEventListener('click', () => {
                        const productId = button.getAttribute('data-id');
                        updateCartQuantity(productId, 'increase');
                    });
                });

                document.querySelectorAll('.decrease-quantity').forEach(button => {
                    button.addEventListener('click', () => {
                        const productId = button.getAttribute('data-id');
                        updateCartQuantity(productId, 'decrease');
                    });
                });
            })
            .catch(error => console.error('Fetch error:', error));
    }

    function updateCartQuantity(productId, action) {
        fetch(`cart-handler.php?action=${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCart(); // Refresh cart after quantity change
            } else {
                console.error('Error:', data.error || 'Unknown error');
            }
        })
        .catch(error => console.error('Fetch error:', error));
    }

    updateCart();

    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', () => {
            const productId = button.getAttribute('data-id');
            if (!productId) {
                console.error('Product ID is missing');
                return;
            }

            fetch('cart-handler.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ product_id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Product added to cart');
                    updateCart(); // Update cart after adding
                } else {
                    console.error('Error:', data.error || 'Unknown error');
                }
            })
            .catch(error => console.error('Fetch error:', error));
        });
    });

    document.getElementById('cart').addEventListener('click', (event) => {
        if (event.target.id === 'empty-cart') {
            fetch('cart-handler.php?action=empty', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Cart emptied');
                    updateCart(); // Update cart after emptying
                } else {
                    console.error('Error:', data.error || 'Unknown error');
                }
            })
            .catch(error => console.error('Fetch error:', error));
        }
    });
});
