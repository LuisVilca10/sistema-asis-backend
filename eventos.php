<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

require_once 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$secretKey = '3st0y-S3gurO-4Qu1';
$method = $_SERVER['REQUEST_METHOD'];

// ==========================
// CREAR EVENTO (POST)
// ==========================
if ($method === 'POST') {
    

    

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
// LISTAR EVENTOS (GET)
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

// ========================
// ELIMINAR EVENTO (DELETE)
// ========================
if ($method === 'DELETE') {
    

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "ID del evento no proporcionado"]);
        exit;
    }

    $id = intval($_GET['id']);
    $conn = new mysqli("localhost", "root", "", "metodica_maestro");
    if ($conn->connect_error) {
        echo json_encode(["error" => "Error al conectar a la BD"]);
        exit;
    }

    $sql = "DELETE FROM maestro_evento WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true, "message" => "Evento eliminado correctamente"]);
    } else {
        echo json_encode(["error" => "No se pudo eliminar el evento", "detalle" => $conn->error]);
    }

    $conn->close();
    exit;
}

if ($method === 'PUT') {
    $headers = getallheaders();

    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['id'])) {
        echo json_encode(["success" => false, "error" => "ID requerido"]);
        exit;
    }

    $conn = new mysqli("localhost", "root", "", "metodica_maestro");
    if ($conn->connect_error) {
        echo json_encode(["success" => false, "error" => "Error de conexión"]);
        exit;
    }

    $id = intval($input['id']);
    $sql = "UPDATE maestro_evento SET ";
    $updates = [];

    if (isset($input['nombre'])) {
        $nombre = $conn->real_escape_string($input['nombre']);
        $updates[] = "nombre = '$nombre'";
    }

    if (isset($input['imagen'])) {
        $base64 = $input['imagen'];
        $ruta = "imagenes_eventos/evento_$id.jpg";

        // Crear carpeta si no existe
        if (!is_dir("imagenes_eventos")) {
            mkdir("imagenes_eventos", 0777, true);
        }

        // Guardar archivo
        file_put_contents($ruta, base64_decode($base64));
        $updates[] = "imagen = '$ruta'";
    }

    if (empty($updates)) {
        echo json_encode(["success" => false, "error" => "Nada para actualizar"]);
        exit;
    }

    $sql .= implode(', ', $updates) . " WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(["success" => true, "message" => "Evento actualizado"]);
    } else {
        echo json_encode(["success" => false, "error" => "Error al actualizar: " . $conn->error]);
    }

    $conn->close();
    exit;
}


echo json_encode(["error" => "Método no permitido o mal uso de la API"]);
?>