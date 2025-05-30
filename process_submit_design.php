<?php
session_start();
require_once 'db_config.php';

// Ensure user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a design.']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'User ID not found in session.']);
    exit;
}

// Check if form data and file are present
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_FILES['design_file']) || $_FILES['design_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Design file upload failed.']);
    exit;
}

$design_name = filter_input(INPUT_POST, 'design_name', FILTER_SANITIZE_STRING);
$description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
$contact_email = filter_input(INPUT_POST, 'contact_email', FILTER_VALIDATE_EMAIL);
$contact_phone = filter_input(INPUT_POST, 'contact_phone', FILTER_SANITIZE_STRING);
$customization_details = filter_input(INPUT_POST, 'customization_details', FILTER_UNSAFE_RAW); // JSON string

if (!$design_name || !$contact_email) {
    echo json_encode(['success' => false, 'message' => 'Design name and contact email are required.']);
    exit;
}

// Handle file upload
$upload_dir = 'uploads/designs/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$filename = basename($_FILES['design_file']['name']);
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$allowed_exts = ['png', 'jpg', 'jpeg'];

if (!in_array($ext, $allowed_exts)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PNG and JPG are allowed.']);
    exit;
}

$new_filename = uniqid('design_', true) . '.' . $ext;
$target_path = $upload_dir . $new_filename;

if (!move_uploaded_file($_FILES['design_file']['tmp_name'], $target_path)) {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
    exit;
}

// Insert into database
$stmt = $conn->prepare("INSERT INTO design_submissions (user_id, design_name, description, customization_details, file_path, contact_email, contact_phone, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending_review', NOW())");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("issssss", $user_id, $design_name, $description, $customization_details, $target_path, $contact_email, $contact_phone);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Design submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save design submission: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
