<?php
session_start(); // Start the session at the very beginning
// Initialize cart from session or as an empty array
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cartSubtotal = 0;
$cartTotal = 0;
$isLoggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$username = $isLoggedIn ? htmlspecialchars($_SESSION["username"]) : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - LEFO</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="Slaytanic.css"> <!-- Link to your Slaytanic font CSS -->
    <style>
        .logo {
            font-family: 'Slaytanic', sans-serif; /* Use Slaytanic font with a fallback */
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
            background-color: #d88405; /* Using the orange from your checkout button */
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
            min-width: 18px; /* Ensures circle shape for single digit */
            text-align: center;
        }
        /* Styles for cart page */
        .main-cart {
            padding-top: 20px; /* Add some space below the header */
            color: white; /* Assuming white text like index.html */
        }
        .cart-title {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5em;
            font-weight: bold;
        }
        .cart-content {
            max-width: 800px;
            margin: 0 auto 40px auto;
            background-color: rgba(20, 20, 20, 0.7); /* Darker, semi-transparent background */
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .cart-empty-message {
            text-align: center;
            font-size: 1.2em;
            padding: 20px;
            color: #ccc;
        }
        .cart-summary {
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 20px;
            text-align: right;
        }
        .cart-summary p {
            font-size: 1.2em;
            margin-bottom: 10px;
        }
        .cart-summary .total {
            font-size: 1.5em;
            font-weight: bold;
            color: #d88405; /* Vibrant total color */
        }
        .btn-checkout {
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
            width: fit-content;
            margin: 20px 0 0 auto; /* Aligns button to the right */
        }
        .btn-checkout:hover {
            background-color: #be7508; 
            transform: scale(1.02);
        }

        /* Styles for individual cart items */
        .cart-items-list {
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px; /* Space between cart items */
        }
        .cart-item {
            display: flex;
            align-items: flex-start; /* Align items to the top */
            background-color: rgba(30, 30, 30, 0.6); /* Slightly lighter than content background */
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            padding: 15px;
            gap: 15px; /* Space between image, details, and remove button */
            position: relative; /* For absolute positioning of remove button if needed */
        }
        .cart-item-image {
            width: 100px; /* Fixed width for image */
            height: 100px; /* Fixed height for image */
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .cart-item-details {
            flex-grow: 1; /* Allow details to take remaining space */
        }
        .cart-item-name {
            font-size: 1.2em;
            font-weight: bold;
            margin: 0 0 5px 0;
            color: #f0f0f0;
        }
        .cart-item-price, .cart-item-quantity-controls, .cart-item-total {
            font-size: 0.9em;
            color: #ccc;
            margin-bottom: 5px;
        }
        .cart-item-total {
            font-weight: bold;
        }
        .cart-item-remove {
            color: #ff6b6b; /* Reddish color for remove */
            font-size: 1.5em; /* Make X larger */
            text-decoration: none;
            font-weight: bold;
            padding: 0 5px; /* Some padding for easier clicking */
            line-height: 1; /* Adjust line height for better vertical alignment */
            align-self: flex-start; /* Align to the top of the flex container */
        }
        .cart-item-remove:hover {
            color: #e04343;
            transform: scale(1.1);
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
                <?php if ($isLoggedIn): ?>
                    <a data-aos="fade-down" data-aos-duration="2700" href="my_orders.php">MY ORDERS</a>
                <?php endif; ?>
                <a data-aos="fade-down" data-aos-duration="<?php echo $isLoggedIn ? '2800' : '3000'; ?>" href="cart.php" title="Cart" class="cart-icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    <span class="cart-item-count" id="cart-count-badge">0</span> <!-- Placeholder count -->
                </a>
            </nav>
            <?php if ($isLoggedIn): ?>
                <span style="color: white; margin-right: 15px; font-size: 0.9em;">Hi, <?php echo $username; ?>!</span>
                <a href="logout.php" data-aos="fade-down" data-aos-duration="1500" class="btn-signin" style="text-decoration: none;">LOGOUT</a>
            <?php else: ?>
                <button data-aos="fade-down" data-aos-duration="1500" class="btn-signin">SIGN IN</button>
            <?php endif; ?>
        </header>

        <main class="main-cart">
            <div class="cart-container" data-aos="fade-up">
                <h2 class="cart-title">Your Shopping Cart</h2>
                <?php if (empty($cartItems)): ?>
                    <p class="empty-cart-message" style="text-align: center; font-size: 1.2em; margin: 40px 0;">Your cart is currently empty.</p>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="product.php" class="btn-continue-shopping" style="background: linear-gradient(90deg, #d88405, #701003); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Continue Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="cart-items-list">
                        <?php foreach ($cartItems as $item): ?>
                            <?php
                                $itemTotal = $item['price'] * $item['quantity'];
                                $cartSubtotal += $itemTotal;
                            ?>
                            <div class="cart-item">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-image">
                                <div class="cart-item-details">
                                    <h3 class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="cart-item-price">$<?php echo number_format($item['price'], 2); ?></p>
                                    <div class="cart-item-quantity-controls">
                                        <!-- Basic quantity display. Full controls ( +/-/update ) require more JS and PHP handlers -->
                                        <span>Quantity: <?php echo $item['quantity']; ?></span>
                                        <!-- <input type="number" value="<?php echo $item['quantity']; ?>" min="1" class="cart-item-quantity" data-product-id="<?php echo $item['id']; ?>"> -->
                                    </div>
                                    <p class="cart-item-total">Item Total: $<?php echo number_format($itemTotal, 2); ?></p>
                                </div>
                                <a href="remove_from_cart.php?id=<?php echo $item['id']; ?>" class="cart-item-remove" title="Remove item">&times;</a>
                                <!-- remove_from_cart.php would need to be created -->
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($cartSubtotal, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span>Calculated at checkout</span> <!-- Or a fixed value/free -->
                        </div>
                        <div class="summary-row total-row">
                            <span>Total:</span>
                            <?php $cartTotal = $cartSubtotal; // Add shipping if applicable ?>
                            <span>$<?php echo number_format($cartTotal, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
                        <!-- checkout.php needs to be created -->
                    </div>
                <?php endif; ?>
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
      if (signinBtn && !signinBtn.href) { // Ensure it's the button, not the logout link
          signinBtn.addEventListener('click', function() {
              window.location.href = 'signin.php';
          });
      }

      // Cart badge update & cart display logic (basic localStorage version)
      function updateCartBadge() {
          // Fetch total items from session via a helper PHP script or use localStorage if synced
          // For now, let's assume add_to_cart.php keeps a localStorage value in sync or we fetch it.
          let totalItems = 0;
          <?php
            $sessionTotalItems = 0;
            if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $cart_item) {
                    $sessionTotalItems += $cart_item['quantity'];
                }
            }
          ?>
          totalItems = <?php echo $sessionTotalItems; ?>;
          const cartBadge = document.getElementById('cart-count-badge');
          if (cartBadge) {
              cartBadge.textContent = totalItems;
          }
      }
      window.addEventListener('load', updateCartBadge); // Update badge on load
      // Add logic here to display cart items from localStorage into .cart-items-list
      // and update subtotal/total. This part is more involved.
    </script>
</body>
</html>