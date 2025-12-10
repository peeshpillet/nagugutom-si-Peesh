<?php
// order-confirmed.php - Ramen Naijiro
// Landing page after PayMongo success. Inserts order once into DB,
// then shows a summary + tracking number.

session_start();
require_once "config.php"; // must define $mysqli

// Simple helper: format tracking ID from auto-increment order_id
function formatTrackingId($id) {
    return 'RN-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
}

// ---------------------- Read order data from session ----------------------
$isPaymongoReturn = (
    isset($_GET['via']) &&
    $_GET['via'] === 'paymongo'
);

// We'll store all display data in this array
$order      = null;
$error_msg  = "";

// First time coming from PayMongo: we should have pending_order
if ($isPaymongoReturn) {

    // If we already saved the order before (refresh), reuse last_order only
    if (isset($_SESSION['last_order']) && !isset($_SESSION['pending_order'])) {
        $order = $_SESSION['last_order'];
    } elseif (!isset($_SESSION['pending_order'])) {
        $error_msg = "We couldn't find any pending order data. It may have already been processed.";
    } else {
        // We have a pending_order from paymongo-checkout.php
        $data = $_SESSION['pending_order'];

        $customer_name     = trim($data['customer_name']     ?? '');
        $customer_phone    = trim($data['customer_phone']    ?? '');
        $customer_email    = trim($data['customer_email']    ?? '');
        $delivery_address  = trim($data['delivery_address']  ?? '');
        $delivery_landmark = trim($data['delivery_landmark'] ?? '');
        $order_notes       = trim($data['order_notes']       ?? '');
        $subtotal          = (float)($data['subtotal']       ?? 0);
        $delivery_fee      = (float)($data['delivery_fee']   ?? 0);
        $total_amount      = (float)($data['total_amount']   ?? 0);
        $fulfillment_mode  = trim($data['fulfillment_mode']  ?? 'Delivery'); // raw from snapshot

        // cart_json here is rn_current_order snapshot from checkout
        $cart_json = $data['cart_json'] ?? '[]';

        $snapshot = json_decode($cart_json, true);
        if (!is_array($snapshot)) {
            $snapshot = [];
        }

        $items = $snapshot['items']    ?? [];
        $loc   = $snapshot['location'] ?? [];

        // Location / branch fields from snapshot (see checkout.js)
        $branch_id   = $loc['branchId']   ?? null;
        $branch_name = $loc['branchName'] ?? 'Unknown Branch';
        $province    = $loc['province']   ?? null;
        $city        = $loc['city']       ?? null;
        $barangay    = $loc['barangay']   ?? null;

        // Normalize order_type to match enum('Delivery','Pickup')
        $order_type = ucfirst(strtolower($fulfillment_mode));
        if ($order_type !== 'Delivery' && $order_type !== 'Pickup') {
            $order_type = 'Delivery';
        }

        // This page is only hit after PayMongo success, so lock payment_method
        $payment_method = 'paymongo';

        // ---------------------- Insert into DB (orders table) ----------------------
        if ($customer_name === "" || $customer_phone === "") {
            $error_msg = "Missing required fields (name or phone).";
        } else {
            // We'll set order_code after we get order_id
            $order_code = "";

            $stmt = $mysqli->prepare("
                INSERT INTO orders (
                    order_code,
                    customer_name,
                    customer_phone,
                    customer_email,
                    branch_id,
                    branch_name,
                    province,
                    city,
                    barangay,
                    address,
                    landmark,
                    order_type,
                    payment_method,
                    subtotal,
                    delivery_fee,
                    total_amount,
                    cart_json
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");

            if (!$stmt) {
                $error_msg = "Database error (prepare): " . $mysqli->error;
            } else {
                $stmt->bind_param(
                    "sssssssssssssddds",
                    $order_code,
                    $customer_name,
                    $customer_phone,
                    $customer_email,
                    $branch_id,
                    $branch_name,
                    $province,
                    $city,
                    $barangay,
                    $delivery_address,
                    $delivery_landmark,
                    $order_type,
                    $payment_method,
                    $subtotal,
                    $delivery_fee,
                    $total_amount,
                    $cart_json
                );

                if ($stmt->execute()) {
                    $order_id = $stmt->insert_id;
                    $stmt->close();

                    // Generate tracking code based on order_id and save to DB
                    $tracking_code = formatTrackingId($order_id);

                    $upd = $mysqli->prepare("
                        UPDATE orders
                        SET order_code = ?
                        WHERE order_id = ?
                    ");
                    if ($upd) {
                        $upd->bind_param("si", $tracking_code, $order_id);
                        $upd->execute();
                        $upd->close();
                    } else {
                        // Not fatal for the user view, but loggable
                        // $error_msg .= " (Warning: could not save order_code.)";
                    }

                    // Build order array for display
                    $order = [
                        'order_id'        => $order_id,
                        'tracking_code'   => $tracking_code,
                        'name'            => $customer_name,
                        'phone'           => $customer_phone,
                        'email'           => $customer_email,
                        'branch'          => $branch_name,
                        'address'         => $delivery_address,
                        'landmark'        => $delivery_landmark,
                        'notes'           => $order_notes,
                        'subtotal'        => $subtotal,
                        'delivery_fee'    => $delivery_fee,
                        'total_amount'    => $total_amount,
                        'fulfillment_mode'=> $order_type,
                        'items'           => $items,
                    ];

                    // Build track link + QR code using public track-order page
                    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                    $order['track_url']    = $scheme . '://' . $host . $basePath . '/track-order.php?code=' . urlencode($tracking_code);
                    $order['track_qr_url'] = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($order['track_url']);

                    // Save for refresh-safe display, clear pending_order
                    $_SESSION['last_order']   = $order;
                    unset($_SESSION['pending_order']);

                } else {
                    $error_msg = "Database error (execute): " . $stmt->error;
                    $stmt->close();
                }
            }
        }
    }
} else {
    $error_msg = "This page is only available after completing an online payment.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmed | Ramen Naijiro</title>
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
        <a class="navbar-brand" href="index.php">
            <img src="img/logo.jpg" alt="Ramen Naijiro Logo" class="logo-circle">
            Ramen Naijiro
        </a>
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="about-us.php">About Us</a></li>
            <li class="nav-item"><a class="nav-link" href="menu.php">Menu</a></li>
            <li class="nav-item"><a class="nav-link" href="track-order.php">Track Order</a></li>
        </ul>
    </div>
</nav>

<main class="flex-grow-1">
    <section class="py-5 section-info">
        <div class="container">

            <?php if ($order): ?>
                <!-- ================= SUCCESS STATE ================= -->
                <div class="row justify-content-center mb-4">
                    <div class="col-lg-9">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4 p-md-5">

                                <!-- Top confirmation strip -->
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
                                    <div class="d-flex align-items-center mb-3 mb-md-0">
                                        <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center me-3"
                                             style="width: 52px; height: 52px;">
                                            <i class="fa-solid fa-bowl-food fa-lg"></i>
                                        </div>
                                        <div>
                                            <h1 class="h4 mb-1">Order confirmed!</h1>
                                            <p class="small text-muted mb-0">
                                                Thanks, <?php echo htmlspecialchars($order['name']); ?>. Your ramen is now in the queue.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Tracking number block -->
                                    <div class="text-md-end">
                                        <p class="small text-muted mb-1">Tracking number</p>
                                        <div class="d-inline-flex align-items-center px-3 py-2 rounded-3 bg-dark text-warning">
                                            <i class="fa-solid fa-hashtag me-2"></i>
                                            <span class="font-monospace fw-semibold">
                                                <?php echo htmlspecialchars($order['tracking_code']); ?>
                                            </span>
                                        </div>
                                        <p class="small text-muted mt-2 mb-0">
                                            Use this on <a href="<?php echo htmlspecialchars($order['track_url']); ?>">Track Order</a> to check status,
                                            or scan the QR code below.
                                        </p>
                                        <div class="mt-2">
                                            <p class="small text-muted mb-1">Scan to track on another device:</p>
                                            <img src="<?php echo htmlspecialchars($order['track_qr_url']); ?>"
                                                 alt="QR code to track this order"
                                                 class="img-fluid border rounded"
                                                 style="max-width: 150px;">
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- Customer + delivery row -->
                                <div class="row g-4 mb-3 small">
                                    <div class="col-md-6">
                                        <h6 class="text-uppercase text-muted mb-2">
                                            <i class="fa-solid fa-user me-1"></i> Customer
                                        </h6>
                                        <p class="mb-1">
                                            <strong><?php echo htmlspecialchars($order['name']); ?></strong>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fa-solid fa-phone me-1"></i>
                                            <?php echo htmlspecialchars($order['phone']); ?>
                                        </p>
                                        <?php if (!empty($order['email'])): ?>
                                            <p class="mb-0">
                                                <i class="fa-solid fa-envelope me-1"></i>
                                                <?php echo htmlspecialchars($order['email']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-6">
                                        <h6 class="text-uppercase text-muted mb-2">
                                            <i class="fa-solid fa-location-dot me-1"></i> Delivery details
                                        </h6>
                                        <p class="mb-1">
                                            <strong>Branch:</strong>
                                            <?php echo htmlspecialchars($order['branch']); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Address:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($order['address'] ?: 'Not specified')); ?>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Landmark:</strong>
                                            <?php echo htmlspecialchars($order['landmark'] ?: 'Not specified'); ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Divider + totals row -->
                                <hr>

                                <div class="row align-items-start small g-4">
                                    <div class="col-md-7">
                                        <h6 class="text-uppercase text-muted mb-2">
                                            <i class="fa-solid fa-list-ul me-1"></i> Items in this order
                                        </h6>
                                        <div class="table-responsive mb-2">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Item</th>
                                                        <th class="text-nowrap">Details</th>
                                                        <th class="text-end">Qty</th>
                                                        <th class="text-end">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php if (!empty($order['items'])): ?>
                                                    <?php foreach ($order['items'] as $item): ?>
                                                        <?php
                                                            $name   = htmlspecialchars($item['name'] ?? 'Item');
                                                            $size   = $item['size'] ?? '';
                                                            $extras = $item['extras'] ?? '';
                                                            $qty    = (int)($item['qty'] ?? 1);
                                                            $line   = (float)($item['line_total'] ?? 0);
                                                            $detailsParts = [];
                                                            if ($size !== '')   $detailsParts[] = 'Size: ' . $size;
                                                            if ($extras !== '') $detailsParts[] = 'Extras: ' . $extras;
                                                            $details = implode(' • ', $detailsParts);
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $name; ?></td>
                                                            <td class="text-muted"><?php echo htmlspecialchars($details); ?></td>
                                                            <td class="text-end"><?php echo $qty; ?></td>
                                                            <td class="text-end">₱<?php echo number_format($line, 2); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">
                                                            No items found for this order.
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <h6 class="text-uppercase text-muted mb-2">
                                            <i class="fa-solid fa-receipt me-1"></i> Payment summary
                                        </h6>
                                        <div class="border rounded-3 p-3 bg-light">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Subtotal</span>
                                                <span>₱<?php echo number_format($order['subtotal'], 2); ?></span>
                                            </div>
                                            <?php if ($order['delivery_fee'] > 0): ?>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Delivery fee</span>
                                                    <span>₱<?php echo number_format($order['delivery_fee'], 2); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between fw-bold">
                                                <span>Total</span>
                                                <span class="text-success">
                                                    ₱<?php echo number_format($order['total_amount'], 2); ?>
                                                </span>
                                            </div>
                                            <p class="small text-muted mt-2 mb-0">
                                                Payment method: <strong>PayMongo</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($order['notes'])): ?>
                                    <hr>
                                    <h6 class="text-uppercase text-muted small mb-1">
                                        <i class="fa-solid fa-comment-dots me-1"></i> Notes for the shop
                                    </h6>
                                    <p class="small mb-0">
                                        <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Bottom buttons -->
                                <div class="mt-4 d-flex flex-wrap gap-2 justify-content-between justify-content-md-end">
                                    <a href="<?php echo htmlspecialchars($order['track_url']); ?>" class="btn btn-outline-dark btn-sm">
                                        <i class="fa-solid fa-truck-fast me-1"></i>
                                        Track this order
                                    </a>
                                    <a href="menu.php" class="btn btn-warning btn-sm text-dark fw-semibold">
                                        <i class="fa-solid fa-utensils me-1"></i>
                                        Back to Menu
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- ================= ERROR STATE ================= -->
                <div class="row justify-content-center">
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4 p-md-5">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center me-3"
                                         style="width: 48px; height: 48px;">
                                        <i class="fa-solid fa-circle-exclamation fa-lg"></i>
                                    </div>
                                    <div>
                                        <h1 class="h5 mb-1">We couldn’t confirm your order</h1>
                                        <p class="small text-muted mb-0">
                                            <?php echo htmlspecialchars($error_msg ?: 'No additional error info.'); ?>
                                        </p>
                                    </div>
                                </div>

                                <p class="small text-muted mb-3">
                                    This page only appears right after a successful online payment.
                                    If you opened it directly or refreshed after closing your browser,
                                    please return to the menu and place your order again.
                                </p>

                                <div class="d-flex flex-wrap gap-2">
                                    <a href="menu.php" class="btn btn-warning btn-sm text-dark fw-semibold">
                                        <i class="fa-solid fa-utensils me-1"></i>
                                        Back to Menu
                                    </a>
                                    <a href="contact-us.php" class="btn btn-outline-dark btn-sm">
                                        <i class="fa-solid fa-headset me-1"></i>
                                        Contact Us
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </section>
</main>

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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($order): ?>
<script>
// After a successful order, clear the cart snapshot
try {
    localStorage.removeItem('rn_current_order');
} catch (e) {
    console.error('Unable to clear localStorage', e);
}
</script>
<?php endif; ?>

</body>
</html>
