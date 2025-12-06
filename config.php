<?php
// config.php - shared DB connection for the site

$host = 'localhost';
$user = 'root';
$pass = 'Pokemon2003';
$db   = 'ramen_naijiro'; // must match the DB name above

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    die('Failed to connect to MySQL: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Optional but good practice
$mysqli->set_charset('utf8mb4');
?>
