<?php
session_start(); // Start the session at the very beginning
$isLoggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$username = $isLoggedIn ? htmlspecialchars($_SESSION["username"]) : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="Slaytanic.css"> 
    <style>
        .logo {
            font-family: 'Slaytanic', sans-serif; /* hada  for font */
        }
        /* Style for the logo when it's a link */
        header a .logo {
            color: inherit; /* Inherit the color from its parent */
            text-decoration: none; /* Remove the default underline */
        }
        /* Optional: Add a subtle hover effect */
        header a:hover .logo {
            color: #fff; /* Example: make it pure white on hover */
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
            background-color: #d88405; /* checkout color button */
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
            min-width: 18px; /* cart nuumber */
            text-align: center;
        }

        /* Ensure header is above other content like the spline viewer */
        header {
            position: relative; /* Needed for z-index to work */
            z-index: 10; /* Give header a higher z-index than potential overlays */
        }

        /* Ensure spline viewer doesn't block clicks on elements above it */
        .l3fo {
            z-index: 1; /* Give spline viewer a lower z-index */
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
                <a data-aos="fade-down" data-aos-duration="3000" href="cart.php" title="Cart" class="cart-icon-container"> <!-- Corrected to cart.php -->
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
        <main>
        <div data-aos="fade-zoom-in"
        data-aos-easing="ease-in-back"
        data-aos-delay="300"
        data-aos-offset="0" data-aos-duration="1500" class="content">
            <div class="tag-box">
                <div class="tag">INTRODUCING &wedbar; </div>
            </div>
            <h1 data-aos="fade-zoom-in"
            data-aos-easing="ease-in-back"
            data-aos-delay="300"
            data-aos-offset="0" data-aos-duration="1500">ELEVATE <BR>YOUR STYLE</BR></h1>
            <p class="description">start your way into clothing style with your imagination and our creativity.
            </p><br>
            <div class="buttons">
                <a href="about.html" class="btn-get-started">FOLLOW US &gt;</a>
                <a href="product.php" class="btn-signin-main">OUR PRODUCT &gt;</a> <!-- Corrected to product.php -->
            </div>
        </div>
    </main>
    <spline-viewer class="l3fo"  url="https://prod.spline.design/biBh-73Cym4Hi89B/scene.splinecode"></spline-viewer>
    </div>

    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.9.94/build/spline-viewer.js"></script>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
      AOS.init();

      // Add JavaScript to make the Sign In button work
      const signInButton = document.querySelector('.btn-signin');
      if (signInButton) {
          signInButton.addEventListener('click', function() {
              // Replace 'signin.html' with the actual URL of your sign-in page
              window.location.href = 'signin.php';
          });
      }

      // Cart badge update (simple localStorage version for consistency)
      function getCart() {
          const cart = localStorage.getItem('cart');
          return cart ? JSON.parse(cart) : [];
      }
      function updateCartBadge() {
          const cart = getCart();
          const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
          const cartBadge = document.getElementById('cart-count-badge');
          if (cartBadge) {
              cartBadge.textContent = totalItems;
          }
      }
      window.addEventListener('load', updateCartBadge);
      window.addEventListener('storage', (e) => { if (e.key === 'cart') updateCartBadge(); });
    </script>

</body>
</html>
