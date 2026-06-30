<?php
class Database {
    private $host = "localhost";
    private $db_name = "medflow";
    private $username = "root";
    private $password = "";
    public $conn;

    public function conectar() {
        $this->conn = null;

        try {
            // Configuración de la cadena de conexión con UTF-8
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            
            // Forzar a PDO a lanzar excepciones en caso de errores en los queries o bloqueos
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Configurar el modo de obtención de datos por defecto a arreglos asociativos
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $exception) {
            echo "Error de conexión en la base de datos: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>