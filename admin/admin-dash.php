<?php
// admin/admin-dash.php - Admin dashboard (orders + menu)

session_start();

// Require login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit;
}

require_once "../config.php";

// Logged-in admin info
$admin_name      = $_SESSION['admin_name']      ?? 'Admin';
$admin_branch    = $_SESSION['admin_branch']    ?? 'General Trias';
$admin_branch_id = $_SESSION['admin_branch_id'] ?? null;

// Status helpers (labels + badge classes)
$STATUS_LABELS = [
    'pending'          => 'Pending',
    'preparing'        => 'Preparing',
    'out_for_delivery' => 'Out for Delivery',
    'completed'        => 'Completed',
    'cancelled'        => 'Cancelled',
];

$STATUS_BADGE_CLASS = [
    'pending'          => 'bg-secondary',
    'preparing'        => 'bg-secondary',
    'out_for_delivery' => 'bg-primary',
    'completed'        => 'bg-success',
    'cancelled'        => 'bg-danger',
];

// ---------------------- Handle POST actions (orders + menu) ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Order actions (status updates)
    if (isset($_POST['order_action']) && $_POST['order_action'] === 'update_status') {
        $order_id   = (int) ($_POST['order_id'] ?? 0);
        $new_status = $_POST['new_status'] ?? '';

        $allowed_statuses = [
            'pending',
            'preparing',
            'out_for_delivery',
            'completed',
            'cancelled'
        ];

        if ($order_id > 0 && in_array($new_status, $allowed_statuses, true)) {
            $stmt = $mysqli->prepare("
                UPDATE orders
                   SET status = ?, updated_at = NOW()
                 WHERE order_id = ?
            ");
            if ($stmt) {
                $stmt->bind_param("si", $new_status, $order_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        // keep selected order focused after update
        header("Location: admin-dash.php?order_id=" . $order_id);
        exit;
    }

    // 2) Menu actions (availability + edits)
    if (isset($_POST['menu_action'])) {
        $menu_action = $_POST['menu_action'] ?? '';

        if ($menu_action === 'toggle_item') {
            $item_id    = (int) ($_POST['item_id'] ?? 0);
            $new_status = (int) ($_POST['new_status'] ?? 1);

            if ($item_id > 0) {
                $stmt = $mysqli->prepare("UPDATE menu_items SET is_available = ? WHERE item_id = ?");
                if ($stmt) {
                    $stmt->bind_param("ii", $new_status, $item_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        } elseif ($menu_action === 'toggle_size') {
            // still used for EXTRAS only
            $size_id    = (int) ($_POST['size_id'] ?? 0);
            $new_status = (int) ($_POST['new_status'] ?? 1);

            if ($size_id > 0) {
                $stmt = $mysqli->prepare("UPDATE menu_item_sizes SET is_available = ? WHERE size_id = ?");
                if ($stmt) {
                    $stmt->bind_param("ii", $new_status, $size_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        } elseif ($menu_action === 'update_size') {
            // still used for EXTRAS only
            $size_id    = (int) ($_POST['size_id'] ?? 0);
            $new_price  = (float) ($_POST['new_price'] ?? 0);
            $new_status = (int) ($_POST['new_status'] ?? 1);

            if ($size_id > 0) {
                $stmt = $mysqli->prepare("
                    UPDATE menu_item_sizes
                       SET price = ?, is_available = ?
                     WHERE size_id = ?
                ");
                if ($stmt) {
                    $stmt->bind_param("dii", $new_price, $new_status, $size_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        // Redirect back to avoid form resubmission on refresh
        header("Location: admin-dash.php");
        exit;
    }
}

// ---------------------- Fetch recent orders for this admin's branch ----------------------
$orders         = [];
$selected_order = null;

$stmt = $mysqli->prepare("
    SELECT 
        order_id,
        order_code,
        customer_name      AS name,
        customer_phone     AS contact_number,
        branch_id          AS branch_id,
        branch_name        AS branch,
        address            AS address,
        order_type         AS order_type,
        payment_method     AS payment_method,
        subtotal           AS subtotal,
        delivery_fee       AS delivery_fee,
        total_amount       AS total_amount,
        cart_json          AS cart_json,
        status             AS status,
        created_at         AS date
    FROM orders
    WHERE branch_id = ?
    ORDER BY created_at DESC
    LIMIT 20
");
if ($stmt) {
    $stmt->bind_param("s", $admin_branch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
}
$orders_count = count($orders);

// Determine selected order (from ?order_id=... or default first)
$selected_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : null;

if ($orders_count > 0) {
    if ($selected_id) {
        foreach ($orders as $o) {
            if ((int) $o['order_id'] === $selected_id) {
                $selected_order = $o;
                break;
            }
        }
    }
    if ($selected_order === null) {
        $selected_order = $orders[0];
    }
}

// Build a simple "Items summary" text from cart_json for the selected order
$selected_order_food_summary = '';
if ($selected_order && !empty($selected_order['cart_json'])) {
    $snap = json_decode($selected_order['cart_json'], true);
    if (is_array($snap) && !empty($snap['items'])) {
        $parts = [];
        foreach ($snap['items'] as $item) {
            $qty  = (int)($item['qty'] ?? 1);
            $name = $item['name'] ?? 'Item';
            $size = $item['size'] ?? '';
            $label = $name . ($size ? ' (' . $size . ')' : '');
            $parts[] = $qty . 'x ' . $label;
        }
        $selected_order_food_summary = implode(", ", $parts);
    }
}

// ---------------------- Fetch menu (categories + items + sizes) ----------------------
$menu = []; // keyed by slug

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

        $item_id = (int) $row['item_id'];
        if (!isset($menu[$slug]['items'][$item_id])) {
            $menu[$slug]['items'][$item_id] = [
                'item_id'     => $item_id,
                'name'        => $row['item_name'],
                'description' => $row['description'],
                'image_path'  => $row['image_path'],
                'available'   => (int) $row['item_available'],
                'sizes'       => []
            ];
        }

        if (!empty($row['size_id'])) {
            $menu[$slug]['items'][$item_id]['sizes'][] = [
                'size_id'   => (int) $row['size_id'],
                'code'      => $row['size_code'],
                'label'     => $row['size_label'],
                'price'     => (float) $row['price'],
                'available' => (int) $row['size_available']
            ];
        }
    }

    $stmt->close();
}

// Pretty labels/icons for categories
$categoryMeta = [
    'ramen'  => ['icon' => '🍜', 'title' => 'Ramen'],
    'sides'  => ['icon' => '🥟', 'title' => 'Sides'],
    'extras' => ['icon' => '➕', 'title' => 'Ramen Extras'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <meta charset="UTF-8">
    <title>Ramen Naijiro | Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@300;600;900&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../style.css">
</head>

<body style="font-family: 'Roboto Slab', serif; min-height:100vh; display:flex; flex-direction:column;">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">
                <img src="../img/logo.jpg" alt="Ramen Naijiro Logo" class="logo-circle"
                     style="width:50px; height:50px; object-fit:cover; border-radius:50%; margin-right:10px;">
                Ramen Naijiro
            </a>
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <span class="nav-link disabled text-light small">
                        <?php echo htmlspecialchars($admin_name); ?> (<?php echo htmlspecialchars($admin_branch); ?>)
                    </span>
                </li>

                <li class="nav-item"><a class="nav-link active" href="admin-dash.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="admin-res.php">Reservations</a></li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- DASHBOARD CONTENT -->
    <section class="admin-dashboard-section flex-grow-1">
        <div class="container">
            <h2 class="fw-bold mb-4">Admin Dashboard</h2>
            
            <!-- Dashboard Tab Container -->
            <div class="dashboard-tabs-container container py-4">
                <div class="dashboard-tabs">

                    <!-- Orders Section -->
                    <div class="dashboard-section active-section" id="orders-section">
                        <div class="dashboard-section-container p-4 rounded shadow-sm">
                            <div class="dashboard-section-header mb-3">
                                <h4 class="dashboard-section-title">Orders Management</h4>
                                <p class="dashboard-section-desc">
                                    View, track, and manage all customer orders for your branch.
                                </p>
                            </div>

                            <div class="dashboard-section-content row g-4">
                                <!-- Left Column: Recent Orders + status control -->
                                <div class="cust-details col-md-4">
                                    <h5>Recent Orders</h5>

                                    <?php if ($orders_count > 0): ?>
                                        <ul class="list-group mb-3 small">
                                            <?php foreach ($orders as $o):
                                                $is_active   = ($selected_order && $o['order_id'] == $selected_order['order_id']);
                                                $badge_class = $STATUS_BADGE_CLASS[$o['status']] ?? 'bg-secondary';
                                                $badge_label = $STATUS_LABELS[$o['status']] ?? ucfirst($o['status']);
                                            ?>
                                                <li class="list-group-item bg-white d-flex justify-content-between align-items-center <?php echo $is_active ? 'active' : ''; ?> recent-order-item">
                                                    <a href="admin-dash.php?order_id=<?php echo (int)$o['order_id']; ?>"
                                                       class="<?php echo $is_active ? 'text-black' : ''; ?>"
                                                       style="text-decoration:none;">
                                                        <div class="d-flex flex-column">
                                                            <span>
                                                                <?php echo htmlspecialchars($o['order_code']); ?> –
                                                                <?php echo htmlspecialchars($o['name']); ?>
                                                            </span>
                                                            <small class="<?php echo $is_active ? 'text-black' : 'text-muted'; ?>">
                                                                <?php
                                                                $dt = $o['date'] ?? '';
                                                                echo $dt ? date('m-d H:i', strtotime($dt)) : '';
                                                                ?>
                                                            </small>
                                                        </div>
                                                    </a>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo htmlspecialchars($badge_label); ?>
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p class="text-muted">No orders yet for this branch.</p>
                                    <?php endif; ?>

                                    <?php if ($selected_order): ?>
                                        <?php
                                        $sel_status      = $selected_order['status'] ?? 'pending';
                                        $sel_badge_class = $STATUS_BADGE_CLASS[$sel_status] ?? 'bg-secondary';
                                        $sel_badge_label = $STATUS_LABELS[$sel_status] ?? ucfirst($sel_status);
                                        ?>
                                        <h5 class="mt-4">Status Control</h5>
                                        <form method="post" class="mt-2">
                                            <input type="hidden" name="order_action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?php echo (int)$selected_order['order_id']; ?>">

                                            <div class="mb-2 small">
                                                <div class="mb-1 text-dark">
                                                    <strong>Current status:</strong>
                                                    <span class="badge <?php echo $sel_badge_class; ?>">
                                                        <?php echo htmlspecialchars($sel_badge_label); ?>
                                                    </span>
                                                </div>
                                                <label for="order-status" class="form-label small mb-1 text-dark">Change status</label>
                                                <select name="new_status" id="order-status" class="form-select form-select-sm">
                                                    <?php
                                                    $current_status = $selected_order['status'] ?? 'pending';
                                                    foreach ($STATUS_LABELS as $value => $label):
                                                    ?>
                                                        <option value="<?php echo $value; ?>"
                                                            <?php echo ($current_status === $value) ? 'selected' : ''; ?>>
                                                            <?php echo $label; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                                Save Status
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                <!-- Right Column: Order Summary styled like order-confirmed -->
                                <div class="receipt col-md-8">
                                    <h5>Order Summary</h5>

                                    <?php if ($selected_order): ?>
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-4">

                                                <!-- Header row -->
                                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3">
                                                    <div class="mb-2 mb-md-0">
                                                        <h6 class="mb-1 text-dark">
                                                            Order: <span class="font-monospace">
                                                                <?php echo htmlspecialchars($selected_order['order_code']); ?>
                                                            </span>
                                                        </h6>
                                                        <p class="small mb-0 text-dark">
                                                            Customer: <?php echo htmlspecialchars($selected_order['name']); ?><br>
                                                            Contact: <?php echo htmlspecialchars($selected_order['contact_number']); ?>
                                                        </p>
                                                    </div>
                                                    <div class="text-md-end small">
                                                        <?php
                                                        $sel_status      = $selected_order['status'] ?? 'pending';
                                                        $sel_badge_class = $STATUS_BADGE_CLASS[$sel_status] ?? 'bg-secondary';
                                                        $sel_badge_label = $STATUS_LABELS[$sel_status] ?? ucfirst($sel_status);
                                                        ?>
                                                        <p class="mb-1 text-dark">Status</p>
                                                        <span class="badge <?php echo $sel_badge_class; ?>">
                                                            <?php echo htmlspecialchars($sel_badge_label); ?>
                                                        </span>
                                                        <p class="mb-0 mt-2 text-dark">
                                                            Placed:
                                                            <?php
                                                            $dt = $selected_order['date'] ?? '';
                                                            echo $dt ? date('Y-m-d H:i', strtotime($dt)) : 'N/A';
                                                            ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <hr>

                                                <!-- Customer + delivery row -->
                                                <div class="row g-4 small mb-3">
                                                    <div class="col-md-6">
                                                        <h6 class="text-uppercase text-muted mb-2">
                                                            <i class="fa-solid fa-user me-1"></i> Customer
                                                        </h6>
                                                        <p class="mb-1">
                                                            <strong><?php echo htmlspecialchars($selected_order['name']); ?></strong>
                                                        </p>
                                                        <p class="mb-1">
                                                            <i class="fa-solid fa-phone me-1"></i>
                                                            <?php echo htmlspecialchars($selected_order['contact_number']); ?>
                                                        </p>
                                                        <p class="mb-0">
                                                            <strong>Branch:</strong>
                                                            <?php echo htmlspecialchars($selected_order['branch']); ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="text-uppercase text-muted mb-2">
                                                            <i class="fa-solid fa-location-dot me-1"></i> Address
                                                        </h6>
                                                        <p class="mb-1 text-dark">
                                                            <?php echo nl2br(htmlspecialchars($selected_order['address'] ?: 'Not specified')); ?>
                                                        </p>
                                                        <?php
                                                        $type_raw = $selected_order['order_type']     ?? '';
                                                        $pay_raw  = $selected_order['payment_method'] ?? '';

                                                        $type_label = $type_raw !== '' ? $type_raw : 'Unknown';
                                                        if ($pay_raw === 'paymongo') {
                                                            $pay_label = 'Online (PayMongo)';
                                                        } elseif ($pay_raw === 'cod') {
                                                            $pay_label = 'Cash on Delivery';
                                                        } else {
                                                            $pay_label = $pay_raw !== '' ? $pay_raw : 'Unknown';
                                                        }
                                                        ?>
                                                        <p class="mb-0 text-dark">
                                                            <strong>Type:</strong> <?php echo htmlspecialchars($type_label); ?>
                                                            &middot;
                                                            <strong>Payment:</strong> <?php echo htmlspecialchars($pay_label); ?>
                                                        </p>
                                                    </div>
                                                </div>

                                                <hr>

                                                <!-- Items + totals row -->
                                                <div class="row g-4 small">
                                                    <div class="col-md-7">
                                                        <h6 class="text-uppercase text-muted mb-2">
                                                            <i class="fa-solid fa-list-ul me-1"></i> Items
                                                        </h6>
                                                        <p class="mb-0 text-dark">
                                                            <?php
                                                            echo $selected_order_food_summary
                                                                ? nl2br(htmlspecialchars($selected_order_food_summary))
                                                                : '<span class="text-muted">No items found for this order.</span>';
                                                            ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <h6 class="text-uppercase text-muted mb-2">
                                                            <i class="fa-solid fa-receipt me-1"></i> Totals
                                                        </h6>
                                                        <?php
                                                        $sub = isset($selected_order['subtotal'])     ? (float)$selected_order['subtotal']     : 0;
                                                        $del = isset($selected_order['delivery_fee']) ? (float)$selected_order['delivery_fee'] : 0;
                                                        $tot = isset($selected_order['total_amount']) ? (float)$selected_order['total_amount'] : 0;
                                                        ?>
                                                        <div class="border rounded-3 p-3 bg-light">
                                                            <div class="d-flex justify-content-between mb-1 text-dark">
                                                                <span>Subtotal</span>
                                                                <span>₱<?php echo number_format($sub, 2); ?></span>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-1 text-dark">
                                                                <span>Delivery fee</span>
                                                                <span>₱<?php echo number_format($del, 2); ?></span>
                                                            </div>
                                                            <hr class="my-2">
                                                            <div class="d-flex justify-content-between fw-bold text-dark">
                                                                <span>Total</span>
                                                                <span class="text-success">
                                                                    ₱<?php echo number_format($tot, 2); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted mb-0">No order selected.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Management Section -->
                    <div class="menu-mgmt-section" id="menu-section">
                        <div class="menu-mgmt-container">
                            <div class="menu-mgmt-header">
                                <h4 class="menu-mgmt-title">Menu Management</h4>
                                <p class="menu-mgmt-desc">
                                    Toggle availability of menu items. Extras can still be edited per add-on.
                                </p>
                            </div>

                            <?php
                            // Desired order of categories
                            $order_slugs = ['ramen', 'sides', 'extras'];

                            foreach ($order_slugs as $slug):
                                if (!isset($menu[$slug])) continue;
                                $cat  = $menu[$slug];
                                $meta = $categoryMeta[$slug] ?? ['icon' => '', 'title' => $cat['name']];
                            ?>
                                <h5 class="menu-category-title mb-3">
                                    <?php echo $meta['icon'] . ' ' . htmlspecialchars($meta['title']); ?>
                                </h5>

                                <div class="menu-mgmt-row mb-5">
                                    <?php foreach ($cat['items'] as $item): ?>
                                        <div class="menu-mgmt-col">
                                            <div class="menu-mgmt-card h-100 d-flex flex-column">
                                                <?php
                                                // Show images for ramen and sides; extras are text-only
                                                $showImage = (in_array($slug, ['ramen', 'sides'], true) && !empty($item['image_path']));
                                                ?>
                                                <?php if ($showImage): ?>
                                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>"
                                                         class="menu-mgmt-img"
                                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                <?php endif; ?>

                                                <div class="card-body d-flex flex-column">
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <h6 class="card-title mb-0">
                                                            <?php echo htmlspecialchars($item['name']); ?>
                                                        </h6>
                                                        <?php if ($item['available']): ?>
                                                            <span class="badge bg-success ms-2">Available</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger ms-2">Hidden</span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if (!empty($item['description'])): ?>
                                                        <p class="card-text small">
                                                            <?php echo htmlspecialchars($item['description']); ?>
                                                        </p>
                                                    <?php endif; ?>

                                                    <?php if (!empty($item['sizes'])): ?>
                                                        <ul class="list-unstyled mb-3 small">
                                                            <?php foreach ($item['sizes'] as $size): ?>
                                                                <li class="d-flex justify-content-between align-items-center mb-1">
                                                                    <span class="me-2">
                                                                        <strong><?php echo htmlspecialchars($size['label']); ?></strong>
                                                                    </span>

                                                                    <span class="fw-semibold text-dark">
                                                                        ₱<?php echo number_format($size['price'], 2); ?>
                                                                        <?php if (!$size['available']): ?>
                                                                            <span class="badge bg-secondary ms-1">Unavailable</span>
                                                                        <?php endif; ?>
                                                                    </span>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: ?>
                                                        <p class="text-muted small mb-3">No prices set for this item.</p>
                                                    <?php endif; ?>


                                                    <div class="mt-auto card-btns d-flex gap-2">
                                                        <!-- Availability toggle for whole item -->
                                                        <form method="post">
                                                            <input type="hidden" name="menu_action" value="toggle_item">
                                                            <input type="hidden" name="item_id" value="<?php echo (int)$item['item_id']; ?>">
                                                            <input type="hidden" name="new_status" value="<?php echo $item['available'] ? 0 : 1; ?>">
                                                            <button type="submit"
                                                                    class="btn btn-sm <?php echo $item['available'] ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                                                                <?php echo $item['available'] ? 'Mark Unavailable' : 'Mark Available'; ?>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="text-white py-4 mt-auto">
        <div class="container text-center">
            <p>&copy; 2025 Ramen Naijiro. All rights reserved.</p>
            <a href="https://www.facebook.com/RamenNaijiroGTC" class="text-warning text-decoration-none">
                <i class="fa-brands fa-facebook"></i>
            </a><br>
            <a href="admin-login.php">Admin Login</a>
            <a href="../contact-us.php">Contact Us</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin-tab.js"></script>

    <!-- Edit Size Modal (still used for EXTRAS) -->
    <div class="modal fade" id="editSizeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form method="post" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Size</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <input type="hidden" name="menu_action" value="update_size">
            <input type="hidden" name="size_id" id="edit-size-id">

            <div class="mb-3">
              <label for="edit-size-price" class="form-label">Price (₱)</label>
              <input type="number" step="0.01" min="0"
                     class="form-control" name="new_price" id="edit-size-price">
            </div>

            <div class="mb-3">
              <label for="edit-size-status" class="form-label">Availability</label>
              <select class="form-select" name="new_status" id="edit-size-status">
                <option value="1">Available</option>
                <option value="0">Hidden</option>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>

    <script>
    document.querySelectorAll('.btn-edit-size').forEach(btn => {
      btn.addEventListener('click', () => {
        const id     = btn.dataset.sizeId;
        const price  = btn.dataset.price;
        const status = btn.dataset.status;

        document.getElementById('edit-size-id').value     = id;
        document.getElementById('edit-size-price').value  = price;
        document.getElementById('edit-size-status').value = status;

        const modal = new bootstrap.Modal(document.getElementById('editSizeModal'));
        modal.show();
      });
    });
    </script>

</body>
</html>
