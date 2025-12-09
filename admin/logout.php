<?php
// admin/logout.php - destroy admin session and redirect to login

session_start();

// Destroy everything
session_unset();
session_destroy();

// Optional: delete session cookie too
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login
header("Location: admin-login.php");
exit;
