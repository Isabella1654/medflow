<?php
class Paciente {
    private $conn;
    private $tabla = "pacientes";

    public $id_paciente;
    public $cedula;
    public $nombre;
    public $apellido;
    public $fecha_nacimiento;
    public $eps;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registrar($cedula, $nombre, $apellido, $fecha_nacimiento, $eps) {
        try {
            // REGLA DE ORO: Eliminar absolutamente todos los espacios en blanco dentro de la cédula
            $cedula = str_replace(' ', '', $cedula);
            
            // Limpiar espacios al inicio y final en el resto de los campos
            $nombre = trim($nombre);
            $apellido = trim($apellido);
            $fecha_nacimiento = trim($fecha_nacimiento);
            $eps = trim($eps);

            // 1. Validar si la cédula (ya limpia) existe en el sistema
            $query_verificar = "SELECT id_paciente, nombre, apellido, fecha_nacimiento FROM " . $this->tabla . " WHERE cedula = :cedula LIMIT 1";
            $stmt_verificar = $this->conn->prepare($query_verificar);
            $stmt_verificar->bindParam(":cedula", $cedula);
            $stmt_verificar->execute();

            if ($stmt_verificar->rowCount() > 0) {
                $paciente_existente = $stmt_verificar->fetch();
                
                // Lógica reactiva para detectar la mayoría de edad en la admisión/triaje
                $cumpleanos = new DateTime($paciente_existente['fecha_nacimiento']);
                $hoy = new DateTime(); 
                $edad = $hoy->diff($cumpleanos)->y; 

                if ($edad >= 18) {
                    return [
                        "success" => false,
                        "status" => "alerta",
                        "error" => "El paciente ya existe. ¡ALERTA! Cumplió la mayoría de edad ($edad años). El auxiliar administrativo debe actualizar el tipo de documento a Cédula de Ciudadanía."
                    ];
                }

                return [
                    "success" => false, 
                    "status" => "existente",
                    "error" => "El paciente con esta cédula ya se encuentra registrado."
                ];
            }

            // 2. Inserción segura si el paciente realmente no existe
            $query_insertar = "INSERT INTO " . $this->tabla . " 
                (cedula, nombre, apellido, fecha_nacimiento, eps) 
                VALUES (:cedula, :nombre, :apellido, :fecha_nacimiento, :eps)";
            
            $stmt = $this->conn->prepare($query_insertar);

            // Sanitizar contra inyecciones
            $cedula = htmlspecialchars(strip_tags($cedula));
            $nombre = htmlspecialchars(strip_tags($nombre));
            $apellido = htmlspecialchars(strip_tags($apellido));
            $fecha_nacimiento = htmlspecialchars(strip_tags($fecha_nacimiento));
            $eps = htmlspecialchars(strip_tags($eps));

            $stmt->bindParam(":cedula", $cedula);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":apellido", $apellido);
            $stmt->bindParam(":fecha_nacimiento", $fecha_nacimiento);
            $stmt->bindParam(":eps", $eps);

            if ($stmt->execute()) {
                return [
                    "success" => true, 
                    "id_paciente" => $this->conn->lastInsertId(),
                    "mensaje" => "Paciente registrado exitosamente en el sistema de admisión."
                ];
            }

            return ["success" => false, "error" => "No se pudo completar el registro del paciente."];

        } catch (PDOException $e) {
            return ["success" => false, "error" => "Error crítico de base de datos: " . $e->getMessage()];
        }
    }
}
?>