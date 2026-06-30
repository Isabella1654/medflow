<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once "Database.php";
require_once "Horario.php";

$database = new Database();
$db = $database->conectar();

$horario = new Horario($db);

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['id_empleado_ausente']) && !empty($data['motivo']) && !empty($data['duracion_dias']) && !empty($data['autorizado_por'])) {
    
    $fecha_actual = date('Y-m-d H:i:s');

    $resultado = $horario->procesarNovedadYReemplazo(
        $data['id_empleado_ausente'],
        $data['motivo'],
        $fecha_actual,
        $data['duracion_dias'],
        $data['autorizado_por']
    );

    if ($resultado['success']) {
        http_response_code(200);
        echo json_encode($resultado);
    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "mensaje" => $resultado['mensaje']]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "mensaje" => "Datos incompletos para registrar la novedad laboral."]);
}
?>