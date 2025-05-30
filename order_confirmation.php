<?php
session_start();

$isLoggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$username = $isLoggedIn ? htmlspecialchars($_SESSION["username"]) : "";

$order_id = isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : null;
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;

// Clear messages after displaying them
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - LEFO</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="Slaytanic.css">
    <style>
        .logo { font-family: 'Slaytanic', sans-serif; }
        .cart-icon-container { position: relative; display: inline-flex; align-items: center; }
        .cart-item-count { position: absolute; top: -8px; right: -8px; background-color: #d88405; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.7rem; font-weight: bold; min-width: 18px; text-align: center; }
        .main-confirmation { padding-top: 40px; color: white; text-align: center; }
        .confirmation-container { max-width: 700px; margin: 0 auto; background-color: rgba(20, 20, 20, 0.7); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 10px; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        .confirmation-title { font-size: 2.5em; margin-bottom: 20px; color: #d88405; }
        .confirmation-message { font-size: 1.2em; margin-bottom: 30px; line-height: 1.6; }
        .error-message { color: #ff6b6b; font-weight: bold; }
        .btn-continue-shopping { background: linear-gradient(90deg, #d88405, #701003); color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-size: 1.1em; transition: background 0.3s ease; display: inline-block; margin-top: 20px; }
        .btn-continue-shopping:hover { background: linear-gradient(90deg, #be7508, #5a0d02); }
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

        <main class="main-confirmation">
            <div class="confirmation-container" data-aos="fade-up">
                <?php if ($success_message): ?>
                    <h2 class="confirmation-title">Thank You!</h2>
                    <p class="confirmation-message"><?php echo htmlspecialchars($success_message); ?></p>
                    <p class="confirmation-message">We've received your order and will process it shortly. You will receive an email confirmation soon.</p>
                <?php elseif ($error_message): ?>
                    <h2 class="confirmation-title" style="color: #ff6b6b;">Order Failed</h2>
                    <p class="confirmation-message error-message"><?php echo htmlspecialchars($error_message); ?></p>
                    <p class="confirmation-message">Please try placing your order again or contact support if the issue persists.</p>
                <?php else: ?>
                    <h2 class="confirmation-title">Order Status</h2>
                    <p class="confirmation-message">No order information found. If you just placed an order, please check your email or contact support.</p>
                <?php endif; ?>
                <a href="product.php" class="btn-continue-shopping">Continue Shopping</a>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
      AOS.init({ once: true, duration: 800 });
      const signinBtn = document.querySelector('.btn-signin');
      if (signinBtn && !signinBtn.closest('a')) { signinBtn.addEventListener('click', function() { window.location.href = 'signin.php'; }); }
      // Cart badge should be 0 here as cart is cleared
      document.getElementById('cart-count-badge').textContent = '0';
    </script>
</body>
</html>