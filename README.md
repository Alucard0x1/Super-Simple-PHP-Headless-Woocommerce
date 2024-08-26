# 🛒 Super Simple PHP Headless WooCommerce

Welcome to **Super Simple PHP Headless WooCommerce** – a minimal, no-frills solution for building a headless WooCommerce store using PHP. This application interacts directly with WooCommerce’s REST API to fetch products, manage carts, and process checkouts without relying on the traditional WordPress interface.

## 🚀 Features

- **Headless WooCommerce**: Seamless integration with WooCommerce via REST API.
- **Cart Management**: Dynamic product addition and removal from the cart.
- **Simple Checkout**: Easy-to-use checkout process with order confirmation.
- **Minimal Dependencies**: Pure PHP implementation without requiring complex frameworks.

## 🏗️ How It Works

1. **Product Fetching**: Products are fetched directly from WooCommerce using the API credentials stored in `config.php`. These products are then displayed on the main page (`index.php`).

2. **Cart Functionality**: Users can add products to the cart using the dynamic JavaScript-powered cart interface. The cart status is handled by `cart-handler.php`, which ensures product quantities and totals are updated correctly.

3. **Checkout Process**: Once ready, the user proceeds to checkout (`checkout.php`). The data is processed by `checkout-handler.php`, which sends the order information to WooCommerce.

4. **Success Page**: After successfully placing the order, the user is redirected to a confirmation page (`success.php`), where the order details are displayed.

## 🛠️ Installation

1. Clone this repository:

   ```bash
   git clone https://github.com/Alucard0x1/Super-Simple-PHP-Headless-Woocommerce.git
   ```

2. Navigate to the project directory:

   ```bash
   cd super-simple-php-headless-woocommerce
   ```

3. Configure WooCommerce API keys:

   - Open `config.php` and update the WooCommerce API credentials:
     ```php
     define('WOOCOMMERCE_API_URL', 'https://your-woocommerce-store-url/wp-json/wc/v3/');
     define('WOOCOMMERCE_CONSUMER_KEY', 'ck_your_consumer_key');
     define('WOOCOMMERCE_CONSUMER_SECRET', 'cs_your_consumer_secret');
     ```

4. Start your local PHP server:

   ```bash
   php -S localhost:8000
   ```

5. Open your browser and visit:
   ```
   http://localhost:8000
   ```

## 📂 Project Structure

```bash
.
├── cart-handler.php           # Handles cart operations
├── checkout-handler.php       # Manages checkout process
├── checkout.php               # Displays the checkout page
├── config.php                 # WooCommerce API configuration
├── index.php                  # Main page fetching and displaying products
├── success.php                # Order success confirmation page
├── script.js                  # Frontend logic for cart updates
└── logobca.png                # Logo for the checkout page

## 💡 Notes

- Ensure your WooCommerce store is running with the REST API enabled.
- API credentials must be kept secure and should not be exposed to the client side.

## ✨ Contributions

Feel free to fork this repository, submit issues, and make pull requests. Let's make this project even better together!

## 📝 License

This project is licensed under the MIT License.
```
