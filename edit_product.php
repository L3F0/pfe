<?php
session_start();
require_once 'db_config.php';

// Admin check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("location: signin.php");
    exit;
}

$product_id = null;
$product_name = '';
$product_description = '';
$product_price = '';
$product_image_path = '';
$product_default_color = '';
$product_stock_quantity = 0;
$product_categories_ids = []; // Store IDs of categories product belongs to

$page_title = "Create New Product";
$form_action = "process_product_edit.php";

$all_categories = [
    ['id' => 1, 'name' => 'T-shirt'],
    ['id' => 2, 'name' => 'Hoodie'],
    ['id' => 3, 'name' => 'Hat'],
    ['id' => 4, 'name' => 'Accessories'],
];

if (isset($_GET['id'])) {
    $product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($product_id) {
        $page_title = "Edit Product";

        $sql_product = "SELECT * FROM products WHERE id = ?";
        if ($stmt_product = $conn->prepare($sql_product)) {
            $stmt_product->bind_param("i", $product_id);
            $stmt_product->execute();
            $result_product = $stmt_product->get_result();
            if ($product_data = $result_product->fetch_assoc()) {
                $product_name = $product_data['name'];
                $product_description = $product_data['description'];
                $product_price = $product_data['price'];
                $product_image_path = $product_data['image_path'];
                $product_default_color = $product_data['default_color'];
                $product_stock_quantity = $product_data['stock_quantity'];
            } else {
                $_SESSION['message'] = "Error: Product not found.";
                header("Location: admin_dashboard.php#products-section");
                exit;
            }
            $stmt_product->close();

            // Fetch current categories for this product
            $sql_prod_cat = "SELECT category_id FROM product_categories WHERE product_id = ?";
            if ($stmt_prod_cat = $conn->prepare($sql_prod_cat)) {
                $stmt_prod_cat->bind_param("i", $product_id);
                $stmt_prod_cat->execute();
                $result_prod_cat = $stmt_prod_cat->get_result();
                while ($row_prod_cat = $result_prod_cat->fetch_assoc()) {
                    $product_categories_ids[] = $row_prod_cat['category_id'];
                }
                $stmt_prod_cat->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - LEFO Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Slaytanic.css">
    <style>
        body { background-color: #1a1a1a; color: #f0f0f0; }
        .logo { font-family: 'Slaytanic', sans-serif; }
        .edit-product-container {
            max-width: 700px; margin: 40px auto; padding: 30px;
            background-color: rgba(20, 20, 20, 0.85); border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.5);
        }
        .edit-product-container h2 { text-align: center; margin-bottom: 30px; color: white; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #ccc; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: 100%; padding: 12px; border-radius: 5px; border: 1px solid #555;
            background-color: #333; color: #fff; font-size: 1em; box-sizing: border-box;
        }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .form-group .category-checkboxes label { font-weight: normal; margin-right: 15px; }
        .form-group .category-checkboxes input[type="checkbox"] { margin-right: 5px; }
        .btn-submit-product {
            background: linear-gradient(90deg, #d88405,rgb(121, 78, 13)); color: #111;
            padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer;
            font-size: 1.1em; font-weight: bold; text-transform: uppercase;
            transition: background 0.3s ease; display: block; width: 100%; margin-top: 10px;
        }
        .btn-submit-product:hover { background: linear-gradient(90deg, #d88405, rgb(121, 78, 13))); }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #d88405; text-decoration: none; }
        .btn-back:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <img class="image-gradient" src="gradient.png" alt="gradient">
    <div class="layer-blur"></div>

    <div class="container">
        <header style="padding: 20px 0; text-align:center;">
            <h1 class="logo" style="font-size: 2.5em; color:white;">LEFO Admin</h1>
        </header>

        <main>
            <div class="edit-product-container">
                <a href="admin_dashboard.php#products-section" class="btn-back">&laquo; Back to Products</a>
                <h2><?php echo $page_title; ?></h2>

                <?php if(isset($_SESSION['form_error'])): ?>
                    <div style="padding: 10px; margin-bottom: 15px; background-color: rgba(255,0,0,0.3); color: white; border-radius: 5px;">
                        <?php echo $_SESSION['form_error']; unset($_SESSION['form_error']); ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo $form_action; ?>" method="POST" enctype="multipart/form-data">
                    <?php if ($product_id): ?>
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="product_name">Product Name</label>
                        <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product_name); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="product_description">Description</label>
                        <textarea id="product_description" name="product_description"><?php echo htmlspecialchars($product_description); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="product_price">Price</label>
                        <input type="number" id="product_price" name="product_price" value="<?php echo htmlspecialchars($product_price); ?>" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="product_image">Product Image</label>
                        <input type="file" id="product_image" name="product_image" accept="image/*" <?php echo ($product_id) ? '' : 'required'; ?>>
                        <?php if ($product_image_path): ?>
                            <small style="color:#aaa; display:block; margin-top:5px;">Current image: <?php echo htmlspecialchars($product_image_path); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="product_default_color">Default Color (Optional)</label>
                        <input type="text" id="product_default_color" name="product_default_color" value="<?php echo htmlspecialchars($product_default_color); ?>">
                    </div>

                    <div class="form-group">
                        <label for="product_stock_quantity">Stock Quantity</label>
                        <input type="number" id="product_stock_quantity" name="product_stock_quantity" value="<?php echo htmlspecialchars($product_stock_quantity); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Categories</label>
                        <div class="category-checkboxes">
                            <?php if (!empty($all_categories)): ?>
                                <?php foreach ($all_categories as $category): ?>
                                    <label>
                                        <input type="radio" name="category" value="<?php echo $category['id']; ?>" 
                                               <?php echo in_array($category['id'], $product_categories_ids) ? 'checked' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No categories found. Please add categories first.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" name="submit_product" class="btn-submit-product">
                        <?php echo ($product_id) ? 'Update Product' : 'Create Product'; ?>
                    </button>
                </form>
            </div>
        </main>
    </div>
    <?php if(isset($conn)) $conn->close(); ?>
</body>
</html>