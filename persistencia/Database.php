<?php
/**
 * CAPA DE PERSISTENCIA
 * Clase Database - Manejo de conexión a la base de datos
 */
class Database {
    private $host = 'localhost';
    private $db_name = 'sistema_visitas';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Obtener conexión a la base de datos
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            throw new Exception("Error de conexión: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>