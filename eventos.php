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

if ($method === 'POST') {
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;

    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(["error" => "Token no proporcionado"]);
        exit;
    }

    $token = $matches[1];

    try {
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Token inválido: " . $e->getMessage()]);
        exit;
    }

    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['nombre'], $input['latitud'], $input['longitud'], $input['fecha_inicio'], $input['fecha_fin'])) {
        $conn = new mysqli("localhost", "root", "", "metodica_maestro");
        if ($conn->connect_error) {
            echo json_encode(["error" => "Error al conectar a la BD"]);
            exit;
        }

        $nombre = $conn->real_escape_string($input['nombre']);
        $latitud = floatval($input['latitud']);
        $longitud = floatval($input['longitud']);
        $fecha_inicio = $conn->real_escape_string($input['fecha_inicio']);
        $fecha_fin = $conn->real_escape_string($input['fecha_fin']);
        $imagen = isset($input['imagen']) ? $conn->real_escape_string($input['imagen']) : null;

        $sql = "INSERT INTO maestro_evento (nombre, latitud, longitud, fecha_inicio, fecha_fin, imagen) 
                VALUES ('$nombre', $latitud, $longitud, '$fecha_inicio', '$fecha_fin', " . ($imagen ? "'$imagen'" : "NULL") . ")";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["success" => true, "evento_id" => $conn->insert_id]);
        } else {
            echo json_encode(["error" => "No se pudo insertar el evento", "detalle" => $conn->error]);
        }

        $conn->close();
    } else {
        echo json_encode(["error" => "Faltan campos obligatorios"]);
    }
    exit;
}
// ========================
// LISTAR TODOS LOS EVENTOS
// ========================
if ($method === 'GET') {
    $conn = new mysqli("localhost", "root", "", "metodica_maestro");
    if ($conn->connect_error) {
        echo json_encode(["error" => "Error al conectar a la BD"]);
        exit;
    }

    $result = $conn->query("SELECT * FROM maestro_evento ORDER BY id DESC");

    $eventos = [];
    while ($row = $result->fetch_assoc()) {
        $eventos[] = $row;
    }

    echo json_encode(["success" => true, "eventos" => $eventos]);
    $conn->close();
    exit;
}

echo json_encode(["error" => "Método no permitido o mal uso de la API"]);

?>
