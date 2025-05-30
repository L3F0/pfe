<?php
session_start();
require_once 'db_config.php'; // Your database connection

header('Content-Type: application/json'); // We will always return JSON

$response = ['success' => false, 'message' => 'An unexpected error occurred.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_or_username = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email_or_username) || empty($password)) {
        $response['message'] = "Email/Username and Password are required.";
        echo json_encode($response);
        exit;
    }

    // Determine if input is email or username
    if (filter_var($email_or_username, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT id, username, email, password, is_admin FROM users WHERE email = ?";
    } else {
        $sql = "SELECT id, username, email, password, is_admin FROM users WHERE username = ?";
    }

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email_or_username);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Password is correct, start a new session
                    $_SESSION["loggedin"] = true;
                    $_SESSION["user_id"] = $user['id'];
                    $_SESSION["username"] = $user['username'];
                    $_SESSION["email"] = $user['email']; // Store email in session
                    $_SESSION["is_admin"] = (bool)$user['is_admin'];

                    $response['success'] = true;
                    $response['message'] = "Login successful!";

                    // Determine redirect URL
                    if (isset($_SESSION['redirect_url']) && !empty($_SESSION['redirect_url'])) {
                        $response['redirectUrl'] = $_SESSION['redirect_url'];
                        unset($_SESSION['redirect_url']); // Clear it after use
                    } else {
                        if ($_SESSION["is_admin"]) {
                            $response['redirectUrl'] = 'admin_dashboard.php';
                        } else {
                            $response['redirectUrl'] = 'index.php';
                        }
                    }
                } else {
                    // Password is not valid
                    $response['message'] = "Invalid password.";
                }
            } else {
                // No user found with that email/username
                $response['message'] = "No account found with that email/username.";
            }
        } else {
            // Log error: error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $response['message'] = "Error executing query. Please try again.";
        }
        $stmt->close();
    } else {
        // Log error: error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        $response['message'] = "Database error. Please try again.";
    }
    $conn->close();
} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
exit;
?>