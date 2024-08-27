<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

function add_to_cart($product_id) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    if (!isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = 1;
    } else {
        $_SESSION['cart'][$product_id]++;
    }

    // Debugging the session cart
    error_log(print_r($_SESSION['cart'], true)); // This will log the cart content to the server's error log for inspection
}

function decrease_from_cart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        if ($_SESSION['cart'][$product_id] > 1) {
            $_SESSION['cart'][$product_id]--;
        } else {
            unset($_SESSION['cart'][$product_id]); // Remove item if quantity is 1 and decreased
        }
    }
}

function view_cart() {
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
    
    // Fetch product details if cart is not empty
    $productDetails = !empty($cart) ? fetch_product_prices(array_keys($cart)) : array();
    
    return array(
        'cart' => $cart,          // Return the cart data
        'products' => $productDetails // Return the product details
    );
}

function empty_cart() {
    $_SESSION['cart'] = array();
}

try {
    if ($action === 'add') {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($product_id === false) {
            throw new Exception('Invalid product ID');
        }
        add_to_cart($product_id);
        echo json_encode(array('success' => true));
    } elseif ($action === 'increase') {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($product_id === false) {
            throw new Exception('Invalid product ID');
        }
        add_to_cart($product_id); // Increment the quantity by calling add_to_cart
        echo json_encode(array('success' => true));
    } elseif ($action === 'decrease') {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($product_id === false) {
            throw new Exception('Invalid product ID');
        }
        decrease_from_cart($product_id); // Decrease the quantity or remove if it's 1
        echo json_encode(array('success' => true));
    } elseif ($action === 'view') {
        $cart = view_cart();
        $response = array(
            'cart' => $cart['cart'],  // This should return just the cart array
            'products' => $cart['products']  // This should return the product details
        );
        echo json_encode($response);
    } elseif ($action === 'empty') {
        empty_cart();
        echo json_encode(array('success' => true));
    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
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
    
    // Ensure products are in the right format
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response');
    }

    $productDetails = array();
    foreach ($products as $product) {
        $productDetails[$product['id']] = array(
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['images'][0]['src'] ?? 'placeholder.jpg' // Default to a placeholder if no image is available
        );
    }

    return $productDetails;
}
