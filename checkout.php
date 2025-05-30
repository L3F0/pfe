<?php
session_start();

$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$isLoggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$username = $isLoggedIn ? htmlspecialchars($_SESSION["username"]) : "";
$userEmail = ""; // Initialize to prevent potential undefined variable errors

if (!$isLoggedIn) {
    $_SESSION['redirect_url'] = 'checkout.php'; // Store the intended destination
    header("Location: signin.php");
    exit;
}
$userEmail = $isLoggedIn && isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : ""; // Assuming email is stored in session

// If cart is empty, redirect to product page or cart page
if (empty($cartItems)) {
    header("Location: product.php"); // Or cart.php with an empty message
    exit;
}

$cartSubtotal = 0;
foreach ($cartItems as $item) {
    $cartSubtotal += $item['price'] * $item['quantity'];
}
$shippingCost = 0; // For "Cash on Delivery", shipping might be free or calculated later. Let's assume 0 for now.
$cartTotal = $cartSubtotal + $shippingCost;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - LEFO</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="Slaytanic.css">
    <style>
        .logo {
            font-family: 'Slaytanic', sans-serif;
        }
        .cart-icon-container {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .cart-item-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #d88405;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }
        .main-checkout {
            padding-top: 20px;
            color: white;
        }
        .checkout-title {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5em;
            font-weight: bold;
        }
        .checkout-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            max-width: 1000px;
            margin: 0 auto 40px auto;
            padding: 0 20px;
        }
        .checkout-form-section, .order-summary-section {
            background-color: rgba(20, 20, 20, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .checkout-form-section {
            flex: 2; /* Takes more space */
            min-width: 300px;
        }
        .order-summary-section {
            flex: 1;
            min-width: 280px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #ccc;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #555;
            background-color: #333;
            color: #fff;
            font-size: 1em;
            box-sizing: border-box;
        }
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        .payment-method, .country-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: rgba(30,30,30,0.5);
            border-radius: 5px;
        }
        .payment-method h3, .country-info h3 {
            margin-top: 0;
            color: #d88405;
        }
        .order-summary-section h3 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #d88405;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 10px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.9em;
        }
        .summary-item .item-name {
            max-width: 70%;
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }
        .btn-place-order {
            background-color: #d88405;
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.2em;
            font-weight: bold;
            text-transform: uppercase;
            transition: background 0.3s ease, transform 0.2s ease;
            display: block;
            width: 100%;
            margin-top: 20px;
        }
        .btn-place-order:hover {
            background-color: #be7508;
            transform: scale(1.02);
        }
    </style>
</head>
<body>
    <img class="image-gradient" src="gradient.png" alt="gradient">
    <div class="layer-blur"></div>

    <div class="container">
        <header>
           <h1 data-aos="fade-down" data-aos-duration="1500" class="logo">lEFo</h1>
            <nav>
                <a data-aos="fade-down" data-aos-duration="1500" href="index.php" title="Home" class="nav-icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </a>
                <a data-aos="fade-down" data-aos-duration="1500" href="about.html">ABOUT US</a>
                <a data-aos="fade-down" data-aos-duration="2000" href="product.php">PRODUCTS</a>
                <a data-aos="fade-down" data-aos-duration="2500" href="submit-design.php">SUBMIT YOUR DESIGN</a>
                <a data-aos="fade-down" data-aos-duration="3000" href="cart.php" title="Cart" class="cart-icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    <span class="cart-item-count" id="cart-count-badge">0</span>
                </a>
            </nav>
            <?php if ($isLoggedIn): ?>
                <span style="color: white; margin-right: 15px; font-size: 0.9em;">Hi, <?php echo $username; ?>!</span>
                <a href="logout.php" data-aos="fade-down" data-aos-duration="1500" class="btn-signin" style="text-decoration: none;">LOGOUT</a>
            <?php else: ?>
                <button data-aos="fade-down" data-aos-duration="1500" class="btn-signin">SIGN IN</button>
            <?php endif; ?>
        </header>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div style="background-color: rgba(255, 0, 0, 0.3); color: white; padding: 10px; margin: 20px auto; max-width: 600px; border-radius: 5px; text-align: center;">
                <?php 
                    echo htmlspecialchars($_SESSION['error_message']); 
                    unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div style="background-color: rgba(0, 255, 0, 0.3); color: white; padding: 10px; margin: 20px auto; max-width: 600px; border-radius: 5px; text-align: center;">
                <?php 
                    echo htmlspecialchars($_SESSION['success_message']); 
                    unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <main class="main-checkout">
            <h2 data-aos="fade-up" class="checkout-title">Checkout</h2>
            <div class="checkout-container" data-aos="fade-up" data-aos-delay="200">
                <section class="checkout-form-section">
                    <h3>Shipping Details</h3>
                    <form id="checkoutForm" action="process_order.php" method="POST">
                        <div class="form-group">
                            <label for="customer_name">Full Name</label>
                            <input type="text" id="customer_name" name="customer_name" value="<?php echo $isLoggedIn ? $username : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="customer_email">Email Address</label>
                            <input type="email" id="customer_email" name="customer_email" value="<?php echo $userEmail; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="customer_phone">Phone Number</label>
                            <input type="tel" id="customer_phone" name="customer_phone" placeholder="e.g., 06XXXXXXXX" required>
                        </div>
                        <div class="form-group">
                            <label for="shipping_address_line1">Address Line 1</label>
                            <input type="text" id="shipping_address_line1" name="shipping_address_line1" placeholder="Street address, P.O. box, etc." required>
                        </div>
                        <div class="form-group">
                            <label for="shipping_address_line2">Address Line 2 (Optional)</label>
                            <input type="text" id="shipping_address_line2" name="shipping_address_line2" placeholder="Apartment, suite, unit, building, floor, etc.">
                        </div>
                        <div class="form-group">
                            <label for="shipping_city">City</label>
                            <input type="text" id="shipping_city" name="shipping_city" required>
                        </div>
                        <div class="form-group">
                            <label for="shipping_state">State / Province (Optional)</label>
                            <input type="text" id="shipping_state" name="shipping_state">
                        </div>
                        <div class="form-group">
                            <label for="shipping_zip_code">ZIP / Postal Code</label>
                            <input type="text" id="shipping_zip_code" name="shipping_zip_code" required>
                        </div>
                        <div class="country-info">
                            <h3>Country</h3>
                            <p>Morocco</p>
                            <input type="hidden" name="shipping_country" value="Morocco">
                        </div>
                        <div class="payment-method">
                            <h3>Payment Method</h3>
                            <p>Cash on Delivery</p>
                            <input type="hidden" name="payment_method" value="cash_on_delivery">
                        </div>
                        <div class="form-group">
                            <label for="notes">Order Notes (Optional)</label>
                            <textarea id="notes" name="notes" placeholder="Any special instructions for your order..."></textarea>
                        </div>
                        <button type="submit" class="btn-place-order">Place Order</button>
                    </form>
                </section>

                <section class="order-summary-section">
                    <h3>Order Summary</h3>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <span class="item-name"><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                            <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($cartSubtotal, 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Shipping</span>
                        <span><?php echo $shippingCost > 0 ? '$'.number_format($shippingCost, 2) : 'Free (COD)'; ?></span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>$<?php echo number_format($cartTotal, 2); ?></span>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
      AOS.init({
        once: true,
        duration: 800,
      });

      const signinBtn = document.querySelector('.btn-signin');
      if (signinBtn && !signinBtn.closest('a')) { // Ensure it's the button, not the logout link
          signinBtn.addEventListener('click', function() {
              window.location.href = 'signin.php';
          });
      }

      function updateCartBadge() {
          let totalItems = 0;
          <?php
            $sessionTotalItems = 0;
            if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $cart_item) {
                    if (isset($cart_item['quantity'])) { // Check if quantity is set
                        $sessionTotalItems += $cart_item['quantity'];
                    }
                }
            }
          ?>
          totalItems = <?php echo $sessionTotalItems; ?>;
          const cartBadge = document.getElementById('cart-count-badge');
          if (cartBadge) {
              cartBadge.textContent = totalItems;
          }
      }

      // Add form validation
      document.getElementById('checkoutForm').addEventListener('submit', function(e) {
          const form = this;
          const requiredFields = ['customer_name', 'customer_email', 'customer_phone', 'shipping_address_line1', 'shipping_city', 'shipping_zip_code'];
          let isValid = true;

          requiredFields.forEach(fieldId => {
              const field = document.getElementById(fieldId);
              if (!field.value.trim()) {
                  isValid = false;
                  field.classList.add('error');
              } else {
                  field.classList.remove('error');
              }
          });

          if (!isValid) {
              e.preventDefault();
              alert('Please fill in all required fields.');
              return false;
          }

          // Validate phone number format
          const phone = document.getElementById('customer_phone').value;
          const phonePattern = /^06\d{8}$/;
          if (!phonePattern.test(phone)) {
              e.preventDefault();
              alert('Please enter a valid Moroccan phone number (starts with 06 and has 10 digits total).');
              return false;
          }

          // Validate email format
          const email = document.getElementById('customer_email').value;
          const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailPattern.test(email)) {
              e.preventDefault();
              alert('Please enter a valid email address.');
              return false;
          }

          // If all validations pass, allow form submission
          return true;
      });

      window.addEventListener('load', updateCartBadge);
    </script>
</body>
</html>