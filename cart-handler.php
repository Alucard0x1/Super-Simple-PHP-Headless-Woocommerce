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

    // Debugging the session cart (remove in production)
    error_log(print_r($_SESSION['cart'], true)); 
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
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    if ($action === 'add' || $action === 'increase') {
        if ($product_id === false) {
            throw new Exception('Invalid product ID');
        }
        add_to_cart($product_id);
        echo json_encode(array('success' => true));
    } elseif ($action === 'decrease') {
        if ($product_id === false) {
            throw new Exception('Invalid product ID');
        }
        decrease_from_cart($product_id);
        echo json_encode(array('success' => true));
    } elseif ($action === 'view') {
        $cart = view_cart();
        echo json_encode(array(
            'cart' => $cart['cart'],
            'products' => $cart['products']
        ));
    } elseif ($action === 'empty') {
        empty_cart();
        echo json_encode(array('success' => true));
    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log($e->getMessage()); // Log the actual error message for debugging, do not expose it to the client
    echo json_encode(array('error' => 'An error occurred. Please try again later.'));
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
    if (!empty($products['products'])) {
        foreach ($products['products'] as $product) {
            $productDetails[$product['id']] = array(
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['images'][0]['src'] ?? 'placeholder.jpg' // Default to a placeholder if no image is available
            );
        }
    }

    return $productDetails;
}
