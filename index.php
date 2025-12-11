<?php
// index.php - Ramen Naijiro landing page

// Branch list (Luzon branches)
$branches = [
    [
        'name'     => 'General Trias - Cavite',
        'address'  => 'General Trias, Cavite',
        'hours'    => '12:00 PM – 11:00 PM (Daily)',
        'services' => 'Dine-in, Take-out, Delivery',
        'note'     => 'Original Cavite branch.',
        'map_url'  => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d61861.61069072577!2d120.82814704863281!3d14.291046600000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d504dab7a9f3%3A0x7dd9ea0a753dbcb0!2sRamen%20Naijiro%20-%20General%20Trias!5e0!3m2!1sen!2sph!4v1765280367188!5m2!1sen!2sph'
    ],
    [
        'name'     => 'Dasmariñas - Cavite',
        'address'  => 'Dasmariñas City, Cavite',
        'hours'    => '12:00 PM – 11:00 PM (Daily)',
        'services' => 'Dine-in, Take-out, Delivery',
        'note'     => 'Dasma branch for nearby universities and residents.',
        'map_url'  => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d61861.61069072577!2d120.82814704863281!3d14.291046600000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d5003579fac3%3A0x2b2c70bcabb56dbd!2sRamen%20Naijiro%20Dasmari%C3%B1as!5e0!3m2!1sen!2sph!4v1765280386284!5m2!1sen!2sph'
    ],
    [
        'name'     => 'Odasiba',
        'address'  => 'Metro Manila (Odasiba area)',
        'hours'    => '12:00 PM – 11:00 PM (Daily)',
        'services' => 'Dine-in, Take-out',
        'note'     => 'Manila-side branch for city customers.',
        'map_url'  => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d61767.39006469681!2d120.96472564863282!3d14.629703199999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b7002fc02fdd%3A0xf4157e2ec351bcdb!2sRamen%20Naijiro%20Odasiba!5e0!3m2!1sen!2sph!4v1765280400462!5m2!1sen!2sph'
    ],
    [
        'name'     => 'Marikina',
        'address'  => 'Marikina City',
        'hours'    => '12:00 PM – 11:00 PM (Daily)',
        'services' => 'Dine-in, Take-out, Delivery',
        'note'     => 'Branch serving Marikina and nearby areas.',
        'map_url'  => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d61767.39006469681!2d120.96472564863282!3d14.629703199999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b926b8466ae7%3A0xa2920c45af08db26!2sRamen%20Naijiro%20Marikina!5e0!3m2!1sen!2sph!4v1765280409313!5m2!1sen!2sph'
    ],
    [
        'name'     => 'Cainta',
        'address'  => 'Cainta, Rizal',
        'hours'    => '12:00 PM – 11:00 PM (Daily)',
        'services' => 'Dine-in, Take-out, Delivery',
        'note'     => 'Rizal-side branch for Cainta customers.',
        'map_url'  => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d61767.39006469681!2d120.96472564863282!3d14.629703199999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b939fd5452f3%3A0x1804e4a11053812!2sRamen%20Naijiro%20Cainta!5e0!3m2!1sen!2sph!4v1765280427338!5m2!1sen!2sph'
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ramen Naijiro | Delicious Ramen Online</title>
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
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about-us.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="track-order.php">Track Order</a></li>
                <li class="nav-item ms-3">
                    <button class="btn btn-warning"><i class="fa-solid fa-bag-shopping me-1"></i> Order Now</button>
                </li>
            </ul>
        </div>
    </nav>

<!-- HERO SECTION -->
<section class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content container">
        <h1 class="display-3">Welcome to Ramen Naijiro</h1>
        <p class="lead">Delicious ramen delivered straight to your door.</p>
        <a href="menu.php" class="btn btn-warning btn-lg mt-3">
            Order Now
        </a>
    </div>
</section>

<!-- FEATURED MENU SECTION -->
<section class="featured-menu py-5">
    <div class="container">
        <h2 class="mb-5 text-center">Featured Ramen</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <img src="img/index img/shoyu.jpg" class="card-img-top" alt="Shoyu Ramen">
                    <div class="card-body">
                        <h5 class="card-title">Shoyu Ramen</h5>
                        <p class="card-text">Classic soy sauce ramen with tender noodles and savory broth.</p>
                        <a href="menu.php" class="btn btn-warning">Order Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <img src="img/index img/tonkotsu.jpg" class="card-img-top" alt="Tonkotsu Ramen">
                    <div class="card-body">
                        <h5 class="card-title">Tonkotsu Ramen</h5>
                        <p class="card-text">Rich pork bone broth ramen, creamy and full of flavor.</p>
                        <a href="menu.php" class="btn btn-warning">Order Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <img src="img/index img/tantanmen.jpg" class="card-img-top" alt="Tantanmen Ramen">
                    <div class="card-body">
                        <h5 class="card-title">Tantanmen Ramen</h5>
                        <p class="card-text">Savory miso-based ramen, served with fresh vegetables and noodles.</p>
                        <a href="menu.php" class="btn btn-warning">Order Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- GALLERY + BRANCHES SIDE-BY-SIDE SECTION -->
<section class="gallery py-5">
    <div class="container">
        <div class="row align-items-start">

            <!-- Gallery (Left Column) -->
            <div class="gallery-images col-lg-8 mb-4">
                <h2 class="mb-4">Gallery</h2>
                <div class="row g-3">
                    <div class="col-12 col-md-4 mb-3">
                        <img src="img/index img/gallery4.jpg" class="img-fluid rounded gallery-img" alt="Gallery Image 1">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <img src="img/index img/gallery5.jpg" class="img-fluid rounded gallery-img" alt="Gallery Image 2">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <img src="img/index img/gallery3.jpg" class="img-fluid rounded gallery-img" alt="Gallery Image 3">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <img src="img/index img/gallery9.jpg" class="img-fluid rounded gallery-img" alt="Gallery Image 4">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <img src="img/index img/gallery1.jpg" class="img-fluid rounded gallery-img" alt="Gallery Image 5">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <img src="img/index img/gallery8.jpg" class="img-fluid rounded gallery-img" alt="Gallery Image 6">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <img src="img/index img/gallery12.jpg" class="img-fluid rounded gallery-img" alt="Gallery Image 7">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <img src="img/index img/gallery7.jpg" class="img-fluid rounded gallery-img" alt="Gallery Image 8">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <img src="img/index img/gallery11.jpg" class="img-fluid rounded gallery-img" alt="Gallery Image 9">
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <img src="img/index img/gallery10.jpg" class="img-fluid rounded gallery-img" alt="Gallery Image 10">
                    </div>
                </div>
            </div>

            <!-- Branches / Visit Us (Right Column) -->
            <div class="visit-us col-lg-4">
                <h3 class="mb-3">Visit Us</h3>
                <p class="text-muted small mb-3">
                    Select your nearest branch to view location and services.
                </p>

                <!-- Branch Selector -->
                <select id="branchSelect" class="form-select mb-3">
                    <option value="" disabled selected>Select a branch</option>
                    <?php foreach ($branches as $key => $branch): ?>
                        <option value="<?php echo $key; ?>">
                            <?php echo htmlspecialchars($branch['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Branch Details + Map -->
                <div id="branchDetails" class="border rounded p-3 mb-3" style="display:none;">
                    <h5 id="branchName"></h5>
                    <p class="mb-1">
                        <i class="fa-regular fa-clock me-2"></i>
                        <strong id="branchHours"></strong>
                    </p>
                    <p class="mb-1">
                        <i class="fa-solid fa-location-dot me-2"></i>
                        <span id="branchAddress"></span>
                    </p>
                    <p class="mb-1">
                        <i class="fa-solid fa-truck me-2"></i>
                        <span id="branchServices"></span>
                    </p>
                    <p class="mb-2 text-muted small" id="branchNote"></p>

                    <iframe id="branchMap"
                            width="100%" height="200"
                            loading="lazy" allowfullscreen
                            style="border:0; border-radius:8px; display:none;">
                    </iframe>
                </div>

                <p class="mt-3 small">
                    For directions and delivery coverage, you can also message us on our
                    <a href="https://www.facebook.com/RamenNaijiroGTC" class="text-decoration-none">
                        Facebook page
                    </a>.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- MINI PAGE LINKS SECTION -->
<section class="mini-link py-5">
    <div class="container text-center">

        <h2 class="mb-4">Explore More</h2>
        <div class="row g-4 justify-content-center">

            <!-- Menu Link Card -->
            <div class="col-md-4">
                <a href="menu.php" class="text-decoration-none">
                    <div class="card h-100 shadow-sm mini-link-card">
                        <div class="card-body">
                            <i class="fa-solid fa-bowl-food fa-3x mb-3"></i>
                            <h5 class="card-title">View Menu</h5>
                            <p class="card-text">See our full ramen selection.</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Track Order Link Card -->
            <div class="col-md-4">
                <a href="track-order.php" class="text-decoration-none">
                    <div class="card h-100 shadow-sm mini-link-card">
                        <div class="card-body">
                            <i class="fa-solid fa-receipt fa-3x mb-3"></i>
                            <h5 class="card-title">Track Your Order</h5>
                            <p class="card-text">Check the status of your order.</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- About Us Link Card -->
            <div class="col-md-4">
                <a href="about-us.php" class="text-decoration-none">
                    <div class="card h-100 shadow-sm mini-link-card">
                        <div class="card-body">
                            <i class="fa-solid fa-store fa-3x mb-3"></i>
                            <h5 class="card-title">About Us</h5>
                            <p class="card-text">Learn more about Ramen Naijiro.</p>
                        </div>
                    </div>
                </a>
            </div>

        </div>

    </div>
</section>

<!-- FOOTER -->
<footer class="bg-dark text-white py-4 mt-auto">
    <div class="container text-center">
        <p>&copy; 2025 Ramen Naijiro. All rights reserved.</p>
        <a href="https://www.facebook.com/RamenNaijiroGTC" class="text-warning text-decoration-none">
            <i class="fa-brands fa-facebook"></i>
        </a><br>
        <a href="admin/admin-login.php">Admin Login</a> 
        <a href="contact-us.php">Contact Us</a>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Pass PHP branch data into JS
const branches = <?php echo json_encode($branches, JSON_UNESCAPED_UNICODE); ?>;

const branchSelect   = document.getElementById('branchSelect');
const branchDetails  = document.getElementById('branchDetails');
const branchName     = document.getElementById('branchName');
const branchHours    = document.getElementById('branchHours');
const branchAddress  = document.getElementById('branchAddress');
const branchServices = document.getElementById('branchServices');
const branchNote     = document.getElementById('branchNote');
const branchMap      = document.getElementById('branchMap');

branchSelect.addEventListener('change', function () {
    const index = this.value;
    if (index === '') {
        branchDetails.style.display = 'none';
        branchMap.style.display = 'none';
        return;
    }

    const b = branches[index];

    branchDetails.style.display = 'block';
    branchName.textContent      = b.name || '';
    branchHours.textContent     = b.hours || '';
    branchAddress.textContent   = b.address || '';
    branchServices.textContent  = b.services || '';
    branchNote.textContent      = b.note || '';

    if (b.map_url && b.map_url.trim() !== '') {
        branchMap.src = b.map_url;
        branchMap.style.display = 'block';
    } else {
        branchMap.src = '';
        branchMap.style.display = 'none';
    }
});
</script>

</body>
</html>
