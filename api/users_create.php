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
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ----------------------------
// DB connect
// ----------------------------
require_once "../db.php";

// ----------------------------
// Read + validate body
// ----------------------------
$input = json_decode(file_get_contents("php://input"), true) ?? [];
$name = trim($input['name'] ?? "");
$username = trim($input['username'] ?? "");
$email = trim($input['email'] ?? "");
$password = $input['password'] ?? "";
$role = $input['role'] ?? "user"; // 'admin' | 'user'
$is_active = isset($input['is_active']) ? (int)$input['is_active'] : 1;

if ($name === "" || $username === "" || $email === "" || $password === "") {
  http_response_code(400);
  echo json_encode(["ok"=>false, "error"=>"name, username, email, password required"]);
  exit;
}
if (!in_array($role, ["admin","user"])) { $role = "user"; }

// ----------------------------
// Uniqueness check
// ----------------------------
$check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
$check->bind_param("ss", $username, $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
  http_response_code(409);
  echo json_encode(["ok"=>false, "error"=>"username or email already exists"]);
  exit;
}
$check->close();

// ----------------------------
// Insert
// ----------------------------
$hash = password_hash($password, PASSWORD_DEFAULT);

$ins = $conn->prepare("INSERT INTO users (name, username, email, password_hash, role, is_active) VALUES (?,?,?,?,?,?)");
$ins->bind_param("sssssi", $name, $username, $email, $hash, $role, $is_active);

if ($ins->execute()) {
  echo json_encode([
    "ok"=>true,
    "user"=>[
      "id" => $ins->insert_id,
      "name"=>$name,
      "username"=>$username,
      "email"=>$email,
      "role"=>$role,
      "is_active"=>$is_active
    ]
  ]);
} else {
  http_response_code(500);
  echo json_encode(["ok"=>false, "error"=>"db insert failed: ".$ins->error]);
}
