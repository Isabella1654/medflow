<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once "Database.php";
require_once "Auditoria.php"; // 1. Importamos la clase de auditoría

$database = new Database();
$db = $database->conectar();

if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "mensaje" => "Falta el ID de la consulta médica."]);
    exit;
}

$id_consulta = intval($_GET['id']);

try {
    $query = "SELECT 
                cm.id_consulta,
                p.nombre AS paciente_nombre, 
                p.apellido AS paciente_apellido, 
                p.cedula,
                p.eps,
                p.fecha_nacimiento,
                cm.diagnostico,
                cm.tratamiento AS plan_manejo,
                cm.destino_paciente,
                cm.id_medico,
                CONCAT(e.nombres, ' ', e.apellidos) AS medico_tratante,
                e.registro_profesional AS medico_registro,
                e.firma_digital AS medico_firma,
                cm.fecha_consulta AS hora_egreso
              FROM consultas_medicas cm
              INNER JOIN pacientes p ON cm.id_paciente = p.id_paciente
              INNER JOIN empleados e ON cm.id_medico = e.id_empleado
              WHERE cm.id_consulta = :id_consulta";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":id_consulta", $id_consulta, PDO::PARAM_INT);
    $stmt->execute();
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($datos) {
        $cumpleanos = new DateTime($datos['fecha_nacimiento']);
        $hoy = new DateTime();
        $datos['edad'] = $hoy->diff($cumpleanos)->y;

        // 2. REGISTRO AUTOMÁTICO EN LA TABLA DE AUDITORÍA
        Auditoria::registrar(
            $datos['id_medico'], 
            $datos['medico_tratante'], 
            'Evolución e Impresión', 
            'CONSULTAR_EGRESO', 
            "El médico consultó el formato de egreso para el paciente con C.C. " . $datos['cedula']
        );

        echo json_encode(["success" => true, "data" => $datos]);
    } else {
        echo json_encode(["success" => false, "mensaje" => "No se encontró el registro clínico."]);
    }

} catch (PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error de servidor: " . $e->getMessage()]);
}
?>