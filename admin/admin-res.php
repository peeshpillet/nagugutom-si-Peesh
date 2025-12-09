<?php
// admin/admin-res.php - Reservations management for admins

session_start();

// Require login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit;
}

require_once "../config.php";

// Logged-in admin info
$admin_name   = $_SESSION['admin_name']   ?? 'Admin';
$admin_branch = $_SESSION['admin_branch'] ?? 'General Trias';

$success = "";
$error   = "";

// Branch choices (sync with contact-us + admin-login)
$BRANCHES = [
    'General Trias',
    'Dasmariñas',
    'Odasiba',
    'Marikina',
    'Cainta'
];

// For the "Add Reservation" form, default to the admin's branch if it matches
$defaultBranchForForm = in_array($admin_branch, $BRANCHES, true) ? $admin_branch : '';

// Generate a reservation code like RS-5FFD62
function generateReservationCode() {
    $prefix = "RS-";
    $random = strtoupper(bin2hex(random_bytes(3))); // 6 hex chars → 5A7B2D
    return $prefix . $random;
}

// ---------------------- Handle Add / Remove form actions ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        // Add reservation
        $name    = trim($_POST['name'] ?? '');
        $contact = trim($_POST['contact_number'] ?? '');
        $pax     = (int)($_POST['pax'] ?? 1);
        $date    = trim($_POST['date'] ?? '');
        $branch  = trim($_POST['branch'] ?? '');

        if ($name === '' || $contact === '' || $date === '' || $branch === '') {
            $error = "Please fill in name, contact, date, and branch.";
        } elseif (!in_array($branch, $BRANCHES, true)) {
            $error = "Invalid branch selected.";
        } else {
            if ($pax <= 0) {
                $pax = 1;
            }

            $res_code = generateReservationCode();

            $stmt = $mysqli->prepare("
                INSERT INTO reservations (res_code, name, contact_number, branch, pax, date)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            if ($stmt) {
                $stmt->bind_param("ssssis", $res_code, $name, $contact, $branch, $pax, $date);
                if ($stmt->execute()) {
                    $success = "Reservation added successfully. Code: {$res_code}";
                } else {
                    $error = "Failed to add reservation.";
                }
                $stmt->close();
            } else {
                $error = "Database error while adding reservation.";
            }
        }
    } elseif ($action === 'delete') {
        // Remove reservation by reservation code (global, not per-branch now)
        $res_code = trim($_POST['res_code'] ?? '');

        if ($res_code === '') {
            $error = "Please provide a reservation code.";
        } else {
            $stmt = $mysqli->prepare("
                DELETE FROM reservations
                WHERE res_code = ?
            ");
            if ($stmt) {
                $stmt->bind_param("s", $res_code);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $success = "Reservation {$res_code} removed.";
                } else {
                    $error = "Reservation {$res_code} not found.";
                }
                $stmt->close();
            } else {
                $error = "Database error while removing reservation.";
            }
        }
    }
}

// ---------------------- Fetch ALL reservations (all branches) ----------------------
$reservations          = [];
$reservations_by_date  = []; // date => [rows]

$stmt = $mysqli->prepare("
    SELECT res_id, res_code, name, contact_number, branch, pax, date
    FROM reservations
    ORDER BY date ASC, res_id ASC
");

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;

        $d = $row['date'];
        if (!isset($reservations_by_date[$d])) {
            $reservations_by_date[$d] = [];
        }
        $reservations_by_date[$d][] = $row;
    }

    $stmt->close();
}

$unique_dates = array_keys($reservations_by_date);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ramen Naijiro | Admin Reservations</title>
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
                <li class="nav-item"></li>
                <li class="nav-item"><a class="nav-link" href="admin-dash.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="admin-res.php">Reservations</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a></li>
            </ul>
        </div>
    </nav>

    <!-- RESERVATIONS CONTENT -->
    <section class="admin-res flex-grow-1">
        <div class="container">
            <h2 class="admin-res-title mt-4 mb-3">Reservations Management</h2>

            <?php if ($success): ?>
                <div class="alert alert-success py-2"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
    
            <!-- Main Card -->
            <div class="admin-res-card p-4 rounded shadow-sm mb-4">
                <h4>Reservation Calendar Overview</h4>
                <p class="mb-3">
                    Hover over a date to view reservations for that day. (Auto-refreshes every 60 seconds.)
                </p>

                <!-- Calendar-style date menu + hover details -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="list-group small">
                            <?php if (!empty($unique_dates)): ?>
                                <?php foreach ($unique_dates as $idx => $d): ?>
                                    <button
                                        type="button"
                                        class="list-group-item list-group-item-action res-date-item <?php echo $idx === 0 ? 'active' : ''; ?>"
                                        data-date="<?php echo htmlspecialchars($d); ?>"
                                    >
                                        <?php echo date('M d, Y', strtotime($d)); ?>
                                        <span class="ms-2 text-primary">
                                            • <?php echo count($reservations_by_date[$d]); ?> reservation(s)
                                        </span>
                                    </button>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item text-muted">
                                    No reservations yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <?php if (!empty($unique_dates)): ?>
                            <?php foreach ($unique_dates as $idx => $d): ?>
                                <div
                                    class="date-detail border rounded p-3 mb-2 <?php echo $idx === 0 ? '' : 'd-none'; ?>"
                                    data-date="<?php echo htmlspecialchars($d); ?>"
                                >
                                    <h6 class="mb-2">
                                        Reservations for <?php echo date('M d, Y', strtotime($d)); ?>
                                    </h6>
                                    <ul class="list-group list-group-flush small">
                                        <?php foreach ($reservations_by_date[$d] as $r): ?>
                                            <li class="list-group-item">
                                                <strong><?php echo htmlspecialchars($r['res_code']); ?></strong>
                                                — <?php echo htmlspecialchars($r['name']); ?>
                                                (<?php echo (int)$r['pax']; ?> pax,
                                                <?php echo htmlspecialchars($r['branch']); ?>,
                                                <?php echo htmlspecialchars($r['contact_number']); ?>)
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">No reservation details to show.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Detailed reservations table -->
                <h5>All Reservations</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Reservation Code</th>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Branch</th>
                                <th>Pax</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($reservations)): ?>
                                <?php foreach ($reservations as $r): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($r['res_code']); ?></td>
                                        <td><?php echo htmlspecialchars($r['date']); ?></td>
                                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                                        <td><?php echo htmlspecialchars($r['contact_number']); ?></td>
                                        <td><?php echo htmlspecialchars($r['branch']); ?></td>
                                        <td><?php echo (int)$r['pax']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        No reservations found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ========= ADD / REMOVE FORMS ========= -->

                <!-- Add Reservation Form -->
                <h5 class="mt-3">Add Reservation (Admin Only)</h5>
                <form method="post" id="addReservationForm" class="row g-3 mb-3">
                    <input type="hidden" name="action" value="add">

                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Branch</label>
                        <select name="branch" class="form-select" required>
                            <option value="" disabled <?php echo $defaultBranchForForm === '' ? 'selected' : ''; ?>>
                                Select branch
                            </option>
                            <?php foreach ($BRANCHES as $b): ?>
                                <option
                                    value="<?php echo htmlspecialchars($b); ?>"
                                    <?php echo ($b === $defaultBranchForForm) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($b); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Pax</label>
                        <input type="number" name="pax" class="form-control" min="1" value="1" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                </form>

                <!-- Remove Reservation Form -->
                <h5 class="mt-3">Remove Reservation</h5>
                <form method="post" id="removeReservationForm" class="d-flex flex-wrap gap-2 align-items-center mb-2">
                    <input type="hidden" name="action" value="delete">

                    <div>
                        <label class="form-label mb-0 small">Reservation Code</label>
                        <input
                            type="text"
                            name="res_code"
                            class="form-control form-control-sm"
                            placeholder="e.g. RS-5FFD62"
                            required
                        >
                    </div>
                </form>
    
                <!-- Add/Remove Buttons -->
                <div class="admin-res-actions mt-1 d-flex gap-2">
                    <button type="submit" form="addReservationForm" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-plus"></i> Add Reservation
                    </button>
                    <button type="submit" form="removeReservationForm" class="btn btn-danger btn-sm">
                        <i class="fa-solid fa-xmark"></i> Remove Reservation
                    </button>
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

    <!-- Simple JS for hover details + auto-refresh -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateItems = document.querySelectorAll('.res-date-item');
            const detailPanels = document.querySelectorAll('.date-detail');

            function showDetails(date) {
                detailPanels.forEach(panel => {
                    panel.classList.toggle('d-none', panel.dataset.date !== date);
                });
                dateItems.forEach(item => {
                    item.classList.toggle('active', item.dataset.date === date);
                });
            }

            dateItems.forEach(item => {
                item.addEventListener('mouseenter', () => {
                    showDetails(item.dataset.date);
                });
            });

            // Auto-refresh every 60 seconds
            setInterval(() => {
                window.location.reload();
            }, 60000);
        });
    </script>

</body>

</html>
