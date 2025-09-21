<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
// TODO: DB se real counts; abhi placeholders
echo json_encode([
  "devices_total" => 24,
  "rooms_active"  => 7,
  "ts" => gmdate("c")
], JSON_UNESCAPED_SLASHES);
