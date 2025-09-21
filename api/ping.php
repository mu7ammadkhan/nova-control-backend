<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
http_response_code(200);
echo json_encode(['ok'=>true,'service'=>'nova-control-backend','ts'=>gmdate('c')], JSON_UNESCAPED_SLASHES);
