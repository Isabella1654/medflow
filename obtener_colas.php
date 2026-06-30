<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once "Database.php";

$database = new Database();
$db = $database->conectar();

try {
    // Consulta corregida usando 'nombre' y 'apellido' en lugar de 'nombres' y 'apellidos' para la tabla pacientes
    $query = "SELECT c.id_cola, p.nombre AS paciente_nombres, p.apellido AS paciente_apellidos, 
                     c.nivel_triaje, c.consultorio, c.estado_atencion,
                     e.nombres AS medico_nombres, e.apellidos AS medico_apellidos
              FROM atenciones_colas c
              INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
              LEFT JOIN empleados e ON c.id_medico_asignado = e.id_empleado
              ORDER BY 
                CASE 
                    WHEN c.nivel_triaje = 'Rojo' THEN 1
                    WHEN c.nivel_triaje = 'Amarillo' THEN 2
                    WHEN c.nivel_triaje = 'Verde' THEN 3
                END ASC, c.fecha_ingreso DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $colas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $colas]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error al cargar el tablero: " . $e->getMessage()]);
}
?>