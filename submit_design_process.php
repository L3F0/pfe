<?php
session_start();
header('Content-Type: application/json');

$mysqli = new mysqli('localhost', 'root', '', 'lefo_db');
if ($mysqli->connect_errno) {
    echo json_encode(['success' => false, 'message' => 'Failed to connect to database']);
    exit;
}

// Check if user is logged in
$user_id = isset($_SESSION['id']) ? intval($_SESSION['id']) : null;

// Validate required fields
if (!isset($_POST['design_name']) || !isset($_POST['contact_email']) || !isset($_FILES['design_file'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$design_name = $mysqli->real_escape_string($_POST['design_name']);
$contact_email = $mysqli->real_escape_string($_POST['contact_email']);
$contact_phone = isset($_POST['contact_phone']) ? $mysqli->real_escape_string($_POST['contact_phone']) : null;
$description = isset($_POST['description']) ? $mysqli->real_escape_string($_POST['description']) : null;
$customization_details = isset($_POST['customization_details']) ? $mysqli->real_escape_string($_POST['customization_details']) : null;

// Handle file upload
$upload_dir = 'uploads/designs/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$uploaded_file = $_FILES['design_file'];
$filename = basename($uploaded_file['name']);
$target_file = $upload_dir . uniqid() . '_' . $filename;

if (!move_uploaded_file($uploaded_file['tmp_name'], $target_file)) {
    echo json_encode(['success' => false, 'message' => 'Failed to upload design file']);
    exit;
}

// Insert into database
$stmt = $mysqli->prepare("INSERT INTO design_submissions (user_id, design_name, description, customization_details, file_path, contact_email, contact_phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('issssss', $user_id, $design_name, $description, $customization_details, $target_file, $contact_email, $contact_phone);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Design submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database insert failed']);
}

$stmt->close();
$mysqli->close();
?>
