<?php
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
$user = 'root'; // Cambia si es necesario
$password = ''; // Cambia si es necesario
$database = 'metodica_maestro';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}

// Obtener datos
$dni = $_POST['dni'] ?? '';
$nombre = $_POST['nombres'] ?? '';

if ($dni === '' || $nombre === '') {
    echo json_encode(['success' => false, 'message' => 'Nombre y DNI requeridos']);
    exit;
}

$le = $dni;
$fecha_registro = date('Y-m-d');
$servidor = null;
$centro = null;

// Insertar en la base de datos
$sql = "INSERT INTO maestro_data (nombres, dni, le, fecha_registro, servidor, centro)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $nombre, $dni, $le, $fecha_registro, $servidor, $centro);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuario registrado']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al registrar usuario: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
