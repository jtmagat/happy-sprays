<?php
session_start();

// Redirect to cart if empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Calculate grand total
$grand_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $grand_total += $item['price'] * $item['quantity'];
}

// Prefill form fields if logged in
$name     = $_SESSION['customer_name'] ?? '';
$email    = $_SESSION['customer_email'] ?? '';
$street   = $_SESSION['customer_street'] ?? '';
$city     = $_SESSION['customer_city'] ?? '';
$province = $_SESSION['customer_province'] ?? '';
$postal   = $_SESSION['customer_postal'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout</title>
<style>
    body { font-family: 'Segoe UI', sans-serif; background: #fff; margin: 20px; color: #000; }
    h1 { text-align: center; margin-bottom: 20px; }
    .back-btn { display: inline-block; margin: 10px 0 20px 20px; padding: 8px 16px; border: 1px solid #000; text-decoration: none; color: #000; border-radius: 4px; }
    .back-btn:hover { background: #000; color: #fff; }
    .checkout-container { width: 80%; margin: auto; display: flex; gap: 30px; }
    .form-section, .summary-section { flex: 1; border: 1px solid #000; padding: 20px; border-radius: 4px; }
    label { display: block; margin-bottom: 8px; font-weight: bold; }
    input, textarea, select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #000; border-radius: 4px; background: none; }
    .place-order-btn { display: block; width: 100%; padding: 12px; border: 1px solid #000; background: none; color: #000; font-size: 16px; border-radius: 4px; cursor: pointer; }
    .place-order-btn:hover { background: #000; color: #fff; }
    table { width: 100%; border-collapse: collapse; border: 1px solid #000; }
    th, td { border: 1px solid #000; padding: 10px; text-align: center; }
    th { background: #f2f2f2; }
    img { width: 50px; height: 50px; object-fit: cover; border: 1px solid #000; border-radius: 4px; }
    .gcash-info { display: none; padding: 15px; border: 1px solid #000; border-radius: 6px; margin-top: 10px; font-size: 14px; background: #fafafa; }
    .gcash-info img { width: 150px; margin: 10px 0; border: 1px solid #000; border-radius: 6px; }
    /* Popup styles */
    .popup { position: fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.6); display: flex; justify-content: center; align-items: center; z-index: 9999; }
    .popup-content { background: #fff; padding: 30px; border-radius: 6px; text-align: center; }
    .popup button { margin: 10px; padding: 10px 20px; border: 1px solid #000; background: none; cursor: pointer; font-size: 14px; border-radius: 4px; }
    .popup button:hover { background: #000; color: #fff; }
    .hidden { display: none; }
</style>
<script>
    function togglePaymentDetails() {
        let payment = document.getElementById("payment").value;
        let gcashInfo = document.getElementById("gcash-info");
        if (payment === "gcash") gcashInfo.style.display = "block";
        else gcashInfo.style.display = "none";
    }

    function checkAuthBeforePlaceOrder() {
        let loggedIn = <?= isset($_SESSION['customer_id']) ? 'true' : 'false' ?>;

        if (!loggedIn) {
            document.getElementById('authPopup').classList.remove('hidden');
            <?php $_SESSION['redirect_after_login'] = 'checkout.php'; ?>
            return false;
        }

        // If logged in, submit the form normally
        document.getElementById('checkoutForm').submit();
    }
</script>
</head>
<body>
<a href="cart.php" class="back-btn">← Back to Cart</a>
<h1>Checkout</h1>

<!-- Auth Popup -->
<div id="authPopup" class="popup hidden">
    <div class="popup-content">
        <h2>Login or Create an Account</h2>
        <p>To track the status of your order, please log in or create an account.</p>
        <button onclick="location.href='customer_login.php'">Login</button>
        <button onclick="location.href='customer_register.php'">Create Account</button>
    </div>
</div>

<div class="checkout-container">
    <!-- Checkout Form -->
    <div class="form-section">
        <form id="checkoutForm" action="place_order.php" method="POST" enctype="multipart/form-data">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required value="<?= htmlspecialchars($name) ?>">

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email) ?>">

            <label for="street">Street / Barangay</label>
            <input type="text" id="street" name="street" required value="<?= htmlspecialchars($street) ?>">

            <label for="city">City / Municipality</label>
            <input type="text" id="city" name="city" required value="<?= htmlspecialchars($city) ?>">

            <label for="province">Province</label>
            <input type="text" id="province" name="province" required value="<?= htmlspecialchars($province) ?>">

            <label for="postal">Postal Code</label>
            <input type="text" id="postal" name="postal" required value="<?= htmlspecialchars($postal) ?>">

            <label for="payment">Payment Method</label>
            <select id="payment" name="payment" required onchange="togglePaymentDetails()">
                <option value="cod">Cash on Delivery</option>
                <option value="gcash">GCash</option>
            </select>

            <!-- GCash details -->
            <div id="gcash-info" class="gcash-info">
                <h3>Pay with GCash</h3>
                <p>📱 Scan this QR code to pay:</p>
                <img src="images/qrfake.png" alt="GCash QR">
                <p><strong>Number:</strong> 09451038854</p>
                <p><strong>Account Name:</strong> Happy Sprays</p>
                <br>
                <label for="gcash_ref">Upload Proof of Payment (Screenshot)</label>
                <input type="file" id="gcash_ref" name="gcash_ref" accept="image/*">
            </div>

            <button type="button" class="place-order-btn" onclick="checkAuthBeforePlaceOrder()">Place Order</button>
        </form>
    </div>

    <!-- Order Summary -->
    <div class="summary-section">
        <h2>Order Summary</h2>
        <table>
            <tr>
                <th>Image</th>
                <th>Perfume</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
            <?php foreach ($_SESSION['cart'] as $item): ?>
            <tr>
                <td><img src="images/<?= htmlspecialchars($item['image']) ?>" alt=""></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <th colspan="3">Grand Total</th>
                <th>₱<?= number_format($grand_total, 2) ?></th>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
