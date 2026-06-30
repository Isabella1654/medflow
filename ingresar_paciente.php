<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once "Database.php";
require_once "Paciente.php";

try {
    $database = new Database();
    $db = $database->conectar();
    
    $paciente = new Paciente($db);
    
    // Capturar los datos JSON crudos desde el Frontend (Simulando la apertura del folio/ingreso)
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data['cedula']) && !empty($data['nombre']) && !empty($data['apellido']) && !empty($data['fecha_nacimiento']) && !empty($data['eps'])) {
        
        // Ejecutar el método que limpia espacios y evalúa la edad justo en este momento de la atención
        $resultado = $paciente->registrar(
            $data['cedula'],
            $data['nombre'],
            $data['apellido'],
            $data['fecha_nacimiento'],
            $data['eps']
        );
        
        // Si el paciente ya existe y el sistema detectó que es mayor de edad en este ingreso
        if (isset($resultado['status']) && $resultado['status'] === 'alerta') {
            http_response_code(200); // Código 200 porque la consulta fue exitosa, pero lleva una advertencia de negocio
            echo json_encode([
                "success" => false,
                "alerta_administrativa" => true,
                "mensaje" => $resultado['error']
            ]);
        } 
        // Si es un paciente nuevo registrado con éxito
        elseif ($resultado['success']) {
            http_response_code(201);
            echo json_encode([
                "success" => true,
                "mensaje" => $resultado['mensaje'],
                "id_paciente" => $resultado['id_paciente']
            ]);
        } 
        // Cualquier otro error controlado (ej: paciente existente pero sigue siendo menor de edad)
        else {
            http_response_code(400);
            echo json_encode([
                "success" => false, 
                "mensaje" => $resultado['error']
            ]);
        }
        
    } else {
        http_response_code(400);
        echo json_encode([
            "success" => false, 
            "mensaje" => "No se pudieron procesar los datos. Todos los campos del paciente son obligatorios."
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "mensaje" => "Error inesperado en el servidor de admisión: " . $e->getMessage()
    ]);
}
?>