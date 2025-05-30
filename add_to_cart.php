<?php
session_start();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Invalid request.'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = $_POST['product_id'];
    $productName = isset($_POST['product_name']) ? $_POST['product_name'] : 'Unknown Product';
    $productPrice = isset($_POST['product_price']) ? floatval($_POST['product_price']) : 0.0;
    $productImage = isset($_POST['product_image']) ? $_POST['product_image'] : 'default.png'; // Ensure you have a default image
    $productType = isset($_POST['type']) ? $_POST['type'] : 'product'; // Default to 'product'

    // Check for custom design data
    $customDesign = null;
    if (isset($_POST['custom_design'])) {
        $customDesignJson = $_POST['custom_design'];
        $customDesign = json_decode($customDesignJson, true);
    }

    $found = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] === $productId && $item['type'] === $productType) {
            $_SESSION['cart'][$key]['quantity']++;
            $found = true;
            $response['message'] = htmlspecialchars($productName) . ' quantity updated in cart!';
            break;
        }
    }

    if (!$found) {
        $newCartItem = [
            'id' => $productId,
            'name' => $productName,
            'price' => $productPrice,
            'image' => $productImage,
            'quantity' => 1,
            'type' => $productType
        ];
        if ($customDesign !== null) {
            $newCartItem['custom_design'] = $customDesign;
        }
        $_SESSION['cart'][] = $newCartItem;
        $response['message'] = htmlspecialchars($productName) . ' added to cart!';
    }

    $totalItems = 0;
    foreach ($_SESSION['cart'] as $item) {
        $totalItems += $item['quantity'];
    }

    $response['success'] = true;
    $response['cart_total_items'] = $totalItems;

} else {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Invalid request method.';
    } elseif (!isset($_POST['product_id'])) {
        $response['message'] = 'Product ID is missing.';
    }
}

echo json_encode($response);
exit;
?>