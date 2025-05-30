<?php
// For debugging: Uncomment these lines to see PHP errors directly in the browser response.
// IMPORTANT: Comment them out again for production or if they break JSON parsing.
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();
require_once 'db_config.php'; // Contains $conn

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'An error occurred.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if $conn was initialized by db_config.php
    if (!isset($conn) || !$conn) {
        $response['message'] = 'Database connection object not found. Check db_config.php.';
        echo json_encode($response);
        exit;
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $response['message'] = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $response['message'] = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match.';
    } else {
        // Check if username or email already exists
        $sql_check = "SELECT id, username, email FROM users WHERE username = ? OR email = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("ss", $username, $email);
            if (!$stmt_check->execute()) {
                $response['message'] = 'Error executing user check: ' . htmlspecialchars($stmt_check->error);
                // In a real app, log $stmt_check->error to a server file instead of exposing to client
            } else {
                $stmt_check->store_result();

                if ($stmt_check->num_rows > 0) {
                    $stmt_check->bind_result($id_db, $username_db, $email_db);
                    $stmt_check->fetch(); // Fetch the conflicting record
                    if (strtolower($username_db) === strtolower($username)) {
                        $response['message'] = 'Username already taken.';
                    } elseif (strtolower($email_db) === strtolower($email)) {
                        $response['message'] = 'Email already registered.';
                    } else {
                        $response['message'] = 'Username or Email already exists.'; // Fallback
                    }
                } else {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Prepare an insert statement
                    $sql_insert = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                    if ($stmt_insert = $conn->prepare($sql_insert)) {
                        $stmt_insert->bind_param("sss", $username, $email, $hashed_password);
                        if ($stmt_insert->execute()) {
                            $response['success'] = true;
                            $response['message'] = 'Registration successful! You can now sign in.';
                        } else {
                            $response['message'] = 'Registration failed: ' . htmlspecialchars($stmt_insert->error);
                            // In a real app, log $stmt_insert->error
                        }
                        $stmt_insert->close();
                    } else {
                         $response['message'] = 'Database error (prepare insert failed): ' . htmlspecialchars($conn->error);
                    }
                }
            }
            $stmt_check->close();
        } else {
            $response['message'] = 'Database error (prepare user check failed): ' . htmlspecialchars($conn->error);
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
exit; // Good practice to exit after sending JSON response
?>