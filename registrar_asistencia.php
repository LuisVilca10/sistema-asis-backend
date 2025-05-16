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

$evento_id = $_POST['evento_id'] ?? '';
$dni = $_POST['dni'] ?? '';
$fecha = $_POST['fecha'] ?? date('Y-m-d');
$hora = $_POST['hora'] ?? date('H:i:s');
$porx = $_POST['porx'] ?? $dni;

if ($evento_id === '' || $dni === '') {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$sql = "INSERT INTO maestro_asistencia (evento_id, fecha, hora, dni, porx)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $evento_id, $fecha, $hora, $dni, $porx);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Asistencia registrada']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al registrar asistencia: ' . $conn->error]);
}

$stmt->close();
$conn->close();
