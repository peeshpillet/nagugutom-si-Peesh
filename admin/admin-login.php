<?php
// admin/admin-login.php - Admin login (email + password, optional branch)

session_start();
require_once "../config.php";

// Centralized branch list (same as contact-us + admin-res)
$BRANCHES = [
    'General Trias',
    'Dasmariñas',
    'Odasiba',
    'Marikina',
    'Cainta'
];

// If already logged in, send straight to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin-dash.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read and sanitize input
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $branch   = trim($_POST['branch'] ?? '');

    if ($email === '' || $password === '') {
        $error = "Please enter both email and password.";
    } else {
        // Look up admin by email from `admins` table
        $stmt = $mysqli->prepare("
            SELECT admin_id, name, email, branch, password
            FROM admins
            WHERE email = ?
            LIMIT 1
        ");

        if (!$stmt) {
            $error = "Database error. Please try again later.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                // NOTE: plain-text password comparison, per current design
                if ($password === $row['password']) {
                    // Login OK → store in session
                    $_SESSION['admin_id']    = $row['admin_id'];
                    $_SESSION['admin_name']  = $row['name'];
                    $_SESSION['admin_email'] = $row['email'];

                    // If user picked a branch and it's valid, use that.
                    // Otherwise, fall back to whatever is stored in DB.
                    if ($branch !== '' && in_array($branch, $BRANCHES, true)) {
                        $_SESSION['admin_branch'] = $branch;
                    } else {
                        $_SESSION['admin_branch'] = $row['branch'] ?: 'General Trias';
                    }

                    header("Location: admin-dash.php");
                    exit;
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "Admin account not found for that email.";
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ramen Naijiro | Admin Login</title>
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

<body>

    <div class="admin-page-wrapper">

        <!-- ADMIN NAVBAR -->
        <nav class="admin-navbar navbar navbar-expand-lg sticky-top shadow-sm">
            <div class="container">
                <a class="admin-navbar-brand fw-bold" href="../index.php">
                    <img src="../img/logo.jpg" alt="Ramen Naijiro Logo" class="admin-logo-circle">
                    Ramen Naijiro
                </a>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="admin-nav-link nav-link" href="../index.php">Customer Home</a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- ADMIN LOGIN FORM -->
        <section class="admin-login-section">
            <div class="container d-flex justify-content-center align-items-center">
                <div class="admin-login-card">
                    <h3>Admin Login</h3>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="adminEmail" class="form-label">Email</label>
                            <input
                                type="email"
                                class="form-control"
                                id="adminEmail"
                                name="email"
                                placeholder="admin@naijiro.com"
                                value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                required
                            >
                        </div>

                        <!-- Branch Dropdown (optional override) -->
                        <div class="mb-3">
                            <label for="adminBranch" class="form-label">Branch (optional)</label>
                            <select class="form-select" id="adminBranch" name="branch">
                                <option value=""
                                    <?php echo (empty($branch) ? 'selected' : ''); ?>>
                                    Use account's default branch
                                </option>
                                <?php foreach ($BRANCHES as $b): ?>
                                    <option
                                        value="<?php echo htmlspecialchars($b); ?>"
                                        <?php echo (isset($branch) && $branch === $b) ? 'selected' : ''; ?>
                                    >
                                        <?php echo htmlspecialchars($b); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="adminPassword" class="form-label">Password</label>
                            <input
                                type="password"
                                class="form-control"
                                id="adminPassword"
                                name="password"
                                placeholder="********"
                                required
                            >
                        </div>

                        <!-- Login Button -->
                        <button type="submit" class="admin-btn-login btn-login">
                            <i class="fa-solid fa-right-to-bracket"></i> Login
                        </button>
                    </form>
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

    </div>

</body>

</html>
