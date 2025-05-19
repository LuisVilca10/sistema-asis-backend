<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'metodica_maestro';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

$dni = $_GET['dni'] ?? '';

if (empty($dni)) {
    echo json_encode(['error' => 'DNI requerido']);
    exit;
}

$sql = "SELECT fecha, hora FROM maestro_asistencia WHERE dni = ? ORDER BY id DESC LIMIT 2";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $dni);
$stmt->execute();
$result = $stmt->get_result();

$asistencias = [];

while ($row = $result->fetch_assoc()) {
    $asistencias[] = $row;
}

echo json_encode(['asistencias' => $asistencias]);

$stmt->close();
$conn->close();
