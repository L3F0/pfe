<?php
session_start();
require_once 'db_config.php'; // Your database connection

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    $_SESSION['redirect_url'] = 'my_orders.php'; // Store the intended destination
    header("Location: signin.php");
    exit;
}


// ADD THIS CHECK: Ensure user_id is available in the session
if (!isset($_SESSION['user_id'])) {
    // This indicates a problem with the login process or session management.
    error_log("Session Error: User is logged in (username: " . ($_SESSION['username'] ?? 'N/A') . ") but user_id is not set in session when trying to view orders.");
    $_SESSION['error_message'] = "Your session seems to be incomplete. Please try logging out and logging back in to view your orders.";
    // Redirect to signin or display an error. Redirecting to signin might help re-establish the session.
    $_SESSION['redirect_url'] = 'my_orders.php'; // Try to send them back here after login
    header("Location: signin.php");
    exit;
}
$user_id = $_SESSION['user_id']; // Now we are more confident user_id is set.
$username = htmlspecialchars($_SESSION["username"]);

// Fetch orders for the logged-in user
$orders = [];
$sql_orders = "SELECT
                    o.id AS order_id,
                    o.customer_name,
                    o.order_total,
                    o.order_status,
                    o.payment_method,
                    o.payment_status,
                    o.created_at AS order_date,
                    o.shipping_address_line1,
                    o.shipping_city,
                    o.shipping_zip_code,
                    o.shipping_country
                FROM orders o
                WHERE o.user_id = ?
                ORDER BY o.created_at DESC";

if ($stmt_orders = $conn->prepare($sql_orders)) {
    $stmt_orders->bind_param("i", $user_id);
    $stmt_orders->execute();
    $result_orders = $stmt_orders->get_result();
    if ($result_orders) {
        $orders = $result_orders->fetch_all(MYSQLI_ASSOC);
        $result_orders->free();
    }
    $stmt_orders->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - LEFO</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="Slaytanic.css">
    <style>
        .logo { font-family: 'Slaytanic', sans-serif; }
        .cart-icon-container { position: relative; display: inline-flex; align-items: center; }
        .cart-item-count {
            position: absolute; top: -8px; right: -8px; background-color: #d88405;
            color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.7rem;
            font-weight: bold; min-width: 18px; text-align: center;
        }
        .main-my-orders { padding-top: 40px; padding-bottom: 40px; color: #f0f0f0; }
        .my-orders-container { max-width: 900px; margin: 0 auto; padding: 0 20px; }
        .my-orders-title { text-align: center; margin-bottom: 30px; font-size: 2.5em; font-weight: bold; color: white; }
        .order-card {
            background-color: rgba(20, 20, 20, 0.8); border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px; padding: 25px; margin-bottom: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.4);
        }
        .order-card-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); flex-wrap: wrap;
        }
        .order-card-header h3 { font-size: 1.5em; color: #d88405; margin: 0; }
        .order-card-header .order-date { font-size: 0.9em; color: #ccc; }
        .order-details p { margin: 8px 0; font-size: 1em; line-height: 1.6; }
        .order-details strong { color: #bbb; min-width: 120px; display: inline-block; }
        .order-items-section { margin-top: 20px; padding-top: 15px; border-top: 1px dashed rgba(255,255,255,0.15); }
        .order-items-section h4 { font-size: 1.2em; margin-bottom: 10px; color: #ccc; }
        .order-items-list { list-style-type: none; padding-left: 0; }
        .order-items-list li {
            padding: 8px 0; font-size: 0.95em; display: flex; justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .order-items-list li:last-child { border-bottom: none; }
        .item-name-attrs { flex-grow: 1; }
        .item-name-attrs small { display: block; color: #aaa; font-size: 0.85em; }
        .item-price-qty { text-align: right; min-width: 100px; }
        .no-orders-message { text-align: center; padding: 30px; font-size: 1.2em; background-color: rgba(20, 20, 20, 0.7); border-radius: 8px; }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-processing { color: #17a2b8; font-weight: bold; }
        .status-shipped { color: #007bff; font-weight: bold; }
        .status-delivered { color: #28a745; font-weight: bold; }
        .status-cancelled { color: #dc3545; font-weight: bold; }
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
                <a data-aos="fade-down" data-aos-duration="2700" href="my_orders.php">MY ORDERS</a>
                <a data-aos="fade-down" data-aos-duration="3000" href="cart.php" title="Cart" class="cart-icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    <span class="cart-item-count" id="cart-count-badge">0</span>
                </a>
            </nav>
            <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <span style="color: white; margin-right: 15px; font-size: 0.9em;">Hi, <?php echo $username; ?>!</span>
                <a href="logout.php" data-aos="fade-down" data-aos-duration="1500" class="btn-signin" style="text-decoration: none;">LOGOUT</a>
            <?php else: ?>
                <button data-aos="fade-down" data-aos-duration="1500" class="btn-signin">SIGN IN</button>
            <?php endif; ?>
        </header>

        <main class="main-my-orders">
            <div class="my-orders-container" data-aos="fade-up">
                <h2 class="my-orders-title">My Order History</h2>

                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-card-header">
                                <h3>Order #<?php echo htmlspecialchars($order['order_id']); ?></h3>
                                <span class="order-date">Placed on: <?php echo date("F j, Y, g:i a", strtotime($order['order_date'])); ?></span>
                            </div>
                            <div class="order-details">
                                <p><strong>Status:</strong> <span class="status-<?php echo strtolower(htmlspecialchars($order['order_status'])); ?>"><?php echo htmlspecialchars(ucfirst($order['order_status'])); ?></span></p>
                                <p><strong>Total:</strong> $<?php echo number_format($order['order_total'], 2); ?></p>
                                <p><strong>Payment:</strong> <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $order['payment_method']))); ?> (<?php echo htmlspecialchars(ucfirst($order['payment_status'])); ?>)</p>
                                <p><strong>Shipping To:</strong> <?php echo htmlspecialchars($order['customer_name']); ?>, <?php echo htmlspecialchars($order['shipping_address_line1']); ?>, <?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_zip_code']); ?>, <?php echo htmlspecialchars($order['shipping_country']); ?></p>
                            </div>

                            <div class="order-items-section">
                                <h4>Items:</h4>
                                <?php
                                $order_items = [];
                                $sql_items = "SELECT item_name, quantity, price_at_purchase, attributes FROM order_items WHERE order_id = ?";
                                if ($stmt_items = $conn->prepare($sql_items)) {
                                    $stmt_items->bind_param("i", $order['order_id']);
                                    $stmt_items->execute();
                                    $result_items = $stmt_items->get_result();
                                    if ($result_items) {
                                        $order_items = $result_items->fetch_all(MYSQLI_ASSOC);
                                        $result_items->free();
                                    }
                                    $stmt_items->close();
                                }
                                ?>
                                <?php if (!empty($order_items)): ?>
                                    <ul class="order-items-list">
                                        <?php foreach ($order_items as $item): ?>
                                            <li>
                                                <span class="item-name-attrs">
                                                    <?php echo htmlspecialchars($item['item_name']); ?>
                                                    <?php if (!empty($item['attributes'])): $attrs = json_decode($item['attributes'], true); ?>
                                                        <small>
                                                        <?php 
                                                            $attr_strings = [];
                                                            if (is_array($attrs)) { // Ensure $attrs is an array before looping
                                                                foreach ($attrs as $key => $val) { 
                                                                    $attr_strings[] = htmlspecialchars(ucfirst($key)) . ': ' . htmlspecialchars($val);
                                                                }
                                                                echo implode(', ', $attr_strings);
                                                            }
                                                        ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </span>
                                                <span class="item-price-qty">
                                                    (<?php echo $item['quantity']; ?> x $<?php echo number_format($item['price_at_purchase'], 2); ?>)
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p>No items found for this order.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-orders-message">You haven't placed any orders yet. <a href="product.php" style="color: #d88405;">Start Shopping!</a></p>
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
      if (signinBtn && !signinBtn.closest('a')) { // Ensure it's the button, not the logout link
          signinBtn.addEventListener('click', function() {
              window.location.href = 'signin.php';
          });
      }

      function updateCartBadge() {
          let totalItems = 0;
          <?php
            $sessionTotalItems = 0;
            if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $cart_item) {
                    if (isset($cart_item['quantity'])) {
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
      window.addEventListener('load', updateCartBadge);
      <?php if (isset($conn)) $conn->close(); ?>
    </script>
</body>
</html>