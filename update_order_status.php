<?php
session_start();
require_once 'db_config.php'; // Your database connection

// Check if the user is logged in and is an admin.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    // If not logged in as admin, redirect
    $_SESSION['message'] = "Error: You are not authorized to perform this action.";
    header("location: signin.php"); 
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['order_id']) && isset($_POST['new_status'])) {
        $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $new_status = filter_input(INPUT_POST, 'new_status', FILTER_SANITIZE_STRING);

        // Validate the status to ensure it's one of the allowed values
        $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!$order_id || !in_array($new_status, $allowed_statuses)) {
            $_SESSION['message'] = "Error: Invalid order ID or status provided.";
            header("Location: admin_dashboard.php#orders-section");
            exit;
        }

        // Prepare SQL statement to update the order status
        $sql = "UPDATE orders SET order_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $new_status, $order_id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $_SESSION['message'] = "Order ID: {$order_id} status updated to '{$new_status}' successfully.";
                } else {
                    $_SESSION['message'] = "Order ID: {$order_id} status was already '{$new_status}' or order not found.";
                }
            } else {
                // Log error: error_log("Error updating order status: " . $stmt->error);
                $_SESSION['message'] = "Error updating order status: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Log error: error_log("Error preparing statement: " . $conn->error);
            $_SESSION['message'] = "Error: Could not prepare the update statement.";
        }
        $conn->close();
    } else {
        $_SESSION['message'] = "Error: Missing order ID or new status.";
    }
} else {
    // Not a POST request
    $_SESSION['message'] = "Error: Invalid request method.";
}

// Redirect back to the admin dashboard orders section
header("Location: admin_dashboard.php#orders-section");
exit;

?>