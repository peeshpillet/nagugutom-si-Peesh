<?php
// admin-login.php - Admin login with DB validation

session_start();
require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // Look up user by username
        $stmt = $mysqli->prepare("
            SELECT id, username, password, full_name
            FROM admin_users
            WHERE username = ?
            LIMIT 1
        ");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin  = $result->fetch_assoc();
        $stmt->close();

        // Plain-text compare (no hash as requested)
        if ($admin && $password === $admin['password']) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'] ?: $admin['username'];

            // Send her to dashboard
            header('Location: admin-dash.html');
            exit;
        } else {
            $error = 'Invalid username or password.';
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
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.html">
                <img src="img/logo.jpg" alt="Ramen Naijiro Logo" class="logo-circle">
                Ramen Naijiro
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="menu.html">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="about-us.html">About</a></li>
                <li class="nav-item"><a class="nav-link" href="track-order.html">Track Order</a></li>
            </ul>
        </div>
    </nav>

    <!-- LOGIN FORM -->
    <section class="admin-login-section">
        <div class="container d-flex justify-content-center align-items-center">
            <div class="admin-login-card">
                <h3>Admin Login</h3>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <!-- Username -->
                    <div class="mb-3">
                        <label for="adminUsername" class="form-label">Username</label>
                        <input type="text"
                               class="form-control"
                               id="adminUsername"
                               name="username"
                               placeholder="admin"
                               required>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="adminPassword" class="form-label">Password</label>
                        <input type="password"
                               class="form-control"
                               id="adminPassword"
                               name="password"
                               placeholder="********"
                               required>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn-login">
                        <i class="fa-solid fa-right-to-bracket"></i> Login
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="admin-login-footer">
        <p>&copy; 2025 Ramen Naijiro. All rights reserved.</p>
        <a href="https://www.facebook.com/RamenNaijiroGTC" class="text-warning fs-4">
            <i class="fa-brands fa-facebook"></i>
        </a><br>
        <a href="admin-login.php" class="text-white-50 small">Admin login</a>
    </footer>

</body>

</html>
