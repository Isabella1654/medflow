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
    
    // 1. Guardar los signos vitales y calcular el nivel de triaje
    $resultadoTriaje = $triajeModel->guardarTriaje(
        $data['id_paciente'],
        $data['tension_arterial'],
        $data['temperatura'],
        $data['frecuencia_cardiaca']
    );

    if ($resultadoTriaje['success']) {
        $nivel = $resultadoTriaje['nivel_triaje']; // 'Rojo', 'Amarillo' o 'Verde'
        $id_paciente = $data['id_paciente'];
        
        // Determinar qué especialidad de médico se necesita
        // Rojo y Amarillo van para Médicos de Urgencias (id_especialidad de Urgencias o Médico General en turno de urgencias)
        // Para este ejemplo base usaremos id_especialidad = 1 (Medicina General / Urgencias)
        $id_especialidad_buscada = 1; 

        // 2. ALGORITMO DE ASIGNACIÓN AUTOMÁTICA: 
        // Busca un médico activo de esa especialidad que no esté ocupado en este instante
        try {
            $query_medico = "SELECT id_empleado FROM empleados 
                             WHERE id_especialidad = :esp_id 
                               AND estado = 'Activo' 
                             ORDER BY RAND() LIMIT 1"; // Simula asignación por disponibilidad aleatoria/carga rápida
            
            $stmt_med = $db->prepare($query_medico);
            $stmt_med->bindParam(":esp_id", $id_especialidad_buscada);
            $stmt_med->execute();
            $medico = $stmt_med->fetch(PDO::FETCH_ASSOC);

            $id_medico = $medico ? $medico['id_empleado'] : null;
            
            // Asignar consultorios por defecto según el caso
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

            // 3. Insertar el paciente en la cola de atención médica
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
                "mensaje" => "Triaje guardado. Paciente asignado automáticamente a la cola en el consultorio $consultorio."
            ]);

        } catch (PDOException $e) {
            echo json_encode(["success" => false, "mensaje" => "Error en asignación automática: " . $e->getMessage()]);
        }

    } else {
        echo json_encode(["success" => false, "mensaje" => $resultadoTriaje['mensaje']]);
    }
} else {
    echo json_encode(["success" => false, "mensaje" => "Datos de triaje incompletas."]);
}
?>