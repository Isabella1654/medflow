<?php
class Triaje {
    private $conn;
    private $tabla = "atenciones_triaje";

    public $id_triaje;
    public $id_paciente;
    public $tension_arterial;
    public $temperatura;
    public $frecuencia_cardiaca;
    public $nivel_triaje;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para calcular el nivel de triaje automáticamente
    private function calcularNivel($tension, $temperatura, $frecuencia) {
        // Separar la tensión por la barra (Ej: "120/80")
        $partes_tension = explode('/', $tension);
        $sistolica = isset($partes_tension[0]) ? (int)trim($partes_tension[0]) : 120;

        // Regla 1: ROJO (Emergencia crítica vital)
        if ($temperatura >= 40.0 || $temperatura < 35.0 || $frecuencia >= 130 || $frecuencia < 45 || $sistolica >= 180 || $sistolica <= 70) {
            return 'Rojo';
        }
        
        // Regla 2: AMARILLO (Urgencia moderada / signos alterados)
        if (($temperatura >= 38.5 && $temperatura < 40.0) || ($frecuencia >= 100 && $frecuencia < 130) || ($sistolica >= 140 && $sistolica < 180)) {
            return 'Amarillo';
        }

        // Regla 3: VERDE (Consulta externa / Signos estables)
        return 'Verde';
    }

    // Guardar la valoración del triaje
    public function registrarTriaje($id_paciente, $tension, $temperatura, $frecuencia) {
        try {
            // Calcular el nivel con la lógica médica automática
            $nivel = $this->calcularNivel($tension, $temperatura, $frecuencia);

            $query = "INSERT INTO " . $this->tabla . " 
                (id_paciente, tension_arterial, temperatura, frecuencia_cardiaca, nivel_triaje) 
                VALUES (:id_paciente, :tension, :temperatura, :frecuencia, :nivel)";
            
            $stmt = $this->conn->prepare($query);

            // Sanitizar datos
            $id_paciente = (int)$id_paciente;
            $tension = htmlspecialchars(strip_tags(trim($tension)));
            $temperatura = (float)$temperatura;
            $frecuencia = (int)$frecuencia;

            $stmt->bindParam(":id_paciente", $id_paciente);
            $stmt->bindParam(":tension", $tension);
            $stmt->bindParam(":temperatura", $temperatura);
            $stmt->bindParam(":frecuencia", $frecuencia);
            $stmt->bindParam(":nivel", $nivel);

            if ($stmt->execute()) {
                return [
                    "success" => true,
                    "id_triaje" => $this->conn->lastInsertId(),
                    "nivel_triaje" => $nivel,
                    "mensaje" => "Valoración de triaje guardada con éxito. Clasificación: Nivel $nivel."
                ];
            }
            return ["success" => false, "error" => "No se pudo registrar la valoración médica."];

        } catch (PDOException $e) {
            return ["success" => false, "error" => "Error en la base de datos: " . $e->getMessage()];
        }
    }
}
?>