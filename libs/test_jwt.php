<?php
// Sertakan file library JWT
require_once __DIR__ . '/libs/firebase-jwt/src/JWT.php';
require_once __DIR__ . '/libs/firebase-jwt/src/Key.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Generate Token
$payload = [
    "iss" => "http://localhost",
    "aud" => "http://localhost",
    "iat" => time(),
    "exp" => time() + 3600,
    "data" => [
        "id" => 1,
        "email" => "user@example.com"
    ]
];

$secret_key = "your_secret_key";
$jwt = JWT::encode($payload, $secret_key, 'HS256');

echo "JWT: " . $jwt;

// Decode Token
try {
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    print_r($decoded);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
