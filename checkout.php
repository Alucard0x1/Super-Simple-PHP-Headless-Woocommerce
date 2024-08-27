<?php
session_start();
include 'config.php';

// Function to format price in Indonesian style
function format_rupiah($number) {
    return 'Rp. ' . number_format($number, 0, ',', '.') . ',-';
}

// Fetch cart items
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$products = array();

if (!empty($cart)) {
    $product_ids = array_keys($cart);
    $url = WC_API_URL . 'products?include=' . implode(',', $product_ids);
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Basic ' . base64_encode(WC_CONSUMER_KEY . ':' . WC_CONSUMER_SECRET)
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    $products = json_decode($response, true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Tailwind configuration */
        body {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body class="font-sans bg-gray-100">

    <div class="container mx-auto p-5">
        <h1 class="text-4xl text-center font-bold mb-8">Checkout</h1>

        <!-- Cart Summary -->
        <div id="cart-summary" class="bg-white rounded-lg shadow-lg p-8 mb-10">
            <?php if (!empty($cart)): ?>
                <h2 class="text-2xl font-semibold mb-5">Your Cart</h2>
                <table class="min-w-full text-left table-auto">
                    <thead>
                        <tr class="text-sm text-gray-600 border-b">
                            <th class="pb-3">Product</th>
                            <th class="pb-3">Quantity</th>
                            <th class="pb-3 text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($products as $product): ?>
                            <tr class="text-gray-700">
                                <td class="py-4">
                                    <div class="flex items-center space-x-4">
                                        <img src="<?php echo htmlspecialchars($product['images'][0]['src'], ENT_QUOTES, 'UTF-8'); ?>" class="w-16 h-16 object-cover rounded-lg" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <span><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </td>
                                <td class="py-4"><?php echo htmlspecialchars($cart[$product['id']], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="py-4 text-right"><?php echo format_rupiah($product['price'] * $cart[$product['id']]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="text-lg font-bold">
                            <td class="py-4" colspan="2">Total:</td>
                            <td class="py-4 text-right"><?php
                                $total = 0;
                                foreach ($products as $product) {
                                    $total += $product['price'] * $cart[$product['id']];
                                }
                                echo format_rupiah($total);
                            ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-lg text-center text-gray-500">Your cart is empty.</p>
            <?php endif; ?>
        </div>

        <!-- Checkout Form -->
        <div id="checkout-form" class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-semibold mb-6 text-center">Enter Your Details</h2>
            <form action="checkout-handler.php" method="post">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Name:</label>
                    <input type="text" id="name" name="name" class="w-full border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                    <input type="email" id="email" name="email" class="w-full border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <div class="mb-4">
                    <label for="address" class="block text-sm font-medium text-gray-700">Address:</label>
                    <input type="text" id="address" name="address" class="w-full border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>
                <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold p-4 rounded-lg transition-colors">Place Order</button>
            </form>
        </div>

        <div class="mt-8 text-center">
            <a href="index.php" class="text-blue-600 hover:text-blue-800">← Back to Products</a>
        </div>
    </div>

</body>
</html>
