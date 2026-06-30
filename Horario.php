<?php
class Horario {
    private $conn;
    private $tabla_horarios = "horarios_empleados";
    private $tabla_novedades = "novedades_reemplazos";
    private $tabla_empleados = "empleados";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * ALGORITMO DE REEMPLAZO AUTOMÁTICO
     * Busca personal disponible de la misma especialidad para cubrir una baja médica.
     */
    public function procesarNovedadYReemplazo($id_empleado_ausente, $motivo, $fecha_inicio, $duracion_dias, $autorizado_por) {
        try {
            $this->conn->beginTransaction();

            // 1. Obtener la especialidad del empleado que se ausenta
            $query_esp = "SELECT id_especialidad, nombres, apellidos FROM " . $this->tabla_empleados . " WHERE id_empleado = :id LIMIT 1";
            $stmt_esp = $this->conn->prepare($query_esp);
            $stmt_esp->bindParam(":id", $id_empleado_ausente);
            $stmt_esp->execute();
            $empleado_ausente = $stmt_esp->fetch();

            if (!$empleado_ausente) {
                return ["success" => false, "mensaje" => "El empleado afectado no existe."];
            }

            $id_especialidad = $empleado_ausente['id_especialidad'];

            // 2. BUSQUEDA INTELIGENTE AUTOMÁTICA:
            // Busca un empleado activo, de la misma especialidad, que NO sea el mismo ausente,
            // priorizando al que tenga menor carga laboral (menos turnos asignados esta semana).
            $query_reemplazo = "SELECT e.id_empleado, e.nombres, e.apellidos, COUNT(h.id_horario) as carga_laboral 
                                FROM " . $this->tabla_empleados . " e
                                LEFT JOIN " . $this->tabla_horarios . " h ON e.id_empleado = h.id_empleado
                                WHERE e.id_especialidad = :id_esp 
                                  AND e.id_empleado != :id_ausente 
                                  AND e.estado = 'Activo'
                                GROUP BY e.id_empleado
                                ORDER BY carga_laboral ASC, RAND() 
                                LIMIT 1";

            $stmt_reem = $this->conn->prepare($query_reemplazo);
            $stmt_reem->bindParam(":id_esp", $id_especialidad);
            $stmt_reem->bindParam(":id_ausente", $id_empleado_ausente);
            $stmt_reem->execute();
            $empleado_reemplazo = $stmt_reem->fetch();

            if (!$empleado_reemplazo) {
                $this->conn->rollBack();
                return [
                    "success" => false, 
                    "mensaje" => "No se encontró ningún profesional disponible con la misma especialidad para cubrir la vacante."
                ];
            }

            $id_reemplazo = $empleado_reemplazo['id_empleado'];

            // 3. Actualizar el estado del empleado ausente (Ej: Pasa a 'Incapacidad')
            $query_update_estado = "UPDATE " . $this->tabla_empleados . " SET estado = :motivo WHERE id_empleado = :id";
            $stmt_update = $this->conn->prepare($query_update_estado);
            $stmt_update->bindParam(":motivo", $motivo);
            $stmt_update->bindParam(":id", $id_empleado_ausente);
            $stmt_update->execute();

            // 4. Registrar la novedad en el historial de reemplazos
            $query_ins_novedad = "INSERT INTO " . $this->tabla_novedades . " 
                (id_empleado_ausente, id_empleado_reemplazo, motivo, fecha_inicio, duracion_dias, autorizado_por) 
                VALUES (:ausente, :reemplazo, :motivo, :fecha_ini, :duracion, :autorizado)";
            
            $stmt_nov = $this->conn->prepare($query_ins_novedad);
            $stmt_nov->bindParam(":ausente", $id_empleado_ausente);
            $stmt_nov->bindParam(":reemplazo", $id_reemplazo);
            $stmt_nov->bindParam(":motivo", $motivo);
            $stmt_nov->bindParam(":fecha_ini", $fecha_inicio);
            $stmt_nov->bindParam(":duracion", $duracion_dias);
            $stmt_nov->bindParam(":autorizado", $autorizado_por);
            $stmt_nov->execute();

            $this->conn->commit();

            return [
                "success" => true,
                "mensaje" => "Novedad procesada. El empleado " . $empleado_ausente['nombres'] . " " . $empleado_ausente['apellidos'] . " entró en " . $motivo . ". El sistema asignó automáticamente a " . $empleado_reemplazo['nombres'] . " " . $empleado_reemplazo['apellidos'] . " como reemplazo por tener menor carga laboral."
            ];

        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ["success" => false, "mensaje" => "Error crítico de transacciones: " . $e->getMessage()];
        }
    }
}
?>