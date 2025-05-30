<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>T-shirt Mockup</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="Slaytanic.css" />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <style>
       
        *,
        *::after,
        *::before { box-sizing: border-box }

        :root{
            --color : rgb(255, 255, 255);
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            position: relative;
            height: 2000px;
            background-color: #000000;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        /* End Global Rules  */
        .container{
            width: 90%;
            margin: auto;
            display: flex;
            flex-direction: column;
        }
        .image_container{
            width: 100%;
            height: 500px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .image_container img{
            width: 400px;
            display: block;
            background-color: var(--color);
        }
        .colors{
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100px;
        }
        .colors ul li{
           width: 30px;
           height: 30px;
           /* background-color:var(--color) ; */
           display: inline-block;
           cursor: pointer;
        } 
        .image-gradient {
            position: absolute;
            top: 0;
            right: 0;
            opacity: 0.5;
            z-index: -1;
        }
        .layer-blur {
            height: 0;
            width: 30rem;
            position: absolute;
            top: 20%;
            right: 0;
            box-shadow: 0 0 700px 15px white;
            rotate: -30deg;
            z-index: -1;
        }
    </style>
</head>
<body>
    <img class="image-gradient" src="gradient.png" alt="gradient" />
    <div class="layer-blur"></div>
    <div class="container">
        <div class="image_container">
            <img src="https://i.imgur.com/frhnBT1.png" alt="T-shirt Mockup" />
        </div>
        <div class="colors">
            <ul>
                <li data-color="#F44336" style="background-color: #F44336;"></li>
                <li data-color="#E91E63" style="background-color:#E91E63 ;"></li>
                <li data-color="#9C27B0" style="background-color: #9C27B0;"></li>
                <li data-color="#673AB7" style="background-color:#673AB7 ;"></li>
                <li data-color="#3F51B5" style="background-color: #3F51B5;"></li>
                <li data-color="#2196F3" style="background-color:#2196F3 ;"></li>
                <li data-color="#03A9F4" style="background-color:#03A9F4 ;"></li>
                <li data-color="#00BCD4" style="background-color:#00BCD4 ;"></li>
                <li data-color="#009688" style="background-color:#009688 ;"></li>
                <li data-color="#4CAF50" style="background-color:#4CAF50 ;"></li>
                <li data-color="#8BC34A" style="background-color:#8BC34A ;"></li>
                <li data-color="#CDDC39" style="background-color:#CDDC39 ;"></li>
                <li data-color="#FFEB3B" style="background-color:#FFEB3B ;"></li>
                <li data-color="#FFC107" style="background-color:#FFC107 ;"></li>
                <li data-color="#FF5722" style="background-color:#FF5722 ;"></li>
                <li data-color="#FF9800" style="background-color:#FF9800 ;"></li>
                <li data-color="#795548" style="background-color:#795548 ;"></li>
                <li data-color="#9E9E9E" style="background-color:#9E9E9E ;"></li>
                <li data-color="#607D8B" style="background-color:#607D8B ;"></li>
            </ul>
        </div>
    </div>
    <script>
        let listElements = document.querySelectorAll('li');
        listElements.forEach(element => {
            element.addEventListener('click', function(){
                let clr = this.getAttribute('data-color');
                document.documentElement.style.setProperty('--color', clr);
                listElements.forEach(element=>{
                    element.style.border="none";
                })
                this.style.border="3px solid black";
            })
        });
    </script>
</body>
</html>
