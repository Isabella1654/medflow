<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once "Database.php";

$database = new Database();
$db = $database->conectar();

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['id_cola']) && !empty($data['id_paciente']) && !empty($data['id_medico']) && !empty($data['diagnostico']) && !empty($data['destino_paciente'])) {
    
    try {
        $db->beginTransaction();

        // 1. Registrar la atención médica formal en la historia clínica
        $query_consulta = "INSERT INTO consultas_medicas (id_cola, id_paciente, id_medico, diagnostico, tratamiento, destino_paciente) 
                           VALUES (:id_cola, :id_paciente, :id_medico, :diagnostico, :tratamiento, :destino)";
        
        $stmt = $db->prepare($query_consulta);
        $stmt->bindParam(":id_cola", $data['id_cola']);
        $stmt->bindParam(":id_paciente", $data['id_paciente']);
        $stmt->bindParam(":id_medico", $data['id_medico']);
        $stmt->bindParam(":diagnostico", $data['diagnostico']);
        $stmt->bindParam(":tratamiento", $data['tratamiento']);
        $stmt->bindParam(":destino", $data['destino_paciente']);
        $stmt->execute();

        // 2. Cambiar el estado en la cola a 'Atendido' para liberarlo del dashboard
        $query_update_cola = "UPDATE atenciones_colas 
                              SET estado_atencion = 'Atendido' 
                              WHERE id_cola = :id_cola";
        
        $stmt_update = $db->prepare($query_update_cola);
        $stmt_update->bindParam(":id_cola", $data['id_cola']);
        $stmt_update->execute();

        $db->commit();

        echo json_encode([
            "success" => true,
            "mensaje" => "Paciente atendido con éxito. El tablero se ha actualizado."
        ]);

    } catch (PDOException $e) {
        $db->rollBack();
        echo json_encode(["success" => false, "mensaje" => "Error al procesar la atención: " . $e->getMessage()]);
    }

} else {
    echo json_encode(["success" => false, "mensaje" => "Datos de consulta incompletos."]);
}
?>