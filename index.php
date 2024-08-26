<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

// Function to format price in Indonesian style
function format_rupiah($number) {
    return 'Rp. ' . number_format($number, 0, ',', '.') . ',-';
}

// Fetch products from WooCommerce API using cURL
function fetch_products() {
    $url = WC_API_URL . 'products';
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Basic ' . base64_encode(WC_CONSUMER_KEY . ':' . WC_CONSUMER_SECRET)
    ));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return array();
    }

    curl_close($ch);
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return array();
    }
    return $data;
}

$products = fetch_products();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Headless WooCommerce Online Store</title>
    <link rel="stylesheet" href="style.css">
    <style>
html, body {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    height: 100%;
    width: 100%;
    overflow-x: hidden; /* Avoid horizontal scroll */
}

body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
}

/* Header styling */
header {
    background-color: #007bff;
    color: white;
    padding: 20px;
    text-align: center;
    font-size: 1.5rem;
    font-weight: bold;
}

header p {
    font-size: 1rem;
    margin-top: 10px;
}

/* Navigation styling */
nav {
    background-color: #0056b3;
    color: white;
    display: flex;
    justify-content: center;
    padding: 10px 0;
}

nav a {
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    font-size: 1rem;
}

nav a:hover {
    background-color: #004494;
}

/* Main content layout */
.main-container {
    display: flex;
    flex-direction: row;
    width: 100%;
    height: 100%;
    box-sizing: border-box;
}

/* Product grid styling */
.products-section {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    box-sizing: border-box;
}

#products {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Changed to auto-fit */
    gap: 20px;
}

.product {
    border: 1px solid #ccc;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    padding: 15px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.product img {
    width: 100%;
    height: auto;
}

.product h2 {
    font-size: 1.2rem;
    margin: 0 0 10px;
}

.button-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.product p {
    font-size: 1rem;
}

.product button {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 10px;
    cursor: pointer;
    border-radius: 5px;
    font-size: 1rem;
}

.product button:hover {
    background-color: #218838;
}

/* Sticky cart sidebar styling */
.cart-section {
    width: 25%;
    padding: 20px;
    background: #fff;
    border-left: 1px solid #ccc;
    box-sizing: border-box;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

#cart h2 {
    margin-top: 0;
    font-size: 1.5rem;
}

#cart-items li {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

#cart-items img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 5px;
    margin-right: 10px;
}

#cart-total {
    font-weight: bold;
    font-size: 1.2rem;
    margin-top: 20px;
}

/* Optional: Adjust the margin on the buttons to reduce space between them */
#empty-cart, #checkout-button {
    padding: 10px 0; /* Adjusted for uniform height */
    width: 100%; /* Set buttons to take up full width */
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-size: 1rem;
    text-align: center;
    text-decoration: none; /* Removes underline */
    display: inline-block;
    margin-bottom: 5px; /* Reduce the space between the two buttons */
}
#empty-cart {
    background-color: #dc3545; /* Red color for Empty Cart button */
}

#empty-cart:hover {
    background-color: #c82333;
}

#checkout-button {
    background-color: #007bff; /* Blue color for Checkout button */
}

#checkout-button:hover {
    background-color: #0056b3;
}

/* Align buttons properly in the cart */
.cart-section {
    display: flex;
    flex-direction: column;
    justify-content: flex-start; /* Aligns everything to the top */
    align-items: center; /* Center the buttons horizontally */
    padding-bottom: 20px; /* Keep the padding at the bottom for spacing */
}



/* Accordion Styles for Cart on Tablet and Mobile */
.cart-header {
    display: none;
    background-color: #007bff;
    color: white;
    padding: 15px;
    font-size: 1.5rem;
    cursor: pointer;
    justify-content: space-between;
    align-items: center;
    user-select: none;
}

.cart-header h2 {
    margin: 0;
}

.cart-header .arrow-icon {
    font-size: 1.5rem;
    transition: transform 0.3s ease;
}

.cart-header.collapsed .arrow-icon {
    transform: rotate(90deg);
}

.cart-section.accordion-hidden {
    display: none;
}

/* Footer Styling */
footer {
    background-color: #007bff;
    color: white;
    padding: 20px;
    text-align: center;
    font-size: 1rem;
    position: relative;
    bottom: 0;
    width: 100%;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .main-container {
        flex-direction: column; /* Stacks cart below products */
    }

    .cart-section {
        width: 100%;
        border-left: none;
        border-top: 1px solid #ccc;
    }

    .cart-header {
        display: flex;
    }
}

@media (max-width: 768px) {
    #products {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .product h2 {
        font-size: 1rem;
    }

    .product button {
        font-size: 0.9rem;
    }

    .cart-section {
        padding: 10px;
    }

    #cart h2 {
        font-size: 1.2rem;
    }
}

@media (max-width: 480px) {
    #products {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }

    .product h2 {
        font-size: 0.9rem;
    }

    .product button {
        font-size: 0.8rem;
        padding: 8px;
    }

    .cart-section {
        padding: 10px;
    }

    #cart h2 {
        font-size: 1rem;
    }

    #cart-items img {
        width: 40px;
        height: 40px;
    }

    #cart-total {
        font-size: 1rem;
    }
}

    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <h1>Headless WooCommerce Online Store</h1>
        <p>Best products at unbeatable prices!</p>
    </header>

    <!-- Navigation Bar -->
    <nav>
        <a href="#products">Products</a>
        <a href="#">Special Offers</a>
        <a href="#">Contact Us</a>
    </nav>

    <!-- Main Content with Flex Layout -->
    <div class="main-container">
        <!-- Product Section -->
        <div class="products-section">
            <h1 style="text-align: center; margin-top: 20px;">Products</h1>
            <div id="products">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product">
                            <?php 
                            $image_url = !empty($product['images'][0]['src']) ? $product['images'][0]['src'] : 'placeholder.jpg'; 
                            ?>
                            <img src="<?php echo htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'] ?? 'Product Image', ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">

                            <h2><?php echo htmlspecialchars($product['name'] ?? 'Product Name', ENT_QUOTES, 'UTF-8'); ?></h2>
                            <div class="button-container">
                                <p><?php echo format_rupiah($product['price'] ?? 0); ?></p>
                                <button class="add-to-cart" data-id="<?php echo htmlspecialchars($product['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">Add to Cart</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No products available.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Accordion Cart Section for Tablet and Mobile -->
        <div class="cart-header">
            <h2>Cart</h2>
            <span class="arrow-icon">&#9654;</span>
        </div>

        <!-- Sticky Cart Section -->
        <div class="cart-section" id="cart">
            <h2>Cart</h2>
            <ul id="cart-items"></ul>
            <p>Total: <span id="cart-total">Rp. 0,-</span></p>
            <button id="empty-cart">Empty Cart</button>
            <a id="checkout-button" href="checkout.php">Checkout</a>
        </div>
    </div>

    <!-- Footer Section -->
    <footer>
        <p>&copy; 2024 Headless WooCommerce Online Store. All rights reserved.</p>
    </footer>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const cartHeader = document.querySelector('.cart-header');
    const cartSection = document.querySelector('.cart-section');
    const arrowIcon = document.querySelector('.cart-header .arrow-icon');

    // Handle accordion behavior based on screen size
    function handleAccordion() {
        if (window.innerWidth <= 1024) {
            cartHeader.style.display = 'flex';
            cartSection.classList.add('accordion-hidden');
        } else {
            cartHeader.style.display = 'none';
            cartSection.classList.remove('accordion-hidden');
        }
    }

    cartHeader.addEventListener('click', function () {
        cartSection.classList.toggle('accordion-hidden');
        cartHeader.classList.toggle('collapsed');
    });

    window.addEventListener('resize', handleAccordion);
    handleAccordion(); // Initial call
});


    </script>
    <script src="script.js" defer></script>
</body>
</html>