<?php
// ----------------------------
// CORS setup
// ----------------------------
$allowed_origins = [
  "http://localhost:5173",
  "http://127.0.0.1:5173"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
  header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Vary: Origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

// ----------------------------
// DB connection
// ----------------------------
require_once "../db.php";

// ----------------------------
// Input validation
// ----------------------------
$input = json_decode(file_get_contents("php://input"), true);
$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? $input['password'] : '';

if ($username === '' || $password === '') {
  http_response_code(400);
  echo json_encode(["ok"=>false, "error"=>"username and password required"]);
  exit;
}

// ----------------------------
// User lookup
// ----------------------------
$stmt = $conn->prepare("SELECT id, name, username, email, password_hash, role, is_active FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
  http_response_code(401);
  echo json_encode(["ok"=>false, "error"=>"invalid credentials"]);
  exit;
}

$user = $res->fetch_assoc();

// ----------------------------
// Checks
// ----------------------------
if ((int)$user['is_active'] !== 1) {
  http_response_code(403);
  echo json_encode(["ok"=>false, "error"=>"account disabled"]);
  exit;
}

if (!password_verify($password, $user['password_hash'])) {
  http_response_code(401);
  echo json_encode(["ok"=>false, "error"=>"invalid credentials"]);
  exit;
}

// ----------------------------
// Success response
// ----------------------------
$token = base64_encode(random_bytes(24));

echo json_encode([
  "ok" => true,
  "token" => $token,
  "user" => [
    "id" => (int)$user['id'],
    "name" => $user['name'],
    "username" => $user['username'],
    "email" => $user['email'],
    "role" => $user['role']
  ]
]);
