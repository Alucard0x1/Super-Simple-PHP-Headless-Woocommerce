<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

function place_order($order_data) {
    // For simplicity, we'll just simulate order placement
    // In a real application, you'd save order details to the database
    return true;
}

function sanitize_input($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if the cart is empty
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
        if (empty($cart)) {
            header('Location: index.php?error=cart_empty');
            exit();
        }

        // Sanitize inputs
        $name = sanitize_input($_POST['name'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $address = sanitize_input($_POST['address'] ?? '');

        if (!$name || !$email || !$address) {
            throw new Exception('Invalid input');
        }

        // Fetch product prices for all items in the cart
        $productDetails = fetch_product_prices(array_keys($cart));

        // Calculate the total price based on the cart contents
        $total = 0;
        foreach ($cart as $product_id => $quantity) {
            if (isset($productDetails[$product_id])) {
                $total += $quantity * $productDetails[$product_id]['price'];
            }
        }

        // Create order data
        $order_data = array(
            'name' => $name,
            'email' => $email,
            'address' => $address,
            'cart' => $cart,
            'total' => $total
        );

        // Place the order
        if (place_order($order_data)) {
            // Clear the cart
            $_SESSION['cart'] = array();

            // Store order details in session
            $_SESSION['order_data'] = $order_data;

            // Redirect to success page
            header('Location: success.php');
            exit();
        } else {
            throw new Exception('Order placement failed');
        }
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    // Handle exceptions here if needed
    header('Location: index.php?error=' . urlencode($e->getMessage()));
    exit();
}

function fetch_product_prices($product_ids) {
    if (empty($product_ids)) {
        return array();
    }

    $url = WC_API_URL . 'products?include=' . implode(',', $product_ids);
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Basic ' . base64_encode(WC_CONSUMER_KEY . ':' . WC_CONSUMER_SECRET)
    ));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        throw new Exception('Error fetching product details');
    }

    curl_close($ch);
    $products = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response');
    }

    $productDetails = array();
    foreach ($products as $product) {
        $productDetails[$product['id']] = array(
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['images'][0]['src'] ?? 'placeholder.jpg' // Default to placeholder if no image
        );
    }

    return $productDetails;
}
