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
                <a class="admin-navbar-brand fw-bold" href="../index.html">
                    <img src="../img/logo.jpg" alt="Ramen Naijiro Logo" class="admin-logo-circle">
                    Ramen Naijiro
                </a>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="admin-nav-link nav-link" href="../index.html">Customer Home</a></li>
                </ul>
            </div>
        </nav>

        <!-- ADMIN LOGIN FORM -->
        <section class="admin-login-section">
            <div class="container d-flex justify-content-center align-items-center">
                <div class="admin-login-card">
                    <h3>Admin Login</h3>
                    <form>
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="adminEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="adminEmail" placeholder="admin@naijiro.com">
                        </div>

                        <!-- Branch Dropdown -->
                        <div class="mb-3">
                            <label for="adminBranch" class="form-label">Branch</label>
                            <select class="form-select" id="adminBranch">
                                <option selected disabled>Select a branch</option>
                                <option value="main">Main Branch</option>
                                <option value="east">East Branch</option>
                                <option value="west">West Branch</option>
                                <option value="north">North Branch</option>
                                <option value="south">South Branch</option>
                            </select>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="adminPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="adminPassword" placeholder="********">
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
                <a href="https://www.facebook.com/RamenNaijiroGTC" class="text-warning text-decoration-none"><i
                        class="fa-brands fa-facebook"></i></a><br>
                <a href="admin/admin-login.html">Admin Login</a>
                <a href="contact-us.html">Contact Us</a>
            </div>
        </footer>

    </div>

</body>

</html>