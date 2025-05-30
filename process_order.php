<?php
session_start();
require_once 'db_config.php'; // Your database connection

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    $_SESSION['error_message'] = "You must be logged in to place an order.";
    header("Location: signin.php");
    exit;
}

// Ensure cart is not empty
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cartItems)) {
    $_SESSION['error_message'] = "Your cart is empty. Cannot place an order.";
    header("Location: product.php");
    exit;
}

// ADD THIS CHECK: Ensure user_id is available in the session
if (!isset($_SESSION['user_id'])) {
    // This indicates a problem with the login process or session management.\r\n    // Log this error for admin to investigate.
    error_log("Critical Error: User is logged in (username: " . ($_SESSION['username'] ?? 'N/A') . ") but user_id is not set in session during order processing.");
    $_SESSION['error_message'] = "A critical session error occurred. We could not process your order. Please try logging out and logging back in. If the problem persists, contact support.";
    // It might be wise to destroy the problematic session or parts of it here.
    // For now, redirecting to checkout will allow them to see the error.
    header("Location: checkout.php");
    exit;
}
$user_id = $_SESSION['user_id']; // Now we are more confident user_id is set.


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $customer_name = filter_input(INPUT_POST, 'customer_name', FILTER_SANITIZE_STRING);
    $customer_email = filter_input(INPUT_POST, 'customer_email', FILTER_SANITIZE_EMAIL);
    $customer_phone = filter_input(INPUT_POST, 'customer_phone', FILTER_SANITIZE_STRING);
    $shipping_address_line1 = filter_input(INPUT_POST, 'shipping_address_line1', FILTER_SANITIZE_STRING);
    $shipping_address_line2 = filter_input(INPUT_POST, 'shipping_address_line2', FILTER_SANITIZE_STRING);
    $shipping_city = filter_input(INPUT_POST, 'shipping_city', FILTER_SANITIZE_STRING);
    $shipping_state = filter_input(INPUT_POST, 'shipping_state', FILTER_SANITIZE_STRING);
    $shipping_zip_code = filter_input(INPUT_POST, 'shipping_zip_code', FILTER_SANITIZE_STRING);
    $shipping_country = filter_input(INPUT_POST, 'shipping_country', FILTER_SANITIZE_STRING); // Should be 'Morocco'
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING); // Should be 'cash_on_delivery'
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);

    // Basic validation (add more as needed)
    if (empty($customer_name) || empty($customer_email) || empty($customer_phone) || empty($shipping_address_line1) || empty($shipping_city) || empty($shipping_zip_code)) {
        $_SESSION['error_message'] = "Please fill in all required shipping details.";
        header("Location: checkout.php");
        exit;
    }

    // Recalculate total from session cart to prevent manipulation
    $order_subtotal = 0;
    foreach ($cartItems as $item) {
        $order_subtotal += $item['price'] * $item['quantity'];
    }
    $shipping_cost = 0; // As defined in checkout.php
    $order_total = $order_subtotal + $shipping_cost;

    // Database operations
    $conn->begin_transaction();

    try {
        // 1. Insert into `orders` table
        $sql_order = "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone,
                                      shipping_address_line1, shipping_address_line2, shipping_city,
                                      shipping_state, shipping_zip_code, shipping_country,
                                      order_total, order_status, payment_method, payment_status, notes)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_order = $conn->prepare($sql_order);
        if ($stmt_order === false) {
            throw new Exception("Prepare failed (order): " . $conn->error);
        }

        $order_status = 'pending'; // Initial status
        $payment_status = 'pending'; // For cash on delivery

        $stmt_order->bind_param("isssssssssdssss",
            $user_id, $customer_name, $customer_email, $customer_phone,
            $shipping_address_line1, $shipping_address_line2, $shipping_city,
            $shipping_state, $shipping_zip_code, $shipping_country,
            $order_total, $order_status, $payment_method, $payment_status, $notes
        );

        if (!$stmt_order->execute()) {
            throw new Exception("Execute failed (order): " . $stmt_order->error);
        }

        $order_id = $conn->insert_id; // Get the ID of the newly inserted order
        $stmt_order->close();

        // 2. Insert into `order_items` table
        $sql_order_item = "INSERT INTO order_items (order_id, product_id, design_submission_id, item_name, quantity, price_at_purchase, attributes)
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_order_item = $conn->prepare($sql_order_item);
        if ($stmt_order_item === false) {
            throw new Exception("Prepare failed (order_item): " . $conn->error);
        }

foreach ($cartItems as $cart_item) {
    $item_name = $cart_item['name'];
    $quantity = $cart_item['quantity'];
    $price_at_purchase = $cart_item['price'];

    $product_id_var = null;
    $design_submission_id_var = null;

    if (isset($cart_item['type']) && $cart_item['type'] === 'design_submission') {
        // Validate design_submission_id exists
        $design_submission_id_candidate = $cart_item['id'];
        $stmt_check_design = $conn->prepare("SELECT id FROM design_submissions WHERE id = ?");
        $stmt_check_design->bind_param("i", $design_submission_id_candidate);
        $stmt_check_design->execute();
        $stmt_check_design->store_result();
        if ($stmt_check_design->num_rows > 0) {
            $design_submission_id_var = $design_submission_id_candidate;
        }
        $stmt_check_design->close();
    } else {
        // Validate product_id exists
        $product_id_candidate = $cart_item['id'];
        $stmt_check_product = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $stmt_check_product->bind_param("i", $product_id_candidate);
        $stmt_check_product->execute();
        $stmt_check_product->store_result();
        if ($stmt_check_product->num_rows > 0) {
            $product_id_var = $product_id_candidate;
        }
        $stmt_check_product->close();
    }

    $attributes = [];
    if (!empty($cart_item['size'])) {
        $attributes['size'] = $cart_item['size'];
    }
    if (!empty($cart_item['color'])) {
        $attributes['color'] = $cart_item['color'];
    }
    if (!empty($cart_item['custom_design'])) {
        $attributes['custom_design'] = $cart_item['custom_design'];
    }
    $attributes_json = !empty($attributes) ? json_encode($attributes) : null;

    // Only insert if either product_id_var or design_submission_id_var is set
    if ($product_id_var !== null || $design_submission_id_var !== null) {
        $stmt_order_item->bind_param("iiisids",
            $order_id,
            $product_id_var,
            $design_submission_id_var,
            $item_name,
            $quantity,
            $price_at_purchase,
            $attributes_json
        );

        if (!$stmt_order_item->execute()) {
            throw new Exception("Execute failed (order_item for item {$item_name}): " . $stmt_order_item->error);
        }
    }
}
        $stmt_order_item->close();

        // If all successful, commit the transaction
        $conn->commit();

        // Clear the cart
        unset($_SESSION['cart']);

        // Redirect to an order confirmation page
        $_SESSION['success_message'] = "Your order has been placed successfully! Order ID: " . $order_id;
        header("Location: order_confirmation.php?order_id=" . $order_id);
        exit;

    } catch (Exception $e) {
        $conn->rollback(); // Rollback on error
        // Log error: error_log("Order processing error: " . $e->getMessage());
        // ADD THIS LINE TO LOG THE ERROR
        error_log("Order processing error: " . $e->getMessage());
        $_SESSION['error_message'] = "There was an error processing your order. Please try again. " . $e->getMessage();
        header("Location: checkout.php");
        exit;
    } finally {
        if (isset($stmt_order) && $stmt_order) $stmt_order->close();
        if (isset($stmt_order_item) && $stmt_order_item) $stmt_order_item->close();
        $conn->close();
    }


} else {
    // Not a POST request, redirect to cart or home
    header("Location: cart.php");
    exit;
}
?>
