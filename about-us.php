<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <meta charset="UTF-8">
    <title>Ramen Naijiro | About Us</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100..900&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="img/logo.jpg" alt="Ramen Naijiro Logo" class="logo-circle">
                Ramen Naijiro
            </a>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link active" href="about-us.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="track-order.php">Track Order</a></li>
                <li class="nav-item ms-3">
                    <a href="menu.php" class="btn btn-warning">
                        <i class="fa-solid fa-bag-shopping me-1"></i>
                        Order Now
                    </a>
                </li>
            </ul>
        </div>
    </nav>

<!-- OUR STORY SECTION -->
<section class="about-page py-5">
    <div class="container">
        <div class="row align-items-center">
            
            <!-- LEFT: STORY TEXT -->
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2>Our Story</h2>
                <p>
                    Ramen Naijiro started with a simple passion: to bring authentic, flavorful ramen to our
                    community. Each bowl is crafted with care, from the savory broth to the perfectly cooked
                    noodles, combining tradition, quality ingredients, and a touch of love. Our goal is to make
                    every visit a warm and memorable experience.
                </p>
            </div>

            <!-- RIGHT: FACEBOOK REELS CAROUSEL -->
            <div class="col-lg-6">
                <div class="story-reels-box">
                    <div id="storyReelsCarousel" class="carousel slide text-center" data-bs-ride="carousel">
                        
                        <!-- Carousel Inner -->
                        <div class="carousel-inner">

                            <div class="carousel-item active">
                                <div class="reel-wrapper mx-auto">
                                    <iframe
                                        src="https://www.facebook.com/plugins/video.php?href=https://www.facebook.com/reel/1248913082653500/&show_text=0&width=400"
                                        class="reel-iframe"
                                        frameborder="0"
                                        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                                        allowfullscreen>
                                    </iframe>
                                </div>
                            </div>

                            <div class="carousel-item">
                                <div class="reel-wrapper mx-auto">
                                    <iframe
                                        src="https://www.facebook.com/plugins/video.php?href=https://www.facebook.com/reel/769176214890505/&show_text=0&width=400"
                                        class="reel-iframe"
                                        frameborder="0"
                                        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                                        allowfullscreen>
                                    </iframe>
                                </div>
                            </div>

                            <div class="carousel-item">
                                <div class="reel-wrapper mx-auto">
                                    <iframe
                                        src="https://www.facebook.com/plugins/video.php?href=https://www.facebook.com/reel/1670237890118162/&show_text=0&width=400"
                                        class="reel-iframe"
                                        frameborder="0"
                                        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                                        allowfullscreen>
                                    </iframe>
                                </div>
                            </div>

                        </div>

                        <!-- Controls -->
                        <button class="carousel-control-prev custom-carousel-btn" type="button"
                                data-bs-target="#storyReelsCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next custom-carousel-btn" type="button"
                                data-bs-target="#storyReelsCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>

                        <!-- Indicators -->
                        <div class="carousel-indicators mt-3 d-flex justify-content-center">
                            <button type="button" data-bs-target="#storyReelsCarousel" data-bs-slide-to="0"
                                    class="active mx-1"></button>
                            <button type="button" data-bs-target="#storyReelsCarousel" data-bs-slide-to="1"
                                    class="mx-1"></button>
                            <button type="button" data-bs-target="#storyReelsCarousel" data-bs-slide-to="2"
                                    class="mx-1"></button>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

    
    <!-- WHY CHOOSE US SECTION -->
    <section class="why-choose-us py-5">
        <div class="container text-center">
            <h2 class="mb-5">Why Choose Us?</h2>
            <div class="row g-4 justify-content-center">
                <div class="col-md-4">
                    <i class="fa-solid fa-bowl-food fa-3x mb-3"></i>
                    <h5>Authentic Flavors</h5>
                    <p>Experience ramen made with traditional recipes and the freshest ingredients.</p>
                </div>
                <div class="col-md-4">
                    <i class="fa-solid fa-leaf fa-3x mb-3"></i>
                    <h5>Quality Ingredients</h5>
                    <p>We source only premium meats, vegetables, and noodles for every bowl.</p>
                </div>
                <div class="col-md-4">
                    <i class="fa-solid fa-heart fa-3x mb-3"></i>
                    <h5>Passion & Care</h5>
                    <p>Every dish is crafted with love to bring comfort and delight to your table.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p>&copy; 2025 Ramen Naijiro. All rights reserved.</p>
            <a href="https://www.facebook.com/RamenNaijiroGTC" class="text-warning text-decoration-none"><i
                    class="fa-brands fa-facebook"></i></a><br>
            <a href="admin/admin-login.php">Admin Login</a>
            <a href="contact-us.php">Contact Us</a>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>