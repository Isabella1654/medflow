<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once "Database.php";
require_once "Triaje.php";

$database = new Database();
$db = $database->conectar();

$triaje = new Triaje($db);

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['id_paciente']) && !empty($data['tension_arterial']) && isset($data['temperatura']) && !empty($data['frecuencia_cardiaca'])) {
    
    $resultado = $triaje->registrarTriaje(
        $data['id_paciente'],
        $data['tension_arterial'],
        $data['temperatura'],
        $data['frecuencia_cardiaca']
    );

    if ($resultado['success']) {
        http_response_code(201);
        echo json_encode($resultado);
    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "mensaje" => $resultado['error']]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "mensaje" => "Datos incompletos. Todos los signos vitales son obligatorios."]);
}
?>