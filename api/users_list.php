<?php
// ----------------------------
// CORS setup
// ----------------------------
$allowed_origins = ["http://localhost:5173","http://127.0.0.1:5173"];
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
  header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Vary: Origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ----------------------------
// DB + fetch users
// ----------------------------
require_once "../db.php";

$q = "SELECT id, name, username, email, role, is_active, created_at
      FROM users
      ORDER BY id DESC";
$res = $conn->query($q);

$rows = [];
while ($row = $res->fetch_assoc()) {
  $row['id'] = (int)$row['id'];
  $row['is_active'] = (int)$row['is_active'];
  $rows[] = $row;
}

echo json_encode(["ok"=>true, "data"=>$rows]);
