<?php
// contact-us.php - Customer reservations + contact info

require_once "config.php";

// Branch choices (align these with what you use in admin-res.php later)
$BRANCHES = [
    'General Trias',
    'Dasmariñas',
    'Odasiba',
    'Marikina',
    'Cainta'
];

$res_success = '';
$res_error   = '';

/**
 * Generate a reservation code like RS-5FFD62
 */
function generateReservationCode()
{
    $prefix = "RS-";
    $random = strtoupper(bin2hex(random_bytes(3))); // 6 hex chars → 5A7B2D
    return $prefix . $random;
}

// ---------------------- Handle reservation form submit ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'reserve') {
        // Form fields
        $name    = trim($_POST['customerName'] ?? '');
        $email   = trim($_POST['customerEmail'] ?? '');      // not stored yet, but kept for future use
        $phone   = trim($_POST['customerPhone'] ?? '');
        $date    = trim($_POST['reservationDate'] ?? '');
        $time    = trim($_POST['reservationTime'] ?? '');
        $pax     = (int) ($_POST['reservationPax'] ?? 1);
        $branch  = trim($_POST['reservationBranch'] ?? '');
        $notes   = trim($_POST['reservationNotes'] ?? '');   // also for future use / possible extra column

        // Basic validation
        if ($name === '' || $phone === '' || $date === '' || $time === '' || $branch === '') {
            $res_error = "Please fill in your name, phone, date, time, and branch.";
        } elseif (!in_array($branch, $BRANCHES, true)) {
            $res_error = "Invalid branch selected.";
        } else {
            if ($pax <= 0) {
                $pax = 1;
            }

            // Combine date + time into one string for the `date` column
            // If your column is DATE, MySQL will ignore the time and just store the date.
            $datetime = $date . ' ' . $time . ':00';

            $res_code = generateReservationCode();

            // Insert into reservations table (same shape as in admin-res.php)
            $stmt = $mysqli->prepare("
                INSERT INTO reservations (res_code, name, contact_number, branch, pax, date)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            if ($stmt) {
                $stmt->bind_param("ssssis", $res_code, $name, $phone, $branch, $pax, $datetime);

                if ($stmt->execute()) {
                    $res_success = "Reservation submitted! Your reservation code is {$res_code}. Please keep this code for confirmation.";
                    // Optional: clear POST so form goes blank on success
                    $_POST = [];
                } else {
                    $res_error = "Sorry, something went wrong while saving your reservation.";
                }

                $stmt->close();
            } else {
                $res_error = "Database error while saving reservation.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ramen Naijiro | Contact Us</title>
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

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about-us.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="menu.php">Menu</a></li>
                    <li class="nav-item"><a class="nav-link" href="track-order.php">Track Order</a></li>
                    <li class="nav-item"><a class="nav-link active" href="contact-us.php">Contact Us</a></li>
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                        <a href="menu.php" class="btn btn-warning">
                            <i class="fa-solid fa-bag-shopping me-1"></i> Order Now
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- PAGE HEADER -->
    <section class="text-center py-5 page-header">
        <div class="container">
            <h1 class="display-4">Contact Us</h1>
            <p class="lead">Reserve your table or reach out to Ramen Naijiro.</p>
        </div>
    </section>

    <!-- CONTACT / RESERVATION SECTION -->
    <section class="contact-us-section py-5 flex-grow-1">
        <div class="container">
            <div class="row justify-content-center g-4">

                <!-- Reservation Form -->
                <div class="col-lg-6">
                    <h2 class="mb-3 text-center">Reserve Your Table</h2>

                    <?php if ($res_success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($res_success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($res_error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($res_error); ?>
                        </div>
                    <?php endif; ?>

                    <form class="contact-form p-4 rounded shadow-sm" method="post" action="contact-us.php">
                        <input type="hidden" name="action" value="reserve">

                        <!-- Customer Details -->
                        <div class="mb-3">
                            <label for="customerName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="customerName" name="customerName"
                                   placeholder="John Doe"
                                   value="<?php echo htmlspecialchars($_POST['customerName'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="customerEmail" class="form-label">Email (optional)</label>
                            <input type="email" class="form-control" id="customerEmail" name="customerEmail"
                                   placeholder="example@email.com"
                                   value="<?php echo htmlspecialchars($_POST['customerEmail'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="customerPhone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="customerPhone" name="customerPhone"
                                   placeholder="+63 912 345 6789"
                                   value="<?php echo htmlspecialchars($_POST['customerPhone'] ?? ''); ?>"
                                   required>
                        </div>

                        <!-- Branch & Pax -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reservationBranch" class="form-label">Branch</label>
                                <select class="form-select" id="reservationBranch" name="reservationBranch" required>
                                    <option value="">Select branch</option>
                                    <?php
                                    $selectedBranch = $_POST['reservationBranch'] ?? '';
                                    foreach ($BRANCHES as $b):
                                        $sel = ($selectedBranch === $b) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo htmlspecialchars($b); ?>" <?php echo $sel; ?>>
                                            <?php echo htmlspecialchars($b); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="reservationPax" class="form-label">Number of Guests</label>
                                <input type="number" class="form-control" id="reservationPax" name="reservationPax"
                                       min="1"
                                       value="<?php echo htmlspecialchars($_POST['reservationPax'] ?? '1'); ?>"
                                       required>
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reservationDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="reservationDate" name="reservationDate"
                                       value="<?php echo htmlspecialchars($_POST['reservationDate'] ?? ''); ?>"
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="reservationTime" class="form-label">Time</label>
                                <input type="time" class="form-control" id="reservationTime" name="reservationTime"
                                       value="<?php echo htmlspecialchars($_POST['reservationTime'] ?? ''); ?>"
                                       required>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="reservationNotes" class="form-label">Notes / Special Requests</label>
                            <textarea class="form-control" id="reservationNotes" name="reservationNotes" rows="3"
                                      placeholder="Allergies, seating preference, occasion, etc."><?php
                                echo htmlspecialchars($_POST['reservationNotes'] ?? '');
                            ?></textarea>
                        </div>

                        <!-- Submit -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-warning px-4">Submit Reservation</button>
                        </div>
                    </form>
                </div>

                <!-- Contact Info / Reach Us -->
                <div class="col-lg-6">
                    <h2 class="mb-3 text-center">Reach Ramen Naijiro</h2>

                    <div class="p-4 rounded shadow-sm mb-4 bg-light">
                        <h5 class="mb-3"><i class="fa-solid fa-phone me-2"></i>Contact Numbers</h5>
                        <p class="mb-1">General Trias: <strong>+63 9xx xxx xxxx</strong></p>
                        <p class="mb-1">Dasmariñas: <strong>+63 9xx xxx xxxx</strong></p>
                        <p class="mb-1">Odasiba: <strong>+63 9xx xxx xxxx</strong></p>
                        <p class="mb-1">Marikina: <strong>+63 9xx xxx xxxx</strong></p>
                        <p class="mb-1">Cainta: <strong>+63 9xx xxx xxxx</strong></p>
                    </div>

                    <div class="p-4 rounded shadow-sm mb-4 bg-light">
                        <h5 class="mb-3"><i class="fa-brands fa-facebook me-2"></i>Facebook</h5>
                        <p class="mb-2">
                            Message us on our official page for quick questions, delivery inquiries, and promos:
                        </p>
                        <p class="mb-0">
                            <a href="https://www.facebook.com/RamenNaijiroGTC"
                               class="btn btn-outline-primary" target="_blank">
                                Open Ramen Naijiro Facebook Page
                            </a>
                        </p>
                    </div>

                    <div class="p-4 rounded shadow-sm bg-light">
                        <h5 class="mb-3"><i class="fa-solid fa-clock me-2"></i>Operating Hours</h5>
                        <p class="mb-1"><strong>All branches:</strong></p>
                        <p class="mb-2">12:00 NN – 11:00 PM (Daily)</p>
                        <p class="small text-muted mb-0">
                            For branch-specific announcements (holidays, maintenance, etc.), please check our Facebook page.
                        </p>
                    </div>
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
</body>
</html>
