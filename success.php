<?php
session_start();
include 'config.php';

// Retrieve order data from session
$order_data = $_SESSION['order_data'] ?? array();

if (empty($order_data)) {
    echo 'No order data available.';
    exit();
}

// Define your bank account details here
$bank_account_info = [
    'bank_name' => 'BCA Bank',
    'account_name' => 'Example Account',
    'account_number' => '123456789'
];

// Fetch product details
$product_ids = array_keys($order_data['cart']);
$product_details = fetch_product_details($product_ids);

function fetch_product_details($product_ids) {
    $url = WC_API_URL . 'products?include=' . implode(',', $product_ids);
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Basic ' . base64_encode(WC_CONSUMER_KEY . ':' . WC_CONSUMER_SECRET)
    ));

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Function to format price in Indonesian style
function format_rupiah($number) {
    return 'Rp. ' . number_format($number, 0, ',', '.') . ',-';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <div class="container mx-auto p-5">
        <!-- Success Message -->
        <h1 class="text-4xl font-bold text-green-600 text-center mb-8">Order Success</h1>

        <!-- Order Details -->
        <div class="bg-white shadow-lg rounded-lg p-8 mb-8">
            <h2 class="text-2xl font-semibold mb-6">Order Details</h2>
            <p class="mb-4"><strong>Name:</strong> <?php echo htmlspecialchars($order_data['name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="mb-4"><strong>Email:</strong> <?php echo htmlspecialchars($order_data['email'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="mb-4"><strong>Address:</strong> <?php echo htmlspecialchars($order_data['address'], ENT_QUOTES, 'UTF-8'); ?></p>

            <!-- Cart Items -->
            <h3 class="text-xl font-semibold mb-4">Cart Items</h3>
            <div class="space-y-4">
                <?php foreach ($product_details as $product): ?>
                    <div class="flex items-center space-x-4 border-b border-gray-200 pb-4">
                        <img src="<?php echo htmlspecialchars($product['images'][0]['src'], ENT_QUOTES, 'UTF-8'); ?>" class="w-16 h-16 object-cover rounded-lg" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="flex-grow">
                            <p class="text-lg font-medium"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p>Quantity: <?php echo htmlspecialchars($order_data['cart'][$product['id']], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <p class="text-lg font-semibold"><?php echo format_rupiah($product['price'] * $order_data['cart'][$product['id']]); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Total Price -->
            <p class="text-right text-xl font-bold mt-6">Total: <?php echo format_rupiah($order_data['total']); ?></p>
        </div>

        <!-- Bank Details -->
        <div class="bg-white shadow-lg rounded-lg p-8 mb-8">
            <h3 class="text-xl font-semibold mb-6">Bank Transfer Information</h3>
            <div class="flex items-center">
                <img src="logobca.png" alt="BCA Bank Logo" class="w-20 h-auto mr-8">
                <div class="flex-grow">
                    <p class="text-lg"><strong>Bank Name:</strong> BCA Bank</p>
                    <p class="text-lg"><strong>Account Name:</strong> Example Account</p>
                    <p class="text-lg"><strong>Account Number:</strong> 123456789</p>
                </div>
            </div>
        </div>

        <!-- Back to Products Button -->
        <div class="text-center">
            <a href="index.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">← Back to Products</a>
        </div>
    </div>

</body>
</html>
