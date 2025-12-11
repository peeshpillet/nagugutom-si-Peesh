<?php
// track-order.php - Public order tracking

session_start();
require_once "config.php";

// Helper: fetch order by code
function get_order_by_code(mysqli $mysqli, string $code): ?array
{
    $stmt = $mysqli->prepare("
        SELECT *
        FROM orders
        WHERE order_code = ?
        LIMIT 1
    ");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    return $row ?: null;
}

// Helper: map status -> step index (1..4)
function status_to_step(string $status): int
{
    switch ($status) {
        case 'pending':
            return 1;
        case 'preparing':
            return 2;
        case 'out_for_delivery':
            return 3;
        case 'completed':
        case 'cancelled': // treat as "final"
            return 4;
        default:
            return 1;
    }
}

// Helper: decode cart_json into items array
function parse_cart_items(?string $cart_json): array
{
    if (!$cart_json) return [];

    $snap = json_decode($cart_json, true);
    if (!is_array($snap) || empty($snap['items']) || !is_array($snap['items'])) {
        return [];
    }

    $items = [];
    foreach ($snap['items'] as $it) {
        $name       = $it['name'] ?? 'Item';
        $size       = $it['size'] ?? '';
        $extras     = $it['extras'] ?? '';
        $qty        = (int)($it['qty'] ?? 1);
        $line_total = isset($it['line_total']) ? (float)$it['line_total'] : 0.0;

        $items[] = [
            'name'       => $name,
            'size'       => $size,
            'extras'     => $extras,
            'qty'        => $qty,
            'line_total' => $line_total,
        ];
    }

    return $items;
}

// ---------- AJAX endpoint (no extra file needed) ----------
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json; charset=utf-8');

    $code = trim($_GET['code'] ?? '');
    if ($code === '') {
        echo json_encode([
            'success' => false,
            'error'   => 'Please enter a tracking ID.',
        ]);
        exit;
    }

    $order = get_order_by_code($mysqli, $code);
    if (!$order) {
        echo json_encode([
            'success' => false,
            'error'   => 'No order found with that tracking ID.',
        ]);
        exit;
    }

    $items = parse_cart_items($order['cart_json'] ?? null);
    $step  = status_to_step($order['status']);

    // Build clean payload
    $payload = [
        'order_id'       => (int)$order['order_id'],
        'order_code'     => $order['order_code'],
        'customer_name'  => $order['customer_name'],
        'customer_phone' => $order['customer_phone'],
        'customer_email' => $order['customer_email'],
        'branch_name'    => $order['branch_name'],
        'province'       => $order['province'],
        'city'           => $order['city'],
        'barangay'       => $order['barangay'],
        'address'        => $order['address'],
        'landmark'       => $order['landmark'],
        'order_type'     => $order['order_type'],
        'payment_method' => $order['payment_method'],
        'subtotal'       => (float)$order['subtotal'],
        'delivery_fee'   => (float)$order['delivery_fee'],
        'total_amount'   => (float)$order['total_amount'],
        'status'         => $order['status'],
        'created_at'     => $order['created_at'],
        'updated_at'     => $order['updated_at'],
    ];

    echo json_encode([
        'success' => true,
        'order'   => $payload,
        'items'   => $items,
        'step'    => $step,
    ]);
    exit;
}

// ---------- Normal page load ----------
$initial_code  = trim($_GET['code'] ?? '');
$initial_order = null;
$initial_items = [];
$initial_step  = 0;

if ($initial_code !== '') {
    $initial_order = get_order_by_code($mysqli, $initial_code);
    if ($initial_order) {
        $initial_items = parse_cart_items($initial_order['cart_json'] ?? null);
        $initial_step  = status_to_step($initial_order['status']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ramen Naijiro | Order Tracking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@300;600;900&display=swap" rel="stylesheet">

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
                <li class="nav-item"><a class="nav-link" href="menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link active" href="track-order.php">Track Order</a></li>
                <li class="nav-item ms-3">
                    <a href="menu.php" class="btn btn-warning">
                        <i class="fa-solid fa-bag-shopping me-1"></i>
                        Order Now
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- HEADER -->
    <section
        class="tracking-header position-relative text-center text-white d-flex align-items-center justify-content-center">
        <div class="overlay"></div>
        <div class="content">
            <h1 class="display-4">Track Your Order</h1>
            <p class="lead">Fresh ramen is on its way — check your delivery status below.</p>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <section class="py-5 track-order-page">
        <div class="container">

            <!-- Unified Tracking Card -->
            <div class="card tracking-card shadow-lg p-4 mx-auto" style="max-width: 900px; border-radius: 20px;">

                <!-- Search Bar -->
                <div class="mb-4">
                    <h4 class="fw-bold mb-3 text-center">Enter Your Tracking ID</h4>
                    <div class="d-flex flex-column flex-md-row gap-3">
                        <input
                            type="text"
                            id="trackingCodeInput"
                            class="form-control tracking-input"
                            placeholder="e.g., RN-000001"
                            value="<?php echo htmlspecialchars($initial_code); ?>"
                        >
                        <button class="btn btn-warning fw-bold tracking-btn" id="trackingButton">
                            <i class="fa-solid fa-search"></i> Track
                        </button>
                    </div>
                    <div id="trackMessage" class="mt-2 small text-center text-danger"></div>
                </div>

                <hr class="my-4">

                <!-- Order Status Progress Bar -->
                <h4 class="fw-bold mb-3 text-center">Order Status</h4>
                <div class="modern-progress d-flex justify-content-between align-items-center position-relative mb-4">
                    <div class="step text-center <?php echo ($initial_step >= 1 ? 'active' : ''); ?>" data-step="1" id="step1">
                        <div class="icon mb-2"><i class="fa-solid fa-receipt fa-lg"></i></div>
                        <p class="mb-0 small">Order Placed</p>
                    </div>
                    <div class="line flex-grow-1 mx-2 <?php echo ($initial_step >= 2 ? 'active' : ''); ?>" id="line12"></div>

                    <div class="step text-center <?php echo ($initial_step >= 2 ? 'active' : ''); ?>" data-step="2" id="step2">
                        <div class="icon mb-2"><i class="fa-solid fa-bowl-food fa-lg"></i></div>
                        <p class="mb-0 small">Preparing</p>
                    </div>
                    <div class="line flex-grow-1 mx-2 <?php echo ($initial_step >= 3 ? 'active' : ''); ?>" id="line23"></div>

                    <div class="step text-center <?php echo ($initial_step >= 3 ? 'active' : ''); ?>" data-step="3" id="step3">
                        <div class="icon mb-2"><i class="fa-solid fa-motorcycle fa-lg"></i></div>
                        <p class="mb-0 small">Out for Delivery</p>
                    </div>
                    <div class="line flex-grow-1 mx-2 <?php echo ($initial_step >= 4 ? 'active' : ''); ?>" id="line34"></div>

                    <div class="step text-center <?php echo ($initial_step >= 4 ? 'active' : ''); ?>" data-step="4" id="step4">
                        <div class="icon mb-2"><i class="fa-solid fa-house fa-lg"></i></div>
                        <p class="mb-0 small">Delivered</p>
                    </div>
                </div>

                <p class="text-center mb-4">
                    <span class="badge bg-dark" id="orderStatusBadge">
                        <?php echo $initial_order ? htmlspecialchars($initial_order['status']) : 'No order loaded'; ?>
                    </span>
                </p>

                <hr class="my-4">

                <!-- Order Details -->
                <h4 class="fw-bold mb-3 text-center">Order Details</h4>
                <div class="detail-box p-3 border rounded">
                    <p class="mb-2">
                        <i class="fa-solid fa-hashtag text-warning me-2"></i>
                        <strong>Order Code:</strong>
                        <span id="orderCodeDisplay">
                            <?php echo $initial_order ? htmlspecialchars($initial_order['order_code']) : '—'; ?>
                        </span>
                    </p>
                    <p class="mb-2">
                        <i class="fa-regular fa-calendar text-warning me-2"></i>
                        <strong>Date &amp; Time:</strong>
                        <span id="orderDateTime">
                            <?php
                            if ($initial_order) {
                                echo htmlspecialchars(date('Y-m-d H:i', strtotime($initial_order['created_at'])));
                            } else {
                                echo '—';
                            }
                            ?>
                        </span>
                    </p>
                    <p class="mb-2">
                        <i class="fa-solid fa-user text-warning me-2"></i>
                        <strong>Customer:</strong>
                        <span id="orderCustomerName">
                            <?php echo $initial_order ? htmlspecialchars($initial_order['customer_name']) : '—'; ?>
                        </span>
                    </p>
                    <p class="mb-2">
                        <i class="fa-solid fa-phone text-warning me-2"></i>
                        <strong>Phone:</strong>
                        <span id="orderCustomerPhone">
                            <?php echo $initial_order ? htmlspecialchars($initial_order['customer_phone']) : '—'; ?>
                        </span>
                    </p>
                    <p class="mb-2">
                        <i class="fa-solid fa-store text-warning me-2"></i>
                        <strong>Branch:</strong>
                        <span id="orderBranch">
                            <?php echo $initial_order ? htmlspecialchars($initial_order['branch_name']) : '—'; ?>
                        </span>
                    </p>
                    <p class="mb-2">
                        <i class="fa-solid fa-bag-shopping text-warning me-2"></i>
                        <strong>Order Type:</strong>
                        <span id="orderType">
                            <?php echo $initial_order ? htmlspecialchars($initial_order['order_type']) : '—'; ?>
                        </span>
                    </p>
                    <p class="mb-2">
                        <i class="fa-solid fa-wallet text-warning me-2"></i>
                        <strong>Payment:</strong>
                        <span id="orderPayment">
                            <?php echo $initial_order ? htmlspecialchars($initial_order['payment_method']) : '—'; ?>
                        </span>
                    </p>
                    <p class="mb-2">
                        <i class="fa-solid fa-location-dot text-warning me-2"></i>
                        <strong>Address:</strong>
                        <span id="orderAddress">
                            <?php
                            if ($initial_order) {
                                $parts = array_filter([
                                    $initial_order['address'] ?? '',
                                    $initial_order['barangay'] ?? '',
                                    $initial_order['city'] ?? '',
                                    $initial_order['province'] ?? '',
                                ]);
                                echo htmlspecialchars(implode(', ', $parts));
                            } else {
                                echo '—';
                            }
                            ?>
                        </span>
                    </p>
                    <p class="mb-2">
                        <i class="fa-solid fa-map-pin text-warning me-2"></i>
                        <strong>Landmark:</strong>
                        <span id="orderLandmark">
                            <?php echo ($initial_order && $initial_order['landmark']) ? htmlspecialchars($initial_order['landmark']) : '—'; ?>
                        </span>
                    </p>

                    <hr class="my-3">

                    <p class="mb-2">
                        <i class="fa-solid fa-list text-warning me-2"></i>
                        <strong>Receipt:</strong>
                    </p>
                    <ul class="receipt mb-2 ps-3" id="orderItemsList">
                        <?php if ($initial_items): ?>
                            <?php foreach ($initial_items as $it): ?>
                                <li>
                                    <?php echo (int)$it['qty']; ?>x
                                    <?php echo htmlspecialchars($it['name']); ?>
                                    <?php if (!empty($it['size'])): ?>
                                        (<?php echo htmlspecialchars($it['size']); ?>)
                                    <?php endif; ?>
                                    <?php if (!empty($it['extras'])): ?>
                                        - <?php echo htmlspecialchars($it['extras']); ?>
                                    <?php endif; ?>
                                    — ₱<?php echo number_format($it['line_total'], 2); ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-muted">No items to display yet.</li>
                        <?php endif; ?>
                    </ul>

                    <p class="fw-bold mb-0 text-end">
                        <span class="d-block">
                            Subtotal: ₱<span id="orderSubtotal">
                                <?php echo $initial_order ? number_format($initial_order['subtotal'], 2) : '0.00'; ?>
                            </span>
                        </span>
                        <span class="d-block">
                            Delivery Fee: ₱<span id="orderDeliveryFee">
                                <?php echo $initial_order ? number_format($initial_order['delivery_fee'], 2) : '0.00'; ?>
                            </span>
                        </span>
                        <span class="d-block">
                            Total: ₱<span id="orderTotal">
                                <?php echo $initial_order ? number_format($initial_order['total_amount'], 2) : '0.00'; ?>
                            </span>
                        </span>
                    </p>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    (function () {
        const input     = document.getElementById('trackingCodeInput');
        const button    = document.getElementById('trackingButton');
        const messageEl = document.getElementById('trackMessage');

        const statusBadge   = document.getElementById('orderStatusBadge');
        const codeDisplay   = document.getElementById('orderCodeDisplay');
        const dateTimeEl    = document.getElementById('orderDateTime');
        const customerName  = document.getElementById('orderCustomerName');
        const customerPhone = document.getElementById('orderCustomerPhone');
        const branchEl      = document.getElementById('orderBranch');
        const orderTypeEl   = document.getElementById('orderType');
        const paymentEl     = document.getElementById('orderPayment');
        const addressEl     = document.getElementById('orderAddress');
        const landmarkEl    = document.getElementById('orderLandmark');

        const itemsList     = document.getElementById('orderItemsList');
        const subtotalEl    = document.getElementById('orderSubtotal');
        const deliveryFeeEl = document.getElementById('orderDeliveryFee');
        const totalEl       = document.getElementById('orderTotal');

        const steps = [
            document.getElementById('step1'),
            document.getElementById('step2'),
            document.getElementById('step3'),
            document.getElementById('step4'),
        ];
        const lines = [
            document.getElementById('line12'),
            document.getElementById('line23'),
            document.getElementById('line34'),
        ];

        let currentCode = input.value.trim();
        let pollTimer   = null;

        function setMessage(text, type) {
            messageEl.textContent = text || '';
            if (!text) return;
            messageEl.classList.remove('text-danger', 'text-success');
            if (type === 'success') {
                messageEl.classList.add('text-success');
            } else {
                messageEl.classList.add('text-danger');
            }
        }

        function updateProgress(step, status) {
            steps.forEach((el, idx) => {
                if (!el) return;
                if (idx < step) el.classList.add('active');
                else el.classList.remove('active');
            });
            lines.forEach((el, idx) => {
                if (!el) return;
                if (idx + 2 <= step) el.classList.add('active');
                else el.classList.remove('active');
            });

            if (statusBadge) {
                statusBadge.textContent = status || 'Unknown';
            }
        }

        function renderOrder(json) {
            const o = json.order;

            codeDisplay.textContent   = o.order_code || '—';
            dateTimeEl.textContent    = o.created_at ? o.created_at : '—';
            customerName.textContent  = o.customer_name || '—';
            customerPhone.textContent = o.customer_phone || '—';
            branchEl.textContent      = o.branch_name || '—';
            orderTypeEl.textContent   = o.order_type || '—';
            paymentEl.textContent     = o.payment_method || '—';

            const addrParts = [];
            if (o.address)  addrParts.push(o.address);
            if (o.barangay) addrParts.push(o.barangay);
            if (o.city)     addrParts.push(o.city);
            if (o.province) addrParts.push(o.province);
            addressEl.textContent = addrParts.length ? addrParts.join(', ') : '—';

            landmarkEl.textContent = o.landmark || '—';

            subtotalEl.textContent    = o.subtotal.toFixed(2);
            deliveryFeeEl.textContent = o.delivery_fee.toFixed(2);
            totalEl.textContent       = o.total_amount.toFixed(2);

            // Items
            itemsList.innerHTML = '';
            if (json.items && json.items.length) {
                json.items.forEach(it => {
                    const li = document.createElement('li');
                    const parts = [];
                    parts.push((it.qty || 1) + 'x ' + it.name);
                    if (it.size)   parts.push('(' + it.size + ')');
                    if (it.extras) parts.push('- ' + it.extras);
                    parts.push('— ₱' + it.line_total.toFixed(2));
                    li.textContent = parts.join(' ');
                    itemsList.appendChild(li);
                });
            } else {
                const li = document.createElement('li');
                li.className = 'text-muted';
                li.textContent = 'No items to display yet.';
                itemsList.appendChild(li);
            }

            updateProgress(json.step || 1, o.status);
        }

        function fetchOrderOnce(code, showErrors) {
            if (!code) {
                if (showErrors) setMessage('Please enter your tracking ID.', 'error');
                return;
            }
            fetch('track-order.php?ajax=1&code=' + encodeURIComponent(code))
                .then(r => r.json())
                .then(json => {
                    if (!json.success) {
                        if (showErrors) {
                            setMessage(json.error || 'Order not found.', 'error');
                            updateProgress(0, 'No order loaded');
                        }
                        return;
                    }
                    setMessage('Order status updated.', 'success');
                    renderOrder(json);
                })
                .catch(() => {
                    if (showErrors) setMessage('Unable to contact server right now.', 'error');
                });
        }

        function startPolling() {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
            if (!currentCode) return;
            pollTimer = setInterval(() => {
                fetchOrderOnce(currentCode, false);
            }, 5000); // 5 seconds
        }

        button.addEventListener('click', function () {
            currentCode = input.value.trim();
            fetchOrderOnce(currentCode, true);
            startPolling();
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                button.click();
            }
        });

        // If page loaded already with ?code=..., start polling it
        if (currentCode) {
            startPolling();
        }
    })();
    </script>

</body>
</html>
