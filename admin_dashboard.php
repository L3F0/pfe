<?php
session_start();

// Check if the user is logged in and is an admin.
// The $_SESSION["is_admin"] flag is set in login_handler.php
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    // If not logged in as admin, redirect to the regular sign-in page or homepage
    header("location: signin.php"); // Or index.php, depending on desired behavior
    exit;
}

require_once 'db_config.php'; // Include your database configuration

$username = htmlspecialchars($_SESSION["username"]);

// Fetch orders
$orders = [];
$sql_orders = "SELECT
                    o.id AS order_id,
                    o.customer_name,
                    o.customer_email,
                    o.order_total,
                    o.order_status,
                    o.payment_method,
                    o.payment_status,
                    o.notes AS order_notes,
                    o.created_at AS order_date,
                    u.username AS ordered_by_username
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC";

if ($result_orders = $conn->query($sql_orders)) {
    $orders = $result_orders->fetch_all(MYSQLI_ASSOC);
}

// --- Fetch Users ---
$users = [];
$sql_users = "SELECT id, username, email, created_at, is_admin FROM users ORDER BY created_at DESC";
if ($result_users = $conn->query($sql_users)) {
    $users = $result_users->fetch_all(MYSQLI_ASSOC);
}

// --- Fetch Products (including design-based products) ---
$products = [];
$sql_products = "SELECT 
                    p.id, 
                    p.name, 
                    p.price, 
                    p.stock_quantity, 
                    p.image_path, 
                    p.is_design,
                    p.design_id,
                    ds.design_name AS original_design_name,
                    ds.status AS design_status,
                    ds.file_path AS design_file,
                    u.username AS designer_username,
                    GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS categories, 
                    p.created_at 
                 FROM products p
                 LEFT JOIN product_categories pc ON p.id = pc.product_id
                 LEFT JOIN categories c ON pc.category_id = c.id
                 LEFT JOIN design_submissions ds ON p.design_id = ds.id
                 LEFT JOIN users u ON ds.user_id = u.id
                 GROUP BY p.id
                 ORDER BY p.created_at DESC";
if ($result_products = $conn->query($sql_products)) {
    $products = $result_products->fetch_all(MYSQLI_ASSOC);
}

// --- Fetch Pending Design Submissions (not yet products) ---
$pending_submissions = [];
$sql_submissions = "SELECT 
                        ds.id, 
                        ds.design_name, 
                        ds.description,
                        ds.file_path, 
                        ds.status, 
                        ds.submitted_at AS created_at,
                        u.username AS submitted_by,
                        u.email AS designer_email
                    FROM design_submissions ds
                    LEFT JOIN users u ON ds.user_id = u.id
                    LEFT JOIN products p ON ds.id = p.design_id
                    WHERE p.id IS NULL
                    ORDER BY ds.submitted_at DESC";
if ($result_submissions = $conn->query($sql_submissions)) {
    $pending_submissions = $result_submissions->fetch_all(MYSQLI_ASSOC);
}

// --- Fetch Design Submissions ---
$submissions = [];
$sql_submissions = "SELECT 
                        ds.id, ds.design_name, ds.contact_email, ds.file_path, 
                        ds.status, ds.submitted_at, u.username AS submitted_by 
                    FROM design_submissions ds
                    LEFT JOIN users u ON ds.user_id = u.id
                    ORDER BY ds.submitted_at DESC";
if ($result_submissions = $conn->query($sql_submissions)) {
    $submissions = $result_submissions->fetch_all(MYSQLI_ASSOC);
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LEFO</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="Slaytanic.css">
    <style>
        /* Basic styles from other pages */
        .logo { font-family: 'Slaytanic', sans-serif; }
        .cart-icon-container { position: relative; display: inline-flex; align-items: center; }
        .cart-item-count {
            position: absolute; top: -8px; right: -8px; background-color: #d88405;
            color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.7rem;
            font-weight: bold; min-width: 18px; text-align: center;
        }

        /* Admin Page Specific Styles */
        .main-admin {
            padding-top: 40px;
            padding-bottom: 40px;
            color: #f0f0f0;
            min-height: calc(100vh - 160px); /* Adjust based on header/footer height */
        }
        .admin-container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: rgba(20, 20, 20, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.5);
        }
        .admin-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            font-weight: bold;
            color: white;
        }
        .admin-nav {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap; /* Allow items to wrap on smaller screens */
        }
        .admin-nav a {
            background-color: #333;
            color: #d88405;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .admin-nav a:hover {
            background-color: #d88405;
            color: #111;
        }
        .admin-section {
            margin-bottom: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .admin-section h3 {
            font-size: 1.8em;
            margin-bottom: 20px;
            color: #ccc;
        }
        /* Placeholder styles for lists/tables */
        .admin-list {
            /* This is where your user list, product list, etc. would go */
            /* You'll likely use tables or flex/grid layouts here */
            color: #b0b0b0;
        }
        .admin-list p {
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        /* Table styles for orders */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.9em;
        }
        .orders-table th, .orders-table td {
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 10px 12px;
            text-align: left;
        }
         .users-table th, .users-table td,
        .products-table th, .products-table td,
        .submissions-table th, .submissions-table td {
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 10px 12px;
        }
         .orders-table th {
            background-color: rgba(0, 255, 157, 0.1); /* Light green accent */
            color: #d88405;
            font-weight: bold;
    
        }

           .users-table th,
        .products-table th,
        .submissions-table th {
            background-color: rgba(0, 255, 157, 0.1); /* Light green accent */
            color: #d88405;
            font-weight: bold;
        }
        .orders-table tr:nth-child(even),
        .users-table tr:nth-child(even),
        .products-table tr:nth-child(even),
        .submissions-table tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.03);
        }
        .orders-table tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.03);


        }
        .order-items-list {
            list-style-type: none;
            padding-left: 0;
            margin-top: 5px;
            font-size: 0.9em;
        }
        .order-items-list li {
            padding: 3px 0;
            border-bottom: 1px dashed rgba(255,255,255,0.1);
        }
        .order-items-list li:last-child {
            border-bottom: none;
        }
        .status-pending { color: #ffc107; } /* Yellow */
        .status-processing { color: #17a2b8; } /* Teal */
        .status-shipped { color: #007bff; } /* Blue */
        .status-delivered { color: #28a745; } /* Green */
        .status-cancelled { color: #dc3545; } /* Red */
        .status-pending_review { color: #ffc107; } /* Yellow for submissions */
        .status-approved { color: #28a745; } /* Green for submissions */
        .status-rejected { color: #dc3545; } /* Red for submissions */
        .status-quoted { color: #17a2b8; } /* Teal for submissions */

        .no-orders-message {
            
            text-align: center;
            padding: 20px;
        }
        .no-data-message {
text-align: center;
            padding: 20px;
        }
        .status-update-form select {
            padding: 5px;
            border-radius: 3px;
            border: 1px solid #555;
            background-color: #333;
            color: #fff;
            margin-right: 5px;
        }
        .status-update-form button {
            padding: 5px 10px;
            font-size: 0.9em;
            cursor: pointer;
        }

         .product-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 3px;
        }
  .btn-action {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 3px;
            font-size: 0.9em;
            margin-right: 5px;
        }
        .btn-edit { background-color: #007bff; color: white; }
        .btn-edit:hover { background-color: #0056b3; }
        .btn-create { display: inline-block; margin-bottom: 15px; background-color: #d88405; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-weight: bold;}
        .btn-create:hover { background-color: #d88405; }

        /* Responsive adjustments for admin page */
        @media (max-width: 768px) {
            .admin-container {
                padding: 20px;
                margin: 0 10px;
            }
            .admin-title {
                font-size: 2em;
            }
            .admin-nav {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
            .admin-nav a {
                width: 80%;
                max-width: 200px;
                text-align: center;
            }
            .admin-section h3 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <img class="image-gradient" src="gradient.png" alt="gradient">
    <div class="layer-blur"></div>

    <div class="container">
        <header>
            <h1 class="logo" data-aos="fade-down" data-aos-duration="1000">lEFo</h1>
            <!-- Admin header might be different - maybe just logo and logout -->
            <!-- For now, keeping standard header structure but removing nav links -->
            <nav style="display: none;">
                <!-- Standard nav links hidden on admin page -->
            </nav>
            <a href="logout.php" data-aos="fade-down" data-aos-duration="1500" class="btn-signin" style="text-decoration: none;">LOGOUT</a>
        </header>

        <main class="main-admin">
            <div class="admin-container" data-aos="fade-up">
                <h2 class="admin-title">Admin Dashboard</h2>

                <?php if(isset($_SESSION['message'])): ?>
                    <div style="padding: 10px; margin-bottom: 20px; border-radius: 5px; text-align:center; background-color: <?php echo strpos($_SESSION['message'], 'Error') !== false ? 'rgba(255,0,0,0.3)' : 'rgba(0,255,0,0.3)'; ?>; color: white;">
                        <?php 
                        echo $_SESSION['message']; 
                        unset($_SESSION['message']); // Clear the message after displaying
                        ?>
                    </div>
                <?php endif; ?>

                <div class="admin-nav">
                    <a href="#users-section">Manage Users</a>
                    <a href="#products-section">Manage Products</a>
                    <a href="#submissions-section">Manage Submissions</a>
                    <a href="#orders-section">Manage Orders</a>
                </div>

                <div id="users-section" class="admin-section">
                    <h3>Users</h3>
                    <div class="admin-list">
                         <?php if (!empty($users)): ?>
                            <table class="users-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Registered At</th>
                                        <th>Admin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo date("M d, Y H:i", strtotime($user['created_at'])); ?></td>
                                            <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="no-data-message">No users found.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="products-section" class="admin-section">
                    <h3>Products</h3>
                    <div class="admin-list">
                         <a href="edit_product.php" class="btn-create">Create New Product</a>
                         <?php if (!empty($products)): ?>
                            <table class="products-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Categories</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?php echo $product['id']; ?></td>
                                            <td><img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail"></td>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                                            <td><?php echo $product['stock_quantity']; ?></td>
                                            <td>T-shirt. Hoodie. Hat. Accessories</td>
                                            <td><?php echo date("M d, Y H:i", strtotime($product['created_at'])); ?></td>
                                             <td>
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-action btn-edit">Edit</a>
                                                <form action="delete_product.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <button type="submit" class="btn-action btn-remove" style="background-color:#dc3545; color:white; border:none; padding:5px 10px; border-radius:3px; cursor:pointer;">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="no-data-message">No products found.</p>
                        <?php endif; ?>
                </div>

                <div id="submissions-section" class="admin-section">
                    <h3>Design Submissions</h3>
                    <div class="admin-list">
                         <?php if (!empty($submissions)): ?>
                            <table class="submissions-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Design Name</th>
                                        <th>Contact Email</th>
                                        <th>File</th>
                                        <th>Status</th>
                                        <th>Submitted At</th>
                                        <th>Submitted By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submissions as $submission): ?>
                                        <tr>
                                            <td><?php echo $submission['id']; ?></td>
                                            <td><?php echo htmlspecialchars($submission['design_name']); ?></td>
                                            <td><?php echo htmlspecialchars($submission['contact_email']); ?></td>
                                            <td>
                                                <?php if (!empty($submission['file_path'])): ?>
                                                    <?php
                                                    $file_ext = pathinfo($submission['file_path'], PATHINFO_EXTENSION);
                                                    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                                                    if (in_array(strtolower($file_ext), $image_extensions)): ?>
                                                        <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank">
                                                            <img src="<?php echo htmlspecialchars($submission['file_path']); ?>" alt="Design Image" style="max-width: 100px; max-height: 100px; border-radius: 5px;">
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank">View File</a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    No file uploaded
                                                <?php endif; ?>
                                            </td>
                                            <td class="status-<?php echo strtolower(htmlspecialchars(str_replace(' ', '_', $submission['status']))); ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $submission['status']))); ?></td>
                                            <td><?php echo date("M d, Y H:i", strtotime($submission['submitted_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($submission['submitted_by'] ?? 'Guest'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="no-data-message">No design submissions found.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="orders-section" class="admin-section">
                    <h3>Orders</h3>
                    <div class="admin-list">
                        <?php if (!empty($orders)): ?>
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Date</th>
                                        <th>Notes</th>
                                        <th>Items</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?php echo $order['order_id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?> <?php echo $order['ordered_by_username'] ? '(' . htmlspecialchars($order['ordered_by_username']) . ')' : '(Guest)'; ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                                            <td>$<?php echo number_format($order['order_total'], 2); ?></td>
                                            <td>
                                                <form action="update_order_status.php" method="POST" class="status-update-form">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                    <select name="new_status">
                                                        <?php 
                                                        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
                                                        foreach ($statuses as $status): 
                                                        ?>
                                                            <option value="<?php echo $status; ?>" <?php echo ($order['order_status'] == $status) ? 'selected' : ''; ?>>
                                                                <?php echo ucfirst($status); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="btn-signin" style="padding: 5px 10px; font-size:0.9em; margin-left:5px;">Update</button>
                                                </form>
                                                <span class="status-<?php echo strtolower(htmlspecialchars($order['order_status'])); ?>" style="display:block; margin-top:5px; font-weight:bold;">
                                                    Current: <?php echo htmlspecialchars(ucfirst($order['order_status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $order['payment_method']))); ?> (<?php echo htmlspecialchars(ucfirst($order['payment_status'])); ?>)</td>
                                            <td><?php echo date("M d, Y H:i", strtotime($order['order_date'])); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($order['order_notes'] ?? '')); ?></td>
                                            <td>
                                                <?php
                                                $order_items = [];
                                                $sql_items = "SELECT id, product_id, design_submission_id, item_name, quantity, price_at_purchase, attributes FROM order_items WHERE order_id = ?";
                                                if ($stmt_items = $conn->prepare($sql_items)) {
                                                    $stmt_items->bind_param("i", $order['order_id']);
                                                    $stmt_items->execute();
                                                    $result_items = $stmt_items->get_result();
                                                    $order_items = $result_items->fetch_all(MYSQLI_ASSOC);
                                                    $stmt_items->close();
                                                }
                                                ?>
                                                <?php if (!empty($order_items)): ?>
                                                    <ul class="order-items-list">
                                                <?php foreach ($order_items as $item): ?>
                                                    <li>
                                                        <?php
                                                        if (!empty($item['design_submission_id'])) {
                                                            // Fetch design submission details and status
                                                            $design_id = $item['design_submission_id'];
                                                            $design_name = "Custom T-Shirt";
                                                            $design_details = "";
                                                            $design_status = "";
                                                            $stmt_design = $conn->prepare("SELECT design_name, customization_details, status FROM design_submissions WHERE id = ?");
                                                            if ($stmt_design) {
                                                                $stmt_design->bind_param("i", $design_id);
                                                                $stmt_design->execute();
                                                                $stmt_design->bind_result($ds_name, $ds_customization, $ds_status);
                                                                if ($stmt_design->fetch()) {
                                                                    $design_name = $ds_name ?: $design_name;
                                                                    $design_details = $ds_customization ?: "";
                                                                    $design_status = $ds_status ?: "";
                                                                }
                                                                $stmt_design->close();
                                                            }
                                                            echo htmlspecialchars($design_name) . " (x" . htmlspecialchars($item['quantity']) . ")";
                                                            if (!empty($design_details)) {
                                                                $custom_attrs = json_decode($design_details, true);
                                                                if (is_array($custom_attrs)) {
                                                                    echo " <small>[";
                                                                    // Show tshirt color and size explicitly if available
                                                                    if (isset($custom_attrs['tshirt_color'])) {
                                                                        echo "T-Shirt Color: " . htmlspecialchars($custom_attrs['tshirt_color']) . " ";
                                                                    }
                                                                    if (isset($custom_attrs['tshirt_size'])) {
                                                                        echo "T-Shirt Size: " . htmlspecialchars($custom_attrs['tshirt_size']) . " ";
                                                                    }
                                                                    // Show other customization details
                                                                    foreach ($custom_attrs as $key => $val) {
                                                                        if ($key !== 'tshirt_color' && $key !== 'tshirt_size') {
                                                                            echo htmlspecialchars(ucfirst($key)) . ": " . htmlspecialchars($val) . " ";
                                                                        }
                                                                    }
                                                                    echo "]</small>";
                                                                }
                                                            }
                                                        } else {
                                                            // Regular product item
                                                            echo htmlspecialchars($item['item_name']) . " (x" . htmlspecialchars($item['quantity']) . ")";
                                                            if (!empty($item['attributes'])) {
                                                                $attrs = json_decode($item['attributes'], true);
                                                                if (is_array($attrs)) {
                                                                    echo " <small>[";
                                                                    foreach ($attrs as $key => $val) {
                                                                        echo htmlspecialchars(ucfirst($key)) . ": " . htmlspecialchars($val) . " ";
                                                                    }
                                                                    echo "]</small>";
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    </li>
                                                <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="no-data-message">No orders found.</p>
                        <?php endif; ?>
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

      // Cart badge update (kept for consistency, though maybe not needed on admin page)
      function getCart() {
          const cart = localStorage.getItem('cart');
          return cart ? JSON.parse(cart) : [];
      }
      function updateCartBadge() {
          const cart = getCart();
          const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
          // Assuming there's no cart badge on the admin page, or use a different ID
          // const cartBadge = document.getElementById('cart-count-badge-admin');
          // if (cartBadge) {
          //     cartBadge.textContent = totalItems;
          // }
      }
      window.addEventListener('load', updateCartBadge);
      window.addEventListener('storage', function(e) {
          if (e.key === 'cart') {
              updateCartBadge();
          }
      });

      // Close the database connection if it was opened
      <?php if (isset($conn)) $conn->close(); ?>
    </script>
</body>
</html>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
      AOS.init({
        once: true,
        duration: 800,
      });

      // Cart badge update (kept for consistency, though maybe not needed on admin page)
      function getCart() {
          const cart = localStorage.getItem('cart');
          return cart ? JSON.parse(cart) : [];
      }
      function updateCartBadge() {
          const cart = getCart();
          const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
          // Assuming there's no cart badge on the admin page, or use a different ID
          // const cartBadge = document.getElementById('cart-count-badge-admin');
          // if (cartBadge) {
          //     cartBadge.textContent = totalItems;
          // }
      }
      window.addEventListener('load', updateCartBadge);
      window.addEventListener('storage', function(e) {
          if (e.key === 'cart') {
              updateCartBadge();
          }
      });

      // Close the database connection if it was opened
      <?php if (isset($conn)) $conn->close(); ?>
    </script>
</body>
</html>