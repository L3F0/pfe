<?php
session_start();
// If user is already logged in, redirect to homepage
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In / Sign Up - LEFO</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="Slaytanic.css">
    <style>
        /* Basic styles from other pages */
        .logo { font-family: 'Slaytanic', sans-serif; }
        .cart-icon-container { position: relative; display: inline-flex; align-items: center; }
        .cart-item-count {
            position: absolute; top: -8px; right: -8px; background-color: #d88405;
            color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.7rem; /* Corrected color */
            font-weight: bold; min-width: 18px; text-align: center;
        }

        /* Header adjustments for centering nav */
        header {
            justify-content: flex-start; /* Override space-between from style.css */
        }
        header nav {
            margin: 0 auto; /* Center the navigation */
        }
        /* Styles for sign-in page */
        .main-auth {
            padding-top: 40px;
            padding-bottom: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 160px); /* Adjust based on header/footer height if you add a footer */
        }
        .auth-container {
            background-color: rgba(20, 20, 20, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 30px 40px;
            width: 100%;
            max-width: 450px;
            color: #f0f0f0;
            box-shadow: 0 8px 25px rgba(0,0,0,0.5);
        }
        .auth-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2em;
            font-weight: bold;
            color: white;
        }
        .auth-form .form-group {
            margin-bottom: 20px;
        }
        .auth-form label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9em;
            color: #ccc;
        }
        .auth-form input[type="text"],
        .auth-form input[type="email"],
        .auth-form input[type="password"] {
            width: 100%;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #555;
            background-color: #333;
            color: #fff;
            font-size: 1em;
            box-sizing: border-box;
        }
        .auth-form .btn-submit-auth {
            background: linear-gradient(90deg, #d88405, #701003); 
            color: white; /* Changed to white for better contrast */
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            text-transform: uppercase;
            transition: background 0.3s ease, transform 0.2s ease;
            width: 100%;
            margin-top: 10px;
        }
        .auth-form .btn-submit-auth:hover {
            background: linear-gradient(90deg, #be7508, #5a0d02); /* Darker hover */
            transform: scale(1.02);
        }
        .auth-toggle {
            text-align: center;
            margin-top: 25px;
        }
        .auth-toggle a {
            color: #d88405;
            text-decoration: none;
            font-weight: bold;
        }
        .auth-toggle a:hover {
            text-decoration: underline;
        }
        .message-area {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        .message-area.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;}
        .message-area.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;}
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
                    <span class="cart-item-count" id="cart-count-badge">0</span> <!-- Placeholder count -->
                </a>
            </nav>
            <!-- No Sign In/Sign Up button in header for this page -->
        </header>

        <main class="main-auth">
            <div class="auth-container" data-aos="fade-up">
                <!-- Sign In Form -->
                <div id="signin-form">
                    <h2 class="auth-title">Sign In</h2>
                    <div id="signin-message" class="message-area" style="display: none;"></div>
                    <form class="auth-form" id="loginForm">
                        <div class="form-group">
                            <label for="signin-email">Email or Username</label>
                            <input type="text" id="signin-email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="signin-password">Password</label>
                            <input type="password" id="signin-password" name="password" required>
                        </div>
                        <button type="submit" class="btn-submit-auth">Sign In</button>
                    </form>
                    <div class="auth-toggle">
                        <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
      AOS.init({
        once: true,
        duration: 800,
      });

      // Cart badge update (copied for consistency)
      function updateCartBadge() {
          let totalItems = 0;
          <?php
            $sessionTotalItems = 0;
            if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
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
      
      const signinMessageDiv = document.getElementById('signin-message');

      window.addEventListener('load', function() {
          updateCartBadge();
          // Check for registration success message
          const urlParams = new URLSearchParams(window.location.search);
          if (urlParams.has('registered') && urlParams.get('registered') === 'true') {
              displayMessage(signinMessageDiv, 'Registration successful! Please sign in.', true);
          }
      });

     function displayMessage(element, message, isSuccess) {
          element.textContent = message;
          element.className = 'message-area ' + (isSuccess ? 'success' : 'error');
          element.style.display = 'block';
      }

      document.getElementById('loginForm').addEventListener('submit', function(event) {
          event.preventDefault();
          if(signinMessageDiv) signinMessageDiv.style.display = 'none'; // Hide previous messages
          const formData = new FormData(this);

          fetch('login_handler.php', {
              method: 'POST',
              body: formData
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  displayMessage(signinMessageDiv, data.message, true);
                  if (data.redirectUrl) {
                      setTimeout(() => {
                          window.location.href = data.redirectUrl;
                      }, 1500); // Wait a bit to show message
                  }
              } else {
                  displayMessage(signinMessageDiv, data.message, false);
              }
          })
          .catch(error => {
              console.error('Error:', error);
              displayMessage(signinMessageDiv, 'An unexpected error occurred. Please try again.', false);
          });
      });
    </script>
</body>
</html>