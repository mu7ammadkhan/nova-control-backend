<?php
$host = "localhost";
$user = "root";     // default XAMPP MySQL user
$pass = "";         // default password empty hota hai
$db   = "home_automation";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
