<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ramen Naijiro | Menu</title>
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
                <li class="nav-item"><a class="nav-link" href="about-us.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link active" href="menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="track-order.php">Track Order</a></li>
                <li class="nav-item ms-3">
                    <button class="btn btn-warning"><i class="fa-solid fa-bag-shopping me-1"></i> Order Now</button>
                </li>
            </ul>
        </div>
    </nav>

    <!-- PAGE HEADER -->
    <section class="page-header text-center py-5">
        <div class="container">
            <h1 class="display-4">Our Menu</h1>
            <p class="lead">Explore our menu and order your favorite ramen today</p>
        </div>
    </section>

    <!-- SIDE CART -->
    <div id="side-cart" class="side-cart d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <h5>Your Order</h5>
            <!-- Close Button with Bootstrap sizing + custom class -->
            <button id="close-cart" class="btn btn-sm side-cart-btn"><i class="fa-solid fa-xmark"></i></button>
        </div>
    
        <!-- Scrollable cart content -->
        <div class="cart-content flex-grow-1 overflow-auto px-3">
            <ul id="cart-items" class="list-group list-group-flush mb-3"></ul>
    
            <!-- Delivery or Pickup -->
            <div class="mb-3">
                <label class="form-label fw-bold">Order Type:</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="order-type" id="pickup" value="Pickup" checked>
                    <label class="form-check-label" for="pickup">Pickup</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="order-type" id="delivery" value="Delivery">
                    <label class="form-check-label" for="delivery">Delivery</label>
                </div>
            </div>
    
            <!-- Payment Method -->
            <div class="mb-3">
                <label class="form-label fw-bold">Payment Method:</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment-method" id="gcash" value="GCash" checked>
                    <label class="form-check-label" for="gcash">GCash</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment-method" id="cash" value="Cash">
                    <label class="form-check-label" for="cash">Cash</label>
                </div>
            </div>
    
            <!-- Customer Notes -->
            <div class="mb-3">
                <label for="customer-notes" class="form-label fw-bold">Notes for the Owner:</label>
                <textarea class="form-control" id="customer-notes" rows="2" placeholder="Leave a note..."></textarea>
            </div>
        </div>
    
        <div class="p-3 border-top">
            <p class="fw-bold">Total: ₱<span id="cart-total">0</span></p>
            <!-- Checkout Button with Bootstrap width + custom class -->
            <button class="btn side-cart-btn w-100" id="checkout-btn">Checkout</button>
        </div>
    </div>

    <!-- AREA COVERAGE CHECK -->
    <section class="branch-form-section py-5">
        <div class="container">
            <div class="card border-0 shadow-sm p-4">
                <h2 class="text-center mb-3 text-uppercase fw-bold">
                    CHECK TO SEE IF WE DELIVER IN YOUR AREA
                </h2>

                <form id="coverageForm" class="row g-3 align-items-center justify-content-center">

                    <!-- Province -->
                    <div class="col-md-3">
                        <select class="form-select" id="province-select">
                            <option value="" selected disabled>Province</option>
                            <!-- options filled by JS -->
                        </select>
                    </div>

                    <!-- City / Municipality -->
                    <div class="col-md-3">
                        <select class="form-select" id="city-select" disabled>
                            <option value="" selected disabled>City / Municipality</option>
                        </select>
                    </div>

                    <!-- Barangay / Subdivision -->
                    <div class="col-md-3">
                        <select class="form-select" id="barangay-select" disabled>
                            <option value="" selected disabled>Barangay / Subdivision</option>
                        </select>
                    </div>

                    <!-- Delivery / Pick-up -->
                    <div class="col-md-2 d-flex align-items-center">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="coverage-order-type"
                                   id="cov-delivery" value="Delivery" checked>
                            <label class="form-check-label" for="cov-delivery">Delivery</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="coverage-order-type"
                                   id="cov-pickup" value="Pickup">
                            <label class="form-check-label" for="cov-pickup">Pick-up</label>
                        </div>
                    </div>

                    <!-- Change My Location button -->
                    <div class="col-md-2">
                        <button type="button" class="btn btn-warning w-100" id="change-location-btn">
                            Change My Location
                        </button>
                    </div>

                    <!-- Hidden fields to feed checkout later -->
                    <input type="hidden" id="selected-branch-id">
                    <input type="hidden" id="selected-branch-name">
                    <input type="hidden" id="selected-province">
                    <input type="hidden" id="selected-city">
                    <input type="hidden" id="selected-barangay">
                    <input type="hidden" id="delivery-allowed" value="0">
                </form>

                <div id="coverage-result" class="text-center small mt-3"></div>

                <p class="text-center text-muted small mt-2 mb-0">
                    Can’t find your location? You might want to try our Pick up option instead
                    or contact your nearest Ramen Naijiro branch.
                </p>
            </div>
        </div>
    </section>



    <!-- MENU ITEMS / RAMEN ORDER SECTION -->
    <section class="ramen-section py-5">
        <div class="container">
            <div class="row g-4">
                <h1>Ramen</h1>

                <!-- Shoyu Ramen -->
                <div class="col-md-3">
                    <div class="card h-100 text-center menu-card">
                        <img src="img/menu img/shoyu3.jpg" class="card-img-top" alt="Shoyu Ramen">
                        <div class="card-body">
                            <h5 class="card-title">Shoyu Ramen</h5>
                            <p class="card-text">soy sauce - chashu pork - beansprouts - leeks - egg</p>
                            <p class="fw-bold">S 169 / R 199 / L 229</p>
                            <button class="btn btn-warning add-to-order-btn" data-name="Shoyu Ramen"
                                data-sizes="S:169,R:199,L:229">
                                <i class="fa-solid fa-bag-shopping me-1"></i> Add to Order
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tonkotsu Ramen -->
                <div class="col-md-3">
                    <div class="card h-100 text-center menu-card">
                        <img src="img/menu img/tonkotsu2.jpg" class="card-img-top" alt="Tonkotsu Ramen">
                        <div class="card-body">
                            <h5 class="card-title">Tonkotsu Ramen</h5>
                            <p class="card-text">chashu pork - black fungus - spring onions - leeks - egg</p>
                            <p class="fw-bold">S 159 / R 189 / L 219</p>
                            <button class="btn btn-warning add-to-order-btn" data-name="Tonkotsu Ramen"
                                data-sizes="S:159,R:189,L:219">
                                <i class="fa-solid fa-bag-shopping me-1"></i> Add to Order
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Miso Ramen -->
                <div class="col-md-3">
                    <div class="card h-100 text-center menu-card">
                        <img src="img/menu img/miso2.jpg" class="card-img-top" alt="Miso Ramen">
                        <div class="card-body">
                            <h5 class="card-title">Miso Ramen</h5>
                            <p class="card-text">miso - chashu pork - wakame seaweed - spring onion - egg</p>
                            <p class="fw-bold">S 169 / R 199 / L 229</p>
                            <button class="btn btn-warning add-to-order-btn" data-name="Miso Ramen"
                                data-sizes="S:169,R:199,L:229">
                                <i class="fa-solid fa-bag-shopping me-1"></i> Add to Order
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tantanmen Ramen -->
                <div class="col-md-3">
                    <div class="card h-100 text-center menu-card">
                        <img src="img/menu img/tantanmen2.jpg" class="card-img-top" alt="Tantanmen Ramen">
                        <div class="card-body">
                            <h5 class="card-title">Tantanmen Ramen</h5>
                            <p class="card-text">spicy - chashu pork - beansprouts - seaweed strips- sesame seeds - egg
                            </p>
                            <p class="fw-bold">S 169 / R 199 / L 229</p>
                            <button class="btn btn-warning add-to-order-btn" data-name="Tantanmen Ramen"
                                data-sizes="S:169,R:199,L:229">
                                <i class="fa-solid fa-bag-shopping me-1"></i> Add to Order
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Chicken Butter -->
                <div class="col-md-3">
                    <div class="card h-100 text-center menu-card">
                        <img src="img/menu img/chicken butter.jpg" class="card-img-top" alt="Chicken Butter">
                        <div class="card-body">
                            <h5 class="card-title">Chicken Butter</h5>
                            <p class="card-text">chicken fillet - butter - seaweed strips - spring onion - egg</p>
                            <p class="fw-bold">S 189 / L 279</p>
                            <button class="btn btn-warning add-to-order-btn" data-name="Chicken Butter"
                                data-sizes="S:189,L:279">
                                <i class="fa-solid fa-bag-shopping me-1"></i> Add to Order
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Black Garlic Ramen -->
                <div class="col-md-3">
                    <div class="card h-100 text-center menu-card">
                        <img src="img/menu img/black garlic2.jpg" class="card-img-top" alt="Black Garlic Ramen">
                        <div class="card-body">
                            <h5 class="card-title">Black Garlic Ramen</h5>
                            <p class="card-text">black garlic - chashu pork - wakame - seaweed - kikurage - egg</p>
                            <p class="fw-bold">S 169 / R 199 / L 229</p>
                            <button class="btn btn-warning add-to-order-btn" data-name="Black Garlic Ramen"
                                data-sizes="S:169,R:199,L:229">
                                <i class="fa-solid fa-bag-shopping me-1"></i> Add to Order
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Red Ramen -->
                <div class="col-md-3">
                    <div class="card h-100 text-center menu-card">
                        <img src="img/menu img/red ramen.jpg" class="card-img-top" alt="Red Ramen">
                        <div class="card-body">
                            <h5 class="card-title">Red Ramen</h5>
                            <p class="card-text">spicy meatball - kikurage - spring onion - seaweed strips - chashu pork
                            </p>
                            <p class="fw-bold">R 289 / L 319</p>
                            <button class="btn btn-warning add-to-order-btn" data-name="Red Ramen"
                                data-sizes="R:289,L:319">
                                <i class="fa-solid fa-bag-shopping me-1"></i> Add to Order
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- MENU ITEMS / SIDES SECTION -->
    <section class="apps-section py-5">
        <div class="container">
            <div class="row g-4">
                <h1>Sides</h1>

                <!-- Gyoza -->
                <div class="col-md-3">
                    <div class="card h-100 text-center menu-card">
                        <img src="img/menu img/gyoza.jpg" class="card-img-top" alt="Gyoza">
                        <div class="card-body">
                            <h5 class="card-title">Gyoza</h5>
                            <p class="card-text">Pan-fried dumplings</p>
                            <p class="fw-bold">4pcs ₱89 / 6pcs ₱129</p>
                            <button class="btn btn-warning add-to-order-btn" data-name="Gyoza"
                                data-sizes="4pcs:89,6pcs:129">
                                <i class="fa-solid fa-bag-shopping me-1"></i> Add to Order
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Chicken Karaage -->
                <div class="col-md-3">
                    <div class="card h-100 text-center menu-card">
                        <img src="img/menu img/kaarage.jpg" class="card-img-top" alt="Chicken Karaage">
                        <div class="card-body">
                            <h5 class="card-title">Chicken Karaage</h5>
                            <p class="card-text">Crispy Japanese fried chicken</p>
                            <p class="fw-bold">₱149</p>
                            <button class="btn btn-warning add-to-order-btn" data-name="Chicken Karaage"
                                data-sizes="Single:149">
                                <i class="fa-solid fa-bag-shopping me-1"></i> Add to Order
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Teriyaki Tofu -->
                <div class="col-md-3">
                    <div class="card h-100 text-center menu-card">
                        <img src="img/menu img/teriyaki tofu.jpg" class="card-img-top" alt="Teriyaki Tofu">
                        <div class="card-body">
                            <h5 class="card-title">Teriyaki Tofu</h5>
                            <p class="card-text">Grilled tofu with teriyaki sauce</p>
                            <p class="fw-bold">₱89</p>
                            <button class="btn btn-warning add-to-order-btn" data-name="Teriyaki Tofu"
                                data-sizes="Single:89">
                                <i class="fa-solid fa-bag-shopping me-1"></i> Add to Order
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Garlic Butter Tofu -->
                <div class="col-md-3">
                    <div class="card h-100 text-center menu-card">
                        <img src="img/menu img/garlic butter tofu.jpg" class="card-img-top" alt="Garlic Butter Tofu">
                        <div class="card-body">
                            <h5 class="card-title">Garlic Butter Tofu</h5>
                            <p class="card-text">Grilled tofu with garlic butter sauce</p>
                            <p class="fw-bold">₱89</p>
                            <button class="btn btn-warning add-to-order-btn" data-name="Garlic Butter Tofu"
                                data-sizes="Single:89">
                                <i class="fa-solid fa-bag-shopping me-1"></i> Add to Order
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

        <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script src="menu-cart.js"></script>
    <script src="map.js"></script>
</body>
</html>
