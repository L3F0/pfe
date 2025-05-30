<?php
session_start(); // Start the session at the very beginning
$isLoggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$username = $isLoggedIn ? htmlspecialchars($_SESSION["username"]) : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Your Design - LEFO</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="Slaytanic.css">
    <style>
        body {
            /* Reusing styles from product.html for consistency */
        }
        .logo {
            font-family: 'Slaytanic', sans-serif;
        }
        header a .logo {
            color: inherit;
            text-decoration: none;
        }
        header a:hover .logo {
            color: #fff;
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
            background-color: #d88405;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }
        .main-submit-design {
            padding-top: 40px;
            padding-bottom: 40px;
        }
        .submit-design-title {
            text-align: center;
            margin-bottom: 40px;
            color: white;
            font-size: 2.5em;
            font-weight: bold;
        }
        .customizer-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            padding: 0 20px;
            max-width: 1200px;
            margin: 0 auto;
            color: #f0f0f0;
        }
        .mockup-preview-area {
            flex: 1 1 500px;
            display: flex;
            flex-direction: column; 
            justify-content: center;
            align-items: center;
            background-color: rgba(20, 20, 20, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 20px;
            min-height: 450px; 
        }
        .tshirt-mockup-container { /* This container will hold the new t-shirt image and the design overlay */
            position: relative; 
            width: 350px; 
            height: 400px; 
            user-select: none;
            margin-bottom: 20px; /* Space before color controls */
        }
        #t-shirt { /* New T-shirt image ID */
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain; 
        }
        #design-overlay {
            position: absolute;
            /* top, left, width, height will be set by JS */
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            cursor: move;
            touch-action: none; 
            box-sizing: border-box;
        }

        .interact-resizable .interact-handle {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: rgba(0, 255, 157, 0.8);
            border: 1px solid #fff;
            box-sizing: border-box;
            z-index: 10;
        }
        .interact-resizable .interact-handle-tl { top: -5px; left: -5px; cursor: nwse-resize; }
        .interact-resizable .interact-handle-tr { top: -5px; right: -5px; cursor: nesw-resize; }
        .interact-resizable .interact-handle-bl { bottom: -5px; left: -5px; cursor: nesw-resize; }
        .interact-resizable .interact-handle-br { bottom: -5px; right: -5px; cursor: nwse-resize; }

        .controls-area {
            flex: 1 1 300px;
            background-color: rgba(20, 20, 20, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 20px;
        }
        .controls-area .form-group {
            margin-bottom: 20px;
        }
        .controls-area .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 1em;
            color: #ccc;
        }
        .controls-area .form-group select,
        .controls-area .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #555;
            background-color: #333;
            color: #fff;
            font-size: 1em;
        }
        .controls-area .form-group input[type="file"]::file-selector-button {
            background: linear-gradient(90deg, #00ff9d, #00e08b);
            color: #111;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 10px;
            transition: background 0.3s ease;
        }
        .controls-area .form-group input[type="file"]::file-selector-button:hover {
            background: linear-gradient(90deg, #00e08b, #00c77a);
        }
        .price-display {
            font-size: 1.8em;
            font-weight: bold;
            color: #00ff9d;
            margin-top: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-submit-design {
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
        }
        .btn-submit-design:hover {
            background: linear-gradient(90deg, #e65a50, #f07b6a);
            transform: scale(1.02);
        }

        /* Styles for the new color slider */
        .hue-controls { /* Renamed from .controls to avoid conflict if .controls is generic elsewhere */
          margin-top: 20px;
          color: #ccc; /* Text color for "Color:" label */
          text-align: center;
        }
        .hue-controls strong {
            font-weight: normal; /* Making label less bold, or remove if not needed */
        }
        
        .color-range-wrap {
          --scrubber-width: 5px;
          position: relative;
          display: inline-block;
          vertical-align: middle;
          width: 200px; /* Increased width for better usability */
          height: 25px; /* Adjusted height */
          margin-left: 10px;
          background: linear-gradient(to right, #f00, #ff0, #0f0, #0ff, #00f, #f0f, #f00); /* Full hue spectrum */
          border-radius: 5px; 
        }
        
        .color-range-wrap input[type="range"] {
          position: absolute;
          left:0; 
          top:0;
          height: 100%;
          width: 100%;
          opacity: 0; 
          cursor: pointer;
          margin: 0; /* Reset margin */
          padding: 0; /* Reset padding */
        }
        
        .color-range-wrap .scrubber-trough {
          position: absolute;
          left: 0;
          top: 0;
          width: 100%;
          height: 100%;
          box-sizing: border-box;
          padding-right: var(--scrubber-width); 
          pointer-events: none; 
        }
        
        .color-range-wrap .scrubber {
          position: relative; 
          height: 100%;
          width: var(--scrubber-width);
          background: rgba(0, 0, 0, 0.7); /* Dark semi-transparent scrubber */
          border: 1px solid rgba(255,255,255,0.5); /* Light border for visibility */
          border-radius: 2px; 
          box-sizing: border-box;
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
                <a data-aos="fade-down" data-aos-duration="3000" href="cart.php" title="Cart" class="cart-icon-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    <span class="cart-item-count" id="cart-count-badge">0</span>
                </a>
            </nav>
            <?php if ($isLoggedIn): ?>
                <span style="color: white; margin-right: 15px; font-size: 0.9em;">Hi, <?php echo $username; ?>!</span>
                <a href="logout.php" data-aos="fade-down" data-aos-duration="1500" class="btn-signin" style="text-decoration: none;">LOGOUT</a>
            <?php else: ?>
                <button data-aos="fade-down" data-aos-duration="1500" class="btn-signin">SIGN IN</button>
            <?php endif; ?>
        </header>

        <main class="main-submit-design">
            <h2 data-aos="fade-up" data-aos-duration="1000" class="submit-design-title">Customize Your LEFO Tee</h2>
            <div class="customizer-container" data-aos="fade-up" data-aos-delay="200">
                <div class="mockup-preview-area">
                    <div class="tshirt-mockup-container">
                        <img id="t-shirt" src="https://i.imgur.com/22TDMwh.png" alt="T-shirt Mockup" />
                        <div id="design-overlay"></div>
                    </div>
                    <div class="hue-controls"> <strong>Color: </strong>
                      <div class="color-range-wrap">
                        <input id="hue-range" type="range" step="any" min="0" max="100" value="0"> <div class="scrubber-trough">
                          <div class="scrubber"></div>
                        </div>
                      </div>
                    </div>
                </div>
                <div class="controls-area">
                    <form id="custom-design-form">
                        <div class="form-group">
                            <label for="tshirt-size-custom">Size:</label>
                            <select id="tshirt-size-custom" name="size" required>
                                <option value="">Select Size</option><option value="S">S</option><option value="M">M</option><option value="L">L</option><option value="XL">XL</option><option value="XXL">XXL</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="design-upload">Upload Your Design (PNG/JPG):</label>
                            <input type="file" id="design-upload" name="design_file" accept="image/png, image/jpeg">
                        </div>
                        <div class="price-display">
                            Price: <span id="custom-tshirt-price">$35.00</span>
                        </div>
                        <button type="submit" class="btn-submit-design">Add to Cart & Proceed</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
    <script>
      AOS.init({
        once: true,
        duration: 800,
      });

      // Updated T-shirt element reference
      const shirtImage = document.getElementById('t-shirt'); 
      const customSizeSelect = document.getElementById('tshirt-size-custom');
      const designOverlay = document.getElementById('design-overlay');
      const mockupContainer = designOverlay.parentElement; 
      const designUploadInput = document.getElementById('design-upload');
      const customDesignForm = document.getElementById('custom-design-form');

      // New hue slider elements
      const hueRangeInput = document.getElementById('hue-range');
      const hueScrubber = hueRangeInput.parentNode.querySelector(".scrubber");

      const defaultOverlayWidthPercent = 40;
      const defaultOverlayHeightPercent = 40;
      const initialOverlayPercentages = { top: (100 - defaultOverlayHeightPercent) / 2, left: (100 - defaultOverlayWidthPercent) / 2, width: defaultOverlayWidthPercent, height: defaultOverlayHeightPercent };

      let overlayState = { x: 0, y: 0, width: 0, height: 0 };

      function setOverlayInitialPositionAndSize() {
          if (!mockupContainer || mockupContainer.offsetWidth === 0) { // Ensure container is ready
              requestAnimationFrame(setOverlayInitialPositionAndSize);
              return;
          }
          const containerWidth = mockupContainer.offsetWidth;
          const containerHeight = mockupContainer.offsetHeight;

          overlayState.width = (initialOverlayPercentages.width / 100) * containerWidth;
          overlayState.height = (initialOverlayPercentages.height / 100) * containerHeight;
          overlayState.x = (initialOverlayPercentages.left / 100) * containerWidth;
          overlayState.y = (initialOverlayPercentages.top / 100) * containerHeight;

          designOverlay.style.width = `${overlayState.width}px`;
          designOverlay.style.height = `${overlayState.height}px`;
          designOverlay.style.transform = `translate(${overlayState.x}px, ${overlayState.y}px)`;
          designOverlay.setAttribute('data-x', overlayState.x);
          designOverlay.setAttribute('data-y', overlayState.y);
      }

      function getCart() {
          const cart = localStorage.getItem('cart');
          return cart ? JSON.parse(cart) : [];
      }

      function saveCart(cart) {
          localStorage.setItem('cart', JSON.stringify(cart));
      }

      function updateCartBadge() {
          const cart = getCart();
          const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
          const cartBadge = document.getElementById('cart-count-badge');
          if (cartBadge) {
              cartBadge.textContent = totalItems;
          }
      }
      
      // New function to set hue for T-shirt
      function setShirtHue(huePercentage = 0) {
        if (hueScrubber) { // Check if element exists
            hueScrubber.style.left = huePercentage + "%";
        }
        if (shirtImage) { // Check if element exists
            shirtImage.style.filter = `hue-rotate(${ (huePercentage / 100) * 360 }deg)`;
        }
      }

      if (hueRangeInput) {
          hueRangeInput.addEventListener("input", function() {
            const value = Number(this.value);
            setShirtHue(value);
          });
      }
      
      designUploadInput.addEventListener('change', function(event) {
          const file = event.target.files[0];
          if (file) {
              const reader = new FileReader();
              reader.onload = function(e) {
                  designOverlay.style.backgroundImage = `url('${e.target.result}')`;
                  // Consider resetting overlay to center if a new design is uploaded while it was moved/resized
                  // setOverlayInitialPositionAndSize(); // Uncomment if you want to reset position on new image upload
              }
              reader.readAsDataURL(file);
          } else {
              designOverlay.style.backgroundImage = 'none';
          }
      });

      interact(designOverlay)
        .draggable({
          inertia: true,
          modifiers: [
            interact.modifiers.restrictRect({ restriction: 'parent' })
          ],
          autoScroll: true,
          listeners: { 
            move(event) {
              const target = event.target;
              let x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
              let y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

              target.style.transform = `translate(${x}px, ${y}px)`;
              target.setAttribute('data-x', x);
              target.setAttribute('data-y', y);
              overlayState.x = x;
              overlayState.y = y;
            }
          }
        })
        .resizable({
          edges: { left: true, right: true, bottom: true, top: true },
          modifiers: [
            interact.modifiers.restrictEdges({ outer: 'parent' }),
            interact.modifiers.restrictSize({ min: { width: 50, height: 50 } }),
          ],
          inertia: true,
          listeners: { 
            move(event) {
              const target = event.target;
              let x = parseFloat(target.getAttribute('data-x')) || 0;
              let y = parseFloat(target.getAttribute('data-y')) || 0;

              target.style.width = `${event.rect.width}px`;
              target.style.height = `${event.rect.height}px`;

              x += event.deltaRect.left;
              y += event.deltaRect.top;

              target.style.transform = `translate(${x}px, ${y}px)`;
              target.setAttribute('data-x', x);
              target.setAttribute('data-y', y);
              overlayState.x = x;
              overlayState.y = y;
              overlayState.width = event.rect.width;
              overlayState.height = event.rect.height;
            }
          }
        });
      
      if (shirtImage) {
        shirtImage.addEventListener('load', setOverlayInitialPositionAndSize);
      }
      window.addEventListener('resize', setOverlayInitialPositionAndSize);
      
      window.addEventListener('load', () => {
        if (shirtImage && shirtImage.complete) { // If image already loaded from cache
            setOverlayInitialPositionAndSize();
        }
        updateCartBadge();
        if (hueRangeInput) { // Set initial hue based on slider's default value
            setShirtHue(Number(hueRangeInput.value));
        }

        const signInButtonElement = document.querySelector('button.btn-signin'); 
        if (signInButtonElement) {
            signInButtonElement.addEventListener('click', function() {
                window.location.href = 'signin.php';
            });
        }
      });

      window.addEventListener('storage', function(e) {
          if (e.key === 'cart') {
              updateCartBadge();
          }
      });

      customDesignForm.addEventListener('submit', function(event) {
          event.preventDefault();

          // Get selected hue value instead of color name
          const selectedHue = hueRangeInput ? hueRangeInput.value : '0'; 
          const selectedSize = customSizeSelect.value;
          const price = document.getElementById('custom-tshirt-price').textContent;

          if (selectedSize === "") {
              alert('Please select a size.');
              return;
          }

          if (!designUploadInput.files[0]) {
               alert('Please upload your design.');
               return;
          }

          const formData = new FormData();
          formData.append('product_id', 'custom_' + Date.now());
          formData.append('product_name', 'Custom LEFO Tee');
          formData.append('hue_value', selectedHue); // Store hue value
          formData.append('size', selectedSize);
          formData.append('price', price.replace('$', '')); 
          formData.append('design_file', designUploadInput.files[0]);
          
          formData.append('design_position_x', overlayState.x);
          formData.append('design_position_y', overlayState.y);
          formData.append('design_width', overlayState.width);
          formData.append('design_height', overlayState.height);
          formData.append('base_image_src', shirtImage ? shirtImage.src : ''); // Store base T-shirt image src

          let cart = getCart();
          const newItem = {
              id: formData.get('product_id'),
              name: formData.get('product_name') + ` (Hue: ${selectedHue}, Size: ${selectedSize})`, // Updated name
              price: parseFloat(price.replace('$', '')),
              quantity: 1,
              image: shirtImage ? shirtImage.src : '', // Base image. Actual look depends on hue + design.
              hue: selectedHue, // Store hue for potential display in cart
              designDetails: {
                  file: designUploadInput.files[0] ? designUploadInput.files[0].name : 'N/A',
                  x: overlayState.x,
                  y: overlayState.y,
                  width: overlayState.width,
                  height: overlayState.height
              }
          };
          
          cart.push(newItem);
          saveCart(cart);
          updateCartBadge();
          alert('Custom T-Shirt added to cart! You will be redirected to the cart.');
          window.location.href = 'cart.php'; 

      }); 
      
    </script>
    
</body>
</html>