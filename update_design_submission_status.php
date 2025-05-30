<?php
session_start();
require_once 'db_config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $design_submission_id = filter_input(INPUT_POST, 'design_submission_id', FILTER_VALIDATE_INT);
    $new_status = filter_input(INPUT_POST, 'new_status', FILTER_SANITIZE_STRING);

    $valid_statuses = ['pending_review', 'approved', 'rejected', 'quoted'];
    if (!$design_submission_id || !in_array($new_status, $valid_statuses)) {
        $_SESSION['message'] = "Invalid design submission ID or status.";
        header("Location: admin_dashboard.php#orders-section");
        exit;
    }

    $stmt = $conn->prepare("UPDATE design_submissions SET status = ? WHERE id = ?");
    if (!$stmt) {
        $_SESSION['message'] = "Database error: " . $conn->error;
        header("Location: admin_dashboard.php#orders-section");
        exit;
    }

    $stmt->bind_param("si", $new_status, $design_submission_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Design submission status updated successfully.";
    } else {
        $_SESSION['message'] = "Failed to update status: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();

    header("Location: admin_dashboard.php#orders-section");
    exit;
} else {
    http_response_code(405);
    echo "Method not allowed.";
    exit;
}
?>
