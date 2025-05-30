<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    // Not logged in as admin, redirect to signin
    header("location: signin.php");
    exit;
}

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);

    // Prepare and execute delete query
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $product_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Product deleted successfully.";
        } else {
            $_SESSION['message'] = "Error deleting product.";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Error preparing delete statement.";
    }
} else {
    $_SESSION['message'] = "Invalid request.";
}

$conn->close();

// Redirect back to admin dashboard
header("Location: admin_dashboard.php");
exit;
?>
