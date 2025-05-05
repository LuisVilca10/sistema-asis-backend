<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$secretKey = '3st0y-S3gurO-4Qu1';
$method = $_SERVER['REQUEST_METHOD'];

// ========================
// LOGIN DE ADMINISTRADOR
// ========================
if ($method === 'POST' && !isset(getallheaders()['Authorization'])) {
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);

    if (isset($data["nombre"], $data["correo"], $data["dni"])) {
        $nombre = $data["nombre"];
        $correo = $data["correo"];
        $dni = $data["dni"];

        $admins = [
            "02416492", "42729761", "02445278", "02045892", "02430689",
            "02045304", "02412446", "02145647", "02173801", "02435472",
            "43989847", "88888888", "99999999"
        ];

        if (in_array($dni, $admins)) {
            $payload = [
                "dni" => $dni,
                "nombre" => $nombre,
                "correo" => $correo,
                "iat" => time()
            ];

            $jwt = JWT::encode($payload, $secretKey, 'HS256');

            echo json_encode([
                'status' => 1,
                'message' => 'Login exitoso',
                'token' => $jwt
            ]);
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'No eres un administrador.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 0,
            'message' => 'Faltan parámetros (dni, nombre, correo)'
        ]);
    }
    exit;
}