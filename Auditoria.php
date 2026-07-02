<?php
require_once "Database.php";

class Auditoria {
    public static function registrar($id_usuario, $usuario_nombre, $modulo, $accion, $descripcion) {
        try {
            $database = new Database();
            $db = $database->conectar();

            $query = "INSERT INTO auditoria_sistema (id_usuario, usuario_nombre, modulo, accion, descripcion, ip_origen) 
                      VALUES (:id_usuario, :usuario_nombre, :modulo, :accion, :descripcion, :ip_origen)";
            
            $stmt = $db->prepare($query);
            
            // Obtener IP del cliente de forma segura
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(":usuario_nombre", $usuario_nombre, PDO::PARAM_STR);
            $stmt->bindParam(":modulo", $modulo, PDO::PARAM_STR);
            $stmt->bindParam(":accion", $accion, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":ip_origen", $ip, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            // Se puede registrar en un log de archivos local si falla la BD
            error_log("Error en auditoría: " . $e->getMessage());
            return false;
        }
    }
}
?>