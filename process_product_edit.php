<?php
session_start();
require_once 'db_config.php';

// Admin check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    $_SESSION['message'] = "Error: Unauthorized access.";
    header("location: signin.php");
    exit;
}

if (isset($_POST['submit_product'])) {
    $product_id = isset($_POST['product_id']) ? filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT) : null;
    $name = filter_input(INPUT_POST, 'product_name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'product_description', FILTER_SANITIZE_STRING); // Or allow some HTML if needed
    $price = filter_input(INPUT_POST, 'product_price', FILTER_VALIDATE_FLOAT);
    $default_color = filter_input(INPUT_POST, 'product_default_color', FILTER_SANITIZE_STRING);
    $stock_quantity = filter_input(INPUT_POST, 'product_stock_quantity', FILTER_VALIDATE_INT);
    $selected_categories = isset($_POST['category']) ? [$_POST['category']] : [];
    
    // Handle file upload for product image
    $image_path = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_dir = 'uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_tmp = $_FILES['product_image']['tmp_name'];
        $file_name = basename($_FILES['product_image']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($file_ext, $allowed_exts)) {
            $_SESSION['form_error'] = "Error: Invalid image file type. Allowed types: jpg, jpeg, png, gif, webp.";
            if ($product_id) {
                header("Location: edit_product.php?id=" . $product_id);
            } else {
                header("Location: edit_product.php");
            }
            exit;
        }
        $new_file_name = uniqid('prod_', true) . '.' . $file_ext;
        $destination = $upload_dir . $new_file_name;
        if (!move_uploaded_file($file_tmp, $destination)) {
            $_SESSION['form_error'] = "Error: Failed to upload image file.";
            if ($product_id) {
                header("Location: edit_product.php?id=" . $product_id);
            } else {
                header("Location: edit_product.php");
            }
            exit;
        }
        $image_path = $destination;
    } else {
        // If no new file uploaded and editing existing product, keep old image path
        if ($product_id) {
            $sql_old_image = "SELECT image_path FROM products WHERE id = ?";
            $stmt_old_image = $conn->prepare($sql_old_image);
            if ($stmt_old_image) {
                $stmt_old_image->bind_param("i", $product_id);
                $stmt_old_image->execute();
                $stmt_old_image->bind_result($old_image_path);
                if ($stmt_old_image->fetch()) {
                    $image_path = $old_image_path;
                }
                $stmt_old_image->close();
            }
        }
    }

    // Basic validation
    if (empty($name) || $price === false || $price < 0 || empty($image_path) || $stock_quantity === false || $stock_quantity < 0) {
        $_SESSION['form_error'] = "Error: Please fill in all required fields with valid data (Name, Price, Image, Stock).";
        if ($product_id) {
            header("Location: edit_product.php?id=" . $product_id);
        } else {
            header("Location: edit_product.php");
        }
        exit;
    }

    $conn->begin_transaction();

    try {
        if ($product_id) { // Update existing product
            $sql = "UPDATE products SET name = ?, description = ?, price = ?, image_path = ?, default_color = ?, stock_quantity = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Prepare failed (update product): " . $conn->error);
            $stmt->bind_param("ssdssii", $name, $description, $price, $image_path, $default_color, $stock_quantity, $product_id);
        } else { // Insert new product
            $sql = "INSERT INTO products (name, description, price, image_path, default_color, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Prepare failed (insert product): " . $conn->error);
            $stmt->bind_param("ssdssi", $name, $description, $price, $image_path, $default_color, $stock_quantity);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed (product): " . $stmt->error);
        }

        $current_product_id = $product_id ? $product_id : $conn->insert_id;
        $stmt->close();

        // Handle categories
        // 1. Delete existing categories for this product
        $sql_delete_cat = "DELETE FROM product_categories WHERE product_id = ?";
        $stmt_delete_cat = $conn->prepare($sql_delete_cat);
        if (!$stmt_delete_cat) throw new Exception("Prepare failed (delete categories): " . $conn->error);
        $stmt_delete_cat->bind_param("i", $current_product_id);
        if (!$stmt_delete_cat->execute()) {
            throw new Exception("Execute failed (delete categories): " . $stmt_delete_cat->error);
        }
        $stmt_delete_cat->close();

        // 2. Insert selected categories
        if (!empty($selected_categories)) {
            $sql_insert_cat = "INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)";
            $stmt_insert_cat = $conn->prepare($sql_insert_cat);
            if (!$stmt_insert_cat) throw new Exception("Prepare failed (insert categories): " . $conn->error);

            foreach ($selected_categories as $category_id) {
                $valid_category_id = filter_var($category_id, FILTER_VALIDATE_INT);
                if ($valid_category_id) {
                    $stmt_insert_cat->bind_param("ii", $current_product_id, $valid_category_id);
                    if (!$stmt_insert_cat->execute()) {
                        // Log this error, but maybe don't halt everything if one category fails
                        error_log("Failed to insert category ID {$valid_category_id} for product ID {$current_product_id}: " . $stmt_insert_cat->error);
                    }
                }
            }
            $stmt_insert_cat->close();
        }

        $conn->commit();
        $_SESSION['message'] = $product_id ? "Product updated successfully." : "Product created successfully.";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Error processing product: " . $e->getMessage();
        // Log the detailed error: error_log("Product processing error: " . $e->getMessage());
        
        // Redirect back to form with error
        $_SESSION['form_error'] = "Database error: " . $e->getMessage();
        if ($product_id) {
            header("Location: edit_product.php?id=" . $product_id);
        } else {
            header("Location: edit_product.php");
        }
        exit;
    }

} else {
    $_SESSION['message'] = "Error: Invalid request.";
}

if (isset($conn)) $conn->close();

header("Location: admin_dashboard.php#products-section");
exit;

?>