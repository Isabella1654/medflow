<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once "Database.php";
require_once "Triaje.php";

$database = new Database();
$db = $database->conectar();

$triajeModel = new Triaje($db);

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['id_paciente']) && isset($data['temperatura']) && isset($data['frecuencia_cardiaca']) && !empty($data['tension_arterial'])) {
    
    // 1. LLAMADO CORREGIDO: Usamos registrarTriaje que es el método real de tu modelo
    $resultadoTriaje = $triajeModel->registrarTriaje(
        $data['id_paciente'],
        $data['tension_arterial'],
        $data['temperatura'],
        $data['frecuencia_cardiaca']
    );

    if ($resultadoTriaje['success']) {
        $nivel = $resultadoTriaje['nivel_triaje']; 
        $id_paciente = $data['id_paciente'];

        try {
            // 2. Buscar un médico activo disponible en el hospital
            $query_medico = "SELECT id_empleado FROM empleados 
                             WHERE estado = 'Activo' 
                             ORDER BY RAND() LIMIT 1"; 
            
            $stmt_med = $db->prepare($query_medico);
            $stmt_med->execute();
            $medico = $stmt_med->fetch(PDO::FETCH_ASSOC);

            $id_medico = $medico ? $medico['id_empleado'] : null;
            
            // 3. Asignar consultorios por defecto según el nivel de triaje calculado
            $consultorio = "Cons-01";
            $estado_inicial = "En Espera";
            
            if ($nivel === 'Rojo') {
                $consultorio = "SALA-REA";
                $estado_inicial = "Reanimación";
            } elseif ($nivel === 'Amarillo') {
                $consultorio = "Urg-02";
            } else {
                $consultorio = "Gral-05";
            }

            // 4. Insertar el paciente en la cola de atención médica
            $query_cola = "INSERT INTO atenciones_colas (id_paciente, nivel_triaje, id_medico_asignado, consultorio, estado_atencion) 
                           VALUES (:paciente, :nivel, :medico, :consultorio, :estado)";
            
            $stmt_cola = $db->prepare($query_cola);
            $stmt_cola->bindParam(":paciente", $id_paciente);
            $stmt_cola->bindParam(":nivel", $nivel);
            $stmt_cola->bindParam(":medico", $id_medico);
            $stmt_cola->bindParam(":consultorio", $consultorio);
            $stmt_cola->bindParam(":estado", $estado_inicial);
            $stmt_cola->execute();

            echo json_encode([
                "success" => true,
                "nivel_triaje" => $nivel,
                "mensaje" => "Triaje guardado. Paciente asignado automáticamente a la cola en el consultorio " . $consultorio
            ]);

        } catch (PDOException $e) {
            echo json_encode(["success" => false, "mensaje" => "Error en asignación automática de cola: " . $e->getMessage()]);
        }

    } else {
        echo json_encode(["success" => false, "mensaje" => $resultadoTriaje['error']]);
    }
} else {
    echo json_encode(["success" => false, "mensaje" => "Datos de triaje incompletos."]);
}
?>