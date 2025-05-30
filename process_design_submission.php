<?php
session_start();
require_once 'db_config.php';

// Check if user is admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("location: signin.php");
    exit;
}

// Check if required parameters are set
if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_POST['submission_id']) || empty($_POST['action'])) {
    $_SESSION['message'] = "Invalid request";
    header("location: admin_dashboard.php#submissions-section");
    exit;
}

$submission_id = (int)$_POST['submission_id'];
$action = $_POST['action'];
$admin_notes = !empty($_POST['admin_notes']) ? trim($_POST['admin_notes']) : '';

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get submission details
    $stmt = $conn->prepare("SELECT * FROM design_submissions WHERE id = ?");
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Design submission not found");
    }
    
    $submission = $result->fetch_assoc();
    $status = '';
    $message = '';
    
    switch ($action) {
        case 'approve':
            // Create a new product from the design
            $status = 'approved';
            $message = 'Design approved and product created successfully';
            
            // Insert product
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock_quantity, image_path, is_design, design_id) 
                                  VALUES (?, ?, 0.00, 1, ?, 1, ?)");
            $product_name = $submission['design_name'] . ' (Design)';
            $stmt->bind_param("sssi", $product_name, $submission['description'], $submission['file_path'], $submission_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create product: " . $conn->error);
            }
            
            $product_id = $conn->insert_id;
            
            // Update submission with product_id
            $stmt = $conn->prepare("UPDATE design_submissions SET status = 'approved', product_id = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("isi", $product_id, $admin_notes, $submission_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update submission: " . $conn->error);
            }
            
            // Add to default category (you may want to make this configurable)
            $default_category = 1; // Assuming 1 is the ID of your default category
            $stmt = $conn->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $product_id, $default_category);
            $stmt->execute(); // Don't throw error if this fails
            break;
            
        case 'reject':
            $status = 'rejected';
            $message = 'Design has been rejected';
            $stmt = $conn->prepare("UPDATE design_submissions SET status = 'rejected', admin_notes = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $admin_notes, $submission_id);
            break;
            
        case 'request_revision':
            $status = 'revision_requested';
            $message = 'Revision requested for design';
            $stmt = $conn->prepare("UPDATE design_submissions SET status = 'revision_requested', admin_notes = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $admin_notes, $submission_id);
            break;
            
        default:
            throw new Exception("Invalid action");
    }
    
    if (!empty($stmt) && !$stmt->execute()) {
        throw new Exception("Failed to update submission status: " . $conn->error);
    }
    
    // Send notification to user (you'll need to implement this function)
    send_design_notification($submission['user_id'], $status, $admin_notes);
    
    // Commit transaction
    $conn->commit();
    
    $_SESSION['message'] = $message;
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['message'] = "Error: " . $e->getMessage();
}

// Redirect back to submissions section
header("location: admin_dashboard.php#submissions-section");

/**
 * Send notification to user about their design submission
 * This is a placeholder - implement your actual notification system here
 */
function send_design_notification($user_id, $status, $notes = '') {
    // Implementation depends on your notification system
    // Could be email, in-app notification, etc.
    // Example:
    /*
    $user = get_user_by_id($user_id);
    $subject = "Your Design Submission Update";
    $message = "Your design submission status has been updated to: " . ucwords(str_replace('_', ' ', $status));
    if (!empty($notes)) {
        $message .= "\n\nAdmin Notes: " . $notes;
    }
    mail($user['email'], $subject, $message);
    */
    return true;
}
?>
