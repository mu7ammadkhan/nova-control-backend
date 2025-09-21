<?php
require "db.php";

$name = "System Admin";
$username = "admin";
$email = "admin@example.com";
$plainPassword = "admin123"; // baad me change kar lena

$hash = password_hash($plainPassword, PASSWORD_DEFAULT);

// check if exists
$check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
$check->bind_param("ss", $username, $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
  echo "ℹ️ Admin already exists. (username: admin)";
  exit;
}
$check->close();

// insert admin
$ins = $conn->prepare("INSERT INTO users (name, username, email, password_hash, role, is_active) VALUES (?,?,?,?, 'admin', 1)");
$ins->bind_param("ssss", $name, $username, $email, $hash);

if ($ins->execute()) {
  echo "✅ Admin created — username: admin, password: {$plainPassword}";
} else {
  echo "❌ Error: " . $ins->error;
}
