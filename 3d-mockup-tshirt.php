<?php
session_start();
$isLoggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
$username = $isLoggedIn ? htmlspecialchars($_SESSION["username"]) : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>3D Mockup T-Shirt - LEFO</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="Slaytanic.css" />
    <style>
        body {
            margin: 0;
            background-color: #121212;
            color: #e0e0e0;
            font-family: Arial, Helvetica, sans-serif;
        }
        header {
            position: relative;
            z-index: 10;
            background-color: #1e1e1e;
            box-shadow: 0 2px 4px rgba(0,0,0,0.5);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            font-family: 'Slaytanic', sans-serif;
            color: #00ff9d;
            font-size: 2em;
            text-decoration: none;
        }
        nav a {
            color: #e0e0e0;
            font-weight: bold;
            margin: 0 10px;
            text-decoration: none;
        }
        nav a:hover {
            color: #00ff9d;
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
        #canvas-container {
            width: 100%;
            height: 600px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #222;
        }
        #color-picker {
            margin: 20px auto;
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            max-width: 600px;
        }
        .color-swatch {
            width: 30px;
            height: 30px;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border 0.3s ease;
        }
        .color-swatch.selected {
            border: 3px solid #00ff9d;
        }
    </style>
</head>
<body>
    <img class="image-gradient" src="gradient.png" alt="gradient" />
    <div class="layer-blur"></div>

    <div class="container">
        <header>
            <h1 data-aos="fade-down" data-aos-duration="1500" class="logo">lEFo</h1>
            <nav>
                <a data-aos="fade-down" data-aos-duration="1500" href="index.php" title="Home" class="nav-icon-container">Home</a>
                <a data-aos="fade-down" data-aos-duration="1500" href="about.html">ABOUT US</a>
                <a data-aos="fade-down" data-aos-duration="2000" href="product.php">PRODUCTS</a>
                <a data-aos="fade-down" data-aos-duration="2500" href="submit-design.php">SUBMIT YOUR DESIGN</a>
                <a data-aos="fade-down" data-aos-duration="3000" href="cart.php" title="Cart" class="cart-icon-container">
                    Cart
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

        <main>
            <div id="canvas-container"></div>
            <div id="color-picker">
                <div class="color-swatch" data-color="#F44336" style="background-color: #F44336;"></div>
                <div class="color-swatch" data-color="#E91E63" style="background-color: #E91E63;"></div>
                <div class="color-swatch" data-color="#9C27B0" style="background-color: #9C27B0;"></div>
                <div class="color-swatch" data-color="#673AB7" style="background-color: #673AB7;"></div>
                <div class="color-swatch" data-color="#3F51B5" style="background-color: #3F51B5;"></div>
                <div class="color-swatch" data-color="#2196F3" style="background-color: #2196F3;"></div>
                <div class="color-swatch" data-color="#03A9F4" style="background-color: #03A9F4;"></div>
                <div class="color-swatch" data-color="#00BCD4" style="background-color: #00BCD4;"></div>
                <div class="color-swatch" data-color="#009688" style="background-color: #009688;"></div>
                <div class="color-swatch" data-color="#4CAF50" style="background-color: #4CAF50;"></div>
                <div class="color-swatch" data-color="#8BC34A" style="background-color: #8BC34A;"></div>
                <div class="color-swatch" data-color="#CDDC39" style="background-color: #CDDC39;"></div>
                <div class="color-swatch" data-color="#FFEB3B" style="background-color: #FFEB3B;"></div>
                <div class="color-swatch" data-color="#FFC107" style="background-color: #FFC107;"></div>
                <div class="color-swatch" data-color="#FF5722" style="background-color: #FF5722;"></div>
                <div class="color-swatch" data-color="#FF9800" style="background-color: #FF9800;"></div>
                <div class="color-swatch" data-color="#795548" style="background-color: #795548;"></div>
                <div class="color-swatch" data-color="#9E9E9E" style="background-color: #9E9E9E;"></div>
                <div class="color-swatch" data-color="#607D8B" style="background-color: #607D8B;"></div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/three@0.152.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.152.2/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.152.2/examples/js/controls/OrbitControls.js"></script>
    <script>
        let scene, camera, renderer, controls, tshirt;

        function init() {
            const container = document.getElementById('canvas-container');

            scene = new THREE.Scene();
            camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 1000);
            camera.position.set(0, 1.5, 3);

            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(container.clientWidth, container.clientHeight);
            container.appendChild(renderer.domElement);

            controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.minDistance = 1;
            controls.maxDistance = 5;
            controls.maxPolarAngle = Math.PI / 2;

            const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
            scene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
            directionalLight.position.set(5, 10, 7.5);
            scene.add(directionalLight);

            // Load the 3D T-shirt model (GLTF format)
            const loader = new THREE.GLTFLoader();
            loader.load('mockups/shirts/3d_tshirt_model.gltf', function(gltf) {
                tshirt = gltf.scene;
                tshirt.scale.set(1.5, 1.5, 1.5);
                scene.add(tshirt);
                animate();
            }, undefined, function(error) {
                console.error('An error happened loading the 3D model:', error);
            });
        }

        function animate() {
            requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
        }

        // Change T-shirt color material
        function changeTshirtColor(color) {
            if (!tshirt) return;
            tshirt.traverse(function(child) {
                if (child.isMesh) {
                    if (child.material) {
                        child.material.color.set(color);
                        child.material.needsUpdate = true;
                    }
                }
            });
        }

        // Color picker event listeners
        const colorSwatches = document.querySelectorAll('.color-swatch');
        colorSwatches.forEach(swatch => {
            swatch.addEventListener('click', () => {
                colorSwatches.forEach(s => s.classList.remove('selected'));
                swatch.classList.add('selected');
                const color = swatch.getAttribute('data-color');
                changeTshirtColor(color);
            });
        });

        window.addEventListener('resize', () => {
            const container = document.getElementById('canvas-container');
            camera.aspect = container.clientWidth / container.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(container.clientWidth, container.clientHeight);
        });

        init();
    </script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
      AOS.init();
    </script>
</body>
</html>
