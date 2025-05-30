<?php
session_start(); // Start the session at the very beginning
require_once 'db_config.php'; // Include your database configuration

$isLoggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$username = $isLoggedIn ? htmlspecialchars($_SESSION["username"]) : "";

// Fetch products from the database
$db_products = [];
$sql = "SELECT p.id, p.name, p.price, p.image_path, p.default_color, c.name AS category_name
        FROM products p
        LEFT JOIN product_categories pc ON p.id = pc.product_id
        LEFT JOIN categories c ON pc.category_id = c.id
        ORDER BY c.name, p.name";

// $sql = "SELECT id, name, price, image_path, category, default_color FROM products ORDER BY category, name"; -- Old query causing error
if ($conn && $result = $conn->query($sql)) { // Check if $conn is valid
    while ($row = $result->fetch_assoc()) {
        $db_products[] = $row;
    }
    $result->free();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Products - LEFO</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="Slaytanic.css"> <!-- Link to your Slaytanic font CSS -->
    <style>
        .logo {
            font-family: 'Slaytanic', sans-serif; /* Use Slaytanic font with a fallback */
        }
        .cart-icon-container {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .cart-item-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #d88405; /* Using the orange from your checkout button */
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
            min-width: 18px; /* Ensures circle shape for single digit */
            text-align: center;
        }
        /* Styles for product page */
        .main-products {
            padding-top: 20px; /* Add some space below the header */
        }
        .products-title {
            text-align: center;
            margin-bottom: 40px;
            color: white; /* Assuming white text like index.html */
            font-size: 2.5em;
            font-weight: bold;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); /* Responsive grid */
            gap: 30px; /* Space between product items */
            padding: 0 20px 40px 20px; /* Padding around the grid */
        }
        .product-item {
            background-color: rgba(20, 20, 20, 0.7); /* Darker, semi-transparent background for items */
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 20px;
            color: #f0f0f0; /* Light text color for content */
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.5);
        }
        .product-item img {
            max-width: 100%;
            height: 200px; /* Fixed height for consistency */
            object-fit: cover; /* Ensures image covers the area well */
            border-radius: 8px;
            margin-bottom: 15px;
            background-color: #333; /* Placeholder background for image area */
        }
        .product-item h3 {
            font-size: 1.8em;
            margin-bottom: 10px;
            color: #fff;
        }
        .product-item .price {
            font-size: 1.4em;
            font-weight: bold;
            color: #00ff9d; /* A vibrant price color */
            margin-bottom: 15px;
        }
        .product-item .form-group {
            margin-bottom: 12px;
            text-align: left;
        }
        .product-item .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.9em;
            color: #ccc;
        }
        .product-item .form-group select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #555;
            background-color: #333;
            color: #fff;
            font-size: 1em;
        }
        .btn-add-to-cart {
            background: linear-gradient(90deg, #ff6f61, #ff8c7a);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            text-transform: uppercase;
            transition: background 0.3s ease, transform 0.2s ease;
            width: 100%;
            margin-top: 10px;
        }
        .btn-add-to-cart:hover {
            background: linear-gradient(90deg, #e65a50, #f07b6a);
            transform: scale(1.02);
        }
        
    </style>
</head>
<body>
    <img class="image-gradient" src="gradient.png" alt="gradient">
    <div class="layer-blur"></div>

    <div class="container">
        <header>
            <h1 data-aos="fade-down" data-aos-duration="1500" class="logo">lEFo</h1>
            <nav>
                <a data-aos="fade-down" data-aos-duration="1500" href="index.php" title="Home" class="nav-icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </a>
                <a data-aos="fade-down" data-aos-duration="1500" href="about.html">ABOUT US</a>
                <a data-aos="fade-down" data-aos-duration="2000" href="product.php">PRODUCTS</a>
                <a data-aos="fade-down" data-aos-duration="2500" href="submit-design.php">SUBMIT YOUR DESIGN</a>
                <?php if ($isLoggedIn): ?>
                    <a data-aos="fade-down" data-aos-duration="2700" href="my_orders.php">MY ORDERS</a>
                <?php endif; ?>
                <a data-aos="fade-down" data-aos-duration="<?php echo $isLoggedIn ? '2800' : '3000'; ?>" href="cart.php" title="Cart" class="cart-icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    <span class="cart-item-count" id="cart-count-badge">0</span> <!-- Placeholder count -->
                </a>
            </nav>
            <?php if ($isLoggedIn): ?>
                <span style="color: white; margin-right: 15px; font-size: 0.9em;">Hi, <?php echo $username; ?>!</span>
                <a href="logout.php" data-aos="fade-down" data-aos-duration="1500" class="btn-signin" style="text-decoration: none;">LOGOUT</a>
            <?php else: ?>
                <button data-aos="fade-down" data-aos-duration="1500" class="btn-signin">SIGN IN</button>
            <?php endif; ?>
        </header>

        <main class="main-products">
            <h2 data-aos="fade-up" data-aos-duration="1000" class="products-title">Our Collection</h2>
            <div class="products-grid">
                <?php if (empty($db_products)): ?>
                    <p style="grid-column: 1 / -1; text-align: center; color: white;">No products found at the moment. Please check back later!</p>
                <?php else: ?>
                    <?php foreach ($db_products as $index => $product): ?>
                        <div class="product-item" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                            <img id="<?php echo htmlspecialchars(strtolower($product['category_name'] ?? 'prod') . '-image-' . $product['id']); ?>" 
                                 src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']) . ' - ' . htmlspecialchars($product['default_color'] ?? ''); ?>">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                            <form class="add-to-cart-form" 
                                  data-product-id="<?php echo $product['id']; ?>" 
                                  data-product-name="<?php echo htmlspecialchars($product['name']); ?>" 
                                  data-product-price="<?php echo $product['price']; ?>"
                                  data-product-image-path="<?php echo htmlspecialchars($product['image_path']); // Store the base image path ?>">
                                
                                <?php
                                $product_name_lower = strtolower($product['name']);
                                $category_name = $product['category_name'] ?? '';
                                $product_id_attr = $product['id']; // Use a different var name to avoid conflict if $product['id'] is used directly in HTML ids later
                                $default_color_lower = strtolower($product['default_color'] ?? '');
                                $image_id_prefix = htmlspecialchars(strtolower($category_name ?: 'prod'));

                                if ($category_name === 'Apparel') {
                                    // Common size options for apparel
                                    echo '
                                    <div class="form-group">
                                        <label for="apparel-size-' . $product_id_attr . '">Size:</label>
                                        <select id="apparel-size-' . $product_id_attr . '" name="size" required>
                                            <option value="">Select Size</option><option value="S">S</option><option value="M">M</option><option value="L">L</option><option value="XL">XL</option><option value="XXL">XXL</option>
                                        </select>
                                    </div>';

                                    if (strpos($product_name_lower, 'tee') !== false || strpos($product_name_lower, 't-shirt') !== false) {
                                        // T-Shirt specific color options
                                        ?>
                                        <div class="form-group">
                                            <label for="tshirt-color-<?php echo $product_id_attr; ?>">Color:</label>
                                            <select id="tshirt-color-<?php echo $product_id_attr; ?>" name="color" class="product-color-select"
                                                    data-image-target="<?php echo $image_id_prefix . '-image-' . $product_id_attr; ?>"
                                                    data-image-basepath="mockups/shirts/" data-image-suffix=" t-shirt.png" required>
                                                <option value="">Select Color</option>
                                                <option value="Black" <?php echo ($default_color_lower === 'black' ? 'selected' : ''); ?>>Black</option>
                                                <option value="White" <?php echo ($default_color_lower === 'white' ? 'selected' : ''); ?>>White</option>
                                                <option value="Grey" <?php echo ($default_color_lower === 'grey' ? 'selected' : ''); ?>>Grey</option>
                                                <option value="Navy" <?php echo ($default_color_lower === 'navy' ? 'selected' : ''); ?>>Navy</option>
                                            </select>
                                        </div>
                                        <?php
                                    } elseif (strpos($product_name_lower, 'hoodie') !== false) {
                                        // Hoodie specific color options
                                        ?>
                                        <div class="form-group">
                                            <label for="hoodie-color-<?php echo $product_id_attr; ?>">Color:</label>
                                            <select id="hoodie-color-<?php echo $product_id_attr; ?>" name="color" class="product-color-select"
                                                    data-image-target="<?php echo $image_id_prefix . '-image-' . $product_id_attr; ?>"
                                                    data-image-basepath="mockups/hoodie/" data-image-suffix=" hoodie.png" required>
                                                <option value="">Select Color</option>
                                                <option value="black" <?php echo ($default_color_lower === 'black' ? 'selected' : ''); ?>>black</option>
                                                <option value="Olive" <?php echo ($default_color_lower === 'olive' ? 'selected' : ''); ?>>Olive</option>
                                                <option value="cyan" <?php echo ($default_color_lower === 'cyan' ? 'selected' : ''); ?>>cyan</option>
                                            </select>
                                        </div>
                                        <?php
                                    } else {
                                        // Potentially other apparel types or no color options if not tee/hoodie
                                    } // Closes inner if/elseif/else for tee/hoodie options
                                } // Closes: if ($category_name === 'Apparel')
                                elseif (($product['category_name'] ?? '') === 'Accessories' && strpos(strtolower($product['name']), 'cap') !== false) { // No <?php, use {
                                    // Example for Hats within Accessories
                                    ?>
                                    <div class="form-group">
                                        <label for="hat-size-<?php echo $product['id']; ?>">Size:</label>
                                        <select id="hat-size-<?php echo $product['id']; ?>" name="size" required>
                                            <option value="One Size">One Size Fits Most</option><option value="S/M">S/M</option><option value="L/XL">L/XL</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="hat-color-<?php echo $product['id']; ?>">Color:</label>
                                        <select id="hat-color-<?php echo $product_id_attr; ?>" name="color" class="product-color-select" data-image-target="<?php echo $image_id_prefix . '-image-' . $product_id_attr; ?>" data-image-basepath="mockups/hats/" data-image-suffix=" hat.png" required>
                                            <option value="">Select Color</option><option value="Black" <?php echo ($default_color_lower === 'black' ? 'selected' : ''); ?>>Black</option>
                                        </select>
                                    </div>
                                    <?php
                                } elseif (($product['category_name'] ?? '') === 'Accessories' && strpos(strtolower($product['name']), 'ring') !== false) { // No <?php, use {
                                    // Example for Rings within Accessories
                                    ?>
                                    <div class="form-group">
                                        <label for="ring-size-<?php echo $product_id_attr; ?>">Hand Size (US):</label>
                                        <select id="ring-size-<?php echo $product_id_attr; ?>" name="size" required> <!-- Changed name to size -->
                                            <option value="">Select Size</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option>
                                        </select>
                                    </div>
                                    <?php
                                } else { // No <?php, use { - Default for other categories or products without specific options
                                     ?>
                                     <!-- No specific size/color options, or add a generic one if needed -->
                                <?php } // Closes the main if/elseif/else structure. The <?php endif; ?> 
                                <button type="submit" class="btn-add-to-cart">Add to Cart</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
      AOS.init({
        once: true, // Animations happen once per page load
        duration: 800, // Default duration for animations
      });
       function updateCartBadge(totalItems) {
            const cartBadge = document.getElementById('cart-count-badge');
            if (cartBadge) {
                cartBadge.textContent = totalItems;
            }
        }

        // Add event listeners to all "Add to Cart" forms
        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent default form submission

                const productId = form.dataset.productId;
                const productName = form.dataset.productName;
                const productPrice = form.dataset.productPrice;
                // Get the base image path stored in the form's data attribute
                let productImagePath = form.dataset.productImagePath; 

                // For products with color options, the image might have changed.
                // We need to get the currently displayed image's src if a color was selected.
                const colorSelect = form.querySelector('.product-color-select');
                if (colorSelect && colorSelect.value) {
                    const imageTargetId = colorSelect.dataset.imageTarget;
                    const imageElement = document.getElementById(imageTargetId);
                    if (imageElement) {
                        // Extract the relative path from the full src URL
                        const url = new URL(imageElement.src);
                        productImagePath = url.pathname.substring(url.pathname.indexOf('mockups/')); // Adjust if base path changes
                    }
                }

                const productSizeSelect = form.querySelector('select[name="size"]');
                const productSize = productSizeSelect ? productSizeSelect.value : null;
                const productColor = colorSelect ? colorSelect.value : null;

                if (productSizeSelect && !productSize) {
                    alert('Please select a size.');
                    return;
                }
                if (colorSelect && !productColor) {
                    alert('Please select a color.');
                    return;
                }

                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('product_name', productName);
                formData.append('product_price', productPrice);
                formData.append('product_image', productImagePath); // Send the correct image path
                if (productSize) formData.append('product_size', productSize);
                if (productColor) formData.append('product_color', productColor);

                fetch('add_to_cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success && data.cart_total_items !== undefined) {
                        updateCartBadge(data.cart_total_items); // Update with new total from server
                    }
                })
                .catch(error => {
                    console.error('Error adding to cart:', error);
                    alert('Could not add product to cart. Please try again.');
                });
            });
        });

        // Update cart badge on page load
        <?php
            $initialCartTotalItems = 0;
            if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $cart_item_init) {
                    if (isset($cart_item_init['quantity'])) {
                        $initialCartTotalItems += $cart_item_init['quantity'];
                    }
                }
            }
        ?>
        updateCartBadge(<?php echo $initialCartTotalItems; ?>);

        
        // Image switcher for color selects
        document.querySelectorAll('.product-color-select').forEach(selectElement => {
            selectElement.addEventListener('change', function() {
                const selectedColor = this.value;
                const imageTargetId = this.dataset.imageTarget;
                const imageBasePath = this.dataset.imageBasepath;
                const imageSuffix = this.dataset.imageSuffix;
                const targetImageElement = document.getElementById(imageTargetId);

                if (targetImageElement && selectedColor) {
                    targetImageElement.src = imageBasePath + selectedColor.toLowerCase().replace(/\s+/g, '') + imageSuffix; // Handle spaces in color names for filename
                    targetImageElement.alt = targetImageElement.alt.replace(/- \w+$/, `- ${selectedColor}`);
                }
            });
        });

// sign in buttona
      const signInButton = document.querySelector('.btn-signin');
      if (signInButton) {
          signInButton.addEventListener('click', function() {
              window.location.href = 'signin.php';
          });
      }
    </script>

</body>
</html>