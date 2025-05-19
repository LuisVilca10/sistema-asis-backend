<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'metodica_maestro';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

$dni = $_GET['dni'] ?? '';
$evento_id = $_GET['evento_id'] ?? '';
$fecha = $_GET['fecha'] ?? date('Y-m-d');

if (empty($dni) || empty($evento_id)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM maestro_asistencia WHERE dni = ? AND evento_id = ? AND fecha = ?");
$stmt->bind_param("sis", $dni, $evento_id, $fecha);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode(['ya_asistio' => $result->num_rows > 0]);

$stmt->close();
$conn->close();
