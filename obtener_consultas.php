<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once "Database.php";

$database = new Database();
$db = $database->conectar();

try {
    // Consulta experta que calcula los tiempos de estancia y unifica los datos médicos
    $query = "SELECT 
                cm.id_consulta,
                p.nombre AS paciente_nombre, 
                p.apellido AS paciente_apellido, 
                p.cedula,
                p.eps,
                cm.diagnostico,
                cm.tratamiento AS medicinas_formuladas,
                cm.destino_paciente,
                CONCAT(e.nombres, ' ', e.apellidos) AS medico_tratante,
                cm.fecha_consulta AS hora_salida,
                col.fecha_ingreso AS hora_ingreso,
                TIMESTAMPDIFF(MINUTE, col.fecha_ingreso, cm.fecha_consulta) AS duracion_minutos
              FROM consultas_medicas cm
              INNER JOIN pacientes p ON cm.id_paciente = p.id_paciente
              INNER JOIN empleados e ON cm.id_medico = e.id_empleado
              INNER JOIN atenciones_colas col ON cm.id_cola = col.id_cola
              ORDER BY cm.fecha_consulta DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $consultas]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error al obtener historial clínico: " . $e->getMessage()]);
}
?>