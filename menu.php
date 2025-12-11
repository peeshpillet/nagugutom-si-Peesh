<?php
// menu.php - Client menu (driven by DB availability)

session_start();
require_once "config.php";

// ---------------------- Fetch menu (same structure as admin-dash) ----------------------
$menu = [];

$stmt = $mysqli->prepare("
    SELECT
        c.category_id,
        c.name  AS category_name,
        c.slug  AS category_slug,
        i.item_id,
        i.name  AS item_name,
        i.description,
        i.image_path,
        i.is_available AS item_available,
        s.size_id,
        s.size_code,
        s.size_label,
        s.price,
        s.is_available AS size_available
    FROM menu_categories c
    JOIN menu_items i
        ON i.category_id = c.category_id
    LEFT JOIN menu_item_sizes s
        ON s.item_id = i.item_id
    ORDER BY c.category_id, i.item_id, s.price
");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $slug = $row['category_slug'];

        if (!isset($menu[$slug])) {
            $menu[$slug] = [
                'name'  => $row['category_name'],
                'items' => []
            ];
        }

        $item_id = (int)$row['item_id'];
        if (!isset($menu[$slug]['items'][$item_id])) {
            $menu[$slug]['items'][$item_id] = [
                'item_id'     => $item_id,
                'name'        => $row['item_name'],
                'description' => $row['description'],
                'image_path'  => $row['image_path'],
                'available'   => (int)$row['item_available'],
                'sizes'       => []
            ];
        }

        if (!empty($row['size_id'])) {
            $menu[$slug]['items'][$item_id]['sizes'][] = [
                'size_id'   => (int)$row['size_id'],
                'code'      => $row['size_code'],
                'label'     => $row['size_label'],
                'price'     => (float)$row['price'],
                'available' => (int)$row['size_available']
            ];
        }
    }

    $stmt->close();
}

// ---------------------- Build extras string for cart dropdown ----------------------
// Only extras with item_available=1 AND size_available=1 go into data-extras
$extras_attr = '';
if (isset($menu['extras'])) {
    $pairs = [];

    foreach ($menu['extras']['items'] as $extraItem) {
        if (!(int)$extraItem['available']) {
            continue; // item hidden in admin → not selectable by client
        }

        if (!empty($extraItem['sizes'])) {
            foreach ($extraItem['sizes'] as $size) {
                if (!(int)$size['available']) {
                    continue;
                }
                // Use extra name as label (Egg, Noodles, etc.)
                $label = $extraItem['name'];
                $price = (int)$size['price'];
                $pairs[] = $label . ':' . $price;
                // Only first active size per extra
                break;
            }
        }
    }

    $extras_attr = implode(',', $pairs);
}
?>
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
            <button id="close-cart" class="btn btn-sm side-cart-btn">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="cart-content flex-grow-1 overflow-auto px-3">
            <ul id="cart-items" class="list-group list-group-flush mb-3"></ul>

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
        </div>

        <div class="p-3 border-top">
            <p class="fw-bold">Total: ₱<span id="cart-total">0</span></p>
            <button class="btn side-cart-btn w-100" id="checkout-btn">Checkout</button>
        </div>
    </div>

    <!-- AREA COVERAGE CHECK -->
    <section class="branch-form-section py-5">
        <div class="container">
            <div class="card border-0 shadow-sm p-4">
                <h2 class="text-center mb-3 text-uppercase fw-bold text-black">
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

    <!-- MENU ITEMS / RAMEN SECTION (DB driven) -->
    <section class="ramen-section py-5">
        <div class="container">
            <div class="row g-4">
                <h1>Ramen</h1>

                <?php if (!empty($menu['ramen']['items'])): ?>
                    <?php foreach ($menu['ramen']['items'] as $item): ?>
                        <?php
                        $isAvailable = (int)$item['available'] === 1;

                        // Build size display "S 169 / R 199 / L 229" and data-sizes "S:169,R:199,L:229"
                        $sizeDisplayParts = [];
                        $sizeAttrParts    = [];

                        if (!empty($item['sizes'])) {
                            foreach ($item['sizes'] as $size) {
                                if ((int)$size['available'] !== 1) {
                                    continue; // skip hidden sizes
                                }
                                $code  = $size['code'] ?: $size['label'];
                                $price = (int)$size['price'];

                                $sizeDisplayParts[] = $code . ' ' . $price;
                                $sizeAttrParts[]    = $code . ':' . $price;
                            }
                        }

                        $sizes_display = $sizeDisplayParts ? implode(' / ', $sizeDisplayParts) : 'No prices set';
                        $sizes_attr    = implode(',', $sizeAttrParts);

                        // Fix image path "../img/..." -> "img/..."
                        $imgPath = $item['image_path'] ?? '';
                        if ($imgPath !== '' && strncmp($imgPath, '../', 3) === 0) {
                            $imgPath = substr($imgPath, 3);
                        }
                        ?>
                        <div class="col-md-3">
                            <div class="card h-100 text-center menu-card <?php echo $isAvailable ? '' : 'opacity-50'; ?>">
                                <?php if ($imgPath !== ''): ?>
                                    <img src="<?php echo htmlspecialchars($imgPath); ?>"
                                         class="card-img-top"
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php endif; ?>

                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars($item['description']); ?>
                                    </p>
                                    <p class="fw-bold"><?php echo htmlspecialchars($sizes_display); ?></p>

                                    <?php if ($isAvailable && $sizes_attr !== ''): ?>
                                        <button
                                            class="btn btn-warning add-to-order-btn"
                                            data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                            data-sizes="<?php echo htmlspecialchars($sizes_attr); ?>"
                                            data-extras="<?php echo htmlspecialchars($extras_attr); ?>"
                                        >
                                            <i class="fa-solid fa-bag-shopping me-1"></i> Add to Order
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100" disabled>
                                            Unavailable
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No ramen items configured.</p>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <!-- MENU ITEMS / SIDES SECTION (DB driven) -->
    <section class="apps-section py-5">
        <div class="container">
            <div class="row g-4">
                <h1>Sides</h1>

                <?php if (!empty($menu['sides']['items'])): ?>
                    <?php foreach ($menu['sides']['items'] as $item): ?>
                        <?php
                        $isAvailable = (int)$item['available'] === 1;

                        $sizeDisplayParts = [];
                        $sizeAttrParts    = [];

                        if (!empty($item['sizes'])) {
                            foreach ($item['sizes'] as $size) {
                                if ((int)$size['available'] !== 1) {
                                    continue;
                                }
                                $code  = $size['code'] ?: $size['label'];
                                $price = (int)$size['price'];

                                $sizeDisplayParts[] = $code . ' ' . $price;
                                $sizeAttrParts[]    = $code . ':' . $price;
                            }
                        }

                        $sizes_display = $sizeDisplayParts ? implode(' / ', $sizeDisplayParts) : 'No prices set';
                        $sizes_attr    = implode(',', $sizeAttrParts);

                        $imgPath = $item['image_path'] ?? '';
                        if ($imgPath !== '' && strncmp($imgPath, '../', 3) === 0) {
                            $imgPath = substr($imgPath, 3);
                        }
                        ?>
                        <div class="col-md-3">
                            <div class="card h-100 text-center menu-card <?php echo $isAvailable ? '' : 'opacity-50'; ?>">
                                <?php if ($imgPath !== ''): ?>
                                    <img src="<?php echo htmlspecialchars($imgPath); ?>"
                                         class="card-img-top"
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php endif; ?>

                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars($item['description']); ?>
                                    </p>
                                    <p class="fw-bold"><?php echo htmlspecialchars($sizes_display); ?></p>

                                    <?php if ($isAvailable && $sizes_attr !== ''): ?>
                                        <button
                                            class="btn btn-warning add-to-order-btn"
                                            data-name="<?php echo htmlspecialchars($item['name']); ?>"
                                            data-sizes="<?php echo htmlspecialchars($sizes_attr); ?>"
                                        >
                                            <i class="fa-solid fa-bag-shopping me-1"></i> Add to Order
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100" disabled>
                                            Unavailable
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No side dishes configured.</p>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script src="menu-cart.js"></script>
    <script src="map.js"></script>
</body>
</html>
