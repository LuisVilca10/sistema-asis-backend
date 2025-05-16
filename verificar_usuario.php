<?php
// Cabeceras para CORS y JSON
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Conexión a la base de datos
$host = 'localhost';
$user = 'root'; // ← Reemplaza con tu usuario
$password = ''; // ← Reemplaza con tu contraseña
$database = 'metodica_maestro'; // ← Reemplaza con el nombre de tu base de datos

$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}

// Obtener el DNI desde GET
$dni = $_GET['dni'] ?? '';

if (empty($dni)) {
    echo json_encode(['success' => false, 'message' => 'DNI requerido']);
    exit;
}

// Consulta preparada
$sql = "SELECT id FROM maestro_data WHERE dni = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $dni);
$stmt->execute();
$result = $stmt->get_result();

// Resultado
$existe = $result->num_rows > 0;
echo json_encode(['existe' => $existe]);

$stmt->close();
$conn->close();
